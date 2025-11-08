<?php
// Enhanced Session Manager with Timeout Enforcement

// Session timeout configuration (30 minutes for security)
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('SESSION_WARNING_TIME', 300); // 5 minutes warning before timeout

// Only manage session if not already started by the main file
if (session_status() === PHP_SESSION_NONE) {
    // Set session name
    $session_name = 'APS_DREAM_HOME_SESSID';
    session_name($session_name);

    // Set session cookie parameters (shorter lifetime for security)
    $lifetime = SESSION_TIMEOUT; // 30 minutes
    $path = '/';    
    $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $secure = isset($_SERVER['HTTPS']);
    $httponly = true;
    $samesite = 'Strict';

    // For PHP 7.3+ with SameSite support
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
    } else {
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
    }

    // Start the session
    session_start();

    // Initialize session activity tracking if not set
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }

    // Check for session timeout
    checkSessionTimeout();
}

// Function to check if customer is logged in
function isCustomerLoggedIn() {
    return isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'] === true;
}

// Function to require login - use relative path to avoid conflicts
function requireCustomerLogin($redirectTo = 'customer_login.php') {
    if (!isCustomerLoggedIn()) {
        // Store the current URL for redirecting back after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

        // Log the unauthorized access attempt
        error_log('Unauthorized access attempt to: ' . $_SERVER['REQUEST_URI']);

        // Use relative path to avoid URL conflicts
        header('Location: ' . $redirectTo);
        exit();
    }
}

// Function to check session timeout
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        $current_time = time();
        $elapsed_time = $current_time - $_SESSION['last_activity'];
        
        // Check if session has timed out
        if ($elapsed_time > SESSION_TIMEOUT) {
            // Session expired - log out automatically
            forceLogout('Session expired due to inactivity');
            exit();
        }
        
        // Check if session is about to timeout (warning period)
        if ($elapsed_time > (SESSION_TIMEOUT - SESSION_WARNING_TIME)) {
            $_SESSION['session_warning'] = true;
            $_SESSION['time_remaining'] = SESSION_TIMEOUT - $elapsed_time;
        } else {
            unset($_SESSION['session_warning']);
            unset($_SESSION['time_remaining']);
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = $current_time;
    }
}

// Force logout with reason
function forceLogout($reason = '') {
    // Log the automatic logout
    if (!empty($reason)) {
        error_log('Automatic logout: ' . $reason . ' - IP: ' . $_SERVER['REMOTE_ADDR']);
    }
    
    // Unset all of the session variables
    $_SESSION = array();

    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    // Redirect to login with timeout message
    header('Location: customer_login.php?timeout=1&reason=' . urlencode($reason));
    exit();
}

// Function to log out
function customerLogout() {
    // Log the manual logout
    error_log('User initiated logout - IP: ' . $_SERVER['REMOTE_ADDR']);
    
    // Unset all of the session variables
    $_SESSION = array();

    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    // Use relative path
    header('Location: customer_login.php');
    exit();
}

// Function to extend session (call this on user activity)
function extendSession() {
    if (isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        unset($_SESSION['session_warning']);
        unset($_SESSION['time_remaining']);
    }
}

// Function to get remaining session time
function getRemainingSessionTime() {
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        return max(0, SESSION_TIMEOUT - $elapsed);
    }
    return 0;
}
?>
