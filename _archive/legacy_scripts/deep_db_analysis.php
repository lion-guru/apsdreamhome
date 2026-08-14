<?php
/**
 * DEEP DATABASE ANALYSIS
 * - Current state vs backup vs ideal real estate ERP
 * - Classify tables by domain, criticality, current usage
 * - Identify gaps and over-engineering
 */
set_time_limit(180);
$log = [];

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== STEP 1: Current DB state ===\n";
$allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Current tables: " . count($allTables) . "\n";

echo "\n=== STEP 2: Backup state ===\n";
$backup = file_get_contents('C:\xampp\htdocs\apsdreamhome\database\backup_20260525.sql');
preg_match_all('/CREATE TABLE `(\w+)`/', $backup, $m);
$backupTables = array_unique($m[1]);
echo "Backup tables: " . count($backupTables) . "\n";

echo "\n=== STEP 3: Domain classification (by prefix) ===\n";
$domains = [];
foreach ($allTables as $t) {
    $prefix = preg_match('/^([a-z]+)_/', $t, $mm) ? $mm[1] . '_*' : $t;
    $domains[$prefix][] = $t;
}
ksort($domains);
foreach ($domains as $prefix => $tables) {
    $count = count($tables);
    $samples = array_slice($tables, 0, 3);
    echo sprintf("  %-25s %3d tables  e.g. %s%s\n",
        $prefix, $count,
        implode(', ', $samples),
        $count > 3 ? ', ...' : ''
    );
}

echo "\n=== STEP 4: Empty tables (potential dead weight) ===\n";
$empty = [];
foreach ($allTables as $t) {
    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    if ($cnt === 0) $empty[] = $t;
}
echo "Empty tables: " . count($empty) . " / " . count($allTables) . " (" . round(count($empty)*100/count($allTables),1) . "%)\n";
echo "First 20 empty: " . implode(', ', array_slice($empty, 0, 20)) . "\n";

echo "\n=== STEP 5: Heavy tables (data-rich, business-critical) ===\n";
$heavy = [];
foreach ($allTables as $t) {
    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    if ($cnt > 0) $heavy[$t] = $cnt;
}
arsort($heavy);
echo "Top 20 by row count:\n";
$i = 0;
foreach ($heavy as $t => $c) {
    if ($i++ >= 20) break;
    echo sprintf("  %6d  %s\n", $c, $t);
}

echo "\n=== STEP 6: Compare with backup - what we lost ===\n";
$lost = array_diff($backupTables, $allTables);
echo "Tables in backup but NOT in current DB: " . count($lost) . "\n";
if (count($lost) > 0 && count($lost) < 100) {
    foreach (array_slice($lost, 0, 50) as $t) echo "  - $t\n";
    if (count($lost) > 50) echo "  ... and " . (count($lost) - 50) . " more\n";
}

echo "\n=== STEP 7: Tables in current DB NOT in backup (added/modified since) ===\n";
$added = array_diff($allTables, $backupTables);
echo "Tables added/modified after 2026-05-25: " . count($added) . "\n";
if (count($added) > 0) {
    foreach (array_slice($added, 0, 20) as $t) echo "  + $t\n";
    if (count($added) > 20) echo "  ... and " . (count($added) - 20) . " more\n";
}

echo "\n=== STEP 8: Real estate industry standard modules ===\n";
// Standard modules in any real estate ERP
$industryModules = [
    'Properties (Plot/House/Flat/Commercial)' => ['property', 'plot', 'real_estate', 'listing'],
    'Customers & Leads' => ['customer', 'lead', 'contact', 'inquiry', 'prospect'],
    'Sales & Bookings' => ['booking', 'sale', 'reservation', 'transaction'],
    'Payments & EMI' => ['payment', 'emi', 'installment', 'invoice'],
    'CRM' => ['crm', 'interaction', 'followup', 'activity'],
    'Marketing' => ['campaign', 'marketing', 'promotion', 'referral'],
    'Document Management' => ['document', 'agreement', 'contract', 'kyc', 'registry'],
    'Finance & Accounting' => ['finance', 'accounting', 'expense', 'revenue', 'tax', 'gst'],
    'HR & Payroll' => ['employee', 'payroll', 'salary', 'attendance', 'leave', 'hr'],
    'Reports & Analytics' => ['report', 'analytics', 'kpi', 'metric', 'dashboard'],
    'AI/ML' => ['ai_', 'ml_', 'model', 'prediction'],
    'Communications' => ['sms', 'email', 'whatsapp', 'notification', 'chat'],
    'Projects/Colonies' => ['project', 'colony', 'site', 'phase'],
    'Commissions & MLM' => ['commission', 'mlm', 'referral', 'payout'],
    'Compliance/Legal' => ['legal', 'compliance', 'rera', 'rera_'],
    'Inventory & Materials' => ['inventory', 'material', 'stock', 'item'],
    'Workflow & Automation' => ['workflow', 'automation', 'task', 'job', 'queue'],
    'Audit & Logs' => ['audit', 'log', 'history', 'track'],
    'System & Config' => ['setting', 'config', 'permission', 'role', 'user', 'session'],
];
$covered = [];
$missing = [];
foreach ($industryModules as $module => $keywords) {
    $hits = [];
    foreach ($allTables as $t) {
        foreach ($keywords as $kw) {
            if (stripos($t, $kw) !== false) { $hits[] = $t; break; }
        }
    }
    if (count($hits) > 0) {
        $covered[$module] = count($hits);
    } else {
        $missing[] = $module;
    }
}
echo "Industry modules COVERED:\n";
foreach ($covered as $mod => $cnt) echo sprintf("  [OK]  %-40s %d tables\n", $mod, $cnt);
echo "\nIndustry modules MISSING:\n";
foreach ($missing as $mod) echo "  [--]  $mod\n";

echo "\n=== STEP 9: Index/FK health ===\n";
$tablesNoPK = 0;
$tablesNoIndex = 0;
foreach ($allTables as $t) {
    $pk = $pdo->query("SHOW INDEXES FROM `$t` WHERE Key_name = 'PRIMARY'")->fetchAll();
    if (empty($pk)) $tablesNoPK++;
    $idx = $pdo->query("SHOW INDEXES FROM `$t`")->fetchAll();
    if (count($idx) <= 1) $tablesNoIndex++;
}
echo "Tables without PRIMARY KEY: $tablesNoPK\n";
echo "Tables with no secondary indexes: $tablesNoIndex\n";
$fks = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = 'apsdreamhome' AND CONSTRAINT_TYPE = 'FOREIGN KEY'")->fetchColumn();
echo "Total FK constraints: $fks\n";

echo "\n=== STEP 10: DB size ===\n";
$size = $pdo->query("SELECT SUM(DATA_LENGTH + INDEX_LENGTH) FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'apsdreamhome'")->fetchColumn();
echo "Total DB size: " . round($size/1024/1024, 2) . " MB\n";?>