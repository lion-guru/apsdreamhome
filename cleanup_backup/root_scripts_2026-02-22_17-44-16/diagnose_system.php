<?php
// Diagnostic Script for APS Dream Home

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', __DIR__);

echo "Starting System Diagnostics...\n";
echo "==============================\n";

// 1. Check Critical Files
$criticalFiles = [
    'app/Core/App.php',
    'app/Core/Database.php',
    'app/Core/Routing/Router.php',
    'app/Core/Session/SessionManager.php',
    'app/Http/Controllers/Admin/AdminController.php',
    'app/Http/Controllers/Admin/EMIController.php',
    'app/Services/EMIAutomationService.php',
    'routes/modern.php',
    'app/helpers.php',
    'composer.json',
    '.htaccess'
];

echo "\n[Checking Critical Files]\n";
$missingFiles = [];
foreach ($criticalFiles as $file) {
    if (file_exists(BASE_PATH . '/' . $file)) {
        echo "OK: $file\n";
    } else {
        echo "MISSING: $file\n";
        $missingFiles[] = $file;
    }
}

// 2. Syntax Check (Linting)
echo "\n[Syntax Check]\n";
$filesToLint = [
    'app/Services/EMIAutomationService.php',
    'app/Http/Controllers/Admin/EMIController.php',
    'app/views/admin/emi/foreclosure_report.php',
    'app/helpers.php'
];

foreach ($filesToLint as $file) {
    if (file_exists(BASE_PATH . '/' . $file)) {
        $output = [];
        $returnVar = 0;
        exec("php -l " . escapeshellarg(BASE_PATH . '/' . $file), $output, $returnVar);
        if ($returnVar === 0) {
            echo "OK: $file syntax is valid.\n";
        } else {
            echo "ERROR: $file syntax error!\n";
            print_r($output);
        }
    }
}

// 3. Database Connection Check
echo "\n[Database Connection Check]\n";
try {
    require_once BASE_PATH . '/vendor/autoload.php';
    require_once BASE_PATH . '/app/Core/Database.php';

    // Attempt to load config if needed, but Database class might handle it
    // Assuming Database::getInstance() works with environment or defaults

    $db = \App\Core\Database::getInstance();
    $conn = $db->getConnection();
    echo "OK: Database connection successful.\n";

    // Check key tables
    $tables = ['admin', 'users', 'emi_plans', 'foreclosure_logs'];
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT 1 FROM $table LIMIT 1");
            echo "OK: Table '$table' exists.\n";
        } catch (PDOException $e) {
            echo "WARNING: Table '$table' check failed: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: Database connection failed: " . $e->getMessage() . "\n";
}

// 4. Directory Permissions Check
echo "\n[Directory Permissions Check]\n";
$directories = [
    'logs',
    'reports',
    'storage',
    'public/uploads'
];

foreach ($directories as $dir) {
    $path = BASE_PATH . '/' . $dir;
    if (!is_dir($path)) {
        echo "WARNING: Directory '$dir' does not exist. Creating...\n";
        mkdir($path, 0755, true);
    }

    if (is_writable($path)) {
        echo "OK: Directory '$dir' is writable.\n";
    } else {
        echo "ERROR: Directory '$dir' is NOT writable.\n";
    }
}

// 5. Route Verification (Basic)
echo "\n[Route Verification]\n";
if (file_exists(BASE_PATH . '/routes/modern.php')) {
    $routesContent = file_get_contents(BASE_PATH . '/routes/modern.php');
    if (strpos($routesContent, '/emi/foreclosure-report') !== false) {
        echo "OK: Foreclosure report route found.\n";
    } else {
        echo "ERROR: Foreclosure report route NOT found in routes/modern.php\n";
    }
}

echo "\nDiagnostics Complete.\n";
