<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../config/ai_tools_config.php';

use App\Core\App;

header('Content-Type: application/json');

// Verify admin access
function verifyAdminAccess($mlSupport) {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => h($mlSupport->translate('Forbidden: Admin access required'))]);
        exit;
    }
}

// Log AI tool usage
function logAIToolUsage($tool_name, $user_id, $action, $details) {
    $db = \App\Core\App::database();
    try {
        $db->insert(AI_LOGS_TABLE, [
            'tool_name' => h($tool_name),
            'user_id' => intval($user_id),
            'action' => h($action),
            'details' => json_encode($details)
        ]);
    } catch (\Exception $e) {
        error_log("Error logging AI tool usage: " . $e->getMessage());
    }
}

// Get AI tool statistics
function getAIToolStats($tool_name, $mlSupport) {
    $db = \App\Core\App::database();
    $stats = [];

    try {
        if ($tool_name === 'chatbot') {
            // Get chatbot statistics
            $sql = "
                SELECT
                    COUNT(*) as total_chats,
                    AVG(satisfaction_score) as avg_satisfaction,
                    AVG(response_time) as avg_response_time
                FROM " . CHATBOT_TABLE;
            $stats = $db->fetch($sql);
        } elseif ($tool_name === 'property_description') {
            // Get property description generator statistics
            $sql = "
                SELECT
                    COUNT(*) as total_descriptions,
                    AVG(word_count) as avg_word_count
                FROM " . PROPERTY_DESC_TABLE;
            $stats = $db->fetch($sql);
        }
    } catch (\Exception $e) {
        error_log("Error getting AI tool stats: " . $e->getMessage());
        return ['error' => h($mlSupport->translate('Error retrieving stats'))];
    }

    return $stats;
}

// Update AI tool settings
function updateAIToolSettings($tool_name, $settings) {
    $db = \App\Core\App::database();
    try {
        $db->execute("UPDATE " . AI_SETTINGS_TABLE . " SET settings = :settings WHERE tool_name = :tool_name", [
            'settings' => json_encode($settings),
            'tool_name' => $tool_name
        ]);
        return true;
    } catch (\Exception $e) {
        error_log("Error updating AI tool settings: " . $e->getMessage());
        return false;
    }
}

// Handle API requests
$action = $_GET['action'] ?? '';

// Global CSRF validation for state-changing requests or sensitive GETs
$csrf_token = $_GET['csrf_token'] ?? $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['error' => h($mlSupport->translate('Security validation failed'))]);
    exit();
}

switch ($action) {
    case 'get_stats':
        verifyAdminAccess($mlSupport);
        $tool_name = $_GET['tool'] ?? '';
        if (!empty($tool_name)) {
            echo json_encode(getAIToolStats($tool_name, $mlSupport));
        } else {
            http_response_code(400);
            echo json_encode(['error' => h($mlSupport->translate('Tool name is required'))]);
        }
        break;

    case 'update_settings':
        verifyAdminAccess($mlSupport);

        // RBAC: Only superadmin or manager can update settings
        if (!hasRole('superadmin') && !hasRole('manager')) {
            http_response_code(403);
            echo json_encode(['error' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can update settings'))]);
            exit();
        }

        $tool_name = $_POST['tool'] ?? '';
        $settings = $_POST['settings'] ?? '';

        if (!empty($tool_name) && !empty($settings)) {
            $settings = json_decode($settings, true);
            if ($settings && updateAIToolSettings($tool_name, $settings)) {
                echo json_encode(['success' => true, 'message' => h($mlSupport->translate('Settings updated successfully'))]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => h($mlSupport->translate('Invalid settings format or update failed'))]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => h($mlSupport->translate('Tool name and settings are required'))]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => h($mlSupport->translate('Invalid action'))]);
        break;
}
