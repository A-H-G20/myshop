<!DOCTYPE html>
<html lang="en">
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $uploadedImages = [];
                if (isset($_FILES['images'])) {
                    $uploadDir = '../uploads/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                        if ($_FILES['images']['error'][$key] == 0) {
                            $fileName = time() . '_' . basename($_FILES['images']['name'][$key]);
                            $targetPath = $uploadDir . $fileName;
                            if (move_uploaded_file($tmpName, $targetPath)) {
                                $uploadedImages[] = '../uploads/' . $fileName;
                            }
                        }
                    }
                }
                $images = implode(',', $uploadedImages);
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $price = (float)$_POST['price'];
                $stock = (int)$_POST['stock_quantity'];
                $category_id = (int)$_POST['category_id'];
                
                // Handle sizes - convert array to comma-separated string
                $sizes = '';
                if (isset($_POST['sizes']) && is_array($_POST['sizes'])) {
                    $sizesArray = array_filter($_POST['sizes'], function($size) {
                        return !empty(trim($size));
                    });
                    $sizes = implode(',', array_map('trim', $sizesArray));
                }

                $query = "INSERT INTO products (name, description, price, stock_quantity, category_id, images, sizes, created_at) 
                         VALUES ('$name', '$description', $price, $stock, $category_id, '$images', '$sizes', NOW())";
                mysqli_query($conn, $query);
                header("Location: products.php");
                exit;

            case 'edit':
                $product_id = (int)$_POST['product_id'];
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $price = (float)$_POST['price'];
                $stock = (int)$_POST['stock_quantity'];
                $category_id = (int)$_POST['category_id'];
                
                // Handle sizes - convert array to comma-separated string
                $sizes = '';
                if (isset($_POST['sizes']) && is_array($_POST['sizes'])) {
                    $sizesArray = array_filter($_POST['sizes'], function($size) {
                        return !empty(trim($size));
                    });
                    $sizes = implode(',', array_map('trim', $sizesArray));
                }

                $uploadedImages = [];
                if (isset($_FILES['images']) && $_FILES['images']['tmp_name'][0] != '') {
                    $uploadDir = '../uploads/';
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                        if ($_FILES['images']['error'][$key] == 0) {
                            $fileName = time() . '_' . basename($_FILES['images']['name'][$key]);
                            $targetPath = $uploadDir . $fileName;
                            if (move_uploaded_file($tmpName, $targetPath)) {
                                $uploadedImages[] = '../uploads/' . $fileName;
                            }
                        }
                    }
                    $images = implode(',', $uploadedImages);
                    $query = "UPDATE products SET name='$name', description='$description', price=$price, 
                             stock_quantity=$stock, category_id=$category_id, images='$images', sizes='$sizes' WHERE product_id=$product_id";
                } else {
                    $query = "UPDATE products SET name='$name', description='$description', price=$price, 
                             stock_quantity=$stock, category_id=$category_id, sizes='$sizes' WHERE product_id=$product_id";
                }
                mysqli_query($conn, $query);
                header("Location: products.php");
                exit;

            case 'delete':
                $product_id = (int)$_POST['product_id'];
                $result = mysqli_query($conn, "SELECT images FROM products WHERE product_id=$product_id");
                if ($row = mysqli_fetch_assoc($result)) {
                    $images = explode(',', $row['images']);
                    foreach ($images as $image) {
                        $image = trim($image);
                        if ($image && file_exists($image)) {
                            unlink($image);
                        }
                    }
                }
                mysqli_query($conn, "DELETE FROM products WHERE product_id=$product_id");
                header("Location: products.php");
                exit;
        }
    }
}

// Function to convert file path to web-accessible path
function getWebPath($imagePath) {
    $imagePath = trim($imagePath);
    if (empty($imagePath)) {
        return '';
    }
    
    // Remove '../' from the beginning to make it web accessible
    $webPath = str_replace('../', '', $imagePath);
    
    return $webPath;
}

$categoriesResult = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$categories = [];
while ($row = mysqli_fetch_assoc($categoriesResult)) {
    $categories[] = $row;
}

$productsQuery = "SELECT p.*, c.name as category_name FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  ORDER BY p.created_at DESC";
$productsResult = mysqli_query($conn, $productsQuery);
?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/products.css">
    <title>Products Management - SaadiShop</title>
    <style>
       
    </style>
</head>

<body>
    <!-- Header with Navigation -->
<?php include 'navbar.php'; ?>

    <div class="container main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Products Management</h1>
     
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New Product
            </button>
        </div>

        <!-- Products Table -->
        <div class="products-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Images</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Sizes</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = mysqli_fetch_assoc($productsResult)): ?>
                        <tr>
                            <td><?= $product['product_id'] ?></td>
                            <td>
                                <div class="product-images">
                                    <?php 
                                    if (!empty($product['images'])) {
                                        $images = explode(',', $product['images']);
                                        $displayCount = 0;
                                        foreach ($images as $image) {
                                            if ($displayCount >= 2) break; // Limit to 2 images for table display
                                            
                                            $image = trim($image);
                                            if (!empty($image)) {
                                                $webPath = getWebPath($image);
                                                ?>
                                               <img src="../<?= htmlspecialchars($webPath) ?>
" 
                                                     alt="Product Image" 
                                                     class="product-thumb" 
                                                     style="width: 50px; height: 50px; object-fit: cover; margin: 2px; border-radius: 4px; border: 1px solid #ddd;"
                                                     onerror="this.style.display='none'; console.log('Image failed to load: <?= htmlspecialchars($webPath) ?>');">
                                                <?php
                                                $displayCount++;
                                            }
                                        }
                                        
                                        // Show count if more than 2 images
                                        if (count($images) > 2) {
                                            echo '<span class="more-images">+' . (count($images) - 2) . ' more</span>';
                                        }
                                    } else {
                                        echo '<span class="no-image">No images</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                            <td>$<?= number_format($product['price'], 2) ?></td>
                            <td><?= $product['stock_quantity'] ?></td>
                            <td>
                                <div class="sizes-display">
                                    <?php 
                                    if (!empty($product['sizes'])) {
                                        $sizes = explode(',', $product['sizes']);
                                        foreach ($sizes as $size) {
                                            $size = trim($size);
                                            if (!empty($size)) {
                                                echo '<span class="size-tag">' . htmlspecialchars(strtoupper($size)) . '</span>';
                                            }
                                        }
                                    } else {
                                        echo '<span class="no-sizes">No sizes</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td><?= date('M j, Y', strtotime($product['created_at'])) ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-warning btn-small" onclick="openEditModal(<?= htmlspecialchars(json_encode($product)) ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-small">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2>Add New Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Product Sizes</label>
                    <div id="addSizesContainer" class="sizes-container">
                        <div class="size-input-group">
                            <input type="text" name="sizes[]" class="size-input" placeholder="e.g., S, M, L, XL">
                            <button type="button" class="remove-size-btn" onclick="removeSize(this)">×</button>
                        </div>
                    </div>
                    <button type="button" class="add-size-btn" onclick="addSizeInput('addSizesContainer')">
                        <i class="fas fa-plus"></i> Add Size
                    </button>
                </div>

                <div class="form-group">
                    <label for="images">Product Images (Max 4)</label>
                    <input type="file" id="images" name="images[]" class="form-control" multiple accept="image/*" onchange="previewImages(this, 'addPreview')">
                    <div id="addPreview" class="image-preview"></div>
                </div>

                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2>Edit Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_product_id" name="product_id">
                
                <div class="form-group">
                    <label for="edit_name">Product Name</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_price">Price ($)</label>
                        <input type="number" id="edit_price" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_stock_quantity">Stock Quantity</label>
                        <input type="number" id="edit_stock_quantity" name="stock_quantity" class="form-control" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_category_id">Category</label>
                    <select id="edit_category_id" name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Product Sizes</label>
                    <div id="editSizesContainer" class="sizes-container">
                        <!-- Sizes will be populated by JavaScript -->
                    </div>
                    <button type="button" class="add-size-btn" onclick="addSizeInput('editSizesContainer')">
                        <i class="fas fa-plus"></i> Add Size
                    </button>
                </div>

                <div class="form-group">
                    <label>Current Images</label>
                    <div id="currentImages" class="image-preview"></div>
                </div>

                <div class="form-group">
                    <label for="edit_images">Replace Images (Optional - Max 4)</label>
                    <input type="file" id="edit_images" name="images[]" class="form-control" multiple accept="image/*" onchange="previewImages(this, 'editPreview')">
                    <div id="editPreview" class="image-preview"></div>
                </div>

                <button type="submit" class="btn btn-primary">Update Product</button>
            </form>
        </div>
    </div>

    <script>
        // Function to convert file path to web-accessible path (JavaScript version)
        function getWebPath(imagePath) {
            if (!imagePath) return '';
            
            imagePath = imagePath.trim();
            
            // Simply remove '../' from the beginning to make it web accessible
            return imagePath.replace('../', '');
        }

        function addSizeInput(containerId) {
            const container = document.getElementById(containerId);
            const sizeGroup = document.createElement('div');
            sizeGroup.className = 'size-input-group';
            sizeGroup.innerHTML = `
                <input type="text" name="sizes[]" class="size-input" placeholder="e.g., S, M, L, XL">
                <button type="button" class="remove-size-btn" onclick="removeSize(this)">×</button>
            `;
            container.appendChild(sizeGroup);
        }

        function removeSize(button) {
            const container = button.parentElement.parentElement;
            if (container.children.length > 1) {
                button.parentElement.remove();
            } else {
                // Clear the input instead of removing if it's the last one
                button.parentElement.querySelector('.size-input').value = '';
            }
        }

        function populateSizes(containerId, sizesString) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            
            if (sizesString && sizesString.trim() !== '') {
                const sizes = sizesString.split(',');
                sizes.forEach(size => {
                    size = size.trim();
                    if (size) {
                        const sizeGroup = document.createElement('div');
                        sizeGroup.className = 'size-input-group';
                        sizeGroup.innerHTML = `
                            <input type="text" name="sizes[]" class="size-input" value="${size}" placeholder="e.g., S, M, L, XL">
                            <button type="button" class="remove-size-btn" onclick="removeSize(this)">×</button>
                        `;
                        container.appendChild(sizeGroup);
                    }
                });
            }
            
            // Always ensure at least one size input exists
            if (container.children.length === 0) {
                addSizeInput(containerId);
            }
        }

        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

       function openEditModal(product) {
    document.getElementById('edit_product_id').value = product.product_id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_description').value = product.description;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_stock_quantity').value = product.stock_quantity;
    document.getElementById('edit_category_id').value = product.category_id;

    // Populate sizes
    populateSizes('editSizesContainer', product.sizes || '');

    // Show current images
    const currentImagesDiv = document.getElementById('currentImages');
    currentImagesDiv.innerHTML = '';

    if (product.images && product.images.trim() !== '') {
        const images = product.images.split(',');
        images.forEach(image => {
            image = image.trim();
            if (image) {
                // Ensure web-accessible path
                let webPath = image.replace('../', ''); // Remove leading ../ if exists
                webPath = '../' + webPath; // Add correct relative path for admin/products.php

                const imgContainer = document.createElement('div');
                imgContainer.style.display = 'inline-block';
                imgContainer.style.margin = '5px';
                imgContainer.style.position = 'relative';

                const img = document.createElement('img');
                img.src = webPath;
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '5px';
                img.style.border = '2px solid #ddd';
                img.style.display = 'block';

                img.onerror = function () {
                    console.log('Failed to load image: ' + webPath);
                    this.src = '../uploads/default.png'; // Fallback image
                };

                imgContainer.appendChild(img);
                currentImagesDiv.appendChild(imgContainer);
            }
        });
    } else {
        currentImagesDiv.innerHTML = '<p style="color: #666; font-style: italic;">No current images</p>';
    }

    document.getElementById('editModal').style.display = 'block';
}

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            // Clear preview images
            document.getElementById('addPreview').innerHTML = '';
            document.getElementById('editPreview').innerHTML = '';
            
            // Reset size inputs to have at least one empty input
            if (modalId === 'addModal') {
                document.getElementById('addSizesContainer').innerHTML = '';
                addSizeInput('addSizesContainer');
            }
        }

        function previewImages(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            
            if (input.files) {
                const files = Array.from(input.files).slice(0, 4); // Limit to 4 images
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgContainer = document.createElement('div');
                        imgContainer.style.display = 'inline-block';
                        imgContainer.style.margin = '5px';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '100px';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '5px';
                        img.style.border = '2px solid #ddd';
                        
                        imgContainer.appendChild(img);
                        preview.appendChild(imgContainer);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target == addModal) {
                closeModal('addModal');
            }
            if (event.target == editModal) {
                closeModal('editModal');
            }
        }

        // Initialize add modal with one size input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            addSizeInput('addSizesContainer');
        });
    </script>
</body>
</html>