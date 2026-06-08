<?php
/**
 * Test Gemini API Integration
 * 
 * Get your API key from: https://aistudio.google.com/apikey
 * Add to .env: GEMINI_API_KEY=your_key_here
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/Core/Config.php';

$apiKey = Env::get('GEMINI_API_KEY', 'YOUR_GEMINI_KEY_HERE');

if ($apiKey === 'YOUR_GEMINI_KEY_HERE' || empty($apiKey)) {
    echo "<h1>⚠️ API Key Not Configured</h1>";
    echo "<p>Please set GEMINI_API_KEY in your .env file</p>";
    echo "<p>Get your key from: <a href='https://aistudio.google.com/apikey' target='_blank'>Google AI Studio</a></p>";
    exit;
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

$data = [
    "contents" => [["parts" => [["text" => "Hello, is this API active? Test message from APS Dream Home."]]]]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>Gemini API Test - APS Dream Home</h1>";
echo "<h2>HTTP Status: " . $httpCode . "</h2>";
echo "<h3>Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

if ($httpCode === 200) {
    echo "<h3 style='color: green;'>✅ API Connection Successful!</h3>";
    $responseData = json_decode($response, true);
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        echo "<h4>AI Response:</h4>";
        echo "<p>" . htmlspecialchars($responseData['candidates'][0]['content']['parts'][0]['text']) . "</p>";
    }
} else {
    echo "<h3 style='color: red;'>❌ API Connection Failed!</h3>";
}

echo "<hr>";
echo "<h3>Test Information:</h3>";
echo "<ul>";
echo "<li>API Key: " . substr($apiKey, 0, 10) . "..." . substr($apiKey, -10) . "</li>";
echo "<li>Model: gemini-1.5-flash</li>";
echo "<li>Endpoint: " . htmlspecialchars($url) . "</li>";
echo "<li>Timestamp: " . date('Y-m-d H:i:s') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='/admin/ai-settings'>Access AI Settings Admin Panel</a></li>";
echo "<li><a href='/api/gemini/test'>Test Public API Endpoint</a></li>";
echo "<li><a href='/api/gemini/status'>Check API Status</a></li>";
echo "</ol>";
