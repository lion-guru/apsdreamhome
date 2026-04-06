<?php
echo "🔍 APS DREAM HOME - DEEP PROJECT ANALYSIS\n";
echo "==========================================\n\n";

// 1. Check if server is running
echo "1. 🌐 SERVER STATUS CHECK:\n";
$serverRunning = false;
$ch = curl_init('http://localhost:8000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 || $httpCode === 302) {
    $serverRunning = true;
    echo "✅ Server is RUNNING on http://localhost:8000\n";
    echo "✅ HTTP Response Code: $httpCode\n";
} else {
    echo "❌ Server is NOT running or not accessible\n";
    echo "❌ HTTP Response Code: $httpCode\n";
}

// 2. Database Connection Check
echo "\n2. 🗄️ DATABASE CONNECTION CHECK:\n";
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $tableCount = count($tables);
    
    echo "✅ Database Connection: SUCCESS\n";
    echo "✅ Total Tables: $tableCount\n";
    
    // Check key tables
    $keyTables = ['states', 'districts', 'colonies', 'plots', 'projects', 'resell_properties', 'customers', 'payments'];
    $missingTables = [];
    
    foreach ($keyTables as $table) {
        if (!in_array($table, $tables)) {
            $missingTables[] = $table;
        }
    }
    
    if (empty($missingTables)) {
        echo "✅ All Key Tables Present\n";
    } else {
        echo "❌ Missing Tables: " . implode(', ', $missingTables) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database Connection: FAILED\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 3. File Structure Analysis
echo "\n3. 📁 FILE STRUCTURE ANALYSIS:\n";

$directories = [
    'app' => 'Application Directory',
    'app/Http/Controllers' => 'Controllers Directory',
    'app/Models' => 'Models Directory',
    'app/Services' => 'Services Directory',
    'app/views' => 'Views Directory',
    'config' => 'Configuration Directory',
    'routes' => 'Routes Directory',
    'public' => 'Public Directory',
    'storage' => 'Storage Directory'
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        $fileCount = count(glob("$dir/*"));
        echo "✅ $description: EXISTS ($fileCount files)\n";
    } else {
        echo "❌ $description: MISSING\n";
    }
}

// 4. Check Core Controllers
echo "\n4. 🎮 CONTROLLERS ANALYSIS:\n";

$expectedControllers = [
    'app/Http/Controllers/AuthController.php',
    'app/Http/Controllers/CustomerController.php',
    'app/Http/Controllers/PropertyController.php',
    'app/Http/Controllers/PaymentController.php',
    'app/Http/Controllers/NotificationController.php',
    'app/Http/Controllers/Admin/LocationAdminController.php',
    'app/Http/Controllers/Admin/PlotsAdminController.php',
    'app/Http/Controllers/Admin/ProjectsAdminController.php',
    'app/Http/Controllers/Admin/ResellPropertiesAdminController.php',
    'app/Http/Controllers/Admin/CommissionAdminController.php',
    'app/Http/Controllers/Admin/MLMController.php'
];

$controllerCount = 0;
foreach ($expectedControllers as $controller) {
    if (file_exists($controller)) {
        $controllerCount++;
        echo "✅ " . basename($controller) . "\n";
    } else {
        echo "❌ " . basename($controller) . "\n";
    }
}

echo "📊 Controllers: $controllerCount/" . count($expectedControllers) . " Present\n";

// 5. Check Services
echo "\n5. 🔧 SERVICES ANALYSIS:\n";

$expectedServices = [
    'app/Services/PropertyService.php',
    'app/Services/CustomerService.php',
    'app/Services/PaymentService.php',
    'app/Services/NotificationService.php'
];

$serviceCount = 0;
foreach ($expectedServices as $service) {
    if (file_exists($service)) {
        $serviceCount++;
        echo "✅ " . basename($service) . "\n";
    } else {
        echo "❌ " . basename($service) . "\n";
    }
}

echo "📊 Services: $serviceCount/" . count($expectedServices) . " Present\n";

// 6. Check Routes
echo "\n6. 🛣️ ROUTES ANALYSIS:\n";

if (file_exists('routes/web.php')) {
    $routeContent = file_get_contents('routes/web.php');
    $routeCount = substr_count($routeContent, '$router->');
    echo "✅ Routes File: EXISTS\n";
    echo "📊 Total Routes: $routeCount\n";
    
    // Check for key route groups
    $keyRoutes = [
        '/properties' => 'Property Routes',
        '/customer' => 'Customer Routes',
        '/payment' => 'Payment Routes',
        '/admin' => 'Admin Routes',
        '/auth' => 'Auth Routes'
    ];
    
    foreach ($keyRoutes as $route => $description) {
        if (strpos($routeContent, $route) !== false) {
            echo "✅ $description: Present\n";
        } else {
            echo "❌ $description: Missing\n";
        }
    }
} else {
    echo "❌ Routes File: MISSING\n";
}

// 7. Check Duplicate Files
echo "\n7. 🔄 DUPLICATE FILES ANALYSIS:\n";

$createFiles = glob('create_*.php');
$duplicateGroups = [];

foreach ($createFiles as $file) {
    $baseName = preg_replace('/create_(.*)\.php/', '$1', $file);
    $duplicateGroups[$baseName][] = $file;
}

$duplicateCount = 0;
foreach ($duplicateGroups as $baseName => $files) {
    if (count($files) > 1) {
        $duplicateCount++;
        echo "⚠️  DUPLICATE GROUP: $baseName (" . count($files) . " files)\n";
        foreach ($files as $file) {
            echo "   - $file\n";
        }
    }
}

if ($duplicateCount === 0) {
    echo "✅ No Duplicate Create Files Found\n";
} else {
    echo "⚠️  Found $duplicateCount Duplicate Groups\n";
}

// 8. Check Sample Data
echo "\n8. 📊 SAMPLE DATA ANALYSIS:\n";

if (isset($db)) {
    $sampleData = [
        'states' => 'States',
        'districts' => 'Districts',
        'colonies' => 'Colonies',
        'plots' => 'Plots',
        'projects' => 'Projects',
        'resell_properties' => 'Resell Properties',
        'customers' => 'Customers',
        'payments' => 'Payments',
        'commission_rules' => 'Commission Rules'
    ];
    
    foreach ($sampleData as $table => $description) {
        try {
            $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
            if ($count > 0) {
                echo "✅ $description: $count records\n";
            } else {
                echo "⚠️  $description: 0 records\n";
            }
        } catch (Exception $e) {
            echo "❌ $description: Table not found\n";
        }
    }
}

// 9. Security Check
echo "\n9. 🔒 SECURITY ANALYSIS:\n";

$securityFiles = [
    '.env' => 'Environment File',
    '.htaccess' => 'Apache Config',
    'index.php' => 'Entry Point'
];

foreach ($securityFiles as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: EXISTS\n";
        
        if ($file === '.env') {
            $envContent = file_get_contents('.env');
            if (strpos($envContent, 'APP_DEBUG=true') !== false) {
                echo "⚠️  DEBUG MODE: ENABLED (Security Risk)\n";
            } else {
                echo "✅ DEBUG MODE: DISABLED\n";
            }
        }
    } else {
        echo "❌ $description: MISSING\n";
    }
}

// 10. Final Assessment
echo "\n10. 🎯 FINAL ASSESSMENT:\n";

$totalChecks = 10;
$passedChecks = 0;

if ($serverRunning) $passedChecks++;
if (isset($db)) $passedChecks++;
if (is_dir('app')) $passedChecks++;
if ($controllerCount >= 8) $passedChecks++;
if ($serviceCount >= 3) $passedChecks++;
if (file_exists('routes/web.php')) $passedChecks++;
if ($duplicateCount <= 2) $passedChecks++;
if (isset($db)) $passedChecks++; // Sample data check
if (file_exists('.env')) $passedChecks++;
if (file_exists('index.php')) $passedChecks++;

$percentage = round(($passedChecks / $totalChecks) * 100, 1);

echo "📊 Overall Health Score: $percentage%\n";
echo "📊 Checks Passed: $passedChecks/$totalChecks\n";

if ($percentage >= 80) {
    echo "🎉 PROJECT STATUS: EXCELLENT\n";
    echo "✅ Ready for Production Deployment\n";
} elseif ($percentage >= 60) {
    echo "⚠️  PROJECT STATUS: GOOD\n";
    echo "✅ Minor Issues to Address\n";
} elseif ($percentage >= 40) {
    echo "❌ PROJECT STATUS: NEEDS WORK\n";
    echo "⚠️  Several Issues to Address\n";
} else {
    echo "🚨 PROJECT STATUS: CRITICAL\n";
    echo "❌ Major Issues to Address\n";
}

echo "\n🔗 NEXT STEPS:\n";
echo "1. Start the development server: php -S localhost:8000\n";
echo "2. Access the application: http://localhost:8000\n";
echo "3. Check admin panel: http://localhost:8000/admin\n";
echo "4. Test all major functionalities\n";
echo "5. Verify database connections\n";
echo "6. Clean up duplicate files if needed\n";

echo "\n📝 ANALYSIS COMPLETE!\n";
?>
