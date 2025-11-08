<?php
/**
 * AI Property Valuation API Endpoint
 * Provides AI-powered property valuations
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

$required_fields = ['location', 'type', 'area', 'bedrooms', 'condition'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

$property_data = [
    'location' => $input['location'],
    'type' => $input['type'],
    'area' => $input['area'],
    'bedrooms' => $input['bedrooms'],
    'bathrooms' => $input['bathrooms'] ?? $input['bedrooms'], // Default to bedrooms if not provided
    'year_built' => $input['year_built'] ?? '2020',
    'condition' => $input['condition'],
    'amenities' => $input['amenities'] ?? ['Parking', 'Security']
];

try {
    $ai = new AIDreamHome();
    $result = $ai->estimatePropertyValue($property_data);

    if (isset($result['error'])) {
        echo json_encode(['error' => $result['error']]);
    } else {
        echo json_encode(['valuation' => $result['success']]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
