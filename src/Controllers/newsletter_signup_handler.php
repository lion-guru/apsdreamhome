<?php
// Newsletter signup handler
require_once(__DIR__ . '/config.php');
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address.';
    } else {
        $stmt = $con->prepare('INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)');
        $stmt->bind_param('s', $email);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Thank you for subscribing!';
        } else {
            $response['message'] = 'Subscription failed. Please try again.';
        }
        $stmt->close();
    }
} else {
    $response['message'] = 'Invalid request.';
}
echo json_encode($response);
