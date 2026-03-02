<?php
/**
 * APS Dream Home - Co-Worker Simple Testing Script
 * Execute basic functionality tests without cURL
 */

echo "🧪 APS DREAM HOME - CO-WORKER SIMPLE TESTING\n";
echo "============================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Test results array
$testResults = [];
$totalTests = 5;
$passedTests = 0;

echo "Test 1: Database Connectivity\n";
try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($conn->connect_error) {
        echo "❌ Database Connection: FAILED - " . $conn->connect_error . "\n";
        $testResults['database'] = 'FAILED';
    } else {
        echo "✅ Database Connection: SUCCESS\n";
        $testResults['database'] = 'PASSED';
        $passedTests++;
        
        // Get table count
        $result = $conn->query('SHOW TABLES');
        $tableCount = $result->num_rows;
        echo "   📊 Found $tableCount tables\n";
        
        // Get property count
        $result = $conn->query('SELECT COUNT(*) as count FROM properties');
        $row = $result->fetch_assoc();
        echo "   📊 Found " . $row['count'] . " properties\n";
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ Database Connection: ERROR - " . $e->getMessage() . "\n";
    $testResults['database'] = 'ERROR';
}

echo "\nTest 2: File System Access\n";
try {
    $paths = [
        'BASE_PATH' => BASE_PATH,
        'APP_PATH' => APP_PATH,
        'PUBLIC_PATH' => PUBLIC_PATH,
        'CONFIG_PATH' => CONFIG_PATH,
        'VENDOR_PATH' => VENDOR_PATH
    ];
    
    $allAccessible = true;
    foreach ($paths as $name => $path) {
        if (is_dir($path)) {
            echo "   ✅ $name: Accessible\n";
        } else {
            echo "   ❌ $name: Not accessible\n";
            $allAccessible = false;
        }
    }
    
    if ($allAccessible) {
        echo "✅ File System Access: SUCCESS\n";
        $testResults['filesystem'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ File System Access: FAILED\n";
        $testResults['filesystem'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ File System Access: ERROR - " . $e->getMessage() . "\n";
    $testResults['filesystem'] = 'ERROR';
}

echo "\nTest 3: Configuration Files\n";
try {
    $configFiles = [
        'database.php' => CONFIG_PATH . '/database.php',
        'app.php' => CONFIG_PATH . '/app.php',
        'autoload.php' => APP_PATH . '/core/autoload.php',
        'App.php' => APP_PATH . '/core/App.php'
    ];
    
    $allExists = true;
    foreach ($configFiles as $name => $file) {
        if (file_exists($file)) {
            echo "   ✅ $name: Exists\n";
        } else {
            echo "   ❌ $name: Not found\n";
            $allExists = false;
        }
    }
    
    if ($allExists) {
        echo "✅ Configuration Files: SUCCESS\n";
        $testResults['config'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ Configuration Files: FAILED\n";
        $testResults['config'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Configuration Files: ERROR - " . $e->getMessage() . "\n";
    $testResults['config'] = 'ERROR';
}

echo "\nTest 4: PHP Extensions\n";
try {
    $extensions = ['mysqli', 'gd', 'curl', 'json', 'mbstring', 'openssl'];
    $loadedExtensions = [];
    $missingExtensions = [];
    
    foreach ($extensions as $ext) {
        if (extension_loaded($ext)) {
            $loadedExtensions[] = $ext;
            echo "   ✅ $ext: Loaded\n";
        } else {
            $missingExtensions[] = $ext;
            echo "   ❌ $ext: Not loaded\n";
        }
    }
    
    $loadedCount = count($loadedExtensions);
    $totalCount = count($extensions);
    $loadRate = round(($loadedCount / $totalCount) * 100, 1);
    
    echo "   📊 Extension Load Rate: $loadRate%\n";
    
    if ($loadRate >= 80) {
        echo "✅ PHP Extensions: SUCCESS\n";
        $testResults['extensions'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ PHP Extensions: FAILED\n";
        $testResults['extensions'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ PHP Extensions: ERROR - " . $e->getMessage() . "\n";
    $testResults['extensions'] = 'ERROR';
}

echo "\nTest 5: Application Bootstrap\n";
try {
    // Try to load the autoloader
    if (file_exists(APP_PATH . '/core/autoload.php')) {
        require_once APP_PATH . '/core/autoload.php';
        echo "   ✅ Autoloader: Loaded\n";
        
        // Try to instantiate the App class
        if (class_exists('App\Core\App')) {
            $app = new App();
            echo "   ✅ App Class: Instantiated\n";
            
            // Try to run the app
            if (method_exists($app, 'run')) {
                echo "   ✅ App Run: Method exists\n";
                echo "✅ Application Bootstrap: SUCCESS\n";
                $testResults['bootstrap'] = 'PASSED';
                $passedTests++;
            } else {
                echo "   ❌ App Run: Method not found\n";
                echo "❌ Application Bootstrap: FAILED\n";
                $testResults['bootstrap'] = 'FAILED';
            }
        } else {
            echo "   ❌ App Class: Not found\n";
            echo "❌ Application Bootstrap: FAILED\n";
            $testResults['bootstrap'] = 'FAILED';
        }
    } else {
        echo "   ❌ Autoloader: Not found\n";
        echo "❌ Application Bootstrap: FAILED\n";
        $testResults['bootstrap'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Application Bootstrap: ERROR - " . $e->getMessage() . "\n";
    $testResults['bootstrap'] = 'ERROR';
} catch (Error $e) {
    echo "❌ Application Bootstrap: FATAL ERROR - " . $e->getMessage() . "\n";
    $testResults['bootstrap'] = 'ERROR';
}

// Summary
echo "\n============================================\n";
echo "📊 SIMPLE TESTING SUMMARY\n";
echo "============================================\n";

foreach ($testResults as $test => $result) {
    $status = $result === 'PASSED' ? '✅' : ($result === 'FAILED' ? '❌' : '⚠️');
    echo "$status $test: $result\n";
}

$successRate = round(($passedTests / $totalTests) * 100, 1);
echo "\n📊 TOTAL: $passedTests/$totalTests tests passed ($successRate%)\n";

if ($successRate >= 80) {
    echo "🎉 SIMPLE TESTING: EXCELLENT!\n";
} elseif ($successRate >= 60) {
    echo "✅ SIMPLE TESTING: GOOD!\n";
} else {
    echo "⚠️  SIMPLE TESTING: NEEDS IMPROVEMENT\n";
}

echo "\n🚀 Ready to proceed with detailed testing!\n";
?>
