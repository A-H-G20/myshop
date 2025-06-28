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
$selected_size = isset($_POST['selected_size']) ? trim($_POST['selected_size']) : '';
$selected_color = isset($_POST['selected_color']) ? trim($_POST['selected_color']) : '';

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
    $stmt = $pdo->prepare("SELECT stock_quantity, name, sizes, colors FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    if ($product['stock_quantity'] < $quantity) {
        throw new Exception('Insufficient stock available');
    }
    
    // Validate selected size if product has sizes
    if (!empty($product['sizes'])) {
        $available_sizes = array_map('trim', explode(',', $product['sizes']));
        if (empty($selected_size)) {
            throw new Exception('Please select a size');
        }
        if (!in_array($selected_size, $available_sizes)) {
            throw new Exception('Invalid size selected');
        }
    }
    
    // Validate selected color if product has colors
    if (!empty($product['colors'])) {
        $available_colors = array_map('trim', explode(',', $product['colors']));
        if (empty($selected_color)) {
            throw new Exception('Please select a color');
        }
        if (!in_array($selected_color, $available_colors)) {
            throw new Exception('Invalid color selected');
        }
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
    
    // Check if product with same attributes already exists in cart
    $existing_item_query = "SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?";
    $existing_item_params = [$cart_id, $product_id];
    
    // Add size and color conditions if they exist
    if (!empty($selected_size)) {
        $existing_item_query .= " AND selected_size = ?";
        $existing_item_params[] = $selected_size;
    } else {
        $existing_item_query .= " AND (selected_size IS NULL OR selected_size = '')";
    }
    
    if (!empty($selected_color)) {
        $existing_item_query .= " AND selected_color = ?";
        $existing_item_params[] = $selected_color;
    } else {
        $existing_item_query .= " AND (selected_color IS NULL OR selected_color = '')";
    }
    
    $stmt = $pdo->prepare($existing_item_query);
    $stmt->execute($existing_item_params);
    $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_item) {
        // Update existing cart item
        $new_quantity = $existing_item['quantity'] + $quantity;
        
        // Check if new quantity exceeds stock
        if ($new_quantity > $product['stock_quantity']) {
            throw new Exception('Not enough stock for requested quantity');
        }
        
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE cart_item_id = ?");
        $stmt->execute([$new_quantity, $existing_item['cart_item_id']]);
        
        $message = 'Product quantity updated in cart';
    } else {
        // Add new cart item with size and color
        $stmt = $pdo->prepare("
            INSERT INTO cart_items (cart_id, product_id, quantity, selected_size, selected_color, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $cart_id, 
            $product_id, 
            $quantity, 
            !empty($selected_size) ? $selected_size : null,
            !empty($selected_color) ? $selected_color : null
        ]);
        
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
    
    // Build response message with attributes
    $attribute_info = [];
    if (!empty($selected_size)) {
        $attribute_info[] = "Size: " . $selected_size;
    }
    if (!empty($selected_color)) {
        $attribute_info[] = "Color: " . $selected_color;
    }
    
    $full_message = $message;
    if (!empty($attribute_info)) {
        $full_message .= " (" . implode(', ', $attribute_info) . ")";
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $full_message,
        'cart_count' => $cart_info['total_items'],
        'product_name' => $product['name'],
        'selected_size' => $selected_size,
        'selected_color' => $selected_color
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