<?php
/**
 * Agent Orchestrator Cron
 *
 * Runs all 8 AI agents to scan business data, create tasks,
 * generate insights, and escalate critical issues.
 *
 * Usage: php scripts/cron_agent_orchestrator.php [--agent=lead_gen|sales|...] [--dry-run]
 *
 * Schedule: every 15 minutes
 */

require_once __DIR__ . '/../app/Core/autoload.php';

use App\Services\AgenticAI\AgentOrchestrator;

$startTime = microtime(true);
echo "=== Agentic AI Orchestrator ===\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

$specificAgent = null;
foreach ($argv ?? [] as $arg) {
    if (str_starts_with($arg, '--agent=')) {
        $specificAgent = substr($arg, 8);
    }
}

try {
    $orchestrator = new AgentOrchestrator();

    if ($specificAgent) {
        echo "Running single agent: $specificAgent...\n";
        $result = $orchestrator->runAgent($specificAgent);
        echo "  " . json_encode($result) . "\n";
    } else {
        echo "Running all agents...\n";
        $results = $orchestrator->runAll();
        foreach ($results as $type => $result) {
            $status = isset($result['error']) ? 'ERROR' : 'OK';
            echo "  [$status] $type: " . (isset($result['error']) ? $result['error'] : count($result) . ' tasks') . "\n";
        }
    }

    $elapsed = round(microtime(true) - $startTime, 2);
    echo "\nCompleted in {$elapsed}s\n";
} catch (Exception $e) {
    echo "FATAL: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}?>