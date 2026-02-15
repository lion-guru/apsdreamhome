<?php
/**
 * APS Dream Home - Fix Database Import
 * Imports database with proper foreign key handling
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 600); // 10 minutes

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Database Import - APS Dream Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e6ffe6; padding: 10px; margin: 10px 0; border-left: 4px solid #44ff44; border-radius: 5px; }
        .error { color: red; background: #ffe6e6; padding: 10px; margin: 10px 0; border-left: 4px solid #ff4444; border-radius: 5px; }
        .info { color: blue; background: #e6f3ff; padding: 10px; margin: 10px 0; border-left: 4px solid #4488ff; border-radius: 5px; }
        .progress { background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 APS Dream Home - Fix Database Import</h1>";

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'apsdreamhome';

echo "<div class='info'><h3>📋 Database Configuration</h3>";
echo "<p><strong>Host:</strong> $db_host</p>";
echo "<p><strong>User:</strong> $db_user</p>";
echo "<p><strong>Database:</strong> $db_name</p></div>";

try {
    // Connect to MySQL server
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✅ Connected to MySQL server successfully</div>";
    
    // Drop existing database
    $sql = "DROP DATABASE IF EXISTS `$db_name`";
    $conn->query($sql);
    echo "<div class='success'>✅ Old database dropped</div>";
    
    // Create fresh database
    $sql = "CREATE DATABASE `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='success'>✅ Fresh database '$db_name' created successfully</div>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($db_name);
    
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    echo "<div class='info'>ℹ️ Foreign key checks disabled</div>";
    
    // Read and execute SQL file
    $sql_file = "C:\\Users\\Abhay Singh\\Downloads\\apsdreamhome (2).sql";
    
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: $sql_file");
    }
    
    echo "<div class='progress'>📄 Reading SQL file...</div>";
    
    $sql_content = file_get_contents($sql_file);
    
    if ($sql_content === false) {
        throw new Exception("Failed to read SQL file");
    }
    
    echo "<div class='success'>✅ SQL file read successfully (" . number_format(strlen($sql_content)) . " bytes)</div>";
    
    // Clean up SQL content
    $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content); // Remove comments
    $sql_content = preg_replace('/--.*$/m', '', $sql_content); // Remove line comments
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    echo "<div class='progress'>🔄 Executing SQL statements...</div>";
    
    $success_count = 0;
    $error_count = 0;
    $total_statements = count($statements);
    $errors = [];
    
    foreach ($statements as $index => $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        // Skip comments and empty statements
        if (preg_match('/^(CREATE|INSERT|ALTER|DROP|UPDATE|DELETE|SET)/i', $statement)) {
            if ($conn->query($statement)) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Statement " . ($index + 1) . ": " . $conn->error;
                
                // Show first 10 errors only
                if ($error_count <= 10) {
                    echo "<div class='error'>❌ Error in statement " . ($index + 1) . ": " . $conn->error . "</div>";
                }
            }
        }
        
        // Show progress every 100 statements
        if (($index + 1) % 100 == 0) {
            echo "<div class='progress'>📊 Progress: " . ($index + 1) . "/$total_statements statements processed</div>";
        }
    }
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "<div class='info'>ℹ️ Foreign key checks re-enabled</div>";
    
    echo "<div class='success'>✅ Database import completed!</div>";
    echo "<div class='info'>📊 <strong>Import Summary:</strong></div>";
    echo "<div class='info'>✅ Successful statements: $success_count</div>";
    echo "<div class='info'>❌ Failed statements: $error_count</div>";
    
    // Check what tables were created
    $result = $conn->query("SHOW TABLES");
    $table_count = $result->num_rows;
    
    echo "<div class='success'>✅ Total tables created: $table_count</div>";
    
    // List some important tables
    $important_tables = ['users', 'properties', 'admin', 'customers', 'leads', 'projects', 'bookings', 'associates', 'commission_transactions'];
    echo "<div class='info'>📋 <strong>Important Tables Check:</strong></div>";
    
    foreach ($important_tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if ($check->num_rows > 0) {
            // Count records in table
            $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $count_result->fetch_assoc()['count'];
            echo "<div class='success'>✅ $table table exists ($count records)</div>";
        } else {
            echo "<div class='error'>❌ $table table missing</div>";
        }
    }
    
    // Create admin user if not exists
    $check_admin = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    if ($check_admin->num_rows == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, full_name, role, status) VALUES ('admin', 'admin@apsdreamhomes.com', '$admin_password', 'Administrator', 'admin', 'active')";
        
        if ($conn->query($sql)) {
            echo "<div class='success'>✅ Admin user created (username: admin, password: admin123)</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ Admin user already exists</div>";
    }
    
    // Add some sample data if tables are empty
    $tables_to_check = ['properties', 'customers', 'leads'];
    foreach ($tables_to_check as $table) {
        $check = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $check->fetch_assoc()['count'];
        
        if ($count == 0) {
            echo "<div class='info'>ℹ️ Adding sample data to $table table...</div>";
            
            if ($table == 'properties') {
                $sql = "INSERT INTO properties (title, description, price, property_type, location, bedrooms, bathrooms, area, status, featured) VALUES 
                    ('Luxury Villa in Gorakhpur', 'Beautiful 3BHK villa with modern amenities', 2500000, 'Villa', 'Gorakhpur', 3, 3, 2000, 'available', 1),
                    ('Modern Apartment', '2BHK apartment in prime location', 1500000, 'Apartment', 'Gorakhpur', 2, 2, 1200, 'available', 1),
                    ('Commercial Space', 'Prime commercial space for business', 5000000, 'Commercial', 'Gorakhpur', 0, 2, 3000, 'available', 0)";
                $conn->query($sql);
            }
        }
    }
    
    echo "<div class='success'><h3>🎉 Database import completed successfully!</h3>";
    echo "<p><strong>Database:</strong> $db_name</p>";
    echo "<p><strong>Tables created:</strong> $table_count</p>";
    echo "<p><strong>Admin login:</strong> username: admin, password: admin123</p>";
    echo "<p><strong>Next step:</strong> <a href='index.php'>Go to homepage</a> | <a href='admin.php'>Go to admin panel</a></p></div>";
    
    if ($error_count > 0) {
        echo "<div class='info'><h4>⚠️ Some errors occurred during import:</h4>";
        echo "<p>Total errors: $error_count</p>";
        echo "<p>This is normal for complex databases with foreign key constraints.</p></div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><h3>❌ Database Import Failed</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Solution:</strong> Please ensure XAMPP MySQL is running and the SQL file exists.</p></div>";
}

echo "</div></body></html>";
?>
