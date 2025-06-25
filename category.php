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

$user_id = $_SESSION['user_id'] ?? 0;
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($category_id <= 0) {
    echo "<div style='text-align: center; padding: 50px;'>Invalid category ID</div>";
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        echo "<div style='text-align: center; padding: 50px;'>Category not found</div>";
        exit;
    }

    $sort = $_GET['sort'] ?? 'name_asc';
    $sort_clause = match($sort) {
        'name_desc' => 'ORDER BY products.name DESC',
        'price_asc' => 'ORDER BY products.price ASC',
        'price_desc' => 'ORDER BY products.price DESC',
        'stock_asc' => 'ORDER BY products.stock_quantity ASC',
        'stock_desc' => 'ORDER BY products.stock_quantity DESC',
        default => 'ORDER BY products.name ASC',
    };

    $stmt = $pdo->prepare("
        SELECT products.*, categories.name AS category_name 
        FROM products 
        LEFT JOIN categories ON products.category_id = categories.category_id 
        WHERE products.category_id = ? 
        {$sort_clause}
    ");
    $stmt->execute([$category_id]);
    $fetchedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clean processing (no reference)
    $products = [];
    foreach ($fetchedProducts as $product) {
        // Clean image path
        $product['clean_image'] = '';
        if (!empty($product['images'])) {
            $filename = basename($product['images']);
            $product['clean_image'] = 'uploads/' . $filename;
        }

        // Stock status
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

        $products[] = $product;
    }

} catch (PDOException $e) {
    echo "<div style='text-align: center; padding: 50px;'>Error loading category products</div>";
    exit;
}
?>

<div class="category-header">
    <div class="container">
        <h1><?php echo htmlspecialchars($category['name']); ?></h1>

        <?php if (!empty($category['image'])): 
            $imagePath = 'category/' . basename($category['image']);
        ?>
            <div class="category-image" style="margin: 20px 0;">
                <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                     alt="<?php echo htmlspecialchars($category['name']); ?>" 
                     style="max-width: 100%; height: auto; border-radius: 10px;">
            </div>
        <?php endif; ?>

        <?php if (!empty($category['description'])): ?>
            <div class="category-description">
                <?php echo htmlspecialchars($category['description']); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="products-container">
  

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
                <div class="product-card" onclick="window.location.href='details.php?id=<?php echo $product['product_id']; ?>'">
                    <?php if (!empty($product['clean_image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['clean_image']); ?>" 
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
<?php include 'footer.php'; ?>
<script>
function changeSortOrder() {
    const sortSelect = document.getElementById('sort');
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('sort', sortSelect.value);
    window.location.href = currentUrl.toString();
}
</script>
</body>
</html>
