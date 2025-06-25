<?php
include 'header.php';
include 'config.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'] ?? 0;

if ($order_id <= 0 || $user_id <= 0) {
    header("Location: orders.php");
    exit;
}

// Fetch order details
$stmt = $pdo->prepare("
    SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) AS customer_name, u.email, u.phone
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.order_id = ? AND o.user_id = ?
");

$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div style='padding: 20px; text-align: center;'>Order not found.</div>";
    exit;
}

// Fetch order items
$stmt2 = $pdo->prepare("
    SELECT oi.*, p.name, p.images
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmt2->execute([$order_id]);
$items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Calculate subtotal
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price_at_time'] * $item['quantity'];
}

// Simulate delivery fee
$delivery_fee = 3;
$total = $subtotal + $delivery_fee;
?>

<link rel="stylesheet" href="css/order-details.css">
<link rel="stylesheet" href="css/index.css">
<div class="container">
    <div class="order-details">
        <div class="order-header">
            <h1 class="order-title">Order #<?= $order['order_id'] ?></h1>
            <div class="order-info">
                <div><strong>Order Date:</strong> <?= date('F j, Y', strtotime($order['created_at'])) ?></div>
                <div><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
                <div><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></div>
                <div><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></div>
            </div>
            <div class="order-status">
                <div class="status-badge status-<?= htmlspecialchars($order['status']) ?>"><?= ucfirst($order['status']) ?></div>
                <div class="order-total">Total: <?= number_format($total, 0, '.', ',') ?> $</div>
            </div>
        </div>

        <div class="order-sections">
            <div class="order-items">
                <h2 class="section-title">Order Items</h2>
                <div class="items-list">
                    <?php foreach ($items as $item):
                        $img_raw = explode(',', $item['images'])[0] ?? '';
                        $img = !empty($img_raw) ? 'uploads/' . basename(trim($img_raw)) : '';

                        $total_item_price = $item['price_at_time'] * $item['quantity'];
                    ?>
                        <div class="order-item">
                            <?php if (!empty($img) && file_exists($img)): ?>
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="item-image-fallback" style="display: none;">
                                    <div>📷<br>No Image</div>
                                </div>
                            <?php else: ?>
                                <div class="item-image-fallback" style="display: flex;">
                                    <div>📷<br>No Image</div>
                                </div>
                            <?php endif; ?>

                            <div class="item-details">
                                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="item-price"><?= number_format($item['price_at_time'], 0, '.', ',') ?> $ each</div>
                                <div class="item-quantity">Quantity: <?= $item['quantity'] ?></div>
                            </div>
                            <div class="item-total"><?= number_format($total_item_price, 0, '.', ',') ?> $</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="order-sidebar">
                <div class="order-summary">
                    <h2 class="section-title">Order Summary</h2>
                    <div class="summary-content">
                        <div class="summary-item"><span>Subtotal:</span><span><?= number_format($subtotal, 0, '.', ',') ?> $</span></div>
                        <div class="summary-item"><span>Delivery Fee:</span><span><?= number_format($delivery_fee, 0, '.', ',') ?> $</span></div>
                        <div class="summary-item"><span>Total:</span><span><?= number_format($total, 0, '.', ',') ?> $</span></div>
                    </div>
                </div>

                <div class="delivery-info">
                    <h2 class="section-title">Delivery Information</h2>
                    <div class="delivery-content">
                        <div><strong>Delivery Method:</strong> Home Delivery</div>
                        <div><strong>Estimated Delivery:</strong> <?= date('F j, Y', strtotime($order['created_at'] . ' +2 days')) ?></div>
                        <div><strong>Delivery Address:</strong></div>
                        <div class="delivery-address"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<script>
    function trackOrder() {
        alert("Tracking info sent to your email.");
    }

    function downloadInvoice() {
        alert("Downloading invoice...");
        const link = document.createElement('a');
        link.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent('Invoice for Order #<?= $order['order_id'] ?>\n\nTotal: <?= number_format($total, 0, '.', ',') ?> $');
        link.download = 'invoice-<?= $order['order_id'] ?>.txt';
        link.click();
    }

    function cancelOrder() {
        if (confirm("Are you sure you want to cancel this order?")) {
            const btn = document.getElementById('cancelBtn');
            btn.disabled = true;
            btn.innerText = 'Cancelling...';

            fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'order_id=<?= $order['order_id'] ?>'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        btn.innerText = 'Order Cancelled';
                        btn.classList.remove('btn-danger');
                        btn.classList.add('btn-disabled');
                        document.querySelector('.status-badge').textContent = 'Cancelled';
                        document.querySelector('.status-badge').classList = 'status-badge status-cancelled';
                        alert('Order cancelled successfully.');
                    } else {
                        alert('Failed to cancel order.');
                        btn.disabled = false;
                        btn.innerText = 'Cancel Order';
                    }
                });
        }
    }
</script>