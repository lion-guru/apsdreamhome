<?php
$db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo 'Checking table existence:' . "\n";
$stmt = $db->query('SHOW TABLES LIKE "projects"');
if ($stmt->rowCount() > 0) {
    echo 'Projects table exists' . "\n";
    $stmt = $db->query('DESCRIBE projects');
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} else {
    echo 'Projects table does not exist' . "\n";
}
?>
