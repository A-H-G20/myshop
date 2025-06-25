<?php
include 'header.php';
include 'config.php';

$query = trim($_GET['query'] ?? '');

if (empty($query)) {
    echo "<div style='text-align: center; padding: 50px;'>Please enter a search term.</div>";
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE name LIKE ?
        ORDER BY name ASC
    ");
    $stmt->execute(['%' . $query . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div style='text-align: center; padding: 50px;'>Error searching products.</div>";
    exit;
}
?>
<link rel="stylesheet" href="css/category.css">
<br><br>
<div class="products-container">
    <h2 style="text-align:center;">Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>

    <?php if (empty($results)): ?>
        <div style="text-align:center; padding:30px;">No products found.</div>
    <?php else: ?>
        <div class="products-grid">
            <?php
             foreach ($results as $product): 
                $image = 'uploads/' . basename($product['images']);
            ?>
                <div class="product-card" onclick="window.location.href='details.php?id=<?php echo $product['product_id']; ?>'">
                    <img src="<?php echo htmlspecialchars($image); ?>" class="product-image"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="product-image-fallback"><div>📷<br>No Image</div></div>

                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-price"><?php echo number_format($product['price'], 0, '.', ','); ?> $</div>
                        <div class="product-description"><?php echo htmlspecialchars($product['description']); ?></div>
                        <a href="details.php?id=<?php echo $product['product_id']; ?>" class="btn-view">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
include 'footer.php';?>