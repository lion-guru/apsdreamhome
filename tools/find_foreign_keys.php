<?php
// Configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

// Connect to DB
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

$sql = "
SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = '$dbname' AND REFERENCED_TABLE_NAME = 'user';
";

$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n--- Foreign Keys referencing 'user' ---\n";
foreach ($results as $row) {
    echo "Table: {$row['TABLE_NAME']} | Column: {$row['COLUMN_NAME']} | Constraint: {$row['CONSTRAINT_NAME']}\n";
}

$sqlAgents = "
SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = '$dbname' AND REFERENCED_TABLE_NAME = 'agents';
";

$stmt = $pdo->query($sqlAgents);
$resultsAgents = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n--- Foreign Keys referencing 'agents' ---\n";
foreach ($resultsAgents as $row) {
    echo "Table: {$row['TABLE_NAME']} | Column: {$row['COLUMN_NAME']} | Constraint: {$row['CONSTRAINT_NAME']}\n";
}
?>
