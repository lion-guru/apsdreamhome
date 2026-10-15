<?php
/**
 * Agentic AI Cron Job
 * Runs all 8 autonomous agents every 5 minutes
 * Usage: php scripts/cron_agentic_ai.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/bootstrap.php';

use App\Services\AgenticAI\AgentOrchestrator;
use App\Core\Middleware\TenantContext;

echo "[" . date('Y-m-d H:i:s') . "] Starting Agentic AI Orchestrator...\n";

try {
    // Set tenant context (default tenant = 1)
    TenantContext::setById(1);
    
    $orchestrator = new AgentOrchestrator();
    $results = $orchestrator->runAll();
    
    $summary = [];
    $hasErrors = false;
    
    foreach ($results as $type => $result) {
        if (isset($result['error'])) {
            $summary[] = "$type: ERROR - {$result['error']}";
            $hasErrors = true;
            error_log("Agentic AI [$type] ERROR: {$result['error']}");
        } elseif (is_array($result)) {
            $count = count($result);
            $summary[] = "$type: $count tasks completed";
            echo "  ✅ $type: $count actions\n";
        } else {
            $summary[] = "$type: completed";
            echo "  ✅ $type: completed\n";
        }
    }
    
    $message = implode(' | ', $summary);
    echo "[" . date('Y-m-d H:i:s') . "] $message\n";
    
    // Log to database for monitoring
    try {
        $db = \App\Core\Database\Database::getInstance();
        $db->execute(
            "INSERT INTO ai_alerts (type, message, priority, created_at) VALUES (?, ?, ?, NOW())",
            ['agentic_cron', $message, $hasErrors ? 'high' : 'normal']
        );
    } catch (\Throwable $e) {
        // Silent fail for logging
    }
    
    exit($hasErrors ? 1 : 0);
    
} catch (\Throwable $e) {
    $errorMsg = "Agentic AI Cron Fatal: " . $e->getMessage();
    echo "[" . date('Y-m-d H:i:s') . "] $errorMsg\n";
    error_log($errorMsg);
    
    try {
        $db = \App\Core\Database\Database::getInstance();
        $db->execute(
            "INSERT INTO ai_alerts (type, message, priority, created_at) VALUES (?, ?, 'critical', NOW())",
            ['agentic_cron_fatal', $errorMsg]
        );
    } catch (\Throwable $e2) {}
    
    exit(1);
}