<?php
// API Key Validator Script
// Tests all API keys from .env file

require_once __DIR__ . '/../app/Core/Config.php';

echo "=== API Key Validator ===\n\n";

$results = [];

// 1. Test OpenAI API Key
echo "Testing OpenAI API Key...\n";
$openai_key = Env::get('OPENAI_API_KEY', '');
if (empty($openai_key) || $openai_key === 'YOUR_OPENAI_API_KEY_HERE') {
    echo "⚠️  OPENAI_API_KEY - NOT CONFIGURED\n";
    $results['openai'] = 'NOT_CONFIGURED';
} else {
    $ch = curl_init('https://api.openai.com/v1/models');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $openai_key]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        echo "✅ OPENAI_API_KEY - VALID\n";
        $results['openai'] = 'VALID';
    } else {
        echo "❌ OPENAI_API_KEY - INVALID (HTTP $http_code)\n";
        $results['openai'] = 'INVALID';
    }
}

// 2. Test OpenRouter API Key
echo "\nTesting OpenRouter API Key...\n";
$openrouter_key = Env::get('OPENROUTER_API_KEY', '');
if (empty($openrouter_key) || strpos($openrouter_key, 'sk-or-v1-') !== 0) {
    echo "⚠️  OPENROUTER_API_KEY - NOT CONFIGURED\n";
    $results['openrouter'] = 'NOT_CONFIGURED';
} else {
    $ch = curl_init('https://openrouter.ai/api/v1/models');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $openrouter_key, 'HTTP-Referer: http://localhost', 'X-Title: APS Dream Home']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        echo "✅ OPENROUTER_API_KEY - VALID\n";
        $results['openrouter'] = 'VALID';
    } else {
        echo "❌ OPENROUTER_API_KEY - INVALID (HTTP $http_code)\n";
        $results['openrouter'] = 'INVALID';
    }
}

// 3. Test Gemini API Key
echo "\nTesting Gemini API Key...\n";
$gemini_key = Env::get('GEMINI_API_KEY', '');
if (empty($gemini_key) || strpos($gemini_key, 'AIzaSy') !== 0) {
    echo "⚠️  GEMINI_API_KEY - NOT CONFIGURED\n";
    $results['gemini'] = 'NOT_CONFIGURED';
} else {
    $ch = curl_init('https://generativelanguage.googleapis.com/v1/models?key=' . $gemini_key);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        echo "✅ GEMINI_API_KEY - VALID\n";
        $results['gemini'] = 'VALID';
    } else {
        echo "❌ GEMINI_API_KEY - INVALID (HTTP $http_code)\n";
        $results['gemini'] = 'INVALID';
    }
}

// 4. Test Hugging Face API Key
echo "\nTesting Hugging Face API Key...\n";
$hf_key = Env::get('HUGGING_FACE_API_KEY', '');
if (empty($hf_key) || !preg_match('/^hf_[a-zA-Z0-9]+$/', $hf_key)) {
    echo "⚠️  HUGGING_FACE_API_KEY - NOT CONFIGURED\n";
    $results['huggingface'] = 'NOT_CONFIGURED';
} else {
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
        $results['huggingface'] = 'VALID';
    } else {
        echo "❌ HUGGING_FACE_API_KEY - INVALID (HTTP $http_code)\n";
        $results['huggingface'] = 'INVALID';
    }
}

// 5. Test Anthropic API Key
echo "\nTesting Anthropic API Key...\n";
$anthropic_key = Env::get('ANTHROPIC_API_KEY', '');
if (empty($anthropic_key) || strpos($anthropic_key, 'sk-ant-') !== 0) {
    echo "⚠️  ANTHROPIC_API_KEY - NOT CONFIGURED\n";
    $results['anthropic'] = 'NOT_CONFIGURED';
} else {
    $ch = curl_init('https://api.anthropic.com/v1/models');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $anthropic_key,
        'anthropic-version: 2023-06-01'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        echo "✅ ANTHROPIC_API_KEY - VALID\n";
        $results['anthropic'] = 'VALID';
    } else {
        echo "❌ ANTHROPIC_API_KEY - INVALID (HTTP $http_code)\n";
        $results['anthropic'] = 'INVALID';
    }
}

echo "\n=== SUMMARY ===\n";
foreach ($results as $provider => $status) {
    $icon = $status == 'VALID' ? '✅' : ($status == 'NOT_CONFIGURED' ? '⚠️' : '❌');
    echo "$icon $provider: $status\n";
}

echo "\n=== FREE MODEL ALTERNATIVES ===\n";
echo "If paid keys are invalid, use these FREE options:\n";
echo "1. OpenRouter - qwen/qwen3-coder:free (FREE but limited)\n";
echo "2. Hugging Face - Free inference endpoints\n";
echo "3. Groq - Free tier available (groq.com)\n";
echo "4. Cohere - Free tier (cohere.com)\n";
