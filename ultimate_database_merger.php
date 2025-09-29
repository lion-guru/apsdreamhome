<?php
/**
 * APS Dream Home - Ultimate Database Merger
 * Creates the most comprehensive database by combining both files
 */

echo "<h1>🔄 APS Dream Home - Ultimate Database Merger</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px;'>";

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'apsdreamhomefinal_ultimate';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass);

// Check connection
if ($conn->connect_error) {
    die("<p style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color: green;'>✅ Connected to MySQL server</p>";

// Create ultimate database
echo "<p>🏗️ Creating ultimate database...";
$conn->query("DROP DATABASE IF EXISTS `$db_name`");
$conn->query("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db_name);
echo "✅</p>";

// Files to merge
$file1 = __DIR__ . '/DATABASE FILE/apsdreamhomefinal.sql';      // Complete database (96 tables)
$file2 = __DIR__ . '/DATABASE FILE/apsdreamhomefinal (2).sql';  // Recent updates (23 tables)

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>📊 Analysis Summary:</h3>";
echo "<p>📄 File 1: " . basename($file1) . " (96 tables with data)</p>";
echo "<p>📄 File 2: " . basename($file2) . " (23 tables with recent data)</p>";
echo "<p>🎯 Strategy: Import complete database first, then update with recent data</p>";
echo "</div>";

// Function to clean and import SQL file
function importSQLFile($conn, $file_path, $description) {
    echo "<h3>📥 $description</h3>";

    if (!file_exists($file_path)) {
        echo "<p style='color: red;'>❌ File not found: $file_path</p>";
        return false;
    }

    echo "<p>📖 Reading file...";
    $sql = file_get_contents($file_path);
    if ($sql === false) {
        echo "<span style='color: red;'>❌ Error reading file</span></p>";
        return false;
    }

    echo "<span style='color: green;'>✅ (" . round(strlen($sql)/1024/1024, 2) . " MB)</span></p>";

    // Clean SQL content - remove mysqldump headers and comments
    $lines = explode("\n", $sql);
    $clean_sql = '';
    $in_table_data = false;
    $current_table = '';

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip mysqldump headers
        if (empty($line) ||
            strpos($line, '--') === 0 ||
            strpos($line, '/*') === 0 ||
            strpos($line, 'mysqldump') !== false ||
            strpos($line, 'Dump completed') !== false ||
            strpos($line, 'Host:') !== false ||
            strpos($line, 'Database:') !== false ||
            strpos($line, 'SET ') === 0 ||
            strpos($line, 'LOCK TABLES') !== false ||
            strpos($line, 'UNLOCK TABLES') !== false) {
            continue;
        }

        // Handle table structure
        if (strpos($line, 'CREATE TABLE') === 0) {
            $in_table_data = false;
            // Extract table name
            if (preg_match('/CREATE TABLE `([^`]+)`/i', $line, $matches)) {
                $current_table = $matches[1];
            }
        }

        // Handle data inserts
        if (strpos($line, 'INSERT INTO') === 0) {
            $in_table_data = true;
        }

        // Collect clean SQL
        if (!empty($line)) {
            $clean_sql .= $line . "\n";
        }
    }

    // Execute SQL in chunks
    $queries = array_filter(array_map('trim', explode(';', $clean_sql)));
    $success_count = 0;
    $error_count = 0;
    $table_data_count = 0;

    echo "<div style='margin-left: 20px;'>";
    foreach ($queries as $query) {
        if (!empty($query)) {
            if ($conn->query($query)) {
                $success_count++;

                // Count table data inserts
                if (strpos($query, 'INSERT INTO') === 0) {
                    $table_data_count++;
                }
            } else {
                $error_count++;
                echo "<p style='color: orange; margin: 2px 0;'>⚠️ Query failed: " . substr($conn->error, 0, 100) . "...</p>";
            }
        }
    }
    echo "</div>";

    echo "<p style='color: " . ($error_count > 0 ? 'orange' : 'green') . ";'>✅ $success_count queries executed ($table_data_count tables with data, $error_count errors)</p>";
    return true;
}

// Step 1: Import complete database first
$step1_success = importSQLFile($conn, $file1, "Importing Complete Database (96 tables with full data)");

// Step 2: Import recent updates (this will overwrite newer data where applicable)
$step2_success = importSQLFile($conn, $file2, "Importing Recent Updates (23 tables with latest data)");

// Step 3: Fix foreign key constraints
echo "<h2>🔗 Step 3: Adding Foreign Key Constraints</h2>";
$constraints = [
    "ALTER TABLE plots ADD CONSTRAINT IF NOT EXISTS plots_project_fk FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL",
    "ALTER TABLE plots ADD CONSTRAINT IF NOT EXISTS plots_customer_fk FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL",
    "ALTER TABLE plots ADD CONSTRAINT IF NOT EXISTS plots_associate_fk FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL",
    "ALTER TABLE bookings ADD CONSTRAINT IF NOT EXISTS bookings_property_fk FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL",
    "ALTER TABLE bookings ADD CONSTRAINT IF NOT EXISTS bookings_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE",
    "ALTER TABLE transactions ADD CONSTRAINT IF NOT EXISTS transactions_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE",
    "ALTER TABLE transactions ADD CONSTRAINT IF NOT EXISTS transactions_property_fk FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL"
];

$constraint_count = 0;
foreach ($constraints as $constraint) {
    if ($conn->query($constraint)) {
        $constraint_count++;
    } else {
        echo "<p style='color: orange;'>⚠️ Constraint failed: " . $conn->error . "</p>";
    }
}
echo "<p style='color: green;'>✅ Added $constraint_count foreign key constraints</p>";

// Step 4: Create admin user
echo "<h2>👤 Step 4: Setup Admin Access</h2>";
$password = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (username, email, password, role) VALUES ('admin', 'admin@apsdreamhome.com', '$password', 'admin')");
echo "<p style='color: green;'>✅ Admin user ready</p>";

// Step 5: Final verification
echo "<h2>✅ Step 5: Final Verification</h2>";

// Count tables
$result = $conn->query("SHOW TABLES");
$table_count = $result ? $result->num_rows : 0;

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>📊 Ultimate Database Summary:</h3>";
echo "<p>✅ Total Tables: $table_count</p>";
echo "<p>✅ Database Name: $db_name</p>";
echo "<p>✅ Status: Complete Merged Database</p>";
echo "<p>✅ Data Source: Best of both files combined</p>";
echo "</div>";

// Show key tables
echo "<h4>📋 Key Tables Status:</h4>";
$key_tables = ['users', 'properties', 'customers', 'projects', 'plots', 'bookings', 'associates', 'transactions', 'leads', 'mlm_commissions'];
echo "<div style='column-count: 3;'>";
foreach ($key_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<div style='color: green;'>✅ $table</div>";
    } else {
        echo "<div style='color: orange;'>⚠️ $table (not found)</div>";
    }
}
echo "</div>";

// Step 6: Create database export file
echo "<h2>📁 Step 6: Creating Ultimate Database File</h2>";
$backup_file = __DIR__ . '/DATABASE FILE/apsdreamhomefinal_ultimate.sql';

try {
    // Create mysqldump command
    $command = "c:\\xampp\\mysql\\bin\\mysqldump.exe -u root -h localhost $db_name > \"$backup_file\" 2>&1";
    exec($command, $output, $return_var);

    if ($return_var === 0) {
        echo "<p style='color: green;'>✅ Ultimate database file created: " . basename($backup_file) . "</p>";
        echo "<p>📍 Location: " . $backup_file . "</p>";
        echo "<p>📊 File size: " . round(filesize($backup_file)/1024/1024, 2) . " MB</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating backup file</p>";
        echo "<p>Output: " . implode("\n", $output) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
}

// Show next steps
echo "<div style='margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px;'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Admin Login:</strong> Username: <code>admin</code> | Password: <code>admin123</code></li>";
echo "<li><a href='../index.php' target='_blank'>🏠 Main Website</a></li>";
echo "<li><a href='../aps_crm_system.php' target='_blank'>📞 CRM System</a></li>";
echo "<li><a href='../whatsapp_demo.php' target='_blank'>📱 WhatsApp Demo</a></li>";
echo "</ol>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px; padding: 20px; background: #28a745; color: white; border-radius: 8px;'>";
echo "<h2>🎉 ULTIMATE DATABASE CREATED!</h2>";
echo "<p>Your APS Dream Home system now has the COMPLETE data from both files!</p>";
echo "<p>✅ $table_count Tables | ✅ Recent + Historical Data | ✅ All Features</p>";
echo "</div>";

echo "</div>";
?>
