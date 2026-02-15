<?php

/**
 * Admin Functions for APS Dream Homes
 * Contains functions specific to admin section
 */

// Include security and logging utilities
require_once dirname(__DIR__) . '/includes/SecurityUtility.php';
require_once dirname(__DIR__) . '/includes/AdminLogger.php';
require_once dirname(__DIR__) . '/includes/ErrorHandler.php';

// Include common functions if not already included
if (!function_exists('get_asset_url')) {
    include_once(dirname(__DIR__) . '/includes/functions/common-functions.php');
}

// Global security configuration
define('ENVIRONMENT', 'production'); // Change to 'development' for detailed error reporting

/**
 * Check if current user is an admin
 * 
 * @return bool True if user is admin, false otherwise
 */
/**
 * Check if current user is an admin with enhanced security
 * 
 * @return bool True if user is admin, false otherwise
 */
/**
 * Comprehensive admin authentication and authorization function
 * 
 * @return bool True if user is a valid admin, false otherwise
 */
function redirectToAdminLogin()
{
    // Redirect specifically to admin login page
    header('Location: /admin/login.php');
    exit();
}

function isAdmin()
{
    try {
        // Ensure session is started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Enhanced admin authentication check
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            // Log unauthorized access attempt
            AdminLogger::log('UNAUTHORIZED_ADMIN_ACCESS', [
                'ip' => $_SESSION['REMOTE_ADDR'] ?? 'Unknown',
                'attempted_url' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'action' => 'admin_access_denied',
                'reason' => 'No valid session',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
            return false;
        }

        // Check session timeout (30 minutes)
        $currentTime = time();
        $sessionLifetime = 1800; // 30 minutes
        if (($currentTime - ($_SESSION['admin_session']['last_activity'] ?? 0)) > $sessionLifetime) {
            // Session expired
            logAdminAction([
                'action' => 'session_expired',
                'username' => $_SESSION['admin_session']['username'] ?? 'Unknown',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);

            // Terminate the expired session
            session_unset();
            session_destroy();
            return false;
        }

        // Verify admin status in database
        $db = \App\Core\App::database();
        $admin = $db->fetch(
            "SELECT id, role, status FROM admin WHERE auser = :username",
            ['username' => $_SESSION['admin_session']['username']]
        );

        // Additional checks
        if (!$admin || $admin['status'] != 1) {
            logAdminAction([
                'action' => 'admin_access_denied',
                'reason' => $admin ? 'Inactive account' : 'User not found',
                'username' => $_SESSION['admin_session']['username'] ?? 'Unknown',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
            return false;
        }

        // Role-based access control
        $allowedRoles = ['superadmin', 'admin', 'manager'];
        if (!in_array($admin['role'], $allowedRoles)) {
            logAdminAction([
                'action' => 'admin_access_denied',
                'reason' => 'Insufficient role privileges',
                'username' => $_SESSION['admin_session']['username'] ?? 'Unknown',
                'role' => $admin['role'],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
            return false;
        }

        // Update last activity timestamp
        $_SESSION['admin_session']['last_activity'] = $currentTime;

        return true;
    } catch (PDOException $e) {
        // Log database errors
        error_log('Admin verification error: ' . $e->getMessage());

        logAdminAction([
            'action' => 'system_error',
            'error' => 'Database verification failed',
            'details' => $e->getMessage(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);

        return false;
    } catch (Exception $e) {
        // Catch any other unexpected errors
        error_log('Unexpected admin verification error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check if admin is logged in
 * 
 * @return bool True if admin is logged in, false otherwise
 */
function is_admin_logged_in()
{
    return isset($_SESSION['auser']);
}

/**
 * Check if admin is logged in (alias for compatibility)
 * @return bool
 */
function isAdminLoggedIn()
{
    return is_admin_logged_in();
}

/**
 * Log admin actions for security and audit purposes
 * 
 * @param array $logData Associative array of log details
 * @return void
 */
function logAdminAction($logData)
{
    // Ensure log data is an array
    if (!is_array($logData)) {
        error_log('Invalid log data provided');
        return;
    }

    // Default log details
    $defaultLogData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'action' => 'undefined_action'
    ];

    // Merge default and provided log data
    $logEntry = array_merge($defaultLogData, $logData);

    // Convert log entry to JSON for easy parsing
    $logJson = json_encode($logEntry);

    // Log to error log
    error_log($logJson);

    // Optional: Log to database or file
    try {
        $db = \App\Core\App::database();
        $query = "INSERT INTO admin_action_logs (action, details, ip_address, created_at) 
                  VALUES (:action, :details, :ip_address, NOW())";

        $db->execute($query, [
            'action' => $logEntry['action'],
            'details' => $logJson,
            'ip_address' => $logEntry['ip_address']
        ]);
    } catch (Exception $e) {
        // Fallback logging if database insert fails
        error_log('Failed to log admin action: ' . $e->getMessage());
    }
}

/**
 * Check if admin has a specific permission (stub, always true or implement as needed)
 * @param string $perm
 * @return bool
 */
function hasPermission($perm)
{
    // Implement your permission logic here if needed
    return true;
}

/**
 * Get admin asset URL
 * 
 * @param string $file The asset filename
 * @param string $type The asset type (img, css, js, plugins)
 * @return string Full URL path to the admin asset
 */
function get_admin_asset_url($file, $type = 'img')
{
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

    switch ($type) {
        case 'css':
            return $base_url . '/admin/assets/css/' . $file;
        case 'js':
            return $base_url . '/admin/assets/js/' . $file;
        case 'plugins':
            return $base_url . '/admin/assets/plugins/' . $file;
        default:
            return $base_url . '/admin/assets/img/' . $file;
    }
}

/**
 * Format date for admin display
 * 
 * @param string $date The date to format
 * @param string $format The format to use (default: 'd M, Y')
 * @return string Formatted date
 */
function format_admin_date($date, $format = 'd M, Y')
{
    return date($format, strtotime($date));
}

/**
 * Sanitize admin input
 * 
 * @param string $data The input data to sanitize
 * @return string Sanitized data
 */
function sanitize_admin_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Get gallery images
 *
 * @return array List of gallery images
 */
function getGalleryImages()
{
    $db = \App\Core\App::database();
    $images = [];

    try {
        $results = $db->fetch("SELECT id, title, description, image_path FROM gallery ORDER BY created_at DESC");

        foreach ($results as $row) {
            $images[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'path' => $row['image_path']
            ];
        }
    } catch (Exception $e) {
        error_log("Error getting gallery images: " . $e->getMessage());
    }

    return $images;
}
