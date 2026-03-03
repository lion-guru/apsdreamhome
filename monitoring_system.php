<?php
/**
 * APS Dream Home - Monitoring System
 * Real-time system monitoring and alerting
 */

class MonitoringSystem {
    private $alerts = [];
    
    public function checkSystemHealth() {
        $checks = [
            "disk_space" => $this->checkDiskSpace(),
            "memory_usage" => $this->checkMemoryUsage(),
            "database_status" => $this->checkDatabaseStatus(),
            "error_logs" => $this->checkErrorLogs(),
            "performance" => $this->checkPerformance()
        ];
        
        return $checks;
    }
    
    private function checkDiskSpace() {
        $free = disk_free_space(__DIR__);
        $total = disk_total_space(__DIR__);
        $percentage = round(($free / $total) * 100, 1);
        
        return [
            "status" => $percentage > 10 ? "OK" : "CRITICAL",
            "free_space" => $this->formatBytes($free),
            "percentage" => $percentage
        ];
    }
    
    private function checkMemoryUsage() {
        $usage = memory_get_usage(true);
        $limit = $this->parseMemoryLimit(ini_get("memory_limit"));
        $percentage = round(($usage / $limit) * 100, 1);
        
        return [
            "status" => $percentage < 80 ? "OK" : "WARNING",
            "usage" => $this->formatBytes($usage),
            "percentage" => $percentage
        ];
    }
    
    private function checkDatabaseStatus() {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=apsdreamhome", "root", "");
            $pdo->query("SELECT 1");
            return ["status" => "OK", "message" => "Database connected"];
        } catch (PDOException $e) {
            return ["status" => "CRITICAL", "message" => $e->getMessage()];
        }
    }
    
    private function checkErrorLogs() {
        $errorLog = __DIR__ . "/logs/error.log";
        if (file_exists($errorLog)) {
            $errors = count(file($errorLog));
            return [
                "status" => $errors < 10 ? "OK" : "WARNING",
                "error_count" => $errors
            ];
        }
        return ["status" => "OK", "error_count" => 0];
    }
    
    private function checkPerformance() {
        $start = microtime(true);
        // Simple performance test
        $pdo = new PDO("mysql:host=localhost;dbname=apsdreamhome", "root", "");
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $stmt->fetch();
        $time = round((microtime(true) - $start) * 1000, 2);
        
        return [
            "status" => $time < 100 ? "OK" : "SLOW",
            "response_time" => $time . "ms"
        ];
    }
    
    private function formatBytes($bytes) {
        $units = ["B", "KB", "MB", "GB"];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 1) . " " . $units[$pow];
    }
    
    private function parseMemoryLimit($limit) {
        $limit = strtolower($limit);
        $multiplier = 1;
        
        if (strpos($limit, "g") !== false) $multiplier = 1024 * 1024 * 1024;
        elseif (strpos($limit, "m") !== false) $multiplier = 1024 * 1024;
        elseif (strpos($limit, "k") !== false) $multiplier = 1024;
        
        return (int) $limit * $multiplier;
    }
}

// Usage example
$monitor = new MonitoringSystem();
$health = $monitor->checkSystemHealth();

echo "📊 SYSTEM HEALTH STATUS:\n";
foreach ($health as $check => $result) {
    $icon = $result["status"] === "OK" ? "✅" : ($result["status"] === "WARNING" ? "⚠️" : "❌");
    echo "$icon " . ucwords($check) . ": " . $result["status"] . "\n";
}
?>