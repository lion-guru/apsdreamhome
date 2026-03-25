<?php

/**
 * APS Dream Home - Secure AI Backend Service
 * NEVER commit real API keys to Git!
 */

namespace App\Services\AI;

header('Content-Type: application/json');

// Load secure configuration
require_once __DIR__ . '/config/gemini_config.php';
$config = require __DIR__ . '/config/gemini_config.php';

// Get user input safely
$data = json_decode(file_get_contents('php://input'), true);
$user_text = $data['message'] ?? '';

if (empty($user_text)) {
    echo json_encode(['error' => 'Sawal khali hai!']);
    exit;
}

// Check if API key is configured
if (empty($config['api_key']) || $config['api_key'] === 'YOUR_REAL_GEMINI_API_KEY_HERE') {
    echo json_encode([
        'error' => 'Gemini API key configure nahi kiya gaya. .env file mein real API key dalein.',
        'status' => 'not_configured'
    ]);
    exit;
}

// Secure API call
$url = $config['api_url'] . '?key=' . $config['api_key'];

$payload = [
    "system_instruction" => [
        "parts" => [
            ["text" => "Tum APS Dream Home ka official AI assistant ho. Aap professional real estate guide ki tarah kaam karein. Coding, website development, aur property management ke baare mein expert help karein. Raghunath Nagri, Gorakhpur, aur Uttar Pradesh ke real estate market ka knowledge rakhein."]
        ]
    ],
    "contents" => [
        [
            "role" => "user",
            "parts" => [["text" => $user_text]]
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Handle response
if ($response && $http_code === 200) {
    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $ai_reply = $result['candidates'][0]['content']['parts'][0]['text'];
        echo json_encode(['reply' => $ai_reply, 'status' => 'success']);
    } else {
        echo json_encode(['error' => 'AI se proper response nahi mila.', 'status' => 'api_error']);
    }
} else {
    echo json_encode([
        'error' => 'Google API connection failed. HTTP Code: ' . $http_code,
        'status' => 'connection_error'
    ]);
}
