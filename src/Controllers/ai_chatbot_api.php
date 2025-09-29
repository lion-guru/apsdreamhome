<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/includes/config/openai.php');
$config = include(__DIR__ . '/includes/config/openai.php');
$api_key = $config['api_key'] ?? ''; // Fixed: Properly named variable
if (!$api_key || $api_key === 'YOUR_OPENAI_API_KEY_HERE') { // Fixed: Proper variable name
    echo json_encode(['success'=>false,'error'=>'OpenAI API key not set. Contact admin.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = trim($input['message'] ?? '');
    if (!$message) {
        echo json_encode(['success'=>false,'error'=>'No message provided.']);
        exit;
    }
    // Call OpenAI API (gpt-3.5-turbo)
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role'=>'system','content'=>'You are APS Dream Homes AI Assistant. Help with real estate, bookings, and site info.'],
            ['role'=>'user','content'=>$message]
        ],
        'max_tokens' => 256,
        'temperature' => 0.7
    ];
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key // Fixed: Proper variable name
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        echo json_encode(['success'=>false,'error'=>'cURL error: '.$err]);
        exit;
    }
    $resp = json_decode($result, true);
    if (isset($resp['choices'][0]['message']['content'])) {
        echo json_encode(['success'=>true,'reply'=>trim($resp['choices'][0]['message']['content'])]);
    } else {
        echo json_encode(['success'=>false,'error'=>'No response from AI.']);
    }
    exit;
}
echo json_encode(['success'=>false,'error'=>'Invalid request method.']);

