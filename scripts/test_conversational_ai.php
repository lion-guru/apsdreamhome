<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\AI\ConversationalAIService;

$service = new ConversationalAIService();

echo "=== Conversational AI Service Tests ===\n\n";

// Test 1: Create session
echo "1. Testing session creation...\n";
$sessionId = $service->getOrCreateSession(1);
echo "Session created: $sessionId\n\n";

// Test 2: First message
echo "2. Testing first message...\n";
$result = $service->processMessage($sessionId, 1, "Mujhe Gorakhpur mein 50 lakh ka plot chahiye");
echo "Response: " . substr($result['response'], 0, 200) . "...\n";
echo "Intent: {$result['intent']}, Confidence: {$result['confidence']}\n";
echo "Actions: " . count($result['actions']) . "\n";
if (!empty($result['actions'])) {
    foreach ($result['actions'] as $action) {
        echo "  - {$action['action']}: " . json_encode($action['params']) . "\n";
    }
}
echo "Suggestions: " . count($result['suggestions']) . "\n";
foreach ($result['suggestions'] as $s) {
    echo "  - {$s['label']} ({$s['action']})\n";
}
echo "\n";

// Test 3: Second message (context continuation)
echo "3. Testing context continuation...\n";
$result = $service->processMessage($sessionId, 1, "Site visit book karna hai kal");
echo "Response: " . substr($result['response'], 0, 200) . "...\n";
echo "Intent: {$result['intent']}, Confidence: {$result['confidence']}\n";
echo "Actions: " . count($result['actions']) . "\n";
if (!empty($result['actions'])) {
    foreach ($result['actions'] as $action) {
        echo "  - {$action['action']}: " . json_encode($action['params']) . "\n";
    }
}
echo "\n";

// Test 4: Third message (EMI)
echo "4. Testing EMI query...\n";
$result = $service->processMessage($sessionId, 1, "EMI calculator chahiye 50 lakh ke liye");
echo "Response: " . substr($result['response'], 0, 200) . "...\n";
echo "Intent: {$result['intent']}, Confidence: {$result['confidence']}\n";
echo "Actions: " . count($result['actions']) . "\n";
if (!empty($result['actions'])) {
    foreach ($result['actions'] as $action) {
        echo "  - {$action['action']}: " . json_encode($action['params']) . "\n";
    }
}
echo "\n";

// Test 5: Get history
echo "5. Testing history retrieval...\n";
$history = $service->getHistory($sessionId);
echo "History turns: " . count($history) . "\n";
foreach ($history as $turn) {
    echo "  [{$turn['role']}] " . substr($turn['content'], 0, 80) . "...\n";
}
echo "\n";

// Test 6: Session info
echo "6. Testing session info...\n";
$sessionInfo = $service->getSessionInfo($sessionId);
echo "Session info: ";
print_r($sessionInfo);
echo "\n";

// Test 7: End session
echo "7. Testing session end...\n";
$service->endSession($sessionId);
$sessionInfo = $service->getSessionInfo($sessionId);
echo "Session ended: ";
print_r($sessionInfo);
echo "\n";

// Test 8: New session after end
echo "8. Testing new session after end...\n";
$newSessionId = $service->getOrCreateSession(1);
echo "New session: $newSessionId\n";

$result = $service->processMessage($newSessionId, 1, "Hello, naya session");
echo "Response: " . substr($result['response'], 0, 200) . "...\n";

echo "\n=== All Conversational AI Tests Completed ===\n";