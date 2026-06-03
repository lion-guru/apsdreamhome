<?php
/**
 * Senior Dev Deep Database Analysis
 * Connects to real DB and provides actionable insights
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== REAL DATABASE STATE (LIVE QUERY) ===\n\n";

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$total = count($tables);
echo "Total tables: $total\n";

// Classify all tables
$withData = [];
$empty = [];
$errors = [];
foreach ($tables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        if ($count > 0) {
            $withData[$t] = $count;
        } else {
            $empty[] = $t;
        }
    } catch (Exception $e) {
        $errors[$t] = $e->getMessage();
    }
}

arsort($withData);
sort($empty);

echo "Tables with data: " . count($withData) . " (" . round(count($withData)/$total*100,1) . "%)\n";
echo "Empty tables: " . count($empty) . " (" . round(count($empty)/$total*100,1) . "%)\n";
echo "Broken tables: " . count($errors) . "\n";

echo "\n=== TOP 20 TABLES BY ROW COUNT ===\n";
$i = 0;
foreach ($withData as $t => $c) {
    if ($i++ >= 20) break;
    echo str_pad($t, 35) . " " . number_format($c) . " rows\n";
}

echo "\n=== USER ECOSYSTEM (5 tables) ===\n";
foreach (['users', 'customers', 'admin_users', 'agents', 'associates', 'employees'] as $t) {
    $c = $withData[$t] ?? 0;
    $note = '';
    if ($t === 'users') {
        $roles = $pdo->query("SELECT role, COUNT(*) c FROM users GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);
        $note = ' (';
        foreach ($roles as $r) $note .= $r['role'].':'.$r['c'].' ';
        $note = rtrim($note).')';
    }
    echo str_pad($t, 20) . " $c rows$note\n";
}

echo "\n=== LEAD ECOSYSTEM (17+ tables) ===\n";
$leadTables = array_filter($tables, fn($t) => preg_match('/^lead/i', $t));
foreach ($leadTables as $t) {
    $c = $withData[$t] ?? 0;
    echo str_pad($t, 35) . " $c rows\n";
}

echo "\n=== AI/VOICE TABLES (50+) ===\n";
$aiTables = array_filter($tables, fn($t) => preg_match('/^(ai_|voice_)/i', $t));
foreach ($aiTables as $t) {
    $c = $withData[$t] ?? 0;
    echo str_pad($t, 40) . " $c rows\n";
}

echo "\n=== MLM TABLES ===\n";
$mlmTables = array_filter($tables, fn($t) => preg_match('/^mlm_|^network_|^wallet_/i', $t));
foreach ($mlmTables as $t) {
    $c = $withData[$t] ?? 0;
    echo str_pad($t, 40) . " $c rows\n";
}

echo "\n=== EMPTY TABLES CATEGORIZED ===\n";
$categories = [
    'AI/Voice' => [],
    'MLM/Network' => [],
    'Lead/CRM' => [],
    'Property/Plot' => [],
    'HRM/Payroll' => [],
    'System/Log' => [],
    'Experimental' => [],
    'Other' => []
];
foreach ($empty as $t) {
    if (preg_match('/^(ai_|voice_|chat_)/i', $t)) $categories['AI/Voice'][] = $t;
    elseif (preg_match('/^(mlm_|network_|wallet_|commission_|payout_|associate_)/i', $t)) $categories['MLM/Network'][] = $t;
    elseif (preg_match('/^lead/i', $t)) $categories['Lead/CRM'][] = $t;
    elseif (preg_match('/^(property|plot|project|site|booking)/i', $t)) $categories['Property/Plot'][] = $t;
    elseif (preg_match('/^(hr|employee|salary|payroll|attendance|leave)/i', $t)) $categories['HRM/Payroll'][] = $t;
    elseif (preg_match('/(log|audit|event|track|history|queue|metrics|analytics|report|cache|temp)/i', $t)) $categories['System/Log'][] = $t;
    elseif (preg_match('/(blockchain|iot|metaverse|edge_|quantum|nft|crypto|token|web3|pwa|3d_)/i', $t)) $categories['Experimental'][] = $t;
    else $categories['Other'][] = $t;
}
foreach ($categories as $cat => $list) {
    if (count($list) > 0) {
        echo "\n$cat (" . count($list) . " tables):\n";
        foreach ($list as $t) echo "  - $t\n";
    }
}

echo "\n=== FK CONSTRAINT CHECK (USERS) ===\n";
$fkCount = $pdo->query("
    SELECT TABLE_NAME, CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'users' AND TABLE_SCHEMA = 'apsdreamhome'
")->fetchAll();
echo "Tables with FK to users: " . count($fkCount) . "\n";
foreach ($fkCount as $fk) echo "  - {$fk['TABLE_NAME']}->{$fk['CONSTRAINT_NAME']}\n";

echo "\n=== FK CONSTRAINT CHECK (CUSTOMERS - LEGACY) ===\n";
$fkCust = $pdo->query("
    SELECT TABLE_NAME, CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'customers' AND TABLE_SCHEMA = 'apsdreamhome'
")->fetchAll();
echo "Tables with FK to customers (legacy): " . count($fkCust) . "\n";
foreach ($fkCust as $fk) echo "  - {$fk['TABLE_NAME']}->{$fk['CONSTRAINT_NAME']}\n";

echo "\n=== ORPHANED RECORDS CHECK ===\n";
// Check for FK violations across all tables
$orphanCount = 0;
foreach ($withData as $t => $c) {
    if ($c === 0) continue;
    // Skip user tables and lookups
    if (in_array($t, ['users','customers','admin_users','agents','associates','employees'])) continue;

    try {
        $fk = $pdo->query("
            SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = '$t'
              AND REFERENCED_TABLE_NAME IS NOT NULL
              AND TABLE_SCHEMA = 'apsdreamhome'
        ")->fetch(PDO::FETCH_ASSOC);

        if ($fk) {
            $col = $fk['COLUMN_NAME'];
            $refT = $fk['REFERENCED_TABLE_NAME'];
            $refC = $fk['REFERENCED_COLUMN_NAME'];
            $orphans = $pdo->query("
                SELECT COUNT(*) FROM `$t` t1
                LEFT JOIN `$refT` t2 ON t1.`$col` = t2.`$refC`
                WHERE t1.`$col` IS NOT NULL AND t2.`$refC` IS NULL
            ")->fetchColumn();
            if ($orphans > 0) {
                echo "  $t.$col -> $refT.$refC: $orphans ORPHANED rows\n";
                $orphanCount += $orphans;
            }
        }
    } catch (Exception $e) {}
}
echo "\nTotal orphaned rows across all FKs: $orphanCount\n";

echo "\n=== ANALYSIS COMPLETE ===\n";
