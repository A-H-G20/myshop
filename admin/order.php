<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management – MyShop Admin</title>
    <link rel="stylesheet" href="css/order.css">
</head>

<body>
  <?php include 'navbar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Order Management</h1>
                <p>Manage and track all customer orders</p>
            </div>

            <div id="alert-container"></div>

            <?php
                include 'config.php';        // DB connection
            $orders_sql = "SELECT o.*, u.first_name, u.last_name
               FROM orders o
               JOIN users u ON o.user_id = u.id
               ORDER BY o.created_at DESC";

                $orders_result = mysqli_query($conn, $orders_sql);

                $orders = [];
                while ($order = mysqli_fetch_assoc($orders_result)) {
                    $order_id   = $order['order_id'];

                    $items_sql  = "SELECT oi.*,
                                          p.name   AS product_name,
                                          p.images AS product_image
                                   FROM   order_items oi
                                   JOIN   products p ON oi.product_id = p.product_id
                                   WHERE  order_id = $order_id";
                    $items_res  = mysqli_query($conn, $items_sql);

                    $items = [];
                    while ($item = mysqli_fetch_assoc($items_res)) {
                        $items[] = $item;
                    }
                    $order['items'] = $items;
                    $orders[]       = $order;
                }
            ?>

            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order&nbsp;ID</th>
                            <th>Customer</th>
                            <th>Total&nbsp;Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['order_id'] ?></td>
                                <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?= $order['status'] ?>
                                    </span>
                                </td>
                                <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <div class="actions">
                                        <button
                                            class="btn btn-primary btn-small"
                                            onclick='showDetails(<?= json_encode($order, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>
                                            View
                                        </button>

                                        <button
                                            class="btn btn-secondary btn-small"
                                            onclick="approveOrder(<?= $order['order_id'] ?>)">
                                            Approve
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetailsContent"></div>
        </div>
    </div>

    <script>
        /* ------------------ APPROVE ORDER ------------------ */
        function approveOrder(orderId) {
            if (!confirm("Are you sure you want to approve this order?")) return;

            fetch('approve_order.php', {
                method : 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body   : `order_id=${orderId}`
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            })
            .catch(err => {
                console.error(err);
                alert("An error occurred while approving the order.");
            });
        }

        /* ------------------ SHOW DETAILS ------------------- */
        function showDetails(order) {
            const modal   = document.getElementById('orderModal');
            const content = document.getElementById('orderDetailsContent');

            let itemsHTML = '';
            order.items.forEach(item => {
                /* 1. Resolve image path exactly as stored (../uploads/…) */
                let imagePath = item.product_image
                              ? item.product_image.split(',')[0]  // first image if multiple
                              : '';

                if (!imagePath) imagePath = 'images/default.png';

                /* 2. Format size display - with debugging */
                let sizeDisplay = '';
                if (item.sizes && item.sizes.trim() !== '') {
                    sizeDisplay = `<div style="color:#666;font-size:14px;">Size: ${item.sizes}</div>`;
                } else {
                    // Debug: show if sizes field exists but is empty
                    console.log('Sizes field for item:', item.product_name, 'Sizes value:', item.sizes);
                }

                itemsHTML += `
                    <div class="item-row"
                         style="display:flex;align-items:center;gap:15px;margin-bottom:10px;">
                        <img src="${imagePath}"
                             alt="Product"
                             style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #ccc;">
                        <div>
                            <div><strong>${item.product_name}</strong></div>
                            ${sizeDisplay}
                            <div>
                                Qty: ${item.quantity} × $${parseFloat(item.price_at_time).toFixed(2)}
                                = <strong>$${(item.quantity * item.price_at_time).toFixed(2)}</strong>
                            </div>
                        </div>
                    </div>`;
            });

            /* 3. Inject into modal */
            content.innerHTML = `
                <div class="order-details">
                    <div class="detail-section">
                        <h4>Order Information</h4>
                        <p><strong>Order&nbsp;ID:</strong> #${order.order_id}</p>
                        <p><strong>Customer:</strong> ${order.first_name} ${order.last_name}</p>
                        <p><strong>Total:</strong> $${parseFloat(order.total_amount).toFixed(2)}</p>
                        <p><strong>Status:</strong>
                           <span class="status-badge status-${order.status}">
                               ${order.status}
                           </span>
                        </p>
                    </div>
                </div>

                <div class="order-items">
                    <h4>Order&nbsp;Items</h4>
                    ${itemsHTML}
                </div>`;

            modal.style.display = 'block';
        }

        /* -------------- Modal Close Behaviour --------------- */
        document.querySelector('.close').onclick = () =>
            document.getElementById('orderModal').style.display = 'none';

        window.onclick = e => {
            if (e.target === document.getElementById('orderModal'))
                document.getElementById('orderModal').style.display = 'none';
        };
    </script>
</body>
</html>