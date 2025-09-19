<?php
header('Content-Type: application/json');
require __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

try {
    $property_id = $_POST['property_id'];
    $visit_date = $_POST['visit_date'];
    
    $stmt = $conn->prepare("INSERT INTO bookings (property_id, visit_date) VALUES (?, ?)");
    $stmt->bind_param('is', $property_id, $visit_date);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
