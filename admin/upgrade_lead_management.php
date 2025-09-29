<?php
/**
 * Lead Management System Database Upgrade Script
 * 
 * This script will upgrade the database schema to support the enhanced lead management system.
 * It adds new tables and updates existing ones with additional fields.
 */

// Disable error display in production
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session and check authentication
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Unauthorized access');
}

// Include database configuration
require_once __DIR__ . '/../includes/db_config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => true,
    'message' => 'Upgrade started',
    'steps' => []
];

// Function to log step results
function logStep($message, $success = true) {
    global $response;
    $response['steps'][] = [
        'message' => $message,
        'success' => $success,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (!$success) {
        $response['success'] = false;
        $response['message'] = 'Upgrade failed: ' . $message;
    }
}

try {
    // Read the SQL file
    $sqlFile = __DIR__ . '/../database/upgrade_lead_management_simple.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception('SQL upgrade file not found');
    }
    
    // Read and split the SQL file into individual queries
    $sql = file_get_contents($sqlFile);
    $queries = explode(';', $sql);
    
    // Connect to database
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Execute each query
    $executed = 0;
    $skipped = 0;
    $errors = [];
    
    foreach ($queries as $query) {
        $query = trim($query);
        
        // Skip empty queries
        if (empty($query)) {
            continue;
        }
        
        try {
            // Execute the query
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            $executed++;
            logStep("Executed: " . substr($query, 0, 100) . (strlen($query) > 100 ? '...' : ''));
        } catch (PDOException $e) {
            // Check if this is a "table already exists" or similar error
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate column name') !== false) {
                $skipped++;
                logStep("Skipped (already exists): " . substr($query, 0, 100) . '...', true);
            } else {
                $errors[] = $e->getMessage();
                logStep("Error: " . $e->getMessage() . " in query: " . substr($query, 0, 100) . '...', false);
            }
        }
    }
    
    // If there were errors, rollback
    if (!empty($errors)) {
        $conn->rollBack();
        $response['errors'] = $errors;
        logStep("Upgrade failed with " . count($errors) . " errors. Changes rolled back.", false);
    } else {
        // Commit the transaction
        $conn->commit();
        logStep("Successfully executed $executed queries. $skipped queries were skipped.");
        
        // Update system version or settings if needed
        try {
            $version = '1.1.0';
            $updateStmt = $conn->prepare("INSERT INTO settings (`key`, `value`) VALUES ('lead_management_version', :version) ON DUPLICATE KEY UPDATE `value` = :version");
            $updateStmt->execute([':version' => $version]);
            logStep("Updated system version to $version");
        } catch (Exception $e) {
            logStep("Warning: Could not update system version: " . $e->getMessage(), false);
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    $response['success'] = false;
    $response['message'] = 'Fatal error: ' . $e->getMessage();
    logStep("Fatal error: " . $e->getMessage(), false);
}

// Output the response
echo json_encode($response, JSON_PRETTY_PRINT);

// Log the upgrade attempt
$logMessage = date('Y-m-d H:i:s') . " - " . ($response['success'] ? 'SUCCESS' : 'FAILED') . " - " . $response['message'] . "\n";
file_put_contents(__DIR__ . '/../logs/lead_management_upgrade.log', $logMessage, FILE_APPEND);

// If this is an AJAX request, exit
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Management System Upgrade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .step { margin-bottom: 15px; padding: 10px; border-left: 4px solid #0d6efd; }
        .step.success { border-color: #198754; background-color: #f8f9fa; }
        .step.error { border-color: #dc3545; background-color: #fff5f5; }
        .step-warning { border-color: #ffc107; background-color: #fffcf5; }
        .log-entry { font-family: monospace; margin-bottom: 5px; }
        .success-text { color: #198754; }
        .error-text { color: #dc3545; }
        .warning-text { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">Lead Management System Upgrade</h1>
                
                <?php if (isset($response['success']) && $response['success']): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Upgrade Completed Successfully!</h4>
                        <p><?php echo htmlspecialchars($response['message']); ?></p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Upgrade Failed</h4>
                        <p><?php echo htmlspecialchars($response['message'] ?? 'Unknown error occurred'); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Upgrade Log</h5>
                    </div>
                    <div class="card-body">
                        <div id="upgradeLog">
                            <?php foreach ($response['steps'] as $step): ?>
                                <div class="step <?php echo $step['success'] ? 'success' : 'error'; ?>">
                                    <div class="d-flex justify-content-between">
                                        <span class="log-entry"><?php echo htmlspecialchars($step['message']); ?></span>
                                        <span class="badge bg-<?php echo $step['success'] ? 'success' : 'danger'; ?>">
                                            <?php echo $step['success'] ? 'SUCCESS' : 'ERROR'; ?>
                                        </span>
                                    </div>
                                    <small class="text-muted"><?php echo $step['timestamp']; ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($response['errors']) && !empty($response['errors'])): ?>
                    <div class="card border-danger mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Errors</h5>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <?php foreach ($response['errors'] as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="leads.php" class="btn btn-primary">Go to Leads Dashboard</a>
                    <a href="index.php" class="btn btn-secondary">Back to Admin</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
