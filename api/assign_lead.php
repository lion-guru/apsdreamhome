<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

// Verify user has permission to assign leads
if (!in_array($_SESSION['user_role'], ['admin', 'lead_manager'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get and validate input
$leadId = filter_input(INPUT_POST, 'lead_id', FILTER_VALIDATE_INT);
$assignedTo = filter_input(INPUT_POST, 'assigned_to', FILTER_VALIDATE_INT);

if (!$leadId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid lead ID']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Update lead assignment
    $stmt = $conn->prepare("UPDATE contact_inquiries SET assigned_to = ?, status = 'assigned', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $assignedTo, $leadId);
    $stmt->execute();
    
    // Log the assignment
    $activity = $assignedTo 
        ? "Assigned to user ID: $assignedTo" 
        : 'Assignment removed';
    
    $logStmt = $conn->prepare("INSERT INTO lead_activities (lead_id, user_id, activity_type, activity_details) VALUES (?, ?, 'status_change', ?)");
    $logStmt->bind_param("iis", $leadId, $_SESSION['user_id'], $activity);
    $logStmt->execute();
    
    // Send notification to the assigned agent if applicable
    if ($assignedTo) {
        $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type) VALUES (?, 'New Lead Assigned', 'You have been assigned a new lead #$leadId', 'lead_assigned', ?, 'lead')");
        $notifStmt->bind_param("ii", $assignedTo, $leadId);
        $notifStmt->execute();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Lead assigned successfully',
        'assigned_to' => $assignedTo
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
