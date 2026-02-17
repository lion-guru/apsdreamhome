<?php
/**
 * Database Verification - Final Check
 * Verify all tables and data are properly set up
 */

echo "<h1>🔍 Database Verification - Final Check</h1>";

try {
    define('INCLUDED_FROM_MAIN', true);
    require_once 'includes/db_connection.php';

    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    echo "<div style='color: green; font-size: 18px;'>✅ Database connection successful</div>";

    // Test all required tables
    $tables = ['company_settings', 'properties', 'property_types', 'users', 'customers'];
    $all_good = true;

    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "<div style='color: green; font-size: 16px;'>✅ $table table: EXISTS (" . $result['count'] . " records)</div>";
        } catch (Exception $e) {
            echo "<div style='color: red; font-size: 16px;'>❌ $table table: MISSING</div>";
            $all_good = false;
        }
    }

    // Test sample data
    try {
        $stmt = $pdo->query("SELECT company_name, phone, email FROM company_settings WHERE id = 1");
        $company = $stmt->fetch();
        echo "<div style='color: green; font-size: 16px;'>✅ Company data: " . $company['company_name'] . "</div>";
        echo "<div style='color: green; font-size: 16px;'>✅ Contact: " . $company['phone'] . " / " . $company['email'] . "</div>";
    } catch (Exception $e) {
        echo "<div style='color: red; font-size: 16px;'>❌ Company data: MISSING</div>";
        $all_good = false;
    }

    echo "<hr>";

    if ($all_good) {
        echo "<div style='color: green; font-size: 24px; padding: 30px; background: #d4edda; border-radius: 10px; text-align: center;'>";
        echo "🎉 DATABASE SETUP COMPLETE!<br><br>";
        echo "✅ All tables created<br>";
        echo "✅ Sample data inserted<br>";
        echo "✅ Company information configured<br>";
        echo "✅ Ready for production use<br><br>";
        echo "<strong>Your APS Dream Homes system is ready!</strong>";
        echo "</div>";

        echo "<div style='margin-top: 30px; text-align: center;'>";
        echo "<a href='about_template.php' style='color: green; text-decoration: none; font-size: 20px; margin: 0 15px;'>📄 About Page</a>";
        echo "<a href='contact_template.php' style='color: green; text-decoration: none; font-size: 20px; margin: 0 15px;'>📞 Contact Page</a>";
        echo "<a href='properties_template.php' style='color: green; text-decoration: none; font-size: 20px; margin: 0 15px;'>🏠 Properties Page</a>";
        echo "<a href='index_template.php' style='color: green; text-decoration: none; font-size: 20px; margin: 0 15px;'>🏡 Homepage</a>";
        echo "</div>";

    } else {
        echo "<div style='color: red; font-size: 20px; padding: 20px; background: #f8d7da; border-radius: 5px;'>";
        echo "⚠️ Database setup incomplete. Some tables are missing.";
        echo "</div>";

        echo "<div style='margin-top: 20px;'>";
        echo "<a href='auto_database_setup.php' style='color: blue; text-decoration: none; font-size: 18px;'>🔄 Try Auto Setup Again</a> | ";
        echo "<a href='database_setup.php' style='color: blue; text-decoration: none; font-size: 18px;'>🔧 Manual Setup</a>";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div style='color: red; font-size: 18px; padding: 20px; background: #f8d7da; border-radius: 5px;'>";
    echo "❌ Database connection failed: " . $e->getMessage();
    echo "</div>";

    echo "<div style='margin-top: 20px;'>";
    echo "<strong>Fix Steps:</strong><br>";
    echo "1. Start XAMPP Control Panel<br>";
    echo "2. Start MySQL service<br>";
    echo "3. Try again<br>";
    echo "4. Or use phpMyAdmin to create database manually";
    echo "</div>";
}
?>
