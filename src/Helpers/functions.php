<?php
// Common utility functions and helpers

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $string;
}

/**
 * Format date to Indian format
 */
function format_date_indian($date) {
    return date('d-m-Y', strtotime($date));
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user role
 */
function get_user_role() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if user has permission
 */
function has_permission($required_role) {
    $user_role = get_user_role();
    $role_hierarchy = [
        'admin' => 3,
        'associate' => 2,
        'user' => 1
    ];
    
    return isset($role_hierarchy[$user_role]) && 
           isset($role_hierarchy[$required_role]) && 
           $role_hierarchy[$user_role] >= $role_hierarchy[$required_role];
}

/**
 * Format currency in Indian Rupees
 */
function format_indian_currency($amount) {
    return '₹ ' . number_format($amount, 2);
}

/**
 * Redirect with flash message
 */
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
    header("Location: " . $url);
    exit();
}

/**
 * Get and clear flash message
 */
function get_flash_message() {
    $message = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    return $message;
}

/**
 * Debug function
 */
function debug($data) {
    if (APP_ENV === 'development') {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}