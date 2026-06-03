<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pricePerSqft = 1500;
$plots = [];

// Block A: 40 plots of 20x50 (1000 sqft)
for ($i = 1; $i <= 40; $i++) {
    $plots[] = ['A', "A-$i", "20'x50'", 1000, 1000 * $pricePerSqft, 1000 * $pricePerSqft * 0.05];
}
// Block B: 60 plots of 20x50 (1000 sqft)
for ($i = 1; $i <= 60; $i++) {
    $plots[] = ['B', "B-$i", "20'x50'", 1000, 1000 * $pricePerSqft, 1000 * $pricePerSqft * 0.05];
}
// Block C: 30 plots of 30x50 (1500 sqft)
for ($i = 1; $i <= 30; $i++) {
    $plots[] = ['C', "C-$i", "30'x50'", 1500, 1500 * $pricePerSqft, 1500 * $pricePerSqft * 0.08];
}

echo "Total Raghunath Nagri plots: " . count($plots) . PHP_EOL;

// 1. Seed inventory_plots
$ins = $db->prepare("INSERT INTO inventory_plots (block_name, plot_no, dimension, size_sqft, basic_price, plc_charges, status, colony_id) VALUES (?, ?, ?, ?, ?, ?, 'Available', 4)");
$cnt = 0;
$db->beginTransaction();
foreach ($plots as $p) { $ins->execute($p); $cnt++; }
$db->commit();
echo "Seeded $cnt plots into inventory_plots for Raghunath Nagri.\n";

// 2. Seed plots table
$pCols = $db->query("SHOW COLUMNS FROM plots")->fetchAll(PDO::FETCH_COLUMN, 0);
$hasFrontage = in_array('frontage_ft', $pCols);
$hasDepth = in_array('depth_ft', $pCols);
$hasAreaSqm = in_array('area_sqm', $pCols);
$hasCorner = in_array('corner_plot', $pCols);

$cols = ['colony_id','plot_number','block','area_sqft','total_price','status'];
$holders = ['?','?','?','?','?','?'];
if ($hasFrontage) { $cols[] = 'frontage_ft'; $holders[] = '?'; }
if ($hasDepth) { $cols[] = 'depth_ft'; $holders[] = '?'; }
if ($hasAreaSqm) { $cols[] = 'area_sqm'; $holders[] = '?'; }
if ($hasCorner) { $cols[] = 'corner_plot'; $holders[] = '?'; }

$sql = "INSERT INTO plots (" . implode(',', $cols) . ", created_at) VALUES (" . implode(',', $holders) . ", NOW())";
$pIns = $db->prepare($sql);
$cnt2 = 0;
foreach ($plots as $p) {
    $params = [4, $p[1], $p[0], $p[3], $p[4], 'available'];
    if ($hasFrontage) {
        preg_match("/(\d+)'x(\d+)'/", $p[2], $m);
        $params[] = (float)($m[1] ?? 20);
    }
    if ($hasDepth) $params[] = (float)($m[2] ?? 50);
    if ($hasAreaSqm) $params[] = round($p[3] * 0.0929, 2);
    if ($hasCorner) $params[] = 0;
    $pIns->execute($params);
    $cnt2++;
}
echo "Seeded $cnt2 plots into plots table.\n";

echo "Done! Raghunath Nagri seeding complete.\n";
