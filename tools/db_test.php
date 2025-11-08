<?php
/**
 * Database Connection Test
 * Test if the database connection is working properly
 */

define('INCLUDED_FROM_MAIN', true);
require_once 'includes/db_connection.php';

echo "<h1>Database Connection Test</h1>";

if ($pdo) {
    echo "<div style='color: green; font-size: 18px;'>✅ Database connection successful!</div>";

    try {
        // Test a simple query
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM company_settings");
        $result = $stmt->fetch();

        echo "<div style='color: blue; font-size: 16px;'>✅ Query executed successfully!</div>";
        echo "<p>Company settings records: " . $result['count'] . "</p>";

        // Test properties table
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM properties");
        $result = $stmt->fetch();

        echo "<p>Properties records: " . $result['count'] . "</p>";

    } catch (Exception $e) {
        echo "<div style='color: red; font-size: 16px;'>❌ Query failed: " . $e->getMessage() . "</div>";
    }

} else {
    echo "<div style='color: red; font-size: 18px;'>❌ Database connection failed!</div>";
    echo "<p>Please check your database configuration.</p>";
}

echo "<hr>";
echo "<a href='about_template.php'>Test About Page</a> | ";
echo "<a href='index_template.php'>Test Homepage</a> | ";
echo "<a href='contact_template.php'>Test Contact Page</a>";
?>
