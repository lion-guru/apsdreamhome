<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
foreach (['bank_accounts', 'user_bank_accounts'] as $t) {
    echo PHP_EOL . "=== $t ===" . PHP_EOL;
    foreach ($pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        echo "  " . $c['Field'] . ' (' . $c['Type'] . ')' . PHP_EOL;
    }
}
