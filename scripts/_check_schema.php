<?php
require_once __DIR__ . '/../vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();
$r = $db->query('DESCRIBE admin_menu_items');
while($row = $r->fetch(\PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' | ' . $row['Type'] . PHP_EOL;
}
