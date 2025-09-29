<?php
/**
 * APS Dream Home - Utility Functions
 * Useful helper functions for the website
 */

// Database connection utility
function getDatabaseConnection() {
    try {
        require_once 'includes/db_connection.php';
        return getDbConnection();
    } catch (Exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Security utilities
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// File upload utility
function uploadImage($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 2097152) {
    $errors = [];

    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        $errors[] = 'No file uploaded';
        return ['success' => false, 'errors' => $errors];
    }

    // Check file size
    if ($file['size'] > $max_size) {
        $errors[] = 'File size too large. Maximum: ' . ($max_size / 1024 / 1024) . 'MB';
    }

    // Check file type
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        $errors[] = 'Invalid file type. Allowed: ' . implode(', ', $allowed_types);
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Generate unique filename
    $new_filename = uniqid() . '.' . $file_ext;
    $upload_path = 'uploads/images/' . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return [
            'success' => true,
            'filename' => $new_filename,
            'path' => $upload_path,
            'url' => BASE_URL . '/' . $upload_path
        ];
    } else {
        return ['success' => false, 'errors' => ['Failed to upload file']];
    }
}

// Email utility
function sendEmail($to, $subject, $message, $from = 'noreply@apsdreamhome.com') {
    $headers = "From: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($to, $subject, $message, $headers);
}

// Property search utility
function searchProperties($filters = []) {
    $conn = getDatabaseConnection();
    if (!$conn) return [];

    $sql = "SELECT * FROM properties WHERE status = 'active'";
    $params = [];

    if (!empty($filters['city'])) {
        $sql .= " AND city = ?";
        $params[] = $filters['city'];
    }

    if (!empty($filters['property_type'])) {
        $sql .= " AND property_type = ?";
        $params[] = $filters['property_type'];
    }

    if (!empty($filters['min_price'])) {
        $sql .= " AND price >= ?";
        $params[] = $filters['min_price'];
    }

    if (!empty($filters['max_price'])) {
        $sql .= " AND price <= ?";
        $params[] = $filters['max_price'];
    }

    if (!empty($filters['bedrooms'])) {
        $sql .= " AND bedrooms >= ?";
        $params[] = $filters['bedrooms'];
    }

    $sql .= " ORDER BY created_at DESC";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Property search error: " . $e->getMessage());
        return [];
    }
}

// User authentication utility
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: clean_login.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;

    $conn = getDatabaseConnection();
    if (!$conn) return null;

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get current user error: " . $e->getMessage());
        return null;
    }
}

// URL and navigation utilities
function getBaseURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    return rtrim("$protocol://$host$path", '/');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isActivePage($page) {
    $current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
    return $current_page === $page ? 'active' : '';
}

// Date and time utilities
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

// String utilities
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

function slugify($text) {
    $text = preg_replace('/[^\w\s-]/', '', $text);
    $text = str_replace(' ', '-', $text);
    return strtolower(trim($text, '-'));
}

// Number formatting
function formatPrice($price) {
    return '₹' . number_format($price, 0, '.', ',');
}

function formatNumber($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return number_format($number);
}

// Array utilities
function arrayGet($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

function safeArrayAccess($array, $key, $default = '') {
    return is_array($array) && isset($array[$key]) ? $array[$key] : $default;
}

// Session utilities
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type']; // success, error, warning, info
        $message = $flash['message'];
        return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
            $message
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
    }
    return '';
}
?>
