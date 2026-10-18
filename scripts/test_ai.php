<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\AI\FreeAIEngines;

$ai = FreeAIEngines::getInstance();
$result = $ai->generate('Hello, respond in Hinglish briefly', [], 'chat');
print_r($result);