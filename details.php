<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Carrefour Lebanon</title>
    <link rel="stylesheet" href="css/index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .product-details {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
        }
        
        .product-images {
            display: flex;
            flex-direction: column;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
            cursor: zoom-in;
            transition: transform 0.2s ease;
            border: 1px solid #ddd;
        }
        
        .main-image:hover {
            transform: scale(1.02);
        }
        
        .main-image-fallback {
            width: 100%;
            height: 400px;
            background: #f0f0f0;
            display: none;
            align-items: center;
            justify-content: center;
            color: #666;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 18px;
        }
        
        .thumbnail-images {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 5px;
        }
        
        .thumbnail-images::-webkit-scrollbar {
            height: 6px;
        }
        
        .thumbnail-images::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .thumbnail-images::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            opacity: 0.7;
            flex-shrink: 0;
        }
        
        .thumbnail:hover {
            border-color: #3498db;
            opacity: 1;
            transform: scale(1.05);
        }
        
        .thumbnail.active {
            border-color: #3498db;
            opacity: 1;
        }
        
        .thumbnail.error {
            opacity: 0.3;
            cursor: not-allowed;
        }
        
        .product-info h1 {
            font-size: 2em;
            margin-bottom: 15px;
            color: #333;
            line-height: 1.2;
        }
        
        .product-price {
            font-size: 1.8em;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 15px;
        }
        
        .product-description {
            font-size: 1.1em;
            line-height: 1.6;
            color: #666;
            margin-bottom: 20px;
        }
        
        .product-meta {
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .meta-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .meta-item:last-child {
            border-bottom: none;
        }
        
        .stock-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        
        .stock-available {
            background: #d4edda;
            color: #155724;
        }
        
        .stock-low {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-out {
            background: #f8d7da;
            color: #721c24;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .quantity-btn {
            background: #f8f9fa;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s;
            min-width: 45px;
        }
        
        .quantity-btn:hover {
            background: #e9ecef;
        }
        
        .quantity-btn:disabled {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }
        
        .quantity-input {
            border: none;
            padding: 10px;
            width: 60px;
            text-align: center;
            font-size: 16px;
            background: white;
        }
        
        .add-to-cart-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-weight: bold;
        }
        
        .add-to-cart-btn:hover:not(:disabled) {
            background: #2980b9;
            transform: translateY(-1px);
        }
        
        .add-to-cart-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
        }
        
        .back-btn {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }
        
        .back-btn:hover {
            background: #7f8c8d;
        }
        
        /* Image Zoom Overlay */
        .zoom-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            cursor: zoom-out;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .zoom-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .zoom-overlay img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            transform: scale(0.8);
            transition: transform 0.3s ease;
        }
        
        .zoom-overlay.active img {
            transform: scale(1);
        }
        
        .zoom-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 30px;
            cursor: pointer;
            z-index: 10000;
        }
        
        /* Message Popup */
        .message-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { 
                transform: translateX(100%); 
                opacity: 0; 
            }
            to { 
                transform: translateX(0); 
                opacity: 1; 
            }
        }
        
        @keyframes slideOut {
            from { 
                transform: translateX(0); 
                opacity: 1; 
            }
            to { 
                transform: translateX(100%); 
                opacity: 0; 
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .product-details {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 15px;
            }
            
            .main-image {
                height: 300px;
            }
            
            .thumbnail {
                width: 60px;
                height: 60px;
            }
            
            .product-info h1 {
                font-size: 1.5em;
            }
            
            .product-price {
                font-size: 1.5em;
            }
            
            .quantity-selector {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .product-details {
                padding: 10px;
            }
            
            .main-image {
                height: 250px;
            }
            
            .thumbnail {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>
   <?php 
include 'header.php';
include 'config.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    echo "<div style='text-align: center; padding: 50px;'>Invalid product ID</div>";
    exit;
}

try {
    // Join products with categories
    $stmt = $pdo->prepare("
        SELECT products.*, categories.name AS category_name 
        FROM products 
        LEFT JOIN categories ON products.category_id = categories.category_id 
        WHERE products.product_id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "<div style='text-align: center; padding: 50px;'>Product not found</div>";
        exit;
    }

    // Parse and fix image paths
    $images = [];
    if (!empty($product['images'])) {
        $decoded_images = json_decode($product['images'], true);
        $raw_images = is_array($decoded_images) ? $decoded_images : [$product['images']];
        $images = array_map(function($image) {
            $filename = basename($image); // Just get file name
            return 'uploads/' . rawurlencode($filename);
        }, $raw_images);
    }

    // Determine stock status
    $stock_status = '';
    $stock_class = '';
    if ($product['stock_quantity'] <= 0) {
        $stock_status = 'Out of Stock';
        $stock_class = 'stock-out';
    } elseif ($product['stock_quantity'] <= 5) {
        $stock_status = 'Low Stock (' . $product['stock_quantity'] . ' left)';
        $stock_class = 'stock-low';
    } else {
        $stock_status = $product['stock_quantity'] . ' available';
        $stock_class = 'stock-available';
    }

} catch (PDOException $e) {
    echo "<div style='text-align: center; padding: 50px;'>Error loading product</div>";
    exit;
}
?>


    <div class="container">

        
        <div class="product-details">
            <div class="product-images">
                <?php if (!empty($images)): ?>
                    <img src="<?php echo htmlspecialchars($images[0]); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="main-image" 
                         id="mainImage"
                         onclick="openImageZoom(this.src)"
                         onerror="this.style.display='none'; document.querySelector('.main-image-fallback').style.display='flex';">
                    
                    <div class="main-image-fallback">
                        <div>
                            <i>📷</i><br>
                            Image Not Available
                        </div>
                    </div>
                    
                    <?php if (count($images) > 1): ?>
                        <div class="thumbnail-images">
                            <?php foreach($images as $index => $image): ?>
                                <img src="<?php echo htmlspecialchars($image); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?> - View <?php echo $index + 1; ?>" 
                                     class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                     onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)"
                                     onerror="this.classList.add('error'); this.onclick=null; this.style.opacity='0.3';">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="main-image-fallback" style="display: flex;">
                        <div>
                            <i>📷</i><br>
                            No Image Available
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-price">
                    <?php echo number_format($product['price'], 0, '.', ','); ?> LBP
                </div>
                
                <?php if (!empty($product['description'])): ?>
                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                <?php endif; ?>
                
                <div class="product-meta">
                    <div class="meta-item">
                        <span><strong>Stock Status:</strong></span>
                        <span class="stock-status <?php echo $stock_class; ?>">
                            <?php echo $stock_status; ?>
                        </span>
                    </div>
                    
                   <div class="meta-item">
    <span><strong>Category:</strong></span>
    <span><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
</div>

                </div>
                
                <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="quantity-selector">
                        <label for="quantity"><strong>Quantity:</strong></label>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                        </div>
                    </div>
                <?php endif; ?>
                
                <button class="add-to-cart-btn" 
                        onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                        <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                    <?php echo $product['stock_quantity'] <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Image Zoom Overlay -->
    <div class="zoom-overlay" id="zoomOverlay" onclick="closeImageZoom()">
        <span class="zoom-close" onclick="closeImageZoom()">&times;</span>
        <img id="zoomImage" src="" alt="">
    </div>

    <script>
        // Image handling functions
        function changeMainImage(src, thumbnail) {
            const mainImage = document.getElementById('mainImage');
            const fallback = document.querySelector('.main-image-fallback');
            
            if (mainImage) {
                mainImage.src = src;
                mainImage.style.display = 'block';
                if (fallback) {
                    fallback.style.display = 'none';
                }
            }
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            if (thumbnail && !thumbnail.classList.contains('error')) {
                thumbnail.classList.add('active');
            }
        }
        
        function openImageZoom(src) {
            const overlay = document.getElementById('zoomOverlay');
            const zoomImage = document.getElementById('zoomImage');
            
            zoomImage.src = src;
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeImageZoom() {
            const overlay = document.getElementById('zoomOverlay');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // Quantity control functions
        function changeQuantity(delta) {
            const quantityInput = document.getElementById('quantity');
            if (!quantityInput) return;
            
            const currentValue = parseInt(quantityInput.value);
            const maxStock = parseInt(quantityInput.max);
            const newValue = currentValue + delta;
            
            if (newValue >= 1 && newValue <= maxStock) {
                quantityInput.value = newValue;
            }
            
            // Update button states
            const minusBtn = document.querySelector('.quantity-btn[onclick="changeQuantity(-1)"]');
            const plusBtn = document.querySelector('.quantity-btn[onclick="changeQuantity(1)"]');
            
            if (minusBtn) minusBtn.disabled = (newValue <= 1);
            if (plusBtn) plusBtn.disabled = (newValue >= maxStock);
        }
        
        // Add to cart function
        function addToCart(productId) {
            const btn = event.target;
            const userId = <?php echo $user_id; ?>;
            const quantityInput = document.getElementById('quantity');
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
            
            // Validate user login
            if (userId === 0) {
                showMessage('Please login to add items to cart', 'error');
                return;
            }
            
            // Show loading state
            const originalText = btn.innerHTML;
            const originalBg = btn.style.background;
            
            btn.style.background = '#f39c12';
            btn.innerHTML = 'Adding to Cart...';
            btn.disabled = true;
            
            $.ajax({
                url: 'add_to_cart.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    user_id: userId,
                    quantity: quantity
                },
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    if(response.success) {
                        // Success state
                        btn.style.background = '#27ae60';
                        btn.innerHTML = 'Added to Cart ✓';
                        
                        showMessage(response.message || 'Product added to cart successfully!', 'success');
                        
                        // Reset button after 2 seconds
                        setTimeout(() => {
                            btn.style.background = originalBg || '#3498db';
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }, 2000);
                    } else {
                        handleCartError(btn, originalBg, originalText, response.message || 'Failed to add to cart');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Network error. Please try again.';
                    
                    if (status === 'timeout') {
                        errorMessage = 'Request timed out. Please try again.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Cart service not found.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please try again later.';
                    }
                    
                    handleCartError(btn, originalBg, originalText, errorMessage);
                }
            });
        }
        
        function handleCartError(btn, originalBg, originalText, message) {
            btn.style.background = '#e74c3c';
            btn.innerHTML = 'Error';
            
            showMessage(message, 'error');
            
            setTimeout(() => {
                btn.style.background = originalBg || '#3498db';
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }
        
        // Message display function
        function showMessage(message, type) {
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.message-popup');
            existingMessages.forEach(msg => {
                msg.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => msg.remove(), 300);
            });
            
            // Create new message
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message-popup';
            messageDiv.style.background = type === 'success' ? '#27ae60' : '#e74c3c';
            messageDiv.textContent = message;
            
            document.body.appendChild(messageDiv);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                if (document.body.contains(messageDiv)) {
                    messageDiv.style.animation = 'slideOut 0.3s ease-in';
                    setTimeout(() => messageDiv.remove(), 300);
                }
            }, 4000);
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Quantity input validation
            const quantityInput = document.getElementById('quantity');
            if (quantityInput) {
                quantityInput.addEventListener('input', function() {
                    const value = parseInt(this.value);
                    const max = parseInt(this.max);
                    
                    if (isNaN(value) || value < 1) {
                        this.value = 1;
                    } else if (value > max) {
                        this.value = max;
                    }
                    
                    // Update button states
                    const minusBtn = document.querySelector('.quantity-btn[onclick="changeQuantity(-1)"]');
                    const plusBtn = document.querySelector('.quantity-btn[onclick="changeQuantity(1)"]');
                    
                    if (minusBtn) minusBtn.disabled = (parseInt(this.value) <= 1);
                    if (plusBtn) plusBtn.disabled = (parseInt(this.value) >= max);
                });
                
                // Initial button state
                const minusBtn = document.querySelector('.quantity-btn[onclick="changeQuantity(-1)"]');
                if (minusBtn) minusBtn.disabled = true;
            }
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeImageZoom();
                }
            });
        });
    </script>
</body>
</html>