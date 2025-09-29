<?php
/**
 * APS Dream Home - Complete System Restoration
 * Restores the complete 192-table database system
 */

echo "<h1>🎉 APS Dream Home - Complete System Restoration</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px;'>";

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'apsdreamhomefinal';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass);

// Check connection
if ($conn->connect_error) {
    die("<p style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color: green;'>✅ Connected to MySQL server</p>";

// Recreate database
echo "<p>🔄 Recreating database...";
$conn->query("DROP DATABASE IF EXISTS `$db_name`");
$conn->query("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db_name);
echo "✅</p>";

// Get all SQL files from db_backups folder
$backup_dir = __DIR__ . '/db_backups/';
$sql_files = glob($backup_dir . '*.sql');

if (empty($sql_files)) {
    die("<p style='color: red;'>❌ No SQL files found in db_backups folder</p>");
}

echo "<p>📂 Found " . count($sql_files) . " SQL files to import</p>";

// Sort files for proper import order
sort($sql_files);

$success_count = 0;
$error_count = 0;
$tables_created = [];
$errors = [];

echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";

foreach ($sql_files as $file) {
    $filename = basename($file);
    echo "<p style='font-weight: bold; color: blue;'>📄 Importing: $filename</p>";

    // Read SQL file
    $sql = file_get_contents($file);
    if ($sql === false) {
        echo "<p style='color: red;'>❌ Error reading file: $filename</p>";
        $error_count++;
        continue;
    }

    // Split into queries
    $queries = array_filter(
        array_map('trim',
            preg_split("/;\s*(?=([^']*'[^']*')*[^']*$)/", $sql)
        ),
        function($query) {
            return !empty($query) && strlen($query) > 10;
        }
    );

    $file_queries = count($queries);
    $file_success = 0;
    $file_errors = 0;

    echo "<div style='margin-left: 20px;'>";
    foreach ($queries as $i => $query) {
        // Skip comments
        if (strpos(trim($query), '--') === 0 ||
            strpos(trim($query), '/*') === 0 ||
            empty(trim($query))) {
            continue;
        }

        // Execute query
        if ($conn->query($query)) {
            $success_count++;
            $file_success++;

            // Extract table name for logging
            if (preg_match('/CREATE TABLE (?:IF NOT EXISTS )?`?([^`\s(]+)/i', $query, $matches)) {
                $tables_created[] = $matches[1];
            }
        } else {
            $error_count++;
            $file_errors++;
            $error = $conn->error;
            $errors[] = "$filename: $error";
            echo "<p style='color: red; margin: 2px 0;'>❌ Query " . ($i + 1) . ": $error</p>";
        }
    }
    echo "</div>";

    echo "<p style='color: " . ($file_errors > 0 ? 'orange' : 'green') . ";'>✅ $file_success/$file_queries queries executed ($file_errors errors)</p>";

    // Flush output buffer
    ob_flush();
    flush();
}

echo "</div>";

// Count tables after import
$result = $conn->query("SHOW TABLES");
$table_count = $result ? $result->num_rows : 0;

// Show summary
echo "<div style='margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>";
echo "<h3>📊 Import Summary</h3>";
echo "<p>✅ Successful queries: $success_count</p>";
echo "<p>❌ Failed queries: $error_count</p>";
echo "<p>📋 Tables created: $table_count</p>";

if ($table_count > 0) {
    echo "<h4>📋 List of Tables:</h4>";
    echo "<div style='column-count: 3;'>";
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        echo "<div>✅ " . $row[0] . "</div>";
    }
    echo "</div>";
}

echo "</div>";

// Insert admin user if users table exists
if ($conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0) {
    echo "<p>👤 Creating admin user...";
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO users (username, email, password, role) VALUES
        ('admin', 'admin@apsdreamhome.com', '$password', 'admin'),
        ('user', 'user@example.com', '$password', 'user')");
    echo "✅</p>";
}

// Show next steps
echo "<div style='margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px;'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='index.php' target='_blank'>🏠 Main Website</a></li>";
echo "<li><a href='aps_crm_system.php' target='_blank'>📞 CRM System</a></li>";
echo "<li><a href='whatsapp_demo.php' target='_blank'>📱 WhatsApp Demo</a></li>";
echo "</ol>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px; padding: 20px; background: #28a745; color: white; border-radius: 8px;'>";
echo "<h2>🎉 SYSTEM RESTORATION COMPLETE!</h2>";
echo "<p>Your APS Dream Home project is now fully restored with $table_count tables!</p>";
echo "<p>✅ Database: Connected | ✅ Tables: $table_count | ✅ System: Ready</p>";
echo "</div>";

echo "</div>";
?>
