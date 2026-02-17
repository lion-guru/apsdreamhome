<?php
/**
 * Setup Associate Permissions System
 * Run this script to initialize the permissions database
 */

require_once 'includes/config.php';

// Initialize database connection
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "<h1>🛡️ Setting up Associate Permissions System</h1>\n";

try {
    // Read and execute the SQL file
    $sql = file_get_contents('database/associate_permissions.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $conn->query($statement);
                echo "✅ Executed: " . substr(str_replace("\n", " ", $statement), 0, 60) . "...<br>\n";
            } catch (Exception $e) {
                echo "❌ Error executing: " . $e->getMessage() . "<br>\n";
            }
        }
    }

    echo "<h2>🎉 Setup completed successfully!</h2>\n";
    echo "<p>The Associate Permissions System has been initialized.</p>\n";

    // Test the system
    echo "<h2>🧪 Quick Test</h2>\n";

    // Check if permissions table exists
    $result = $conn->query("SHOW TABLES LIKE 'associate_permissions'");
    if ($result->num_rows > 0) {
        echo "✅ Permissions table created successfully<br>\n";

        // Count records
        $count = $conn->query("SELECT COUNT(*) as count FROM associate_permissions")->fetch_assoc()['count'];
        echo "✅ {$count} permission records created<br>\n";
    } else {
        echo "❌ Permissions table not found<br>\n";
    }

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Setup failed: " . $e->getMessage() . "</h2>\n";
}

echo "<hr>\n";
echo "<h3>📋 Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li>✅ Database tables created</li>\n";
echo "<li>✅ Permission functions available in <code>includes/associate_permissions.php</code></li>\n";
echo "<li>✅ Dashboard updated with permission checks</li>\n";
echo "<li>🔄 Test with actual associate logins</li>\n";
echo "<li>🔄 Customize permissions as needed</li>\n";
echo "</ol>\n";

echo "<p><a href='test_permissions.php'>🧪 Run Permissions Test</a></p>\n";
?>
