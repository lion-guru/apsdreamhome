<?php
/**
 * Get Land Record AJAX handler
 */
require_once __DIR__ . '/core/init.php';

// Access control - basic check for admin session as in original
if (!isset($_SESSION['auser'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized access.']));
}

if (isset($_GET['id'])) {
    $id = SecurityUtility::sanitizeInput($_GET['id'], 'int');
    
    $record = \App\Core\App::database()->fetch("SELECT * FROM kisaan_land_management WHERE id = ?", [$id]);
    
    if ($record) {
        echo json_encode($record);
    } else {
        echo json_encode(['error' => 'Record not found']);
    }
}
?>
