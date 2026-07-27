<?php
require_once __DIR__ . '/../vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$cols = $db->query("SHOW COLUMNS FROM admin_menu_items")->fetchAll(\PDO::FETCH_ASSOC);
foreach($cols as $c) echo $c['Field'] . ' (' . $c['Type'] . ')' . PHP_EOL;
