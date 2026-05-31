<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 Checking MLM Associate System Implementation...\n";
    
    // 1. Check existing MLM tables
    echo "\n📊 Checking MLM Tables:\n";
    $mlmTables = [
        'mlm_associates',
        'mlm_plans', 
        'mlm_commission_plans',
        'mlm_commission_levels',
        'mlm_commission_records',
        'mlm_commission_ledger',
        'mlm_network_tree',
        'mlm_referrals',
        'mlm_rank_criteria',
        'mlm_rank_rates',
        'mlm_rank_advancements',
        'mlm_payouts',
        'mlm_withdrawal_requests',
        'salaries'
    ];
    
    foreach ($mlmTables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
            echo "✅ $table: $count records\n";
        } else {
            echo "❌ $table: Missing\n";
        }
    }
    
    // 2. Check Associate Plan Structure
    echo "\n👥 Checking Associate Plan Structure:\n";
    
    if ($db->query("SHOW TABLES LIKE 'mlm_plans'")->rowCount() > 0) {
        $stmt = $db->query("SELECT * FROM mlm_plans LIMIT 5");
        $plans = $stmt->fetchAll();
        
        if (count($plans) > 0) {
            echo "✅ MLM Plans found:\n";
            foreach ($plans as $plan) {
                echo "   - {$plan['name']}: {$plan['type']} ({$plan['commission_rate']}%)\n";
            }
        } else {
            echo "❌ No MLM plans found\n";
        }
    }
    
    // 3. Check Commission Structure
    echo "\n💰 Checking Commission Structure:\n";
    
    if ($db->query("SHOW TABLES LIKE 'mlm_commission_levels'")->rowCount() > 0) {
        $stmt = $db->query("SELECT * FROM mlm_commission_levels ORDER BY level");
        $levels = $stmt->fetchAll();
        
        if (count($levels) > 0) {
            echo "✅ Commission Levels:\n";
            foreach ($levels as $level) {
                echo "   Level {$level['level']}: {$level['name']} - {$level['commission_rate']}%\n";
            }
        } else {
            echo "❌ No commission levels found\n";
        }
    }
    
    // 4. Check Salary Plan
    echo "\n💼 Checking Salary Plan:\n";
    
    if ($db->query("SHOW TABLES LIKE 'salaries'")->rowCount() > 0) {
        $stmt = $db->query("SELECT * FROM salaries LIMIT 3");
        $salaries = $stmt->fetchAll();
        
        if (count($salaries) > 0) {
            echo "✅ Salary Plans found:\n";
            foreach ($salaries as $salary) {
                echo "   - {$salary['role']}: ₹{$salary['base_salary']} + {$salary['commission_percent']}%\n";
            }
        } else {
            echo "❌ No salary plans found\n";
        }
    }
    
    // 5. Check Network Tree
    echo "\n🌳 Checking Network Tree:\n";
    
    if ($db->query("SHOW TABLES LIKE 'mlm_network_tree'")->rowCount() > 0) {
        $count = $db->query("SELECT COUNT(*) as count FROM mlm_network_tree")->fetch()['count'];
        echo "✅ Network Tree: $count relationships\n";
        
        $stmt = $db->query("SELECT parent_id, COUNT(*) as children FROM mlm_network_tree GROUP BY parent_id LIMIT 5");
        $relationships = $stmt->fetchAll();
        
        if (count($relationships) > 0) {
            echo "   Sample relationships:\n";
            foreach ($relationships as $rel) {
                echo "   Parent {$rel['parent_id']}: {$rel['children']} children\n";
            }
        }
    }
    
    // 6. Check Commission Records
    echo "\n📈 Checking Commission Records:\n";
    
    if ($db->query("SHOW TABLES LIKE 'mlm_commission_records'")->rowCount() > 0) {
        $count = $db->query("SELECT COUNT(*) as count FROM mlm_commission_records")->fetch()['count'];
        echo "✅ Commission Records: $count records\n";
        
        $stmt = $db->query("SELECT commission_type, SUM(amount) as total FROM mlm_commission_records GROUP BY commission_type");
        $commissions = $stmt->fetchAll();
        
        if (count($commissions) > 0) {
            echo "   Commission breakdown:\n";
            foreach ($commissions as $comm) {
                echo "   - {$comm['commission_type']}: ₹" . number_format($comm['total']) . "\n";
            }
        }
    }
    
    // 7. Check Associate Registration
    echo "\n📝 Checking Associate Registration:\n";
    
    if ($db->query("SHOW TABLES LIKE 'mlm_associates'")->rowCount() > 0) {
        $count = $db->query("SELECT COUNT(*) as count FROM mlm_associates")->fetch()['count'];
        echo "✅ Associates: $count registered\n";
        
        $stmt = $db->query("SELECT status, COUNT(*) as count FROM mlm_associates GROUP BY status");
        $statusCounts = $stmt->fetchAll();
        
        if (count($statusCounts) > 0) {
            echo "   Status breakdown:\n";
            foreach ($statusCounts as $status) {
                echo "   - {$status['status']}: {$status['count']}\n";
            }
        }
    }
    
    // 8. Check UI Components
    echo "\n🎨 Checking UI Components:\n";
    
    $uiFiles = [
        'app/views/admin/mlm/associates/index.php',
        'app/views/admin/mlm/associates/create.php',
        'app/views/admin/mlm/commission/index.php',
        'app/views/admin/mlm/network/tree.php',
        'app/views/admin/mlm/payouts/index.php'
    ];
    
    foreach ($uiFiles as $file) {
        if (file_exists($file)) {
            echo "✅ $file\n";
        } else {
            echo "❌ $file\n";
        }
    }
    
    // 9. Check Controllers
    echo "\n🎮 Checking Controllers:\n";
    
    $controllerFiles = [
        'app/Http/Controllers/Admin/MLMController.php',
        'app/Http/Controllers/Admin/AssociateController.php',
        'app/Http/Controllers/Admin/CommissionController.php'
    ];
    
    foreach ($controllerFiles as $file) {
        if (file_exists($file)) {
            echo "✅ $file\n";
        } else {
            echo "❌ $file\n";
        }
    }
    
    // 10. Check Routes
    echo "\n🛣️ Checking Routes:\n";
    
    $routesFile = 'routes/web.php';
    if (file_exists($routesFile)) {
        $content = file_get_contents($routesFile);
        
        $mlmRoutes = [
            '/admin/mlm',
            '/admin/associates',
            '/admin/commission',
            '/admin/network',
            '/admin/payouts'
        ];
        
        foreach ($mlmRoutes as $route) {
            if (strpos($content, $route) !== false) {
                echo "✅ $route\n";
            } else {
                echo "❌ $route\n";
            }
        }
    }
    
    echo "\n🎉 MLM System Analysis Complete!\n";
    echo "📊 Review the above results to see what's implemented\n";
    echo "🔧 Missing components will need to be added\n";
    echo "🎯 Focus on: Associate Plan, Salary Structure, Commission Hierarchy\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}
?>
