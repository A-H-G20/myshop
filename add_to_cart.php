<?php
session_start();
header('Content-Type: application/json');

// Include database connection
include 'config.php'; // Assuming you have a database connection file

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Validate input
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if product exists and has sufficient stock
    $stmt = $pdo->prepare("SELECT stock_quantity, name FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    if ($product['stock_quantity'] < $quantity) {
        throw new Exception('Insufficient stock available');
    }
    
    // Find or create cart for user
    $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart) {
        // Create new cart
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, created_at) VALUES (?, NOW())");
        $stmt->execute([$user_id]);
        $cart_id = $pdo->lastInsertId();
    } else {
        $cart_id = $cart['cart_id'];
    }
    
    // Check if product already exists in cart
    $stmt = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_item) {
        // Update existing cart item
        $new_quantity = $existing_item['quantity'] + $quantity;
        
        // Check if new quantity exceeds stock
        if ($new_quantity > $product['stock_quantity']) {
            throw new Exception('Not enough stock for requested quantity');
        }
        
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
        $stmt->execute([$new_quantity, $existing_item['cart_item_id']]);
        
        $message = 'Product quantity updated in cart';
    } else {
        // Add new cart item
        $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$cart_id, $product_id, $quantity]);
        
        $message = 'Product added to cart';
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Get updated cart count
    $stmt = $pdo->prepare("
        SELECT COUNT(ci.cart_item_id) as item_count, 
               COALESCE(SUM(ci.quantity), 0) as total_items
        FROM cart c 
        LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'cart_count' => $cart_info['total_items'],
        'product_name' => $product['name']
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollback();
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>