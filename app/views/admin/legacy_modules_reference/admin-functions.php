<?php

/**
 * Admin Functions for APS Dream Homes
 * Contains functions specific to admin section
 */

require_once __DIR__ . '/core/init.php';

// Include security and logging utilities
require_once dirname(__DIR__) . '/includes/SecurityUtility.php';
require_once dirname(__DIR__) . '/includes/AdminLogger.php';
require_once dirname(__DIR__) . '/includes/ErrorHandler.php';

// Include common functions if not already included
if (!function_exists('get_asset_url')) {
    include_once(dirname(__DIR__) . '/includes/functions/common-functions.php');
}

// Global security configuration
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'production'); // Change to 'development' for detailed error reporting
}

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

if (!function_exists('isAdmin')) {
    /**
     * Check if current user is an admin with enhanced security
     * 
     * @return bool True if user is admin, false otherwise
     */
    function isAdmin()
    {
        try {
            // Use unified helper for basic check
            if (!isAuthenticated()) {
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

            // Check if user is admin role
            if (getAuthRole() !== 'admin') {
                return false;
            }

            // Check session timeout using helper
            if (isSessionTimedOut()) {
                // Session expired
                logAdminAction([
                    'action' => 'session_expired',
                    'username' => getAuthUsername(),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);

                destroyAuthSession();
                return false;
            }

            // Verify admin status in database if needed
            // (Keeping legacy DB check for extra security)
            $db = \App\Core\App::database();
            $admin = $db->fetch("SELECT id, role, status FROM admin WHERE auser = :auser", ['auser' => getAuthUsername()]);

            // Additional checks
            if (!$admin || $admin['status'] != 'active') {
                logAdminAction([
                    'action' => 'admin_access_denied',
                    'reason' => $admin ? 'Inactive account' : 'User not found',
                    'username' => getAuthUsername(),
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
                    'username' => getAuthUsername(),
                    'role' => $admin['role'],
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                return false;
            }

            // Update last activity timestamp using helper
            updateLastActivity();

            return true;
        } catch (\Exception $e) {
            error_log('Admin verification error: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Check if admin is logged in
 * 
 * @return bool True if admin is logged in, false otherwise
 */
function is_admin_logged_in()
{
    return isAuthenticated() && getAuthRole() === 'admin';
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
        $db->insert('admin_action_logs', [
            'action' => $logEntry['action'],
            'details' => $logJson,
            'ip_address' => $logEntry['ip_address'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } catch (\Exception $e) {
        // Fallback logging if database insert fails
        error_log('Failed to log admin action: ' . $e->getMessage());
    }
}

if (!function_exists('hasPermission')) {
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
}

/**
 * Update or insert a setting in the site_settings table.
 * @param string $settingName The name of the setting.
 * @param string $settingValue The value of the setting.
 * @return bool True on success, false on failure.
 */
function updateSetting(string $settingName, string $settingValue): bool
{
    try {
        $db = \App\Core\App::database();
        $db->query("INSERT INTO site_settings (setting_name, value) VALUES (:name, :value) ON DUPLICATE KEY UPDATE value = :update_value", [
            'name' => $settingName,
            'value' => $settingValue,
            'update_value' => $settingValue
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Error updating setting '{$settingName}': " . $e->getMessage());
        return false;
    }
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
    $base_url = defined('ADMIN_URL') ? ADMIN_URL : '/apsdreamhome/admin';

    switch ($type) {
        case 'css':
            return $base_url . '/assets/css/' . $file;
        case 'js':
            return $base_url . '/assets/js/' . $file;
        case 'plugins':
            return $base_url . '/assets/plugins/' . $file;
        default:
            return $base_url . '/assets/img/' . $file;
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
    if ($data === null) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = h($data);
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
    $rows = $db->fetchAll("SELECT * FROM gallery ORDER BY created_at DESC");

    $images = [];
    foreach ($rows as $row) {
        $images[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'path' => $row['image_path']
        ];
    }
    return $images;
}
