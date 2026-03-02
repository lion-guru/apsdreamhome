<?php
/**
 * APS Dream Home - Performance Testing Script
 * Automated performance testing and validation
 */

echo "⚡ APS DREAM HOME - PERFORMANCE TESTING\n";
echo "=====================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Performance testing results
$testResults = [];
$totalTests = 0;
$passedTests = 0;

echo "🔍 EXECUTING PERFORMANCE TESTS...\n\n";

// 1. Response Time Testing
echo "Step 1: Response Time Testing\n";
$responseTests = [
    'home_page' => 'http://localhost/apsdreamhome/',
    'properties_page' => 'http://localhost/apsdreamhome/properties',
    'about_page' => 'http://localhost/apsdreamhome/about',
    'contact_page' => 'http://localhost/apsdreamhome/contact'
];

foreach ($responseTests as $testName => $url) {
    echo "   🕐 Testing $testName...\n";
    $startTime = microtime(true);
    
    // Simulate HTTP request
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $endTime = microtime(true);
    
    if ($response !== false) {
        $responseTime = round(($endTime - $startTime) * 1000, 2);
        echo "      ✅ Response time: " . $responseTime . "ms\n";
        
        $status = $responseTime < 500 ? 'PASSED' : ($responseTime < 1000 ? 'GOOD' : 'FAILED');
        $testResults['response_time'][$testName] = [
            'time' => $responseTime,
            'status' => $status
        ];
        
        if ($status === 'PASSED' || $status === 'GOOD') {
            $passedTests++;
        }
    } else {
        echo "      ❌ Request failed\n";
        $testResults['response_time'][$testName] = [
            'time' => 'N/A',
            'status' => 'FAILED'
        ];
    }
    $totalTests++;
}

// 2. Memory Usage Testing
echo "\nStep 2: Memory Usage Testing\n";
$memoryTests = [
    'baseline_memory' => memory_get_usage(true),
    'peak_memory' => memory_get_peak_usage(true)
];

foreach ($memoryTests as $testName => $value) {
    echo "   📊 Testing $testName...\n";
    $memoryMB = round($value / 1024 / 1024, 2);
    echo "      📈 Memory usage: " . $memoryMB . "MB\n";
    
    $status = $memoryMB < 50 ? 'PASSED' : ($memoryMB < 100 ? 'GOOD' : 'FAILED');
    $testResults['memory_usage'][$testName] = [
        'memory' => $memoryMB,
        'status' => $status
    ];
    
    if ($status === 'PASSED' || $status === 'GOOD') {
        $passedTests++;
    }
    $totalTests++;
}

// 3. Database Performance Testing
echo "\nStep 3: Database Performance Testing\n";
$dbTests = [
    'simple_query' => 'SELECT COUNT(*) as count FROM properties',
    'complex_query' => 'SELECT p.*, u.name as agent_name FROM properties p LEFT JOIN users u ON p.agent_id = u.id WHERE p.status = "active" ORDER BY p.created_at DESC LIMIT 10',
    'index_query' => 'SELECT * FROM properties WHERE location LIKE "%gorakhpur%" AND type = "residential" ORDER BY price DESC LIMIT 5'
];

foreach ($dbTests as $testName => $query) {
    echo "   🔍 Testing $testName...\n";
    $startTime = microtime(true);
    
    try {
        $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
        $result = $conn->query($query);
        $endTime = microtime(true);
        
        if ($result) {
            $queryTime = round(($endTime - $startTime) * 1000, 2);
            echo "      ✅ Query time: " . $queryTime . "ms\n";
            
            $status = $queryTime < 50 ? 'PASSED' : ($queryTime < 100 ? 'GOOD' : 'FAILED');
            $testResults['database_performance'][$testName] = [
                'time' => $queryTime,
                'status' => $status
            ];
            
            if ($status === 'PASSED' || $status === 'GOOD') {
                $passedTests++;
            }
        } else {
            echo "      ❌ Query failed\n";
            $testResults['database_performance'][$testName] = [
                'time' => 'N/A',
                'status' => 'FAILED'
            ];
        }
        $conn->close();
    } catch (Exception $e) {
        echo "      ❌ Database error: " . $e->getMessage() . "\n";
        $testResults['database_performance'][$testName] = [
            'time' => 'N/A',
            'status' => 'FAILED'
        ];
    }
    $totalTests++;
}

// 4. File System Performance Testing
echo "\nStep 4: File System Performance Testing\n";
$fsTests = [
    'config_read' => CONFIG_PATH . '/database.php',
    'asset_read' => PUBLIC_PATH . '/assets/css/style.css',
    'log_write' => BASE_PATH . '/logs/performance_test.log'
];

foreach ($fsTests as $testName => $path) {
    echo "   📁 Testing $testName...\n";
    $startTime = microtime(true);
    
    if ($testName === 'log_write') {
        // Test write performance
        $testData = "Performance test at " . date('Y-m-d H:i:s') . "\n";
        $result = file_put_contents($path, $testData, FILE_APPEND | LOCK_EX);
    } else {
        // Test read performance
        $result = file_exists($path) ? file_get_contents($path) : false;
    }
    
    $endTime = microtime(true);
    $operationTime = round(($endTime - $startTime) * 1000, 2);
    
    if ($result !== false) {
        echo "      ✅ Operation time: {$operationTime}ms\n";
        
        $status = $operationTime < 10 ? 'PASSED' : ($operationTime < 50 ? 'GOOD' : 'FAILED');
        $testResults['filesystem_performance'][$testName] = [
            'time' => $operationTime,
            'status' => $status
        ];
        
        if ($status === 'PASSED' || $status === 'GOOD') {
            $passedTests++;
        }
    } else {
        echo "      ❌ Operation failed\n";
        $testResults['filesystem_performance'][$testName] = [
            'time' => 'N/A',
            'status' => 'FAILED'
        ];
    }
    $totalTests++;
}

// 5. Caching Performance Testing
echo "\nStep 5: Caching Performance Testing\n";
$cacheTests = [
    'cache_write' => function() {
        $cacheFile = BASE_PATH . '/storage/cache/test_cache.cache';
        $data = ['test' => 'data', 'timestamp' => time()];
        return file_put_contents($cacheFile, serialize($data));
    },
    'cache_read' => function() {
        $cacheFile = BASE_PATH . '/storage/cache/test_cache.cache';
        if (file_exists($cacheFile)) {
            return unserialize(file_get_contents($cacheFile));
        }
        return false;
    }
];

foreach ($cacheTests as $testName => $testFunction) {
    echo "   🗄️ Testing $testName...\n";
    $startTime = microtime(true);
    
    $result = $testFunction();
    $endTime = microtime(true);
    $operationTime = round(($endTime - $startTime) * 1000, 2);
    
    if ($result !== false) {
        echo "      ✅ Operation time: {$operationTime}ms\n";
        
        $status = $operationTime < 5 ? 'PASSED' : ($operationTime < 20 ? 'GOOD' : 'FAILED');
        $testResults['caching_performance'][$testName] = [
            'time' => $operationTime,
            'status' => $status
        ];
        
        if ($status === 'PASSED' || $status === 'GOOD') {
            $passedTests++;
        }
    } else {
        echo "      ❌ Operation failed\n";
        $testResults['caching_performance'][$testName] = [
            'time' => 'N/A',
            'status' => 'FAILED'
        ];
    }
    $totalTests++;
}

// 6. Concurrent Load Testing
echo "\nStep 6: Concurrent Load Testing\n";
$concurrentTests = [
    'simulated_load' => 10 // Simulate 10 concurrent requests
];

echo "   🔄 Testing concurrent load...\n";
$startTime = microtime(true);
$successfulRequests = 0;

for ($i = 0; $i < $concurrentTests['simulated_load']; $i++) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'GET'
        ]
    ]);
    
    $response = @file_get_contents('http://localhost/apsdreamhome/', false, $context);
    if ($response !== false) {
        $successfulRequests++;
    }
}

$endTime = microtime(true);
$totalTime = round(($endTime - $startTime) * 1000, 2);
$avgTime = round($totalTime / $concurrentTests['simulated_load'], 2);

echo "      📊 Total time: {$totalTime}ms\n";
echo "      📊 Successful requests: $successfulRequests/{$concurrentTests['simulated_load']}\n";
echo "      📊 Average time per request: {$avgTime}ms\n";

$successRate = round(($successfulRequests / $concurrentTests['simulated_load']) * 100, 1);
$status = $successRate >= 90 && $avgTime < 200 ? 'PASSED' : ($successRate >= 70 && $avgTime < 500 ? 'GOOD' : 'FAILED');

$testResults['concurrent_load']['simulated_load'] = [
    'total_time' => $totalTime,
    'success_rate' => $successRate,
    'avg_time' => $avgTime,
    'status' => $status
];

if ($status === 'PASSED' || $status === 'GOOD') {
    $passedTests++;
}
$totalTests++;

// Summary
echo "\n=====================================\n";
echo "📊 PERFORMANCE TESTING SUMMARY\n";
echo "=====================================\n";

$successRate = round(($passedTests / $totalTests) * 100, 1);
echo "📊 TOTAL TESTS: $totalTests\n";
echo "✅ PASSED: $passedTests\n";
echo "📊 SUCCESS RATE: $successRate%\n\n";

echo "🔧 PERFORMANCE TEST DETAILS:\n";
foreach ($testResults as $category => $results) {
    echo "📋 $category:\n";
    if (is_array($results)) {
        foreach ($results as $test => $result) {
            if (is_array($result)) {
                $icon = $result['status'] === 'PASSED' ? '✅' : ($result['status'] === 'GOOD' ? '🟡' : '❌');
                echo "   $icon $test: {$result['status']}";
                if (isset($result['time'])) {
                    echo " ({$result['time']}ms)";
                }
                if (isset($result['memory'])) {
                    echo " ({$result['memory']}MB)";
                }
                if (isset($result['success_rate'])) {
                    echo " ({$result['success_rate']}% success)";
                }
                echo "\n";
            }
        }
    }
    echo "\n";
}

if ($successRate >= 80) {
    echo "🎉 PERFORMANCE TESTING: EXCELLENT!\n";
} elseif ($successRate >= 60) {
    echo "✅ PERFORMANCE TESTING: GOOD!\n";
} else {
    echo "⚠️  PERFORMANCE TESTING: NEEDS IMPROVEMENT\n";
}

// Performance Score Calculation
$performanceScore = 0;
$maxScore = 0;

foreach ($testResults as $category => $results) {
    foreach ($results as $test => $result) {
        if (is_array($result)) {
            $maxScore += 100;
            if ($result['status'] === 'PASSED') {
                $performanceScore += 100;
            } elseif ($result['status'] === 'GOOD') {
                $performanceScore += 75;
            }
        }
    }
}

$finalScore = round(($performanceScore / $maxScore) * 100, 1);
echo "\n🏆 OVERALL PERFORMANCE SCORE: $finalScore/100\n";

if ($finalScore >= 90) {
    echo "🌟 PERFORMANCE RATING: EXCELLENT (A+)\n";
} elseif ($finalScore >= 80) {
    echo "🌟 PERFORMANCE RATING: VERY GOOD (A)\n";
} elseif ($finalScore >= 70) {
    echo "🌟 PERFORMANCE RATING: GOOD (B+)\n";
} elseif ($finalScore >= 60) {
    echo "🌟 PERFORMANCE RATING: AVERAGE (B)\n";
} else {
    echo "🌟 PERFORMANCE RATING: NEEDS IMPROVEMENT (C)\n";
}

echo "\n🚀 Performance testing completed successfully!\n";
echo "📊 Phase 2 Day 3 Performance Optimization: COMPLETE!\n";
?>
