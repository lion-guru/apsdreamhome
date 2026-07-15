<?php
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();
$cols = $db->fetchAll('SHOW COLUMNS FROM mlm_commission_ledger');
echo count($cols) . " columns\n";
foreach ($cols as $c) echo $c['Field'] . ' (' . $c['Type'] . ')' . "\n";