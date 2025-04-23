<?php
// db_schema_audit.php
// Quick script to audit your MySQL database structure (tables and columns)

require_once __DIR__ . '/includes/config/config.php';

// Use the existing $con from config.php
if (!$con) {
    die('Database connection failed.');
}

$db_name = $con->query('SELECT DATABASE()')->fetch_row()[0];
echo "<h2>Database: $db_name</h2>";

$tables = [];
$res = $con->query('SHOW TABLES');
if ($res) {
    while ($row = $res->fetch_array()) {
        $tables[] = $row[0];
    }
}

if (empty($tables)) {
    echo '<p>No tables found.</p>';
    exit;
}

echo '<ul>';
foreach ($tables as $table) {
    echo "<li><strong>$table</strong><ul>";
    $cols = $con->query("SHOW COLUMNS FROM `$table`");
    if ($cols) {
        while ($col = $cols->fetch_assoc()) {
            echo '<li>' . htmlspecialchars($col['Field']) . ' - ' . htmlspecialchars($col['Type']);
            if ($col['Key']) echo ' <em>(' . htmlspecialchars($col['Key']) . ')</em>';
            if ($col['Null'] === 'NO') echo ' <strong>NOT NULL</strong>';
            if ($col['Default'] !== null) echo ' <span>DEFAULT: ' . htmlspecialchars($col['Default']) . '</span>';
            echo '</li>';
        }
    }
    echo '</ul></li>';
}
echo '</ul>';
?>
