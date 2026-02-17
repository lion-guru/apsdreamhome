<?php
require_once __DIR__ . '/core/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Error: Invalid request method. Deletion must be performed via POST.");
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    die("Error: CSRF token validation failed.");
}

if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = (int)$_POST['id'];
    try {
        $db = \App\Core\App::database();
        $db->execute("DELETE FROM admin WHERE id = :id", ['id' => $id]);
    } catch (Exception $e) {
        header("Location: adminlist.php?error=" . urlencode("Delete failed: " . $e->getMessage()));
        exit();
    }
}
header("Location: adminlist.php?msg=Deleted");
exit();
