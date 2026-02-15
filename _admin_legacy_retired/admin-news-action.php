<?php
// Secure admin news action handler
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit;
}

// admin-news-action.php: Handles add, edit, and delete actions for news articles
error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Basic input sanitization
$title = trim($_POST['title'] ?? '');
$date = trim($_POST['date'] ?? '');
$summary = trim($_POST['summary'] ?? '');
$image = trim($_POST['image'] ?? '');
$content = trim($_POST['content'] ?? '');

$success = false;
$error = '';

if ($action === 'add') {
    $sql = "INSERT INTO news (title, date, summary, image, content) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss', $title, $date, $summary, $image, $content);
    $success = $stmt->execute();
    $stmt->close();
    if ($success) {
        header('Location: /admin/admin-news.php?msg=' . urlencode('added'));
        exit;
    } else {
        $error = 'Failed to add news article.';
    }
} elseif ($action === 'edit' && $id > 0) {
    $sql = "UPDATE news SET title=?, date=?, summary=?, image=?, content=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssi', $title, $date, $summary, $image, $content, $id);
    $success = $stmt->execute();
    $stmt->close();
    if ($success) {
        header('Location: /admin/admin-news.php?msg=' . urlencode('updated'));
        exit;
    } else {
        $error = 'Failed to update news article.';
    }
} elseif ($action === 'delete' && $id > 0) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM news WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $success = $stmt->execute();
    $stmt->close();
    if ($success) {
        header('Location: /admin/admin-news.php?msg=' . urlencode('News deleted successfully.'));
        exit;
    } else {
        $error = "<p class='alert alert-warning'>* Error: " . htmlspecialchars($conn->error) . "</p>";
    }
}

// On error, redirect back with error message
header('Location: /admin/admin-news.php?error=' . urlencode($error));
exit;

?>
<html>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<!-- DYNAMICALLY MOVED CONTENT BELOW -->
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
