<?php
// AI Auto-Reply for Messages (demo)
// Usage: call this when a new message/support request arrives
require_once __DIR__ . '/core/init.php';
function ai_generate_reply($user_message) {
    global $OPENAI_API_KEY; // Fixed: Properly reference the API key variable
    $api_url = 'https://api.openai.com/v1/chat/completions';
    $headers = [
        'Content-Type: application/json',
        'Authorization: ' . 'Bearer ' . $OPENAI_API_KEY // Fixed: Properly reference the API key variable
    ];
    $payload = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful admin assistant. Reply briefly and politely in Hindi or Hinglish if possible.'],
            ['role' => 'user', 'content' => $user_message]
        ]
    ];
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? 'Sorry, auto-reply not available.';
}
// Example usage:
// $reply = ai_generate_reply('Booking help needed');
// echo $reply;

