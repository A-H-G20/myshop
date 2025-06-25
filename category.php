    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category - Carrefour Lebanon</title>
    <link rel="stylesheet" href="css/index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/category.css">
</head>
<body>
    <?php 
    include 'header.php';
    include 'config.php';
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    // Get category ID from URL
    $category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($category_id <= 0) {
        echo "<div style='text-align: center; padding: 50px;'>Invalid category ID</div>";
        exit;
    }
    
    try {
        // Get category information
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            echo "<div style='text-align: center; padding: 50px;'>Category not found</div>";
            exit;
        }
        
        // Get sort parameter
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
        
        // Build sort clause
        $sort_clause = '';
        switch($sort) {
            case 'name_asc':
                $sort_clause = 'ORDER BY products.name ASC';
                break;
            case 'name_desc':
                $sort_clause = 'ORDER BY products.name DESC';
                break;
            case 'price_asc':
                $sort_clause = 'ORDER BY products.price ASC';
                break;
            case 'price_desc':
                $sort_clause = 'ORDER BY products.price DESC';
                break;
            case 'stock_asc':
                $sort_clause = 'ORDER BY products.stock_quantity ASC';
                break;
            case 'stock_desc':
                $sort_clause = 'ORDER BY products.stock_quantity DESC';
                break;
            default:
                $sort_clause = 'ORDER BY products.name ASC';
        }
        
        // Get products in this category
        $stmt = $pdo->prepare("
            SELECT products.*, categories.name AS category_name 
            FROM products 
            LEFT JOIN categories ON products.category_id = categories.category_id 
            WHERE products.category_id = ? 
            {$sort_clause}
        ");
        $stmt->execute([$category_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process products images
        foreach ($products as &$product) {
            $images = [];
            if (!empty($product['images'])) {
                $decoded_images = json_decode($product['images'], true);
                $raw_images = is_array($decoded_images) ? $decoded_images : [$product['images']];
                $images = array_map(function($image) {
                    $filename = basename($image);
                    return 'uploads/' . rawurlencode($filename);
                }, $raw_images);
            }
            $product['processed_images'] = $images;
            
            // Determine stock status
            if ($product['stock_quantity'] <= 0) {
                $product['stock_status'] = 'Out of Stock';
                $product['stock_class'] = 'stock-out';
            } elseif ($product['stock_quantity'] <= 5) {
                $product['stock_status'] = 'Low Stock';
                $product['stock_class'] = 'stock-low';
            } else {
                $product['stock_status'] = 'In Stock';
                $product['stock_class'] = 'stock-available';
            }
        }
        
    } catch (PDOException $e) {
        echo "<div style='text-align: center; padding: 50px;'>Error loading category products</div>";
        exit;
    }
    ?>

    <div class="category-header">
        <div class="container">
            <h1><?php echo htmlspecialchars($category['name']); ?></h1>
            <?php if (!empty($category['description'])): ?>
                <div class="category-description">
                    <?php echo htmlspecialchars($category['description']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="products-container">
        <a href="index.php" class="back-btn">← Back to Home</a>
        
        <div class="products-header">
            <div class="products-count">
                <?php echo count($products); ?> product<?php echo count($products) !== 1 ? 's' : ''; ?> found
            </div>
            
            <div class="sort-filter">
                <label for="sort">Sort by:</label>
                <select id="sort" class="sort-select" onchange="changeSortOrder()">
                    <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low-High)</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High-Low)</option>
                    <option value="stock_asc" <?php echo $sort === 'stock_asc' ? 'selected' : ''; ?>>Stock (Low-High)</option>
                    <option value="stock_desc" <?php echo $sort === 'stock_desc' ? 'selected' : ''; ?>>Stock (High-Low)</option>
                </select>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="no-products">
                <h3>No Products Found</h3>
                <p>There are currently no products available in this category.</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" onclick="window.location.href='details    .php?id=<?php echo $product['product_id']; ?>'">
                        <?php if (!empty($product['processed_images'])): ?>
                            <img src="<?php echo htmlspecialchars($product['processed_images'][0]); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="product-image-fallback">
                                <div>📷<br>No Image</div>
                            </div>
                        <?php else: ?>
                            <div class="product-image-fallback" style="display: flex;">
                                <div>📷<br>No Image</div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            
                            <div class="product-price">
                                <?php echo number_format($product['price'], 0, '.', ','); ?> LBP
                            </div>
                            
                            <?php if (!empty($product['description'])): ?>
                                <div class="product-description">
                                    <?php echo htmlspecialchars($product['description']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-stock">
                                <span class="stock-badge <?php echo $product['stock_class']; ?>">
                                    <?php echo $product['stock_status']; ?>
                                </span>
                                <?php if ($product['stock_quantity'] > 0 && $product['stock_quantity'] <= 10): ?>
                                    <span style="font-size: 0.8em; color: #666;">
                                        <?php echo $product['stock_quantity']; ?> left
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-actions" onclick="event.stopPropagation();">
                                <a href="details.php?id=<?php echo $product['product_id']; ?>" class="btn-view">
                                    View Details
                                </a>
                                
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Sort change function
        function changeSortOrder() {
            const sortSelect = document.getElementById('sort');
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('sort', sortSelect.value);
            window.location.href = currentUrl.toString();
        }
        
        // Add to cart function
        function addToCart(productId) {
            const btn = event.target;
            const userId = <?php echo $user_id; ?>;
            
            // Validate user login
            if (userId === 0) {
                showMessage('Please login to add items to cart', 'error');
                return;
            }
            
            // Show loading state
            const originalText = btn.innerHTML;
            const originalBg = btn.style.background;
            
            btn.style.background = '#f39c12';
            btn.innerHTML = '<div class="loading"></div>';
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
                timeout: 10000,
                success: function(response) {
                    if(response.success) {
                        // Success state
                        btn.style.background = '#27ae60';
                        btn.innerHTML = '✓';
                        
                        showMessage(response.message || 'Product added to cart successfully!', 'success');
                        
                        // Reset button after 2 seconds
                        setTimeout(() => {
                            btn.style.background = originalBg || '#27ae60';
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
            btn.innerHTML = '✗';
            
            showMessage(message, 'error');
            
            setTimeout(() => {
                btn.style.background = originalBg || '#27ae60';
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
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scrolling for back button
            const backBtn = document.querySelector('.back-btn');
            if (backBtn) {
                backBtn.addEventListener('click', function(e) {
                    // Add a subtle transition effect
                    document.body.style.opacity = '0.8';
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 150);
                });
            }
        });
    </script>
</body>
</html>