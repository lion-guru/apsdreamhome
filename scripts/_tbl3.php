<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Core\Database\Database;
$db = Database::getInstance();
$cols = $db->fetchAll('SHOW COLUMNS FROM activity_logs_unified');
foreach ($cols as $c) echo $c['Field'] . "\n";
