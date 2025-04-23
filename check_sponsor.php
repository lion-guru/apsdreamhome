<?php
require_once(__DIR__ . "/config.php");

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Get the sponsor ID from the request
$id = isset($_GET['id']) ? strtoupper(trim($_GET['id'])) : '';

// Validate format first (APS followed by 6 digits)
if (!preg_match('/^APS\d{6}$/', $id)) {
    echo json_encode([
        'valid' => false, 
        'message' => 'Invalid sponsor ID format. Must be APS followed by 6 digits.',
        'format_error' => true
    ]);
    exit;
}

// Check if sponsor ID exists in the database
$stmt = $con->prepare("SELECT a.uid, u.name FROM associates a JOIN users u ON a.user_id = u.id WHERE a.uid = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

// Return the validation result with additional information
if ($result->num_rows > 0) {
    $sponsor = $result->fetch_assoc();
    echo json_encode([
        'valid' => true,
        'message' => 'Valid sponsor ID',
        'sponsor_name' => $sponsor['name'] // Include sponsor name for better user feedback
    ]);
} else {
    echo json_encode([
        'valid' => false,
        'message' => 'This sponsor ID does not exist in our system.'
    ]);
}