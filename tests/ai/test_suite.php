<?php
/**
 * Comprehensive AI System Test Suite
 */
require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/../../includes/ai/AIManager.php';

header('Content-Type: text/plain');

$conn = getDbConnection();
$aiManager = new AIManager($conn);

echo "--- AI System Test Suite ---\n\n";

// 1. Test NLP Processor
echo "[1] Testing NLP Processor...\n";
$nlpResult = $aiManager->understand("Hello, I am interested in a 3BHK villa in Sector 12.");
echo "Intent: " . ($nlpResult['intent']['name'] ?? 'unknown') . " (Confidence: " . ($nlpResult['intent']['confidence'] ?? 0) . ")\n";
echo "Sentiment: " . ($nlpResult['sentiment']['label'] ?? 'unknown') . "\n";
echo "Result: " . ($nlpResult ? "PASS" : "FAIL") . "\n\n";

// 2. Test Data Analyst
echo "[2] Testing Data Analyst...\n";
$analystResult = $aiManager->analyze("system_health", ["limit" => 5]);
echo "Analysis Type: " . ($analystResult['type'] ?? 'unknown') . "\n";
echo "Summary: " . ($analystResult['summary'] ?? 'N/A') . "\n";
echo "Result: " . ($analystResult ? "PASS" : "FAIL") . "\n\n";

// 3. Test Decision Engine
echo "[3] Testing Decision Engine...\n";
$decisionResult = $aiManager->decide("lead_prioritization", ["budget" => 7500000, "intent" => "high_interest"]);
echo "Decision: " . ($decisionResult['decision'] ?? 'unknown') . "\n";
echo "Reason: " . ($decisionResult['reason'] ?? 'N/A') . "\n";
echo "Result: " . ($decisionResult ? "PASS" : "FAIL") . "\n\n";

// 4. Test Code Assistant
echo "[4] Testing Code Assistant...\n";
$coderResult = $aiManager->codeAssist("generate_snippet", ["task" => "Create a PHP function to connect to MySQL", "lang" => "php"]);
echo "Generated Snippet Length: " . strlen($coderResult['code'] ?? '') . " chars\n";
echo "Result: " . ($coderResult ? "PASS" : "FAIL") . "\n\n";

// 5. Test Workflow Engine (Graph-based)
echo "[5] Testing Workflow Engine (Graph)...\n";
$testWorkflow = [
    'nodes' => [
        ['id' => '1', 'name' => 'Webhook', 'type' => 'trigger', 'config' => [], 'pos' => ['x' => 0, 'y' => 0]],
        ['id' => '2', 'name' => 'AI Processor', 'type' => 'agent', 'config' => ['task_type' => 'decide'], 'pos' => ['x' => 200, 'y' => 0]],
        ['id' => '3', 'name' => 'Notification', 'type' => 'notification', 'config' => ['message' => 'Processed lead with score {{nodes.2.score}}'], 'pos' => ['x' => 400, 'y' => 0]]
    ],
    'connections' => [
        ['from' => '1', 'to' => '2', 'from_port' => 'output_1', 'to_port' => 'input_1'],
        ['from' => '2', 'to' => '3', 'from_port' => 'output_1', 'to_port' => 'input_1']
    ]
];

$stmt = $conn->prepare("INSERT INTO ai_workflows (name, nodes, trigger_type, status) VALUES ('Suite Test', ?, 'webhook', 'active')");
$nodesJson = json_encode($testWorkflow);
$stmt->bind_param("s", $nodesJson);
$stmt->execute();
$workflowId = $stmt->insert_id;

$wfResult = $aiManager->executeWorkflow($workflowId, ['webhook_data' => ['lead_id' => 123, 'score' => 85]]);
echo "Workflow Status: " . ($wfResult['status'] ?? 'failed') . "\n";
echo "Steps Logged: " . count($wfResult['log'] ?? []) . "\n";
echo "Result: " . ($wfResult['status'] === 'success' ? "PASS" : "FAIL") . "\n\n";

// Cleanup
$conn->query("DELETE FROM ai_workflows WHERE id = $workflowId");
$conn->query("DELETE FROM workflow_executions WHERE workflow_id = $workflowId");

echo "--- Test Suite Finished ---";
