<?php
/**
 * AJAX Handler for saving content updates from visual editor
 * Loaded via AjaxController::saveContent() — bootstrap + autoloader already initialized
 */

// Verify admin authentication (only Super Admin or Manager allowed to save content)
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// CSRF validation
$csrf_token = \App\Core\Security::sanitize($_POST['csrf_token'] ?? '');
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security validation failed']);
    exit();
}

// Check if request is AJAX and POST
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed']);
    exit();
}

// Initialize controller (autoloader handles class loading)
$adminController = new \App\Http\Controllers\Admin\AdminController();

// Get POST data
$content = isset($_POST['content']) ? \App\Core\Security::sanitize($_POST['content']) : null;
$pageId = isset($_POST['page_id']) ? (int)\App\Core\Security::sanitize($_POST['page_id']) : null;
$layout = isset($_POST['layout']) ? \App\Core\Security::sanitize($_POST['layout']) : null;

if(!$content || !$pageId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Update content
// Assuming AdminController has updatePageContent or similar
$result = $adminController->updatePageContent($pageId, $content, $layout);

// Send response
header('Content-Type: application/json');
echo json_encode($result);

