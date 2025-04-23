<?php
// Include configuration
require_once __DIR__ . '/common-functions.php';
require_once __DIR__ . '/../config.php';

// Existing sanitize_input function
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        global $con;
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return mysqli_real_escape_string($con, $data);
    }
}

// Enhanced token generation with expiry
function generate_token($expiry = 3600) {
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;
    $_SESSION['token_expiry'] = time() + $expiry;
    return $token;
}

// Validate token
function validate_token($token) {
    if (!isset($_SESSION['token']) || !isset($_SESSION['token_expiry'])) {
        return false;
    }
    if ($_SESSION['token'] !== $token) {
        return false;
    }
    if (time() > $_SESSION['token_expiry']) {
        return false;
    }
    return true;
}

// Enhanced file upload validation
function validate_file_upload($file, $allowed_types = ['jpg', 'jpeg', 'png']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        log_error('Invalid file parameter');
        return false;
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        log_error('Invalid file type: ' . $file_extension);
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        log_error('File too large: ' . $file['size']);
        return false;
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = [
        'image/jpeg',
        'image/png'
    ];

    if (!in_array($mime_type, $allowed_mimes)) {
        log_error('Invalid MIME type: ' . $mime_type);
        return false;
    }
    
    return true;
}

// Enhanced error logging
function log_error($message) {
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $log_message = "[$timestamp] IP: $user_ip | Agent: $user_agent | Message: $message\n";
    error_log($log_message, 3, $log_file);
}

// Secure file upload handler
function handle_file_upload($file, $destination_path) {
    if (!validate_file_upload($file)) {
        return false;
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $destination = $destination_path . '/' . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        log_error('Failed to move uploaded file');
        return false;
    }

    return $new_filename;
}

// Session security check
function check_session_security() {
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }
    
    if (time() - $_SESSION['last_activity'] > 1800) { // 30 minutes
        session_destroy();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

// Password strength validation
function validate_password($password) {
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match("/[A-Z]/", $password)) {
        return false;
    }
    if (!preg_match("/[a-z]/", $password)) {
        return false;
    }
    if (!preg_match("/[0-9]/", $password)) {
        return false;
    }
    if (!preg_match("/[^A-Za-z0-9]/", $password)) {
        return false;
    }
    return true;
}

// --- Google OAuth Login URL Generator (Proxy) ---
if (!function_exists('getGoogleLoginUrl')) {
    function getGoogleLoginUrl($redirectUri = null) {
        require_once __DIR__ . '/common-functions.php';
        return \getGoogleLoginUrl($redirectUri);
    }
}

// --- Google OAuth Associate Login URL Generator (Proxy) ---
if (!function_exists('getAssociateGoogleLoginUrl')) {
    function getAssociateGoogleLoginUrl($redirectUri = null) {
        require_once __DIR__ . '/common-functions.php';
        return \getAssociateGoogleLoginUrl($redirectUri);
    }
}
?>