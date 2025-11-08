<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

// Verify user is logged in and has access
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get and validate input
$leadId = filter_input(INPUT_POST, 'lead_id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
$notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

if (!$leadId || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Validate status
$validStatuses = ['new', 'contacted', 'qualified', 'converted', 'lost'];
if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

try {
    // Verify user has access to this lead
    $accessStmt = $conn->prepare("
        SELECT id, assigned_to 
        FROM contact_inquiries 
        WHERE id = ? AND (
            assigned_to = ? 
            OR ? IN ('admin', 'lead_manager')
            OR ? IS NULL
        )
    ");
    $accessStmt->bind_param(
        "iiss", 
        $leadId, 
        $_SESSION['user_id'], 
        $_SESSION['user_role'],
        $_SESSION['user_role']
    );
    $accessStmt->execute();
    $lead = $accessStmt->get_result()->fetch_assoc();
    
    if (!$lead) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
    
    $conn->begin_transaction();
    
    // Update lead status
    $updateStmt = $conn->prepare("
        UPDATE contact_inquiries 
        SET status = ?, 
            updated_at = NOW(),
            last_contacted_at = CASE 
                WHEN ? IN ('contacted', 'converted', 'lost') THEN NOW() 
                ELSE last_contacted_at 
            END
        WHERE id = ?
    ");
    $updateStmt->bind_param("ssi", $status, $status, $leadId);
    $updateStmt->execute();
    
    // Log the status change
    $activityDetails = "Status changed to " . ucfirst($status);
    if ($notes) {
        $activityDetails .= ": " . $notes;
    }
    
    $logStmt = $conn->prepare("
        INSERT INTO lead_activities (lead_id, user_id, activity_type, activity_details)
        VALUES (?, ?, 'status_change', ?)
    ");
    $logStmt->bind_param("iis", $leadId, $_SESSION['user_id'], $activityDetails);
    $logStmt->execute();
    
    // If converted to client, create client record
    if ($status === 'converted') {
        // Get lead details
        $leadStmt = $conn->prepare("
            SELECT name, email, phone, source, message 
            FROM contact_inquiries 
            WHERE id = ?
        ");
        $leadStmt->bind_param("i", $leadId);
        $leadStmt->execute();
        $leadDetails = $leadStmt->get_result()->fetch_assoc();
        
        // Create client (you'll need to implement this table and logic)
        $clientStmt = $conn->prepare("
            INSERT INTO clients (
                name, email, phone, source, notes, 
                status, assigned_to, created_by, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, 'active', ?, ?, NOW(), NOW())
        ");
        $clientStmt->bind_param(
            "sssssii",
            $leadDetails['name'],
            $leadDetails['email'],
            $leadDetails['phone'],
            $leadDetails['source'],
            $leadDetails['message'],
            $lead['assigned_to'] ?? $_SESSION['user_id'],
            $_SESSION['user_id']
        );
        $clientStmt->execute();
        $clientId = $conn->insert_id;
        
        // Link lead to client
        $linkStmt = $conn->prepare("
            UPDATE contact_inquiries 
            SET client_id = ? 
            WHERE id = ?
        ");
        $linkStmt->bind_param("ii", $clientId, $leadId);
        $linkStmt->execute();
        
        // Log client creation
        $clientLogStmt = $conn->prepare("
            INSERT INTO lead_activities (lead_id, user_id, activity_type, activity_details)
            VALUES (?, ?, 'status_change', 'Converted to client #' || ?)
        ");
        $clientLogStmt->bind_param("iii", $leadId, $_SESSION['user_id'], $clientId);
        $clientLogStmt->execute();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'status' => $status
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
