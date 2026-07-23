<?php
require __DIR__ . '/../vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();
echo $db->fetchColumn('SHOW TABLES LIKE "audit_log"');