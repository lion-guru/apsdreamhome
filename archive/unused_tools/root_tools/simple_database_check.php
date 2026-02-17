<?php
/**
 * Simple Database Check - APS Dream Home
 * Basic database connection and structure check
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Check - APS Dream Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
<h1>🗄️ Database Structure Check - APS Dream Home</h1>";

try {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    echo "<div class='success'>✅ Database Connection: SUCCESSFUL</div>";
    echo "<div class='info'>📍 Database: apsdreamhome</div>";

    // Get all tables
    $result = $conn->query("SHOW TABLES");
    $tables = [];

    if ($result) {
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
    }

    echo "<h2>📋 Database Tables (" . count($tables) . " total)</h2>";

    if (count($tables) > 0) {
        echo "<table>";
        echo "<tr><th>Table Name</th><th>Records</th><th>Category</th></tr>";

        foreach ($tables as $table) {
            // Get row count
            $countResult = $conn->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $countResult->fetch_assoc();
            $rowCount = $count['count'];

            // Categorize
            $category = 'Other';
            if (strpos($table, 'user') !== false || $table === 'users') {
                $category = '👥 Users';
            } elseif (strpos($table, 'propert') !== false || strpos($table, 'plot') !== false) {
                $category = '🏠 Properties';
            } elseif (strpos($table, 'customer') !== false || strpos($table, 'lead') !== false) {
                $category = '📞 CRM';
            } elseif (strpos($table, 'ai_') === 0 || strpos($table, 'chat') !== false) {
                $category = '🤖 AI';
            } elseif (strpos($table, 'log') !== false) {
                $category = '📊 Logs';
            }

            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>$rowCount</td>";
            echo "<td>$category</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Check for key tables
        echo "<h2>🔍 Key Tables Status</h2>";

        $keyTables = [
            'users' => 'User management',
            'properties' => 'Property listings',
            'customers' => 'Customer data',
            'associates' => 'Associate management',
            'projects' => 'Project management',
            'plots' => 'Plot management',
            'ai_chat_conversations' => 'AI chat system',
            'ai_chat_messages' => 'AI message logs'
        ];

        echo "<table>";
        echo "<tr><th>Table</th><th>Purpose</th><th>Status</th><th>Records</th></tr>";

        foreach ($keyTables as $table => $purpose) {
            $exists = in_array($table, $tables);
            $status = $exists ? '✅ Present' : '❌ Missing';
            $statusClass = $exists ? 'success' : 'error';

            $records = 0;
            if ($exists) {
                $countResult = $conn->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $countResult->fetch_assoc();
                $records = $count['count'];
            }

            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>$purpose</td>";
            echo "<td class='$statusClass'>$status</td>";
            echo "<td>$records</td>";
            echo "</tr>";
        }
        echo "</table>";

    } else {
        echo "<div class='warning'>⚠️ No tables found in database</div>";
    }

    // Database size
    $sizeResult = $conn->query("SELECT
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
        FROM information_schema.tables
        WHERE table_schema = 'apsdreamhome'");

    if ($sizeResult) {
        $sizeInfo = $sizeResult->fetch_assoc();
        $dbSize = $sizeInfo['size_mb'] ?? 0;
        echo "<h2>💾 Database Size</h2>";
        echo "<div class='info'>📏 Total size: <strong>{$dbSize} MB</strong></div>";
    }

    $conn->close();

} catch (Exception $e) {
    echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>💡 Make sure MySQL is running in XAMPP Control Panel</div>";
}

echo "<h2>📁 Database Files in Project</h2>";

$databaseFiles = [
    'includes/db_config.php' => 'Database configuration',
    'includes/Database.php' => 'Database class',
    'includes/db_connection.php' => 'Connection utility',
    'database_setup.php' => 'Database setup script',
    'apsdreamhome_ultimate.sql' => 'SQL schema file',
    'current_database_check.php' => 'Database inspection'
];

echo "<table>";
echo "<tr><th>File</th><th>Purpose</th><th>Status</th></tr>";

foreach ($databaseFiles as $file => $purpose) {
    $exists = file_exists($file);
    $status = $exists ? '✅ Exists' : '❌ Missing';
    $statusClass = $exists ? 'success' : 'error';

    echo "<tr>";
    echo "<td><strong>$file</strong></td>";
    echo "<td>$purpose</td>";
    echo "<td class='$statusClass'>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>💡 Summary</h2>";
echo "<div class='info'>";
echo "<p><strong>Database Status:</strong> " . (count($tables) > 0 ? "✅ Configured" : "⚠️ Needs Setup") . "</p>";
echo "<p><strong>Tables Found:</strong> " . count($tables) . "</p>";
echo "<p><strong>Configuration Files:</strong> " . count(array_filter($databaseFiles, function($file) { return file_exists($file); })) . "/" . count($databaseFiles) . "</p>";
echo "</div>";

echo "</body></html>";
?>
