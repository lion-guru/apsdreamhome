<?php
/**
 * Agentic AI Cron Job
 * Runs all 8 autonomous agents every 5 minutes
 * Usage: php scripts/cron_agentic_ai.php [--dry-run]
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/bootstrap.php';

use App\Services\AgenticAI\AgentOrchestrator;
use App\Core\Middleware\TenantContext;
use App\Services\VoiceProviderManager;

echo "[" . date('Y-m-d H:i:s') . "] Starting Agentic AI Orchestrator...\n";

$dryRun = in_array('--dry-run', $argv, true);

try {
    // Set tenant context (default tenant = 1)
    TenantContext::setById(1);

    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getConnection();
    if ($dryRun) {
        $pdo->beginTransaction();
        echo "DRY-RUN MODE: everything runs inside a transaction that is rolled back — no rows persist.\n";
    }

    try {
        $orchestrator = new AgentOrchestrator();
        $results = $orchestrator->runAll();
    } finally {
        if ($dryRun && $pdo->inTransaction()) {
            $pdo->rollBack();
            echo "ROLLBACK issued — dry-run complete, nothing was written.\n";
        }
    }

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
    
    // Log to database for monitoring (skipped in dry-run: persist nothing)
    if (!$dryRun) {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $db->execute(
                "INSERT INTO ai_alerts (alert_type, severity, message, tenant_id, created_at) VALUES (?, ?, ?, 1, NOW())",
                ['agentic_cron', $hasErrors ? 'high' : 'low', $message]
            );
        } catch (\Throwable $e) {
            // Silent fail for logging
        }
    }
    
    // Check Voice Provider status if agents need voice capabilities
    if (!$dryRun) {
        try {
            $voiceManager = new VoiceProviderManager();
            $providerStatus = $voiceManager->getProvidersStatus();
            $configured = 0;
            foreach ($providerStatus as $kp => $p) {
                if (!empty($p['configured'])) { $configured++; }
            }
            echo "  Voice/AI providers configured: {$configured}/" . count($providerStatus) . "\n";
        } catch (\Throwable $e) {
            echo "  ⚠️ Voice Provider check failed: " . $e->getMessage() . "\n";
        }
    }
    
    exit($hasErrors ? 1 : 0);
    
} catch (\Throwable $e) {
    $errorMsg = "Agentic AI Cron Fatal: " . $e->getMessage();
    echo "[" . date('Y-m-d H:i:s') . "] $errorMsg\n";
    error_log($errorMsg);
    
    try {
        $db = \App\Core\Database\Database::getInstance();
        $db->execute(
            "INSERT INTO ai_alerts (alert_type, severity, message, tenant_id, created_at) VALUES (?, 'critical', ?, 1, NOW())",
            ['agentic_cron_fatal', $errorMsg]
        );
    } catch (\Throwable $e2) {}
    
    exit(1);
}