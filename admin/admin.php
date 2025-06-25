<?php
// Start output buffering to prevent header issues
ob_start();

// Include database configuration
include 'config.php';

// Start session for admin authentication and CSRF protection
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Simple admin authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // In production, redirect to login page
    // header("Location: admin_login.php");
    // exit;
}

// CSRF Token generation and validation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Initialize message variables
$message = '';
$error = '';

// User Management Class
class UserManager {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function getAllUsers($page = 1, $limit = 20, $search = '', $sortBy = 'id', $sortOrder = 'DESC', $roleFilter = 'admin') {
        $offset = ($page - 1) * $limit;

        $allowedSortFields = ['id', 'first_name', 'last_name', 'username', 'email', 'phone', 'role', 'created_at'];
        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $whereClause = "WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($roleFilter)) {
            $whereClause .= " AND role = ?";
            $params[] = $roleFilter;
            $types .= "s";
        }

        if (!empty($search)) {
            $whereClause .= " AND (first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, array_fill(0, 5, $searchTerm));
            $types .= str_repeat("s", 5);
        }

        $usersQuery = "SELECT id, first_name, last_name, username, email, phone, role, created_at FROM users $whereClause ORDER BY $sortBy $sortOrder LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($usersQuery);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);

        $countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
        $countStmt = $this->conn->prepare($countQuery);
        if (!empty($roleFilter)) {
            $countParams = [$roleFilter];
            $countTypes = "s";
            if (!empty($search)) {
                $countParams = array_merge($countParams, array_fill(0, 5, $searchTerm));
                $countTypes .= str_repeat("s", 5);
            }
            $countStmt->bind_param($countTypes, ...$countParams);
        } elseif (!empty($search)) {
            $countStmt->bind_param(str_repeat("s", 5), ...array_fill(0, 5, $searchTerm));
        }
        $countStmt->execute();
        $totalCount = $countStmt->get_result()->fetch_assoc()['total'];

        return [
            'users' => $users,
            'total' => $totalCount,
            'pages' => ceil($totalCount / $limit),
            'current_page' => $page
        ];
    }

    public function getUserById($userId) {
        $query = "SELECT id, first_name, last_name, username, email, phone, role, created_at FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function deleteUser($userId) {
        $this->conn->begin_transaction();

        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception("User not found");
            }

            $orderCheckQuery = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?";
            $stmt = $this->conn->prepare($orderCheckQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $orderCount = $stmt->get_result()->fetch_assoc()['order_count'];

            if ($orderCount > 0) {
                throw new Exception("Cannot delete user with existing orders. User has $orderCount order(s).");
            }

            $deleteQuery = "DELETE FROM users WHERE id = ?";
            $stmt = $this->conn->prepare($deleteQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Failed to delete user");
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'User deleted successfully'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createUser($userData) {
        $this->conn->begin_transaction();

        try {
            $required = ['first_name', 'last_name', 'username', 'email', 'password'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new Exception("$field is required");
                }
            }

            $checkQuery = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->bind_param("ss", $userData['username'], $userData['email']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("Username or email already exists");
            }

            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            $role = isset($userData['role']) && !empty($userData['role']) ? $userData['role'] : 'admin';

            $insertQuery = "INSERT INTO users (first_name, last_name, username, email, phone, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($insertQuery);
            $stmt->bind_param("sssssss",
                $userData['first_name'],
                $userData['last_name'],
                $userData['username'],
                $userData['email'],
                $userData['phone'],
                $hashedPassword,
                $role
            );
            $stmt->execute();
            $this->conn->commit();
            header("Location: admin.php");
            exit;

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateUser($userId, $userData) {
        $this->conn->begin_transaction();

        try {
            $existingUser = $this->getUserById($userId);
            if (!$existingUser) {
                throw new Exception("User not found");
            }

            if (!empty($userData['username']) || !empty($userData['email'])) {
                $checkQuery = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
                $stmt = $this->conn->prepare($checkQuery);
                $stmt->bind_param("ssi",
                    $userData['username'] ?: $existingUser['username'],
                    $userData['email'] ?: $existingUser['email'],
                    $userId
                );
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    throw new Exception("Username or email already exists");
                }
            }

            $updateFields = [];
            $params = [];
            $types = "";

            $allowedFields = ['first_name', 'last_name', 'username', 'email', 'phone', 'role'];
            foreach ($allowedFields as $field) {
                if (isset($userData[$field]) && $userData[$field] !== '') {
                    $updateFields[] = "$field = ?";
                    $params[] = $userData[$field];
                    $types .= "s";
                }
            }

            if (!empty($userData['password'])) {
                $updateFields[] = "password = ?";
                $params[] = password_hash($userData['password'], PASSWORD_DEFAULT);
                $types .= "s";
            }

            if (empty($updateFields)) {
                throw new Exception("No fields to update");
            }

            $params[] = $userId;
            $types .= "i";

            $updateQuery = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            $this->conn->commit();
            return ['success' => true, 'message' => 'User updated successfully'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getUserStats() {
        $stats = [];

        $totalQuery = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
        $result = $this->conn->query($totalQuery);
        $stats['total_users'] = $result->fetch_assoc()['total'];

        $monthQuery = "SELECT COUNT(*) as monthly FROM users WHERE role = 'admin' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $result = $this->conn->query($monthQuery);
        $stats['monthly_users'] = $result->fetch_assoc()['monthly'];

        $activeQuery = "SELECT COUNT(DISTINCT u.id) as active FROM users u INNER JOIN orders o ON u.id = o.user_id WHERE u.role = 'admin'";
        $result = $this->conn->query($activeQuery);
        $stats['active_users'] = $result->fetch_assoc()['active'];

        return $stats;
    }

    public function getRoles() {
        return ['admin'];
    }
}

$userManager = new UserManager($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRF($_POST['csrf_token'])) {
        $error = "Invalid request. Please try again.";
    } else {
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];
            $result = $userManager->deleteUser($userId);
            $message = $result['success'] ? $result['message'] : '';
            $error = !$result['success'] ? $result['error'] : '';
        }

        if (isset($_POST['action']) && $_POST['action'] === 'create') {
            $userData = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? 'admin'
            ];
            $userManager->createUser($userData); // redirects to admin.php
        }

        if (isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];
            $userData = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'role' => $_POST['role'] ?? 'admin'
            ];
            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }
            $result = $userManager->updateUser($userId, $userData);
            $message = $result['success'] ? $result['message'] : '';
            $error = !$result['success'] ? $result['error'] : '';
        }
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$sortBy = $_GET['sort'] ?? 'id';
$sortOrder = $_GET['order'] ?? 'DESC';
$roleFilter = $_GET['role'] ?? 'admin';

$userData = $userManager->getAllUsers($page, 20, $search, $sortBy, $sortOrder, $roleFilter);
$users = $userData['users'];
$totalPages = $userData['pages'];
$currentPage = $userData['current_page'];
$totalUsers = $userData['total'];

$stats = $userManager->getUserStats();
$availableRoles = $userManager->getRoles();

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - Myshop Admin</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
  <?php include 'navbar.php'; ?>
    <!-- Main Content -->
    <div class="container">
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Admin Management</h1>
                <p class="page-subtitle">Manage system users and their permissions</p>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Total Admin Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['monthly_users']); ?></div>
                    <div class="stat-label">New This Month</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['active_users']); ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($totalUsers); ?></div>
                    <div class="stat-label">Filtered Results</div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <strong>✓</strong> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <strong>✗</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Controls -->
            <div class="controls-section">
                <div class="controls-header">
                    <div class="search-filters">
                        <form method="GET" style="display: contents;">
                            <div class="form-group">
                                <label for="search">Search Users</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                       placeholder="Name, username, email..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        
                   
                            <div class="form-group">
                                <label for="order">Order</label>
                                <select id="order" name="order" class="form-control">
                                    <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                                    <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary">🔍 Search</button>
                            </div>
                        </form>
                    </div>
                  <button type="button" class="btn btn-success" onclick="openModal('createModal')">
    + Add New User
</button>

                </div>
            </div>

            <!-- Users Table -->
            <div class="users-table-container">
                <div class="table-header">
                    <h3 class="table-title">Users List (<?php echo number_format($totalUsers); ?> total)</h3>
                </div>
                
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">👥</div>
                        <h3 class="empty-state-title">No Users Found</h3>
                        <p class="empty-state-text">
                            <?php if (!empty($search) || !empty($roleFilter)): ?>
                                No users match your current filters. Try adjusting your search criteria.
                            <?php else: ?>
                                There are no users in the system yet. Create the first user to get started.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th onclick="sortTable('id')" class="<?php echo $sortBy === 'id' ? 'active' : ''; ?>">
                                        ID <span class="sort-icon"><?php echo $sortBy === 'id' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕'; ?></span>
                                    </th>
                                    <th onclick="sortTable('first_name')" class="<?php echo $sortBy === 'first_name' ? 'active' : ''; ?>">
                                        Name <span class="sort-icon"><?php echo $sortBy === 'first_name' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕'; ?></span>
                                    </th>
                                    <th onclick="sortTable('username')" class="<?php echo $sortBy === 'username' ? 'active' : ''; ?>">
                                        Username <span class="sort-icon"><?php echo $sortBy === 'username' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕'; ?></span>
                                    </th>
                                    <th onclick="sortTable('email')" class="<?php echo $sortBy === 'email' ? 'active' : ''; ?>">
                                        Email <span class="sort-icon"><?php echo $sortBy === 'email' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕'; ?></span>
                                    </th>
                                    <th>Phone</th>
                                    <th onclick="sortTable('role')" class="<?php echo $sortBy === 'role' ? 'active' : ''; ?>">
                                        Role <span class="sort-icon"><?php echo $sortBy === 'role' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕'; ?></span>
                                    </th>
                                    <th onclick="sortTable('created_at')" class="<?php echo $sortBy === 'created_at' ? 'active' : ''; ?>">
                                        Created <span class="sort-icon"><?php echo $sortBy === 'created_at' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕'; ?></span>
                                    </th>
                                  
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                                </div>
                                                <div class="user-details">
                                                    <div class="user-name">
                                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                    </div>
                                                    <div class="user-username">@<?php echo htmlspecialchars($user['username']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                                        <td>
                                            <span class="role-badge role-<?php echo htmlspecialchars($user['role']); ?>">
                                                <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=1&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&sort=<?php echo urlencode($sortBy); ?>&order=<?php echo urlencode($sortOrder); ?>">First</a>
                        <a href="?page=<?php echo ($currentPage - 1); ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&sort=<?php echo urlencode($sortBy); ?>&order=<?php echo urlencode($sortOrder); ?>">Previous</a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&sort=<?php echo urlencode($sortBy); ?>&order=<?php echo urlencode($sortOrder); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo ($currentPage + 1); ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&sort=<?php echo urlencode($sortBy); ?>&order=<?php echo urlencode($sortOrder); ?>">Next</a>
                        <a href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&sort=<?php echo urlencode($sortBy); ?>&order=<?php echo urlencode($sortOrder); ?>">Last</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New User</h2>
                <button type="button" class="close-btn" onclick="closeModal('createModal')">&times;</button>
            </div>
            <form method="POST" onsubmit="return validateForm('createForm')">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="create_first_name">First Name *</label>
                        <input type="text" id="create_first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="create_last_name">Last Name *</label>
                        <input type="text" id="create_last_name" name="last_name" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="create_username">Username *</label>
                        <input type="text" id="create_username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="create_email">Email *</label>
                        <input type="email" id="create_email" name="email" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="create_phone">Phone</label>
                        <input type="tel" id="create_phone" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="create_role">Role *</label>
                        <select id="create_role" name="role" class="form-control" required>
                            <?php foreach ($availableRoles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role); ?>" 
                                        <?php echo $role === 'admin' ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(htmlspecialchars($role)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row single">
                    <div class="form-group">
                        <label for="create_password">Password *</label>
                        <input type="password" id="create_password" name="password" class="form-control" required minlength="6">
                        <small style="color: var(--gray-dark);">Minimum 6 characters</small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <span class="btn-text">Create User</span>
                        <span class="loading" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    
        


<script>
function openModal(id) {
    document.getElementById(id).classList.add('show');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

// Optional: click outside to close
window.onclick = function(event) {
    document.querySelectorAll(".modal").forEach(function(modal) {
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    });
};
</script>
