<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Carrefour Lebanon</title>
    <link rel="stylesheet" href="css/index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/details.css">
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