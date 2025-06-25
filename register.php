<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Account - Shop</title>
  <link rel="stylesheet" href="css/register.css">
</head>
<body>

<?php
require 'config.php'; // must define $pdo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $date_of_birth = $_POST['birthdate'] ?? '';
    $role = 'user';
$base_username = strtolower($first_name . '.' . $last_name);
$username = $base_username;
$suffix = 1;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->execute([$username]);
while ($stmt->fetchColumn() > 0) {
    $username = $base_username . $suffix;
    $stmt->execute([$username]);
    $suffix++;
}

   $verification_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    $created_at = date('Y-m-d H:i:s');
    $verified = 0;

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($phone) || empty($gender) || empty($address) || empty($city) || empty($date_of_birth)) {
        $error = "All fields are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "Email is already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $insert = $pdo->prepare("INSERT INTO users (first_name, last_name, username, email, phone, password, verification_code, role, address, city, date_of_birth, gender, verified, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insert->execute([
                    $first_name, $last_name, $username, $email, $phone, $hashed_password,
                    $verification_code, $role, $address, $city, $date_of_birth, $gender, $verified, $created_at
                ]);

                $mail = new PHPMailer(true);
                include 'email.php'; // sets $mail->Username and $mail->Password

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('email@gmail.com', 'Souk Al Dahab Store');
                $mail->addAddress($email, $first_name);
                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email - Lebanon Tourism';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
                        <div style='text-align: center; padding-bottom: 20px;'>
                            <h2 style='color: #028383;'>Welcome to Souk Al Dahab Store</h2>
                        </div>
                        <p style='font-size: 16px; color: #333;'>Dear <b>{$first_name}</b>,</p>
                        <p style='font-size: 16px; color: #555;'>Thank you for registering with us. To complete your registration, please use the following verification code:</p>
                        <div style='text-align: center; margin: 20px 0;'>
                            <span style='font-size: 24px; color: #e74c3c; font-weight: bold; border: 2px dashed #028383; padding: 10px 20px; display: inline-block; border-radius: 5px;'>{$verification_code}</span>
                        </div>
                        <p style='font-size: 16px; color: #555;'>If you did not initiate this registration, please disregard this email.</p>
                        <p style='font-size: 16px; color: #555;'>Best regards,</p>
                        <p style='font-size: 16px; color: #028383;'><b>Souk Al Dahab Administrator</b></p>
                    </div>";

                $mail->send();

   header("Location: verify.php?email=" . urlencode($email));

            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<div class="register-container">
  <div class="register-left">
    <div class="logo">
      <div class="logo-icon">🛒</div>
      <span>Shop</span>
    </div>

    <?php if ($error): ?>
      <p style="color:red; margin-bottom: 15px; text-align:center;"><strong><?= htmlspecialchars($error) ?></strong></p>
    <?php elseif ($success): ?>
      <p style="color:green; margin-bottom: 15px; text-align:center;"><strong><?= htmlspecialchars($success) ?></strong></p>
    <?php endif; ?>

    <h2 class="form-title">Create Account</h2>
    <p class="form-subtitle">Join us and get access to exclusive deals and fast delivery</p>

    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label>First Name</label>
          <input class="form-input" type="text" name="first_name" required>
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input class="form-input" type="text" name="last_name" required>
        </div>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input class="form-input" type="email" name="email" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Phone Number</label>
          <input class="form-input" type="tel" name="phone" required>
        </div>
        <div class="form-group">
          <label>Date of Birth</label>
          <input class="form-input" type="date" name="birthdate" required>
        </div>
      </div>

      <div class="form-group">
        <label>Address</label>
        <input class="form-input" type="text" name="address" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>City</label>
          <input class="form-input" type="text" name="city" required>
        </div>
        <div class="form-group">
          <label>Gender</label>
          <select class="form-select" name="gender" required>
            <option value="">Select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input class="form-input" type="password" name="password" required>
      </div>

      <div class="checkbox-group">
        <input type="checkbox" required>
        <label>I agree to the <a href="#">Terms</a> & <a href="#">Privacy Policy</a></label>
      </div>

      <button type="submit" class="register-btn">Create Account</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="login">Sign In</a>
    </div>
  </div>

  <div class="register-right">
    <div class="decorative-elements">
      <div class="circle circle1"></div>
      <div class="circle circle2"></div>
      <div class="circle circle3"></div>
    </div>

    <div class="join-text">Join Our Community</div>
    <div class="join-desc">
      Create your account today and unlock amazing benefits
    </div>

    <ul class="benefits">
      <li>Free delivery on orders over $50</li>
      <li>Exclusive member discounts</li>
      <li>Early access to sales</li>
      <li>Personalized recommendations</li>
      <li>Easy returns and exchanges</li>
      <li>24/7 customer support</li>
      <li>Loyalty rewards program</li>
      <li>Birthday special offers</li>
    </ul>
  </div>
</div>
</body>
</html>
