<?php
/**
 * System Check Script
 * Verifies system requirements and configurations
 */

// Start output buffering
ob_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to check PHP version
function check_php_version($min_version = '7.4.0') {
    $current = phpversion();
    $is_ok = version_compare($current, $min_version, '>=');
    return [
        'status' => $is_ok ? 'OK' : 'ERROR',
        'current' => $current,
        'required' => $min_version,
        'message' => $is_ok ? "PHP version is compatible" : "PHP $min_version or higher is required"
    ];
}

// Function to check PHP extensions
function check_php_extensions() {
    $required = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session', 'filter', 'openssl'];
    $results = [];
    
    foreach ($required as $ext) {
        $loaded = extension_loaded($ext);
        $results[$ext] = [
            'status' => $loaded ? 'OK' : 'ERROR',
            'message' => $loaded ? 'Loaded' : 'Not found'
        ];
    }
    
    return $results;
}

// Function to check file permissions
function check_permissions() {
    $paths = [
        'logs/' => 'writable',
        'uploads/' => 'writable',
        'includes/config.php' => 'readable',
        'includes/db_connection.php' => 'readable'
    ];
    
    $results = [];
    
    foreach ($paths as $path => $type) {
        $full_path = __DIR__ . '/' . $path;
        $exists = file_exists($full_path);
        $is_writable = is_writable($full_path);
        $is_readable = is_readable($full_path);
        
        $status = 'OK';
        $message = '';
        
        if (!$exists) {
            $status = 'WARNING';
            $message = 'Does not exist';
        } elseif ($type === 'writable' && !$is_writable) {
            $status = 'ERROR';
            $message = 'Not writable';
        } elseif ($type === 'readable' && !$is_readable) {
            $status = 'ERROR';
            $message = 'Not readable';
        } else {
            $message = 'OK';
        }
        
        $results[$path] = [
            'status' => $status,
            'message' => $message
        ];
    }
    
    return $results;
}

// Function to check database connection
function check_database_connection() {
    try {
        require_once __DIR__ . '/includes/db_connection.php';
        
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            throw new Exception('Database connection not properly initialized');
        }
        
        // Test connection
        $stmt = $pdo->query('SELECT VERSION() as version');
        $mysql_version = $stmt->fetch(PDO::FETCH_ASSOC)['version'];
        
        // Check if tables exist
        $tables = [];
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        return [
            'status' => 'OK',
            'mysql_version' => $mysql_version,
            'tables_found' => count($tables),
            'message' => 'Database connection successful',
            'tables' => $tables
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'ERROR',
            'message' => 'Database connection failed: ' . $e->getMessage()
        ];
    }
}

// Run all checks
$php_version = check_php_version('7.4.0');
$extensions = check_php_extensions();
$permissions = check_permissions();
$db = check_database_connection();

// Output results as JSON if requested
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'php_version' => $php_version,
        'extensions' => $extensions,
        'permissions' => $permissions,
        'database' => $db
    ], JSON_PRETTY_PRINT);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Check - APS Dream Home</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 0;
        }
        h2 {
            color: #2c3e50;
            margin-top: 30px;
            border-left: 4px solid #3498db;
            padding-left: 10px;
        }
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.9em;
            margin-left: 10px;
        }
        .status-ok {
            background-color: #d4edda;
            color: #155724;
        }
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .summary {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>APS Dream Home - System Check</h1>
        
        <div class="summary">
            <h3>System Summary</h3>
            <p><strong>PHP Version:</strong> <?php echo $php_version['current']; ?> 
                <span class="status status-<?php echo strtolower($php_version['status']); ?>">
                    <?php echo $php_version['status']; ?>
                </span>
            </p>
            <p><strong>Database Connection:</strong> 
                <span class="status status-<?php echo strtolower($db['status']); ?>">
                    <?php echo $db['status']; ?>
                </span>
            </p>
            <p><a href="?json=1" target="_blank">View raw data as JSON</a></p>
        </div>

        <h2>PHP Extensions</h2>
        <table>
            <tr>
                <th>Extension</th>
                <th>Status</th>
                <th>Message</th>
            </tr>
            <?php foreach ($extensions as $ext => $info): ?>
            <tr>
                <td><?php echo $ext; ?></td>
                <td>
                    <span class="status status-<?php echo strtolower($info['status']); ?>">
                        <?php echo $info['status']; ?>
                    </span>
                </td>
                <td><?php echo $info['message']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>File Permissions</h2>
        <table>
            <tr>
                <th>Path</th>
                <th>Status</th>
                <th>Message</th>
            </tr>
            <?php foreach ($permissions as $path => $info): ?>
            <tr>
                <td><?php echo $path; ?></td>
                <td>
                    <span class="status status-<?php echo strtolower($info['status']); ?>">
                        <?php echo $info['status']; ?>
                    </span>
                </td>
                <td><?php echo $info['message']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Database Information</h2>
        <?php if ($db['status'] === 'OK'): ?>
            <p><strong>MySQL Version:</strong> <?php echo $db['mysql_version']; ?></p>
            <p><strong>Tables Found:</strong> <?php echo $db['tables_found']; ?></p>
            
            <?php if (!empty($db['tables'])): ?>
                <h3>Database Tables</h3>
                <ul>
                    <?php foreach ($db['tables'] as $table): ?>
                        <li><?php echo $table; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No tables found in the database. Run the database setup script.</p>
            <?php endif; ?>
            
        <?php else: ?>
            <p class="status status-error"><?php echo $db['message']; ?></p>
            <p>Please check your database configuration in <code>includes/config.php</code> and ensure the database exists.</p>
        <?php endif; ?>

        <div class="footer">
            <p>Generated on <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
