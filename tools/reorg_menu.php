<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->beginTransaction();

try {
    // === STEP 1: Delete placeholder/broken items ===
    $pdo->exec("DELETE FROM admin_menu_items WHERE url = '#'"); // Role Dashboards, Bookings & Plots, AI Calling
    $pdo->exec("DELETE FROM admin_menu_items WHERE name = 'All Posts' AND section = 'main'"); // duplicate of News
    $pdo->exec("DELETE FROM admin_menu_items WHERE name = 'Add Post' AND section = 'main'"); // covered by News

    // === STEP 2: Move dashboards out of main ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'dashboards' WHERE section = 'main' AND url IN (
        '/admin/dashboard','/admin/dashboard/agent','/admin/dashboard/builder',
        '/admin/dashboard/ceo','/admin/dashboard/cfo','/admin/dashboard/cm',
        '/admin/dashboard/coo','/admin/dashboard/cto','/admin/dashboard/director',
        '/admin/dashboard/finance','/admin/dashboard/hr','/admin/dashboard/it',
        '/admin/dashboard/marketing','/admin/dashboard/operations','/admin/dashboard/sales',
        '/admin/dashboard/superadmin'
    )");

    // === STEP 3: Move content/cms out of main ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'cms' WHERE section = 'main' AND url IN (
        '/admin/news','/admin/news/create','/admin/pages','/admin/gallery',
        '/admin/testimonials','/admin/blog','/admin/careers','/admin/legal-pages'
    )");

    // === STEP 4: Move properties out of main ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'properties' WHERE section = 'main' AND url IN (
        '/admin/properties','/admin/projects','/admin/sites','/admin/plots',
        '/admin/resell-properties','/admin/user-properties','/admin/colonies'
    )");

    // === STEP 5: Move financial out of main ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'financial' WHERE section = 'main' AND url IN (
        '/admin/bookings','/admin/plot-costs'
    )");

    // === STEP 6: Move MLM items out of main ===
    $pdo->exec("DELETE FROM admin_menu_items WHERE section = 'main' AND url = '/admin/commission'"); // dup, already in mlm
    $pdo->exec("UPDATE admin_menu_items SET section = 'mlm' WHERE section = 'main' AND url IN (
        '/admin/mlm','/admin/payouts','/admin/mlm-realestate'
    )");

    // === STEP 7: Move reports out of main ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'reports' WHERE section = 'main' AND url IN (
        '/admin/reports','/admin/analytics','/admin/reports/sales','/admin/reports/leads','/admin/reports/commission'
    )");

    // === STEP 8: Move CRM items out of main ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'crm' WHERE section = 'main' AND url IN (
        '/admin/leads','/admin/deals','/admin/inquiries','/admin/services'
    )");
    // Telecalling is already in crm

    // === STEP 9: Move campaigns to marketing ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'marketing' WHERE section = 'main' AND url IN (
        '/admin/campaigns','/admin/visits'
    )");

    // === STEP 10: Move user/admin items ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'users' WHERE section = 'main' AND url IN (
        '/admin/users'
    )");
    $pdo->exec("UPDATE admin_menu_items SET section = 'settings' WHERE section = 'main' AND url IN (
        '/admin/api-keys','/admin/ai_settings'
    )");
    // Remove settings duplicates from main
    $pdo->exec("DELETE FROM admin_menu_items WHERE section = 'main' AND (url = '/admin/settings' OR url LIKE '/admin/settings/%')");

    // === STEP 11: Move operations out of main ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'operations' WHERE section = 'main' AND url IN (
        '/admin/tasks'
    )");

    // === STEP 12: Move locations out of main ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'locations' WHERE section = 'main' AND url IN (
        '/admin/locations/states'
    )");

    // === STEP 13: Merge orphan single-item sections ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'operations' WHERE section = 'hrm'");
    $pdo->exec("UPDATE admin_menu_items SET section = 'bookings' WHERE section = 'bookings_plots'");
    // ai section - the only item was 'AI Calling → #' which was already deleted

    // === STEP 14: Remaining main items → general ===
    $pdo->exec("UPDATE admin_menu_items SET section = 'general' WHERE section = 'main'");

    $pdo->commit();
    echo "Menu reorganization complete!\n";

    $items = $pdo->query("SELECT section, COUNT(*) as cnt FROM admin_menu_items GROUP BY section ORDER BY section")->fetchAll(PDO::FETCH_ASSOC);
    echo "\n=== NEW STRUCTURE ===\n";
    $total = 0;
    foreach ($items as $i) {
        echo "  {$i['section']}: {$i['cnt']} items\n";
        $total += $i['cnt'];
    }
    echo "  Total: $total items\n";

    // Show what went to 'general' (should be minimal)
    $gen = $pdo->query("SELECT name, url FROM admin_menu_items WHERE section = 'general'")->fetchAll(PDO::FETCH_ASSOC);
    if (count($gen)) {
        echo "\n=== Items still in 'general' ===\n";
        foreach ($gen as $g) echo "  {$g['name']} → {$g['url']}\n";
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
