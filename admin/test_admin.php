<?php
// Test Admin Panel Functionality

// 1. Check if admin is logged in
function isAdminLoggedIn() {
    session_start();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// 2. Check required files and directories
function checkRequiredFiles() {
    $requiredFiles = [
        '../includes/config/db_config.php',
        '../includes/functions.php',
        'dashboard.php',
        'includes/header.php',
        'includes/sidebar.php',
        'includes/footer.php'
    ];
    
    $missingFiles = [];
    foreach ($requiredFiles as $file) {
        if (!file_exists($file)) {
            $missingFiles[] = $file;
        }
    }
    
    return $missingFiles;
}

// 3. Check database connection and required tables
function checkDatabase() {
    require_once __DIR__ . '/../includes/db_connection.php';
    
    $tables = [
        'users',
        'properties',
        'bookings',
        'property_types',
        'property_features',
        'property_images'
    ];
    
    $missingTables = [];
    
    try {
        $conn = getDbConnection();
        
        // Check each required table using prepared statement
        foreach ($tables as $table) {
            $stmt = $conn->prepare("SHOW TABLES LIKE ?");
            $stmt->bind_param('s', $table);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                $missingTables[] = $table;
            }
            $stmt->close();
        }
        
        $conn->close();
    } catch (Exception $e) {
        return [
            'error' => 'Database connection failed: ' . $e->getMessage(),
            'missing_tables' => $tables // Assume all tables are missing if connection fails
        ];
    }
    
    return [
        'missing_tables' => $missingTables
    ];
}

// 4. Check PHP extensions
function checkPHPExtensions() {
    $requiredExtensions = [
        'mysqli',
        'pdo_mysql',
        'gd',
        'json',
        'session',
        'filter',
        'mbstring'
    ];
    
    $missingExtensions = [];
    
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $missingExtensions[] = $ext;
        }
    }
    
    return $missingExtensions;
}

// 5. Check file permissions
function checkFilePermissions() {
    $directories = [
        '../uploads',
        '../assets/images',
        '../admin/uploads',
        '../includes/config'
    ];
    
    $permissionIssues = [];
    
    foreach ($directories as $dir) {
        if (!is_writable($dir)) {
            $permissionIssues[] = [
                'path' => $dir,
                'required_permission' => 'Writable (755 or 775)'
            ];
        }
    }
    
    return $permissionIssues;
}

// Run all checks
$results = [
    'admin_logged_in' => isAdminLoggedIn(),
    'missing_files' => checkRequiredFiles(),
    'database' => checkDatabase(),
    'missing_extensions' => checkPHPExtensions(),
    'permission_issues' => checkFilePermissions()
];

// Output results as JSON
header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);

// If not logged in, provide a login link
if (!$results['admin_logged_in']) {
    echo "\n\nTo access the admin panel, please log in at: ";
    echo "<a href='login.php'>Admin Login</a>";
}
?>
