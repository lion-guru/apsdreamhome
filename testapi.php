<?php
/**
 * Test API Connection
 * 
 * Configure your API key in .env file:
 * GEMINI_API_KEY=your_key_here
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/app/Core/Config.php';

$apiKey = Env::get('GEMINI_API_KEY', '');

if (empty($apiKey) || strpos($apiKey, 'YOUR_') === 0) {
    echo "<h1>⚠️ API Key Not Configured</h1>";
    echo "<p>Please set GEMINI_API_KEY in your .env file</p>";
    echo "<p>Get your key from: <a href='https://aistudio.google.com/apikey' target='_blank'>Google AI Studio</a></p>";
    exit;
}

$url = 'https://generativelanguage.googleapis.com/v/models/gemini-pro:generateContent?key=' . $apiKey;

$data = [
    "contents" => [["parts" => [["text" => "Hello, is this API active?"]]]]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>API Test Result</h1>";
echo "<p>Status: $httpCode</p>";
echo "<pre>" . $response . "</pre>";
