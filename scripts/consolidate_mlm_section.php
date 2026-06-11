<?php
/**
 * MLM Section Consolidation — reduce 35 items to ~18 essential items
 * Run: php scripts/consolidate_mlm_section.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== MLM SECTION CONSOLIDATION ===\n\n";
    
    // Items to REMOVE (redundant/overlapping):
    $removals = [
        // Network/Tree: keep Network Tree (93), remove Genealogy (118) — same thing
        118,
        // MLM Dashboard (295): keep MLM overview (37), remove standalone dashboard
        295,
        // MLM Settings (151): merge into Commission Rules (152) — keep rules
        151,
        // MLM Levels (161): merge into Commission Structure (158) — keep structure
        161,
        // MLM Records (162): merge into MLM Analytics (163) — keep analytics
        162,
        // MLM Analytics (163): keep — covers records + analytics
        // Revenue Daily (164): merge into MLM Analytics (163)
        164,
        // Rank Evaluation (153): merge into Rank Criteria (180)
        153,
        // Rank Progress (154): merge into Rank Criteria (180)
        154,
        // Rank Upgrades (181): merge into Rank Criteria (180)
        181,
        // Rank Benefits (322): merge into Rank Criteria (180)
        322,
        // Manual Rank Promotion (321): merge into Rank Criteria (180)
        321,
        // Pending Payouts (320): merge into Payouts (39)
        320,
        // Payout Batches (296): merge into Payouts (39)
        296,
        // MLM & Enterprise (156): duplicate of MLM overview (37)
        156,
        // Business Associates (224): duplicate of All Associates (67)
        224,
        // Top Performers (226): keep but move to operations section or reports
        226,
        // Calculations (159): merge into Associate Structure (158)
        159,
        // Telecaller Rules (165): move to CRM/telecalling section
        // Actually, let's remove it from MLM — telecaller has its own section
        165,
        // Telecaller Comm. (166): same — remove from MLM
        166,
        // Agent Rates (157): merge into Commissions (69)
        157,
        // Bonuses (160): merge into Commissions (69)
        160,
    ];
    
    echo "1. Removing redundant MLM items...\n";
    foreach ($removals as $id) {
        $pdo->exec("DELETE FROM admin_menu_items WHERE id = $id");
        echo "   Removed id=$id\n";
    }
    
    // Now update the Rank Criteria item to reflect it's the consolidated rank page
    $pdo->exec("UPDATE admin_menu_items SET name = 'Rank Management', url = '/admin/mlm-settings/evaluate' WHERE id = 180");
    echo "\n2. Updated id=180 → 'Rank Management' at /admin/mlm-settings/evaluate\n";
    
    // Update Associate Structure to include calculations
    $pdo->exec("UPDATE admin_menu_items SET name = 'Associate Commission' WHERE id = 158");
    echo "3. Updated id=158 → 'Associate Commission'\n";
    
    // Update MLM Analytics to include records
    $pdo->exec("UPDATE admin_menu_items SET name = 'MLM Analytics & Records' WHERE id = 163");
    echo "4. Updated id=163 → 'MLM Analytics & Records'\n";
    
    // Final count
    $result = $pdo->query("SELECT COUNT(*) as cnt FROM admin_menu_items WHERE is_active=1 AND section='mlm'");
    $count = $result->fetch()['cnt'];
    
    $total = $pdo->query("SELECT COUNT(*) as cnt FROM admin_menu_items WHERE is_active=1")->fetch()['cnt'];
    
    echo "\n=== SUMMARY ===\n";
    echo "MLM items removed: " . count($removals) . "\n";
    echo "Remaining MLM items: $count\n";
    echo "Total active items: $total\n";
    
    // Show final MLM items
    echo "\nFinal MLM section:\n";
    $items = $pdo->query("SELECT id, name, url, order_index FROM admin_menu_items WHERE is_active=1 AND section='mlm' ORDER BY order_index");
    while ($row = $items->fetch()) {
        echo "  [{$row['id']}] {$row['name']} → {$row['url']}\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
