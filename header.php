  <header class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">🛒</div>
                <span>Carrefour</span>
            </div>
            
            <div class="search-bar">
                <input type="text" placeholder="Search for products">
            </div>
            
            <div class="header-right">
                
                
                <a href="#" class="login-btn">Login & Register</a>
                
                <div class="flag"></div>
                
                <div class="cart-btn">🛒</div>
            </div>
        </div>
    </header>

    <!-- Navigation --><?php
     include 'config.php'; // Include database connection
$category_stmt = $pdo->query("SELECT category_id, name FROM categories ORDER BY name ASC");
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

  <nav class="navigation">
    <div class="nav-content">
        <a href="#" class="nav-item categories">📱 All Categories</a>
        <?php foreach ($categories as $cat): ?>
            <a href="category.php?id=<?php echo $cat['category_id']; ?>" class="nav-item">
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
