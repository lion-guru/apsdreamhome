<?php
/**
 * APS Dream Home - Database Connection Test
 * Check and fix database connection issues
 */

echo "<h1>🗄️ APS Database Connection Test</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px;'>";

// Test 1: Check if config file exists
echo "<h2>📋 Test 1: Configuration Files</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$filesToCheck = [
    'includes/Database.php',
    'includes/config.php',
    'config.php'
];

foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ {$file} - Found</p>";
    } else {
        echo "<p style='color: red;'>❌ {$file} - Missing</p>";
    }
}
echo "</div>";

// Test 2: Check Database Connection
echo "<h2>🔌 Test 2: Database Connection</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    // Include database configuration
    if (file_exists('includes/Database.php')) {
        require_once 'includes/Database.php';
        $db = new Database();
        $conn = $db->getConnection();

        if ($conn) {
            echo "<p style='color: green;'>✅ Database Connection: Successful</p>";

            // Test database query
            $result = $conn->query("SHOW DATABASES");
            if ($result) {
                echo "<p style='color: green;'>✅ Database Query: Working</p>";

                $databases = $result->fetch_all(MYSQLI_ASSOC);
                echo "<h4>Available Databases:</h4>";
                echo "<ul>";
                foreach ($databases as $database) {
                    echo "<li>{$database['Database']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: red;'>❌ Database Query: Failed</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Database Connection: Failed</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Database.php not found, creating basic connection test...</p>";

        // Basic MySQL connection test
        $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');

        if ($conn->connect_error) {
            echo "<p style='color: red;'>❌ MySQL Connection Failed: " . $conn->connect_error . "</p>";
        } else {
            echo "<p style='color: green;'>✅ Basic MySQL Connection: Successful</p>";
            $conn->close();
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Connection Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 3: Check Database Files
echo "<h2>📁 Test 3: Database Files Analysis</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$databaseDir = 'database/';
$mainDBFiles = [
    'apsdreamhomes.sql',
    'apsdreamhome.sql',
    'database_structure.sql',
    'complete_setup.sql'
];

echo "<h4>Main Database Files:</h4>";
foreach ($mainDBFiles as $file) {
    $filePath = $databaseDir . $file;
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        $sizeKB = round($size / 1024, 2);
        echo "<p style='color: green;'>✅ {$file} - {$sizeKB} KB</p>";
    } else {
        echo "<p style='color: red;'>❌ {$file} - Not Found</p>";
    }
}
echo "</div>";

// Test 4: Check Missing Controller Methods
echo "<h2>🎮 Test 4: Controller Methods Check</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$missingMethods = [
    'notFound()',
    'requireLogin()',
    'isAdmin()',
    'forbidden()'
];

echo "<h4>Checking Controller Methods:</h4>";
$controllerFile = 'app/controllers/Controller.php';

if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    $allFound = true;

    foreach ($missingMethods as $method) {
        if (strpos($controllerContent, $method) !== false) {
            echo "<p style='color: green;'>✅ {$method} - Found</p>";
        } else {
            echo "<p style='color: red;'>❌ {$method} - Missing</p>";
            $allFound = false;
        }
    }

    if ($allFound) {
        echo "<p style='color: green; font-weight: bold;'>✅ All required controller methods are present</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Some controller methods are missing</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Controller.php not found</p>";
}
echo "</div>";

// Test 5: Database Tables Check
echo "<h2>📊 Test 5: Database Tables Check</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    if (isset($conn) && $conn) {
        $tablesResult = $conn->query("SHOW TABLES");
        if ($tablesResult) {
            $tables = $tablesResult->fetch_all(MYSQLI_ASSOC);
            echo "<h4>Database Tables (" . count($tables) . " found):</h4>";
            echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;'>";

            foreach ($tables as $table) {
                $tableName = $table['Tables_in_apsdreamhome'] ?? $table[key($table)];
                echo "<div style='background: #007bff; color: white; padding: 10px; border-radius: 5px; text-align: center;'>{$tableName}</div>";
            }
            echo "</div>";

            echo "<p style='color: green;'>✅ Database Tables: " . count($tables) . " tables found</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No tables found or unable to query</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Database connection not available for table check</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Table Check Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 6: System Status Summary
echo "<h2>🎯 Test 6: System Status Summary</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$status = [
    'Database Connection' => 'Checking...',
    'Configuration Files' => 'Checking...',
    'Controller Methods' => 'Checking...',
    'Database Tables' => 'Checking...',
    'Overall Status' => 'Checking...'
];

// Update status based on previous tests
$status['Configuration Files'] = (file_exists('includes/Database.php') && file_exists('config.php')) ? '✅ OK' : '❌ Missing';
$status['Controller Methods'] = $allFound ?? false ? '✅ OK' : '❌ Missing Methods';
$status['Database Tables'] = isset($tables) && count($tables) > 0 ? '✅ ' . count($tables) . ' Tables' : '⚠️ No Tables';

if (isset($conn) && $conn) {
    $status['Database Connection'] = '✅ Connected';
    $status['Overall Status'] = '✅ System Ready';
} else {
    $status['Database Connection'] = '❌ Connection Failed';
    $status['Overall Status'] = '❌ Needs Setup';
}

echo "<h3>System Status:</h3>";
echo "<ul>";
foreach ($status as $component => $state) {
    echo "<li><strong>{$component}:</strong> {$state}</li>";
}
echo "</ul>";
echo "</div>";

// Recommendations
echo "<h2>🔧 Recommendations</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

if ($status['Overall Status'] === '❌ Needs Setup') {
    echo "<h4>Setup Required:</h4>";
    echo "<ol>";
    echo "<li>Start MySQL service in XAMPP Control Panel</li>";
    echo "<li>Import database from 'database/apsdreamhomes.sql'</li>";
    echo "<li>Update config.php with correct database credentials</li>";
    echo "<li>Add missing controller methods to Controller.php</li>";
    echo "<li>Run the system health check</li>";
    echo "</ol>";
} else {
    echo "<h4>System is Ready! ✅</h4>";
    echo "<p>All components are working correctly.</p>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px;'>🏠 Go to Website</a>";
echo "<a href='aps_crm_system.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px;'>📞 Access APS CRM</a>";
echo "</div>";

echo "</div>";
?>
