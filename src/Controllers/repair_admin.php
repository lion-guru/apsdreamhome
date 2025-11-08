<?php
// Admin Panel Repair Tool
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Function to format output
function logMessage($type, $message) {
    $colors = [
        'success' => 'green',
        'error' => 'red',
        'warning' => 'orange',
        'info' => 'blue'
    ];
    
    $color = $colors[$type] ?? 'black';
    echo "<div style='margin: 5px 0; padding: 5px; border-left: 3px solid $color;'>";
    echo "<strong>" . ucfirst($type) . ":</strong> $message";
    echo "</div>";
}

// Check if we should run repairs
$runRepairs = isset($_GET['run']) && $_GET['run'] === 'true';

// Function to execute SQL file
function executeSQLFile($conn, $file) {
    if (!file_exists($file)) {
        return ['success' => false, 'message' => "File not found: $file"];
    }
    
    $sql = file_get_contents($file);
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    try {
        $conn->begin_transaction();
        foreach ($queries as $query) {
            if (!empty($query)) {
                $conn->query($query);
            }
        }
        $conn->commit();
        return ['success' => true, 'message' => "Successfully executed $file"];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => "Error executing $file: " . $e->getMessage()];
    }
}

// Start HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Repair Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
            border: none;
            cursor: pointer;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .btn-warning {
            background-color: #ff9800;
        }
        .repair-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .repair-options {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Panel Repair Tool</h1>
        <p>This tool can help fix common issues with the admin panel.</p>
        
        <?php if (!$runRepairs): ?>
            <div class="warning" style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <h3>Before You Begin</h3>
                <p>This tool will attempt to automatically fix issues with your admin panel. It's recommended to:</p>
                <ol>
                    <li>Backup your database before proceeding</li>
                    <li>Make sure no one else is using the system during repairs</li>
                    <li>Run the diagnostic tool first to identify issues</li>
                </ol>
                <a href="?run=true" class="btn">Run Repair Tool</a>
            </div>
        <?php else: ?>
            <div class="repair-results">
                <h2>Repair Results</h2>
                <?php
                // Track overall success
                $allSuccess = true;
                
                // 1. Check and repair database connection
                logMessage('info', 'Checking database connection...');
                try {
                    require_once __DIR__ . '/includes/db_connection.php';
                    $conn = getDbConnection();
                    
                    if ($conn->connect_error) {
                        throw new Exception("Connection failed: " . $conn->connect_error);
                    }
                    
                    logMessage('success', 'Database connection successful');
                    
                    // 2. Check and repair database tables
                    logMessage('info', 'Checking database tables...');
                    
                    // Get list of existing tables
                    $result = $conn->query("SHOW TABLES");
                    $tables = [];
                    while ($row = $result->fetch_array()) {
                        $tables[] = $row[0];
                    }
                    
                    // Check for required tables
                    $requiredTables = ['users', 'properties', 'customers', 'leads', 'property_visits', 'notifications'];
                    $missingTables = array_diff($requiredTables, $tables);
                    
                    if (!empty($missingTables)) {
                        logMessage('warning', 'Missing tables: ' . implode(', ', $missingTables));
                        
                        // Try to run database migrations
                        $migrationFile = __DIR__ . '/database/schema.sql';
                        if (file_exists($migrationFile)) {
                            $result = executeSQLFile($conn, $migrationFile);
                            if ($result['success']) {
                                logMessage('success', 'Successfully executed database schema');
                            } else {
                                logMessage('error', $result['message']);
                                $allSuccess = false;
                            }
                        } else {
                            logMessage('error', 'Database schema file not found. Please restore from backup.');
                            $allSuccess = false;
                        }
                    } else {
                        logMessage('success', 'All required tables exist');
                    }
                    
                    // 3. Check and repair admin user
                    logMessage('info', 'Checking admin user...');
                    $adminCheck = $conn->query("SELECT id FROM users WHERE username = 'admin' AND role = 'admin'");
                    
                    if ($adminCheck->num_rows === 0) {
                        logMessage('warning', 'Admin user not found. Creating admin user...');
                        $password = password_hash('admin123', PASSWORD_DEFAULT);
                        $email = 'admin@example.com';
                        $sql = "INSERT INTO users (username, email, password, role, status, created_at) 
                                VALUES ('admin', '$email', '$password', 'admin', 'active', NOW())";
                        
                        if ($conn->query($sql)) {
                            logMessage('success', 'Admin user created with username: admin, password: admin123');
                            logMessage('warning', 'Please change the default password immediately!');
                        } else {
                            throw new Exception("Failed to create admin user: " . $conn->error);
                        }
                    } else {
                        logMessage('success', 'Admin user exists');
                    }
                    
                    // 4. Check and repair file permissions
                    logMessage('info', 'Checking file permissions...');
                    $writableDirs = [
                        __DIR__ . '/admin/uploads' => 'rwxr-xr-x',
                        __DIR__ . '/cache' => 'rwxr-xr-x',
                        __DIR__ . '/logs' => 'rwxr-xr-x'
                    ];
                    
                    foreach ($writableDirs as $dir => $permissions) {
                        if (!file_exists($dir)) {
                            if (@mkdir($dir, 0755, true)) {
                                logMessage('success', "Created directory: $dir");
                            } else {
                                logMessage('error', "Failed to create directory: $dir");
                                $allSuccess = false;
                            }
                        }
                        
                        if (!is_writable($dir)) {
                            if (@chmod($dir, 0755)) {
                                logMessage('success', "Updated permissions for: $dir");
                            } else {
                                logMessage('error', "Failed to update permissions for: $dir");
                                $allSuccess = false;
                            }
                        }
                    }
                    
                    // 5. Check and repair .htaccess file
                    logMessage('info', 'Checking .htaccess file...');
                    $htaccessFile = __DIR__ . '/.htaccess';
                    $htaccessContent = "# Apache configuration for APS Dream Home\n";
                    $htaccessContent .= "<IfModule mod_rewrite.c>\n";
                    $htaccessContent .= "    RewriteEngine On\n";
                    $htaccessContent .= "    RewriteBase /\n\n";
                    $htaccessContent .= "    # Handle Front Controller...\n";
                    $htaccessContent .= "    RewriteCond %{REQUEST_FILENAME} !-d\n";
                    $htaccessContent .= "    RewriteCond %{REQUEST_FILENAME} !-f\n";
                    $htaccessContent .= "    RewriteRule ^ index.php [L]\n";
                    $htaccessContent .= "</IfModule>\n";
                    
                    if (!file_exists($htaccessFile) || file_get_contents($htaccessFile) !== $htaccessContent) {
                        if (file_put_contents($htaccessFile, $htaccessContent) !== false) {
                            logMessage('success', 'Created/updated .htaccess file');
                        } else {
                            logMessage('error', 'Failed to create/update .htaccess file');
                            $allSuccess = false;
                        }
                    } else {
                        logMessage('success', '.htaccess file is correct');
                    }
                    
                    $conn->close();
                    
                } catch (Exception $e) {
                    logMessage('error', 'Error during repair: ' . $e->getMessage());
                    $allSuccess = false;
                }
                
                // Display final status
                echo "<div class='repair-section' style='margin-top: 30px; padding: 20px; background-color: " . ($allSuccess ? '#e8f5e9' : '#ffebee') . ";'>";
                echo "<h3>Repair " . ($allSuccess ? 'Completed Successfully' : 'Completed with Issues') . "</h3>";
                
                if ($allSuccess) {
                    echo "<p>All repairs have been completed successfully. You can now access the admin panel.</p>";
                    echo "<a href='admin/' class='btn'>Go to Admin Panel</a>";
                } else {
                    echo "<p>Some repairs could not be completed automatically. Please check the error messages above.</p>";
                    echo "<p>If you continue to experience issues, please contact support with the error messages shown above.</p>";
                    echo "<a href='admin_diagnostic.php' class='btn-warning btn'>Run Diagnostic Again</a>";
                }
                
                echo "</div>";
                ?>
            </div>
        <?php endif; ?>
        
        <div class="additional-actions" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <h3>Additional Tools</h3>
            <div class="repair-options">
                <a href="admin_diagnostic.php" class="btn">Run Diagnostic Tool</a>
                <a href="test_admin.php" class="btn">Test Admin Login</a>
                <a href="check_database.php" class="btn">Check Database</a>
            </div>
        </div>
    </div>
</body>
</html>
