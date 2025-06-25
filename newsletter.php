<?php
include 'config.php';

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
    $stmt->execute([$email]);

    echo json_encode(['success' => true, 'message' => 'Subscribed successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
