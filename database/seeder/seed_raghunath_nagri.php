<?php
/**
 * Raghunath Nagri — Complete Data Seeder
 * ─────────────────────────────────────────
 * Project : Raghunath Nagri
 * Developer : APS Dream Homes Pvt. Ltd.
 * Location  : Motiram, Rampur Tappa Rajdhani, Gorakhpur (Uttar Pradesh)
 * Slug      : raghunath-nagri-motiram
 *
 * Seed pipeline:
 *   1. Colony & Layout (idempotent on slug)
 *   2. Land parcels (14 gata numbers, 708.37 decimal)
 *   3. Plot inventory (120 plots across 5 blocks)
 *   4. Price history audit trail (every plot baseline logged)
 *
 * Usage:  php database/seeder/seed_raghunath_nagri.php
 * Safety:  Full transaction — rolls back on any failure.
 */

$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';
$pdo    = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$now    = date('Y-m-d H:i:s');
$userId = 1; // admin / system user for changed_by

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  Raghunath Nagri — Data Seeder                         ║\n";
echo "║  APS Dream Homes Pvt. Ltd.                              ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

/* ─────────────────────────────────────────────────────────────
   1. IDEMPOTENCY CHECK
   ───────────────────────────────────────────────────────────── */
$stmt = $pdo->prepare('SELECT id, name FROM colonies WHERE slug = ?');
$stmt->execute(['raghunath-nagri-motiram']);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "⚠  Raghunath Nagri (Motiram) data is already seeded!\n";
    echo "   Colony ID: {$existing['id']}  Name: {$existing['name']}\n";
    echo "   Nothing to do. Exiting.\n";
    exit(0);
}

echo "[1/4] Idempotency check … PASS (slug not found)\n\n";

/* ─────────────────────────────────────────────────────────────
   BEGIN TRANSACTION
   ───────────────────────────────────────────────────────────── */
$pdo->beginTransaction();
echo "[TX]  Transaction started\n";

try {

    /* ─────────────────────────────────────────────────────────
       2. COLONY & LAYOUT
       ───────────────────────────────────────────────────────── */

    // 2a. Colony
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    $stmt = $pdo->prepare('
        INSERT INTO colonies
            (district_id, name, slug, description, total_plots, available_plots,
             starting_price, is_active, is_featured, show_plots_publicly,
             contact_phone, contact_email)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $colonyDescription = "Raghunath Nagri is a premium residential colony developed by APS Dream Homes Pvt. Ltd. "
        . "located at Motiram, Rampur Tappa Rajdhani, Gorakhpur, Uttar Pradesh. "
        . "Spanning over 708.37 decimal (14 gata numbers), the project offers 120 residential "
        . "and commercial plots across 5 blocks with flexible payment plans and competitive pricing.";

    $totalPlots       = 120;
    $startingPrice    = 999.00;  // lowest per-sqft (Block C)
    $contactPhone     = '+91 92771 21112';
    $contactEmail     = 'info@apsdreamhome.com';

    $stmt->execute([
        5,                            // district_id  → Gorakhpur
        'Raghunath Nagri',
        'raghunath-nagri-motiram',
        $colonyDescription,
        $totalPlots,
        $totalPlots,                  // all available at launch
        $startingPrice,
        1,                            // is_active
        1,                            // is_featured
        1,                            // show_plots_publicly
        $contactPhone,
        $contactEmail,
    ]);

    $colonyId = (int) $pdo->lastInsertId();
    echo "[2a] Colony created — ID {$colonyId}  Name: Raghunath Nagri\n";

    // 2b. Layout
    $totalAreaSqft = ($totalPlots * 1000); // approximate (avg 1000 sqft per plot)

    $stmt = $pdo->prepare('
        INSERT INTO colony_layouts
            (colony_id, layout_name, version, layout_type, road_area_pct,
             common_area_pct, is_current, total_plots, total_area_sqft,
             status, approved_by, approval_date, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $colonyId,
        'Raghunath Nagri Master Phase 1',
        '1.0',
        'mixed',
        15.00,        // road_area_pct
        8.00,         // common_area_pct
        1,            // is_current
        $totalPlots,
        $totalAreaSqft,
        'approved',
        'APS Dream Homes',
        $now,
        $now,
    ]);

    $layoutId = (int) $pdo->lastInsertId();
    echo "[2b] Layout created — ID {$layoutId}  Name: Raghunath Nagri Master Phase 1\n\n";

    /* ─────────────────────────────────────────────────────────
       3. LAND BANK SEEDING (14 Gata Numbers → 708.37 Decimal)
       ───────────────────────────────────────────────────────── */

    $gataData = [
        //  gata_no  |  hectares  |  decimal  |  sqft (1 Ha = 107639.1 sqft)
        [1206, 0.1950,  48.14, 20987.62],
        [1207, 0.1490,  36.79, 16038.21],
        [1208, 0.1220,  30.12, 12932.00],
        [1375, 0.1500,  37.03, 16145.86],
        [1372, 0.0490,  12.09,  5274.31],
        [1373, 0.3750,  92.59, 40364.66],
        [1374, 0.1250,  30.86, 13454.89],
        [1357, 0.8860, 218.76, 95348.09],
        [1293, 0.2210,  54.56, 23676.24],
        [1266, 0.2330,  57.53, 24967.89],
        [1267, 0.0530,  13.08,  5704.87],
        [1268, 0.0480,  11.85,  5166.68],
        [1211, 0.0810,  20.00,  8718.77],
        [1260, 0.1820,  44.94, 19490.32],
    ];

    echo "[3/4] Inserting 14 land parcels (Gata numbers) …\n";

    $totalDecimal = 0;
    $totalHectares = 0;

    $stmt = $pdo->prepare('
        INSERT INTO land_parcels
            (colony_id, khasra_no, survey_number, village, tehsil, district, state,
             area_acres, area_sqft, area_bigha, mutation_status, land_use, notes, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    foreach ($gataData as [$gataNo, $hectares, $decimal, $sqft]) {
        $acres     = round($hectares * 2.47105, 4);  // 1 Ha = 2.47105 acres
        $bigha     = round($hectares * 6.17763, 2);   // 1 Ha ≈ 6.17763 bigha (UP standard)

        $stmt->execute([
            $colonyId,
            "Gata {$gataNo}",           // khasra_no
            "Gata {$gataNo}",           // survey_number
            'Motiram',                   // village
            'Rampur Tappa Rajdhani',     // tehsil
            'Gorakhpur',                 // district
            'Uttar Pradesh',             // state
            $acres,
            round($sqft, 2),
            $bigha,
            'completed',                 // mutation_status
            'residential',               // land_use
            "Gata {$gataNo}: {$decimal} decimal ({$hectares} Ha) — Raghunath Nagri land bank",
            $now,
        ]);

        $totalDecimal += $decimal;
        $totalHectares += $hectares;
    }

    echo "       Total: {$totalHectares} Ha = {$totalDecimal} decimal across 14 gata numbers\n\n";

    /* ─────────────────────────────────────────────────────────
       4. PLOT INVENTORY (120 plots, 5 blocks)
       ───────────────────────────────────────────────────────── */

    echo "[4/4] Generating 120 plots across 5 blocks …\n";

    // Block definitions:  [block, count, area_sqft, base_rate, final_rate, premium_type, payment_plan, corner_flag]
    $blocks = [
        ['A',                  30, 1000, 1500, 1500, 'regular',          'no_emi',       0, 0],
        ['B',                  30, 1000, 1250, 1250, 'regular',          'emi_available', 0, 0],
        ['C',                  40, 1000,  999,  999, 'regular',          'emi_available', 0, 0],
        ['COMMERCIAL_CORNER',  10, 1500, 1500, 1650, 'commercial_corner','no_emi',       1, 1],
        ['CORNER_C',           10, 1000, 1250, 1375, 'corner_c',         'no_emi',       1, 0],
    ];

    $plotInsert = $pdo->prepare('
        INSERT INTO plots
            (colony_id, layout_id, plot_number, block, area_sqft,
             width_ft, length_ft, dimension_label,
             price_per_sqft, base_price_per_sqft, total_price,
             plot_type, corner_plot, park_facing, road_width_ft,
             status, is_active, features, description, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $priceHistInsert = $pdo->prepare('
        INSERT INTO price_history
            (plot_id, colony_id, old_price, new_price,
             old_price_per_sqft, new_price_per_sqft,
             change_type, reason, changed_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $plotCounter = 0;
    $plotSequence = [];

    foreach ($blocks as [$block, $count, $area, $baseRate, $finalRate, $premiumType, $paymentPlan, $cornerFlag, $parkFlag]) {

        $plotType  = ($premiumType === 'commercial_corner') ? 'commercial' : 'residential';
        $roadWidth = ($premiumType === 'commercial_corner') ? 40.0 : 30.0;

        for ($i = 1; $i <= $count; $i++) {
            $plotCounter++;
            $plotNum = sprintf('RN-%s-%03d', $block, $i);

            // Dimension: 20x50 for 1000 sqft, 30x50 for 1500 sqft
            if ($area === 1500) {
                $width = 30.0; $length = 50.0; $dimLabel = '30x50';
            } else {
                $width = 20.0; $length = 50.0; $dimLabel = '20x50';
            }

            $totalPrice = $finalRate * $area;

            $featuresJson = json_encode([
                'payment_plan'  => $paymentPlan,
                'premium_type'  => $premiumType,
                'block'         => $block,
                'facing'        => 'East',
            ]);

            $description = "Plot {$plotNum} — {$dimLabel} ft, {$area} sqft, "
                . "₹{$finalRate}/sqft, Block {$block}";

            $plotInsert->execute([
                $colonyId,
                $layoutId,
                $plotNum,
                $block,
                $area,
                $width,
                $length,
                $dimLabel,
                $finalRate,         // price_per_sqft
                $baseRate,          // base_price_per_sqft
                $totalPrice,
                $plotType,
                $cornerFlag,
                $parkFlag,
                $roadWidth,
                'available',
                1,
                $featuresJson,
                $description,
                $now,
            ]);

            $plotId = (int) $pdo->lastInsertId();

            // 5. Price history audit trail
            $priceHistInsert->execute([
                $plotId,
                $colonyId,
                $totalPrice,        // old_price (baseline = final at launch)
                $totalPrice,        // new_price
                $finalRate,         // old_price_per_sqft
                $finalRate,         // new_price_per_sqft
                'base',
                'Official Raghunath Nagri launch matrix setup',
                $userId,
                $now,
            ]);

            $plotSequence[] = $plotNum;
        }

        echo "       Block {$block}: {$count} plots  (₹{$baseRate}→₹{$finalRate}/sqft  "
            . ($finalRate > $baseRate ? "PLC +".round(($finalRate/$baseRate - 1)*100)."%" : "base") .")\n";
    }

    /* ─────────────────────────────────────────────────────────
       4a. COMMIT
       ───────────────────────────────────────────────────────── */

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    $pdo->commit();

    echo "\n╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ✅  Raghunath Nagri seeded successfully!               ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";

    echo "Summary:\n";
    echo "  Colony ID      : {$colonyId}\n";
    echo "  Layout ID      : {$layoutId}\n";
    echo "  Land Parcels   : 14 gata numbers  ({$totalHectares} Ha / {$totalDecimal} decimal)\n";
    echo "  Total Plots    : {$plotCounter}\n";
    echo "  Price History  : {$plotCounter} baseline records logged\n";
    echo "\n";

    // Quick block breakdown
    echo "Block Breakdown:\n";
    $blockCounts = [];
    foreach ($plotSequence as $p) {
        $b = explode('-', $p)[1]; // RN-**B**-NNN
        $blockCounts[$b] = ($blockCounts[$b] ?? 0) + 1;
    }
    foreach ($blockCounts as $b => $c) {
        echo "  Block {$b}: {$c} plots\n";
    }

} catch (\Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "\n╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ❌  SEED FAILED — Transaction rolled back             ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File:  " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
