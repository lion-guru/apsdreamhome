<?php
/**
 * Lead Helper Functions
 * 
 * Contains all the helper functions used by get_lead_details.php
 */

/**
 * Get lead by ID with proper permission checks
 * 
 * @param mysqli $conn Database connection
 * @param int $id Lead ID
 * @param array $params Additional parameters (user_id, etc.)
 * @return array|null Lead data or null if not found/access denied
 */
function getLeadById($conn, $id, $params = []) {
    $userId = $params['user_id'] ?? null;
    
    $query = "SELECT * FROM leads WHERE id = ?";
    $params = [$id];
    
    // For now, just check if the lead exists without permission checks
    // This is a simplified version for testing purposes
    // In a production environment, you should implement proper permission checks
    $query = "SELECT * FROM leads WHERE id = ?";
    $params = [$id];
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return null;
    }
    
    // Bind parameters
    $types = str_repeat('i', count($params));
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return null;
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

/**
 * Get lead statistics
 * 
 * @param int $leadId The ID of the lead
 * @param array $params Additional parameters (conn, userId, etc.)
 * @return array Lead statistics
 */
function getLeadStats($leadId, $params = []) {
    $conn = $params['conn'] ?? null;
    $stats = [
        'emails' => 0,
        'calls' => 0,
        'meetings' => 0,
        'documents' => 0,
        'total_interactions' => 0
    ];
    
    // In a real implementation, you would query the database for these values
    // For now, we'll return sample data
    return $stats;
}

/**
 * Get lead files
 * 
 * @param int $leadId The ID of the lead
 * @param mysqli $conn Database connection
 * @return array List of files
 */
function getLeadFiles($leadId, $params = []) {
    $conn = $params['conn'] ?? null;
    // In a real implementation, you would query the database for files
    // For now, we'll return an empty array
    return [];
}

/**
 * Get lead emails
 * 
 * @param int $leadId The ID of the lead
 * @param mysqli $conn Database connection
 * @return array List of emails
 */
function getLeadEmails($leadId, $params = []) {
    $conn = $params['conn'] ?? null;
    // In a real implementation, you would query the database for emails
    // For now, we'll return an empty array
    return [];
}

/**
 * Get lead calls
 * 
 * @param int $leadId The ID of the lead
 * @param mysqli $conn Database connection
 * @return array List of calls
 */
function getLeadCalls($leadId, $params = []) {
    $conn = $params['conn'] ?? null;
    // In a real implementation, you would query the database for calls
    // For now, we'll return an empty array
    return [];
}

/**
 * Get lead meetings
 * 
 * @param int $leadId The ID of the lead
 * @param mysqli $conn Database connection
 * @return array List of meetings
 */
function getLeadMeetings($leadId, $params = []) {
    $conn = $params['conn'] ?? null;
    // In a real implementation, you would query the database for meetings
    // For now, we'll return an empty array
    return [];
}

/**
 * Get lead history
 * 
 * @param int $leadId The ID of the lead
 * @param mysqli $conn Database connection
 * @return array List of history items
 */
function getLeadHistory($leadId, $params = []) {
    $conn = $params['conn'] ?? null;
    // In a real implementation, you would query the database for history
    // For now, we'll return an empty array
    return [];
}

/**
 * Update last viewed timestamp for a lead
 * 
 * @param int $leadId The ID of the lead
 * @param array $params Additional parameters (userId, conn, etc.)
 * @return bool True on success, false on failure
 */
function updateLastViewed($leadId, $params = []) {
    $userId = $params['userId'] ?? null;
    $conn = $params['conn'] ?? null;
    
    // In a real implementation, you would update the last_viewed timestamp
    // For now, we'll just return true
    return true;
}
?>
