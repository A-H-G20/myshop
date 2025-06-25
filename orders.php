<?php
include 'header.php';
include 'config.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if ($user_id <= 0) {
    header('Location: login.php');
    exit;
}

// If payment was successful, mark order as paid and clear the cart
if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    $update = $pdo->prepare("UPDATE orders SET status = 'confirmed', payment_status = 'paid', updated_at = NOW() WHERE order_id = ? AND user_id = ?");
    $update->execute([$order_id, $user_id]);

    $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch();

    if ($cart) {
        $cart_id = $cart['cart_id'];
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cart_id]);
        $pdo->prepare("DELETE FROM cart WHERE cart_id = ?")->execute([$cart_id]);
    }
}

$status_filter = $_GET['status'] ?? 'all';
$sort_order = $_GET['sort'] ?? 'desc';

try {
    $query = "
        SELECT o.*, COUNT(oi.order_item_id) AS item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.user_id = ?
    ";

    $params = [$user_id];

    if ($status_filter !== 'all') {
        $query .= " AND o.status = ?";
        $params[] = $status_filter;
    }

    $query .= " GROUP BY o.order_id ORDER BY o.created_at " . ($sort_order === 'asc' ? 'ASC' : 'DESC');

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $orderIds = array_column($orders, 'order_id');
    $items = [];

    if (!empty($orderIds)) {
        $in = str_repeat('?,', count($orderIds) - 1) . '?';
        $stmt2 = $pdo->prepare("
            SELECT oi.*, p.name, p.images
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id IN ($in)
        ");
        $stmt2->execute($orderIds);
        $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $item) {
            $items[$item['order_id']][] = $item;
        }
    }

} catch (PDOException $e) {
    $orders = [];
    $items = [];
}
?>

<link rel="stylesheet" href="css/orders.css">
<link rel="stylesheet" href="css/index.css">

<div class="container">
    <div class="orders-container">
        <div class="page-header">
            <h1 class="page-title">My Orders</h1>

            <?php if (isset($_GET['order_id'])): ?>
                <div class="success-message" style="background:#d4edda;padding:10px;border-radius:5px;margin-bottom:15px;">
                    ✅ Your order was successfully placed!
                </div>
            <?php endif; ?>

            <div class="orders-filter">
                <select class="filter-select" onchange="filterOrders(this.value)">
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Orders</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>

                <select class="filter-select" onchange="sortOrders(this.value)">
                    <option value="desc" <?= $sort_order === 'desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="asc" <?= $sort_order === 'asc' ? 'selected' : '' ?>>Oldest First</option>
                </select>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <h3>No Orders Found</h3>
                <p>You haven't placed any orders yet.</p>
                <a href="index.php" class="shop-now-btn">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info-item"><strong>Order ID:</strong> #<?= $order['order_id'] ?></div>
                            <div class="order-info-item"><strong>Date:</strong> <?= date('M j, Y', strtotime($order['created_at'])) ?></div>
                            <div class="order-info-item"><strong>Status:</strong> <?= ucfirst($order['status']) ?></div>
                            <div class="order-info-item"><strong>Total:</strong> <?= number_format($order['total_amount'], 0, '.', ',') ?> LBP</div>
                        </div>

                        <div class="order-items">
                            <h4><?= $order['item_count'] ?> Item(s)</h4>
                            <?php if (!empty($items[$order['order_id']])): ?>
                             <?php foreach ($items[$order['order_id']] as $item): 
    $images = explode(',', $item['images']);
    $rawImage = trim($images[0] ?? '');
    $img = !empty($rawImage) ? 'uploads/' . basename($rawImage) : '';
?>
    <div class="order-item">
        <?php if (!empty($img) && file_exists($img)): ?>
            <img src="<?= htmlspecialchars($img); ?>" alt="<?= htmlspecialchars($item['name']); ?>" class="item-image">
        <?php else: ?>
            <div class="item-image-fallback">📷</div>
        <?php endif; ?>

                                        <div class="item-details">
                                            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="item-price"><?= number_format($item['price_at_time'], 0, '.', ',') ?> LBP</div>
                                            <div class="item-quantity">Qty: <?= $item['quantity'] ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No items found.</p>
                            <?php endif; ?>
                        </div>

                        <div class="order-actions">
                            <?php if ($order['status'] === 'delivered'): ?>
                                <button onclick="alert('Reorder feature coming soon')">Reorder</button>
                            <?php endif; ?>
                            <a href="order-details.php?id=<?= $order['order_id'] ?>">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    function filterOrders(status) {
        const url = new URL(window.location.href);
        url.searchParams.set('status', status);
        window.location.href = url.toString();
    }

    function sortOrders(order) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', order);
        window.location.href = url.toString();
    }
</script>
