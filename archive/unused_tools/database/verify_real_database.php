<?php
/**
 * APS Dream Home - Real Database Verification Script
 * Checks if all updates are applied to the live database
 */

echo "<h1>🗄️ Real Database Verification</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px;'>";

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'apsdreamhome';

echo "<h2>🔌 Database Connection Test</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "<p style='color: green; font-size: 18px;'>✅ Database Connected Successfully!</p>";
    echo "<p>📊 Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "</p>";

} catch (Exception $e) {
    echo "<p style='color: red; font-size: 18px;'>❌ Database Connection Failed</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>You need to create the database first or check your connection settings.</p>";
    exit;
}

echo "</div>";

// Check critical tables
echo "<h2>📋 Critical Tables Check</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$criticalTables = [
    'users' => 'User management',
    'properties' => 'Property listings',
    'site_settings' => 'Header/Footer settings',
    'password_security' => 'Security features',
    'user_sessions' => 'Session management',
    'system_logs' => 'Activity logging',
    'api_keys' => 'API management',
    'property_amenities' => 'Modern property features',
    'social_media_links' => 'Social media integration',
    'seo_metadata' => 'SEO optimization',
    'user_roles' => 'Role-based access',
    'activity_logs' => 'Audit trail'
];

$tableStatus = [];
foreach ($criticalTables as $table => $description) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $exists = $result->num_rows > 0;

    $color = $exists ? 'green' : 'red';
    $icon = $exists ? '✅' : '❌';

    echo "<p style='color: $color; margin: 5px 0;'><strong>$icon $table:</strong> $description";

    if ($exists) {
        // Get row count
        $countResult = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        if ($countResult) {
            $count = $countResult->fetch_assoc()['count'];
            echo " ($count records)";
        }
    }

    echo "</p>";
    $tableStatus[$table] = $exists;
}

echo "</div>";

// Check site settings
echo "<h2>⚙️ Site Settings Verification</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$settingsCheck = $conn->query("SELECT COUNT(*) as count FROM site_settings");
if ($settingsCheck) {
    $settingsCount = $settingsCheck->fetch_assoc()['count'];
    echo "<p><strong>Site Settings:</strong> $settingsCount configurations found</p>";

    if ($settingsCount > 0) {
        // Show sample settings
        $sampleSettings = $conn->query("SELECT setting_name, setting_value FROM site_settings LIMIT 5");
        echo "<table border='1' style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Setting</th><th style='padding: 8px;'>Value</th></tr>";

        while ($setting = $sampleSettings->fetch_assoc()) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>{$setting['setting_name']}</td>";
            echo "<td style='padding: 8px;'>{$setting['setting_value']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Could not check site settings</p>";
}

echo "</div>";

// Summary and recommendations
echo "<h2>🎯 Verification Summary</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

$totalTables = count($criticalTables);
$existingTables = count(array_filter($tableStatus));

echo "<p><strong>Database Status:</strong> $existingTables/$totalTables critical tables exist</p>";

if ($existingTables == $totalTables) {
    echo "<p style='color: green; font-size: 16px;'>✅ Database is fully updated with all modern features!</p>";
    echo "<p>Your database has all security features, modern enhancements, and is ready for production.</p>";
} elseif ($existingTables > 0) {
    echo "<p style='color: orange; font-size: 16px;'>⚠️ Database partially updated</p>";
    echo "<p>Some tables exist but you may need to apply remaining updates.</p>";
    echo "<p><a href='database/02_security_updates/critical_security_update.sql' style='color: blue;'>Apply Security Updates</a> | ";
    echo "<a href='database/modern_enhancements.sql' style='color: blue;'>Apply Modern Enhancements</a></p>";
} else {
    echo "<p style='color: red; font-size: 16px;'>❌ Database needs complete setup</p>";
    echo "<p>You need to import the main database file and apply all updates.</p>";
    echo "<p><a href='database/01_core_databases/apsdreamhome.sql' style='color: blue;'>Import Main Database</a></p>";
}

echo "</div>";

$conn->close();

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "Verified on: " . date('Y-m-d H:i:s') . " | ";
echo "Database: $dbname | ";
echo "Verification by: APS Dream Home System";
echo "</p>";

echo "</div>";
?>
