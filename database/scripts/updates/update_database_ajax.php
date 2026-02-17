<?php
/**
 * AJAX Handler for Database Updates
 * 
 * This script handles the AJAX requests for the database update process.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Prevent direct access to this file
define('SECURE_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start secure session
start_secure_session('aps_dream_home');

// Set JSON header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'logs' => []
];

// Function to add log entry
function addLog(&$response, $message, $type = 'info') {
    $response['logs'][] = [
        'type' => $type,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Check if user is authenticated and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

// Check CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $response['message'] = 'Invalid CSRF token';
    echo json_encode($response);
    exit;
}

// Get the action
$action = $_POST['action'] ?? '';

// Handle different actions
switch ($action) {
    case 'update_database':
        updateDatabase($response);
        break;
    default:
        $response['message'] = 'Invalid action';
        break;
}

// Send the response
echo json_encode($response);
exit;

/**
 * Update the database schema
 * 
 * @param array &$response Reference to the response array
 */
function updateDatabase(&$response) {
    global $db;
    
    try {
        addLog($response, 'Starting database update process...', 'info');
        
        // Check if the database connection is available
        if (!($db instanceof mysqli)) {
            throw new Exception('Database connection failed');
        }
        
        // Read the SQL file
        $sqlFile = __DIR__ . '/update_properties_schema.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception('SQL file not found');
        }
        
        $sql = file_get_contents($sqlFile);
        
        if (empty($sql)) {
            throw new Exception('SQL file is empty');
        }
        
        // Split the SQL file into individual queries
        $queries = explode(';', $sql);
        $executed = 0;
        $skipped = 0;
        $errors = 0;
        
        // Begin transaction
        $db->begin_transaction();
        
        foreach ($queries as $query) {
            // Skip empty queries
            $query = trim($query);
            if (empty($query)) {
                continue;
            }
            
            try {
                // Execute the query
                $result = $db->query($query);
                
                if ($result === false) {
                    // Check if this is a "table already exists" error that we can safely ignore
                    if (strpos($db->error, 'already exists') !== false) {
                        addLog($response, "Skipped (already exists): " . substr($query, 0, 100) . "...", 'warning');
                        $skipped++;
                        continue;
                    }
                    
                    throw new Exception($db->error);
                }
                
                $executed++;
                addLog($response, "Executed: " . substr($query, 0, 100) . "...", 'success');
                
            } catch (Exception $e) {
                $errors++;
                addLog($response, "Error executing query: " . $e->getMessage(), 'error');
                // Don't stop on error, continue with next query
            }
        }
        
        // Commit the transaction
        $db->commit();
        
        // Update response
        $response['success'] = true;
        $response['message'] = sprintf(
            'Database update completed. Executed: %d, Skipped: %d, Errors: %d',
            $executed,
            $skipped,
            $errors
        );
        
        addLog($response, $response['message'], 'success');
        
    } catch (Exception $e) {
        // Rollback on error
        if (isset($db) && $db instanceof mysqli) {
            $db->rollback();
        }
        
        $response['success'] = false;
        $response['message'] = 'Database update failed: ' . $e->getMessage();
        addLog($response, $response['message'], 'error');
    }
}
?>
