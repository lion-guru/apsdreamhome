<?php
require __DIR__ . '/../vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();
print_r($db->fetchAll('DESCRIBE audit_log'));