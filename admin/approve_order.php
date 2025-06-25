<?php
ob_start();
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$response = ['success' => false, 'message' => 'Something went wrong.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $updated_at = date('Y-m-d H:i:s');

    // 1. Get order and customer email info
    $getOrder = mysqli_query($conn, "SELECT o.*, u.email, u.first_name FROM orders o
                                     JOIN users u ON o.user_id = u.id
                                     WHERE o.order_id = $order_id");

    if (!$getOrder || mysqli_num_rows($getOrder) === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    $order = mysqli_fetch_assoc($getOrder);
    $email = $order['email'];
    $firstName = $order['first_name'];

    // 2. Update status
    $update = mysqli_query($conn, "UPDATE orders SET status = 'Approve', updated_at = '$updated_at' WHERE order_id = $order_id");

    if ($update) {
        // 3. Send Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            include '../email.php'; // Should define: $mail->Username and $mail->Password

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your_email@gmail.com', 'MyShop Team');
            $mail->addAddress($email, $firstName);

            $mail->isHTML(true);
            $mail->Subject = "Your MyShop Order Has Been Approved!";
          $mail->Body = "
    <div style='font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px; color: #333333;'>
        <h2 style='color: #8bc34a;'>Hi $firstName,</h2>
        <p>Your order <strong style='color: #689f38;'>#$order_id</strong> has been <strong>approved</strong> and is now being processed. ✅</p>
        <p>We’ll notify you again once it’s out for delivery. Get ready!</p>
        <hr style='margin: 20px 0; border-top: 1px solid #dcedc8;'>
        <p style='color: #757575;'>Thank you for shopping with us 💚</p>
        <p style='color: #757575;'>– MyShop Team</p>
    </div>
";

            $mail->send();

            $response = ['success' => true, 'message' => 'Order approved and email sent.'];
        } catch (Exception $e) {
            $response = ['success' => true, 'message' => 'Order approved, but email failed: ' . $mail->ErrorInfo];
        }
    } else {
        $response = ['success' => false, 'message' => 'Failed to update order.'];
    }
} else {
    $response = ['success' => false, 'message' => 'Invalid request.'];
}

echo json_encode($response);
ob_end_flush();
?>
