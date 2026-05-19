<?php
/**
 * Assign dimensions (width_ft x length_ft) to all existing plots
 * Standard Real Estate dimensions based on area_sqft
 * Run: php scripts/assign_plot_dimensions.php
 */

$basePath = dirname(__DIR__);
require_once $basePath . '/config/database.php';

$db = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=== Assigning Plot Dimensions ===\n\n";

// Dimension map: area_sqft ranges to standard dimensions
$dimensionMap = [
    [800, 899, 20, 40, '20x40'],
    [900, 999, 20, 45, '20x45'],
    [1000, 1099, 20, 50, '20x50'],
    [1100, 1199, 22, 50, '22x50'],
    [1200, 1299, 25, 48, '25x48'],
    [1300, 1399, 25, 52, '25x52'],
    [1400, 1499, 25, 56, '25x56'],
    [1500, 1599, 25, 60, '25x60'],
    [1600, 1699, 25, 64, '25x64'],
    [1700, 1799, 25, 68, '25x68'],
    [1800, 1899, 30, 60, '30x60'],
    [1900, 1999, 30, 63, '30x63'],
    [2000, 2199, 30, 70, '30x70'],
    [2200, 2399, 40, 55, '40x55'],
    [2400, 2599, 40, 60, '40x60'],
    [2600, 2999, 40, 65, '40x65'],
    [3000, 3499, 50, 60, '50x60'],
    [3500, 3999, 50, 70, '50x70'],
    [4000, 5000, 50, 80, '50x80'],
];

$updated = 0;
$stmt = $db->query("SELECT id, area_sqft, colony_id, block, plot_number FROM plots WHERE (width_ft IS NULL OR width_ft = 0)");
$plots = $stmt->fetchAll();
echo "Found " . count($plots) . " plots without dimensions\n";

foreach ($plots as $plot) {
    $area = floatval($plot['area_sqft'] ?? 0);
    $assigned = false;
    
    // Check block-specific dimensions
    if ($plot['colony_id'] == 4 && $plot['block'] == 'C') { // Raghunath Nagri Block C = 30x50
        $w = 30; $l = 50; $label = '30x50'; $assigned = true;
    } elseif ($plot['colony_id'] == 4 && in_array($plot['block'], ['A', 'B'])) { // Raghunath Nagri A & B = 20x50
        $w = 20; $l = 50; $label = '20x50'; $assigned = true;
    } elseif ($plot['colony_id'] == 2 && $plot['block'] == 'A') { // Suryodaya Block A = 20x50
        $w = 20; $l = 50; $label = '20x50'; $assigned = true;
    }
    
    if (!$assigned) {
        foreach ($dimensionMap as $map) {
            if ($area >= $map[0] && $area <= $map[1]) {
                $w = $map[2]; $l = $map[3]; $label = $map[4];
                $assigned = true; break;
            }
        }
    }
    
    if (!$assigned) {
        $sqrt = sqrt($area);
        $w = round($sqrt / 5) * 5;
        $l = round($area / $w);
        if ($l < $w) { [$w, $l] = [$l, $w]; }
        $label = "{$w}x{$l}";
    }
    
    $upd = $db->prepare("UPDATE plots SET width_ft = ?, length_ft = ?, dimension_label = ?, base_price_per_sqft = price_per_sqft WHERE id = ?");
    $upd->execute([$w, $l, $label, $plot['id']]);
    $updated++;
}
echo "Updated $updated plots with dimensions\n";

// Now assign dimensions to inventory_plots
$updated2 = 0;
$stmt2 = $db->query("SELECT id, size_sqft, dimension FROM inventory_plots WHERE dimension IS NULL OR dimension = ''");
$invPlots = $stmt2->fetchAll();
echo "Found " . count($invPlots) . " inventory plots without dimension\n";

foreach ($invPlots as $p) {
    $area = floatval($p['size_sqft'] ?? 0);
    $assigned = false;
    foreach ($dimensionMap as $map) {
        if ($area >= $map[0] && $area <= $map[1]) {
            $w = $map[2]; $l = $map[3]; $label = $map[4];
            $assigned = true; break;
        }
    }
    if (!$assigned) {
        $sqrt = sqrt($area);
        $w = round($sqrt / 5) * 5;
        $l = round($area / $w);
        if ($l < $w) { [$w, $l] = [$l, $w]; }
        $label = "{$w}x{$l}";
    }
    $upd2 = $db->prepare("UPDATE inventory_plots SET dimension = ?, width_ft = ?, length_ft = ? WHERE id = ?");
    $upd2->execute([$label, $w, $l, $p['id']]);
    $updated2++;
}
echo "Updated $updated2 inventory plots with dimensions\n";

// Set base prices and fix null prices
$db->exec("UPDATE plots SET base_price_per_sqft = price_per_sqft WHERE base_price_per_sqft IS NULL");
$db->exec("UPDATE plots SET price_per_sqft = 1500.00 WHERE price_per_sqft IS NULL OR price_per_sqft = 0");
$db->exec("UPDATE plots SET total_price = area_sqft * price_per_sqft WHERE total_price IS NULL OR total_price = 0");

echo "\nDone! All plots have dimensions and base prices.\n";
