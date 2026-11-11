<?php
/**
 * AI Health Check Cron
 * Runs every 15 minutes
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\AI\FreeAIEngines;
use App\Services\AIChatbotService;
use App\Services\AI\ExecutiveAIService;
use App\Core\Database\Database;

echo "=== AI Health Check ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

$issues = [];

try {
    // Check FreeAIEngines
    $engines = FreeAIEngines::getInstance();
    $status = $engines->getStatus();
    
    $healthyEngines = 0;
    foreach ($status as $engine => $state) {
        if ($state['available'] ?? false) {
            $healthyEngines++;
        } else {
            $issues[] = "Engine $engine is unavailable: " . ($state['error'] ?? 'unknown');
        }
    }
    
    echo "AI Engines: $healthyEngines/" . count($status) . " healthy\n";
    
    // Check AIChatbotService
    $chatbot = new AIChatbotService();
    $chatStatus = $chatbot->getStatus();
    if (!($chatStatus['available'] ?? false)) {
        $issues[] = "AIChatbotService unavailable: " . ($chatStatus['error'] ?? 'unknown');
    }
    
    // Check ExecutiveAIService
    $execAI = new ExecutiveAIService();
    $execStatus = $execAI->getHealth();
    if (!($execStatus['healthy'] ?? false)) {
        $issues[] = "ExecutiveAIService unhealthy: " . ($execStatus['error'] ?? 'unknown');
    }
    
    // Check database connectivity
    $db = Database::getInstance()->getConnection();
    $db->query("SELECT 1");
    echo "Database: OK\n";
    
    // Check Redis (if configured)
    try {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->ping();
        echo "Redis: OK\n";
    } catch (Throwable $e) {
        $issues[] = "Redis unavailable: " . $e->getMessage();
    }
    
    // Log issues if any
    if (!empty($issues)) {
        echo "⚠️ Issues found:\n";
        foreach ($issues as $issue) {
            echo "  - $issue\n";
        }
        
        // Alert if critical
        if (count($issues) > 3) {
            // Log critical alert
            error_log("CRITICAL: AI Health check failed with " . count($issues) . " issues");
        }
    } else {
        echo "✅ All systems healthy\n";
    }
    
} catch (\Throwable $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    error_log("CRITICAL: AI Health check crashed: " . $e->getMessage());
    exit(1);
}

echo "=== AI Health Check Completed ===\n";