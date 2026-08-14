<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();
echo "=== payment_transactions (all rows) ===" . PHP_EOL;
foreach ($db->fetchAll("SELECT * FROM payment_transactions") as $r) {
    foreach ($r as $k => $v) {
        $s = is_string($v) ? substr($v, 0, 40) : $v;
        echo "  $k=$s | ";
    }
    echo PHP_EOL;
}
echo PHP_EOL . "=== bank_accounts (all rows) ===" . PHP_EOL;
foreach ($db->fetchAll("SELECT * FROM bank_accounts") as $r) {
    echo "  id={$r['id']} name={$r['account_name']} num={$r['account_number']} created={$r['created_at']}" . PHP_EOL;
}
echo PHP_EOL . "=== petty_cash (all rows) ===" . PHP_EOL;
foreach ($db->fetchAll("SELECT * FROM petty_cash") as $r) {
    echo "  id={$r['id']} type={$r['transaction_type']} amount={$r['amount']}" . PHP_EOL;
}
echo PHP_EOL . "=== expenses (latest 5) ===" . PHP_EOL;
foreach ($db->fetchAll("SELECT * FROM expenses ORDER BY id DESC LIMIT 5") as $r) {
    echo "  id={$r['id']} cat={$r['category']} amount={$r['amount']} desc={$r['description']} created={$r['created_at']}" . PHP_EOL;
}?>