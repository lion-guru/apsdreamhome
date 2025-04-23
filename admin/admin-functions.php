<?php
/**
 * Admin Functions for APS Dream Homes
 * Contains functions specific to admin section
 */

// Include common functions if not already included
if (!function_exists('get_asset_url')) {
    include_once(dirname(__DIR__) . '/includes/functions/common-functions.php');}

/**
 * Check if current user is an admin
 * 
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE auser = ? AND status = 1");
    $stmt->execute([$_SESSION['admin_logged_in']]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Check if admin is logged in
 * 
 * @return bool True if admin is logged in, false otherwise
 */
function is_admin_logged_in() {
    return isset($_SESSION['auser']);
}

/**
 * Check if admin is logged in (alias for compatibility)
 * @return bool
 */
function isAdminLoggedIn() {
    return is_admin_logged_in();
}

/**
 * Check if admin has a specific permission (stub, always true or implement as needed)
 * @param string $perm
 * @return bool
 */
function hasPermission($perm) {
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
function get_admin_asset_url($file, $type = 'img') {
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
function format_admin_date($date, $format = 'd M, Y') {
    return date($format, strtotime($date));
}

/**
 * Sanitize admin input
 * 
 * @param string $data The input data to sanitize
 * @return string Sanitized data
 */
function sanitize_admin_input($data) {
    global $con;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    if ($con) {
        $data = mysqli_real_escape_string($con, $data);
    }
    return $data;
}

/**
 * Get gallery images
 *
 * @return array List of gallery images
 */
function getGalleryImages() {
    global $conn;
    $images = [];
    $sql = "SELECT * FROM gallery ORDER BY created_at DESC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $images[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'path' => $row['image_path']
            ];
        }
    }
    return $images;
}