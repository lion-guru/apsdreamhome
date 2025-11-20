<?php
/**
 * API - Property Types Endpoint
 * Returns available property types
 */

try {
    require_once __DIR__ . '/../config/bootstrap.php';
    
    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Database connection not available'], 500);
    }

    // Get property types
    $sql = "SELECT id, name, description FROM property_types ORDER BY name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $property_types = $stmt->fetchAll();

    // Format for API response
    $formatted_types = [];
    foreach ($property_types as $type) {
        $formatted_types[] = [
            'id' => (int)$type['id'],
            'name' => $type['name'],
            'description' => $type['description']
        ];
    }

    sendJsonResponse([
        'success' => true,
        'data' => [
            'property_types' => $formatted_types,
            'count' => count($formatted_types)
        ]
    ]);

} catch (Exception $e) {
    error_log('API Property Types Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}