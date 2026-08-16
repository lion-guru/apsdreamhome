<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

$r = $db->query('SHOW TABLES LIKE "site_visits"')->fetch();
echo 'site_visits: ' . ($r ? 'EXISTS' : 'MISSING') . PHP_EOL;

$r = $db->query('SHOW TABLES LIKE "property_visits"')->fetch();
echo 'property_visits: ' . ($r ? 'EXISTS' : 'MISSING') . PHP_EOL;