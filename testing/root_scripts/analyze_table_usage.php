<?php
/**
 * APS Dream Home - Deep Table Analysis
 * Check which tables are actually used vs unused
 */

$host = '127.0.0.1';
$port = 3307;
$user = 'root';
$pass = '';
$dbname = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "===========================================\n";
echo "APS DREAM HOME - TABLE USAGE ANALYSIS\n";
echo "===========================================\n\n";

// Get all tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Scan code for table references
$codeFiles = glob(__DIR__ . '/app/**/*.php') ?: [];
$codeFiles = array_merge($codeFiles, glob(__DIR__ . '/*.php') ?: []);

$tableUsage = [];
foreach ($tables as $table) {
    $tableUsage[$table] = [
        'in_code' => false,
        'files' => [],
        'usage_count' => 0
    ];
}

foreach ($codeFiles as $file) {
    $content = file_get_contents($file);
    $contentLower = strtolower($content);
    
    foreach ($tables as $table) {
        $pattern = '/\b' . preg_quote($table, '/') . '\b/i';
        if (preg_match_all($pattern, $content, $matches)) {
            $tableUsage[$table]['in_code'] = true;
            $tableUsage[$table]['files'][] = basename($file);
            $tableUsage[$table]['usage_count'] += count($matches[0]);
        }
    }
}

// Get row counts
$rowCounts = $pdo->query("
    SELECT TABLE_NAME, TABLE_ROWS 
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = '$dbname'
")->fetchAll(PDO::FETCH_KEY_PAIR);

echo "ANALYSIS RESULTS:\n\n";

// Categorize tables
$activeWithData = [];
$activeEmpty = [];
$unusedWithData = [];
$unusedEmpty = [];

foreach ($tables as $table) {
    $rows = $rowCounts[$table] ?? 0;
    $inCode = $tableUsage[$table]['in_code'];
    $usageCount = $tableUsage[$table]['usage_count'];
    
    if ($inCode && $rows > 0) {
        $activeWithData[] = [
            'table' => $table,
            'rows' => $rows,
            'usage' => $usageCount
        ];
    } elseif ($inCode && $rows == 0) {
        $activeEmpty[] = [
            'table' => $table,
            'usage' => $usageCount
        ];
    } elseif (!$inCode && $rows > 0) {
        $unusedWithData[] = [
            'table' => $table,
            'rows' => $rows
        ];
    } else {
        $unusedEmpty[] = $table;
    }
}

// Sort by usage
usort($activeWithData, function($a, $b) { return $b['usage'] - $a['usage']; });

echo "1. ACTIVE TABLES (Used in Code + Have Data) - " . count($activeWithData) . "\n";
echo "   These tables are essential and working:\n\n";

foreach (array_slice($activeWithData, 0, 30) as $item) {
    echo "   ✓ {$item['table']} ({$item['rows']} rows, used {$item['usage']} times)\n";
}
if (count($activeWithData) > 30) {
    echo "   ... and " . (count($activeWithData) - 30) . " more\n";
}

echo "\n2. ACTIVE BUT EMPTY (Used in Code + No Data) - " . count($activeEmpty) . "\n";
echo "   These tables are referenced but have no data:\n\n";

foreach ($activeEmpty as $item) {
    echo "   ○ {$item['table']} (used {$item['usage']} times)\n";
}

echo "\n3. UNUSED BUT HAS DATA - " . count($unusedWithData) . "\n";
echo "   These tables have data but are NOT referenced in code:\n\n";

foreach ($unusedWithData as $item) {
    echo "   ⚠ {$item['table']} ({$item['rows']} rows) - NO CODE REFERENCE\n";
}

echo "\n4. COMPLETELY UNUSED (No Code + No Data) - " . count($unusedEmpty) . "\n";
echo "   Safe to delete after backup:\n\n";

$unusedEmptyChunks = array_chunk($unusedEmpty, 10);
foreach ($unusedEmptyChunks as $chunk) {
    echo "   " . implode(', ', $chunk) . "\n";
}

echo "\n===========================================\n";
echo "SUMMARY\n";
echo "===========================================\n";
echo "Total Tables: " . count($tables) . "\n";
echo "Active + Data: " . count($activeWithData) . " (KEEP)\n";
echo "Active + Empty: " . count($activeEmpty) . " (MONITOR)\n";
echo "Unused + Data: " . count($unusedWithData) . " (INVESTIGATE)\n";
echo "Completely Unused: " . count($unusedEmpty) . " (POTENTIAL DELETE)\n";

$potentialSavings = count($unusedEmpty);
echo "\nIf you delete unused tables: ~$potentialSavings tables removed\n";
