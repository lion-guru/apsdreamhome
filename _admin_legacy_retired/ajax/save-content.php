<?php
/**
 * AJAX Handler for saving content updates from visual editor
 */

require_once dirname(__DIR__) . '/controllers/SuperAdminController.php';

// Check if request is AJAX and POST
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('Direct access not allowed');
}

// Initialize controller
$superAdmin = new SuperAdminController();

// Get POST data
$content = isset($_POST['content']) ? $_POST['content'] : null;
$pageId = isset($_POST['page_id']) ? (int)$_POST['page_id'] : null;
$layout = isset($_POST['layout']) ? $_POST['layout'] : null;

if(!$content || !$pageId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Sanitize content
require_once dirname(__DIR__, 2) . '/includes/functions/common-functions.php';
$content = sanitize_input($content);

// Update content
$result = $superAdmin->updatePageContent($pageId, $content, $layout);

// Send response
header('Content-Type: application/json');
echo json_encode($result);