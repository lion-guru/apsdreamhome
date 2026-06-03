<?php
/**
 * Hugging Face API Key Tester
 * 
 * USAGE:
 * php scripts/test_hf_key.php hf_YOUR_KEY_HERE
 * 
 * Or set HUGGING_FACE_API_KEY in .env file
 */

require_once __DIR__ . '/../app/Core/Config.php';

$hf_key = $argv[1] ?? Env::get('HUGGING_FACE_API_KEY', '');

if (empty($hf_key)) {
    echo "Usage: php scripts/test_hf_key.php hf_YOUR_KEY\n";
    echo "   Or set HUGGING_FACE_API_KEY in .env\n";
    exit(1);
}

echo "Testing Hugging Face API Key...\n";

$ch = curl_init('https://huggingface.co/api/whoami-v2');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $hf_key]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $data = json_decode($response, true);
    echo "✅ HUGGING_FACE_API_KEY - VALID (User: " . ($data['name'] ?? 'Unknown') . ")\n";
} else {
    echo "❌ HUGGING_FACE_API_KEY - INVALID (HTTP $http_code)\n";
    echo "Response: " . substr($response, 0, 200) . "\n";
}
