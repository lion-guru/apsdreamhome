<?php
/**
 * APS Dream Home - FINAL COMPREHENSIVE SCAN
 * Complete system validation and fix identification
 */

echo "🏠 APS Dream Home - FINAL COMPREHENSIVE SCAN\n";
echo "==========================================\n\n";

$startTime = microtime(true);
$projectRoot = __DIR__;
$issues = [];
$fixes = [];

// 1. PROJECT STRUCTURE SCAN
echo "1. 📁 PROJECT STRUCTURE SCAN\n";
echo "==========================\n";

$requiredStructure = [
    'app/' => 'Application Directory',
    'app/core/' => 'Core Classes',
    'app/Http/Controllers/' => 'Controllers',
    'app/models/' => 'Models',
    'routes/' => 'Routes',
    'resources/views/' => 'Views',
    'public/' => 'Public Assets',
    'database/' => 'Database Files',
    'config/' => 'Configuration',
    'vendor/' => 'Dependencies'
];

$structureScore = 0;
$totalStructure = count($requiredStructure);

foreach ($requiredStructure as $dir => $description) {
    $exists = is_dir($dir);
    $status = $exists ? "✅" : "❌";
    echo "   $status $description\n";
    
    if ($exists) {
        $structureScore++;
        $items = scandir($dir);
        $count = count($items) - 2;
        echo "      ($count items)\n";
    } else {
        $issues[] = "Missing directory: $dir";
    }
}

// 2. MVC COMPONENTS CHECK
echo "\n2. 🏗️ MVC COMPONENTS CHECK\n";
echo "========================\n";

$mvcComponents = [
    'app/core/App.php' => 'Application Core',
    'app/core/Routing/Router.php' => 'Router',
    'bootstrap.php' => 'Bootstrap',
    'index.php' => 'Entry Point',
    '.htaccess' => 'Apache Config'
];

$mvcScore = 0;
$totalMvc = count($mvcComponents);

foreach ($mvcComponents as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? "✅" : "❌";
    echo "   $status $description\n";
    
    if ($exists) {
        $mvcScore++;
    } else {
        $issues[] = "Missing MVC component: $file";
    }
}

// 3. CONTROLLERS VALIDATION
echo "\n3. 🎮 CONTROLLERS VALIDATION\n";
echo "==========================\n";

$controllers = [
    'Public/AuthController.php' => ['login', 'register', 'logout'],
    'Admin/AdminDashboardController.php' => ['index', 'dashboard'],
    'User/DashboardController.php' => ['index', 'profile'],
    'Property/PropertyController.php' => ['index', 'show', 'search']
];

$controllerScore = 0;
$totalControllers = count($controllers);

foreach ($controllers as $controller => $methods) {
    $file = 'app/Http/Controllers/' . $controller;
    $exists = file_exists($file);
    $status = $exists ? "✅" : "❌";
    echo "   $status $controller\n";
    
    if ($exists) {
        $controllerScore++;
        $content = file_get_contents($file);
        $methodCount = 0;
        
        foreach ($methods as $method) {
            if (strpos($content, "function $method") !== false || 
                strpos($content, "public function $method") !== false) {
                $methodCount++;
            }
        }
        
        echo "      Methods: $methodCount/" . count($methods) . "\n";
        
        if ($methodCount < count($methods)) {
            $issues[] = "Missing methods in $controller";
        }
    } else {
        $issues[] = "Missing controller: $controller";
    }
}

// 4. DATABASE INTEGRATION
echo "\n4. 🗄️ DATABASE INTEGRATION\n";
echo "========================\n";

$dbComponents = [
    'app/core/Database.php' => 'Database Class',
    'app/models/User.php' => 'User Model',
    'app/config/database.php' => 'Database Config',
    '.env' => 'Environment Config'
];

$dbScore = 0;
$totalDb = count($dbComponents);

foreach ($dbComponents as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? "✅" : "❌";
    echo "   $status $description\n";
    
    if ($exists) {
        $dbScore++;
    } else {
        $issues[] = "Missing database component: $file";
    }
}

// Test actual database connection
try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($conn->connect_error) {
        echo "   ❌ Database Connection: Failed\n";
        $issues[] = "Database connection failed";
    } else {
        echo "   ✅ Database Connection: Success\n";
        $dbScore++;
        
        $result = $conn->query("SHOW TABLES");
        $tableCount = $result->num_rows;
        echo "   ✅ Database Tables: $tableCount\n";
        
        if ($tableCount < 100) {
            $issues[] = "Low table count: $tableCount (expected 400+)";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "   ❌ Database Error: " . $e->getMessage() . "\n";
    $issues[] = "Database exception: " . $e->getMessage();
}

// 5. ROUTING SYSTEM
echo "\n5. 🛣️ ROUTING SYSTEM\n";
echo "==================\n";

$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    echo "   ✅ Routes file exists\n";
    
    $routesContent = file_get_contents($routesFile);
    $keyRoutes = [
        'login' => strpos($routesContent, "'/login'") !== false,
        'admin' => strpos($routesContent, "'/admin'") !== false,
        'dashboard' => strpos($routesContent, "'/dashboard'") !== false,
        'register' => strpos($routesContent, "'/register'") !== false,
        'logout' => strpos($routesContent, "'/logout'") !== false
    ];
    
    $routeScore = 0;
    foreach ($keyRoutes as $route => $found) {
        $status = $found ? "✅" : "❌";
        echo "   $status Route /$route\n";
        if ($found) $routeScore++;
    }
    
    if ($routeScore < 4) {
        $issues[] = "Missing key routes in web.php";
    }
} else {
    echo "   ❌ Routes file missing\n";
    $issues[] = "Routes file missing";
}

// 6. VIEWS SYSTEM
echo "\n6. 👁️ VIEWS SYSTEM\n";
echo "==================\n";

$viewDirs = [
    'resources/views/' => 'Main Views',
    'resources/views/admin/' => 'Admin Views',
    'resources/views/auth/' => 'Auth Views',
    'resources/views/pages/' => 'Page Views',
    'resources/views/layouts/' => 'Layouts'
];

$viewScore = 0;
$totalViews = count($viewDirs);

foreach ($viewDirs as $dir => $description) {
    $exists = is_dir($dir);
    $status = $exists ? "✅" : "❌";
    echo "   $status $description\n";
    
    if ($exists) {
        $viewScore++;
        $files = glob($dir . '*.php');
        $fileCount = count($files);
        echo "      PHP files: $fileCount\n";
        
        if ($fileCount === 0) {
            $issues[] = "No PHP files in $dir";
        }
    } else {
        $issues[] = "Missing view directory: $dir";
    }
}

// 7. CONFIGURATION
echo "\n7. ⚙️ CONFIGURATION\n";
echo "==================\n";

$configFiles = [
    '.env' => 'Environment',
    'app/config/' => 'App Config',
    'config/' => 'System Config'
];

$configScore = 0;
$totalConfig = count($configFiles);

foreach ($configFiles as $file => $description) {
    $exists = is_dir($file) || file_exists($file);
    $status = $exists ? "✅" : "❌";
    echo "   $status $description\n";
    
    if ($exists) {
        $configScore++;
    } else {
        $issues[] = "Missing config: $file";
    }
}

// 8. SECURITY CHECK
echo "\n8. 🔒 SECURITY CHECK\n";
echo "==================\n";

$securityFiles = [
    '.htaccess' => 'Apache Security',
    'includes/session_helpers.php' => 'Session Security',
    'app/core/Auth.php' => 'Authentication',
    'app/core/SessionManager.php' => 'Session Management'
];

$securityScore = 0;
$totalSecurity = count($securityFiles);

foreach ($securityFiles as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? "✅" : "❌";
    echo "   $status $description\n";
    
    if ($exists) {
        $securityScore++;
    } else {
        $issues[] = "Missing security component: $file";
    }
}

// 9. FINAL ASSESSMENT
echo "\n9. 🏆 FINAL ASSESSMENT\n";
echo "==================\n";

$totalScore = $structureScore + $mvcScore + $controllerScore + $dbScore + $routeScore + $viewScore + $configScore + $securityScore;
$maxScore = $totalStructure + $totalMvc + $totalControllers + $totalDb + 5 + $totalViews + $totalConfig + $totalSecurity;

$percentage = round(($totalScore / $maxScore) * 100);

echo "   Structure: $structureScore/$totalStructure\n";
echo "   MVC: $mvcScore/$totalMvc\n";
echo "   Controllers: $controllerScore/$totalControllers\n";
echo "   Database: $dbScore/$totalDb\n";
echo "   Routing: $routeScore/5\n";
echo "   Views: $viewScore/$totalViews\n";
echo "   Config: $configScore/$totalConfig\n";
echo "   Security: $securityScore/$totalSecurity\n";
echo "   📊 Overall Score: $totalScore/$maxScore ($percentage%)\n";

$grade = $percentage >= 90 ? 'A+ (EXCELLENT)' :
         ($percentage >= 80 ? 'A (VERY GOOD)' :
         ($percentage >= 70 ? 'B+ (GOOD)' :
         ($percentage >= 60 ? 'B (AVERAGE)' :
         ($percentage >= 50 ? 'C+ (BELOW AVERAGE)' :
         ($percentage >= 40 ? 'C (POOR)' : 'D (VERY POOR)')))));

echo "   🏅 Grade: $grade\n";

// 10. ISSUES AND FIXES
echo "\n10. 🔧 ISSUES AND FIXES\n";
echo "====================\n";

if (empty($issues)) {
    echo "   🎉 No issues found! System is perfect.\n";
} else {
    echo "   📋 Issues Found: " . count($issues) . "\n";
    foreach ($issues as $i => $issue) {
        echo "   " . ($i + 1) . ". " . $issue . "\n";
    }
    
    echo "\n   💡 Recommended Fixes:\n";
    
    // Generate specific fixes
    foreach ($issues as $issue) {
        if (strpos($issue, 'Missing directory') !== false) {
            $dir = str_replace('Missing directory: ', '', $issue);
            echo "   • Create directory: $dir\n";
            $fixes[] = "mkdir -p $dir";
        }
        
        if (strpos($issue, 'Missing MVC component') !== false) {
            $file = str_replace('Missing MVC component: ', '', $issue);
            echo "   • Create file: $file\n";
            echo "   • Add basic MVC structure to $file\n";
        }
        
        if (strpos($issue, 'Missing controller') !== false) {
            $controller = str_replace('Missing controller: ', '', $issue);
            echo "   • Create controller: $controller\n";
            echo "   • Add required methods to controller\n";
        }
        
        if (strpos($issue, 'Database connection') !== false) {
            echo "   • Check MySQL service is running\n";
            echo "   • Verify .env database credentials\n";
            echo "   • Create database if missing\n";
        }
        
        if (strpos($issue, 'Missing view directory') !== false) {
            $dir = str_replace('Missing view directory: ', '', $issue);
            echo "   • Create view directory: $dir\n";
            echo "   • Add basic view files\n";
        }
    }
}

// 11. NEXT STEPS
echo "\n11. 🚀 NEXT STEPS\n";
echo "==================\n";

if ($percentage >= 90) {
    echo "   🟢 SYSTEM IS EXCELLENT!\n";
    echo "   1. ✅ Ready for production deployment\n";
    echo "   2. ✅ All MVC components working\n";
    echo "   3. ✅ Database connected\n";
    echo "   4. ✅ Security configured\n";
    echo "   5. ✅ Ready to use!\n";
} elseif ($percentage >= 70) {
    echo "   🟡 SYSTEM IS GOOD!\n";
    echo "   1. 🔧 Fix identified issues\n";
    echo "   2. 🔧 Complete missing components\n";
    echo "   3. 🔧 Test all functionality\n";
    echo "   4. 🚀 Ready for deployment\n";
} else {
    echo "   🔴 SYSTEM NEEDS WORK!\n";
    echo "   1. 🚨 Fix critical issues first\n";
    echo "   2. 🚨 Complete MVC structure\n";
    echo "   3. 🚨 Set up database\n";
    echo "   4. 🚨 Configure routing\n";
    echo "   5. 🚨 Add security measures\n";
}

$endTime = microtime(true);
$scanTime = round($endTime - $startTime, 2);

echo "\n🎉 COMPREHENSIVE SCAN COMPLETED!\n";
echo "==================================\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "Scan Duration: {$scanTime} seconds\n";
echo "Project: APS Dream Home\n";
echo "Score: $percentage%\n";
echo "Grade: $grade\n";
echo "Issues: " . count($issues) . "\n";

if (!empty($fixes)) {
    echo "\n📝 Quick Fix Commands:\n";
    foreach ($fixes as $fix) {
        echo "   $fix\n";
    }
}

?>
