<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Check overlap between plots and inventory_plots by colony_id + plot_no
echo "=== OVERLAP CHECK ===\n\n";

$overlap = $pdo->query("
    SELECT i.colony_id, i.plot_no, i.block_name, i.size_sqft, i.basic_price, i.status as inv_status,
           p.id as plot_id, p.plot_number, p.status as plot_status, p.total_price
    FROM inventory_plots i
    LEFT JOIN plots p ON i.colony_id = p.colony_id AND i.plot_no = p.plot_number
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

$matched = 0; $unmatched = 0;
foreach ($overlap as $r) {
    if ($r['plot_id']) {
        $matched++;
        echo "MATCH: inv[{$r['plot_no']},{$r['colony_id']}] -> plot[{$r['plot_number']},{$r['plot_id']}]\n";
    } else {
        $unmatched++;
        echo "NO MATCH: inv[{$r['plot_no']},{$r['colony_id']},{$r['inv_status']}]\n";
    }
}

// Count totals
$totalInv = $pdo->query("SELECT COUNT(*) FROM inventory_plots")->fetchColumn();
$totalPlots = $pdo->query("SELECT COUNT(*) FROM plots")->fetchColumn();

// How many inventory_plots have matching plots?
$matchedTotal = $pdo->query("
    SELECT COUNT(*) FROM inventory_plots i
    INNER JOIN plots p ON i.colony_id = p.colony_id AND i.plot_no = p.plot_number
")->fetchColumn();

echo "\ninventory_plots: $totalInv\nplots: $totalPlots\nMatched: $matchedTotal\nUnmatched inventory: " . ($totalInv - $matchedTotal) . "\n";

// Check plot_master overlap
$matchedMaster = $pdo->query("
    SELECT COUNT(*) FROM plot_master pm
    INNER JOIN plots p ON pm.site_id = p.site_id AND pm.plot_no = p.plot_number
")->fetchColumn();
$totalMaster = $pdo->query("SELECT COUNT(*) FROM plot_master")->fetchColumn();
echo "\nplot_master: $totalMaster\nMatched with plots: $matchedMaster\n";?>