<?php
require_once __DIR__.'/../includes/db_settings.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid property ID']));
}

$propertyId = (int)$_GET['id'];
try {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT visit_date FROM visit_availability WHERE property_id = ? AND visit_date >= CURDATE()");
    $stmt->bind_param('i', $propertyId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $availableDates = [];
    while ($row = $result->fetch_assoc()) {
        $availableDates[] = $row['visit_date'];
    }
    
    echo json_encode($availableDates);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
