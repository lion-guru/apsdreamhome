<?php
/**
 * Check if user is authenticated
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in
$isAuthenticated = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Return response
echo json_encode([
    'authenticated' => $isAuthenticated,
    'user_id' => $_SESSION['user_id'] ?? null,
    'user_role' => $_SESSION['user_role'] ?? null,
    'user_name' => $_SESSION['user_name'] ?? null
]);

exit;
?>
