<?php
/**
 * AI Property Description API Endpoint
 * Generates property descriptions using AI
 */

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!$config['ai']['enabled']) {
    echo json_encode(['error' => 'AI features are disabled']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Only POST requests allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

$required_fields = ['type', 'location', 'price', 'bedrooms', 'area', 'features'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

$property_data = [
    'type' => $input['type'],
    'location' => $input['location'],
    'price' => $input['price'],
    'bedrooms' => $input['bedrooms'],
    'area' => $input['area'],
    'features' => $input['features']
];

try {
    $ai = new AIDreamHome();
    $result = $ai->generatePropertyDescription($property_data);

    if (isset($result['error'])) {
        echo json_encode(['error' => $result['error']]);
    } else {
        echo json_encode(['description' => $result['success']]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
