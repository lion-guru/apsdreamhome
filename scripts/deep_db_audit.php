<?php
/**
 * Comprehensive Deep Database Analysis Script for APS Dream Home
 */

set_time_limit(300);
ini_set('memory_limit', '512M');

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'apsdreamhome';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "========================================================================\n";
echo "      APS DREAM HOME — DEEP DATABASE ARCHITECTURAL & PERFORMANCE AUDIT\n";
echo "========================================================================\n\n";

// 1. Overview
$totalTables = $pdo->query("SELECT count(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
$totalSizeMB = $pdo->query("
    SELECT ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2)
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA='apsdreamhome'
")->fetchColumn();
$dataSizeMB = $pdo->query("SELECT ROUND(SUM(DATA_LENGTH) / 1024 / 1024, 2) FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
$indexSizeMB = $pdo->query("SELECT ROUND(SUM(INDEX_LENGTH) / 1024 / 1024, 2) FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome'")->fetchColumn();

echo "1. OVERVIEW\n";
echo "   - Total Tables: $totalTables\n";
echo "   - Total Size:   $totalSizeMB MB (Data: $dataSizeMB MB, Index: $indexSizeMB MB)\n\n";

// 2. Storage Engines
echo "2. STORAGE ENGINES\n";
$engines = $pdo->query("SELECT ENGINE, count(*) as cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome' GROUP BY ENGINE")->fetchAll();
foreach ($engines as $e) {
    echo "   - {$e['ENGINE']}: {$e['cnt']} tables\n";
}
echo "\n";

// 3. Collations
echo "3. CHARACTER SET & COLLATION CONSISTENCY\n";
$collations = $pdo->query("SELECT TABLE_COLLATION, count(*) as cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome' GROUP BY TABLE_COLLATION")->fetchAll();
foreach ($collations as $c) {
    echo "   - {$c['TABLE_COLLATION']}: {$c['cnt']} tables\n";
}
echo "\n";

// 4. Tables Without PRIMARY KEY
echo "4. TABLES MISSING PRIMARY KEYS\n";
$noPk = $pdo->query("
    SELECT t.TABLE_NAME 
    FROM information_schema.TABLES t 
    LEFT JOIN information_schema.KEY_COLUMN_USAGE k 
        ON t.TABLE_SCHEMA = k.TABLE_SCHEMA AND t.TABLE_NAME = k.TABLE_NAME AND k.CONSTRAINT_NAME = 'PRIMARY'
    WHERE t.TABLE_SCHEMA = 'apsdreamhome' AND t.TABLE_TYPE = 'BASE TABLE' AND k.COLUMN_NAME IS NULL
")->fetchAll(PDO::FETCH_COLUMN);
echo "   - Found: " . count($noPk) . " tables without PRIMARY KEY\n";
if (!empty($noPk)) {
    foreach (array_slice($noPk, 0, 15) as $tbl) {
        echo "     * $tbl\n";
    }
    if (count($noPk) > 15) echo "     * ... and " . (count($noPk) - 15) . " more\n";
}
echo "\n";

// 5. Suspect / Backup / Temporary / Junk Tables
echo "5. SUSPECT DUPLICATE / BACKUP / JUNK TABLES\n";
$suspect = $pdo->query("
    SELECT TABLE_NAME, TABLE_ROWS, ROUND((DATA_LENGTH + INDEX_LENGTH)/1024, 1) as size_kb
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = 'apsdreamhome' 
    AND (
        TABLE_NAME LIKE '%copy%' OR TABLE_NAME LIKE '%backup%' OR TABLE_NAME LIKE '%temp%' 
        OR TABLE_NAME LIKE '%tmp%' OR TABLE_NAME LIKE '%test%' OR TABLE_NAME LIKE '%_old%' 
        OR TABLE_NAME LIKE '%_new%' OR TABLE_NAME LIKE '%_bak%' OR TABLE_NAME LIKE '%duplicate%'
        OR TABLE_NAME LIKE '%_v2%' OR TABLE_NAME LIKE '%_v1%' OR TABLE_NAME LIKE '%_deprecated%'
    )
")->fetchAll();
echo "   - Found: " . count($suspect) . " suspect tables\n";
foreach ($suspect as $s) {
    echo "     * {$s['TABLE_NAME']} (Rows: {$s['TABLE_ROWS']}, Size: {$s['size_kb']} KB)\n";
}
echo "\n";

// 6. Singular vs Plural / Overlapping Business Entities
echo "6. BUSINESS LOGIC OVERLAPPING / DUPLICATE NAMES\n";
$allTableNames = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome'")->fetchAll(PDO::FETCH_COLUMN);
$nameMap = [];
foreach ($allTableNames as $tbl) {
    $normalized = rtrim($tbl, 's');
    $nameMap[$normalized][] = $tbl;
}
$pluralPairs = [];
foreach ($nameMap as $norm => $list) {
    if (count($list) > 1) {
        // e.g., lead & leads, booking & bookings
        $pluralPairs[] = $list;
    }
}
echo "   - Singular / Plural Entity Pairs: " . count($pluralPairs) . " pairs found\n";
foreach (array_slice($pluralPairs, 0, 20) as $pair) {
    echo "     * " . implode(' <---> ', $pair) . "\n";
}
echo "\n";

// 7. Missing Indexes on Foreign Keys (columns ending in _id)
echo "7. FOREIGN KEY COLUMNS MISSING INDEXES\n";
$fkMissingIdx = $pdo->query("
    SELECT c.TABLE_NAME, c.COLUMN_NAME
    FROM information_schema.COLUMNS c
    LEFT JOIN information_schema.STATISTICS s
        ON c.TABLE_SCHEMA = s.TABLE_SCHEMA 
        AND c.TABLE_NAME = s.TABLE_NAME 
        AND c.COLUMN_NAME = s.COLUMN_NAME
    WHERE c.TABLE_SCHEMA = 'apsdreamhome'
        AND c.COLUMN_NAME IN ('user_id', 'customer_id', 'plot_id', 'colony_id', 'booking_id', 'tenant_id')
        AND s.COLUMN_NAME IS NULL
")->fetchAll();
echo "   - Crucial Foreign Key columns without index: " . count($fkMissingIdx) . "\n";
$tblGroup = [];
foreach ($fkMissingIdx as $row) {
    $tblGroup[$row['TABLE_NAME']][] = $row['COLUMN_NAME'];
}
$cnt = 0;
foreach ($tblGroup as $tbl => $cols) {
    if ($cnt++ >= 15) break;
    echo "     * $tbl (Missing index on: " . implode(', ', $cols) . ")\n";
}
if (count($tblGroup) > 15) echo "     * ... and " . (count($tblGroup) - 15) . " more tables\n";
echo "\n";

// 8. Multi-Tenancy Scoping (tenant_id check)
echo "8. MULTI-TENANCY AUDIT (tenant_id coverage)\n";
$tenantTables = $pdo->query("
    SELECT count(DISTINCT TABLE_NAME) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA='apsdreamhome' AND COLUMN_NAME='tenant_id'
")->fetchColumn();
echo "   - Tables with 'tenant_id' column: $tenantTables / $totalTables (" . round($tenantTables / $totalTables * 100, 1) . "%)\n\n";

// 9. Largest Tables by Row Count and Disk Size
echo "9. TOP 10 LARGEST TABLES (Disk Size)\n";
$topSize = $pdo->query("
    SELECT TABLE_NAME, TABLE_ROWS, 
           ROUND(DATA_LENGTH / 1024 / 1024, 2) as data_mb,
           ROUND(INDEX_LENGTH / 1024 / 1024, 2) as index_mb,
           ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as total_mb
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA='apsdreamhome'
    ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
    LIMIT 10
")->fetchAll();
foreach ($topSize as $t) {
    echo "   - {$t['TABLE_NAME']}: {$t['total_mb']} MB (Data: {$t['data_mb']} MB, Index: {$t['index_mb']} MB, ~Rows: {$t['TABLE_ROWS']})\n";
}
echo "\n";

// 10. Empty Tables (0 rows)
$emptyTables = $pdo->query("
    SELECT count(*) FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_ROWS = 0
")->fetchColumn();
echo "10. EMPTY TABLES AUDIT\n";
echo "    - Empty Tables (0 rows): $emptyTables / $totalTables (" . round($emptyTables / $totalTables * 100, 1) . "%)\n\n";

// 11. Foreign Key Constraints
$fkCount = $pdo->query("
    SELECT count(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA='apsdreamhome' AND CONSTRAINT_TYPE='FOREIGN KEY'
")->fetchColumn();
echo "11. FOREIGN KEY CONSTRAINTS\n";
echo "    - Explicit FK Constraints Defined in DB: $fkCount\n";
if ($fkCount < 50) {
    echo "    - WARNING: Extremely low number of explicit foreign keys ($fkCount for $totalTables tables).\n";
    echo "      Relationships rely almost entirely on application-level integrity.\n";
}
echo "\n";

// 12. Security Audit (Sensitive columns, e.g. aadhaar, pan, password, token)
echo "12. SENSITIVE DATA COLUMNS AUDIT\n";
$sensitive = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='apsdreamhome'
    AND (
        COLUMN_NAME LIKE '%aadhaar%' OR COLUMN_NAME LIKE '%aadhar%'
        OR COLUMN_NAME LIKE '%pan_no%' OR COLUMN_NAME LIKE '%pan_number%'
        OR COLUMN_NAME LIKE '%bank_account%' OR COLUMN_NAME LIKE '%account_number%'
        OR COLUMN_NAME LIKE '%password%'
    )
")->fetchAll();
echo "    - Found: " . count($sensitive) . " sensitive columns across tables\n";
$sensTables = [];
foreach ($sensitive as $s) {
    $sensTables[$s['TABLE_NAME']][] = $s['COLUMN_NAME'];
}
$cnt = 0;
foreach ($sensTables as $tbl => $cols) {
    if ($cnt++ >= 10) break;
    echo "      * $tbl (" . implode(', ', $cols) . ")\n";
}
if (count($sensTables) > 10) echo "      * ... and " . (count($sensTables) - 10) . " more tables\n";
echo "\n";

echo "========================================================================\n";
echo "                         AUDIT COMPLETE\n";
echo "========================================================================\n";
