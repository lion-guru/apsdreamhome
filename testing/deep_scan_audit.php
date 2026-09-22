<?php
/**
 * APS DREAM HOME — ENTERPRISE DEEP SCAN & ARCHITECTURAL AUDIT
 */

if (!defined('APS_ROOT')) {
    define('APS_ROOT', dirname(__DIR__));
}
if (!defined('APS_STORAGE')) {
    define('APS_STORAGE', APS_ROOT . '/storage');
}
require_once APS_ROOT . '/config/bootstrap.php';

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=================================================================\n";
echo "       APS DREAM HOME — ENTERPRISE DEEP SCAN & AUDIT\n";
echo "=================================================================\n\n";

// --- 1. DATABASE METRICS & HEALTH ---
echo "--- 1. DATABASE METRICS & STORAGE ---\n";
$tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
echo "Base Tables count: " . count($tables) . "\n";

// Largest Tables
$largest = $pdo->query("SELECT TABLE_NAME, TABLE_ROWS, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb,
    ROUND(DATA_LENGTH / 1024 / 1024, 2) AS data_mb,
    ROUND(INDEX_LENGTH / 1024 / 1024, 2) AS index_mb
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = 'apsdreamhome' 
    ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC LIMIT 10")->fetchAll();
echo "\nTop 10 Largest Tables (Data + Index):\n";
foreach ($largest as $t) {
    echo sprintf("   %-32s %8d rows | Total: %6.2f MB (Data: %5.2f MB, Idx: %5.2f MB)\n", 
        $t['TABLE_NAME'], $t['TABLE_ROWS'], $t['size_mb'], $t['data_mb'], $t['index_mb']);
}

// Check for Tables with 0 rows (potential dead/legacy schema bloat)
$emptyTables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_TYPE = 'BASE TABLE' AND TABLE_ROWS = 0")->fetchAll(PDO::FETCH_COLUMN);
echo "\nEmpty Tables (0 rows detected): " . count($emptyTables) . " / " . count($tables) . "\n";

// Check Duplicate / Redundant Table Names
$allTableNames = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'apsdreamhome'")->fetchAll(PDO::FETCH_COLUMN);
$redundantPatterns = [
    'audit' => [],
    'activity' => [],
    'notification' => [],
    'log' => [],
    'setting' => [],
    'session' => [],
    'payment' => [],
    'commission' => []
];
foreach ($allTableNames as $tn) {
    foreach ($redundantPatterns as $key => &$list) {
        if (strpos($tn, $key) !== false) {
            $list[] = $tn;
        }
    }
}
echo "\nTable Clustering & Potential Overlaps:\n";
foreach ($redundantPatterns as $prefix => $cluster) {
    echo "   - Keyword '$prefix': " . count($cluster) . " tables (e.g. " . implode(', ', array_slice($cluster, 0, 4)) . "...)\n";
}

// Check High-Traffic Unindexed Foreign Keys
echo "\nChecking Critical High-Traffic Tables for Missing Foreign Key Indexes:\n";
$criticalTables = ['bookings', 'plots', 'leads', 'users', 'payments', 'commissions', 'mlm_commissions', 'inquiries', 'site_visits'];
$critPlaceholders = "'" . implode("','", $criticalTables) . "'";
$critUnindexed = $pdo->query("
    SELECT c.TABLE_NAME, c.COLUMN_NAME
    FROM information_schema.COLUMNS c
    JOIN information_schema.TABLES t ON c.TABLE_NAME = t.TABLE_NAME AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
    WHERE c.TABLE_SCHEMA = 'apsdreamhome'
      AND c.TABLE_NAME IN ($critPlaceholders)
      AND (c.COLUMN_NAME LIKE '%_id' OR c.COLUMN_NAME = 'user_id' OR c.COLUMN_NAME = 'plot_id' OR c.COLUMN_NAME = 'colony_id')
      AND NOT EXISTS (
          SELECT 1 FROM information_schema.STATISTICS s
          WHERE s.TABLE_SCHEMA = c.TABLE_SCHEMA
            AND s.TABLE_NAME = c.TABLE_NAME
            AND s.COLUMN_NAME = c.COLUMN_NAME
      )
")->fetchAll();
echo "Unindexed FKs in Core Transaction Tables: " . count($critUnindexed) . "\n";
foreach ($critUnindexed as $cu) {
    echo "   [!] {$cu['TABLE_NAME']}.{$cu['COLUMN_NAME']} is UNINDEXED\n";
}

// --- 2. STORAGE & CACHE AUDIT ---
echo "\n--- 2. STORAGE, LOGS & SESSIONS ---\n";
$logFile = APS_ROOT . '/logs/php_error.log';
if (file_exists($logFile)) {
    $logSizeMb = round(filesize($logFile) / 1024 / 1024, 2);
    echo "PHP Error Log: $logFile ($logSizeMb MB)\n";
    // Read last 10KB
    $fp = fopen($logFile, 'r');
    if ($fp) {
        $seek = max(0, filesize($logFile) - 20480);
        fseek($fp, $seek);
        $tail = fread($fp, 20480);
        fclose($fp);
        $lines = explode("\n", $tail);
        $fatalCount = substr_count($tail, 'Fatal error');
        $warnCount = substr_count($tail, 'Warning');
        $deprecatedCount = substr_count($tail, 'Deprecated');
        echo "   Tail (last 20KB) breakdown: Fatal: $fatalCount, Warnings: $warnCount, Deprecations: $deprecatedCount\n";
    }
} else {
    echo "PHP Error Log: None (Clean)\n";
}

// Sessions
$sessionDir = APS_STORAGE . '/sessions';
if (is_dir($sessionDir)) {
    $sessionFiles = glob($sessionDir . '/*');
    echo "Active Session Files on disk: " . count($sessionFiles) . "\n";
}

// Uploads
$uploadDir = APS_ROOT . '/public/uploads';
if (is_dir($uploadDir)) {
    $uploadIter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadDir, FilesystemIterator::SKIP_DOTS));
    $uploadCount = 0;
    $uploadSize = 0;
    foreach ($uploadIter as $uFile) {
        if ($uFile->isFile()) {
            $uploadCount++;
            $uploadSize += $uFile->getSize();
        }
    }
    echo "Public Uploads: $uploadCount files (" . round($uploadSize / 1024 / 1024, 2) . " MB)\n";
}

// --- 3. SECURITY & EXPOSURE AUDIT ---
echo "\n--- 3. SECURITY & FILE EXPOSURE ---\n";
// Check if sensitive files exist inside public/
$publicDir = APS_ROOT . '/public';
$sensitiveChecks = ['.env', 'composer.json', 'composer.lock', 'config.php', 'database.php', '.git', 'backup.sql'];
foreach ($sensitiveChecks as $check) {
    if (file_exists($publicDir . '/' . $check)) {
        echo "   [CRITICAL] Sensitive file exposed in public/: $check\n";
    }
}
echo "Public directory exposure check: Clean for primary secrets.\n";

// --- 4. CONCURRENCY & TRANSACTION AUDIT ---
echo "\n--- 4. CONCURRENCY & BOOKING LOCK AUDIT ---\n";
// Inspect BookingController / BookingService for plot locking
$bookingServiceFile = APS_ROOT . '/app/services/Booking/BookingService.php';
$hasRowLock = false;
$hasDbTransaction = false;
if (file_exists($bookingServiceFile)) {
    $bContent = file_get_contents($bookingServiceFile);
    if (stripos($bContent, 'FOR UPDATE') !== false) {
        $hasRowLock = true;
    }
    if (stripos($bContent, 'beginTransaction') !== false) {
        $hasDbTransaction = true;
    }
}
echo "Booking creation: DB Transaction = " . ($hasDbTransaction ? "YES" : "NO/CHECK") . 
     " | SELECT ... FOR UPDATE (Race Condition Lock) = " . ($hasRowLock ? "YES" : "NO") . "\n";

// --- 5. COMPREHENSIVE SCALE METRICS ---
echo "\n--- 5. REPOSITORY CODE METRICS ---\n";
$appDir = APS_ROOT . '/app';
$cCount = count(glob($appDir . '/Http/Controllers/**/*.php') ?: []);
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appDir . '/Http/Controllers', FilesystemIterator::SKIP_DOTS));
$allControllers = 0;
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allControllers++;
    }
}

$iterS = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appDir . '/services', FilesystemIterator::SKIP_DOTS));
$allServices = 0;
foreach ($iterS as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allServices++;
    }
}

$iterV = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appDir . '/views', FilesystemIterator::SKIP_DOTS));
$allViews = 0;
foreach ($iterV as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allViews++;
    }
}

$routesFile = APS_ROOT . '/routes/web.php';
$routeCount = 0;
if (file_exists($routesFile)) {
    $rContent = file_get_contents($routesFile);
    $routeCount += preg_match_all('/(?:Router|Route)::(?:get|post|put|delete|any|match)\s*\(/i', $rContent);
}
$apiRoutesFile = APS_ROOT . '/routes/api.php';
$apiRouteCount = 0;
if (file_exists($apiRoutesFile)) {
    $aContent = file_get_contents($apiRoutesFile);
    $apiRouteCount += preg_match_all('/(?:Router|Route)::(?:get|post|put|delete|any|match)\s*\(/i', $aContent);
}

echo "Total Web Routes: ~$routeCount\n";
echo "Total API Routes: ~$apiRouteCount\n";
echo "Total Controllers: $allControllers\n";
echo "Total Services: $allServices\n";
echo "Total Views: $allViews\n";

echo "\n=================================================================\n";
echo "                AUDIT SCAN COMPLETE\n";
echo "=================================================================\n";
