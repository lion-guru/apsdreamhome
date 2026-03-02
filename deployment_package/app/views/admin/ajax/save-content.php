<?php
/**
 * AJAX Handler for saving content updates from visual editor
 */

require_once dirname(__DIR__) . '/core/init.php';

// Verify admin authentication (only Super Admin or Manager allowed to save content)
if (!hasRole('superadmin') && !hasRole('manager')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can save content'))]);
    exit();
}

// CSRF validation
$csrf_token = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Security validation failed'))]);
    exit();
}

require_once dirname(__DIR__) . '/controllers/SuperAdminController.php';

// Check if request is AJAX and POST
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Direct access not allowed'))]);
    exit();
}

// Initialize controller
$superAdmin = new SuperAdminController();

// Get POST data
$content = isset($_POST['content']) ? $_POST['content'] : null;
$pageId = isset($_POST['page_id']) ? (int)$_POST['page_id'] : null;
$layout = isset($_POST['layout']) ? $_POST['layout'] : null;

if(!$content || !$pageId) {
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Missing required parameters'))]);
    exit;
}

// Sanitize content
require_once dirname(__DIR__, 2) . '/includes/functions/common-functions.php';
$content = sanitize_input($content);

// Update content
$result = $superAdmin->updatePageContent($pageId, $content, $layout);

if (isset($result['message'])) {
    $result['message'] = h($mlSupport->translate($result['message']));
}

// Send response
header('Content-Type: application/json');
echo json_encode($result);
