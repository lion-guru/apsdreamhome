<?php
$db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo 'Checking mlm_plans structure:' . "\n";
$stmt = $db->query('DESCRIBE mlm_plans');
while ($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
echo "\nChecking mlm_commission_levels structure:\n";
$stmt = $db->query('DESCRIBE mlm_commission_levels');
while ($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
?>
