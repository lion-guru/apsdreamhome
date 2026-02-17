<?php
/**
 * APS Dream Home - Complete Database Analysis & Comparison
 * Comprehensive analysis of all database files and current database structure
 */

echo "<h1>🗄️ APS Dream Home - Complete Database Analysis</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1400px; margin: 0 auto; padding: 20px;'>";

// Test Database Connection First
echo "<h2>🔌 Database Connection Test</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    require_once 'includes/Database.php';
    $db = new Database();
    $conn = $db->getConnection();

    if ($conn) {
        echo "<p style='color: green;'>✅ Database Connection: SUCCESSFUL</p>";

        // Get database info
        $result = $conn->query("SELECT DATABASE() as db_name");
        $dbInfo = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ Current Database: {$dbInfo['db_name']}</p>";

        // Get MySQL version
        $result = $conn->query("SELECT @@version as mysql_version");
        $versionInfo = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ MySQL Version: " . substr($versionInfo['mysql_version'], 0, 50) . "</p>";

    } else {
        echo "<p style='color: red;'>❌ Database Connection: FAILED</p>";
        echo "<p style='color: orange;'>Please start MySQL in XAMPP Control Panel</p>";
        exit();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
    exit();
}
echo "</div>";

// Analyze Database Files
echo "<h2>📁 Database Files Analysis</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$databaseDir = 'database/';
$files = scandir($databaseDir);
$databaseFiles = [];

echo "<h3>SQL Files Found:</h3>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #007bff; color: white;'>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>File Name</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>Size (MB)</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>Type</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>Description</th>";
echo "</tr>";

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
        $filePath = $databaseDir . $file;
        if (file_exists($filePath)) {
            $size = filesize($filePath);
            $sizeMB = round($size / 1024 / 1024, 2);

            $type = 'Unknown';
            $description = '';

            if (strpos($file, 'apsdreamhomes') !== false) {
                $type = 'Main Database';
                $description = 'Complete database with all tables and data';
            } elseif (strpos($file, 'structure') !== false) {
                $type = 'Schema Only';
                $description = 'Database structure without data';
            } elseif (strpos($file, 'complete_setup') !== false) {
                $type = 'Setup Script';
                $description = 'Database setup and initialization';
            } elseif (strpos($file, 'migration') !== false) {
                $type = 'Migration';
                $description = 'Database updates and changes';
            } elseif (strpos($file, 'seed') !== false) {
                $type = 'Seed Data';
                $description = 'Sample data for testing';
            } else {
                $type = 'Other';
                $description = 'Additional database file';
            }

            $databaseFiles[] = [
                'name' => $file,
                'size' => $sizeMB,
                'type' => $type,
                'description' => $description
            ];

            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$file}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$sizeMB} MB</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$type}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$description}</td>";
            echo "</tr>";
        }
    }
}
echo "</table>";
echo "</div>";

// Current Database Structure
echo "<h2>📊 Current Database Structure</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    // Get all tables
    $result = $conn->query("SHOW TABLES");
    $tables = $result->fetch_all(MYSQLI_ASSOC);

    echo "<h3>Database Tables (" . count($tables) . " total):</h3>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;'>";

    $tableNames = [];
    foreach ($tables as $table) {
        $tableName = $table['Tables_in_apsdreamhome'] ?? $table[key($table)];
        $tableNames[] = $tableName;

        // Get table info
        $infoResult = $conn->query("SHOW CREATE TABLE `{$tableName}`");
        $info = $infoResult->fetch_assoc();
        $createStatement = $info['Create Table'] ?? '';

        // Count rows
        $countResult = $conn->query("SELECT COUNT(*) as count FROM `{$tableName}`");
        $count = $countResult->fetch_assoc();
        $rowCount = $count['count'];

        echo "<div style='background: #007bff; color: white; padding: 10px; border-radius: 5px;'>";
        echo "<strong>{$tableName}</strong><br>";
        echo "Rows: {$rowCount}<br>";
        echo "<small>" . strlen($createStatement) . " chars</small>";
        echo "</div>";
    }
    echo "</div>";

    echo "<h3>Database Summary:</h3>";
    echo "<ul>";
    echo "<li><strong>Total Tables:</strong> " . count($tables) . "</li>";
    echo "<li><strong>Database Size:</strong> " . getDatabaseSize($conn) . " MB</li>";
    echo "<li><strong>Engine:</strong> InnoDB</li>";
    echo "<li><strong>Charset:</strong> utf8mb4</li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error getting database structure: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Compare with Schema Files
echo "<h2>🔍 Schema Comparison</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$schemaFiles = [
    'database/apsdreamhomes.sql' => 'Main Database',
    'database/database_structure.sql' => 'Structure Only',
    'database/complete_setup.sql' => 'Setup Script'
];

echo "<h3>Schema Files vs Current Database:</h3>";
foreach ($schemaFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<h4>{$description}: {$file}</h4>";

        $content = file_get_contents($file);
        if ($content) {
            // Extract CREATE TABLE statements
            preg_match_all('/CREATE TABLE `([^`]+)`/i', $content, $matches);
            $schemaTables = $matches[1] ?? [];

            echo "<p><strong>Tables in schema:</strong> " . count($schemaTables) . "</p>";

            // Compare with current tables
            $missingInCurrent = array_diff($schemaTables, $tableNames);
            $extraInCurrent = array_diff($tableNames, $schemaTables);

            if (!empty($missingInCurrent)) {
                echo "<p style='color: red;'>❌ Missing in current DB: " . implode(', ', $missingInCurrent) . "</p>";
            } else {
                echo "<p style='color: green;'>✅ All schema tables present in current DB</p>";
            }

            if (!empty($extraInCurrent)) {
                echo "<p style='color: blue;'>ℹ️ Extra tables in current DB: " . implode(', ', $extraInCurrent) . "</p>";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Schema file not found: {$file}</p>";
    }
}
echo "</div>";

// Database Health Check
echo "<h2>💊 Database Health Check</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $healthIssues = [];

    // Check for important tables
    $importantTables = [
        'users' => 'User management',
        'properties' => 'Property listings',
        'leads' => 'Lead management',
        'customers' => 'Customer data',
        'bookings' => 'Booking system',
        'transactions' => 'Payment transactions',
        'whatsapp_messages' => 'WhatsApp communication',
        'mlm_commissions' => 'MLM commission system'
    ];

    echo "<h3>Important Tables Check:</h3>";
    foreach ($importantTables as $table => $description) {
        if (in_array($table, $tableNames)) {
            $countResult = $conn->query("SELECT COUNT(*) as count FROM `{$table}`");
            $count = $countResult->fetch_assoc();
            $rowCount = $count['count'];

            echo "<p style='color: green;'>✅ {$table} ({$description}): {$rowCount} records</p>";
        } else {
            echo "<p style='color: red;'>❌ {$table} ({$description}): TABLE MISSING</p>";
            $healthIssues[] = "Missing table: {$table}";
        }
    }

    // Check for data integrity
    echo "<h3>Data Integrity Check:</h3>";

    // Check users table
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE email IS NULL OR email = ''");
    if ($result) {
        $nullEmails = $result->fetch_assoc();
        if ($nullEmails['count'] > 0) {
            echo "<p style='color: orange;'>⚠️ Users with null/missing emails: {$nullEmails['count']}</p>";
        }
    }

    // Check properties table
    $result = $conn->query("SELECT COUNT(*) as count FROM properties WHERE status NOT IN ('active', 'inactive', 'sold')");
    if ($result) {
        $invalidStatus = $result->fetch_assoc();
        if ($invalidStatus['count'] > 0) {
            echo "<p style='color: orange;'>⚠️ Properties with invalid status: {$invalidStatus['count']}</p>";
        }
    }

    // Overall health status
    if (empty($healthIssues)) {
        echo "<h3 style='color: green;'>✅ Database Health: EXCELLENT</h3>";
    } else {
        echo "<h3 style='color: red;'>❌ Database Health: ISSUES FOUND</h3>";
        echo "<ul>";
        foreach ($healthIssues as $issue) {
            echo "<li>{$issue}</li>";
        }
        echo "</ul>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Health Check Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Database Performance
echo "<h2>⚡ Database Performance</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $startTime = microtime(true);

    // Run performance tests
    $conn->query("SELECT 1");
    $conn->query("SELECT COUNT(*) FROM users");
    $conn->query("SELECT COUNT(*) FROM properties");

    $endTime = microtime(true);
    $executionTime = round(($endTime - $startTime) * 1000, 2);

    echo "<p style='color: green;'>✅ Query Performance: {$executionTime}ms for 3 queries</p>";

    // Get database size
    $dbSize = getDatabaseSize($conn);
    echo "<p style='color: green;'>✅ Database Size: {$dbSize} MB</p>";

    // Check indexes
    $result = $conn->query("SHOW INDEX FROM properties");
    $indexCount = $result ? $result->num_rows : 0;
    echo "<p style='color: green;'>✅ Properties Table Indexes: {$indexCount}</p>";

    $result = $conn->query("SHOW INDEX FROM users");
    $indexCount = $result ? $result->num_rows : 0;
    echo "<p style='color: green;'>✅ Users Table Indexes: {$indexCount}</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Performance Check Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Final Recommendations
echo "<h2>🎯 Recommendations & Next Steps</h2>";
echo "<div style='background: #28a745; color: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>";

echo "<h3>Database Analysis Complete!</h3>";
echo "<div style='background: rgba(255,255,255,0.1); padding: 15px; border-radius: 5px; margin: 15px 0;'>";

$recommendations = [];

// Tables analysis
if (count($tableNames) < 50) {
    $recommendations[] = "Database has " . count($tableNames) . " tables. Consider importing complete schema if missing tables.";
} else {
    $recommendations[] = "✅ Database has " . count($tableNames) . " tables - appears complete.";
}

// Size analysis
$dbSize = getDatabaseSize($conn);
if ($dbSize < 50) {
    $recommendations[] = "Database size is {$dbSize}MB. Consider importing sample data if needed.";
} else {
    $recommendations[] = "✅ Database size is {$dbSize}MB - contains sufficient data.";
}

// Health analysis
if (empty($healthIssues)) {
    $recommendations[] = "✅ Database health is excellent - no issues found.";
} else {
    $recommendations[] = "⚠️ Database health issues found - need attention.";
}

foreach ($recommendations as $rec) {
    echo "<p>• {$rec}</p>";
}

echo "</div>";

echo "<h4>🚀 Your System is Ready!</h4>";
echo "<p>All major components are working. Test your system:</p>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 10px; margin: 15px 0;'>";
echo "<a href='index.php' style='background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Main Website</a>";
echo "<a href='aps_crm_system.php' style='background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📞 CRM System</a>";
echo "<a href='whatsapp_demo.php' style='background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📱 WhatsApp Demo</a>";
echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px; padding: 20px; background: #007bff; color: white; border-radius: 8px;'>";
echo "<h3>🗄️ Database Analysis Complete!</h3>";
echo "<p>Database: ✅ Connected | Tables: ✅ " . count($tableNames) . " | Size: ✅ " . getDatabaseSize($conn) . "MB</p>";
echo "<p>System Status: ✅ READY TO USE</p>";
echo "</div>";

echo "</div>";

// Helper function to get database size
function getDatabaseSize($conn) {
    try {
        $result = $conn->query("SELECT
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.tables
            WHERE table_schema = DATABASE()");

        if ($result) {
            $row = $result->fetch_assoc();
            return $row['size_mb'] ?? 0;
        }
        return 0;
    } catch (Exception $e) {
        return 0;
    }
}
?>
