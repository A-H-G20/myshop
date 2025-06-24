<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

$user_id = $_SESSION['user_id'] ?? 0;

$cartQuery = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$cartQuery->execute([$user_id]);
$cart = $cartQuery->fetch(PDO::FETCH_ASSOC);

$cart_items = [];
if ($cart) {
    $cart_id = $cart['cart_id'];

    $itemsQuery = $pdo->prepare("
        SELECT ci.cart_item_id, ci.quantity, p.name, p.price, p.images 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?
    ");
    $itemsQuery->execute([$cart_id]);
    $cart_items = $itemsQuery->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart - Carrefour Lebanon</title>
    <link rel="stylesheet" href="css/cart.css">
</head>
<body>

<?php include 'header.php'; ?>
<div class="main-content">
    <?php if (empty($cart_items)): ?>
        <div class="empty-cart" id="emptyCart">
            <div class="empty-cart-icon">
                <div class="shopping-cart">
                    <div class="cart-body">
                        <div class="carrefour-logo">🛒<br>Carrefour</div>
                    </div>
                    <div class="cart-handle"></div>
                    <div class="cart-wheels">
                        <div class="wheel"></div>
                        <div class="wheel"></div>
                    </div>
                </div>
                <div class="zero-badge">0</div>
            </div>
            <h2>Looking for something?</h2>
            <p>Add your favourite items to your cart.</p>
            <button class="start-shopping-btn" onclick="window.location.href='index.php'">Start Shopping</button>
        </div>
    <?php else: ?>
        <div class="cart-container" id="cartWithItems">
            <div class="cart-section">
                <div class="checkout-section">
                    <div class="checkout-title">Ready to Checkout?</div>
                </div>

                <div class="cart-items">
                    <div class="cart-title">My Cart</div>
                    <?php 
                    $total = 0;
                    foreach ($cart_items as $item):
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                    ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <img src="<?= htmlspecialchars($item['images']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 80px;">
                        </div>
                        <div class="item-details">
                            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="item-price"><?= number_format($item_total) ?><span style="font-size: 12px; color: #666;"> LBP</span></div>
                        </div>
                        <div class="item-controls">
                            <span>Qty: <?= $item['quantity'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="subtotal">
                    <span>Subtotal</span>
                    <span><?= number_format($total) ?> LBP</span>
                </div>
            </div>

            <div class="order-summary">
                <div class="summary-title">Order Summary</div>
                <div class="summary-row">
                    <span>LBP</span>
                    <span><?= number_format($total) ?>.00</span>
                </div>
                <div class="summary-row">
                    <span></span>
                    <span class="vat-note">(Incl. of VAT)</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
