<?php
/**
 * Emergency Security Fix - API Key Revocation Check
 * 
 * This script checks if compromised API keys are still active
 * Run this to verify keys have been revoked after security incident
 * 
 * USAGE: php emergency_security_fix.php
 */

require_once __DIR__ . '/app/Core/Config.php';

echo "🚨 EMERGENCY SECURITY FIX - API KEYS COMPROMISED\n";
echo "===============================================\n\n";

// Get keys from environment
$keys_to_check = [
    'GEMINI_API_KEY' => Env::get('GEMINI_API_KEY', ''),
    'OPENAI_API_KEY' => Env::get('OPENAI_API_KEY', ''),
    'OPENROUTER_API_KEY' => Env::get('OPENROUTER_API_KEY', ''),
];

foreach ($keys_to_check as $name => $key) {
    if (empty($key) || strpos($key, 'YOUR_') === 0) {
        echo "⚠️  $name - NOT CONFIGURED\n\n";
        continue;
    }
    
    echo "🔍 Checking $name: " . substr($key, 0, 10) . "...\n";
    
    if ($name === 'GEMINI_API_KEY') {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $key;
    } else {
        echo "  ⏭️  Skipping - check manually\n\n";
        continue;
    }
    
    $data = ["contents" => [["parts" => [["text" => "test"]]]]];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "  ❌ KEY IS WORKING - REVOKE IF COMPROMISED!\n";
    } elseif ($http_code === 403 || $http_code === 400) {
        echo "  ✅ KEY IS DISABLED - Good!\n";
    } else {
        echo "  ⚠️  Status Code: $http_code\n";
    }
    echo "\n";
}

echo "🔒 SECURITY BEST PRACTICES:\n";
echo "1. Never commit API keys to git\n";
echo "2. Use .env file for secrets\n";
echo "3. Add .env to .gitignore\n";
echo "4. Rotate keys regularly\n\n";

echo "📧 GET NEW API KEYS:\n";
echo "- Gemini: https://aistudio.google.com/apikey\n";
echo "- OpenAI: https://platform.openai.com/api-keys\n";
echo "- OpenRouter: https://openrouter.ai/keys\n\n";

echo "✅ After getting new keys, update .env file\n";
