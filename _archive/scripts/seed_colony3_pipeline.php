<?php
/**
 * Seed complete pipeline test data for Braj Radha Nagri (id=3).
 * Covers: Colony → Development Costs → Plots → Pricing → Layout
 *
 * Usage: php scripts/seed_colony3_pipeline.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$colonyId = 3;
$colonyName = 'Braj Radha Nagri';
$basePricePerSqft = 12000;
$cornerPremium = 1.10;
$parkPremium = 1.15;

echo "=== Colony Pipeline Seeder: {$colonyName} (ID: {$colonyId}) ===\n\n";

// ── Step 0: FK checks ──────────────────────────────────────────────────────
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
echo "[OK] FK checks disabled\n";

// ── Step 1: Verify colony exists ────────────────────────────────────────────
$stmt = $pdo->prepare('SELECT id, name, total_plots, available_plots FROM colonies WHERE id = ?');
$stmt->execute([$colonyId]);
$colony = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$colony) {
    echo "[FAIL] Colony ID {$colonyId} not found. Aborting.\n";
    exit(1);
}
echo "[OK] Colony: {$colony['name']} (existing plots: {$colony['total_plots']})\n";

// ── Step 2: Check existing plots — skip if already seeded ──────────────────
$stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM plots WHERE colony_id = ?');
$stmt->execute([$colonyId]);
$existingPlots = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

if ($existingPlots >= 40) {
    echo "[SKIP] Colony {$colonyId} already has {$existingPlots} plots. Skipping plot generation.\n";
    $plotsSeeded = false;
} else {
    echo "[INFO] Colony {$colonyId} has {$existingPlots} plots. Generating 40 plots...\n";
    $plotsSeeded = true;
}

// ── Step 3: Development costs ──────────────────────────────────────────────
echo "\n--- Inserting development costs ---\n";

$devCosts = [
    ['road',        'Road Construction',       'Bitumen internal roads with concrete curbs and footpaths', 3800000, 684000],
    ['drainage',    'Storm Water Drainage',    'Open and covered drainage with RCC channels',              1900000, 342000],
    ['electricity', 'Electrical Infrastructure','HT/LT lines, poles, meters, and street lighting',         2700000, 486000],
    ['compound_wall','Compound Wall & Gate',   'Brick compound wall with iron gate and guard room',        2400000, 432000],
];

$stmt = $pdo->prepare('
    INSERT INTO colony_development_costs
        (colony_id, cost_type, work_description, amount, gst_amount, paid_amount,
         balance_amount, status, payment_status, completion_date, created_at)
    VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, CURDATE(), NOW())
    ON DUPLICATE KEY UPDATE
        amount = VALUES(amount),
        gst_amount = VALUES(gst_amount),
        paid_amount = VALUES(paid_amount),
        status = VALUES(status),
        payment_status = VALUES(payment_status),
        updated_at = NOW()
');

$totalCost = 0;
$totalGst = 0;
foreach ($devCosts as $dc) {
    $total = $dc[3] + $dc[4];
    $stmt->execute([
        $colonyId, $dc[0], $dc[2], $dc[3], $dc[4], $dc[3],
        'completed', 'paid'
    ]);
    $totalCost += $dc[3];
    $totalGst += $dc[4];
    echo "  ✓ {$dc[1]}: ₹" . number_format($dc[3]) . " + GST ₹" . number_format($dc[4]) . "\n";
}
echo "[OK] 4 development costs inserted. Total: ₹" . number_format($totalCost) . " + GST ₹" . number_format($totalGst) . " = ₹" . number_format($totalCost + $totalGst) . "\n";

// ── Step 4: Generate plots ──────────────────────────────────────────────────
if ($plotsSeeded) {
    echo "\n--- Generating plots ---\n";

    // Block A: 20 plots, Block B: 20 plots
    // Mix of 30x40 (1200sqft), 30x50 (1500sqft), 40x50 (2000sqft)
    $plotSpecs = [
        ['A', 20, ['30x40', '30x40', '30x50', '30x50', '40x50', '40x50']],
        ['B', 20, ['30x40', '30x40', '30x50', '30x50', '40x50', '40x50']],
    ];

    // Delete any leftover available plots for this colony
    $pdo->prepare('DELETE FROM plots WHERE colony_id = ? AND status = ?')->execute([$colonyId, 'available']);
    echo "[INFO] Cleared existing available plots\n";

    $dimensions = [
        '30x40' => ['width' => 30, 'length' => 40, 'area' => 1200],
        '30x50' => ['width' => 30, 'length' => 50, 'area' => 1500],
        '40x50' => ['width' => 40, 'length' => 50, 'area' => 2000],
    ];

    $cornerPlots = ['A-001', 'A-020', 'B-001', 'B-020'];

    $stmt = $pdo->prepare('
        INSERT INTO plots
            (colony_id, plot_number, block, plot_code, plot_type, area_sqft,
             width_ft, length_ft, dimension_label, frontage_ft, depth_ft,
             base_price_per_sqft, price_per_sqft, total_price, status,
             corner_plot, park_facing, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ');

    $totalArea = 0;
    $minPrice = PHP_INT_MAX;
    $maxPrice = 0;
    $plotCount = 0;
    $plotMap = [];

    foreach ($plotSpecs as [$block, $count, $dimPool]) {
        for ($i = 1; $i <= $count; $i++) {
            $plotNumber = sprintf('%s-%03d', $block, $i);
            $plotCode = "BRJ-{$plotNumber}";

            $dimKey = $dimPool[($i - 1) % count($dimPool)];
            $dim = $dimensions[$dimKey];
            $area = $dim['area'];

            $isCorner = in_array($plotNumber, $cornerPlots) ? 1 : 0;
            $isParkFacing = ($i % 5 === 0) ? 1 : 0;

            $effectivePricePerSqft = $basePricePerSqft;
            if ($isCorner) {
                $effectivePricePerSqft = round($basePricePerSqft * $cornerPremium);
            }
            if ($isParkFacing) {
                $effectivePricePerSqft = round($effectivePricePerSqft * $parkPremium);
            }
            $totalPrice = $effectivePricePerSqft * $area;

            $stmt->execute([
                $colonyId, $plotNumber, $block, $plotCode, 'residential', $area,
                $dim['width'], $dim['length'], $dimKey, $dim['width'], $dim['length'],
                $basePricePerSqft, $effectivePricePerSqft, $totalPrice, 'available',
                $isCorner, $isParkFacing
            ]);

            $totalArea += $area;
            $minPrice = min($minPrice, $totalPrice);
            $maxPrice = max($maxPrice, $totalPrice);
            $plotCount++;

            $flags = [];
            if ($isCorner) $flags[] = 'CORNER';
            if ($isParkFacing) $flags[] = 'PARK';
            $flagStr = !empty($flags) ? ' [' . implode(',', $flags) . ']' : '';
            echo "  ✓ {$plotNumber}: {$dimKey} ({$area} sqft) ₹" . number_format($totalPrice) . "{$flagStr}\n";

            $plotMap[] = [
                'plot_number' => $plotNumber,
                'block'       => $block,
                'dimension'   => $dimKey,
                'area_sqft'   => $area,
                'price'       => $totalPrice,
                'corner'      => $isCorner,
                'park'        => $isParkFacing,
            ];
        }
    }
    echo "[OK] {$plotCount} plots generated\n";

    // ── Step 6: Save layout ─────────────────────────────────────────────────
    echo "\n--- Saving colony layout ---\n";

    $layoutJson = json_encode($plotMap, JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare('
        INSERT INTO colony_layouts
            (colony_id, layout_name, version, layout_type, road_area_pct,
             common_area_pct, is_current, total_plots, total_area_sqft,
             plot_map_json, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    $stmt->execute([
        $colonyId,
        'Braj Radha Master Layout v1',
        '1.0',
        'residential',
        15.00,
        8.00,
        1,
        $plotCount,
        $totalArea,
        $layoutJson,
        'approved'
    ]);
    echo "[OK] Layout saved: \"Braj Radha Master Layout v1\" (v1.0)\n";
    echo "     Total plots: {$plotCount}, Total area: " . number_format($totalArea) . " sqft\n";
    echo "     Road area: 15%, Common area: 8%\n";

    // ── Step 7: Update colonies table ───────────────────────────────────────
    echo "\n--- Updating colony summary ---\n";
    $startingPrice = $minPrice;
    $pdo->prepare('UPDATE colonies SET total_plots = ?, available_plots = ?, starting_price = ?, show_plots_publicly = 1 WHERE id = ?')
        ->execute([$plotCount, $plotCount, $startingPrice, $colonyId]);
    echo "[OK] Colony updated: total_plots={$plotCount}, available_plots={$plotCount}, starting_price=₹" . number_format($startingPrice) . "\n";
} else {
    echo "[SKIP] Layout and colony update skipped (plots already exist)\n";
    $minPrice = 0;
    $maxPrice = 0;
    $totalArea = 0;
    $plotCount = $existingPlots;
    $plotMap = [];
}

// ── Re-enable FK checks ────────────────────────────────────────────────────
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
echo "\n[OK] FK checks re-enabled\n";

// ── Step 8: Summary ────────────────────────────────────────────────────────
echo "\n========================================\n";
echo "  COLONY PIPELINE SUMMARY\n";
echo "========================================\n";
echo "  Colony:          {$colonyName} (ID: {$colonyId})\n";
echo "  Development:     4 projects, ₹" . number_format($totalCost) . " + GST ₹" . number_format($totalGst) . " = ₹" . number_format($totalCost + $totalGst) . "\n";
echo "  Plots:           {$plotCount} plots across blocks A(20), B(20)\n";
echo "  Base price:      ₹" . number_format($basePricePerSqft) . "/sqft\n";
echo "  Corner premium:  +10%  |  Park facing: +15%\n";
echo "  Pricing range:   ₹" . number_format($minPrice) . " — ₹" . number_format($maxPrice) . "\n";
echo "  Total area:      " . number_format($totalArea) . " sqft\n";
if ($plotCount > 0 && $totalArea > 0) {
    $colonyValue = 0;
    if (!empty($plotMap)) {
        foreach ($plotMap as $p) {
            $colonyValue += $p['price'];
        }
    } else {
        $colonyValue = $plotCount * $basePricePerSqft * ($totalArea / $plotCount);
    }
    echo "  Colony value:    ₹" . number_format($colonyValue) . "\n";
}
echo "========================================\n";
echo "Done.\n";
