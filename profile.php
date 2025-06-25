<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Myshop</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
<?php 
include 'header.php';
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
   
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error_message = 'First name, last name, and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);

            if ($stmt->fetch()) {
                $error_message = 'This email address is already in use.';
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users SET 
                        first_name = ?, 
                        last_name = ?, 
                        email = ?, 
                        phone = ?, 
                        address = ?, 
                        city = ?, 
                       
                        date_of_birth = ?, 
                        gender = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $first_name,
                    $last_name,
                    $email,
                    $phone,
                    $address,
                    $city,
                   
                    $date_of_birth,
                    $gender,
                    $user_id
                ]);

               $_SESSION['success_message'] = 'Profile updated successfully!';
header("Location: profile.php");
exit;

            }
        } catch (PDOException $e) {
            $error_message = 'Error updating profile. Please try again.';
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: login.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $order_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT COUNT(*) as cart_items FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT SUM(total_amount) as total_spent FROM orders WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$user_id]);
    $spending_stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div style='text-align: center; padding: 50px;'>Error loading profile</div>";
    exit;
}
?>

<div class="container">
    
    
    <div class="profile-container">
        <div class="profile-header">
            <?php if (!empty($user['profile_image'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" 
                     alt="Profile Picture" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar-placeholder">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            
            <h1 class="profile-name">
                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
            </h1>
            
            <div class="profile-email">
                <?php echo htmlspecialchars($user['email']); ?>
            </div>
            
            <span class="profile-status">Active Member</span>
        </div>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 12px 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="profile-content">
            <div class="profile-info">
                <h2 class="section-title">Profile Information</h2>
                <div class="info-item"><span class="info-label">First Name:</span> <span class="info-value"><?php echo htmlspecialchars($user['first_name']); ?></span></div>
                <div class="info-item"><span class="info-label">Last Name:</span> <span class="info-value"><?php echo htmlspecialchars($user['last_name']); ?></span></div>
                <div class="info-item"><span class="info-label">Email:</span> <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span></div>
                <div class="info-item"><span class="info-label">Phone:</span> <span class="info-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided'; ?></span></div>
                <div class="info-item"><span class="info-label">Address:</span> <span class="info-value"><?php echo !empty($user['address']) ? htmlspecialchars($user['address']) : 'Not provided'; ?></span></div>
                <div class="info-item"><span class="info-label">City:</span> <span class="info-value"><?php echo !empty($user['city']) ? htmlspecialchars($user['city']) : 'Not provided'; ?></span></div>
               
                <div class="info-item"><span class="info-label">Date of Birth:</span> <span class="info-value"><?php echo !empty($user['date_of_birth']) ? date('F j, Y', strtotime($user['date_of_birth'])) : 'Not provided'; ?></span></div>
                <div class="info-item"><span class="info-label">Gender:</span> <span class="info-value"><?php echo !empty($user['gender']) ? ucfirst(htmlspecialchars($user['gender'])) : 'Not specified'; ?></span></div>
                <div class="info-item"><span class="info-label">Member Since:</span> <span class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span></div>
            </div>

            <div class="profile-edit">
                <h2 class="section-title">Edit Profile</h2>
                <form method="POST" action="">
                    <div class="form-group"><label for="first_name" class="form-label">First Name *</label><input type="text" id="first_name" name="first_name" class="form-input" value="<?php echo htmlspecialchars($user['first_name']); ?>" required></div>
                    <div class="form-group"><label for="last_name" class="form-label">Last Name *</label><input type="text" id="last_name" name="last_name" class="form-input" value="<?php echo htmlspecialchars($user['last_name']); ?>" required></div>
                    <div class="form-group"><label for="email" class="form-label">Email *</label><input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required></div>
                    <div class="form-group"><label for="phone" class="form-label">Phone</label><input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"></div>
                    <div class="form-group"><label for="address" class="form-label">Address</label><textarea id="address" name="address" class="form-input form-textarea" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea></div>
                    <div class="form-group"><label for="city" class="form-label">City</label><input type="text" id="city" name="city" class="form-input" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>"></div>
                    
                    <div class="form-group"><label for="date_of_birth" class="form-label">Date of Birth</label><input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>"></div>
                    <div class="form-group"><label for="gender" class="form-label">Gender</label><select id="gender" name="gender" class="form-select"><option value="">Select Gender</option><option value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option><option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option><option value="other" <?php echo ($user['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option></select></div>
                    <div class="btn-group"><button type="submit" class="btn btn-primary">Update Profile</button><button type="reset" class="btn btn-secondary">Reset Changes</button></div>
                </form>
            </div>
        </div>

        <div class="profile-stats">
            <h2 class="section-title">Account Statistics</h2>
            <div class="stats-grid">
                <div class="stat-item"><div class="stat-number"><?php echo number_format($order_stats['order_count']); ?></div><div class="stat-label">Total Orders</div></div>
                <div class="stat-item"><div class="stat-number"><?php echo number_format($cart_stats['cart_items']); ?></div><div class="stat-label">Items in Cart</div></div>
                <div class="stat-item"><div class="stat-number"><?php echo number_format($spending_stats['total_spent'] ?? 0); ?> LBP</div><div class="stat-label">Total Spent</div></div>
                <div class="stat-item"><div class="stat-number"><?php echo ceil((time() - strtotime($user['created_at'])) / (365*24*60*60)); ?></div><div class="stat-label">Years as Member</div></div>
            </div>
        </div>

        <div class="profile-actions">
            <h2 class="section-title">Account Actions</h2>
            <div class="action-buttons">
                <a href="orders.php" class="btn btn-primary">View Order History</a>
                <a href="cart.php" class="btn btn-primary">View Shopping Cart</a>
                <a href="change_password.php" class="btn btn-secondary">Change Password</a>
                <a href="logout.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
