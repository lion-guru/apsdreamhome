<?php
// Idempotent: union telecalling status vocabulary into leads.status ENUM.
// Existing CRM values preserved untouched; adds pending/follow_up/converted/
// not_interested which TelecallingController queries/writes. Additive only.
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';

$want = ['new', 'contacted', 'qualified', 'proposal', 'negotiation',
    'closed_won', 'closed_lost', 'nurture',
    'pending', 'follow_up', 'converted', 'not_interested'];

try {
    $db = \App\Core\Database\Database::getInstance();
    $cur = $db->fetchOne("SHOW COLUMNS FROM leads LIKE 'status'");
    preg_match_all("/'([^']+)'/", $cur['Type'], $m);
    $have = $m[1];
    $missing = array_values(array_diff($want, $have));
    if (empty($missing)) {
        echo "leads.status already includes telecalling vocabulary\n";
        exit(0);
    }
    $union = array_values(array_unique(array_merge($have, $missing)));
    $enumList = "'" . implode("','", $union) . "'";
    $null = ($cur['Null'] === 'YES') ? 'NULL' : 'NOT NULL';
    $default = ($cur['Default'] === null || strtoupper((string)$cur['Default']) === 'NULL')
        ? '' : " DEFAULT '" . addslashes($cur['Default']) . "'";
    // NOTE: AFTER `source` positional clause omitted — MODIFY keeps position.
    $db->execute("ALTER TABLE leads MODIFY COLUMN status ENUM($enumList) $null$default");
    echo 'extended with: ' . implode(',', $missing) . PHP_EOL;
} catch (\Throwable $e) {
    echo 'MIGRATE-FAILED: ' . substr($e->getMessage(), 0, 200) . PHP_EOL;
    exit(1);
}
