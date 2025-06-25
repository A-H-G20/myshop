<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Forgot Password - SaadiShop</title>
  <link rel="stylesheet" href="css/index.css" />
  <link rel="stylesheet" href="css/forgot.css">
</head>

<body>
<?php
session_start();
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id, email, first_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate reset code (6 digit)
        $reset_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $reset_code_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Update user with reset code
        $update = $pdo->prepare("UPDATE users SET reset_code = ?, reset_code_expires = ? WHERE email = ?");
        $updated = $update->execute([$reset_code, $reset_code_expires, $email]);

        if ($updated) {
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;

                include 'email.php'; // defines $mail->Username and $mail->Password

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('your_email@gmail.com', 'SaadiShop Team');
                $mail->addAddress($email, $user['first_name']);
                $mail->isHTML(true);
                $mail->Subject = "Your SaadiShop Password Reset Code";
                $mail->Body = "
                    <h2>Password Reset Request</h2>
                    <p>Hello <strong>{$user['first_name']}</strong>,</p>
                    <p>Your password reset code is:</p>
                    <h3 style='color: #4CAF50;'>$reset_code</h3>
                    <p>This code will expire in 1 hour.</p>
                    <p>If you didn’t request this, you can safely ignore this email.</p>
                ";
                $mail->send();

                $_SESSION['reset_email'] = $email;
                $_SESSION['temp_message'] = "Reset code sent to your email.";
                header('Location: reset_code.php');
                exit;
            } catch (Exception $e) {
                $message = "Failed to send reset email: {$mail->ErrorInfo}";
                $message_type = "error";
            }
        } else {
            $message = "Error occurred. Please try again.";
            $message_type = "error";
        }
    } else {
        // Always return generic response for security
        $message = "If this email exists in our system, you will receive a reset code shortly.";
        $message_type = "success";
    }
}
?>


<section class="auth-section">
  <div class="container">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <div class="auth-icon">
            <i class="fas fa-key"></i>
          </div>
          <h2>Forgot Password</h2>
          <p>Enter your email address and we'll send you a reset code</p>
        </div>

        <?php if ($message): ?>
          <div class="alert alert-<?= $message_type ?>">
            <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
          <div class="form-group">
            <label for="email">
              <i class="fas fa-envelope"></i>
              Email Address
            </label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              required 
              placeholder="Enter your email address"
              autocomplete="email"
            />
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i>
            Send Reset Code
          </button>
        </form>

        <div class="auth-footer">
          <p>Remember your password? <a href="login.php">Back to Login</a></p>
          <p>Don't have an account? <a href="register.php">Sign Up</a></p>
        </div>
      </div>
    </div>
  </div>
</section>

</body>
</html>