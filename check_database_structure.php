<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'apsdreamhomefinal';

// Required tables for APS Dream Home
$required_tables = [
    'users',
    'properties',
    'customers',
    'leads',
    'property_visits',
    'visit_reminders',
    'notifications',
    'notification_templates',
    'transactions',
    'bookings',
    'mlm_commissions',
    'mlm_commission_ledger',
    'mlm_tree'
];

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all tables in the database
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Check for missing tables
$missing_tables = array_diff($required_tables, $tables);

// Check for duplicate/redundant tables
$potential_duplicates = [];
foreach ($tables as $table) {
    foreach ($tables as $other_table) {
        if ($table !== $other_table && 
            (stripos($table, $other_table) !== false || 
             stripos($other_table, $table) !== false)) {
            $pair = [$table, $other_table];
            sort($pair);
            $potential_duplicates[implode(' <-> ', $pair)] = $pair;
        }
    }
}
$potential_duplicates = array_values($potential_duplicates);

// Check for tables with no data
$empty_tables = [];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $empty_tables[] = $table;
    }
}

// Check for tables without primary keys
$tables_without_pk = [];
$result = $conn->query("
    SELECT t.TABLE_NAME
    FROM INFORMATION_SCHEMA.TABLES t
    LEFT JOIN (
        SELECT TABLE_NAME
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = '$db'
        GROUP BY TABLE_NAME, INDEX_NAME
        HAVING SUM(CASE WHEN NON_UNIQUE = 0 AND NULLABLE != 'YES' THEN 1 ELSE 0 END) >= 1
    ) puks ON t.TABLE_NAME = puks.TABLE_NAME
    WHERE t.TABLE_SCHEMA = '$db' AND puks.TABLE_NAME IS NULL
    AND t.TABLE_TYPE = 'BASE TABLE'
");
while ($row = $result->fetch_assoc()) {
    $tables_without_pk[] = $row['TABLE_NAME'];
}

// Output results
echo "<h2>Database Structure Check</h2>";

// Missing tables
if (empty($missing_tables)) {
    echo "<p style='color: green;'>✅ All required tables exist.</p>";
} else {
    echo "<p style='color: red;'>❌ Missing tables: " . implode(', ', $missing_tables) . "</p>";
}

// Duplicate tables
if (empty($potential_duplicates)) {
    echo "<p style='color: green;'>✅ No potential duplicate tables found.</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Potential duplicate tables:</p><ul>";
    foreach ($potential_duplicates as $pair) {
        echo "<li>" . $pair[0] . " and " . $pair[1] . "</li>";
    }
    echo "</ul>";
}

// Empty tables
if (empty($empty_tables)) {
    echo "<p style='color: green;'>✅ All tables contain data.</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Empty tables: " . implode(', ', $empty_tables) . "</p>";
}

// Tables without primary keys
if (empty($tables_without_pk)) {
    echo "<p style='color: green;'>✅ All tables have primary keys.</p>";
} else {
    echo "<p style='color: red;'>❌ Tables without primary keys: " . implode(', ', $tables_without_pk) . "</p>";
}

// Check for foreign key constraints
$fk_issues = [];
$result = $conn->query("
    SELECT 
        TABLE_NAME, 
        COLUMN_NAME, 
        CONSTRAINT_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_SCHEMA = '$db'
");

if ($result->num_rows > 0) {
    echo "<h3>Foreign Key Relationships:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Table</th><th>Column</th><th>References</th><th>Referenced Column</th><th>Status</th></tr>";
    
    while ($fk = $result->fetch_assoc()) {
        $ref_check = $conn->query("SELECT COUNT(*) as count FROM `{$fk['TABLE_NAME']}` WHERE `{$fk['COLUMN_NAME']}` IS NOT NULL AND `{$fk['COLUMN_NAME']}` NOT IN (SELECT `{$fk['REFERENCED_COLUMN_NAME']}` FROM `{$fk['REFERENCED_TABLE_NAME']}`)");
        $ref_count = $ref_check->fetch_assoc()['count'];
        
        $status = $ref_count > 0 ? "<span style='color:red'>$ref_count broken references</span>" : "<span style='color:green'>OK</span>";
        
        echo "<tr>";
        echo "<td>{$fk['TABLE_NAME']}</td>";
        echo "<td>{$fk['COLUMN_NAME']}</td>";
        echo "<td>{$fk['REFERENCED_TABLE_NAME']}</td>";
        echo "<td>{$fk['REFERENCED_COLUMN_NAME']}</td>";
        echo "<td>$status</td>";
        echo "</tr>";
        
        if ($ref_count > 0) {
            $fk_issues[] = "Found $ref_count broken foreign key references in {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} referencing {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}";
        }
    }
    
    echo "</table>";
}

// Output any foreign key issues
if (!empty($fk_issues)) {
    echo "<h3>Foreign Key Issues:</h3><ul>";
    foreach ($fk_issues as $issue) {
        echo "<li style='color:red;'>$issue</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:green;'>✅ No foreign key constraint violations found.</p>";
}

// Close connection
$conn->close();
?>
