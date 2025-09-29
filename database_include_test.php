<?php
/**
 * Database.php Test
 * Test if Database.php can be included correctly
 */

echo "<h1>🔧 Database.php Include Test</h1>";

try {
    // Test including Database.php
    require_once 'includes/Database.php';

    echo "<div style='color: green; font-size: 18px;'>✅ Database.php included successfully!</div>";

    // Check if Database class exists
    if (class_exists('Database')) {
        echo "<div style='color: green; font-size: 16px;'>✅ Database class found</div>";

        // Try to create instance
        $db = new Database();
        echo "<div style='color: green; font-size: 16px;'>✅ Database instance created</div>";

        // Check available methods
        $methods = get_class_methods($db);
        echo "<div style='color: blue; font-size: 14px;'>Available methods: " . implode(', ', $methods) . "</div>";

    } else {
        echo "<div style='color: red; font-size: 16px;'>❌ Database class not found</div>";
    }

} catch (Exception $e) {
    echo "<div style='color: red; font-size: 18px;'>❌ Error including Database.php: " . $e->getMessage() . "</div>";
}

echo "<hr>";

echo "<div style='margin-top: 20px;'>";
echo "<a href='aps_crm_system.php' style='color: green; text-decoration: none; font-size: 18px;'>🧪 Test APS CRM System</a> | ";
echo "<a href='db_test.php' style='color: green; text-decoration: none; font-size: 18px;'>🗄️ Test Database Connection</a> | ";
echo "<a href='auto_database_setup.php' style='color: green; text-decoration: none; font-size: 18px;'>⚙️ Setup Database</a>";
echo "</div>";
?>
