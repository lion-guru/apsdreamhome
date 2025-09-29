<?php
/**
 * APS Dream Home - Complete Database Files Analysis
 * Comprehensive analysis of ALL database files in the project
 */

echo "<h1>🗄️ APS Dream Home - Complete Database Files Analysis</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1600px; margin: 0 auto; padding: 20px;'>";

// Database Connection Test
echo "<h2>🔌 Database Connection Test</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhomefinal');

    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Database Connection: FAILED</p>";
        echo "<p style='color: orange;'>Please start MySQL in XAMPP Control Panel</p>";
    } else {
        echo "<p style='color: green;'>✅ Database Connection: SUCCESSFUL</p>";
        echo "<p style='color: green;'>✅ Database: apsdreamhomefinal</p>";

        // Get database info
        $result = $conn->query("SELECT @@version as mysql_version");
        $versionInfo = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ MySQL Version: " . substr($versionInfo['mysql_version'], 0, 50) . "</p>";

        // Get current table count
        $result = $conn->query("SHOW TABLES");
        $currentTableCount = $result->num_rows;
        echo "<p style='color: green;'>✅ Current Tables: {$currentTableCount}</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
    $conn = null;
}
echo "</div>";

// Analyze Database Files
echo "<h2>📁 Complete Database Files Analysis</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$databaseDir = 'database/';
$files = scandir($databaseDir);
$databaseFiles = [];

echo "<h3>All Database Files (SQL Files Only):</h3>";
echo "<table style='width: 100%; border-collapse: collapse; font-size: 12px;'>";
echo "<tr style='background: #007bff; color: white;'>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>File Name</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Size (KB)</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Type</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Purpose</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Tables</th>";
echo "<th style='padding: 8px; border: 1px solid #ddd;'>Lines</th>";
echo "</tr>";

$categories = [
    'main' => ['apsdreamhomes.sql', 'apsdreamhomefinal.sql', 'apsdreamhomes_backup.sql'],
    'schema' => ['database_structure.sql', 'schema.sql', 'realestate_full_schema.sql'],
    'setup' => ['complete_setup.sql', 'setup.sql', 'colonizer_complete_setup.sql'],
    'migration' => ['aps_data_migration.sql', 'migration_manager.php', 'migrate.php'],
    'seed' => ['complete_seed_data.sql', 'seed_demo_data.sql', 'insert_sample_data.sql'],
    'test' => ['create_test_users.php', 'create_test_associates.php', 'sample_properties.php'],
    'fix' => ['database_fixes.sql', 'fix_missing_data.sql', 'fix_mlm_commissions.sql'],
    'update' => ['update_database.php', 'update_properties_schema.sql', 'update_mlm_tables.php'],
    'other' => []
];

$categoryStats = [];

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
        $filePath = $databaseDir . $file;
        if (file_exists($filePath)) {
            $size = filesize($filePath);
            $sizeKB = round($size / 1024, 2);
            $lines = count(file($filePath));

            // Determine category
            $category = 'other';
            $purpose = 'Additional database file';

            foreach ($categories as $cat => $catFiles) {
                if (in_array($file, $catFiles)) {
                    $category = $cat;
                    break;
                }
            }

            // Auto-categorize based on filename
            if (strpos($file, 'seed') !== false || strpos($file, 'sample') !== false || strpos($file, 'demo') !== false) {
                $category = 'seed';
                $purpose = 'Sample data and demo records';
            } elseif (strpos($file, 'fix') !== false || strpos($file, 'missing') !== false) {
                $category = 'fix';
                $purpose = 'Database fixes and corrections';
            } elseif (strpos($file, 'update') !== false || strpos($file, 'migration') !== false) {
                $category = 'update';
                $purpose = 'Database updates and migrations';
            } elseif (strpos($file, 'create') !== false && strpos($file, 'table') !== false) {
                $category = 'schema';
                $purpose = 'Individual table creation scripts';
            } elseif (strpos($file, 'insert') !== false || strpos($file, 'data') !== false) {
                $category = 'seed';
                $purpose = 'Data insertion scripts';
            }

            // Count tables in file
            $content = file_get_contents($filePath);
            preg_match_all('/CREATE TABLE `([^`]+)`/i', $content, $matches);
            $tableCount = count($matches[1] ?? []);

            $databaseFiles[] = [
                'name' => $file,
                'size' => $sizeKB,
                'category' => $category,
                'purpose' => $purpose,
                'tables' => $tableCount,
                'lines' => $lines
            ];

            echo "<tr>";
            echo "<td style='padding: 6px; border: 1px solid #ddd; font-weight: bold;'>{$file}</td>";
            echo "<td style='padding: 6px; border: 1px solid #ddd;'>{$sizeKB}</td>";
            echo "<td style='padding: 6px; border: 1px solid #ddd;'>" . ucfirst($category) . "</td>";
            echo "<td style='padding: 6px; border: 1px solid #ddd;'>{$purpose}</td>";
            echo "<td style='padding: 6px; border: 1px solid #ddd;'>{$tableCount}</td>";
            echo "<td style='padding: 6px; border: 1px solid #ddd;'>{$lines}</td>";
            echo "</tr>";

            // Update category stats
            if (!isset($categoryStats[$category])) {
                $categoryStats[$category] = ['count' => 0, 'size' => 0, 'tables' => 0];
            }
            $categoryStats[$category]['count']++;
            $categoryStats[$category]['size'] += $sizeKB;
            $categoryStats[$category]['tables'] += $tableCount;
        }
    }
}
echo "</table>";
echo "</div>";

// Category Summary
echo "<h2>📊 Database Files Summary by Category</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;'>";
foreach ($categoryStats as $category => $stats) {
    echo "<div style='background: #007bff; color: white; padding: 15px; border-radius: 8px;'>";
    echo "<h4>" . ucfirst($category) . " Files</h4>";
    echo "<p>Files: {$stats['count']}</p>";
    echo "<p>Size: " . round($stats['size']/1024, 2) . " MB</p>";
    echo "<p>Tables: {$stats['tables']}</p>";
    echo "</div>";
}
echo "</div>";
echo "</div>";

// Main Database Files Analysis
echo "<h2>🏆 Main Database Files Analysis</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$mainFiles = [
    'database/apsdreamhomes.sql' => 'Complete database with all tables and data',
    'database/apsdreamhomefinal.sql' => 'Alternative complete database file',
    'database/apsdreamhomes_backup.sql' => 'Backup of main database',
    'database/database_structure.sql' => 'Structure only (no data)',
    'database/complete_setup.sql' => 'Setup and initialization script'
];

echo "<h3>Key Database Files:</h3>";
foreach ($mainFiles as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        $sizeMB = round($size / 1024 / 1024, 2);
        $lines = count(file($file));

        // Count different statement types
        $content = file_get_contents($file);
        $createTables = preg_match_all('/CREATE TABLE/i', $content);
        $insertStatements = preg_match_all('/INSERT INTO/i', $content);
        $otherStatements = $lines - $createTables - $insertStatements;

        echo "<div style='background: white; padding: 10px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff;'>";
        echo "<h4>{$file} ({$sizeMB} MB)</h4>";
        echo "<p><strong>Purpose:</strong> {$description}</p>";
        echo "<p><strong>Lines:</strong> {$lines}</p>";
        echo "<p><strong>CREATE TABLE statements:</strong> {$createTables}</p>";
        echo "<p><strong>INSERT statements:</strong> {$insertStatements}</p>";
        echo "<p><strong>Other statements:</strong> {$otherStatements}</p>";

        // Extract table names
        preg_match_all('/CREATE TABLE `([^`]+)`/i', $content, $matches);
        $tables = $matches[1] ?? [];
        echo "<p><strong>Tables:</strong> " . implode(', ', array_slice($tables, 0, 10));
        if (count($tables) > 10) echo " ... (" . count($tables) . " total)";
        echo "</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ {$file} - File not found</p>";
    }
}
echo "</div>";

// Current Database vs Schema Comparison
echo "<h2>🔍 Current Database vs Schema Files</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

if ($conn) {
    // Get current tables
    $result = $conn->query("SHOW TABLES");
    $currentTables = [];
    while ($row = $result->fetch_array()) {
        $currentTables[] = $row[0];
    }

    echo "<h3>Current Database Tables: " . count($currentTables) . "</h3>";

    // Compare with main schema files
    $schemaComparisons = [
        'database/apsdreamhomes.sql' => 'Main Database Schema',
        'database/database_structure.sql' => 'Structure Only Schema'
    ];

    foreach ($schemaComparisons as $schemaFile => $schemaName) {
        if (file_exists($schemaFile)) {
            $content = file_get_contents($schemaFile);
            preg_match_all('/CREATE TABLE `([^`]+)`/i', $content, $matches);
            $schemaTables = $matches[1] ?? [];

            echo "<h4>Comparison with {$schemaName}:</h4>";
            echo "<p><strong>Schema tables:</strong> " . count($schemaTables) . "</p>";

            $missingInCurrent = array_diff($schemaTables, $currentTables);
            $extraInCurrent = array_diff($currentTables, $schemaTables);

            if (!empty($missingInCurrent)) {
                echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h5 style='color: red;'>❌ Missing in Current Database:</h5>";
                echo "<p>" . implode(', ', $missingInCurrent) . "</p>";
                echo "</div>";
            } else {
                echo "<p style='color: green;'>✅ All schema tables present in current database</p>";
            }

            if (!empty($extraInCurrent)) {
                echo "<div style='background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h5 style='color: blue;'>ℹ️ Extra Tables in Current Database:</h5>";
                echo "<p>" . implode(', ', $extraInCurrent) . "</p>";
                echo "</div>";
            }
        }
    }
} else {
    echo "<p style='color: red;'>❌ Cannot compare - database not connected</p>";
}
echo "</div>";

// Database Recommendations
echo "<h2>🎯 Database Analysis Recommendations</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

echo "<h3>📋 Summary of Findings:</h3>";

$findings = [];

if ($conn) {
    $currentTableCount = count($currentTables ?? []);

    if ($currentTableCount < 50) {
        $findings[] = "Current database has {$currentTableCount} tables - may be missing some components";
    } else {
        $findings[] = "✅ Current database has {$currentTableCount} tables - appears complete";
    }

    // Check for important tables
    $importantTables = ['users', 'properties', 'leads', 'customers', 'bookings', 'transactions'];
    $missingImportant = array_diff($importantTables, $currentTables ?? []);

    if (!empty($missingImportant)) {
        $findings[] = "❌ Missing important tables: " . implode(', ', $missingImportant);
    } else {
        $findings[] = "✅ All important tables are present";
    }

} else {
    $findings[] = "❌ Database not connected - start MySQL first";
}

// File analysis findings
$totalFiles = count($databaseFiles);
$totalSize = array_sum(array_column($databaseFiles, 'size')) / 1024; // MB
$findings[] = "📁 Total database files: {$totalFiles} files ({$totalSize} MB total)";

$mainDbSize = 0;
foreach ($databaseFiles as $file) {
    if ($file['category'] === 'main') {
        $mainDbSize += $file['size'];
    }
}
if ($mainDbSize > 0) {
    $findings[] = "🗄️ Main database files: " . round($mainDbSize/1024, 2) . " MB";
}

foreach ($findings as $finding) {
    echo "<p>• {$finding}</p>";
}

echo "<h3>🚀 Recommended Actions:</h3>";
echo "<ol>";
echo "<li><strong>Start MySQL:</strong> Open XAMPP Control Panel → Start MySQL</li>";
echo "<li><strong>Import Main Database:</strong> Use apsdreamhomes.sql (231 MB) for complete setup</li>";
echo "<li><strong>Verify Tables:</strong> Check that all required tables are present</li>";
echo "<li><strong>Test System:</strong> Visit index.php, CRM, and WhatsApp demo</li>";
echo "<li><strong>Backup Current:</strong> Create backup before making changes</li>";
echo "</ol>";

echo "</div>";

// Quick Action Buttons
echo "<h2>⚡ Quick Actions</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>🏠 Test Website</a>";
echo "<a href='aps_crm_system.php' style='background: #28a745; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>📞 Test CRM</a>";
echo "<a href='whatsapp_demo.php' style='background: #25d366; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>📱 Test WhatsApp</a>";
echo "<a href='database_test.php' style='background: #6f42c1; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;'>🧪 Database Test</a>";
echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px; padding: 20px; background: #28a745; color: white; border-radius: 8px;'>";
echo "<h3>🗄️ Database Files Analysis Complete!</h3>";
echo "<p>Total Files: " . count($databaseFiles) . " | Total Size: " . round($totalSize/1024, 2) . " MB</p>";
echo "<p>Current Database: " . ($conn ? "✅ Connected (" . count($currentTables ?? []) . " tables)" : "❌ Not Connected") . "</p>";
echo "<p>System Status: " . ($conn ? "✅ READY" : "⚠️ SETUP NEEDED") . "</p>";
echo "</div>";

echo "</div>";
?>
