<?php
// Check Database Structure
require_once __DIR__ . '/includes/db_connection.php';

// Required tables and their expected columns
$required_tables = [
    'users' => ['id', 'username', 'email', 'password', 'role', 'status'],
    'properties' => ['id', 'title', 'description', 'price', 'status', 'owner_id'],
    'customers' => ['id', 'name', 'email', 'phone', 'address'],
    'leads' => ['id', 'customer_id', 'property_id', 'source', 'status'],
    'property_visits' => ['id', 'customer_id', 'property_id', 'visit_date', 'visit_time', 'status'],
    'notifications' => ['id', 'user_id', 'type', 'title', 'message', 'status'],
    'notification_templates' => ['id', 'type', 'title_template', 'message_template']
];

echo "<h2>Database Structure Check</h2>";

try {
    $conn = getDbConnection();
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get all tables
    $result = $conn->query("SHOW TABLES");
    $existing_tables = [];
    while ($row = $result->fetch_array()) {
        $existing_tables[] = $row[0];
    }
    
    echo "<h3>Found " . count($existing_tables) . " tables in the database</h3>";
    
    // Check each required table
    foreach ($required_tables as $table => $columns) {
        if (in_array($table, $existing_tables)) {
            echo "<div>✓ Table <strong>$table</strong> exists</div>";
            
            // Check columns
            $result = $conn->query("DESCRIBE `$table`");
            $existing_columns = [];
            while ($row = $result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            $missing_columns = array_diff($columns, $existing_columns);
            if (empty($missing_columns)) {
                echo "<div style='margin-left: 20px; color: green;'>✓ All required columns exist</div>";
            } else {
                echo "<div style='margin-left: 20px; color: red;'>✗ Missing columns: " . implode(', ', $missing_columns) . "</div>";
            }
            
        } else {
            echo "<div style='color: red;'>✗ Table <strong>$table</strong> is missing</div>";
        }
    }
    
    // Check for demo data
    echo "<h3>Demo Data Check</h3>";
    $tables_to_check = ['users', 'properties', 'customers', 'leads'];
    foreach ($tables_to_check as $table) {
        if (in_array($table, $existing_tables)) {
            $count = $conn->query("SELECT COUNT(*) as count FROM `$table`")->fetch_assoc()['count'];
            echo "<div>$table: $count records</div>";
        }
    }
    
    // Check admin user
    if (in_array('users', $existing_tables)) {
        echo "<h3>Admin User Check</h3>";
        $result = $conn->query("SELECT id, username, email, role, status FROM users WHERE role = 'admin' LIMIT 1");
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            echo "<div>✓ Found admin user: " . htmlspecialchars($admin['username']) . "</div>";
            echo "<div>Email: " . htmlspecialchars($admin['email']) . "</div>";
            echo "<div>Status: " . htmlspecialchars($admin['status']) . "</div>";
        } else {
            echo "<div style='color: red;'>✗ No admin user found</div>";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
