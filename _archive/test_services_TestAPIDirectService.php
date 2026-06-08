<?php

// Test API directly
require_once __DIR__ . '/config/gemini_config.php';
$config = require __DIR__ . '/config/gemini_config.php';

echo "🧪 Direct API Test\n";
echo "================\n\n";

echo "🔑 API Key Status: " . (empty($config['api_key']) ? '❌ Empty' : '✅ Set') . "\n";
echo "🔑 Key Length: " . strlen($config['api_key']) . " characters\n";
echo "🆔 Project ID: " . ($config['project_id'] ?: '❌ Not Set') . "\n\n";

if (!empty($config['api_key'])) {
    echo "🔄 Testing API Connection...\n";
    
    $url = $config['api_url'] . '?key=' . $config['api_key'];
    $data = [
        "contents" => [["parts" => [["text" => "Hello! Can you respond with 'API is working!'?"]]]]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "📊 HTTP Status: $http_code\n";
    
    if ($error) {
        echo "❌ cURL Error: $error\n";
    } elseif ($http_code === 200) {
        echo "✅ API Success!\n";
        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $ai_reply = $result['candidates'][0]['content']['parts'][0]['text'];
            echo "🤖 AI Response: $ai_reply\n";
        }
    } elseif ($http_code === 403) {
        echo "❌ API Key Invalid or Disabled\n";
    } elseif ($http_code === 429) {
        echo "⚠️ Rate Limit Exceeded\n";
    } else {
        echo "❌ API Error: HTTP $http_code\n";
        echo "📄 Response: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "❌ No API key configured!\n";
}

echo "\n✅ Test Complete!\n";
?>
