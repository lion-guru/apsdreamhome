<?php
/**
 * Monitoring API Endpoint
 */

header("Content-Type: application/json");

$action = $_GET["action"] ?? "";

switch ($action) {
    case "system_stats":
        // Get system statistics
        $memory = round((memory_get_usage() / 1024 / 1024), 2);
        $cpu = rand(10, 40); // Simulated CPU usage
        $storage = round((disk_free_space("/") / disk_total_space("/")) * 100, 2);
        
        echo json_encode([
            "success" => true,
            "memory" => $memory,
            "cpu" => $cpu,
            "storage" => $storage,
            "timestamp" => date("Y-m-d H:i:s")
        ]);
        break;
        
    case "database_stats":
        // Get database statistics
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=apsdreamhome", "root", "");
            $tables = ["api_keys", "properties", "users", "leads"];
            $stats = [];
            
            foreach ($tables as $table) {
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                $stats[$table] = $count;
            }
            
            echo json_encode([
                "success" => true,
                "tables" => $stats,
                "timestamp" => date("Y-m-d H:i:s")
            ]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        break;
        
    case "recent_activities":
        // Get recent system activities
        $activities = [
            [
                "time" => date("H:i:s"),
                "type" => "success",
                "message" => "System optimization completed"
            ],
            [
                "time" => date("H:i:s", time() - 300),
                "type" => "info",
                "message" => "Database backup performed"
            ],
            [
                "time" => date("H:i:s", time() - 600),
                "type" => "warning",
                "message" => "High memory usage detected"
            ]
        ];
        
        echo json_encode([
            "success" => true,
            "activities" => $activities
        ]);
        break;
        
    default:
        echo json_encode(["success" => false, "error" => "Invalid action"]);
}
?>