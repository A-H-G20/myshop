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

// Fetch cart items
$stmt = $pdo->prepare("
    SELECT ci.product_id, ci.quantity, p.name, p.price
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
\Stripe\Stripe::setApiKey("sk_test_51Rb3oLFYWxx1m0NMXgUNK1HPza5Jvr9EfLdZA6QZICC6U4CIt2FaNWMKT1fpYsoQmQgLnLjEx5Yewh3hKjIcmHOx00lwYpMQMk");

$line_items = [];
$total_amount = 0;

foreach ($cart_items as $item) {
    $line_items[] = [
        'quantity' => $item['quantity'],
        'price_data' => [
            'currency' => 'usd',
            'unit_amount' => $item['price'] * 100,
            'product_data' => [
                'name' => $item['name']
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

// Insert order items
$item_stmt = $pdo->prepare("
    INSERT INTO order_items (order_id, product_id, quantity, price_at_time, created_at)
    VALUES (?, ?, ?, ?, ?)
");

foreach ($cart_items as $item) {
    $item_stmt->execute([
        $order_id,
        $item['product_id'],
        $item['quantity'],
        $item['price'],
        $now
    ]);
}

// Send confirmation email
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
    $mail->Subject = 'Your Order is Being Processed';

    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
            <div style='text-align: center; padding-bottom: 20px;'>
                <h2 style='color: #028383;'>Thank You for Your Order</h2>
            </div>
            <p style='font-size: 16px; color: #333;'>Hi <b>{$first_name}</b>,</p>
            <p style='font-size: 16px; color: #555;'>We received your order #{$order_id}. Our team is currently processing it.</p>
            <p style='font-size: 16px; color: #555;'>Shipping Address: <strong>$shipping_address</strong><br>Billing Address (City): <strong>$billing_address</strong></p>
            <p style='font-size: 16px; color: #555;'>Total: <strong>$$total_amount USD</strong></p>
            <p style='font-size: 16px; color: #555;'>Thank you for shopping with us!</p>
            <p style='font-size: 16px; color: #028383;'><b>Myshop Team</b></p>
        </div>
    ";

    $mail->send();
} catch (Exception $e) {
    error_log("Email Error: " . $mail->ErrorInfo);
}

// Stripe checkout session
$checkout_session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
    "success_url" => "http://localhost/sm/orders.php?order_id=" . $order_id,
    "cancel_url" => "http://localhost/sm",
    "locale" => "auto",
    "line_items" => $line_items
]);

http_response_code(303);
header("Location: " . $checkout_session->url);
exit;
