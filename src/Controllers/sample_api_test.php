<?php
// Load environment variables from .env if available (for local development)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
}

// Get API keys from environment
$openai_api_key = getenv('OPENAI_API_KEY');
$gemini_api_key = getenv('GEMINI_API_KEY');
$google_client_id = getenv('GOOGLE_CLIENT_ID');
$google_client_secret = getenv('GOOGLE_CLIENT_SECRET');

// Test OpenAI API (simple curl call)
function test_openai_api($api_key) {
    $url = 'https://api.openai.com/v1/chat/completions';
    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "user", "content" => "write a haiku about ai"]
        ]
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) {
        return "OpenAI API Error: $error";
    }
    return $response;
}

// Test Gemini API (simple curl call)
function test_gemini_api($api_key) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key;
    $data = [
        "contents" => [
            ["parts" => [["text" => "write a haiku about ai"]]]
        ]
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) {
        return "Gemini API Error: $error";
    }
    return $response;
}

// Output results
echo "<h2>API Test Results</h2>";
if ($openai_api_key) {
    echo "<h3>OpenAI API Response:</h3>";
    $openai_result = test_openai_api($openai_api_key);
    echo '<pre>' . htmlspecialchars($openai_result) . '</pre>';
} else {
    echo "<p style='color:red'>OpenAI API key not set.</p>";
}

if ($gemini_api_key) {
    echo "<h3>Gemini API Response:</h3>";
    $gemini_result = test_gemini_api($gemini_api_key);
    echo '<pre>' . htmlspecialchars($gemini_result) . '</pre>';
} else {
    echo "<p style='color:red'>Gemini API key not set.</p>";
}

// Google OAuth test: Just show the client ID and secret are loaded
if ($google_client_id && $google_client_secret) {
    echo "<h3>Google OAuth (apsdreamhomes)</h3>";
    echo "Client ID: <code>" . htmlspecialchars($google_client_id) . "</code><br>";
    echo "Client Secret: <code>" . htmlspecialchars($google_client_secret) . "</code><br>";
} else {
    echo "<p style='color:red'>Google OAuth credentials not set.</p>";
}
?>

