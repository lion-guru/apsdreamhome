<?php
// Comprehensive Project Analysis
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== APS DREAM HOME - COMPREHENSIVE PROJECT ANALYSIS ===\n\n";

    // 1. Database Tables Analysis
    echo "1. DATABASE TABLES ANALYSIS\n";
    echo str_repeat("-", 50) . "\n";
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Total tables: " . count($tables) . "\n";
    
    // Key tables check
    $keyTables = [
        'users', 'admin_users', 'employees', 'customers',
        'admin_menu_items', 'admin_role_menu_permissions', 'admin_user_menu_permissions',
        'properties', 'leads', 'bookings', 'payments'
    ];
    
    foreach ($keyTables as $table) {
        $exists = in_array($table, $tables);
        echo sprintf("  %-40s %s\n", $table . ":", $exists ? "✅ EXISTS" : "❌ MISSING");
        if ($exists) {
            $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "    Records: $count\n";
        }
    }
    echo "\n";

    // 2. User Roles Analysis
    echo "2. USER ROLES ANALYSIS\n";
    echo str_repeat("-", 50) . "\n";
    
    $roleQueries = [
        'users' => "SELECT DISTINCT role, COUNT(*) as count FROM users WHERE role IS NOT NULL GROUP BY role",
        'admin_users' => "SELECT DISTINCT role, COUNT(*) as count FROM admin_users WHERE role IS NOT NULL GROUP BY role",
        'employees' => "SELECT DISTINCT role, COUNT(*) as count FROM employees WHERE role IS NOT NULL GROUP BY role"
    ];
    
    foreach ($roleQueries as $table => $query) {
        if (in_array($table, $tables)) {
            echo "$table roles:\n";
            $roles = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
            foreach ($roles as $role) {
                echo sprintf("  %-30s %d users\n", $role['role'] . ":", $role['count']);
            }
        }
    }
    echo "\n";

    // 3. RBAC Menu System Analysis
    echo "3. RBAC MENU SYSTEM ANALYSIS\n";
    echo str_repeat("-", 50) . "\n";
    
    if (in_array('admin_menu_items', $tables)) {
        $totalItems = $db->query("SELECT COUNT(*) FROM admin_menu_items")->fetchColumn();
        $activeItems = $db->query("SELECT COUNT(*) FROM admin_menu_items WHERE is_active = 1")->fetchColumn();
        $sections = $db->query("SELECT DISTINCT section, COUNT(*) as count FROM admin_menu_items WHERE is_active = 1 GROUP BY section")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Total menu items: $totalItems\n";
        echo "Active menu items: $activeItems\n";
        echo "Menu sections:\n";
        foreach ($sections as $section) {
            echo sprintf("  %-30s %d items\n", $section['section'] . ":", $section['count']);
        }
        
        // Check role permissions
        if (in_array('admin_role_menu_permissions', $tables)) {
            $rolePerms = $db->query("SELECT DISTINCT role, COUNT(*) as count FROM admin_role_menu_permissions GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);
            echo "\nRole permissions:\n";
            foreach ($rolePerms as $perm) {
                echo sprintf("  %-30s %d permissions\n", $perm['role'] . ":", $perm['count']);
            }
        }
    }
    echo "\n";

    // 4. Layout Files Analysis
    echo "4. LAYOUT FILES ANALYSIS\n";
    echo str_repeat("-", 50) . "\n";
    
    $layoutDirs = [
        'app/views/admin/layouts',
        'app/views/layouts',
        'app/views/customer/layouts',
        'app/views/associate/layouts',
        'app/views/agent/layouts',
        'app/views/employee/layouts'
    ];
    
    foreach ($layoutDirs as $dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            $phpFiles = array_filter($files, function($f) { return pathinfo($f, PATHINFO_EXTENSION) === 'php'; });
            echo sprintf("%-40s %d files\n", $dir . ":", count($phpFiles));
        } else {
            echo sprintf("%-40s ❌ MISSING\n", $dir . ":");
        }
    }
    echo "\n";

    // 5. Partials Analysis
    echo "5. PARTIALS ANALYSIS\n";
    echo str_repeat("-", 50) . "\n";
    
    $partialsDirs = [
        'app/views/admin/partials',
        'app/views/partials',
        'app/views/customer/partials',
        'app/views/associate/partials'
    ];
    
    foreach ($partialsDirs as $dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            $phpFiles = array_filter($files, function($f) { return pathinfo($f, PATHINFO_EXTENSION) === 'php'; });
            echo sprintf("%-40s %d files\n", $dir . ":", count($phpFiles));
            if (count($phpFiles) > 0) {
                foreach ($phpFiles as $file) {
                    echo "  - $file\n";
                }
            }
        } else {
            echo sprintf("%-40s ❌ MISSING (Critical)\n", $dir . ":");
        }
    }
    echo "\n";

    // 6. View Files Consistency Analysis
    echo "6. VIEW FILES CONSISTENCY ANALYSIS\n";
    echo str_repeat("-", 50) . "\n";
    
    $viewDirs = [
        'app/views/admin' => 'Admin',
        'app/views/customer' => 'Customer', 
        'app/views/associate' => 'Associate',
        'app/views/agent' => 'Agent',
        'app/views/employee' => 'Employee'
    ];
    
    foreach ($viewDirs as $dir => $name) {
        if (is_dir($dir)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            $phpFiles = 0;
            $standaloneFiles = 0;
            $layoutFiles = 0;
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $phpFiles++;
                    $content = file_get_contents($file->getPathname());
                    
                    // Check if file uses layout
                    if (strpos($content, 'include.*layout') !== false || strpos($content, '$layout') !== false) {
                        $layoutFiles++;
                    } elseif (strpos($content, '<!DOCTYPE html>') !== false || strpos($content, '<html') !== false) {
                        $standaloneFiles++;
                    }
                }
            }
            
            echo "$name views:\n";
            echo "  Total PHP files: $phpFiles\n";
            echo "  Uses layout system: $layoutFiles\n";
            echo "  Standalone HTML files: $standaloneFiles\n";
            
            if ($standaloneFiles > $layoutFiles) {
                echo "  ⚠️  WARNING: More standalone files than layout-based files\n";
            }
        }
    }
    echo "\n";

    // 7. Routes Analysis
    echo "7. ROUTES ANALYSIS\n";
    echo str_repeat("-", 50) . "\n";
    
    $routeFiles = [
        'routes/web.php',
        'routes/api.php'
    ];
    
    foreach ($routeFiles as $routeFile) {
        if (file_exists($routeFile)) {
            $content = file_get_contents($routeFile);
            $routeCount = substr_count($content, '$router->');
            echo "$routeFile: $routeCount routes\n";
        }
    }
    echo "\n";

    echo "=== ANALYSIS COMPLETE ===\n";
    echo "Review the findings above to identify improvement areas.\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>