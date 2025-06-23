<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrefour Lebanon</title>
    <link rel="stylesheet" href="css/index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php 
    include 'header.php';
    
    // Database connection
    include 'config.php'; // Assuming you have a database connection file
    
    // Check if user is logged in
    session_start();
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    // Fetch products from database
    try {
        $stmt = $pdo->prepare("SELECT product_id, name, description, price, stock_quantity, category_id, images FROM products WHERE stock_quantity > 0 ORDER BY created_at DESC");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
        $products = [];
    }
    ?>

    <!-- Free Delivery Banner -->
    <div class="free-delivery">
        <div class="free-delivery-badge">FREE 🚚 DELIVERY</div>
        <div>Free delivery on orders over 4.5 million LBP on same day orders</div>
    </div>

  <div class="new-arrivals">
    <div class="products-grid" style="position: relative;">
        <?php foreach($products as $product): ?>
            <div class="product-card" data-product-id="<?php echo $product['product_id']; ?>">
                <button class="add-btn" onclick="addToCart(<?php echo $product['product_id']; ?>)">+</button>
                
                <div class="product-image" onclick="openProductDetails(<?php echo $product['product_id']; ?>)" style="cursor: pointer;">
                    <?php if(!empty($product['images'])): ?>
                        <?php 
                        // Handle both JSON array and single image string
                        $images = json_decode($product['images'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($images)) {
                            // It's a valid JSON array
                            $firstImage = $images[0];
                        } else {
                            // It's a single image string
                            $firstImage = $product['images'];
                        }
                        
                        // Convert relative path to web-accessible path
                        $imagePath = str_replace('../uploads/', 'uploads/', $firstImage);
                        // Or use absolute path from document root
                        // $imagePath = '/path/to/your/project/' . str_replace('../', '', $firstImage);
                        ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             style="width: 100%; height: 100%; object-fit: cover;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        
                        <!-- Fallback div in case image fails to load -->
                        <div style="background: #f0f0f0; height: 100%; display: none; align-items: center; justify-content: center; color: #666;">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </div>
                    <?php else: ?>
                        <div style="background: #f0f0f0; height: 100%; display: flex; align-items: center; justify-content: center; color: #666;">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-price" onclick="openProductDetails(<?php echo $product['product_id']; ?>)" style="cursor: pointer;">
                    <?php echo number_format($product['price'], 0, '.', ','); ?> <span style="font-size: 12px;">LBP</span>
                </div>
                
                <div class="product-name" onclick="openProductDetails(<?php echo $product['product_id']; ?>)" style="cursor: pointer;">
                    <?php echo htmlspecialchars($product['name']); ?>
                </div>
                
                <?php if(!empty($product['description'])): ?>
                    <div class="product-description" onclick="openProductDetails(<?php echo $product['product_id']; ?>)" 
                         style="font-size: 12px; color: #666; margin-top: 5px; cursor: pointer;">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : ''); ?>
                    </div>
                <?php endif; ?>
                
                <div class="stock-info" style="font-size: 11px; color: #888; margin-top: 5px;">
                    Stock: <?php echo $product['stock_quantity']; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($products)): ?>
            <div style="text-align: center; width: 100%; padding: 50px 0; color: #666;">
                No products available at the moment.
            </div>
        <?php endif; ?>
    </div>
</div>

    <script>
        // Simple carousel functionality
        document.addEventListener('DOMContentLoaded', function() {
            const leftArrow = document.querySelector('.nav-arrow.left');
            const rightArrow = document.querySelector('.nav-arrow.right');
            const productsGrid = document.querySelector('.products-grid');
            
            if(leftArrow && rightArrow) {
                leftArrow.addEventListener('click', function() {
                    productsGrid.scrollBy({ left: -220, behavior: 'smooth' });
                });
                
                rightArrow.addEventListener('click', function() {
                    productsGrid.scrollBy({ left: 220, behavior: 'smooth' });
                });
            }
        });

        // Open product details page
        function openProductDetails(productId) {
            window.location.href = 'details.php?id=' + productId;
        }

        // Add to cart functionality
        function addToCart(productId) {
            const btn = event.target;
            const userId = <?php echo $user_id; ?>;
            
            // Show loading state
            btn.style.background = '#f39c12';
            btn.innerHTML = '...';
            btn.disabled = true;
            
            $.ajax({
                url: 'add_to_cart.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    user_id: userId,
                    quantity: 1
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        // Success state
                        btn.style.background = '#27ae60';
                        btn.innerHTML = '✓';
                        
                        // Show success message
                        showMessage('Product added to cart!', 'success');
                        
                        // Reset button after 1.5 seconds
                        setTimeout(() => {
                            btn.style.background = '#3498db';
                            btn.innerHTML = '+';
                            btn.disabled = false;
                        }, 1500);
                    } else {
                        // Error state
                        btn.style.background = '#e74c3c';
                        btn.innerHTML = '✗';
                        
                        showMessage(response.message || 'Failed to add to cart', 'error');
                        
                        // Reset button after 1.5 seconds
                        setTimeout(() => {
                            btn.style.background = '#3498db';
                            btn.innerHTML = '+';
                            btn.disabled = false;
                        }, 1500);
                    }
                },
                error: function() {
                    // Error state
                    btn.style.background = '#e74c3c';
                    btn.innerHTML = '✗';
                    
                    showMessage('Network error. Please try again.', 'error');
                    
                    // Reset button after 1.5 seconds
                    setTimeout(() => {
                        btn.style.background = '#3498db';
                        btn.innerHTML = '+';
                        btn.disabled = false;
                    }, 1500);
                }
            });
        }

        // Show message function
        function showMessage(message, type) {
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.message-popup');
            existingMessages.forEach(msg => msg.remove());
            
            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message-popup';
            messageDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 5px;
                color: white;
                font-weight: bold;
                z-index: 1000;
                animation: slideIn 0.3s ease-out;
                background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
            `;
            messageDiv.textContent = message;
            
            // Add animation styles
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
            
            document.body.appendChild(messageDiv);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                messageDiv.remove();
            }, 3000);
        }
    </script>
</body>
</html>