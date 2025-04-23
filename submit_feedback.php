<?php
// Basic backend for feedback form (can be expanded for DB/email later)
session_start();
require_once __DIR__ . '/includes/log_admin_activity.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false, 'message'=>'Method Not Allowed']);
    exit;
}

$rating = $_POST['rating'] ?? 0;
$message = $_POST['message'] ?? '';
$name = $_POST['name'] ?? '';

if ($rating < 1 || $rating > 5 || strlen($message) < 5) {
    echo json_encode(['success'=>false, 'message'=>'Please provide a rating and feedback message.']);
    exit;
}

// Simple spam prevention (honeypot)
if (!empty($_POST['website'])) {
    echo json_encode(['success'=>false, 'message'=>'Spam detected.']);
    exit;
}

// Save feedback to a simple file (expand to DB/email as needed)
$feedback_line = date('Y-m-d H:i:s') . "\t" . $rating . "\t" . str_replace(["\r","\n"],[' ',' '], $message) . "\t" . $name . "\n";
file_put_contents(__DIR__ . '/feedback.txt', $feedback_line, FILE_APPEND | LOCK_EX);

// Log feedback submission
log_admin_activity('submit_feedback', 'Feedback submitted by: ' . $name);

// Respond success
http_response_code(200);
echo json_encode(['success'=>true, 'message'=>'Thank you for your feedback!']);

?>
<?php require_once(__DIR__ . '/includes/templates/new_footer.php'); ?>
</body>
</html>
