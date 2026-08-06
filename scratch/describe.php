<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database;
$db = Database::getInstance();
$users = $db->fetchAll("DESCRIBE users");
$ledger = $db->fetchAll("DESCRIBE mlm_commission_ledger");
$associates = $db->fetchAll("DESCRIBE associates");
echo "=== users ===\n";
print_r($users);
echo "=== mlm_commission_ledger ===\n";
print_r($ledger);
echo "=== associates ===\n";
print_r($associates);
