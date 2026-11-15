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
    
    // Check AIChatbotService — status() returns provider map; need >=1 available provider
    $chatbot = new AIChatbotService();
    $chatStatus = $chatbot->getStatus();
    $availableProviders = 0;
    foreach ($chatStatus as $provider => $state) {
        if (is_array($state) && (!empty($state['available']))) { $availableProviders++; }
    }
    if ($availableProviders === 0) {
        $issues[] = "AIChatbotService: no available provider";
    }
    
    // Check ExecutiveAIService — healthCheck() returns ['status' => HEALTHY|..., ...]
    $execAI = \App\Services\AI\ExecutiveAIService::getInstance();
    $execStatus = $execAI->healthCheck();
    $execOk = (($execStatus['status'] ?? '') === 'HEALTHY');
    if (!$execOk) {
        $issues[] = "ExecutiveAIService unhealthy: " . json_encode($execStatus);
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

    // Write AI-degraded flag: healthy when at least one production-relevant engine is available
    // (optional/non-deployed engines like ollama, xai, deepseek, together, cohere are ignored).
    try {
        $topEngines = ['groq', 'opencode_zen', 'gemini', 'openrouter', 'huggingface'];
        $topHealthy = 0;
        foreach ($topEngines as $t) {
            if (is_array($status[$t] ?? null) && !empty($status[$t]['available'])) { $topHealthy++; }
        }
        $degraded = ($topHealthy >= 1) ? '0' : '1';
        $pdo2 = Database::getInstance()->getConnection();
        $upd = $pdo2->prepare("UPDATE service_configs SET config_value = ?, updated_at = NOW() WHERE service_name = 'ai_health' AND config_key = 'degraded'");
        $upd->execute([$degraded]);
        echo "AI health flag -> degraded=$degraded (top=$topHealthy)\n";
    } catch (Throwable $e) {
        echo "WARN: could not write ai_health flag: " . $e->getMessage() . "\n";
    }
    
} catch (\Throwable $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    error_log("CRITICAL: AI Health check crashed: " . $e->getMessage());
    exit(1);
}

echo "=== AI Health Check Completed ===\n";