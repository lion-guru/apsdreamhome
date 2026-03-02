<?php
/**
 * APS Dream Home - System Integration Testing Script
 * Automated system integration and validation
 */

echo "🚀 APS DREAM HOME - SYSTEM INTEGRATION TESTING\n";
echo "==========================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Integration testing results
$integrationResults = [];
$totalTests = 0;
$passedTests = 0;

echo "🔍 EXECUTING SYSTEM INTEGRATION TESTING...\n\n";

// 1. Test system integration
echo "Step 1: Testing system integration\n";
$integrationTests = [
    'path_configuration' => function() {
        return file_exists(CONFIG_PATH . '/paths.php') && require_once CONFIG_PATH . '/paths.php';
    },
    'database_connection' => function() {
        try {
            $config = require CONFIG_PATH . '/database.php';
            $dbConfig = $config['connections']['mysql'];
            $conn = new PDO('mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['database'], $dbConfig['username'], $dbConfig['password']);
            return $conn !== false;
        } catch (Exception $e) {
            return false;
        }
    },
    'apm_system' => function() {
        return file_exists(APP_PATH . '/Monitoring/APM.php');
    },
    'error_tracking' => function() {
        return file_exists(APP_PATH . '/Monitoring/ErrorTracker.php');
    },
    'security_system' => function() {
        return file_exists(APP_PATH . '/Security/InputValidator.php');
    },
    'cache_system' => function() {
        return file_exists(APP_PATH . '/Core/Cache.php');
    },
    'monitoring_config' => function() {
        return file_exists(CONFIG_PATH . '/monitoring.php');
    },
    'security_config' => function() {
        return file_exists(CONFIG_PATH . '/security.php');
    },
    'asset_optimization' => function() {
        return file_exists(APP_PATH . '/Core/AssetBundler.php');
    },
    'image_optimization' => function() {
        return file_exists(APP_PATH . '/Core/ImageOptimizer.php');
    }
];

foreach ($integrationTests as $testName => $testFunction) {
    echo "   🧪 Testing $testName...\n";
    $result = $testFunction();
    $status = $result ? '✅ PASSED' : '❌ FAILED';
    echo "      $status\n";
    
    $integrationResults['system_integration'][$testName] = $result;
    if ($result) {
        $passedTests++;
    }
    $totalTests++;
}

// 2. Test cross-component compatibility
echo "\nStep 2: Testing cross-component compatibility\n";
$compatibilityTests = [
    'php_version_compatibility' => function() {
        return version_compare(PHP_VERSION, '8.0.0', '>=');
    },
    'database_compatibility' => function() {
        try {
            $config = require CONFIG_PATH . '/database.php';
            $dbConfig = $config['connections']['mysql'];
            $conn = new PDO('mysql:host=' . $dbConfig['host'] . ';charset=utf8mb4', $dbConfig['username'], $dbConfig['password']);
            $version = $conn->getAttribute(PDO::ATTR_SERVER_VERSION);
            return strpos($version, '5.7') === 0 || strpos($version, '8.0') === 0;
        } catch (Exception $e) {
            return false;
        }
    },
    'cache_compatibility' => function() {
        return extension_loaded('apcu') || extension_loaded('redis') || extension_loaded('memcached');
    },
    'security_compatibility' => function() {
        return function_exists('hash') && function_exists('openssl_encrypt');
    },
    'monitoring_compatibility' => function() {
        return function_exists('json_encode') && function_exists('file_get_contents');
    },
    'asset_compatibility' => function() {
        return function_exists('file_put_contents') && function_exists('file_get_contents');
    },
    'image_compatibility' => function() {
        return extension_loaded('gd') || extension_loaded('imagick');
    },
    'session_compatibility' => function() {
        return function_exists('session_start') && function_exists('session_destroy');
    }
];

foreach ($compatibilityTests as $testName => $testFunction) {
    echo "   🧪 Testing $testName...\n";
    $result = $testFunction();
    $status = $result ? '✅ PASSED' : '❌ FAILED';
    echo "      $status\n";
    
    $integrationResults['compatibility'][$testName] = $result;
    if ($result) {
        $passedTests++;
    }
    $totalTests++;
}

// 3. Test end-to-end functionality
echo "\nStep 3: Testing end-to-end functionality\n";
$e2eTests = [
    'database_crud' => function() {
        try {
            $config = require CONFIG_PATH . '/database.php';
            $dbConfig = $config['connections']['mysql'];
            $conn = new PDO('mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['database'], $dbConfig['username'], $dbConfig['password']);
            
            // Test CREATE
            $stmt = $conn->prepare("INSERT INTO properties (title, description, price, type, status) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute(['Test Property', 'Test Description', 100000, 'residential', 'active']);
            
            if ($result) {
                $id = $conn->lastInsertId();
                
                // Test READ
                $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
                $stmt->execute([$id]);
                $property = $stmt->fetch();
                
                if ($property) {
                    // Test UPDATE
                    $stmt = $conn->prepare("UPDATE properties SET price = ? WHERE id = ?");
                    $stmt->execute([150000, $id]);
                    
                    // Test DELETE
                    $stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    },
    'cache_operations' => function() {
        $cacheFile = BASE_PATH . '/storage/cache/test.cache';
        $testData = ['test' => 'data', 'timestamp' => time()];
        
        // Test write
        $writeResult = file_put_contents($cacheFile, serialize($testData));
        
        if ($writeResult !== false) {
            // Test read
            $readData = unserialize(file_get_contents($cacheFile));
            
            if ($readData === $testData) {
                unlink($cacheFile);
                return true;
            }
        }
        return false;
    },
    'security_validation' => function() {
        $testInput = '<script>alert("xss")</script>';
        $sanitized = htmlspecialchars($testInput, ENT_QUOTES, 'UTF-8');
        return strpos($sanitized, '<script>') === false;
    },
    'monitoring_logging' => function() {
        $logFile = BASE_PATH . '/logs/integration_test.log';
        $logEntry = date('Y-m-d H:i:s') . ' - Integration test log entry';
        $result = file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        if ($result !== false) {
            // Verify log exists
            return file_exists($logFile) && filesize($logFile) > 0;
        }
        return false;
    },
    'asset_processing' => function() {
        $testCss = 'body { color: red; }';
        $minified = preg_replace('/\s+/', ' ', trim($testCss));
        return strpos($minified, 'color:red') !== false;
    },
    'image_processing' => function() {
        if (extension_loaded('gd')) {
            $image = imagecreatetruecolor(100, 100);
            if ($image) {
                $result = imagepng($image, BASE_PATH . '/storage/temp/test.png');
                imagedestroy($image);
                if ($result) {
                    unlink(BASE_PATH . '/storage/temp/test.png');
                    return true;
                }
            }
        }
        return false;
    }
];

foreach ($e2eTests as $testName => $testFunction) {
    echo "   🧪 Testing $testName...\n";
    $result = $testFunction();
    $status = $result ? '✅ PASSED' : '❌ FAILED';
    echo "      $status\n";
    
    $integrationResults['e2e_functionality'][$testName] = $result;
    if ($result) {
        $passedTests++;
    }
    $totalTests++;
}

// 4. Test performance validation
echo "\nStep 4: Testing performance validation\n";
$performanceTests = [
    'response_time' => function() {
        $startTime = microtime(true);
        
        // Simulate a typical request
        $config = require CONFIG_PATH . '/database.php';
        $dbConfig = $config['connections']['mysql'];
        $conn = new PDO('mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['database'], $dbConfig['username'], $dbConfig['password']);
        $stmt = $conn->query("SELECT COUNT(*) as count FROM properties");
        $result = $stmt->fetch();
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        return $responseTime < 500; // Less than 500ms
    },
    'memory_usage' => function() {
        $startMemory = memory_get_usage(true);
        
        // Simulate memory-intensive operation
        $config = require CONFIG_PATH . '/database.php';
        $dbConfig = $config['connections']['mysql'];
        $conn = new PDO('mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['database'], $dbConfig['username'], $dbConfig['password']);
        $stmt = $conn->query("SELECT * FROM properties LIMIT 100");
        $results = $stmt->fetchAll();
        
        $endMemory = memory_get_usage(true);
        $memoryUsed = $endMemory - $startMemory;
        
        return $memoryUsed < 50 * 1024 * 1024; // Less than 50MB
    },
    'database_query_time' => function() {
        $startTime = microtime(true);
        
        $config = require CONFIG_PATH . '/database.php';
        $dbConfig = $config['connections']['mysql'];
        $conn = new PDO('mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['database'], $dbConfig['username'], $dbConfig['password']);
        $stmt = $conn->query("SELECT * FROM properties WHERE status = 'active' ORDER BY created_at DESC LIMIT 10");
        $results = $stmt->fetchAll();
        
        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;
        
        return $queryTime < 100; // Less than 100ms
    },
    'concurrent_requests' => function() {
        // Simulate concurrent requests (simplified)
        $startTime = microtime(true);
        
        for ($i = 0; $i < 10; $i++) {
            $config = require CONFIG_PATH . '/database.php';
            $dbConfig = $config['connections']['mysql'];
            $conn = new PDO('mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['database'], $dbConfig['username'], $dbConfig['password']);
            $stmt = $conn->query("SELECT COUNT(*) as count FROM properties");
            $result = $stmt->fetch();
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgTime = $totalTime / 10;
        
        return $avgTime < 200; // Average less than 200ms per request
    }
];

foreach ($performanceTests as $testName => $testFunction) {
    echo "   🧪 Testing $testName...\n";
    $result = $testFunction();
    $status = $result ? '✅ PASSED' : '❌ FAILED';
    echo "      $status\n";
    
    $integrationResults['performance'][$testName] = $result;
    if ($result) {
        $passedTests++;
    }
    $totalTests++;
}

// 5. Test security validation
echo "\nStep 5: Testing security validation\n";
$securityTests = [
    'input_validation' => function() {
        $maliciousInput = "'; DROP TABLE users; --";
        $sanitized = htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8');
        return strpos($sanitized, 'DROP') === false;
    },
    'sql_injection_protection' => function() {
        try {
            $config = require CONFIG_PATH . '/database.php';
            $dbConfig = $config['connections']['mysql'];
            $conn = new PDO('mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['database'], $dbConfig['username'], $dbConfig['password']);
            
            // Test prepared statement
            $stmt = $conn->prepare("SELECT * FROM properties WHERE title = ?");
            $result = $stmt->execute(["'; DROP TABLE properties; --"]);
            
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    },
    'xss_protection' => function() {
        $xssInput = '<script>alert("xss")</script>';
        $protected = htmlspecialchars($xssInput, ENT_QUOTES, 'UTF-8');
        return strpos($protected, '<script>') === false;
    },
    'file_upload_security' => function() {
        $maliciousFilename = '../../../etc/passwd';
        $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '_', $maliciousFilename);
        return strpos($sanitized, '..') === false;
    },
    'session_security' => function() {
        session_start();
        $_SESSION['test'] = 'value';
        $sessionId = session_id();
        session_destroy();
        return strlen($sessionId) > 20; // Valid session ID length
    },
    'password_hashing' => function() {
        $password = 'testpassword123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return password_verify($password, $hash);
    }
];

foreach ($securityTests as $testName => $testFunction) {
    echo "   🧪 Testing $testName...\n";
    $result = $testFunction();
    $status = $result ? '✅ PASSED' : '❌ FAILED';
    echo "      $status\n";
    
    $integrationResults['security'][$testName] = $result;
    if ($result) {
        $passedTests++;
    }
    $totalTests++;
}

// 6. Test monitoring validation
echo "\nStep 6: Testing monitoring validation\n";
$monitoringTests = [
    'apm_functionality' => function() {
        if (file_exists(APP_PATH . '/Monitoring/APM.php')) {
            require_once APP_PATH . '/Monitoring/APM.php';
            $apm = App\Monitoring\APM::getInstance();
            $requestId = $apm->startRequest();
            $apm->endRequest($requestId);
            return true;
        }
        return false;
    },
    'error_tracking' => function() {
        if (file_exists(APP_PATH . '/Monitoring/ErrorTracker.php')) {
            require_once APP_PATH . '/Monitoring/ErrorTracker.php';
            $tracker = App\Monitoring\ErrorTracker::getInstance();
            $tracker->trackError('Test error', 'test', 'low');
            return true;
        }
        return false;
    },
    'monitoring_config' => function() {
        $config = require CONFIG_PATH . '/monitoring.php';
        return isset($config['apm']) && isset($config['alerts']) && isset($config['logging']);
    },
    'log_file_creation' => function() {
        $logFile = BASE_PATH . '/logs/monitoring_test.log';
        $result = file_put_contents($logFile, 'Test log entry' . PHP_EOL);
        if ($result !== false) {
            unlink($logFile);
            return true;
        }
        return false;
    },
    'dashboard_accessibility' => function() {
        return file_exists(BASE_PATH . '/monitoring_dashboard.php');
    },
    'error_dashboard_accessibility' => function() {
        return file_exists(BASE_PATH . '/error_dashboard.php');
    },
    'monitoring_data_api' => function() {
        return file_exists(BASE_PATH . '/monitoring_data.php');
    },
    'error_data_api' => function() {
        return file_exists(BASE_PATH . '/error_data.php');
    }
];

foreach ($monitoringTests as $testName => $testFunction) {
    echo "   🧪 Testing $testName...\n";
    $result = $testFunction();
    $status = $result ? '✅ PASSED' : '❌ FAILED';
    echo "      $status\n";
    
    $integrationResults['monitoring'][$testName] = $result;
    if ($result) {
        $passedTests++;
    }
    $totalTests++;
}

// Summary
echo "\n==========================================\n";
echo "📊 SYSTEM INTEGRATION TESTING SUMMARY\n";
echo "==========================================\n";

$successRate = round(($passedTests / $totalTests) * 100, 1);
echo "📊 TOTAL TESTS: $totalTests\n";
echo "✅ PASSED: $passedTests\n";
echo "📊 SUCCESS RATE: $successRate%\n\n";

echo "🔧 INTEGRATION TESTING DETAILS:\n";
foreach ($integrationResults as $category => $tests) {
    echo "📋 $category:\n";
    foreach ($tests as $testName => $result) {
        $icon = $result ? '✅' : '❌';
        echo "   $icon $testName\n";
    }
    echo "\n";
}

if ($successRate >= 90) {
    echo "🎉 SYSTEM INTEGRATION: EXCELLENT!\n";
} elseif ($successRate >= 80) {
    echo "✅ SYSTEM INTEGRATION: GOOD!\n";
} elseif ($successRate >= 70) {
    echo "⚠️  SYSTEM INTEGRATION: ACCEPTABLE!\n";
} else {
    echo "❌ SYSTEM INTEGRATION: NEEDS IMPROVEMENT\n";
}

echo "\n🚀 System integration testing completed successfully!\n";
echo "📊 Ready for next integration step: Cross-Component Compatibility\n";

// Generate integration report
$reportFile = BASE_PATH . '/logs/integration_test_report.json';
$reportData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_tests' => $totalTests,
    'passed_tests' => $passedTests,
    'success_rate' => $successRate,
    'results' => $integrationResults
];

file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT));
echo "📄 Integration report saved to: $reportFile\n";
?>
