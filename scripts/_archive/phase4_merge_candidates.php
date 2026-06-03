<?php
/**
 * PHASE 4: Find tables with low code refs (1-2) that have active FKs
 * These are potential merge candidates
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Pre-load all code refs
$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

// Group by similar purpose
$groups = [
    'wallet' => [],
    'commission' => [],
    'payout' => [],
    'attendance' => [],
    'salary' => [],
    'notification' => [],
    'audit' => [],
    'analytics' => [],
    'log' => [],
];

foreach ($tables as $t) {
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) {
        $codeRef += preg_match_all($pattern, $content);
    }
    if ($codeRef > 2) continue;

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $fkTo = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();

    $lower = strtolower($t);
    $cat = '';
    if (preg_match('/(wallet|points|reward)/', $lower)) $cat = 'wallet';
    elseif (preg_match('/(commission)/', $lower)) $cat = 'commission';
    elseif (preg_match('/(payout)/', $lower)) $cat = 'payout';
    elseif (preg_match('/(attendance|leave)/', $lower)) $cat = 'attendance';
    elseif (preg_match('/(salary|payroll)/', $lower)) $cat = 'salary';
    elseif (preg_match('/(notification|email_|sms_|whatsapp_)/', $lower)) $cat = 'notification';
    elseif (preg_match('/(audit|log|history|track)/', $lower)) $cat = 'audit';
    elseif (preg_match('/(analytics|metrics|stat|forecast)/', $lower)) $cat = 'analytics';

    if ($cat) {
        $groups[$cat][] = ['name' => $t, 'rows' => $rows, 'code' => $codeRef, 'fkTo' => $fkTo];
    }
}

foreach ($groups as $cat => $list) {
    if (empty($list)) continue;
    echo "\n=== $cat (" . count($list) . " candidates) ===\n";
    usort($list, fn($a, $b) => $b['rows'] - $a['rows']);
    foreach ($list as $t) {
        echo sprintf("  %-45s %5d rows  Code:%d  FK←:%d\n", $t['name'], $t['rows'], $t['code'], $t['fkTo']);
    }
}
