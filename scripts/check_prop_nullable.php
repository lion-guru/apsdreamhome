<?php
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

$cols = $db->fetchAll('SHOW COLUMNS FROM mlm_commission_ledger LIKE "property_id"');
print_r($cols);