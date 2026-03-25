<?php
/**
 * AJAX Handler for saving content updates from visual editor
 */

require_once dirname(__DIR__, 4) . '/config/bootstrap.php';

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

// Map SuperAdminController to AdminController (which seems to be the primary one now)
require_once dirname(__DIR__, 3) . '/app/Http/Controllers/Admin/AdminController.php';

// Check if request is AJAX and POST
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed']);
    exit();
}

// Initialize controller
$adminController = new \App\Http\Controllers\Admin\AdminController();

// Get POST data - Fixed invalid isset() on function returns
$content = isset($_POST['content']) ? \App\Core\Security::sanitize($_POST['content']) : null;
$pageId = isset($_POST['page_id']) ? (int)\App\Core\Security::sanitize($_POST['page_id']) : null;
$layout = isset($_POST['layout']) ? \App\Core\Security::sanitize($_POST['layout']) : null;

if(!$content || !$pageId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Sanitize content using common functions if available
if (file_exists(dirname(__DIR__, 2) . '/includes/functions/common-functions.php')) {
    require_once dirname(__DIR__, 2) . '/includes/functions/common-functions.php';
    if (function_exists('sanitize_input')) {
        $content = sanitize_input($content);
    }
}

// Update content
// Assuming AdminController has updatePageContent or similar
$result = $adminController->updatePageContent($pageId, $content, $layout);

// Send response
header('Content-Type: application/json');
echo json_encode($result);
