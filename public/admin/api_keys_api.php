<?php
/**
 * API Keys Management API Endpoint
 */
require_once "../config/KeyManager.php";

header("Content-Type: application/json");

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "apsdreamhome";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$keyManager = KeyManager::getInstance($pdo);

$action = $_GET["action"] ?? "";

switch ($action) {
    case "list":
        $keys = $keyManager->getAllKeys();
        $stats = $keyManager->getKeyStats();
        
        $totalKeys = count($keys);
        $activeKeys = array_filter($keys, fn($k) => $k["is_active"]);
        $totalUsage = array_sum(array_column($keys, "usage_count"));
        
        echo json_encode([
            "success" => true,
            "keys" => $keys,
            "stats" => [
                "totalKeys" => $totalKeys,
                "activeKeys" => count($activeKeys),
                "totalServices" => count($stats),
                "totalUsage" => $totalUsage
            ]
        ]);
        break;
        
    case "add":
        $keyName = $_POST["keyName"] ?? "";
        $keyValue = $_POST["keyValue"] ?? "";
        $keyType = $_POST["keyType"] ?? "";
        $serviceName = $_POST["serviceName"] ?? "";
        $description = $_POST["description"] ?? "";
        
        if (empty($keyName) || empty($keyValue) || empty($keyType) || empty($serviceName)) {
            echo json_encode(["success" => false, "message" => "All required fields must be filled"]);
            break;
        }
        
        $success = $keyManager->storeKey($keyName, $keyValue, $keyType, $serviceName, $description);
        echo json_encode(["success" => $success]);
        break;
        
    case "get":
        $keyName = $_GET["keyName"] ?? "";
        if (empty($keyName)) {
            echo json_encode(["success" => false, "message" => "Key name is required"]);
            break;
        }
        
        $keyValue = $keyManager->getKey($keyName);
        echo json_encode(["success" => !empty($keyValue), "value" => $keyValue]);
        break;
        
    case "deactivate":
        $keyName = $_POST["keyName"] ?? "";
        if (empty($keyName)) {
            echo json_encode(["success" => false, "message" => "Key name is required"]);
            break;
        }
        
        $success = $keyManager->deactivateKey($keyName);
        echo json_encode(["success" => $success]);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
}
?>