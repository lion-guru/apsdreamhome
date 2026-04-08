<?php
/**
 * Find Duplicate Tables by Prefix/Category
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Group by first word/prefix
$groups = [];
foreach($tables as $t) {
    // Extract prefix (first word before underscore)
    if (preg_match('/^([a-z]+)_/i', $t, $m)) {
        $prefix = $m[1];
    } else {
        $prefix = 'other';
    }
    
    if (!isset($groups[$prefix])) $groups[$prefix] = [];
    $groups[$prefix][] = $t;
}

// Sort by count descending
uasort($groups, function($a, $b) { return count($b) - count($a); });

echo "=== DUPLICATE TABLE GROUPS ===\n\n";

$totalDup = 0;
$groupNum = 0;

foreach ($groups as $prefix => $items) {
    if (count($items) >= 3) {
        $groupNum++;
        echo "GROUP $groupNum: " . strtoupper($prefix) . " ($prefix_*) - " . count($items) . " tables\n";
        echo str_repeat("-", 70) . "\n";
        
        $hasData = 0;
        $empty = 0;
        
        foreach ($items as $t) {
            try {
                $cnt = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
                if ($cnt > 0) $hasData++;
                else $empty++;
            } catch(Exception $e) { 
                $cnt = "BROKEN"; 
            }
            $status = $cnt > 0 ? "✓" : "○";
            echo "  $status $t ($cnt rows)\n";
        }
        
        echo "  Summary: $hasData with data, $empty empty\n\n";
        $totalDup += count($items);
    }
}

echo "=== SUMMARY ===\n";
echo "Total duplicate groups (3+ tables): $groupNum\n";
echo "Total tables in duplicate groups: $totalDup\n";
echo "Target for cleanup: " . ($totalDup > 100 ? "HIGH" : "MEDIUM") . "\n";
