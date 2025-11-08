<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../app/services/GeminiService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['prompt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt is required']);
    exit;
}

$geminiService = new \App\Services\GeminiService();
$result = $geminiService->generateContent($data['prompt']);

echo json_encode($result);