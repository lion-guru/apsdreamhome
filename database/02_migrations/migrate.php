<?php
/**
 * Database Migration Runner
 * 
 * This script handles running database migrations to keep the database schema up to date.
 * It can be run from the command line or via a web request.
 */

// Set error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set the content type to JSON for API responses
header('Content-Type: application/json');

// Function to send JSON response
function sendJsonResponse($success, $message, $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if running from command line
$isCli = php_sapi_name() === 'cli';

// Only allow access from localhost or CLI
if (!$isCli) {
    $allowedIps = ['127.0.0.1', '::1'];
    $clientIp = $_SERVER['REMOTE_ADDR'];
    
    if (!in_array($clientIp, $allowedIps)) {
        http_response_code(403);
        sendJsonResponse(false, 'Access denied');
    }
}

try {
    // Include necessary files
    require_once __DIR__ . '/../includes/db_connection.php';
    
    // Get all migration files
    $migrationDir = __DIR__ . '/migrations';
    $migrationFiles = [];
    
    if (is_dir($migrationDir)) {
        $files = scandir($migrationDir);
        foreach ($files as $file) {
            if (preg_match('/^\d{4}_\d{2}_\d{2}_\d+_.+\.php$/', $file)) {
                $migrationFiles[] = $file;
            }
        }
        
        // Sort migrations by filename
        sort($migrationFiles);
    }
    
    if (empty($migrationFiles)) {
        sendJsonResponse(false, 'No migration files found');
    }
    
    // Create migrations table if it doesn't exist
    $conn = getMysqliConnection();
    
    // First check if migrations table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'migrations'");
    
    if ($tableExists->num_rows === 0) {
        // Create the migrations table
        $createTableSql = "
        CREATE TABLE `migrations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `batch` int(11) NOT NULL,
            `executed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `migration` (`migration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        if (!$conn->query($createTableSql)) {
            throw new Exception("Failed to create migrations table: " . $conn->error);
        }
        
        // If this is the first run, we need to commit the transaction
        if ($conn->autocommit(true) === false) {
            throw new Exception("Failed to set autocommit: " . $conn->error);
        }
    }
    
    // Get already executed migrations
    $executedMigrations = [];
    $result = $conn->query("SELECT `migration` FROM `migrations` ORDER BY `id`");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $executedMigrations[] = $row['migration'];
        }
    }
    
    // Find migrations to run
    $migrationsToRun = array_diff($migrationFiles, $executedMigrations);
    
    if (empty($migrationsToRun)) {
        sendJsonResponse(true, 'Database is already up to date');
    }
    
    // Get the next batch number
    $batchResult = $conn->query("SELECT MAX(`batch`) as max_batch FROM `migrations`");
    $batch = $batchResult ? ($batchResult->fetch_assoc()['max_batch'] ?? 0) + 1 : 1;
    
    // Run migrations
    $executed = [];
    $errors = [];
    
    foreach ($migrationsToRun as $migrationFile) {
        try {
            // Include the migration file
            require_once "$migrationDir/$migrationFile";
            
            // Get the class name from the file name
            $className = str_replace('.php', '', $migrationFile);
            $className = implode('_', array_map('ucfirst', explode('_', $className)));
            
            if (!class_exists($className)) {
                throw new Exception("Class $className not found in $migrationFile");
            }
            
            // Run the migration
            $migration = new $className();
            if (!method_exists($migration, 'up')) {
                throw new Exception("Method 'up' not found in $className");
            }
            
            $result = $migration->up();
            
            if ($result === false) {
                throw new Exception("Migration $migrationFile failed");
            }
            
            // Record the migration
            $stmt = $conn->prepare("INSERT INTO `migrations` (`migration`, `batch`) VALUES (?, ?)");
            $stmt->bind_param('si', $migrationFile, $batch);
            $stmt->execute();
            $stmt->close();
            
            $executed[] = $migrationFile;
            
        } catch (Exception $e) {
            $errors[] = [
                'file' => $migrationFile,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
    
    if (!empty($errors)) {
        // Rollback the batch if any migration failed
        if (!empty($executed)) {
            $placeholders = rtrim(str_repeat('?,', count($executed)), ',');
            $stmt = $conn->prepare("DELETE FROM `migrations` WHERE `migration` IN ($placeholders)");
            $types = str_repeat('s', count($executed));
            $stmt->bind_param($types, ...$executed);
            $stmt->execute();
        }
        
        sendJsonResponse(false, 'Some migrations failed', ['errors' => $errors]);
    }
    
    sendJsonResponse(true, 'Migrations completed successfully', [
        'executed' => $executed,
        'total' => count($migrationFiles),
        'batch' => $batch
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    sendJsonResponse(false, 'An error occurred: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
