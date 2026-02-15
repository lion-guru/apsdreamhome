<?php
require_once('../config.php');
require_once('../config/ai_tools_config.php');

header('Content-Type: application/json');

// Verify admin access
function verifyAdminAccess() {
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }
}

// Log AI tool usage
function logAIToolUsage($tool_name, $user_id, $action, $details) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO " . AI_LOGS_TABLE . " (tool_name, user_id, action, details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tool_name, $user_id, $action, json_encode($details)]);
    } catch (PDOException $e) {
        error_log("Error logging AI tool usage: " . $e->getMessage());
    }
}

// Get AI tool statistics
function getAIToolStats($tool_name) {
    global $pdo;
    $stats = [];
    
    try {
        if ($tool_name === 'chatbot') {
            // Get chatbot statistics
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_chats,
                    AVG(satisfaction_score) as avg_satisfaction,
                    AVG(response_time) as avg_response_time
                FROM " . CHATBOT_TABLE
            );
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif ($tool_name === 'property_description') {
            // Get property description generator statistics
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_descriptions,
                    AVG(word_count) as avg_word_count
                FROM " . PROPERTY_DESC_TABLE
            );
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Error getting AI tool stats: " . $e->getMessage());
    }
    
    return $stats;
}

// Update AI tool settings
function updateAIToolSettings($tool_name, $settings) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE " . AI_SETTINGS_TABLE . " SET settings = ? WHERE tool_name = ?");
        $stmt->execute([json_encode($settings), $tool_name]);
        return true;
    } catch (PDOException $e) {
        error_log("Error updating AI tool settings: " . $e->getMessage());
        return false;
    }
}

// Handle API requests
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_stats':
        verifyAdminAccess();
        $tool_name = $_GET['tool'] ?? '';
        if (!empty($tool_name)) {
            echo json_encode(getAIToolStats($tool_name));
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Tool name is required']);
        }
        break;
        
    case 'update_settings':
        verifyAdminAccess();
        $tool_name = $_POST['tool'] ?? '';
        $settings = $_POST['settings'] ?? '';
        
        if (!empty($tool_name) && !empty($settings)) {
            $settings = json_decode($settings, true);
            if ($settings && updateAIToolSettings($tool_name, $settings)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid settings format']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Tool name and settings are required']);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}