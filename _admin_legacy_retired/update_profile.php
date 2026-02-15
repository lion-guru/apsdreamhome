<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db_connection.php';

// Set JSON header
header('Content-Type: application/json');

// Function to send JSON response and exit
function sendJsonResponse($success, $message = '', $data = []) {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if (!empty($data)) $response = array_merge($response, $data);
    
    // Log errors
    if (!$success) {
        error_log('Profile Update Error: ' . $message);
    }
    
    echo json_encode($response);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['admin_session']['user_id'])) {
    sendJsonResponse(false, 'User not logged in');
}

// Get user ID from session
$user_id = (int)$_SESSION['admin_session']['user_id'];

// Validate required fields
if (empty($_POST['name'])) {
    sendJsonResponse(false, 'Name is required');
}

// Sanitize input
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
    $profile_picture = ''; // This will be set to default in the database
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
    finfo_close($finfo);
    
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
    
    // Move uploaded file
    if (move_uploaded_file($file_tmp, $target_file)) {
        // Delete old profile picture if it exists and is not the default one
        if (!empty($current_picture) && $current_picture !== 'default.png') {
            $old_file = $upload_dir . $current_picture;
            if (file_exists($old_file) && is_writable($old_file)) {
                @unlink($old_file);
            }
        }
        $profile_picture = $new_filename;
    } else {
        $error = error_get_last();
        sendJsonResponse(false, 'Failed to upload file: ' . ($error['message'] ?? 'Unknown error'));
    }
}

try {
    $conn = $con;
    
    // Prepare SQL
    $sql = "UPDATE users SET name = ?, phone = ?";
    $types = 'ss';
    $params = [$name, $phone];
    
    // Add profile picture to update if it was changed
    if ($profile_picture !== $current_picture) {
        $sql .= ", profile_picture = ?";
        $types .= 's';
        $params[] = $profile_picture;
    }
    
    // Add WHERE condition
    $sql .= " WHERE id = ?";
    $types .= 'i';
    $params[] = $user_id;
    
    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    // Bind parameters
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update profile: ' . $stmt->error);
    }
    
    // Update session data
    $_SESSION['admin_session']['username'] = $name;
    if ($profile_picture !== $current_picture) {
        if (empty($profile_picture)) {
            // If picture was removed, set to empty in session
            $_SESSION['admin_session']['profile_picture'] = '';
        } else {
            $_SESSION['admin_session']['profile_picture'] = $profile_picture;
        }
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'message' => 'Profile updated successfully',
        'name' => $name
    ];
    
    if ($profile_picture !== $current_picture) {
        if ($remove_picture) {
            $response['profile_picture'] = ''; // Indicate picture was removed
            $response['message'] = 'Profile picture removed successfully';
        } else {
            $response['profile_picture'] = $profile_picture;
        }
    }
    
    sendJsonResponse(true, $response['message'], $response);
    
} catch (Exception $e) {
    // Log the error
    error_log('Profile update error: ' . $e->getMessage());
    
    // Send error response
    sendJsonResponse(false, 'An error occurred while updating your profile');
}

// Ensure no whitespace or newlines after closing PHP tag
?>