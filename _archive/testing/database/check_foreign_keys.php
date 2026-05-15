<?php
$db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo 'Checking foreign key constraints:' . "\n";
$stmt = $db->query('SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = "apsdreamhome" AND TABLE_NAME IN ("mlm_plans", "mlm_commission_levels", "mlm_commission_plans") AND REFERENCED_TABLE_NAME IS NOT NULL');
while ($row = $stmt->fetch()) {
    echo $row['CONSTRAINT_NAME'] . ': ' . $row['TABLE_NAME'] . '.' . $row['COLUMN_NAME'] . ' -> ' . $row['REFERENCED_TABLE_NAME'] . '.' . $row['REFERENCED_COLUMN_NAME'] . "\n";
}
?>
