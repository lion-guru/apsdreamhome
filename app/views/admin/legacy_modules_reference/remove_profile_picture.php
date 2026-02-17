<?php
require_once __DIR__ . '/core/init.php';

use App\Core\Database;

if (!isAuthenticated() || !isAdmin()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Set JSON header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Security validation failed']);
    exit();
}

$user_id = getAuthUserId();
$db = \App\Core\App::database();

try {
    $db->beginTransaction();

    // Get current profile picture filename
    $user = $db->fetch("SELECT uimage FROM admin WHERE id = :id", ['id' => $user_id]);

    if (!$user) {
        throw new Exception('User not found');
    }

    $profile_picture = $user['uimage'];

    if (!empty($profile_picture) && $profile_picture !== 'default.png') {
        $upload_dir = __DIR__ . '/uploads/profile_pictures/';
        $file_path = $upload_dir . $profile_picture;
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
    }

    // Remove the profile picture reference from the database
    $db->execute("UPDATE admin SET uimage = NULL WHERE id = :id", ['id' => $user_id]);

    $db->commit();

    // Log the action
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $details = 'Removed profile picture (ID: ' . $user_id . ')';
    $db->execute("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (:user_id, 'Remove Profile Picture', :details, :ip)", [
        'user_id' => $user_id,
        'details' => $details,
        'ip' => $ip
    ]);

    echo json_encode(['success' => true, 'message' => 'Profile picture removed successfully']);
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log('Error removing profile picture: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while removing the profile picture']);
}
