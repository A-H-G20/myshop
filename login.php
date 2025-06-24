<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Shop</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['identifier']);
    $password = $_POST['password'];

    // Try matching by email or phone
    $query = $pdo->prepare("
        SELECT * FROM users 
        WHERE (email = :input OR phone = :input) AND verified = 1 
        LIMIT 1
    ");
    $query->execute(['input' => $input]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = "Invalid credentials or account not verified.";
    }
}
?>

<body>
    <div class="login-container">
        <div class="login-left">
            <div class="decorative-elements">
                <div class="circle circle1"></div>
                <div class="circle circle2"></div>
                <div class="circle circle3"></div>
            </div>
            <div class="logo">
                <div class="logo-icon">🛒</div>
                <span>Shop</span>
            </div>
            <div class="welcome-text">Welcome Back!</div>
            <div class="welcome-desc">
                Sign in to access your account and continue shopping with amazing deals and fast delivery.
            </div>
        </div>

        <div class="login-right">
            <h2 class="form-title">Sign In</h2>
            <p class="form-subtitle">Enter your credentials to access your account</p>
<form method="POST" action="">
    <div class="form-group">
        <label class="form-label" for="identifier">Email or Phone</label>
        <input class="form-input" type="text" id="identifier" name="identifier" placeholder="Enter email or phone" required>
    </div>

    <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="form-input" type="password" id="password" name="password" placeholder="Enter your password" required>
    </div>

    <div class="form-options">
        <div class="checkbox-group">
            <input type="checkbox" id="remember">
            <label for="remember">Remember me</label>
        </div>
        <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
    </div>

    <button type="submit" class="login-btn">Sign In</button>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</form>

           

         

            <div class="register-link">
                Don't have an account? <a href="register">Create Account</a>
            </div>
        </div>
    </div>
</body>
</html>