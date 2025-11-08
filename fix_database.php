<?php
/**
 * Database Setup & Fix Script
 * This script creates the missing site_settings table with correct structure
 */

require_once 'includes/db_connection.php';

try {
    $conn = getDbConnection();

    echo "<h2>🔧 Database Setup & Fix</h2>\n";

    // Check if site_settings table exists
    $check_table_sql = "SHOW TABLES LIKE 'site_settings'";
    $table_exists = $conn->query($check_table_sql)->rowCount() > 0;

    if (!$table_exists) {
        echo "<p>📋 Creating site_settings table...</p>\n";

        // Create site_settings table with correct structure
        $create_table_sql = "
            CREATE TABLE IF NOT EXISTS site_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_name VARCHAR(100) NOT NULL UNIQUE,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $conn->exec($create_table_sql);
        echo "<p>✅ site_settings table created successfully!</p>\n";
    } else {
        echo "<p>✅ site_settings table already exists.</p>\n";

        // Check current structure
        $describe_sql = "DESCRIBE site_settings";
        $columns = $conn->query($describe_sql)->fetchAll(PDO::FETCH_ASSOC);

        echo "<h4>📊 Current Table Structure:</h4>\n";
        echo "<ul>\n";
        foreach ($columns as $column) {
            echo "<li><strong>{$column['Field']}:</strong> {$column['Type']} " .
                 ($column['Null'] == 'NO' ? 'NOT NULL' : 'NULL') .
                 ($column['Key'] == 'PRI' ? ' (PRIMARY KEY)' : '') .
                 ($column['Key'] == 'UNI' ? ' (UNIQUE)' : '') . "</li>\n";
        }
        echo "</ul>\n";
    }

    // Insert default settings if they don't exist
    echo "<h3>📝 Inserting Default Settings...</h3>\n";

    $default_settings = [
        ['setting_name' => 'company_name', 'setting_value' => 'APS Dream Home'],
        ['setting_name' => 'company_phone', 'setting_value' => '+91-9000000001'],
        ['setting_name' => 'company_email', 'setting_value' => 'info@apsdreamhome.com'],
        ['setting_name' => 'company_address', 'setting_value' => 'Gorakhpur, Uttar Pradesh, India'],
        ['setting_name' => 'company_description', 'setting_value' => 'Your trusted partner in real estate solutions. We provide comprehensive property services with modern technology and personalized approach.'],
        ['setting_name' => 'working_hours', 'setting_value' => 'Mon-Sat: 9:00 AM - 8:00 PM'],
        ['setting_name' => 'facebook_url', 'setting_value' => 'https://facebook.com/apsdreamhome'],
        ['setting_name' => 'twitter_url', 'setting_value' => 'https://twitter.com/apsdreamhome'],
        ['setting_name' => 'instagram_url', 'setting_value' => 'https://instagram.com/apsdreamhome'],
        ['setting_name' => 'linkedin_url', 'setting_value' => 'https://linkedin.com/company/apsdreamhome']
    ];

    foreach ($default_settings as $setting) {
        // Check if setting exists
        $check_sql = "SELECT id FROM site_settings WHERE setting_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$setting['setting_name']]);

        if ($check_stmt->rowCount() == 0) {
            // Insert new setting
            $insert_sql = "INSERT INTO site_settings (setting_name, setting_value) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $result = $insert_stmt->execute([$setting['setting_name'], $setting['setting_value']]);

            if ($result) {
                echo "<p>✅ Added: {$setting['setting_name']}</p>\n";
            } else {
                echo "<p>❌ Failed to add: {$setting['setting_name']}</p>\n";
            }
        } else {
            echo "<p>⚠️ Already exists: {$setting['setting_name']}</p>\n";
        }
    }

    echo "<hr>\n";
    echo "<h3>🎉 Database Setup Complete!</h3>\n";
    echo "<p>✅ All tables created and configured</p>\n";
    echo "<p>✅ Default settings inserted</p>\n";
    echo "<p>✅ Ready for customization</p>\n";

    echo "<div class='mt-4'>\n";
    echo "<a href='update_company_info.php' class='btn btn-primary'>Try Company Info Update</a>\n";
    echo "<a href='index.php' class='btn btn-success'>Go to Homepage</a>\n";
    echo "<a href='admin_panel.php' class='btn btn-secondary'>Go to Admin Panel</a>\n";
    echo "</div>\n";

} catch (PDOException $e) {
    echo "<h3>❌ Database Error:</h3>\n";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>\n";
    echo "<p><strong>Code:</strong> " . $e->getCode() . "</p>\n";

    echo "<h4>🔧 Possible Solutions:</h4>\n";
    echo "<ul>\n";
    echo "<li>Make sure your database server is running</li>\n";
    echo "<li>Check your database credentials in db_connection.php</li>\n";
    echo "<li>Ensure the database user has CREATE and INSERT permissions</li>\n";
    echo "<li>Try creating the database manually if it doesn't exist</li>\n";
    echo "</ul>\n";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">🔧 Database Setup Required</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">This script will set up your database tables and fix the settings structure.</p>

                        <div class="alert alert-info">
                            <h6>📋 What this script does:</h6>
                            <ul>
                                <li>Creates the missing <code>site_settings</code> table</li>
                                <li>Adds proper column structure</li>
                                <li>Inserts default company settings</li>
                                <li>Prepares database for customization</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
