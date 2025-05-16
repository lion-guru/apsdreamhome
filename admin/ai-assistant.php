<?php
// Secure AI Assistant endpoint for OpenAI integration
header('Content-Type: application/json');

// Load API key from config (do NOT hardcode here)
require_once __DIR__ . '/../config.php'; // Make sure $OPENAI_// SECURITY: Sensitive information removed is set in config.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['message']) || trim($data['message']) === '') {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

$user_message = trim($data['message']);

// Prepare OpenAI API call
$api_url = 'https://api.openai.com/v1/chat/completions';
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $OPENAI_// SECURITY: Sensitive information removed
];
$payload = [
    'model' => 'gpt-4o-mini',
    'messages' => [
        ['role' => 'system', 'content' => 'You are an AI assistant for the APS Dream Homes admin dashboard. Answer briefly and helpfully in Hindi or Hinglish if the user speaks that way.'],
        ['role' => 'user', 'content' => $user_message]
    ]
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    echo json_encode(['error' => 'OpenAI API error', 'details' => $error]);
    exit;
}

$result = json_decode($response, true);
$ai_reply = $result['choices'][0]['message']['content'] ?? 'Sorry, AI response not available.';

echo json_encode(['reply' => $ai_reply]);

