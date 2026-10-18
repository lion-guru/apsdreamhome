<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\AI\AIGateway;

$gateway = AIGateway::getInstance();
$result = $gateway->process('chat', [
    'message' => 'What are my top priorities today?',
    'system_prompt' => 'You are the CEO AI assistant. Respond in Hinglish.'
], [
    'role' => 'ceo',
    'user_id' => 1
]);
print_r($result);