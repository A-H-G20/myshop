  <?php
session_start();
?>
<style>
    .profile-btn {
    cursor: pointer;
    font-size: 22px;
    margin-left: 10px;
    display: inline-block;
    
}

</style>
  <header class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">🛒</div>
                <span>My shop</span>
            </div>
            
           <div class="search-bar">
    <form action="searchresult.php" method="GET">
        <input type="text" name="query" placeholder="Search for products" required>
        
    </form>
</div>

            
            <div class="header-right">
                
                
           <?php if (isset($_SESSION['user_id'])): ?>
    <a href="logout.php" class="login-btn">Logout</a>
<?php else: ?>
    <a href="login.php" class="login-btn">Login & Register</a>
<?php endif; ?>

                
             
                
            <?php if (isset($_SESSION['user_id'])): ?>
    <div class="cart-btn" onclick="window.location.href='cart.php'">🛒</div>
<?php endif; ?>
<?php if (isset($_SESSION['user_id'])): ?>
    <div class="profile-btn" onclick="window.location.href='profile.php'">👤</div>
<?php endif; ?>


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
        <a href="index.php" class="nav-item categories">Home</a>
        <?php foreach ($categories as $cat): ?>
            <a href="category.php?id=<?php echo $cat['category_id']; ?>" class="nav-item">
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>
        <?php endforeach; ?>
         <?php if (isset($_SESSION['user_id'])): ?>
    <a href="orders.php" class="nav-item">My Order</a>
<?php endif; ?>

        <a href="about.php" class="nav-item ">About Us</a>
    </div>
</nav>
