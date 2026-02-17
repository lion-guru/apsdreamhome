<?php
require_once __DIR__ . '/core/init.php';

// Set JSON header
header('Content-Type: application/json');

function sendJsonResponse($success, $message = '', $data = []) {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if (!empty($data)) $response = array_merge($response, $data);
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    sendJsonResponse(false, 'Security validation failed');
}

use App\Core\Database;

$db = \App\Core\App::database();

$user_id = getAuthUserId();

// Validate required fields
if (empty($_POST['name'])) {
    sendJsonResponse(false, 'Name is required');
}

$name = trim($_POST['name']);
$phone = trim($_POST['phone'] ?? '');
$current_picture = trim($_POST['current_picture'] ?? '');
$remove_picture = isset($_POST['remove_picture']) && $_POST['remove_picture'] == '1';
$profile_picture = $current_picture;

// Handle profile picture removal
if ($remove_picture) {
    if (!empty($current_picture) && $current_picture !== 'default.png') {
        $upload_dir = __DIR__ . '/uploads/profile_pictures/';
        $old_file = $upload_dir . $current_picture;
        if (file_exists($old_file) && is_writable($old_file)) {
            @unlink($old_file);
        }
    }
    $profile_picture = ''; 
}
// Handle file upload
elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_picture'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_tmp);
    if (is_resource($finfo)) { finfo_close($finfo); }
    
    if (!in_array($mime_type, $allowed_types)) {
        sendJsonResponse(false, 'Only JPG, PNG, and GIF files are allowed');
    }
    
    // Validate file size (max 2MB)
    if ($file_size > (2 * 1024 * 1024)) {
        sendJsonResponse(false, 'File size must be less than 2MB');
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = __DIR__ . '/uploads/profile_pictures/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
            sendJsonResponse(false, 'Failed to create upload directory');
        }
    }
    
    // Generate unique filename
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
    $target_file = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file_tmp, $target_file)) {
        if (!empty($current_picture) && $current_picture !== 'default.png') {
            $old_file = $upload_dir . $current_picture;
            if (file_exists($old_file) && is_writable($old_file)) {
                @unlink($old_file);
            }
        }
        $profile_picture = $new_filename;
    } else {
        sendJsonResponse(false, 'Failed to upload profile picture');
    }
}

// Update database
if ($db->execute("UPDATE admin SET auser = :name, phone = :phone, uimage = :image WHERE id = :id", [
    'name' => $name,
    'phone' => $phone,
    'image' => $profile_picture,
    'id' => $user_id
])) {
    // Log the action
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $details = 'Updated profile via AJAX (ID: ' . $user_id . ')';
    $db->execute("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (:user_id, 'Update Profile', :details, :ip)", [
        'user_id' => $user_id,
        'details' => $details,
        'ip' => $ip
    ]);

    sendJsonResponse(true, 'Profile updated successfully', ['profile_picture' => $profile_picture]);
} else {
    sendJsonResponse(false, 'Database update failed');
}
?>
