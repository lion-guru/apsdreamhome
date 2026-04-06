<?php
$db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Checking all tables:\n";
$stmt = $db->query("SHOW TABLES");
while ($row = $stmt->fetch()) {
    echo "Table: " . $row[0] . "\n";
}

echo "\nChecking marketing_campaigns structure:\n";
$stmt = $db->query("DESCRIBE marketing_campaigns");
while ($row = $stmt->fetch()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
