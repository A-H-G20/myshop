<?php
/*******************************
 *  cart.php – Carrefour LB     *
 *******************************/
include 'header.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$success_message = $error_message = '';

/* ---------- handle cart actions (POST via AJAX) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $cart_item_id = intval($_POST['cart_item_id'] ?? 0);

    try {
        switch ($_POST['action']) {

            case 'update_quantity':
                $quantity = max(0, intval($_POST['quantity'] ?? 0));
                if ($quantity > 0) {
                    $stmt = $pdo->prepare("
                        UPDATE cart_items ci
                        JOIN cart c ON c.cart_id = ci.cart_id
                        SET ci.quantity = ?
                        WHERE ci.cart_item_id = ? AND c.user_id = ?
                    ");
                    $stmt->execute([$quantity, $cart_item_id, $user_id]);
                    $success_message = 'Cart updated successfully';
                } else {
                    $stmt = $pdo->prepare("
                        DELETE ci FROM cart_items ci
                        JOIN cart c ON c.cart_id = ci.cart_id
                        WHERE ci.cart_item_id = ? AND c.user_id = ?
                    ");
                    $stmt->execute([$cart_item_id, $user_id]);
                    $success_message = 'Item removed from cart';
                }
                break;

            case 'remove_item':
                $stmt = $pdo->prepare("
                    DELETE ci FROM cart_items ci
                    JOIN cart c ON c.cart_id = ci.cart_id
                    WHERE ci.cart_item_id = ? AND c.user_id = ?
                ");
                $stmt->execute([$cart_item_id, $user_id]);
                $success_message = 'Item removed from cart';
                break;
        }

        echo json_encode(['success' => true, 'message' => $success_message]);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
}

/* ---------- fetch cart items for display ---------- */
$items = [];
$grand_total = 0;

$stmt = $pdo->prepare("
    SELECT ci.cart_item_id,
           ci.quantity,
           p.product_id,
           p.name,
           p.price,
           p.stock_quantity,
           p.images
    FROM cart_items ci
    JOIN cart c ON c.cart_id = ci.cart_id
    JOIN products p ON p.product_id = ci.product_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($items as $it) {
    $grand_total += $it['price'] * $it['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart – Carrefour Lebanon</title>
    <link rel="stylesheet" href="css/index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* ------- cart layout ------- */
        .cart-container   { max-width:1200px;margin:40px auto;padding:20px; }
        .cart-header      { font-size:2em;margin-bottom:20px;color:#333;text-align:center; }
        .cart-table       { width:100%;border-collapse:collapse;margin-bottom:30px; }
        .cart-table th,
        .cart-table td    { padding:15px;text-align:center; border-bottom:1px solid #eee; }
        .cart-table th    { background:#f8f9fa;font-weight:600; }
        .item-img         { width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #ddd; }
        .qty-controls     { display:inline-flex;border:1px solid #ddd;border-radius:5px;overflow:hidden; }
        .qty-btn          { padding:8px 14px;font-size:16px;background:#f8f9fa;border:none;cursor:pointer; }
        .qty-btn:hover    { background:#e9ecef; }
        .qty-btn:disabled { opacity:.4;cursor:not-allowed; }
        .qty-input        { width:55px;text-align:center;border:none;padding:8px;background:white;font-size:15px; }
        .remove-btn       { color:#e74c3c;cursor:pointer;font-size:18px;transition:color .2s; }
        .remove-btn:hover { color:#c0392b; }
        .total-box        { text-align:right;font-size:1.5em;font-weight:bold;color:#27ae60; }
        .empty-cart       { text-align:center;font-size:1.3em;padding:60px 20px;color:#666; }
        .checkout-btn     { background:#27ae60;color:#fff;border:none;padding:15px 40px;font-size:18px;border-radius:6px;cursor:pointer;display:block;margin:0 auto;transition:background .3s; }
        .checkout-btn:hover { background:#1f8b4e; }
        /* message popup reused */
        .message-popup    { position:fixed;top:20px;right:20px;padding:15px 20px;border-radius:5px;color:#fff;font-weight:bold;z-index:1000;animation:slideIn .3s ease-out; }
        @keyframes slideIn { from{transform:translateX(100%);opacity:0;} to{transform:translateX(0);opacity:1;} }
        @keyframes slideOut{ from{transform:translateX(0);opacity:1;}   to{transform:translateX(100%);opacity:0;} }
    </style>
</head>
<body>

<div class="cart-container">
    <div class="cart-header">🛒 Your Shopping Cart</div>

<?php if (!$items): ?>
    <div class="empty-cart">Your cart is empty.</div>
<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price (LBP)</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): 
            /* image thumbnail */
            $thumb = 'assets/placeholder.png';
            if (!empty($item['images'])) {
                $decoded = json_decode($item['images'], true);
                $first   = is_array($decoded) ? $decoded[0] : $item['images'];
                $thumb   = 'uploads/' . rawurlencode(basename($first));
            }
            $in_stock = $item['stock_quantity'] >= $item['quantity'];
        ?>
            <tr data-id="<?= $item['cart_item_id']; ?>">
                <td style="display:flex;align-items:center;gap:15px;">
                    <img src="<?= htmlspecialchars($thumb); ?>" alt="" class="item-img">
                    <span><?= htmlspecialchars($item['name']); ?></span>
                </td>
                <td><?= number_format($item['price'], 0, '.', ','); ?></td>
                <td>
                    <?php if ($in_stock): ?>
                    <div class="qty-controls">
                        <button class="qty-btn minus">-</button>
                        <input type="number" class="qty-input" value="<?= $item['quantity']; ?>" min="1" max="<?= $item['stock_quantity']; ?>">
                        <button class="qty-btn plus">+</button>
                    </div>
                    <?php else: ?>
                        <span style="color:#e74c3c;font-weight:bold;">Out of stock</span>
                    <?php endif; ?>
                </td>
                <td class="subtotal"><?= number_format($item['price'] * $item['quantity'], 0, '.', ','); ?></td>
             <td>
    <span class="remove-btn" data-id="<?= $item['cart_item_id'] ?>" title="Remove item">✕</span>
</td>

            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-box">
        Total: <span id="grandTotal"><?= number_format($grand_total, 0, '.', ','); ?></span> LBP
    </div>

    <button class="checkout-btn" <?= $grand_total == 0 ? 'disabled' : ''; ?> onclick="location.href='checkout.php'">
        Proceed to Checkout
    </button>
<?php endif; ?>
</div>

<script>
/* ---------- helpers ---------- */
function showMessage(msg, type) {
    const popup = $('<div>', {class:'message-popup'}).text(msg)
          .css('background', type === 'success' ? '#27ae60' : '#e74c3c')
          .appendTo('body');
    setTimeout(() => {
        popup.css('animation','slideOut .3s ease-in');
        setTimeout(()=>popup.remove(),300);
    }, 3000);
}
function formatNumber(num){ return num.toLocaleString('en-US'); }

/* ---------- quantity +/- ---------- */
$('.qty-controls .minus, .qty-controls .plus').on('click', function () {
    const row   = $(this).closest('tr');
    const input = row.find('.qty-input');
    const max   = parseInt(input.attr('max'));
    let val     = parseInt(input.val()) || 1;
    val += $(this).hasClass('plus') ? 1 : -1;
    if (val < 1 || val > max) return;
    input.val(val).trigger('change');
});

$('.qty-input').on('change', function () {
    const input = $(this);
    const row   = input.closest('tr');
    const cartId= row.data('id');
    const qty   = parseInt(input.val()) || 1;
    const price = parseInt(row.find('td:eq(1)').text().replace(/,/g,''));

    /* optimistic UI */
    row.find('.subtotal').text(formatNumber(price * qty));
    recalcTotal();

    $.post('cart.php', {
        action: 'update_quantity',
        cart_item_id: cartId,
        quantity: qty
    }, resp => {
        const r = JSON.parse(resp);
        showMessage(r.message || 'Updated', r.success ? 'success' : 'error');
        if (!r.success) location.reload();
    });
});

/* ---------- remove item ---------- */
$('.remove-btn').on('click', function () {
    const row    = $(this).closest('tr');
    const cartId = row.data('id');
    $.post('cart.php', { action:'remove_item', cart_item_id:cartId }, resp => {
        const r = JSON.parse(resp);
        if (r.success) {
            row.remove();
            recalcTotal();
            showMessage(r.message,'success');
            if ($('tbody tr').length===0) location.reload(); // show empty state
        } else {
            showMessage('Could not remove item','error');
        }
    });
});

/* ---------- recalc grand total ---------- */
function recalcTotal() {
    let total = 0;
    $('tbody tr').each(function () {
        const sub = parseInt($(this).find('.subtotal').text().replace(/,/g,''));
        total += sub;
    });
    $('#grandTotal').text(formatNumber(total));
    $('.checkout-btn').prop('disabled', total === 0);
}
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('.remove-btn').click(function () {
        if (!confirm("Are you sure you want to remove this item from your cart?")) return;

        const cart_item_id = $(this).data('id');

        $.post('cart.php', {
            action: 'remove_item',
            cart_item_id: cart_item_id
        }, function (response) {
            const res = JSON.parse(response);
            if (res.success) {
                location.reload(); // ✅ This triggers auto-refresh
            } else {
                alert("Failed to remove item.");
            }
        });
    });
});
</script>
<?php include 'footer.php'; ?>
</body>
</html>
