<?php
// Simple Database Import Script
echo "<h1>🔄 Importing Database...</h1>";

// Database config
$db = new mysqli('localhost', 'root', '', 'apsdreamhome');
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

// Recreate database
$db->query("DROP DATABASE IF EXISTS apsdreamhome");
$db->query("CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4");
$db->select_db('apsdreamhome');

// Get all SQL files
$files = glob('db_backups/*.sql');
$tables_created = 0;

echo "<p>Found " . count($files) . " SQL files to import</p><hr>";

foreach ($files as $file) {
    echo "<p>Importing: " . basename($file) . "... ";
    $sql = file_get_contents($file);
    $queries = array_filter(explode(';', $sql), 'strlen');
    
    $success = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if ($db->query($query)) $success++;
        }
    }
    
    // Count new tables
    $result = $db->query("SHOW TABLES");
    $new_count = $result ? $result->num_rows : 0;
    $added = $new_count - $tables_created;
    $tables_created = $new_count;
    
    echo "✅ $success queries ($added tables)</p>";
    flush();
}

// Show final count
$result = $db->query("SHOW TABLES");
$tables = $result ? $result->num_rows : 0;

echo "<hr><h2>✅ Import Complete!</h2>";
echo "<p>Total tables created: $tables</p>";

echo "<h3>System Links:</h3>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>🏠 Main Website</a></li>";
echo "<li><a href='aps_crm_system.php' target='_blank'>📞 CRM System</a></li>";
echo "<li><a href='whatsapp_demo.php' target='_blank'>📱 WhatsApp Demo</a></li>";
echo "</ul>";
?>
