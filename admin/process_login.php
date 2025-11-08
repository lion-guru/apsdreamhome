<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/login_errors.log');

// Function to log debug messages
function log_debug($message, $data = null) {
    $log_message = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    if ($data !== null) {
        $log_message .= 'Data: ' . print_r($data, true) . "\n";
    }
    error_log($log_message, 3, __DIR__ . '/login_debug.log');
}

log_debug('=== Starting login process ===');

// Include required files
try {
    require_once __DIR__ . '/admin_login_handler.php';
    log_debug('Required files loaded successfully');
} catch (Exception $e) {
    log_debug('Error loading required files', ['error' => $e->getMessage()]);
    die('System error. Please try again later.');
}

log_debug('Entered process_login.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    log_debug('POST data received', $_POST);
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; // Don't trim password
    $captcha_answer = isset($_POST['captcha_answer']) ? intval($_POST['captcha_answer']) : 0;
    
    log_debug('Login attempt', [
        'username' => $username,
        'captcha_provided' => !empty($captcha_answer),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);

    // CAPTCHA temporarily disabled for debugging
    /*
    if (!isset($_SESSION['captcha_answer']) || $captcha_answer !== (int)$_SESSION['captcha_answer']) {
        // Debug info for CAPTCHA troubleshooting
        $expected = isset($_SESSION['captcha_answer']) ? $_SESSION['captcha_answer'] : 'NOT SET';
        $_SESSION['login_error'] = 'Invalid CAPTCHA answer. (You entered: ' . htmlspecialchars($captcha_answer) . ', Expected: ' . htmlspecialchars($expected) . ')';
        echo 'DEBUG: CAPTCHA mismatch, redirecting to index.php<br>';
        header('Location: index.php');
        exit();
    }
    */

    log_debug('Calling AdminLoginHandler::login()');
    try {
        // Process login
        $result = AdminLoginHandler::login($username, $password);
        log_debug('AdminLoginHandler::login() result', $result);
    } catch (Exception $e) {
        log_debug('Exception in AdminLoginHandler::login()', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        $_SESSION['login_error'] = 'A system error occurred. Please try again.';
        header('Location: index.php');
        exit();
    }

    if ($result['status'] === 'success') {
        $redirect = $result['redirect'] ?? 'dashboard.php';
        log_debug('Login successful, redirecting to: ' . $redirect);
        
        // Clear any existing error messages
        unset($_SESSION['login_error']);
        
        // Set success message
        $_SESSION['login_success'] = 'Login successful!';
        
        // Redirect to appropriate dashboard
        header('Location: ' . $redirect);
        exit();
    } else {
        $errorMsg = $result['message'] ?? 'Invalid username or password';
        log_debug('Login failed', ['error' => $errorMsg]);
        
        // Set error message
        $_SESSION['login_error'] = $errorMsg;
        
        // Preserve username for better UX
        $_SESSION['login_username'] = htmlspecialchars($username);
        
        // Redirect back to login page
        header('Location: index.php');
        exit();
    }
} else {
    log_debug('Invalid request method', ['method' => $_SERVER['REQUEST_METHOD']]);
    $_SESSION['login_error'] = 'Invalid request method';
    header('Location: index.php');
    exit();
}

// Log completion of login process
log_debug('=== Login process completed ===\n\n');
?>
