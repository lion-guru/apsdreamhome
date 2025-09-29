<?php
// Simple session manager - avoid conflicts with main files

// Only manage session if not already started by the main file
if (session_status() === PHP_SESSION_NONE) {
    // Set session name
    $session_name = 'APS_DREAM_HOME_SESSID';
    session_name($session_name);

    // Set session cookie parameters
    $lifetime = 86400; // 24 hours
    $path = '/';
    $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $secure = isset($_SERVER['HTTPS']);
    $httponly = true;

    session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);

    // Start the session
    session_start();
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

// Function to log out
function customerLogout() {
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
?>
