<?php
/**
 * Seed Suryodaya Colony Plots (287 plots across 5 blocks)
 * Run: php scripts/seed_suryodaya_plots.php
 */

// Bootstrap
$basePath = dirname(__DIR__);
require_once $basePath . '/config/database.php';

try {
    $db = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage() . "\n");
}

// Ensure colony exists
$stmt = $db->prepare("SELECT id FROM colonies WHERE name LIKE ? LIMIT 1");
$stmt->execute(['%Suryoday%']);
$colony = $stmt->fetch();

if (!$colony) {
    // Need to check if colonies table exists / what columns
    $stmt = $db->query("SHOW TABLES LIKE 'colonies'");
    if ($stmt->rowCount() > 0) {
        $cols = $db->query("SHOW COLUMNS FROM colonies")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (in_array('name', $cols)) {
            $db->prepare("INSERT INTO colonies (name, location, status) VALUES (?, ?, 'active')")->execute(['Suryoday Heights', 'Gorakhpur']);
            $colonyId = $db->lastInsertId();
        } else {
            $colonyId = 1; // fallback
        }
    } else {
        $colonyId = 1; // fallback
    }
} else {
    $colonyId = $colony['id'];
}

// Check if tables exist & what columns plot_master needs
$plotCols = [];
$hasPlotMaster = false;
$stmt = $db->query("SHOW TABLES LIKE 'plot_master'");
if ($stmt->rowCount() > 0) {
    $hasPlotMaster = true;
    $plotCols = $db->query("SHOW COLUMNS FROM plot_master")->fetchAll(PDO::FETCH_COLUMN, 0);
}

// Also check if inventory_plots table exists or create it
$stmt = $db->query("SHOW TABLES LIKE 'inventory_plots'");
if ($stmt->rowCount() == 0) {
    $db->exec("CREATE TABLE IF NOT EXISTS `inventory_plots` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `block_name` varchar(10) NOT NULL,
        `plot_no` varchar(20) NOT NULL,
        `size_sqft` decimal(10,2) NOT NULL,
        `dimension` varchar(50) DEFAULT NULL,
        `basic_price` decimal(15,2) NOT NULL,
        `plc_charges` decimal(15,2) DEFAULT 0.00,
        `status` enum('Available','Hold','Tokenized_25%','Registered') DEFAULT 'Available',
        `colony_id` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_block` (`block_name`),
        KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Created inventory_plots table.\n";
}

// Define plot data
$pricePerSqft = 1500; // ₹1,500/sqft base rate
$plots = [];

// Block A: 6 plots of 40x60 (2400 sqft) + 24 plots of 20x50 (1000 sqft)
$blockA_plots_40x60 = [
    ['A-1','40\'x60\'',2400],['A-2','40\'x60\'',2400],['A-3','40\'x60\'',2400],
    ['A-4','40\'x60\'',2400],['A-5','40\'x60\'',2400],['A-6','40\'x60\'',2400],
];
for ($i = 1; $i <= 24; $i++) {
    $blockA_plots_20x50[] = ['A-' . ($i + 6), '20\'x50\'', 1000];
}
foreach ($blockA_plots_40x60 as $p) {
    $plots[] = ['A', $p[0], $p[1], $p[2], $p[2] * $pricePerSqft, ($p[2] * $pricePerSqft * 0.08)];
}
foreach ($blockA_plots_20x50 as $p) {
    $plots[] = ['A', $p[0], $p[1], $p[2], $p[2] * $pricePerSqft, ($p[2] * $pricePerSqft * 0.05)];
}

// Block B: 103 plots of 20x50 (1000 sqft)
for ($i = 1; $i <= 103; $i++) {
    $plots[] = ['B', "B-$i", '20\'x50\'', 1000, 1000 * $pricePerSqft, 1000 * $pricePerSqft * 0.05];
}

// Block D: 102 plots of 20x50 (1000 sqft)
for ($i = 1; $i <= 102; $i++) {
    $plots[] = ['D', "D-$i", '20\'x50\'', 1000, 1000 * $pricePerSqft, 1000 * $pricePerSqft * 0.05];
}

// Block C: 38 plots of 30x50 (1500 sqft)
for ($i = 1; $i <= 38; $i++) {
    $plots[] = ['C', "C-$i", '30\'x50\'', 1500, 1500 * $pricePerSqft, 1500 * $pricePerSqft * 0.08];
}

// Block CL: 14 plots of 20x50 (1000 sqft)
for ($i = 1; $i <= 14; $i++) {
    $plots[] = ['CL', "CL-$i", '20\'x50\'', 1000, 1000 * $pricePerSqft, 1000 * $pricePerSqft * 0.05];
}

echo "Total plots to seed: " . count($plots) . "\n";

// Clear existing Suryodaya plots
$db->prepare("DELETE FROM inventory_plots WHERE colony_id = ?")->execute([$colonyId]);

// Batch insert
$insertStmt = $db->prepare("INSERT INTO inventory_plots (block_name, plot_no, dimension, size_sqft, basic_price, plc_charges, status, colony_id) VALUES (?, ?, ?, ?, ?, ?, 'Available', ?)");

$count = 0;
$db->beginTransaction();
try {
    foreach ($plots as $p) {
        $insertStmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $colonyId]);
        $count++;
        if ($count % 50 == 0) {
            echo "  Seeded $count plots...\n";
        }
    }
    $db->commit();
    echo "Successfully seeded $count plots for Suryodaya Colony (Colony ID: $colonyId)\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "ERROR seeding plots: " . $e->getMessage() . "\n";
}

// Also seed into existing `plots` table (main inventory)
$plotsTableExists = $db->query("SHOW TABLES LIKE 'plots'")->rowCount() > 0;
if ($plotsTableExists) {
    // Check if already seeded
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM plots WHERE colony_id = ?");
    $stmt->execute([$colonyId]);
    $existingPlots = $stmt->fetch();
    
    if (($existingPlots['cnt'] ?? 0) == 0) {
        echo "Also seeding into plots table...\n";
        $pCols = $db->query("SHOW COLUMNS FROM plots")->fetchAll(PDO::FETCH_COLUMN, 0);
        $hasProjectId = in_array('project_id', $pCols);
        $hasCornerPlot = in_array('corner_plot', $pCols);
        $hasParkFacing = in_array('park_facing', $pCols);
        $hasFrontage = in_array('frontage_ft', $pCols);
        $hasDepth = in_array('depth_ft', $pCols);
        $hasAreaSqm = in_array('area_sqm', $pCols);
        
        // Build dynamic INSERT based on existing columns
        $colList = ['colony_id', 'plot_number', 'block', 'area_sqft', 'total_price', 'status'];
        $valList = ['?', '?', '?', '?', '?', "'available'"];
        $colPlaceholders = ['?', '?', '?', '?', '?', 'available'];
        
        if ($hasProjectId) { $colList[] = 'project_id'; $valList[] = '?'; $colPlaceholders[] = 2; }
        if ($hasCornerPlot) { $colList[] = 'corner_plot'; $valList[] = '?'; $colPlaceholders[] = 0; }
        if ($hasParkFacing) { $colList[] = 'park_facing'; $valList[] = '?'; $colPlaceholders[] = 0; }
        if ($hasFrontage) { $colList[] = 'frontage_ft'; $valList[] = '?'; }
        if ($hasDepth) { $colList[] = 'depth_ft'; $valList[] = '?'; }
        if ($hasAreaSqm) { $colList[] = 'area_sqm'; $valList[] = '?'; }
        $colList[] = 'created_at'; $valList[] = 'NOW()';
        
        $sql = "INSERT INTO plots (" . implode(',', $colList) . ") VALUES (" . implode(',', $valList) . ")";
        $plotsInsert = $db->prepare($sql);
        
        $pmCount = 0;
        $db->beginTransaction();
        try {
            foreach ($plots as $p) {
                $block = $p[0];
                $plotNum = $p[1];
                $dim = $p[2];
                $sqft = $p[3];
                $price = $p[4];
                
                // Parse dimensions for frontage/depth
                $frontage = 0; $depth = 0;
                if (preg_match("/(\d+)'x(\d+)'/", $dim, $m)) {
                    $frontage = (float)$m[1];
                    $depth = (float)$m[2];
                }
                
                $params = [$colonyId, $plotNum, $block, $sqft, $price];
                if ($hasProjectId) $params[] = null; // project_id
                if ($hasCornerPlot) $params[] = 0;
                if ($hasParkFacing) $params[] = 0;
                if ($hasFrontage) $params[] = $frontage;
                if ($hasDepth) $params[] = $depth;
                if ($hasAreaSqm) $params[] = round($sqft * 0.0929, 2);
                
                $plotsInsert->execute($params);
                $pmCount++;
            }
            $db->commit();
            echo "Seeded $pmCount plots into plots table.\n";
        } catch (Exception $e) {
            $db->rollBack();
            echo "WARNING: Could not seed plots table: " . $e->getMessage() . "\n";
        }
    } else {
        echo "plots table already has {$existingPlots['cnt']} Suryodaya plots, skipping.\n";
    }
}

// Also seed into plot_master if it exists and has compatible columns
if ($hasPlotMaster && in_array('plot_no', $plotCols) && in_array('area', $plotCols) && in_array('plot_price', $plotCols)) {
    $pmStmt = $db->prepare("SELECT COUNT(*) as cnt FROM plot_master WHERE plot_no LIKE ? LIMIT 1");
    $pmStmt->execute(['A-1']);
    $exists = $pmStmt->fetch();
    
    if ($exists['cnt'] == 0) {
        echo "Also seeding into plot_master table...\n";
        $pmInsert = $db->prepare("INSERT INTO plot_master (plot_no, area, plot_dimension, plot_price, plot_status, site_id) VALUES (?, ?, ?, ?, 'Available', ?)");
        $siteId = 1;
        // Try to get actual site_id for Suryoday
        $stmt = $db->query("SELECT id FROM sites WHERE name LIKE '%Suryoday%' LIMIT 1");
        $site = $stmt->fetch();
        if ($site) $siteId = $site['id'];
        
        $pmCount = 0;
        foreach ($plots as $p) {
            $pmInsert->execute([$p[1], $p[3], $p[2], $p[4], $siteId]);
            $pmCount++;
        }
        echo "Seeded $pmCount plots into plot_master.\n";
    } else {
        echo "plot_master already seeded, skipping.\n";
    }
}

echo "\nDone! Suryodaya Colony seeding complete.\n";
