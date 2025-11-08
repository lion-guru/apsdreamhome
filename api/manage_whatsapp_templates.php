<?php
/**
 * APS Dream Home - WhatsApp Template Management API
 * API for creating, updating, and deleting WhatsApp templates
 */

require_once '../includes/config.php';
require_once '../includes/whatsapp_templates.php';

header('Content-Type: application/json');

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        handleCreateTemplate();
        break;
    case 'PUT':
        handleUpdateTemplate();
        break;
    case 'DELETE':
        handleDeleteTemplate();
        break;
    case 'GET':
        handleGetTemplates();
        break;
    default:
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

/**
 * Handle template creation
 */
function handleCreateTemplate() {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }

    $name = $input['name'] ?? '';
    $category = $input['category'] ?? '';
    $header = $input['header'] ?? '';
    $body = $input['body'] ?? '';
    $footer = $input['footer'] ?? '';
    $variables = $input['variables'] ?? [];

    if (empty($name) || empty($body)) {
        echo json_encode(['error' => 'Template name and body are required']);
        return;
    }

    try {
        $template_manager = getWhatsAppTemplateManager();
        $template = $template_manager->createTemplate($name, [
            'category' => $category,
            'header' => $header,
            'body' => $body,
            'footer' => $footer,
            'variables' => $variables
        ]);

        echo json_encode(['success' => true, 'template' => $template]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to create template: ' . $e->getMessage()]);
    }
}

/**
 * Handle template update
 */
function handleUpdateTemplate() {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }

    $name = $input['name'] ?? '';
    $data = $input;

    unset($data['name']);

    if (empty($name)) {
        echo json_encode(['error' => 'Template name is required']);
        return;
    }

    try {
        $template_manager = getWhatsAppTemplateManager();
        $template = $template_manager->updateTemplate($name, $data);

        if ($template) {
            echo json_encode(['success' => true, 'template' => $template]);
        } else {
            echo json_encode(['error' => 'Template not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to update template: ' . $e->getMessage()]);
    }
}

/**
 * Handle template deletion
 */
function handleDeleteTemplate() {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }

    $name = $input['name'] ?? '';

    if (empty($name)) {
        echo json_encode(['error' => 'Template name is required']);
        return;
    }

    try {
        $template_manager = getWhatsAppTemplateManager();
        $deleted = $template_manager->deleteTemplate($name);

        if ($deleted) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Template not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to delete template: ' . $e->getMessage()]);
    }
}

/**
 * Handle get templates request
 */
function handleGetTemplates() {
    try {
        $template_manager = getWhatsAppTemplateManager();
        $templates = $template_manager->getAllTemplates();

        echo json_encode(['success' => true, 'templates' => $templates]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to get templates: ' . $e->getMessage()]);
    }
}
