<?php
/**
 * Sidebar Cleanup Script - Remove duplicates, fix broken URLs, consolidate items
 * Run: php scripts/cleanup_sidebar.php
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
    
    echo "=== SIDEBAR CLEANUP ===\n\n";
    
    // 1. Remove duplicate dashboards (keep first set, remove second set)
    $dashboardRemovals = [
        246,  // CEO Dashboard (/admin/ceo-dashboard) â€” duplicate of id=5
        247,  // CFO Dashboard (/admin/cfo-dashboard) â€” duplicate of id=6
        248,  // Builder Dashboard (/admin/builder-dashboard) â€” duplicate of id=4
        249,  // Agent Dashboard (/admin/agent-dashboard) â€” duplicate of id=3
        205,  // CM Dashboard (/admin/cm-dashboard) â€” duplicate of id=7
        269,  // Sales Dashboard (/admin/sales-dashboard) â€” duplicate of id=16
        304,  // Cash Collections in dashboards â€” belongs in finance (id=308)
    ];
    
    echo "1. Removing duplicate dashboard items...\n";
    foreach ($dashboardRemovals as $id) {
        $pdo->exec("DELETE FROM admin_menu_items WHERE id = $id");
        echo "   Removed id=$id\n";
    }
    
    // 2. Remove duplicate Resell Properties (keep id=98, remove id=252)
    echo "\n2. Removing duplicate Resell Properties...\n";
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 252");
    echo "   Removed id=252 (/admin/features/resell)\n";
    
    // 3. Remove duplicate Land Acquisitions (keep id=286 in inventory, remove id=187 in colony)
    echo "\n3. Removing duplicate Land Acquisitions...\n";
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 187");
    echo "   Removed id=187 (/admin/land/acquisitions)\n";
    
    // 4. Remove duplicate Services (keep id=32 in CRM, remove id=184 in legal)
    echo "\n4. Removing duplicate Services...\n";
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 184");
    echo "   Removed id=184 (/admin/legal/services)\n";
    
    // 5. Remove duplicate Testimonials (keep id=202 in content, remove id=30 in cms)
    echo "\n5. Removing duplicate Testimonials...\n";
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 30");
    echo "   Removed id=30 (/admin/testimonials)\n";
    
    // 6. Remove duplicate FAQs (keep in CMS section already at id=105 Blog Posts)
    echo "\n6. Removing duplicate FAQs...\n";
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 203");
    echo "   Removed id=203 (/admin/faqs)\n";
    
    // 7. Remove duplicate API Keys (keep id=177 API Integrations, remove id=31)
    echo "\n7. Removing duplicate API Keys...\n";
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 31");
    echo "   Removed id=31 (/admin/api-keys)\n";
    
    // 8. Consolidate Backup/Performance items
    // Keep: 228 Performance Dashboard (/admin/system-perf)
    // Remove: 229-232 sub-pages, 209 Performance Monitor, 218 Backup Integrity
    echo "\n8. Consolidating Backup/Performance items...\n";
    $perfRemovals = [229, 230, 231, 232, 209, 218];
    foreach ($perfRemovals as $id) {
        $pdo->exec("DELETE FROM admin_menu_items WHERE id = $id");
        echo "   Removed id=$id\n";
    }
    
    // 9. Remove duplicate Commissions from CRM (keep MLM id=69 and Sales id=289)
    echo "\n9. Removing duplicate Commissions from CRM...\n";
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 89");
    echo "   Removed id=89 (/admin/telecalling/commissions)\n";
    
    // 10. Consolidate Reports section â€” remove redundant duplicates
    echo "\n10. Removing redundant Reports duplicates...\n";
    // Reports Dashboard (197) is duplicate of Reports (40) and Reports Engine (206)
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 197");
    echo "    Removed id=197 (/admin/reports-new)\n";
    // Daily/Weekly/Monthly Reports â€” merge into one
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 198");
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 199");
    $pdo->exec("DELETE FROM admin_menu_items WHERE id = 200");
    echo "    Removed ids=198,199,200 (Daily/Weekly/Monthly Reports â€” consolidated)\n";
    
    // Final count
    $result = $pdo->query("SELECT COUNT(*) as cnt FROM admin_menu_items WHERE is_active=1");
    $count = $result->fetch()['cnt'];
    
    echo "\n=== SUMMARY ===\n";
    echo "Items removed: " . (count($dashboardRemovals) + 7 + 1 + 1 + 1 + 1 + 1 + count($perfRemovals) + 1 + 4) . "\n";
    echo "Remaining active items: $count\n";
    
    // Show sections with counts
    echo "\nItems per section:\n";
    $sections = $pdo->query("SELECT section, COUNT(*) as cnt FROM admin_menu_items WHERE is_active=1 GROUP BY section ORDER BY section");
    while ($row = $sections->fetch()) {
        echo "  {$row['section']}: {$row['cnt']}\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}?>