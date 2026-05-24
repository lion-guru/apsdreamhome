<?php
/**
 * Cache Warmup Script
 * Pre-populates file cache for admin sidebar, header projects, and dashboard stats
 * Run: php tools/cache_warmup.php
 */

$dbHost = '127.0.0.1';
$dbPort = '3307';
$dbName = 'apsdreamhome';
$dbUser = 'root';
$dbPass = '';

echo "=== APS Dream Home Cache Warmup ===\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $cacheDir = __DIR__ . '/../storage/cache';

    // 1. Warm header projects cache
    echo "[1/3] Warming header projects cache...\n";
    try {
        $stmt = $db->query("
            SELECT p.id, p.name, p.slug, p.location, d.name as district_name, s.name as state_name
            FROM projects p
            LEFT JOIN districts d ON p.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE p.status = 'active' OR p.status IS NULL
            ORDER BY p.name
        ");
        $projects = $stmt->fetchAll();
        file_put_contents($cacheDir . '/header_projects.cache', serialize($projects));
        echo "  OK: " . count($projects) . " projects cached\n";
    } catch (\Exception $e) {
        echo "  WARN: header projects - " . $e->getMessage() . "\n";
        file_put_contents($cacheDir . '/header_projects.cache', serialize([]));
    }

    // 2. Warm admin sidebar cache (all roles)
    echo "\n[2/3] Warming admin sidebar menu cache...\n";
    try {
        $stmt = $db->query("SELECT * FROM admin_menu_items WHERE is_active = 1 ORDER BY section, order_index");
        $allItems = $stmt->fetchAll();
        file_put_contents($cacheDir . '/' . md5('admin_sidebar_all') . '.cache', serialize($allItems));
        echo "  OK: " . count($allItems) . " menu items cached (all)\n";

        $roles = ['admin', 'super_admin', 'manager', 'employee', 'associate', 'agent', 'customer'];
        foreach ($roles as $role) {
            // Role-specific caching based on permission_key matching
            $permStmt = $db->prepare("
                SELECT ami.* FROM admin_menu_items ami
                LEFT JOIN role_permissions rp ON ami.permission_key = rp.permission_key
                LEFT JOIN roles r ON rp.role_id = r.id
                WHERE ami.is_active = 1 AND (r.slug = ? OR ami.permission_key IS NULL OR ami.permission_key = '')
                ORDER BY ami.section, ami.order_index
            ");
            $permStmt->execute([$role]);
            $roleItems = $permStmt->fetchAll();
            $cacheKey = 'admin_sidebar_role_' . md5($role);
            file_put_contents($cacheDir . '/' . md5($cacheKey) . '.cache', serialize($roleItems));
            echo "  OK: $role menu cached (" . count($roleItems) . " items)\n";
        }
    } catch (\Exception $e) {
        echo "  WARN: sidebar cache - " . $e->getMessage() . "\n";
    }

    // 3. Warm dashboard stat caches
    echo "\n[3/3] Warming dashboard stat caches...\n";
    $queries = [
        'admin_dash_total_users' => "SELECT COUNT(*) as c FROM users",
        'admin_dash_total_properties' => "SELECT COUNT(*) as c FROM user_properties",
        'admin_dash_total_inquiries' => "SELECT COUNT(*) as c FROM inquiries",
        'admin_dash_total_revenue' => "SELECT COALESCE(SUM(amount), 0) as c FROM payments WHERE status = 'completed'",
        'admin_dash_active_properties' => "SELECT COUNT(*) as c FROM user_properties WHERE status = 'approved'",
        'admin_dash_new_users_today' => "SELECT COUNT(*) as c FROM users WHERE DATE(created_at) = CURDATE()",
        'admin_dash_pending_approvals' => "SELECT COUNT(*) as c FROM user_properties WHERE status = 'pending'",
        'admin_api_total_leads' => "SELECT COUNT(*) as c FROM leads",
        'admin_api_total_bookings' => "SELECT COUNT(*) as c FROM bookings",
        'admin_api_total_payments' => "SELECT COUNT(*) as c FROM payments",
        'admin_api_total_plots' => "SELECT COUNT(*) as c FROM plots",
    ];

    foreach ($queries as $key => $sql) {
        try {
            $result = (int) $db->query($sql)->fetch()['c'];
            file_put_contents($cacheDir . '/' . md5($key) . '.cache', serialize($result));
            echo "  OK: $key = $result\n";
        } catch (\Exception $e) {
            echo "  WARN: $key - " . $e->getMessage() . "\n";
            file_put_contents($cacheDir . '/' . md5($key) . '.cache', serialize(0));
        }
    }

    // Verify cache files
    echo "\n=== Verification ===\n";
    $files = glob($cacheDir . '/*.cache');
    echo "Total cache files: " . count($files) . "\n";
    foreach ($files as $f) {
        echo "  " . basename($f) . " (" . number_format(filesize($f)) . " bytes)\n";
    }

    echo "\n=== Cache Warmup Complete ===\n";
    echo "Finished: " . date('Y-m-d H:i:s') . "\n";

} catch (\Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
