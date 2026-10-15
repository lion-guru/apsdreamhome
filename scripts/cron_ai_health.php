<?php
/**
 * AI System Health Check Cron
 * Runs every 15 minutes to monitor AI system health
 * Usage: php scripts/cron_ai_health.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/bootstrap.php';

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

echo "[" . date('Y-m-d H:i:s') . "] AI Health Check...\n";

try {
    TenantContext::setById(1);
    $db = Database::getInstance();
    $alerts = [];
    
    // 1. Check Ollama availability
    $ollamaOk = false;
    try {
        $ch = curl_init('http://localhost:11434/api/tags');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 3]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $ollamaOk = ($httpCode === 200);
    } catch (\Throwable $e) {
        $ollamaOk = false;
    }
    
    if (!$ollamaOk) {
        $alerts[] = ['type' => 'ollama_down', 'message' => 'Ollama server not responding on localhost:11434', 'priority' => 'critical'];
    } else {
        echo "  ✅ Ollama: OK\n";
    }
    
    // 2. Check AI API logs for recent errors (last 15 min)
    $errorCount = (int)($db->fetch(
        "SELECT COUNT(*) as c FROM ai_api_logs 
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) 
         AND (status_code != 200 OR status_code IS NULL)"
    )['c'] ?? 0);
    
    if ($errorCount > 10) {
        $alerts[] = ['type' => 'high_error_rate', 'message' => "$errorCount AI API errors in last 15 minutes", 'priority' => 'high'];
    } elseif ($errorCount > 0) {
        echo "  ⚠️ AI API Errors (15min): $errorCount\n";
    } else {
        echo "  ✅ AI API: No errors\n";
    }
    
    // 3. Check Agentic AI cron last run
    $lastAgentic = $db->fetch(
        "SELECT MAX(created_at) as last_run FROM ai_alerts WHERE type = 'agentic_cron'"
    );
    
    if ($lastAgentic && $lastAgentic['last_run']) {
        $diff = time() - strtotime($lastAgentic['last_run']);
        if ($diff > 600) { // 10 minutes
            $alerts[] = ['type' => 'agentic_cron_stale', 'message' => "Agentic AI cron hasn't run in " . round($diff/60) . " minutes", 'priority' => 'high'];
        } else {
            echo "  ✅ Agentic AI Cron: Last run " . round($diff/60) . " min ago\n";
        }
    } else {
        $alerts[] = ['type' => 'agentic_cron_never', 'message' => 'Agentic AI cron has never run', 'priority' => 'critical'];
    }
    
    // 4. Check Auto-Dialer cron last run (during working hours)
    $hour = (int)date('H');
    if ($hour >= 9 && $hour < 20) {
        $lastDialer = $db->fetch(
            "SELECT MAX(created_at) as last_run FROM ai_alerts WHERE type = 'autodialer_cron'"
        );
        
        if ($lastDialer && $lastDialer['last_run']) {
            $diff = time() - strtotime($lastDialer['last_run']);
            if ($diff > 300) { // 5 minutes
                $alerts[] = ['type' => 'autodialer_cron_stale', 'message' => "Auto-Dialer cron hasn't run in " . round($diff/60) . " minutes", 'priority' => 'medium'];
            } else {
                echo "  ✅ Auto-Dialer: Last run " . round($diff/60) . " min ago\n";
            }
        } else {
            $alerts[] => ['type' => 'autodialer_cron_never', 'message' => 'Auto-Dialer cron has never run', 'priority' => 'high'];
        }
    }
    
    // 5. Check pending calls stuck in processing
    $stuckCalls = (int)($db->fetch(
        "SELECT COUNT(*) as c FROM ai_call_sessions 
         WHERE status = 'in_progress' 
         AND started_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
    )['c'] ?? 0);
    
    if ($stuckCalls > 0) {
        $alerts[] = ['type' => 'stuck_calls', 'message' => "$stuckCalls calls stuck in progress > 10 min", 'priority' => 'high'];
    }
    
    // 6. Check API key configuration
    $config = $db->fetch("SELECT groq_api_key, openrouter_api_key, api_key as gemini_key FROM ai_settings WHERE id = 1");
    $missingKeys = [];
    if (empty($config['groq_api_key'])) $missingKeys[] = 'Groq';
    if (empty($config['openrouter_api_key'])) $missingKeys[] = 'OpenRouter';
    if (empty($config['gemini_key'])) $missingKeys[] = 'Gemini';
    
    if (!empty($missingKeys)) {
        $alerts[] = ['type' => 'missing_api_keys', 'message' => 'Missing API keys: ' . implode(', ', $missingKeys), 'priority' => 'high'];
    } else {
        echo "  ✅ API Keys: All configured\n";
    }
    
    // 7. Check disk space for logs
    $diskFree = disk_free_space('C:/') / (1024*1024*1024); // GB
    if ($diskFree < 1) {
        $alerts[] = ['type' => 'disk_space', 'message' => "Low disk space: {$diskFree}GB free", 'priority' => 'critical'];
    } else {
        echo "  ✅ Disk Space: {$diskFree}GB free\n";
    }
    
    // Log alerts
    foreach ($alerts as $alert) {
        $db->execute(
            "INSERT INTO ai_alerts (type, message, priority, created_at) VALUES (?, ?, ?, NOW())",
            [$alert['type'], $alert['message'], $alert['priority']]
        );
        echo "  🚨 {$alert['priority']}: {$alert['message']}\n";
    }
    
    if (empty($alerts)) {
        echo "  ✅ All systems healthy\n";
    }
    
    exit(empty($alerts) ? 0 : 1);
    
} catch (\Throwable $e) {
    $errorMsg = "AI Health Check Fatal: " . $e->getMessage();
    echo "[" . date('Y-m-d H:i:s') . "] $errorMsg\n";
    error_log($errorMsg);
    
    try {
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO ai_alerts (type, message, priority, created_at) VALUES ('health_check_fatal', ?, 'critical', NOW())",
            [$errorMsg]
        );
    } catch (\Throwable $e2) {}
    
    exit(1);
}