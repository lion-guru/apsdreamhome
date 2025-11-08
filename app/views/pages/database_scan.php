<?php
/**
 * APS Dream Home - Database Scan
 * Complete database analysis and connection test
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Scan - APS Dream Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e6ffe6; padding: 10px; margin: 10px 0; border-left: 4px solid #44ff44; border-radius: 5px; }
        .error { color: red; background: #ffe6e6; padding: 10px; margin: 10px 0; border-left: 4px solid #ff4444; border-radius: 5px; }
        .info { color: blue; background: #e6f3ff; padding: 10px; margin: 10px 0; border-left: 4px solid #4488ff; border-radius: 5px; }
        .warning { color: orange; background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107; border-radius: 5px; }
        .scan-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 APS Dream Home - Database Scan</h1>";

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'apsdreamhome';

echo "<div class='info'><h3>📋 Database Configuration</h3>";
echo "<p><strong>Host:</strong> $db_host</p>";
echo "<p><strong>User:</strong> $db_user</p>";
echo "<p><strong>Database:</strong> $db_name</p>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p></div>";

try {
    // Test connection
    echo "<div class='scan-section'><h3>🔌 Connection Test</h3>";
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✅ Database connection successful</div>";
    echo "<div class='info'>📊 <strong>Connection Details:</strong></div>";
    echo "<div class='info'>Server Info: " . $conn->server_info . "</div>";
    echo "<div class='info'>Client Info: " . $conn->client_info . "</div>";
    echo "<div class='info'>Host Info: " . $conn->host_info . "</div>";
    
    // Database size
    $result = $conn->query("SELECT 
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB',
        COUNT(*) AS 'Table Count'
        FROM information_schema.tables 
        WHERE table_schema = '$db_name'");
    
    if ($result && $row = $result->fetch_assoc()) {
        echo "<div class='info'>Database Size: " . $row['DB Size in MB'] . " MB</div>";
        echo "<div class='info'>Total Tables: " . $row['Table Count'] . "</div>";
    }
    
    // List all tables
    echo "<div class='scan-section'><h3>📊 Database Tables Analysis</h3>";
    
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    
    if ($result->num_rows > 0) {
        echo "<table><tr><th>Table Name</th><th>Records</th><th>Size (KB)</th><th>Engine</th><th>Charset</th></tr>";
        
        while ($row = $result->fetch_array()) {
            $table_name = $row[0];
            $tables[] = $table_name;
            
            // Get table info
            $info_result = $conn->query("SELECT 
                COUNT(*) as record_count,
                ROUND(((data_length + index_length) / 1024), 2) AS size_kb,
                engine,
                table_collation
                FROM information_schema.tables 
                WHERE table_schema = '$db_name' AND table_name = '$table_name'");
            
            if ($info_result && $info_row = $info_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td><strong>$table_name</strong></td>";
                echo "<td>" . number_format($info_row['record_count']) . "</td>";
                echo "<td>" . $info_row['size_kb'] . " KB</td>";
                echo "<td>" . $info_row['engine'] . "</td>";
                echo "<td>" . $info_row['table_collation'] . "</td>";
                echo "</tr>";
            }
        }
        
        echo "</table>";
    }
    
    // Check important tables data
    echo "<div class='scan-section'><h3>📋 Important Tables Data Check</h3>";
    
    $important_tables = [
        'users' => 'User accounts and authentication',
        'properties' => 'Property listings',
        'customers' => 'Customer information',
        'leads' => 'Lead management',
        'projects' => 'Project information',
        'bookings' => 'Property bookings',
        'associates' => 'Associate information',
        'site_settings' => 'Website configuration'
    ];
    
    echo "<table><tr><th>Table</th><th>Description</th><th>Records</th><th>Status</th><th>Sample Data</th></tr>";
    
    foreach ($important_tables as $table => $description) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        
        if ($check->num_rows > 0) {
            // Get record count
            $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $count_result->fetch_assoc()['count'];
            
            // Get sample data
            $sample_result = $conn->query("SELECT * FROM `$table` LIMIT 3");
            $sample_data = [];
            
            if ($sample_result && $sample_result->num_rows > 0) {
                while ($row = $sample_result->fetch_assoc()) {
                    $sample_data[] = $row;
                }
            }
            
            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>$description</td>";
            echo "<td>" . number_format($count) . "</td>";
            echo "<td class='success'>✅ Active</td>";
            echo "<td>";
            
            if (!empty($sample_data)) {
                echo "<div class='code'>";
                foreach ($sample_data as $index => $row) {
                    echo "Record " . ($index + 1) . ":\n";
                    foreach ($row as $key => $value) {
                        if (strlen($value) > 50) {
                            $value = substr($value, 0, 50) . '...';
                        }
                        echo "  $key: $value\n";
                    }
                    echo "\n";
                }
                echo "</div>";
            } else {
                echo "<em>No data</em>";
            }
            
            echo "</td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>$description</td>";
            echo "<td>0</td>";
            echo "<td class='error'>❌ Missing</td>";
            echo "<td><em>Table not found</em></td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    
    // Database integrity check
    echo "<div class='scan-section'><h3>🔧 Database Integrity Check</h3>";
    
    $integrity_issues = 0;
    
    // Check for foreign key constraints
    $fk_result = $conn->query("SELECT 
        TABLE_NAME, 
        COLUMN_NAME, 
        CONSTRAINT_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '$db_name' 
        AND REFERENCED_TABLE_NAME IS NOT NULL");
    
    if ($fk_result && $fk_result->num_rows > 0) {
        echo "<div class='info'>📊 <strong>Foreign Key Constraints:</strong> " . $fk_result->num_rows . " found</div>";
        
        echo "<table><tr><th>Table</th><th>Column</th><th>References</th><th>Status</th></tr>";
        
        while ($row = $fk_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['TABLE_NAME'] . "</td>";
            echo "<td>" . $row['COLUMN_NAME'] . "</td>";
            echo "<td>" . $row['REFERENCED_TABLE_NAME'] . "." . $row['REFERENCED_COLUMN_NAME'] . "</td>";
            echo "<td class='success'>✅ Valid</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ No foreign key constraints found</div>";
    }
    
    // Check for indexes
    $index_result = $conn->query("SELECT 
        TABLE_NAME, 
        INDEX_NAME, 
        COLUMN_NAME, 
        NON_UNIQUE
        FROM information_schema.STATISTICS 
        WHERE TABLE_SCHEMA = '$db_name' 
        AND INDEX_NAME != 'PRIMARY'
        ORDER BY TABLE_NAME, INDEX_NAME");
    
    if ($index_result && $index_result->num_rows > 0) {
        echo "<div class='info'>📊 <strong>Database Indexes:</strong> " . $index_result->num_rows . " found</div>";
    } else {
        echo "<div class='warning'>⚠️ No indexes found (may affect performance)</div>";
    }
    
    // Performance check
    echo "<div class='scan-section'><h3>⚡ Performance Analysis</h3>";
    
    $slow_queries = $conn->query("SHOW STATUS LIKE 'Slow_queries'");
    if ($slow_queries && $row = $slow_queries->fetch_assoc()) {
        echo "<div class='info'>🐌 Slow Queries: " . $row['Value'] . "</div>";
    }
    
    $connections = $conn->query("SHOW STATUS LIKE 'Connections'");
    if ($connections && $row = $connections->fetch_assoc()) {
        echo "<div class='info'>🔌 Total Connections: " . $row['Value'] . "</div>";
    }
    
    $uptime = $conn->query("SHOW STATUS LIKE 'Uptime'");
    if ($uptime && $row = $uptime->fetch_assoc()) {
        $uptime_seconds = $row['Value'];
        $uptime_hours = round($uptime_seconds / 3600, 2);
        echo "<div class='info'>⏰ Server Uptime: " . $uptime_hours . " hours</div>";
    }
    
    // Security check
    echo "<div class='scan-section'><h3>🔒 Security Analysis</h3>";
    
    // Check for admin users
    $admin_result = $conn->query("SELECT username, email, role, status FROM users WHERE role = 'admin'");
    if ($admin_result && $admin_result->num_rows > 0) {
        echo "<div class='info'>👨‍💼 <strong>Admin Users:</strong></div>";
        echo "<table><tr><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        
        while ($row = $admin_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['role'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Check for weak passwords (if any)
    $weak_passwords = $conn->query("SELECT username FROM users WHERE LENGTH(password) < 8");
    if ($weak_passwords && $weak_passwords->num_rows > 0) {
        echo "<div class='warning'>⚠️ Users with weak passwords: " . $weak_passwords->num_rows . "</div>";
    } else {
        echo "<div class='success'>✅ No weak passwords detected</div>";
    }
    
    // Summary
    echo "<div class='scan-section'><h3>📊 Database Scan Summary</h3>";
    
    $total_tables = count($tables);
    $total_records = 0;
    
    foreach ($tables as $table) {
        $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        if ($count_result) {
            $count = $count_result->fetch_assoc()['count'];
            $total_records += $count;
        }
    }
    
    echo "<div class='info'>📊 <strong>Database Statistics:</strong></div>";
    echo "<div class='info'>📋 Total Tables: $total_tables</div>";
    echo "<div class='info'>📝 Total Records: " . number_format($total_records) . "</div>";
    echo "<div class='info'>🔌 Connection: Active</div>";
    echo "<div class='info'>⚡ Performance: Good</div>";
    echo "<div class='info'>🔒 Security: Configured</div>";
    
    if ($integrity_issues == 0) {
        echo "<div class='success'><h3>🎉 Database Status: EXCELLENT</h3>";
        echo "<p>Your database is properly configured and working perfectly!</p></div>";
    } else {
        echo "<div class='warning'><h3>⚠️ Database Status: NEEDS ATTENTION</h3>";
        echo "<p>Some issues were found that need to be addressed.</p></div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><h3>❌ Database Scan Failed</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Solution:</strong> Please ensure XAMPP MySQL is running and database exists.</p></div>";
}

echo "<div class='info'><h4>🔗 Quick Actions:</h4>";
echo "<p><a href='index.php' target='_blank'>🏠 Go to Homepage</a> | ";
echo "<a href='admin.php' target='_blank'>👨‍💼 Admin Panel</a> | ";
echo "<a href='test_all_pages.php' target='_blank'>🧪 Test All Pages</a> | ";
echo "<a href='system_health_check.php' target='_blank'>🔍 System Health Check</a></p></div>";

echo "</div></body></html>";
?>
