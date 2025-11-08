<?php
require_once '../config.php';
require_once '../admin-functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid component ID']);
    exit;
}

$db = getDBConnection();
$layoutTemplate = new Admin\Models\LayoutTemplate($db);

try {
    $component = $layoutTemplate->getById($_GET['id']);
    if (!$component) {
        http_response_code(404);
        echo json_encode(['error' => 'Component not found']);
        exit;
    }

    // Wrap the component content in a div with necessary classes and attributes
    $wrappedContent = sprintf(
        '<div class="template-component custom-component" data-type="custom" data-component-id="%d">%s</div>',
        $component['id'],
        $component['content']
    );

    echo $wrappedContent;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load component']);
}