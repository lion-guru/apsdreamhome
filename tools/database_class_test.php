<?php
/**
 * Database.php Test
 * Test if the Database.php file is working correctly
 */

echo "<h1>Database.php Test</h1>";

try {
    // Test the Database.php file
    require_once 'includes/Database.php';

    echo "<div style='color: green; font-size: 18px;'>✅ Database.php loaded successfully!</div>";

    // Check if the Database class exists
    if (class_exists('Database')) {
        echo "<div style='color: blue; font-size: 16px;'>✅ Database class found!</div>";

        // Try to create an instance
        $db = new Database();
        echo "<div style='color: blue; font-size: 16px;'>✅ Database instance created!</div>";

        // Check if connection works
        if (method_exists($db, 'connect') || method_exists($db, 'getConnection')) {
            echo "<div style='color: blue; font-size: 16px;'>✅ Database connection methods available!</div>";
        }

    } else {
        echo "<div style='color: red; font-size: 16px;'>❌ Database class not found!</div>";
    }

} catch (Exception $e) {
    echo "<div style='color: red; font-size: 18px;'>❌ Error loading Database.php: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<a href='aps_crm_system.php'>Test APS CRM System</a> | ";
echo "<a href='database_setup.php'>Set Up Database</a> | ";
echo "<a href='db_test.php'>Test DB Connection</a>";
?>
