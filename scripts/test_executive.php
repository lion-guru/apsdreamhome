<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\ExecutiveAIService;

$service = new ExecutiveAIService();
$result = $service->chat('What are my top priorities today?', 'ceo', 1);
print_r($result);