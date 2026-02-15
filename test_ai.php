<?php
require_once __DIR__ . '/app/config/env.php';
require_once __DIR__ . '/app/core/AI/OpenRouterClient.php';
require_once __DIR__ . '/app/Services/AIService.php';

use App\Services\AIService;
use App\Core\AI\OpenRouterClient;

echo "Testing OpenRouter API Key and AI Service\n";
echo "----------------------------------------\n";

$apiKey = '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';
echo "OPENROUTER_API_KEY from js: " . substr($apiKey, 0, 10) . "...\n";

$client = new OpenRouterClient($apiKey);
$testResult = $client->chat("You are a test assistant.", "Say 'Hello, AI is working!' if you can hear me.");

if ($testResult['ok']) {
    echo "SUCCESS: OpenRouter replied: " . $testResult['content'] . "\n";
} else {
    echo "FAILED: OpenRouter error: " . $testResult['error'] . "\n";
}

echo "\nTesting AIService:\n";
$aiService = new AIService();
$suggestions = $aiService->generateRoleBasedSuggestions(1, 'admin');

echo "Suggestions for Admin:\n";
foreach ($suggestions as $s) {
    echo "- $s\n";
}

echo "\nTesting GeminiService:\n";
require_once __DIR__ . '/app/services/GeminiService.php';
$gemini = new \App\Services\GeminiService();
$geminiResult = $gemini->generateContent("Say 'Hello, Gemini is working!'");

if (isset($geminiResult['error']) && $geminiResult['error']) {
    echo "FAILED: Gemini error: " . $geminiResult['message'] . "\n";
} else {
    echo "SUCCESS: Gemini replied.\n";
    // Print first candidate content if available
    if (isset($geminiResult['candidates'][0]['content']['parts'][0]['text'])) {
        echo "Reply: " . $geminiResult['candidates'][0]['content']['parts'][0]['text'] . "\n";
    }
}
