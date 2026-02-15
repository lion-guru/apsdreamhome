<?php
require_once __DIR__ . '/../core/init.php';

// Verify admin authentication
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => h($mlSupport->translate('Unauthorized access'))]);
    exit();
}

// CSRF validation
$csrf_token = $_GET['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['error' => h($mlSupport->translate('Security validation failed'))]);
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => h($mlSupport->translate('Invalid component ID'))]);
    exit;
}

$db = \App\Core\App::database();
$layoutTemplate = new Admin\Models\LayoutTemplate($db->getConnection());

try {
    $component = $layoutTemplate->getById($_GET['id']);
    if (!$component) {
        http_response_code(404);
        echo json_encode(['error' => h($mlSupport->translate('Component not found'))]);
        exit;
    }

    // Wrap the component content in a div with necessary classes and attributes
    $wrappedContent = sprintf(
        '<div class="template-component custom-component" data-type="custom" data-component-id="%d">%s</div>',
        intval($component['id']),
        $component['content'] // Content is likely HTML from visual editor, so not sanitizing with h() here but ensuring ID is int
    );

    echo $wrappedContent;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => h($mlSupport->translate('Failed to load component'))]);
}
