<?php
$db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo 'Checking salaries table structure:' . "\n";
$stmt = $db->query('DESCRIBE salaries');
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} else {
    echo 'salaries table does not exist' . "\n";
}
?>
