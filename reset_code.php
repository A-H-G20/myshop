<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Verify Reset Code - SaadiShop</title>
  <link rel="stylesheet" href="css/index.css" />
  <link rel="stylesheet" href="css/forgot.css">
</head>

<body>
<?php
session_start();
include 'config.php';

$message = '';
$message_type = '';

// Redirect if user didn't come from forgot password step
if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot.php');
    exit;
}

// Show temporary success message
if (isset($_SESSION['temp_message'])) {
    $message = $_SESSION['temp_message'];
    $message_type = 'success';
    unset($_SESSION['temp_message']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reset_code = $_POST['reset_code'] ?? '';
    $email = $_SESSION['reset_email'];

    // Verify reset code
    $stmt = $pdo->prepare("SELECT id, first_name, reset_code_expires FROM users WHERE email = ? AND reset_code = ?");
    $stmt->execute([$email, $reset_code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if code is still valid
        if (strtotime($user['reset_code_expires']) > time()) {
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['temp_message'] = "Code verified successfully. Please enter your new password.";
            header('Location: new_password.php');
            exit;
        } else {
            $message = "Reset code has expired. Please request a new one.";
            $message_type = "error";
        }
    } else {
        $message = "Invalid reset code. Please check and try again.";
        $message_type = "error";
    }
}
?>

<section class="auth-section">
  <div class="container">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <div class="auth-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h2>Verify Reset Code</h2>
          <p>Enter the 6-digit code sent to<br><strong><?= htmlspecialchars($_SESSION['reset_email']) ?></strong></p>
        </div>

        <?php if ($message): ?>
          <div class="alert alert-<?= $message_type ?>">
            <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
          <div class="form-group">
            <label for="reset_code">
              <i class="fas fa-key"></i>
              Reset Code
            </label>
            <input 
              type="text" 
              id="reset_code" 
              name="reset_code" 
              required 
              placeholder="Enter 6-digit code"
              maxlength="6"
              pattern="[0-9]{6}"
              autocomplete="off"
              class="code-input"
              oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            />
            <small class="form-hint">
              <i class="fas fa-info-circle"></i>
              Code expires in 1 hour
            </small>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="fas fa-check-circle"></i>
            Verify Code
          </button>
        </form>

    
        <div class="auth-footer">
          <p><a href="forgot_password.php">← Back to Email Entry</a></p>
          <p>Remember your password? <a href="login.php">Back to Login</a></p>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Auto-focus on the reset code input
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('reset_code');
    codeInput.focus();
    
    // Auto-submit when 6 digits are entered
    codeInput.addEventListener('input', function() {
        if (this.value.length === 6) {
            // Small delay to ensure user sees the complete code
            setTimeout(() => {
                this.form.submit();
            }, 500);
        }
    });
});

// Resend code functionality
function resendCode() {
    if (confirm('Are you sure you want to resend the reset code?')) {
        // Redirect back to forgot password page to resend
        window.location.href = 'forgot_password.php?resend=1';
    }
}

// Countdown timer for code expiration (optional enhancement)
let timeLeft = 3600; // 1 hour in seconds
function updateTimer() {
    if (timeLeft <= 0) {
        document.querySelector('.form-hint').innerHTML = '<i class="fas fa-exclamation-triangle"></i>Code has expired';
        document.querySelector('.form-hint').style.color = '#f44336';
        return;
    }
    
    const hours = Math.floor(timeLeft / 3600);
    const minutes = Math.floor((timeLeft % 3600) / 60);
    const seconds = timeLeft % 60;
    
    const timeString = hours > 0 ? 
        `${hours}h ${minutes}m ${seconds}s` : 
        `${minutes}m ${seconds}s`;
    
    document.querySelector('.form-hint').innerHTML = 
        `<i class="fas fa-clock"></i>Code expires in ${timeString}`;
    
    timeLeft--;
}

// Start timer (you might want to calculate actual remaining time from server)
// updateTimer();
// setInterval(updateTimer, 1000);
</script>

<style>
/* Additional styles for reset code page */
.code-input {
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
    letter-spacing: 0.5rem;
    font-family: 'Courier New', monospace;
}

.form-hint {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: var(--gray-dark);
}

.form-hint i {
    font-size: 0.8rem;
}

.btn-secondary {
    background: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
    box-shadow: none;
}

.btn-secondary:hover {
    background: var(--primary-color);
    color: var(--white);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 195, 74, 0.3);
}

.auth-actions {
    margin-bottom: 1.5rem;
}

/* Animation for successful code entry */
@keyframes codeSuccess {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.code-input.success {
    animation: codeSuccess 0.3s ease;
    border-color: var(--success-color);
    background-color: rgba(76, 175, 80, 0.05);
}
</style>

</body>
</html>