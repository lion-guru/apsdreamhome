<?php
/**
 * APS Dream Homes - Simple Database Test
 * Tests basic database connectivity and settings
 */

echo "<h1>🗄️ APS Database Connection Test</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px;'>";

// Test 1: Check if config files exist
echo "<h2>📋 Test 1: Configuration Files</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$configFiles = [
    'includes/Database.php',
    'includes/config.php',
    'config.php'
];

foreach ($configFiles as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file - Found</p>";
    } else {
        echo "<p style='color: red;'>❌ $file - Missing</p>";
    }
}
echo "</div>";

// Test 2: Database Connection
echo "<h2>🔌 Test 2: Database Connection</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    // Include database configuration
    if (file_exists('config.php')) {
        include 'config.php';
    }

    // Try to connect using mysqli
    $conn = new mysqli(DB_HOST ?? 'localhost', DB_USER ?? 'root', DB_PASS ?? '', DB_NAME ?? 'apsdreamhome');

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "<p style='color: green; font-size: 18px;'>✅ Database Connected Successfully!</p>";
    echo "<p>📊 Database: " . ($conn->query("SELECT DATABASE()")->fetch_row()[0] ?? 'Unknown') . "</p>";

    // Test 3: Check site_settings table
    echo "</div><h2>⚙️ Test 3: Site Settings</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

    $settingsCheck = $conn->query("SHOW TABLES LIKE 'site_settings'");

    if ($settingsCheck->num_rows > 0) {
        echo "<p style='color: green;'>✅ site_settings table exists</p>";

        // Get sample settings
        $settings = $conn->query("SELECT setting_name, setting_value FROM site_settings LIMIT 10");

        if ($settings->num_rows > 0) {
            echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Setting</th><th style='padding: 8px;'>Value</th></tr>";

            while ($row = $settings->fetch_assoc()) {
                echo "<tr>";
                echo "<td style='padding: 8px;'>{$row['setting_name']}</td>";
                echo "<td style='padding: 8px;'>{$row['setting_value']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p style='color: green; margin-top: 10px;'>✅ Found {$settings->num_rows} settings</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ site_settings table not found</p>";
        echo "<p>You need to import the database configuration file.</p>";
        echo "<p><a href='database_setup.php' style='color: blue;'>Run Database Setup</a></p>";
    }

    $conn->close();

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection error</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><a href='database_setup.php' style='color: blue;'>Run Database Setup</a></p>";
}

echo "</div>";
echo "<hr>";
echo "<p><a href='index.php' style='color: blue; font-size: 18px;'>🏠 Go to Website</a></p>";
echo "</div>";
?>
