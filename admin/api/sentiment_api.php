<?php
// Dummy sentiment API endpoint for local testing
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $feedback = strtolower($input['feedback'] ?? '');
    $sentiment = 'neutral';
    if (strpos($feedback, 'good') !== false || strpos($feedback, 'excellent') !== false) $sentiment = 'positive';
    if (strpos($feedback, 'bad') !== false || strpos($feedback, 'poor') !== false) $sentiment = 'negative';
    echo json_encode(['sentiment' => $sentiment]);
    exit;
}
echo json_encode(['error' => 'Invalid request']);
