<?php
require 'config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
$cols = $db->fetchAll('DESCRIBE user_properties');
foreach ($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . ($c['Null'] ?? '') . ' | ' . ($c['Default'] ?? '') . PHP_EOL;
}
