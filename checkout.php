<?php
require 'config.php';
require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info (for email and address)
$user_stmt = $pdo->prepare("SELECT first_name, email, address, city FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

$first_name = $user['first_name'];
$email = $user['email'];
$shipping_address = $user['address'];
$billing_address = $user['city'];

// Fetch cart items with size and color
$stmt = $pdo->prepare("
    SELECT ci.product_id, ci.quantity, ci.selected_size, ci.selected_color, 
           p.name, p.price
    FROM cart c
    JOIN cart_items ci ON c.cart_id = ci.cart_id
    JOIN products p ON ci.product_id = p.product_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    echo "Your cart is empty.";
    exit;
}

// Stripe setup
\Stripe\Stripe::setApiKey("");

$line_items = [];
$total_amount = 0;

foreach ($cart_items as $item) {
    // Create product name with size and color info for Stripe
    $product_name = $item['name'];
    $product_description = '';
    
    if (!empty($item['selected_size']) || !empty($item['selected_color'])) {
        $attributes = [];
        if (!empty($item['selected_size'])) {
            $attributes[] = "Size: " . $item['selected_size'];
        }
        if (!empty($item['selected_color'])) {
            $attributes[] = "Color: " . $item['selected_color'];
        }
        $product_description = implode(', ', $attributes);
        $product_name .= " (" . $product_description . ")";
    }
    
    $line_items[] = [
        'quantity' => $item['quantity'],
        'price_data' => [
            'currency' => 'usd',
            'unit_amount' => $item['price'] * 100,
            'product_data' => [
                'name' => $product_name,
                'description' => $product_description
            ]
        ]
    ];
    $total_amount += $item['price'] * $item['quantity'];
}

// Insert order
$now = date('Y-m-d H:i:s');
$status = 'pending';
$payment_method = 'Stripe';
$payment_status = 'unpaid';

$order_stmt = $pdo->prepare("
    INSERT INTO orders (user_id, total_amount, status, shipping_address, billing_address, payment_method, payment_status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$order_stmt->execute([
    $user_id, $total_amount, $status, $shipping_address, $billing_address,
    $payment_method, $payment_status, $now, $now
]);

$order_id = $pdo->lastInsertId();

// Insert order items with size and color
$item_stmt = $pdo->prepare("
    INSERT INTO order_items (order_id, product_id, quantity, price_at_time, selected_size, selected_color, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

foreach ($cart_items as $item) {
    $item_stmt->execute([
        $order_id,
        $item['product_id'],
        $item['quantity'],
        $item['price'],
        $item['selected_size'],
        $item['selected_color'],
        $now
    ]);
}

// Send confirmation email with order details including size and color
try {
    $mail = new PHPMailer(true);
    include 'email.php'; // Sets $mail->Username and $mail->Password

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('email@gmail.com', 'Myshop Store');
    $mail->addAddress($email, $first_name);
    $mail->isHTML(true);
    $mail->Subject = 'Your Order is Being Processed - Order #' . $order_id;

    // Build order items HTML for email
    $order_items_html = '';
    foreach ($cart_items as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $attributes_html = '';
        
        if (!empty($item['selected_size']) || !empty($item['selected_color'])) {
            $attributes = [];
            if (!empty($item['selected_size'])) {
                $attributes[] = "<span style='background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-size: 12px;'>Size: " . htmlspecialchars($item['selected_size']) . "</span>";
            }
            if (!empty($item['selected_color'])) {
                $attributes[] = "<span style='background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-size: 12px;'>Color: " . htmlspecialchars($item['selected_color']) . "</span>";
            }
            $attributes_html = '<br>' . implode(' ', $attributes);
        }
        
        $order_items_html .= "
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #eee;'>
                    <strong>" . htmlspecialchars($item['name']) . "</strong>
                    {$attributes_html}
                </td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>" . $item['quantity'] . "</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>$" . number_format($item['price'], 2) . "</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>$" . number_format($item_total, 2) . "</td>
            </tr>
        ";
    }

    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
            <div style='text-align: center; padding-bottom: 20px;'>
                <h2 style='color: #028383;'>Thank You for Your Order!</h2>
            </div>
            
            <p style='font-size: 16px; color: #333;'>Hi <b>{$first_name}</b>,</p>
            <p style='font-size: 16px; color: #555;'>We received your order <strong>#$order_id</strong>. Our team is currently processing it.</p>
            
            <div style='margin: 20px 0;'>
                <h3 style='color: #028383; margin-bottom: 10px;'>Order Details:</h3>
                <table style='width: 100%; border-collapse: collapse; background: white; border-radius: 5px; overflow: hidden;'>
                    <thead>
                        <tr style='background: #028383; color: white;'>
                            <th style='padding: 12px; text-align: left;'>Product</th>
                            <th style='padding: 12px; text-align: center;'>Qty</th>
                            <th style='padding: 12px; text-align: right;'>Price</th>
                            <th style='padding: 12px; text-align: right;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$order_items_html}
                        <tr style='background: #f8f9fa; font-weight: bold;'>
                            <td colspan='3' style='padding: 15px; text-align: right; border-top: 2px solid #028383;'>Grand Total:</td>
                            <td style='padding: 15px; text-align: right; border-top: 2px solid #028383; color: #028383;'>$" . number_format($total_amount, 2) . "</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style='margin: 20px 0; padding: 15px; background: white; border-radius: 5px;'>
                <h4 style='color: #028383; margin-bottom: 10px;'>Shipping Information:</h4>
                <p style='margin: 5px 0;'><strong>Shipping Address:</strong> $shipping_address</p>
                <p style='margin: 5px 0;'><strong>Billing Address (City):</strong> $billing_address</p>
            </div>
            
            <p style='font-size: 16px; color: #555;'>We'll send you another email with tracking information once your order ships.</p>
            <p style='font-size: 16px; color: #555;'>Thank you for shopping with us!</p>
            
            <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>
                <p style='font-size: 16px; color: #028383;'><b>Myshop Team</b></p>
                <p style='font-size: 14px; color: #888;'>Need help? Contact us at support@myshop.com</p>
            </div>
        </div>
    ";

    $mail->send();
} catch (Exception $e) {
    error_log("Email Error: " . $mail->ErrorInfo);
}

// Clear the cart after successful order creation
$clear_cart_stmt = $pdo->prepare("
    DELETE ci FROM cart_items ci
    JOIN cart c ON c.cart_id = ci.cart_id
    WHERE c.user_id = ?
");
$clear_cart_stmt->execute([$user_id]);

// Stripe checkout session
$checkout_session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
    "success_url" => "http://localhost/myshop/orders.php?order_id=" . $order_id,
    "cancel_url" => "http://localhost/myshop/cart.php",
    "locale" => "auto",
    "line_items" => $line_items,
    "metadata" => [
        "order_id" => $order_id
    ]
]);

http_response_code(303);
header("Location: " . $checkout_session->url);
exit;
?>