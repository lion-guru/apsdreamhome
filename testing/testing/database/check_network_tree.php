<?php
$db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo 'Checking mlm_network_tree structure:' . "\n";
$stmt = $db->query('DESCRIBE mlm_network_tree');
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} else {
    echo 'mlm_network_tree table does not exist' . "\n";
}
?>
