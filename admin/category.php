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
    $uploadedImage = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../category/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $timestamp = time();
        $originalName = basename($_FILES['image']['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $hash = md5($originalName . $timestamp);
        $fileName = $timestamp . '_' . $hash . '.' . $extension;

        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $uploadedImage = '../category/' . $fileName; // store relative path
        }
    }

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $query = "INSERT INTO categories (name, description, image, created_at) 
              VALUES ('$name', '$description', '$uploadedImage', NOW())";
    mysqli_query($conn, $query);

    header("Location: category.php");
    exit; // make sure no further code is executed after redirect
    break;


            case 'edit':
                $category_id = (int)$_POST['category_id'];
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);

                $uploadedImage = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $uploadDir = '../category/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $timestamp = time();
                    $originalName = basename($_FILES['image']['name']);
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $hash = md5($originalName . $timestamp);
                    $fileName = $timestamp . '_' . $hash . '.' . $extension;

                    $targetPath = $uploadDir . $fileName;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                        $uploadedImage = '../category/' . $fileName;

                        // Delete old image
                        $oldImageResult = mysqli_query($conn, "SELECT image FROM categories WHERE category_id=$category_id");
                        if ($oldImageRow = mysqli_fetch_assoc($oldImageResult)) {
                            $oldImagePath = '../' . $oldImageRow['image'];
                            if ($oldImageRow['image'] && file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }
                    }

                    $query = "UPDATE categories SET name='$name', description='$description', image='$uploadedImage' WHERE category_id=$category_id";
                } else {
                    $query = "UPDATE categories SET name='$name', description='$description' WHERE category_id=$category_id";
                }
                mysqli_query($conn, $query);
                  header("Location: category.php");
    exit;
                break;

            case 'delete':
                $category_id = (int)$_POST['category_id'];
                
                // Check if category has products
                $productCheck = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE category_id=$category_id");
                $productCount = mysqli_fetch_assoc($productCheck)['count'];
                
                if ($productCount > 0) {
                    $error_message = "Cannot delete category. It has $productCount products associated with it.";
                } else {
                    // Delete category image
                    $result = mysqli_query($conn, "SELECT image FROM categories WHERE category_id=$category_id");
                    if ($row = mysqli_fetch_assoc($result)) {
                        $imagePath = '../' . $row['image'];
                        if ($row['image'] && file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                    mysqli_query($conn, "DELETE FROM categories WHERE category_id=$category_id");
                    $success_message = "Category deleted successfully.";
                }
                break;
        }
    }
}

$categoriesQuery = "SELECT * FROM categories ORDER BY created_at DESC";
$categoriesResult = mysqli_query($conn, $categoriesQuery);
?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/category.css" />
    <title>Categories Management - MyShop</title>
</head>

<body>
    <!-- Header with Navigation -->
   <?php include 'navbar.php'; ?>

    <div class="container main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Categories Management</h1>
           
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New Category
            </button>
        </div>

        <!-- Messages -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success_message ?>
            </div>
        <?php endif; ?>

        <!-- Categories Table -->
        <div class="categories-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Products Count</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                        <?php
                        // Get product count for this category
                        $productCountResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE category_id={$category['category_id']}");
                        $productCount = mysqli_fetch_assoc($productCountResult)['count'];
                        ?>
                        <tr>
                            <td><?= $category['category_id'] ?></td>
                            <td>
                                <div class="category-image">
                                    <?php if ($category['image'] && file_exists($category['image'])): ?>
                                        <img src="<?= htmlspecialchars($category['image']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="category-thumb">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($category['name']) ?></strong>
                            </td>
                            <td>
                                <div class="description-cell">
                                    <?= htmlspecialchars(substr($category['description'], 0, 100)) ?>
                                    <?= strlen($category['description']) > 100 ? '...' : '' ?>
                                </div>
                            </td>
                            <td>
                                <span class="product-count"><?= $productCount ?> products</span>
                            </td>
                            <td><?= date('M j, Y', strtotime($category['created_at'])) ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-warning btn-small" onclick="openEditModal(<?= htmlspecialchars(json_encode($category)) ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-small" <?= $productCount > 0 ? 'disabled title="Cannot delete category with products"' : '' ?>>
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

    <!-- Add Category Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2>Add New Category</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Category Image</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'addPreview')">
                    <div id="addPreview" class="image-preview"></div>
                </div>

                <button type="submit" class="btn btn-primary">Add Category</button>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2>Edit Category</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_category_id" name="category_id">
                
                <div class="form-group">
                    <label for="edit_name">Category Name</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label>Current Image</label>
                    <div id="currentImage" class="image-preview"></div>
                </div>

                <div class="form-group">
                    <label for="edit_image">Replace Image (Optional)</label>
                    <input type="file" id="edit_image" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'editPreview')">
                    <div id="editPreview" class="image-preview"></div>
                </div>

                <button type="submit" class="btn btn-primary">Update Category</button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function openEditModal(category) {
            document.getElementById('edit_category_id').value = category.category_id;
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_description').value = category.description;
            
            // Show current image
            const currentImageDiv = document.getElementById('currentImage');
            currentImageDiv.innerHTML = '';
            if (category.image) {
                const img = document.createElement('img');
                img.src = category.image;
                img.style.width = '150px';
                img.style.height = '150px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '10px';
                img.style.border = '2px solid #ddd';
                currentImageDiv.appendChild(img);
            } else {
                const noImageDiv = document.createElement('div');
                noImageDiv.className = 'no-image-large';
                noImageDiv.innerHTML = '<i class="fas fa-image"></i><br>No Image';
                currentImageDiv.appendChild(noImageDiv);
            }
            
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            // Clear preview images
            document.getElementById('addPreview').innerHTML = '';
            document.getElementById('editPreview').innerHTML = '';
        }

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '150px';
                    img.style.height = '150px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '10px';
                    img.style.border = '2px solid #ddd';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(input.files[0]);
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
    </script>

 
</body>
</html>