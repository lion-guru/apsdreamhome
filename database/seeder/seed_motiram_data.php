<?php
/**
 * APS Motiram Township — Complete Data Seeder
 * ─────────────────────────────────────────────
 * Project : APS Motiram Township
 * Developer : APS Dream Homes Pvt. Ltd.
 * Location  : Jhangha Road, Gorakhpur (Uttar Pradesh)
 * Slug      : motiram-jhangha-road
 *
 * Seed pipeline:
 *   1. Farmers (6 farmer_profiles + 6 farmers + land holdings)
 *   2. Colony & Layout
 *   3. Land parcels (Jhangha Road gata numbers)
 *   4. Land lead + Land deal (Krishna Chand)
 *   5. Plot inventory (91 plots: Pink Block A1-A51 + Yellow Block Y1-Y40)
 *   6. Price history audit trail (every plot baseline logged)
 *
 * Premiums (Pink Block):
 *   - Corner plots (A1, A11, A21, A30, A39, A43, A51): +10%
 *   - Park-facing plots (A44-A47): +15%
 *
 * Usage:  php database/seeder/seed_motiram_data.php
 * Safety:  Full transaction — rolls back on any failure.
 * Idempotent: checks slug before seeding.
 */

$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';
$pdo    = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$now       = date('Y-m-d H:i:s');
$userId    = 1;   // admin / system user for changed_by
$districtId = 5;  // Gorakhpur
$stateId    = 17; // Uttar Pradesh

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  APS Motiram Township — Data Seeder                    ║\n";
echo "║  APS Dream Homes Pvt. Ltd.                              ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

/* ─────────────────────────────────────────────────────────────
   1. IDEMPOTENCY CHECK
   ───────────────────────────────────────────────────────────── */
$stmt = $pdo->prepare('SELECT id, name FROM colonies WHERE slug = ?');
$stmt->execute(['motiram-jhangha-road']);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "⚠  APS Motiram Township data is already seeded!\n";
    echo "   Colony ID: {$existing['id']}  Name: {$existing['name']}\n";
    echo "   Nothing to do. Exiting.\n";
    exit(0);
}

echo "[1/6] Idempotency check … PASS (slug not found)\n\n";

/* ─────────────────────────────────────────────────────────────
   BEGIN TRANSACTION
   ───────────────────────────────────────────────────────────── */
$pdo->beginTransaction();
echo "[TX]  Transaction started\n";

try {

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    /* ─────────────────────────────────────────────────────────
       2. FARMERS (6 farmers)
       ───────────────────────────────────────────────────────── */

    echo "[2/6] Creating 6 farmers …\n";

    $farmersData = [
        //  name,              phone,           village,         aadhaar,          pan,            bank_acct,         ifsc
        ['Krishna Chand',    '+91 99184 56210', 'Jhangha',      '3456-7890-1234', 'AXSPC1234A',   '30123456789',     'SBIN0001234'],
        ['Ram Avtar Yadav',  '+91 97923 45678', 'Jhangha',      '4567-8901-2345', 'AXSPY5678B',   '30234567890',     'HDFC0002345'],
        ['Shyam Sunder',     '+91 88765 43210', 'Jhangha',      '5678-9012-3456', 'AXSPS9012C',   '30345678901',     'UBIN0003456'],
        ['Gopal Prasad',     '+91 94567 89012', 'Jhangha',      '6789-0123-4567', 'AXSPG3456D',   '30456789012',     'PUNB0004567'],
        ['Hari Om Mishra',   '+91 80987 65432', 'Jhangha',      '7890-1234-5678', 'AXSPH7890E',   '30567890123',     'BARB0005678'],
        ['Ramesh Verma',     '+91 98765 43210', 'Jhangha',      '8901-2345-6789', 'AXSPR2345F',   '30678901234',     'CBIN0006789'],
    ];

    // 2a. farmers table (core records)
    $farmerInsert = $pdo->prepare('
        INSERT INTO farmers
            (name, email, phone, address, state, district, region, state_id, district_id,
             aadhar_number, pan_number, bank_account, ifsc_code, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $farmerIds = [];
    foreach ($farmersData as [$name, $phone, $village, $aadhar, $pan, $bank, $ifsc]) {
        $farmerInsert->execute([
            $name,
            strtolower(str_replace(' ', '.', $name)) . '@apsfarmers.com',
            $phone,
            "{$village}, Jhangha Road, Gorakhpur",
            'Uttar Pradesh',
            'Gorakhpur',
            'Gorakhpur',
            $stateId,
            $districtId,
            $aadhar,
            $pan,
            $bank,
            $ifsc,
            'active',
            $now,
        ]);
        $farmerIds[] = (int) $pdo->lastInsertId();
    }

    echo "       Created " . count($farmerIds) . " farmer records: IDs " . implode(', ', $farmerIds) . "\n";

    // 2b. farmer_profiles table (extended records)
    $profileInsert = $pdo->prepare('
        INSERT INTO farmer_profiles
            (farmer_number, full_name, father_name, phone, aadhar_number, pan_number,
             bank_account_number, ifsc_code, account_holder_name,
             village, tehsil, district, state, pincode,
             total_land_holding, status, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $profileData = [
        //  farmer_num  full_name,              father_name,          land_holding (decimal)
        ['F010', 'Krishna Chand',    'Pt. Ram Dayal',    22.50],
        ['F011', 'Ram Avtar Yadav',  'Shri Bhagwan',     15.00],
        ['F012', 'Shyam Sunder',     'Late Harihar',     18.75],
        ['F013', 'Gopal Prasad',     'Shri Raghunath',   12.00],
        ['F014', 'Hari Om Mishra',   'Pt. Ram Lakhan',   30.25],
        ['F015', 'Ramesh Verma',     'Shri Girdhari',    8.50],
    ];

    for ($i = 0; $i < count($farmersData); $i++) {
        [$name, $phone] = [$farmersData[$i][0], $farmersData[$i][1]];
        [$fnum, $fname, $father, $land] = $profileData[$i];

        $profileInsert->execute([
            $fnum,
            $name,
            $father,
            $phone,
            $farmersData[$i][3],    // aadhar
            $farmersData[$i][4],    // pan
            $farmersData[$i][5],    // bank_account
            $farmersData[$i][6],    // ifsc
            $name,                   // account_holder_name
            'Jhangha',
            'Rampur Tappa Rajdhani',
            'Gorakhpur',
            'Uttar Pradesh',
            '273001',
            $land,
            'active',
            $userId,
            $now,
        ]);
    }

    echo "       Created 6 farmer_profiles (F010-F015)\n";

    // 2c. farmer_land_holdings table
    $holdingInsert = $pdo->prepare('
        INSERT INTO farmer_land_holdings
            (farmer_id, khasra_number, land_area, land_area_unit, land_type,
             soil_type, irrigation_source, electricity_available, road_access,
             village, tehsil, district, state, land_value,
             current_status, acquisition_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $holdingsData = [
        //  farmer_id_index (0-5),  khasra,  area decimal,  soil_type,  land_value
        [0, 'Gata 887',  22.50, 'alluvial', 'tube_well', 1575000],
        [1, 'Gata 888',  15.00, 'alluvial', 'canal',     1050000],
        [2, 'Gata 889',  18.75, 'loamy',    'tube_well', 1312500],
        [3, 'Gata 890',  12.00, 'alluvial', 'bore_well',  840000],
        [4, 'Gata 891',  30.25, 'clay',     'canal',     2117500],
        [5, 'Gata 892',   8.50, 'loamy',    'tube_well',  595000],
    ];

    foreach ($holdingsData as [$idx, $khasra, $area, $soil, $irrigation, $value]) {
        $holdingInsert->execute([
            $farmerIds[$idx],
            $khasra,
            $area,
            'decimal',
            'agricultural',
            $soil,
            $irrigation,
            1,  // electricity
            1,  // road access
            'Jhangha',
            'Rampur Tappa Rajdhani',
            'Gorakhpur',
            'Uttar Pradesh',
            $value,
            'cultivated',
            'under_negotiation',
            $now,
        ]);
    }

    echo "       Created 6 land holdings (Gata 887-892)\n\n";

    /* ─────────────────────────────────────────────────────────
       3. COLONY & LAYOUT
       ───────────────────────────────────────────────────────── */

    echo "[3/6] Creating colony and layout …\n";

    // 3a. Colony
    $totalPlots    = 91;  // 51 Pink + 40 Yellow
    $startingPrice = 1400.00;  // Yellow Block base rate

    $colonyDesc = "APS Motiram Township is a premium residential colony developed by APS Dream Homes Pvt. Ltd. "
        . "Located on Jhangha Road, Gorakhpur, Uttar Pradesh. "
        . "Offering 91 residential and commercial plots across 2 blocks (Pink and Yellow) "
        . "with flexible EMI plans, competitive pricing, and world-class amenities.";

    $stmt = $pdo->prepare('
        INSERT INTO colonies
            (district_id, name, slug, description, total_plots, available_plots,
             starting_price, is_active, is_featured, show_plots_publicly,
             contact_phone, contact_email)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $districtId,
        'APS Motiram Township',
        'motiram-jhangha-road',
        $colonyDesc,
        $totalPlots,
        $totalPlots,
        $startingPrice,
        1, 1, 1,
        '+91 92771 21112',
        'info@apsdreamhome.com',
    ]);

    $colonyId = (int) $pdo->lastInsertId();
    echo "       Colony created — ID {$colonyId}\n";

    // 3b. Layout
    $totalAreaSqft = 91000;  // ~1000 sqft per plot avg

    $stmt = $pdo->prepare('
        INSERT INTO colony_layouts
            (colony_id, layout_name, version, layout_type, road_area_pct,
             common_area_pct, is_current, total_plots, total_area_sqft,
             status, approved_by, approval_date, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $colonyId,
        'APS Motiram Township Phase 1',
        '1.0',
        'residential',
        12.00,
        6.00,
        1,
        $totalPlots,
        $totalAreaSqft,
        'approved',
        'APS Dream Homes',
        $now,
        $now,
    ]);

    $layoutId = (int) $pdo->lastInsertId();
    echo "       Layout created — ID {$layoutId}\n\n";

    /* ─────────────────────────────────────────────────────────
       4. LAND PARCELS (Jhangha Road gata numbers)
       ───────────────────────────────────────────────────────── */

    echo "[4/6] Inserting land parcels …\n";

    $parcelData = [
        //  gata_no  |  hectares  |  decimal  |  sqft
        [887, 0.4560, 112.50, 49035.00],
        [888, 0.3045,  75.00, 32686.00],
        [889, 0.3800,  93.75, 40854.00],
        [890, 0.2430,  60.00, 26092.00],
        [891, 0.6120, 151.25, 65749.00],
        [892, 0.1720,  42.50, 18450.00],
        [893, 0.2100,  51.90, 22545.00],
        [894, 0.1850,  45.70, 19842.00],
    ];

    $parcelInsert = $pdo->prepare('
        INSERT INTO land_parcels
            (colony_id, khasra_no, survey_number, village, tehsil, district, state,
             area_acres, area_sqft, area_bigha, mutation_status, land_use, notes, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $totalDecimal = 0;
    $totalHectares = 0;

    foreach ($parcelData as [$gataNo, $hectares, $decimal, $sqft]) {
        $acres = round($hectares * 2.47105, 4);
        $bigha = round($hectares * 6.17763, 2);

        $parcelInsert->execute([
            $colonyId,
            "Gata {$gataNo}",
            "Gata {$gataNo}",
            'Jhangha',
            'Rampur Tappa Rajdhani',
            'Gorakhpur',
            'Uttar Pradesh',
            $acres,
            round($sqft, 2),
            $bigha,
            'completed',
            'residential',
            "Gata {$gataNo}: {$decimal} decimal ({$hectares} Ha) — APS Motiram Township land bank",
            $now,
        ]);

        $totalDecimal += $decimal;
        $totalHectares += $hectares;
    }

    echo "       Total: {$totalHectares} Ha = {$totalDecimal} decimal across 8 gata numbers\n\n";

    /* ─────────────────────────────────────────────────────────
       5. LAND LEAD + LAND DEAL (Krishna Chand)
       ───────────────────────────────────────────────────────── */

    echo "[5/6] Creating land lead + deal for Krishna Chand …\n";

    // 5a. Land lead
    $stmt = $pdo->prepare('
        INSERT INTO land_leads
            (lead_source, land_owner_name, owner_phone, owner_email,
             village, tehsil, district, state, pincode,
             survey_number, area_acres, area_sqft, expected_price,
             status, assigned_to, notes, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $leadAreaAcres  = round(22.50 * 0.404686, 2);  // 22.50 decimal ≈ 9.11 acres
    $leadAreaSqft   = round(22.50 * 435.6, 2);      // 1 decimal = 435.6 sqft
    $leadPrice      = 1575000.00;                     // ₹70,000/decimal

    $stmt->execute([
        'direct',
        'Krishna Chand',
        '+91 99184 56210',
        'krishna.chand@apsfarmers.com',
        'Jhangha',
        'Rampur Tappa Rajdhani',
        'Gorakhpur',
        'Uttar Pradesh',
        '273001',
        'Gata 887',
        $leadAreaAcres,
        $leadAreaSqft,
        $leadPrice,
        'registered',  // completed acquisition
        1,             // assigned_to
        "Krishna Chand — 22.50 decimal (Gata 887). Acquired for APS Motiram Township. Full mutation completed.",
        $now,
    ]);

    $landLeadId = (int) $pdo->lastInsertId();
    echo "       Land lead created — ID {$landLeadId}\n";

    // 5b. Land deal
    $stmt = $pdo->prepare('
        INSERT INTO land_deals
            (land_lead_id, colony_id, total_area_sqft, acquired_area_sqft,
             total_consideration, advance_paid, balance_amount,
             sale_agreement_date, sale_agreement_number,
             registration_date, registration_number, sub_registrar_office,
             stamp_duty_amount, registration_fee,
             mutation_status, mutation_number, mutation_date,
             status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stampDuty    = round($leadPrice * 0.07, 2);   // 7% stamp duty (UP)
    $regFee       = round($leadPrice * 0.01, 2);   // 1% registration fee

    $stmt->execute([
        $landLeadId,
        $colonyId,
        $leadAreaSqft,
        $leadAreaSqft,
        $leadPrice,
        $leadPrice,        // fully paid
        0,                 // no balance
        $now,
        'SA-MOTIRAM-' . date('Ymd') . '-001',
        $now,
        'REG-MOTIRAM-' . date('Ymd') . '-001',
        'SRO Gorakhpur',
        $stampDuty,
        $regFee,
        'completed',
        'MUT-' . date('Ymd') . '-001',
        $now,
        'registered',
        $now,
    ]);

    $landDealId = (int) $pdo->lastInsertId();
    echo "       Land deal created — ID {$landDealId}  (₹{$leadPrice})\n\n";

    /* ─────────────────────────────────────────────────────────
       6. PLOT INVENTORY (91 plots: 51 Pink + 40 Yellow)
       ───────────────────────────────────────────────────────── */

    echo "[6/6] Generating 91 plots across 2 blocks …\n";

    // Corner plots for Pink Block: A1, A11, A21, A30, A39, A43, A51
    $pinkCornerPlots = [1, 11, 21, 30, 39, 43, 51];
    // Park-facing plots: A44-A47
    $pinkParkPlots   = range(44, 47);

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
    $totalValue  = 0;

    // ── PINK BLOCK (A1-A51): Premium residential ──
    $pinkBaseRate = 1500.00;  // ₹1500/sqft base
    $pinkArea     = 1000;     // 1000 sqft per plot

    echo "       PINK BLOCK: 51 plots at ₹{$pinkBaseRate}/sqft base\n";

    for ($i = 1; $i <= 51; $i++) {
        $plotCounter++;
        $plotNum = sprintf('MT-A-%03d', $i);

        $isCorner  = in_array($i, $pinkCornerPlots);
        $isPark    = in_array($i, $pinkParkPlots);

        // Premium calculation
        $rate = $pinkBaseRate;
        $plcLabel = 'base';

        if ($isCorner) {
            $rate = round($pinkBaseRate * 1.10, 2);  // +10% corner
            $plcLabel = 'corner +10%';
        } elseif ($isPark) {
            $rate = round($pinkBaseRate * 1.15, 2);  // +15% park-facing
            $plcLabel = 'park-facing +15%';
        }

        $width = 20.0; $length = 50.0;
        $totalPrice = $rate * $pinkArea;
        $totalValue += $totalPrice;

        $featuresJson = json_encode([
            'payment_plan'  => 'emi_available',
            'premium_type'  => $plcLabel,
            'block'         => 'PINK',
            'facing'        => ($isCorner ? 'East-West' : 'East'),
        ]);

        $description = "Plot {$plotNum} — 20x50 ft, {$pinkArea} sqft, "
            . "₹{$rate}/sqft ({$plcLabel}), Block Pink";

        $plotInsert->execute([
            $colonyId,
            $layoutId,
            $plotNum,
            'PINK',
            $pinkArea,
            $width,
            $length,
            '20x50',
            $rate,                 // price_per_sqft (with PLC)
            $pinkBaseRate,         // base_price_per_sqft
            $totalPrice,
            'residential',
            $isCorner ? 1 : 0,
            $isPark ? 1 : 0,
            30.0,                  // road_width_ft
            'available',
            1,
            $featuresJson,
            $description,
            $now,
        ]);

        $plotId = (int) $pdo->lastInsertId();

        // Price history audit trail
        $priceHistInsert->execute([
            $plotId,
            $colonyId,
            $totalPrice,
            $totalPrice,
            $rate,
            $rate,
            'base',
            "APS Motiram Township launch — Pink Block {$plcLabel}",
            $userId,
            $now,
        ]);

        if ($isCorner) {
            echo "       {$plotNum}: ₹{$rate}/sqft ({$plcLabel}) — ₹" . number_format($totalPrice) . "\n";
        } elseif ($isPark) {
            echo "       {$plotNum}: ₹{$rate}/sqft ({$plcLabel}) — ₹" . number_format($totalPrice) . "\n";
        }
    }

    // ── YELLOW BLOCK (Y1-Y40): Standard residential ──
    $yellowBaseRate = 1400.00;  // ₹1400/sqft base
    $yellowArea     = 1000;     // 1000 sqft per plot

    echo "\n       YELLOW BLOCK: 40 plots at ₹{$yellowBaseRate}/sqft base\n";

    for ($i = 1; $i <= 40; $i++) {
        $plotCounter++;
        $plotNum = sprintf('MT-Y-%03d', $i);

        $totalPrice = $yellowBaseRate * $yellowArea;
        $totalValue += $totalPrice;

        $featuresJson = json_encode([
            'payment_plan'  => 'emi_available',
            'premium_type'  => 'base',
            'block'         => 'YELLOW',
            'facing'        => 'East',
        ]);

        $description = "Plot {$plotNum} — 20x50 ft, {$yellowArea} sqft, "
            . "₹{$yellowBaseRate}/sqft (base), Block Yellow";

        $plotInsert->execute([
            $colonyId,
            $layoutId,
            $plotNum,
            'YELLOW',
            $yellowArea,
            20.0,
            50.0,
            '20x50',
            $yellowBaseRate,
            $yellowBaseRate,
            $totalPrice,
            'residential',
            0,   // no corner flag
            0,   // no park-facing
            30.0,
            'available',
            1,
            $featuresJson,
            $description,
            $now,
        ]);

        $plotId = (int) $pdo->lastInsertId();

        $priceHistInsert->execute([
            $plotId,
            $colonyId,
            $totalPrice,
            $totalPrice,
            $yellowBaseRate,
            $yellowBaseRate,
            'base',
            'APS Motiram Township launch — Yellow Block base',
            $userId,
            $now,
        ]);
    }

    /* ─────────────────────────────────────────────────────────
       COMMIT
       ───────────────────────────────────────────────────────── */

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    $pdo->commit();

    echo "\n╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ✅  APS Motiram Township seeded successfully!         ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";

    echo "Summary:\n";
    echo "  Colony ID      : {$colonyId}\n";
    echo "  Layout ID      : {$layoutId}\n";
    echo "  Farmers        : 6 ({$farmerIds[0]}–{$farmerIds[5]})\n";
    echo "  Farmer Profiles: F010–F015\n";
    echo "  Land Holdings  : 6 (Gata 887–892)\n";
    echo "  Land Lead      : ID {$landLeadId} (Krishna Chand)\n";
    echo "  Land Deal      : ID {$landDealId} (₹" . number_format($leadPrice) . ")\n";
    echo "  Land Parcels   : 8 gata numbers ({$totalHectares} Ha / {$totalDecimal} decimal)\n";
    echo "  Total Plots    : {$plotCounter}\n";
    echo "  Total Value    : ₹" . number_format($totalValue) . "\n";
    echo "  Price History  : {$plotCounter} baseline records logged\n";
    echo "\n";

    // Block breakdown
    echo "Block Breakdown:\n";
    echo "  PINK   : 51 plots  (₹{$pinkBaseRate}/sqft base)\n";
    echo "           Corner +10%: A1, A11, A21, A30, A39, A43, A51\n";
    echo "           Park +15% : A44-A47\n";
    echo "  YELLOW : 40 plots  (₹{$yellowBaseRate}/sqft base)\n";

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
