<?php
/**
 * Ultra-Fast Table Usage Analyzer
 * Reads each file only once and checks for all table names.
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

$projectRoot = dirname(__DIR__);
$searchDirs = ['app', 'routes', 'public', 'scripts'];

$usageCounts = array_fill_keys($tables, 0);
$tableFiles = array_fill_keys($tables, []);

echo "🚀 Starting fast scan of " . count($tables) . " tables...\n";

foreach ($searchDirs as $dir) {
    $fullDir = $projectRoot . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($fullDir)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        if (strpos($file->getPathname(), 'vendor') !== false) continue;
        if (strpos($file->getPathname(), 'node_modules') !== false) continue;
        if (strpos($file->getPathname(), 'table_usage_analyzer.php') !== false) continue;

        $content = file_get_contents($file->getPathname());
        if ($content === false) continue;

        foreach ($tables as $table) {
            if (stripos($content, $table) !== false) {
                $usageCounts[$table]++;
                if (count($tableFiles[$table]) < 3) {
                    $tableFiles[$table][] = str_replace($projectRoot, '', $file->getPathname());
                }
            }
        }
    }
}

// Get row counts
$tableData = [];
foreach ($tables as $table) {
    $rows = -1;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$table` LIMIT 1");
        if ($stmt) $rows = $stmt->fetchColumn();
    } catch (Exception $e) { $rows = -1; }

    $tableData[] = [
        'name' => $table,
        'rows' => $rows,
        'usage' => $usageCounts[$table],
        'files' => $tableFiles[$table]
    ];
}

// Grouping
$active = [];
$emptyNoUsage = [];
$dataNoUsage = [];
$broken = [];

foreach ($tableData as $r) {
    if ($r['rows'] == -1) $broken[] = $r;
    elseif ($r['usage'] > 0) $active[] = $r;
    elseif ($r['rows'] > 0) $dataNoUsage[] = $r;
    else $emptyNoUsage[] = $r;
}

echo "=== DATABASE OPTIMIZATION REPORT ===\n\n";

echo "🟢 ACTIVE TABLES (" . count($active) . ")\n";
echo "These are likely your core tables.\n";
usort($active, function($a, $b) { return $b['usage'] - $a['usage']; });
foreach (array_slice($active, 0, 10) as $r) {
    printf("%-30s | Rows: %-6d | Usage: %-3d | Sample: %s\n", $r['name'], $r['rows'], $r['usage'], implode(', ', $r['files']));
}

echo "\n🟠 INACTIVE TABLES WITH DATA (" . count($dataNoUsage) . ")\n";
echo "These have records but aren't seen in code. Possibly legacy or backend-only.\n";
usort($dataNoUsage, function($a, $b) { return $b['rows'] - $a['rows']; });
foreach (array_slice($dataNoUsage, 0, 10) as $r) {
    printf("%-30s | Rows: %-6d | Usage: 0\n", $r['name'], $r['rows']);
}

echo "\n📁 EMPTY & INACTIVE TABLES (" . count($emptyNoUsage) . ")\n";
echo "Likely 'Future Tables' or unused. SAFE to consolidate or remove if not needed.\n";
foreach (array_slice($emptyNoUsage, 0, 10) as $r) {
    echo "- {$r['name']}\n";
}

echo "\n❌ BROKEN TABLES: " . count($broken) . "\n";
echo "\nTOTAL TABLES: " . count($tables) . "\n";
echo "--------------------------------------------------\n";
echo "Recommended Action: Merge 'Empty & Inactive' tables into core tables where logic overlaps.\n";
