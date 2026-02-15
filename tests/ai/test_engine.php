<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Test for WorkflowEngine and BaseNodes
 */
require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/../../includes/ai/WorkflowEngine.php';
require_once __DIR__ . '/../../includes/ai/AIManager.php';

$conn = getDbConnection();
$aiManager = new AIManager($conn);
$engine = new WorkflowEngine($conn, $aiManager);

echo "--- Workflow Engine Test ---\n";

// Mock a simple workflow
$testWorkflow = [
    'nodes' => [
        ['id' => '1', 'name' => 'Start', 'type' => 'trigger', 'config' => [], 'pos' => ['x' => 0, 'y' => 0]],
        ['id' => '2', 'name' => 'HTTP Test', 'type' => 'http_request', 'config' => ['url' => 'https://jsonplaceholder.typicode.com/todos/1', 'method' => 'GET'], 'pos' => ['x' => 200, 'y' => 0]],
        ['id' => '3', 'name' => 'End', 'type' => 'notification', 'config' => ['message' => 'Test Finished: {{nodes.2.output.data.title}}'], 'pos' => ['x' => 400, 'y' => 0]]
    ],
    'connections' => [
        ['from' => '1', 'to' => '2', 'from_port' => 'output_1', 'to_port' => 'input_1'],
        ['from' => '2', 'to' => '3', 'from_port' => 'output_1', 'to_port' => 'input_1']
    ]
];

// Insert into DB for testing
$stmt = $conn->prepare("INSERT INTO ai_workflows (name, nodes, trigger_type, status) VALUES ('Test Workflow', ?, 'manual', 'active')");
$actions = json_encode($testWorkflow);
$stmt->bind_param("s", $actions);
$stmt->execute();
$workflowId = $stmt->insert_id;

echo "Created Test Workflow ID: $workflowId\n";

// Execute
echo "Executing workflow $workflowId...\n";
try {
    $result = $engine->execute($workflowId);
    echo "Execution completed.\n";
} catch (Throwable $e) {
    echo "FATAL ERROR during execution: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "Status: " . $result['status'] . "\n";
echo "Logs:\n";
foreach ($result['log'] as $log) {
    echo "- " . $log['node_name'] . " (" . $log['node_type'] . "): " . $log['status'] . "\n";
    if (isset($log['error'])) echo "  Error: " . $log['error'] . "\n";
}

if ($result['status'] === 'success') {
    echo "SUCCESS: Workflow executed correctly.\n";
} else {
    echo "FAILED: Workflow execution failed.\n";
}

// Cleanup
$conn->query("DELETE FROM ai_workflows WHERE id = $workflowId");
$conn->query("DELETE FROM workflow_executions WHERE workflow_id = $workflowId");

echo "--- Test Complete ---\n";
