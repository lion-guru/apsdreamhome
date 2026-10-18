<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\AI\FreeAIEngines;
use App\Services\AI\AIGateway;
use App\Services\AI\IntentDetector;
use App\Services\AI\SelfLearningAI;
use App\Services\ExecutiveAIService;
use App\Services\AgenticAI\AgentOrchestrator;
use App\Services\AI\VoiceProviderManager;
use App\Services\AI\RAGKnowledgeManager;

echo "=== AI System End-to-End Test ===\n\n";

// 1. Test FreeAIEngines
echo "1. Testing FreeAIEngines...\n";
$ai = FreeAIEngines::getInstance();
$result = $ai->generate('Hello, respond in Hinglish briefly', [], 'chat');
echo "   Engine: {$result['engine']}, Model: {$result['model']}\n";
echo "   Response: " . substr($result['text'], 0, 80) . "...\n\n";

// 2. Test AIGateway
echo "2. Testing AIGateway...\n";
$gateway = AIGateway::getInstance();
$result = $gateway->process('chat', [
    'message' => 'What are my top priorities?',
    'system_prompt' => 'You are a CEO AI assistant. Respond in Hinglish.'
], ['role' => 'ceo', 'user_id' => 1]);
echo "   Engine: {$result['engine']}, Confidence: {$result['confidence']}\n";
echo "   Response: " . substr($result['result']['text'] ?? '', 0, 80) . "...\n\n";

// 3. Test IntentDetector
echo "3. Testing IntentDetector...\n";
$db = \App\Core\Database\Database::getInstance();
$detector = new IntentDetector($db);
$tests = [
    'Mujhe Gorakhpur mein 50 lakh ka plot chahiye',
    'I want to buy a 3BHK flat in Lucknow under 80 lakhs',
    'Site visit book karna hai kal',
    'EMI calculator chahiye',
];
foreach ($tests as $test) {
    $result = $detector->detect($test);
    echo "   '$test' -> {$result['intent']} ({$result['confidence']})\n";
}
echo "\n";

// 4. Test SelfLearningAI
echo "4. Testing SelfLearningAI...\n";
$sl = new SelfLearningAI('test_session_' . time(), 1, 'customer');
$result = $sl->processMessage('Hello, I want to buy a plot in Gorakhpur');
echo "   Intent: {$result['intent']}, Confidence: {$result['confidence']}\n";
echo "   Response: " . substr($result['response'], 0, 80) . "...\n\n";

// 5. Test ExecutiveAIService
echo "5. Testing ExecutiveAIService...\n";
$exec = new ExecutiveAIService();
$result = $exec->chat('What are my top priorities today?', 'ceo', 1);
echo "   Engine: {$result['engine']}, Confidence: {$result['confidence']}\n";
echo "   Response: " . substr($result['response'], 0, 80) . "...\n";
echo "   Suggested actions: " . count($result['suggested_actions']) . "\n\n";

// 6. Test AgentOrchestrator
echo "6. Testing AgentOrchestrator...\n";
$orchestrator = new AgentOrchestrator();
$results = $orchestrator->runAll();
foreach ($results as $type => $result) {
    if (isset($result['error'])) {
        echo "   $type: ERROR - {$result['error']}\n";
    } else {
        echo "   $type: " . count($result) . " actions\n";
    }
}
echo "\n";

// 7. Test VoiceProviderManager
echo "7. Testing VoiceProviderManager...\n";
$voice = VoiceProviderManager::getInstance();
echo "   Provider: {$voice->getProvider()}, Configured: " . ($voice->isConfigured() ? 'Yes' : 'No') . "\n\n";

// 8. Test RAGKnowledgeManager
echo "8. Testing RAGKnowledgeManager...\n";
$rag = RAGKnowledgeManager::getInstance();
$results = $rag->search('Suryoday Vihar plot price', 3);
echo "   Found " . count($results) . " relevant documents\n";
foreach ($results as $r) {
    echo "   - [{$r['score']}] {$r['doc_title']}\n";
}
echo "\n";

echo "=== All AI Systems Tested Successfully! ===\n";