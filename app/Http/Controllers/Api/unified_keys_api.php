<?php
/**
 * Unified Key Management API Endpoint
 * Handles CRUD operations for both MCP and User API keys
 */

require_once "../../../Core/Unified/base.php";

header("Content-Type: application/json");

// Use unified database connection
$pdo = aps_db();

$keyManager = \App\Services\UnifiedKeyManager::getInstance($pdo);

$action = $_GET["action"] ?? "";

switch ($action) {
    case "stats":
        $stats = $keyManager->getSystemStats();
        echo json_encode(["success" => true, "stats" => $stats]);
        break;
        
    case "mcp_keys":
        $keys = $keyManager->getAllMcpKeys();
        echo json_encode(["success" => true, "keys" => $keys]);
        break;
        
    case "user_keys":
        $keys = $keyManager->getAllUserApiKeys();
        echo json_encode(["success" => true, "keys" => $keys]);
        break;
        
    case "integration":
        $integration = [
            "mcp_servers" => [
                ["name" => "Filesystem", "status" => "active", "description" => "File operations"],
                ["name" => "GitKraken", "status" => "active", "description" => "Version control"],
                ["name" => "GitHub", "status" => "active", "description" => "Repository management"],
                ["name" => "MySQL", "status" => "active", "description" => "Database operations"],
                ["name" => "Playwright", "status" => "active", "description" => "Web automation"],
                ["name" => "Postman API", "status" => "active", "description" => "API testing"],
                ["name" => "Puppeteer", "status" => "active", "description" => "Web scraping"],
                ["name" => "Memory", "status" => "active", "description" => "Knowledge management"],
                ["name" => "Sequential Thinking", "status" => "active", "description" => "Step-by-step reasoning"]
            ],
            "api_system" => [
                ["name" => "BaseApiController", "status" => "active", "description" => "API foundation"],
                ["name" => "ApiLeadController", "status" => "active", "description" => "Lead management"],
                ["name" => "ApiKeyManager", "status" => "active", "description" => "Key management"],
                ["name" => "ApiDocumentation", "status" => "active", "description" => "API docs"]
            ]
        ];
        echo json_encode(["success" => true, "integration" => $integration]);
        break;
        
    case "add_mcp_key":
        $keyName = $_POST["mcpKeyName"] ?? "";
        $keyValue = $_POST["mcpKeyValue"] ?? "";
        $keyType = $_POST["mcpKeyType"] ?? "";
        $serviceName = $_POST["mcpServiceName"] ?? "";
        $description = $_POST["mcpDescription"] ?? "";
        
        if (empty($keyName) || empty($keyValue) || empty($keyType) || empty($serviceName)) {
            echo json_encode(["success" => false, "message" => "All required fields must be filled"]);
            break;
        }
        
        $success = $keyManager->storeMcpKey($keyName, $keyValue, $keyType, $serviceName, $description);
        echo json_encode(["success" => $success, "message" => $success ? "Key added successfully" : "Failed to add key"]);
        break;
        
    case "create_user_key":
        $userName = $_POST["userName"] ?? "";
        $userId = $_POST["userId"] ?? "";
        $rateLimit = $_POST["userRateLimit"] ?? 1000;
        $permissions = json_decode($_POST["permissions"] ?? "[]");
        
        if (empty($userName) || empty($userId)) {
            echo json_encode(["success" => false, "message" => "Name and User ID are required"]);
            break;
        }
        
        $apiKey = $keyManager->createUserApiKey($userId, $userName, $permissions, $rateLimit);
        if ($apiKey) {
            echo json_encode(["success" => true, "message" => "API key created", "api_key" => $apiKey]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to create API key"]);
        }
        break;
        
    case "get_mcp_key":
        $keyName = $_GET["keyName"] ?? "";
        if (empty($keyName)) {
            echo json_encode(["success" => false, "message" => "Key name is required"]);
            break;
        }
        
        $keyValue = $keyManager->getMcpKey($keyName);
        echo json_encode(["success" => !empty($keyValue), "value" => $keyValue]);
        break;
        
    case "validate_user_key":
        $apiKey = $_POST["api_key"] ?? "";
        $requiredPermissions = $_POST["permissions"] ?? [];
        
        $validation = $keyManager->validateApiRequest($apiKey, $requiredPermissions);
        echo json_encode($validation);
        break;
        
    case "deactivate_key":
        $keyName = $_POST["keyName"] ?? "";
        $keyType = $_POST["keyType"] ?? "mcp"; // mcp or user
        
        if (empty($keyName)) {
            echo json_encode(["success" => false, "message" => "Key name is required"]);
            break;
        }
        
        if ($keyType === "user") {
            // Deactivate user API key
            $stmt = $pdo->prepare("UPDATE api_keys SET status = 'revoked' WHERE api_key = ?");
            $success = $stmt->execute([$keyName]);
        } else {
            // Deactivate MCP key
            $success = $keyManager->deactivateKey($keyName);
        }
        
        echo json_encode(["success" => $success, "message" => $success ? "Key deactivated" : "Failed to deactivate key"]);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
}
?>
