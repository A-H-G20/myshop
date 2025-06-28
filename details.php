<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Myshop</title>
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

    // Parse and fix image paths with maximum of 4 images
   // Parse comma-separated image paths
$images = [];
$default_image = 'uploads/default-product.jpg';

if (!empty($product['images'])) {
    $raw_images = explode(',', $product['images']); // Split by comma
    foreach ($raw_images as $img) {
        $img = trim($img); // Clean whitespace
        if (empty($img)) continue;

        // Normalize path
        if (strpos($img, 'uploads/') === false) {
            $img = 'uploads/' . basename($img);
        } else {
            $img = str_replace('../', '', $img); // remove ../ if exists
        }

        if (!in_array($img, $images)) {
            $images[] = $img;
        }
    }
}

// Fallback to default image
if (empty($images)) {
    $images = [$default_image];
}

    
    // Debug: Log the images array (remove this in production)
    error_log("Product ID: " . $product_id . " - Images found: " . count($images) . " - " . implode(', ', $images));

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

    // Parse sizes and colors if they exist
    $available_sizes = [];
    $available_colors = [];
    
    // Debug: Check if sizes and colors fields exist
    error_log("Sizes field exists: " . (array_key_exists('sizes', $product) ? 'YES' : 'NO'));
    error_log("Colors field exists: " . (array_key_exists('colors', $product) ? 'YES' : 'NO'));
    error_log("Sizes value: " . ($product['sizes'] ?? 'NULL'));
    error_log("Colors value: " . ($product['colors'] ?? 'NULL'));
    
    if (isset($product['sizes']) && !empty($product['sizes'])) {
        $sizes = explode(',', $product['sizes']);
        foreach ($sizes as $size) {
            $size = trim($size);
            if (!empty($size)) {
                $available_sizes[] = $size;
            }
        }
    }
    
    if (isset($product['colors']) && !empty($product['colors'])) {
        $colors = explode(',', $product['colors']);
        foreach ($colors as $color) {
            $color = trim($color);
            if (!empty($color)) {
                $available_colors[] = $color;
            }
        }
    }
    
    error_log("Available sizes: " . implode(', ', $available_sizes));
    error_log("Available colors: " . implode(', ', $available_colors));

} catch (PDOException $e) {
    echo "<div style='text-align: center; padding: 50px;'>Error loading product</div>";
    exit;
}
?>

    <div class="container">
        <div class="product-details">
            <div class="product-images">
                <!-- Main Image Display -->
                <img src="<?php echo htmlspecialchars($images[0]); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="main-image" 
                     id="mainImage"
                     onclick="openImageZoom(this.src)"
                     onerror="console.log('Main image failed:', this.src); this.style.display='none'; document.querySelector('.main-image-fallback').style.display='flex';"
                     onload="console.log('Main image loaded:', this.src);">
                
                <div class="main-image-fallback">
                    <div>
                        <i>📷</i><br>
                        Image Not Available
                    </div>
                </div>
                
              
                
                <!-- Thumbnail Images (show only if more than 1 image) -->
                <?php if (count($images) > 1): ?>
                    <div class="thumbnail-images">
                        <?php foreach($images as $index => $image): ?>
                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?> - View <?php echo $index + 1; ?>" 
                                 class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)"
                                 onerror="console.log('Thumbnail failed:', this.src); this.classList.add('error'); this.onclick=null; this.style.opacity='0.3';"
                                 onload="console.log('Thumbnail loaded:', this.src);">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-price">
                    <?php echo number_format($product['price'], 0, '.', ','); ?> $
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
                
             
                
                <!-- Size Selection -->
                <?php if (!empty($available_sizes)): ?>
                    <div class="product-attribute">
                        <label for="size-select"><strong>Size:</strong></label>
                        <div class="size-options">
                            <?php foreach ($available_sizes as $index => $size): ?>
                                <label class="size-option">
                                    <input type="radio" name="selected_size" value="<?php echo htmlspecialchars($size); ?>" 
                                           <?php echo $index === 0 ? 'checked' : ''; ?> 
                                           onchange="updateSelectedAttribute('size', this.value)">
                                    <span class="size-label"><?php echo htmlspecialchars($size); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Color Selection -->
                <?php if (!empty($available_colors)): ?>
                    <div class="product-attribute">
                        <label for="color-select"><strong>Color:</strong></label>
                        <div class="color-options">
                            <?php foreach ($available_colors as $index => $color): ?>
                                <label class="color-option">
                                    <input type="radio" name="selected_color" value="<?php echo htmlspecialchars($color); ?>" 
                                           <?php echo $index === 0 ? 'checked' : ''; ?> 
                                           onchange="updateSelectedAttribute('color', this.value)">
                                    <span class="color-label" style="background-color: <?php echo strtolower($color); ?>;" 
                                          title="<?php echo htmlspecialchars($color); ?>">
                                        <?php echo htmlspecialchars($color); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
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

<?php include 'footer.php'; ?>

    <script>
        // Store selected attributes
        let selectedAttributes = {
            size: '<?php echo !empty($available_sizes) ? htmlspecialchars($available_sizes[0]) : ""; ?>',
            color: '<?php echo !empty($available_colors) ? htmlspecialchars($available_colors[0]) : ""; ?>'
        };
        
        // Update selected attribute
        function updateSelectedAttribute(type, value) {
            selectedAttributes[type] = value;
            console.log('Selected ' + type + ':', value);
        }
        
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
            
            // Prepare cart data with selected attributes
            const cartData = {
                product_id: productId,
                user_id: userId,
                quantity: quantity
            };
            
            // Add selected attributes if they exist
            if (selectedAttributes.size) {
                cartData.selected_size = selectedAttributes.size;
            }
            if (selectedAttributes.color) {
                cartData.selected_color = selectedAttributes.color;
            }
            
            $.ajax({
                url: 'add_to_cart.php',
                method: 'POST',
                data: cartData,
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
            
            // Image error handling for better fallback
            const mainImage = document.getElementById('mainImage');
            if (mainImage) {
                mainImage.addEventListener('error', function() {
                    console.log('Main image failed to load, showing fallback');
                });
            }
        });
    </script>

    <style>
        /* Styles for size and color selection */
        .product-attribute {
            margin: 20px 0;
        }
        
        .product-attribute label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        /* Size Options */
        .size-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .size-option {
            position: relative;
            cursor: pointer;
        }
        
        .size-option input[type="radio"] {
            display: none;
        }
        
        .size-label {
            display: inline-block;
            padding: 8px 16px;
            border: 2px solid #ddd;
            border-radius: 4px;
            background: #fff;
            transition: all 0.3s ease;
            min-width: 40px;
            text-align: center;
            font-weight: 500;
        }
        
        .size-option input[type="radio"]:checked + .size-label {
            border-color: #3498db;
            background: #3498db;
            color: white;
        }
        
        .size-option:hover .size-label {
            border-color: #3498db;
            transform: translateY(-1px);
        }
        
        /* Color Options */
        .color-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .color-option {
            position: relative;
            cursor: pointer;
        }
        
        .color-option input[type="radio"] {
            display: none;
        }
        
        .color-label {
            display: inline-block;
            padding: 8px 16px;
            border: 2px solid #ddd;
            border-radius: 4px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            min-width: 60px;
            text-align: center;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .color-option input[type="radio"]:checked + .color-label {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.3);
        }
        
        .color-option:hover .color-label {
            border-color: #3498db;
            transform: translateY(-1px);
        }
        
        /* Special styling for color labels with actual colors */
        .color-label[style*="background-color"] {
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .size-options, .color-options {
                justify-content: flex-start;
            }
            
            .size-label, .color-label {
                font-size: 14px;
                padding: 6px 12px;
            }
        }
    </style>
</body>
</html>