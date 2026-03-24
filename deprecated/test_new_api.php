<?php

// Test New Gemini API Key
require_once __DIR__ . '/config/gemini_config.php';
$config = require __DIR__ . '/config/gemini_config.php';

echo "🚀 Testing NEW Gemini API Key\n";
echo "==============================\n\n";

echo "🔑 API Key: " . substr($config['api_key'], 0, 20) . "...\n";
echo "🌐 API URL: " . $config['api_url'] . "\n";
echo "📊 Project ID: " . $config['project_id'] . "\n";
echo "🤖 Model: " . $config['model'] . "\n\n";

if (empty($config['api_key']) || $config['api_key'] === 'YOUR_REAL_GEMINI_API_KEY_HERE') {
    echo "❌ API key not configured properly!\n";
    exit;
}

echo "🔄 Testing API Connection...\n";

// Test with new endpoint format
$url = $config['api_url'] . '?key=' . $config['api_key'];

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Hello! Please respond with 'NEW API is working perfectly!' in Hindi."]
            ]
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-goog-api-key: ' . $config['api_key']]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📊 HTTP Status: $http_code\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
} elseif ($http_code === 200) {
    echo "✅ SUCCESS! New API is working!\n";
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $ai_reply = $result['candidates'][0]['content']['parts'][0]['text'];
        echo "🤖 AI Response: $ai_reply\n\n";
        echo "🎉 Perfect! Your new Gemini API key is working!\n";
        echo "📱 Now you can use: http://localhost/apsdreamhome/ai_chat.html\n";
    } else {
        echo "⚠️ API responded but format unexpected:\n";
        echo "📄 " . substr($response, 0, 300) . "...\n";
    }
} elseif ($http_code === 403) {
    echo "❌ API Key Invalid or Disabled\n";
} elseif ($http_code === 429) {
    echo "⚠️ Rate Limit Exceeded - Try again in a few seconds\n";
} else {
    echo "❌ API Error: HTTP $http_code\n";
    echo "📄 Response: " . substr($response, 0, 300) . "...\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 Next Steps:\n";
echo "1. ✅ API Key Updated\n";
echo "2. 🧪 Test AI Chat: http://localhost/apsdreamhome/ai_chat.html\n";
echo "3. 🚀 Start using your APS AI Assistant!\n";
echo str_repeat("=", 50) . "\n";
?>
