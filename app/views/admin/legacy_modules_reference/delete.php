<?php
/**
 * Generic Delete Script
 * Handles deletion of various entities with security hardening
 */

require_once __DIR__ . '/core/init.php';

// Ensure user is authorized
if (!isAuthenticated() || !isAdmin()) {
    http_response_code(403);
    die("Error: Unauthorized access.");
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Error: Invalid request method. Deletion must be performed via POST.");
}

// Validate CSRF Token
if (!validateCsrfToken()) {
    http_response_code(403);
    die("Error: CSRF token validation failed.");
}

// Validate inputs
if (!isset($_POST['type']) || !isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    die("Error: Missing or invalid parameters.");
}

$type = $_POST['type'];
$id = (int)$_POST['id'];

// Map types to tables and primary keys
$typeMap = [
    'property' => ['table' => 'properties', 'pk' => 'id', 'redirect' => 'propertyview.php'],
    'city' => ['table' => 'city', 'pk' => 'cid', 'redirect' => 'cityview.php'],
    'state' => ['table' => 'state', 'pk' => 'sid', 'redirect' => 'stateview.php'],
    'user' => ['table' => 'users', 'pk' => 'uid', 'redirect' => 'userlist.php'],
    'employee' => ['table' => 'employees', 'pk' => 'id', 'redirect' => 'employees.php'],
    'contact' => ['table' => 'contact', 'pk' => 'cid', 'redirect' => 'contactview.php'],
    'feedback' => ['table' => 'feedback', 'pk' => 'fid', 'redirect' => 'feedbackview.php'],
    'about' => ['table' => 'about', 'pk' => 'id', 'redirect' => 'aboutview.php'],
    'news' => ['table' => 'news', 'pk' => 'id', 'redirect' => 'news.php'],
    'project' => ['table' => 'projects', 'pk' => 'id', 'redirect' => 'projects.php'],
    'admin' => ['table' => 'admin', 'pk' => 'id', 'redirect' => 'adminlist.php']
];

if (!array_key_exists($type, $typeMap)) {
    http_response_code(400);
    die("Error: Invalid entity type.");
}

$config = $typeMap[$type];
$table = $config['table'];
$pk = $config['pk'];
$redirect = $config['redirect'];

// Perform deletion using singleton
try {
    $db = \App\Core\App::database();
    $query = "DELETE FROM `$table` WHERE `$pk` = ?";
    
    if ($db->execute($query, [$id])) {
        // Redirect with success message
        header("Location: $redirect?msg=Record deleted successfully");
        exit();
    } else {
        header("Location: $redirect?error=Failed to delete record");
        exit();
    }
} catch (Exception $e) {
    header("Location: $redirect?error=" . urlencode("Database error: " . $e->getMessage()));
    exit();
}

