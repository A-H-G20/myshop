<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Set New Password - SaadiShop</title>
  <link rel="stylesheet" href="css/index.css" />
  <link rel="stylesheet" href="css/forgot.css">
</head>

<body>
<?php
session_start();
include 'config.php';

$message = '';
$message_type = '';

// Redirect if user didn't come from reset_code step
if (!isset($_SESSION['reset_user_id'])) {
    header('Location: forgot_password.php');
    exit;
}

// Temporary message display
if (isset($_SESSION['temp_message'])) {
    $message = $_SESSION['temp_message'];
    $message_type = 'success';
    unset($_SESSION['temp_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['reset_user_id'];

    if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_code_expires = NULL WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);

            session_destroy();
            session_start();
            $_SESSION['temp_message'] = "Password updated successfully! You can now log in with your new password.";
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $message = "Error updating password. Please try again.";
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
            <i class="fas fa-lock"></i>
          </div>
          <h2>Set New Password</h2>
          <p>Enter your new password below</p>
        </div>

        <?php if ($message): ?>
          <div class="alert alert-<?= $message_type ?>">
            <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form method="POST" class="auth-form" id="passwordForm">
          <div class="form-group">
            <label for="password">
              <i class="fas fa-lock"></i>
              New Password
            </label>
            <div class="password-input-container">
              <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                placeholder="Enter new password"
                minlength="8"
                autocomplete="new-password"
              />
              <button type="button" class="password-toggle" onclick="togglePassword('password')">
                <i class="fas fa-eye" id="password-eye"></i>
              </button>
            </div>
            <div class="password-strength" id="passwordStrength"></div>
          </div>

          <div class="form-group">
            <label for="confirm_password">
              <i class="fas fa-lock"></i>
              Confirm New Password
            </label>
            <div class="password-input-container">
              <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                required 
                placeholder="Confirm new password"
                minlength="8"
                autocomplete="new-password"
              />
              <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                <i class="fas fa-eye" id="confirm_password-eye"></i>
              </button>
            </div>
            <div class="password-match" id="passwordMatch"></div>
          </div>

          <div class="password-requirements">
            <h4>Password Requirements:</h4>
            <ul>
              <li id="length">At least 8 characters</li>
              <li id="uppercase">At least one uppercase letter</li>
              <li id="lowercase">At least one lowercase letter</li>
              <li id="number">At least one number</li>
              <li id="special">At least one special character</li>
            </ul>
          </div>

          <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="fas fa-check"></i>
            Update Password
          </button>
        </form>

        <div class="auth-footer">
          <p><a href="login.php">← Back to Login</a></p>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Password visibility toggle
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(fieldId + '-eye');
    
    if (field.type === 'password') {
        field.type = 'text';
        eye.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        eye.className = 'fas fa-eye';
    }
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };

    // Update requirement indicators
    Object.keys(requirements).forEach(req => {
        const element = document.getElementById(req);
        if (requirements[req]) {
            element.className = 'requirement-met';
            strength++;
        } else {
            element.className = 'requirement-unmet';
        }
    });

    return { strength, requirements };
}

// Password match checker
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchDiv = document.getElementById('passwordMatch');
    
    if (confirmPassword.length === 0) {
        matchDiv.innerHTML = '';
        return false;
    }
    
    if (password === confirmPassword) {
        matchDiv.innerHTML = '<i class="fas fa-check"></i> Passwords match';
        matchDiv.className = 'password-match success';
        return true;
    } else {
        matchDiv.innerHTML = '<i class="fas fa-times"></i> Passwords do not match';
        matchDiv.className = 'password-match error';
        return false;
    }
}

// Real-time validation
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const result = checkPasswordStrength(password);
    const strengthDiv = document.getElementById('passwordStrength');
    
    if (password.length === 0) {
        strengthDiv.innerHTML = '';
        return;
    }
    
    let strengthText = '';
    let strengthClass = '';
    
    if (result.strength <= 2) {
        strengthText = 'Weak';
        strengthClass = 'weak';
    } else if (result.strength <= 4) {
        strengthText = 'Medium';
        strengthClass = 'medium';
    } else {
        strengthText = 'Strong';
        strengthClass = 'strong';
    }
    
    strengthDiv.innerHTML = `Password Strength: <span class="${strengthClass}">${strengthText}</span>`;
    
    checkPasswordMatch();
    updateSubmitButton();
});

document.getElementById('confirm_password').addEventListener('input', function() {
    checkPasswordMatch();
    updateSubmitButton();
});

function updateSubmitButton() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const submitBtn = document.getElementById('submitBtn');
    
    const result = checkPasswordStrength(password);
    const passwordsMatch = password === confirmPassword && password.length > 0;
    const allRequirementsMet = result.strength === 5;
    
    if (allRequirementsMet && passwordsMatch) {
        submitBtn.disabled = false;
        submitBtn.classList.add('enabled');
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.remove('enabled');
    }
}

// Form submission
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long!');
        return false;
    }
});
</script>

<style>
/* Additional styles for new password page */
.password-input-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray-dark);
    cursor: pointer;
    font-size: 1rem;
}

.password-toggle:hover {
    color: var(--primary-color);
}

.password-strength {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
}

.password-strength .weak {
    color: var(--danger-color);
}

.password-strength .medium {
    color: var(--warning-color);
}

.password-strength .strong {
    color: var(--success-color);
}

.password-match {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.password-match.success {
    color: var(--success-color);
}

.password-match.error {
    color: var(--danger-color);
}

.password-requirements {
    background: var(--gray-light);
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.password-requirements h4 {
    margin-bottom: 1rem;
    color: var(--text-color);
    font-size: 1rem;
}

.password-requirements ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.password-requirements li {
    padding: 0.3rem 0;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.password-requirements li::before {
    content: '•';
    color: var(--gray-dark);
    font-size: 1.2rem;
}

.requirement-met {
    color: var(--success-color);
}

.requirement-met::before {
    content: '✓';
    color: var(--success-color);
    font-weight: bold;
}

.requirement-unmet {
    color: var(--gray-dark);
}

.btn:disabled {
    background: #e0e0e0;
    color: #9e9e9e;
    cursor: not-allowed;
    box-shadow: none;
}

.btn.enabled {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: var(--white);
    box-shadow: 0 6px 20px rgba(139, 195, 74, 0.3);
}
</style>

</body>
</html>