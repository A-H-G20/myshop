<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Verify Account - SaadiShop</title>
 
  <link rel="stylesheet" href="css/forgot.css">
  <link rel="stylesheet" href="css/verify.css">
  <link href="image/logo.png" rel="icon" />
</head>

<body>
<?php
session_start();
include 'config.php';

$message = '';
$message_type = '';

// Get email from URL parameter or POST
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $verification_code = $_POST['verification_code'] ?? '';

    if (empty($email)) {
        $message = "Email parameter is missing. Please use the verification link from your email.";
        $message_type = "error";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, first_name FROM users WHERE email = ? AND verification_code = ? AND verified = 0");
            $stmt->execute([$email, $verification_code]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $update = $pdo->prepare("UPDATE users SET email_verified_at = NOW(), verification_code = NULL, verified = 1 WHERE email = ? AND verification_code = ?");
                $update->execute([$email, $verification_code]);

                $_SESSION['temp_message'] = "Account verified successfully! You can now log in to your account.";
                header('Location: login.php');
                exit();
            } else {
                $message = "Invalid verification code or account already verified.";
                $message_type = "error";
            }
        } catch (PDOException $e) {
            $message = "Error during verification: " . $e->getMessage();
            $message_type = "error";
        }
    }
}
?>

  <section class="auth-section">
    <div class="container">
      <div class="auth-container">
        <div class="auth-card">
          <div class="auth-header">
            <div class="auth-icon">
              <i class="fas fa-user-check"></i>
            </div>
            <h2>Verify Your Account</h2>
            <p>Enter the verification code sent to<br><strong><?= $email ? $email : 'your email address' ?></strong></p>
          </div>

          <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>">
              <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
              <?= htmlspecialchars($message) ?>
            </div>
          <?php endif; ?>

          <form method="POST" class="auth-form">
            <input type="hidden" name="email" value="<?= $email ?>">

            <div class="form-group">
              <label for="verification_code">
                <i class="fas fa-key"></i>
                Verification Code
              </label>
              <input
                type="text"
                id="verification_code"
                name="verification_code"
                required
                placeholder="Enter verification code"
                autocomplete="off"
                class="code-input" />
              <small class="form-hint">
                <i class="fas fa-info-circle"></i>
                Check your email for the verification code
              </small>
            </div>

            <button type="submit" name="verify_email" class="btn btn-primary">
              <i class="fas fa-check-circle"></i>
              Verify Account
            </button>
          </form>


          <div class="auth-footer">
            <p>Already verified? <a href="login.php">Login to your account</a></p>
            <p>Need help? <a href="contact.php">Contact Support</a></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    // Auto-focus on the verification code input
    document.addEventListener('DOMContentLoaded', function() {
      const codeInput = document.getElementById('verification_code');
      codeInput.focus();
    });

    // Resend verification functionality
    function resendVerification() {
      const email = document.querySelector('input[name="email"]').value;
      if (!email) {
        alert('Email address is required to resend verification code.');
        return;
      }

      if (confirm('Are you sure you want to resend the verification code?')) {
        // You can create a separate endpoint for resending verification
        // For now, we'll redirect to register page or create a resend script
        window.location.href = 'resend_verification.php?email=' + encodeURIComponent(email);
      }
    }

    // Format input for better UX (optional - if you want to restrict to numbers only)
    document.getElementById('verification_code').addEventListener('input', function() {
      // Remove any non-alphanumeric characters if needed
      // this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
    });
  </script>

  <style>

  </style>

</body>

</html>