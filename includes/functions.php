<?php
// Enhanced database connection function with logging and security
function db_connect($retry_attempts = 3) {
    $attempt = 0;
    while ($attempt < $retry_attempts) {
        try {
            // Use persistent connection for better performance
            $conn = mysqli_connect('p:' . DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if (!$conn) {
                throw new Exception("Connection failed: " . mysqli_connect_error());
            }

            // Set connection parameters for security and performance
            mysqli_set_charset($conn, 'utf8mb4');

            // Enable strict mode to catch more potential errors
            mysqli_query($conn, "SET sql_mode = 'STRICT_ALL_TABLES'");

            return $conn;
        } catch (Exception $e) {
            error_log('Database connection attempt ' . ($attempt + 1) . ' failed: ' . $e->getMessage());
            $attempt++;

            // Exponential backoff
            if ($attempt < $retry_attempts) {
                usleep(pow(2, $attempt) * 100000); // Increases wait time between attempts
            }
        }
    }
}

// Helper function to format file sizes
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, 2) . ' ' . $units[$i];
}

// Helper function to get log level colors
function getLogLevelColor($level) {
    $colors = [
        'ERROR' => 'danger',
        'WARNING' => 'warning',
        'INFO' => 'info',
        'DEBUG' => 'secondary',
        'CRITICAL' => 'dark'
    ];

    return $colors[strtoupper($level)] ?? 'secondary';
}

// Helper function to get role colors
function getRoleColor($role) {
    $colors = [
        'admin' => 'danger',
        'agent' => 'primary',
        'customer' => 'success',
        'manager' => 'warning'
    ];

    return $colors[strtolower($role)] ?? 'secondary';
}

// Helper function to sanitize input
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Helper function to generate CSRF token
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// Helper function to validate CSRF token
if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Helper function to validate CSRF token (fallback)
if (!function_exists('validateCSRFTokenFallback')) {
    function validateCSRFTokenFallback($token, $action = 'general') {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Helper function to get user role name
function getRoleName($role) {
    $roles = [
        'admin' => 'Administrator',
        'agent' => 'Real Estate Agent',
        'customer' => 'Customer',
        'manager' => 'Manager'
    ];

    return $roles[$role] ?? ucfirst($role);
}

// Helper function to get status color
function getStatusColor($status) {
    $colors = [
        'active' => 'success',
        'inactive' => 'secondary',
        'pending' => 'warning',
        'suspended' => 'danger',
        'available' => 'success',
        'sold' => 'primary',
        'rented' => 'info'
    ];

    return $colors[strtolower($status)] ?? 'secondary';
}

// Helper function to format currency
function formatCurrency($amount, $currency = '₹') {
    return $currency . number_format($amount, 0, '.', ',');
}

// Helper function to get time ago
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->days > 0) {
        return $diff->days . ' days ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hours ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minutes ago';
    } else {
        return 'Just now';
    }
}

// Helper function to truncate text
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }

    return substr($text, 0, $length) . '...';
}

// Helper function to generate slug
function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// Helper function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Helper function to validate phone number
function isValidPhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

// Helper function to generate random password
function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }

    return $password;
}

// Helper function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Helper function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Helper function to get user avatar
function getUserAvatar($name, $size = 40) {
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&size={$size}";
}

// Helper function to get property type icon
function getPropertyTypeIcon($type) {
    $icons = [
        'apartment' => 'fas fa-building',
        'villa' => 'fas fa-home',
        'house' => 'fas fa-house',
        'commercial' => 'fas fa-store',
        'plot' => 'fas fa-map'
    ];

    return $icons[$type] ?? 'fas fa-home';
}

// Helper function to get lead source color
function getLeadSourceColor($source) {
    $colors = [
        'website' => 'primary',
        'phone' => 'success',
        'email' => 'info',
        'referral' => 'warning',
        'walk-in' => 'secondary'
    ];

    return $colors[strtolower($source)] ?? 'secondary';
}

// Helper function to get booking status color
function getBookingStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'confirmed' => 'success',
        'cancelled' => 'danger',
        'completed' => 'info'
    ];

    return $colors[strtolower($status)] ?? 'secondary';
}

// Helper function to format date
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Helper function to get initials from name
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';

    foreach ($parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return substr($initials, 0, 2);
}

// Advanced input sanitization and validation
function clean_input($data, $type = 'string', $options = []) {
    // Remove whitespace
    $data = trim($data);
    
    // Decode HTML entities to prevent double-encoding
    $data = htmlspecialchars_decode($data, ENT_QUOTES);
    
    // Sanitize based on type
    switch ($type) {
        case 'email':
            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
            if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;
        
        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            $min = $options['min'] ?? PHP_INT_MIN;
            $max = $options['max'] ?? PHP_INT_MAX;
            if (!filter_var($data, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => $min, 'max_range' => $max]
            ])) {
                return false;
            }
            break;
        
        case 'float':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            break;
        
        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL);
            if (!filter_var($data, FILTER_VALIDATE_URL)) {
                return false;
            }
            break;
        
        default: // string
            // Remove potentially dangerous characters
            $data = strip_tags($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

// Enhanced user authentication check with role verification
function is_logged_in($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Optional role-based check
    if ($required_role !== null && $_SESSION['user_role'] !== $required_role) {
        return false;
    }
    
    // Additional security checks
    $inactive_timeout = 1800; // 30 minutes
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity']) > $inactive_timeout) {
        // Destroy session on timeout
        session_unset();
        session_destroy();
        return false;
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
    
    return true;
}

// Advanced redirect with security and logging
function redirect($url, $permanent = false, $log_redirect = true) {
    // Prevent open redirect vulnerabilities
    $allowed_hosts = ['apsdreamhomes.com', 'localhost'];
    $parsed_url = parse_url($url);
    
    if (!in_array($parsed_url['host'] ?? 'localhost', $allowed_hosts)) {
        error_log('Potential open redirect attempt: ' . $url);
        $url = '/'; // Default to homepage
    }
    
    // Log redirect for audit purposes
    if ($log_redirect) {
        error_log('Redirect: ' . $url . ' by user ' . ($_SESSION['user_id'] ?? 'guest'));
    }
    
    // HTTP status code for redirect
    $status_code = $permanent ? 301 : 302;
    
    header('Location: ' . $url, true, $status_code);
    exit();
}

// Backwards-compatible helper used by some admin pages
if (!function_exists('redirectTo')) {
    function redirectTo($url, $permanent = false) {
        // Delegate to the central redirect helper
        redirect($url, $permanent);
    }
}

// CSRF token functions are now in security_functions.php for enhanced security

// Get recent properties
function get_recent_properties($limit = 6) {
  global $conn;
  $query = "
    SELECT 
      p.id,
      p.title,
      p.price,
      p.bedrooms,
      p.bathrooms,
      p.area,
      p.location,
      p.description,
      p.type,
      p.status,
      p.created_at,
      p.updated_at,
      pi.image_path AS main_image
    FROM properties p
    LEFT JOIN property_images pi ON p.id = pi.property_id
    ORDER BY p.created_at DESC LIMIT ?
  ";
  $stmt = $conn->prepare($query);
  $stmt->bind_param('i', $limit);
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get property availability status
function get_property_availability_status($conn, $property_id) {
    $stmt = $conn->prepare("SELECT status FROM properties WHERE id = ?");
    $stmt->bind_param('i', $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['status'] ?? 'unknown';
}
?>
