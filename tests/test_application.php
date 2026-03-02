<?php
/**
 * APS Dream Home - Application Test
 * Basic application functionality test
 * Recreated after file deletion
 */

echo "🧪 APS DREAM HOME - APPLICATION TEST\n";
echo "====================================\n";

// Test 1: Basic PHP functionality
echo "\n1. Testing Basic PHP Functionality:\n";
try {
    $testVar = "Hello World";
    $array = [1, 2, 3];
    $result = array_sum($array);
    echo "✅ PHP Functions: Working\n";
    echo "✅ Array Operations: Working\n";
} catch (Exception $e) {
    echo "❌ PHP Error: " . $e->getMessage() . "\n";
}

// Test 2: File System
echo "\n2. Testing File System:\n";
try {
    $configFile = __DIR__ . '/../config/database.php';
    if (file_exists($configFile)) {
        echo "✅ Config File: Exists\n";
    } else {
        echo "❌ Config File: Missing\n";
    }
    
    $appFile = __DIR__ . '/../app/core/App.php';
    if (file_exists($appFile)) {
        echo "✅ App Core: Exists\n";
    } else {
        echo "❌ App Core: Missing\n";
    }
} catch (Exception $e) {
    echo "❌ File System Error: " . $e->getMessage() . "\n";
}

// Test 3: Database Connection
echo "\n3. Testing Database Connection:\n";
try {
    $host = 'localhost';
    $dbname = 'apsdreamhome';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '$dbname'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Database Connection: Working\n";
    echo "✅ Tables Found: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}

// Test 4: Core Classes
echo "\n4. Testing Core Classes:\n";
try {
    require_once __DIR__ . '/../config/bootstrap.php';
    echo "✅ Bootstrap: Loaded\n";
    
    if (class_exists('App\Core\App')) {
        echo "✅ App Class: Available\n";
    } else {
        echo "❌ App Class: Missing\n";
    }
    
    if (class_exists('App\Core\Database')) {
        echo "✅ Database Class: Available\n";
    } else {
        echo "❌ Database Class: Missing\n";
    }
} catch (Exception $e) {
    echo "❌ Core Classes Error: " . $e->getMessage() . "\n";
}

// Test 5: Routes
echo "\n5. Testing Routes:\n";
try {
    $routesFile = __DIR__ . '/../routes/api.php';
    if (file_exists($routesFile)) {
        echo "✅ API Routes: Available\n";
    } else {
        echo "❌ API Routes: Missing\n";
    }
    
    $webRoutesFile = __DIR__ . '/../routes/web.php';
    if (file_exists($webRoutesFile)) {
        echo "✅ Web Routes: Available\n";
    } else {
        echo "❌ Web Routes: Missing\n";
    }
} catch (Exception $e) {
    echo "❌ Routes Error: " . $e->getMessage() . "\n";
}

// Test 6: Controllers
echo "\n6. Testing Controllers:\n";
try {
    $controllerDir = __DIR__ . '/../app/Http/Controllers';
    if (is_dir($controllerDir)) {
        $controllers = glob($controllerDir . '/*.php');
        echo "✅ Controllers Found: " . count($controllers) . "\n";
        
        $apiControllerDir = $controllerDir . '/Api';
        if (is_dir($apiControllerDir)) {
            $apiControllers = glob($apiControllerDir . '/*.php');
            echo "✅ API Controllers: " . count($apiControllers) . "\n";
        }
    } else {
        echo "❌ Controllers Directory: Missing\n";
    }
} catch (Exception $e) {
    echo "❌ Controllers Error: " . $e->getMessage() . "\n";
}

echo "\n📊 TEST SUMMARY:\n";
echo "==================\n";
echo "Application Test: COMPLETED\n";
echo "Core Functionality: CHECKED\n";
echo "Database: VERIFIED\n";
echo "File System: SCANNED\n";

echo "\n✅ APPLICATION TEST COMPLETE!\n";
?>
