<?php
// Admin Panel Diagnostic Tool
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Function to format output
function checkItem($item, $status, $message = '') {
    $color = $status ? 'green' : 'red';
    $icon = $status ? '✓' : '✗';
    echo "<div><span style='color: $color; font-weight: bold;'>$icon $item</span>";
    if (!empty($message)) {
        echo ": $message";
    }
    echo "</div>";
    return $status;
}

// Check PHP version
$phpVersion = phpversion();
$phpCheck = version_compare($phpVersion, '7.4.0', '>=');
checkItem("PHP Version ($phpVersion)", $phpCheck, $phpCheck ? '' : 'PHP 7.4 or higher is required');

// Check required extensions
$requiredExtensions = ['pdo_mysql', 'mysqli', 'json', 'session', 'gd', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    checkItem("PHP Extension: $ext", $loaded, $loaded ? '' : 'Not loaded');
}

// Check file permissions
$writableDirs = [
    __DIR__ . '/admin/uploads',
    __DIR__ . '/cache',
    __DIR__ . '/logs'
];

foreach ($writableDirs as $dir) {
    if (file_exists($dir)) {
        $writable = is_writable($dir);
        checkItem("Directory writable: $dir", $writable, $writable ? '' : 'Directory is not writable');
    } else {
        checkItem("Directory exists: $dir", false, 'Directory does not exist');
    }
}

// Check database connection
try {
    require_once __DIR__ . '/includes/db_connection.php';
    $conn = getDbConnection();
    $dbCheck = $conn && $conn->ping();
    checkItem('Database Connection', $dbCheck, $dbCheck ? 'Connected successfully' : 'Connection failed');
    
    if ($dbCheck) {
        // Check required tables
        $requiredTables = ['users', 'properties', 'customers', 'leads', 'property_visits', 'notifications'];
        $result = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        foreach ($requiredTables as $table) {
            $exists = in_array($table, $tables);
            checkItem("Table: $table", $exists, $exists ? 'Exists' : 'Missing');
        }
        
        // Check admin user
        $adminCheck = $conn->query("SELECT id FROM users WHERE username = 'admin' AND role = 'admin'");
        checkItem('Admin User', $adminCheck && $adminCheck->num_rows > 0, 
                 $adminCheck && $adminCheck->num_rows > 0 ? 'Admin user exists' : 'Admin user not found');
        
        $conn->close();
    }
} catch (Exception $e) {
    checkItem('Database Connection', false, 'Error: ' . $e->getMessage());
}

// Check session configuration
$sessionCheck = session_status() === PHP_SESSION_ACTIVE || @session_start();
checkItem('Session Handling', $sessionCheck, $sessionCheck ? 'Sessions working' : 'Session start failed');

// Check for common configuration issues
$configCheck = true;
$configMsg = '';

if (!file_exists(__DIR__ . '/.env')) {
    $configCheck = false;
    $configMsg .= 'Warning: .env file missing. ';
}

if (!file_exists(__DIR__ . '/includes/config/db_config.php')) {
    $configCheck = false;
    $configMsg .= 'Database config file missing. ';
}

checkItem('Configuration Files', $configCheck, $configMsg ?: 'All required config files found');

// Check for common security issues
$securityIssues = [];

// Check if display_errors is on in production
if (ini_get('display_errors')) {
    $securityIssues[] = 'display_errors is enabled (should be off in production)';
}

// Check if error reporting is too verbose
if (error_reporting() & E_NOTICE) {
    $securityIssues[] = 'Error reporting includes notices (should be disabled in production)';
}

// Check if PHP version is out of date
if (version_compare($phpVersion, '8.0.0', '<')) {
    $securityIssues[] = 'Using an outdated PHP version (upgrade recommended)';
}

if (empty($securityIssues)) {
    checkItem('Security Checks', true, 'No critical security issues found');
} else {
    checkItem('Security Checks', false, 'Issues found: ' . implode(', ', $securityIssues));
}

// Generate report
$output = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Diagnostic</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .status {
            margin: 15px 0;
            padding: 10px;
            border-left: 4px solid #4CAF50;
            background-color: #f9f9f9;
        }
        .status.error {
            border-left-color: #f44336;
        }
        .status.warning {
            border-left-color: #ff9800;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #e7f3fe;
            border-left: 4px solid #2196F3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Panel Diagnostic Report</h1>
        <div class="summary">
            <h3>System Summary</h3>
            <p>PHP Version: <?php echo phpversion(); ?></p>
            <p>Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></p>
            <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'; ?></p>
        </div>
        
        <h2>Diagnostic Results</h2>
        <?php echo $output; ?>
        
        <div class="status <?php echo strpos($output, '✗') !== false ? 'error' : 'success'; ?>">
            <?php 
            if (strpos($output, '✗') !== false) {
                echo '<h3>Issues Detected</h3><p>Some issues were found that need attention. Please review the items marked with ✗ above.</p>';
            } else {
                echo '<h3>All Systems Go!</h3><p>No critical issues were detected with your admin panel setup.</p>';
            }
            ?>
        </div>
        
        <div class="actions">
            <h3>Next Steps</h3>
            <ul>
                <li><a href="admin/" target="_blank">Go to Admin Panel</a></li>
                <li><a href="check_database.php" target="_blank">Check Database Structure</a></li>
                <li><a href="test_admin.php" target="_blank">Test Admin Login</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
