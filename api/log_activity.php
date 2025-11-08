<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get and validate input
$leadId = filter_input(INPUT_POST, 'lead_id', FILTER_VALIDATE_INT);
$activityType = filter_input(INPUT_POST, 'activity_type', FILTER_SANITIZE_STRING);
$details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_STRING);

if (!$leadId || !$activityType || !$details) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    // Verify user has access to this lead
    $accessStmt = $conn->prepare("SELECT id FROM contact_inquiries WHERE id = ? AND (assigned_to = ? OR ? IN ('admin', 'lead_manager'))");
    $accessStmt->bind_param("iis", $leadId, $_SESSION['user_id'], $_SESSION['user_role']);
    $accessStmt->execute();
    $accessResult = $accessStmt->get_result();
    
    if ($accessResult->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
    
    // Log the activity
    $logStmt = $conn->prepare("INSERT INTO lead_activities (lead_id, user_id, activity_type, activity_details) VALUES (?, ?, ?, ?)");
    $logStmt->bind_param("iiss", $leadId, $_SESSION['user_id'], $activityType, $details);
    $logStmt->execute();
    
    // Update lead's updated_at timestamp
    $updateStmt = $conn->prepare("UPDATE contact_inquiries SET updated_at = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $leadId);
    $updateStmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Activity logged successfully',
        'activity_id' => $conn->insert_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
