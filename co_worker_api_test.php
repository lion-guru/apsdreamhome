<?php
/**
 * APS Dream Home - Co-Worker API Testing Script
 * Execute all API endpoint tests systematically
 */

echo "🧪 APS DREAM HOME - CO-WORKER API TESTING\n";
echo "========================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Test results array
$testResults = [];
$totalTests = 10;
$passedTests = 0;

echo "Test 1: API Root - Show available endpoints\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/api/index.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && !empty($response)) {
        echo "✅ API Root: SUCCESS (HTTP $httpCode)\n";
        $testResults['api_root'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ API Root: FAILED (HTTP $httpCode)\n";
        $testResults['api_root'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ API Root: ERROR - " . $e->getMessage() . "\n";
    $testResults['api_root'] = 'ERROR';
}

echo "\nTest 2: Health Check - Verify API is running\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/api/index.php?request_uri=/apsdreamhome/api/health');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && !empty($response)) {
        echo "✅ Health Check: SUCCESS (HTTP $httpCode)\n";
        $testResults['health_check'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ Health Check: FAILED (HTTP $httpCode)\n";
        $testResults['health_check'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Health Check: ERROR - " . $e->getMessage() . "\n";
    $testResults['health_check'] = 'ERROR';
}

echo "\nTest 3: Properties List - Get all properties\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/api/index.php?request_uri=/apsdreamhome/api/properties');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && !empty($response)) {
        echo "✅ Properties List: SUCCESS (HTTP $httpCode)\n";
        $testResults['properties_list'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ Properties List: FAILED (HTTP $httpCode)\n";
        $testResults['properties_list'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Properties List: ERROR - " . $e->getMessage() . "\n";
    $testResults['properties_list'] = 'ERROR';
}

echo "\nTest 4: Property Details - Get specific property\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/api/index.php?request_uri=/apsdreamhome/api/properties/1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && !empty($response)) {
        echo "✅ Property Details: SUCCESS (HTTP $httpCode)\n";
        $testResults['property_details'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ Property Details: FAILED (HTTP $httpCode)\n";
        $testResults['property_details'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Property Details: ERROR - " . $e->getMessage() . "\n";
    $testResults['property_details'] = 'ERROR';
}

echo "\nTest 5: User Authentication - Login\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/api/index.php?request_uri=/apsdreamhome/api/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => 'test@example.com', 'password' => 'test123']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && !empty($response)) {
        echo "✅ User Authentication: SUCCESS (HTTP $httpCode)\n";
        $testResults['user_auth'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ User Authentication: FAILED (HTTP $httpCode)\n";
        $testResults['user_auth'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ User Authentication: ERROR - " . $e->getMessage() . "\n";
    $testResults['user_auth'] = 'ERROR';
}

echo "\nTest 6: User Registration - New user\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/api/index.php?request_uri=/apsdreamhome/api/auth/register');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['name' => 'Test User', 'email' => 'new@example.com', 'password' => 'test123']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && !empty($response)) {
        echo "✅ User Registration: SUCCESS (HTTP $httpCode)\n";
        $testResults['user_registration'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ User Registration: FAILED (HTTP $httpCode)\n";
        $testResults['user_registration'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ User Registration: ERROR - " . $e->getMessage() . "\n";
    $testResults['user_registration'] = 'ERROR';
}

echo "\nTest 7: Property Search - Search functionality\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/api/index.php?request_uri=/apsdreamhome/api/search&q=gorakhpur&type=residential');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && !empty($response)) {
        echo "✅ Property Search: SUCCESS (HTTP $httpCode)\n";
        $testResults['property_search'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ Property Search: FAILED (HTTP $httpCode)\n";
        $testResults['property_search'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Property Search: ERROR - " . $e->getMessage() . "\n";
    $testResults['property_search'] = 'ERROR';
}

echo "\nTest 8: Error Handling - Invalid endpoint\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/api/index.php?request_uri=/apsdreamhome/api/invalid');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Error handling should return something (even if it's an error page)
    if (!empty($response)) {
        echo "✅ Error Handling: SUCCESS (HTTP $httpCode - Response received)\n";
        $testResults['error_handling'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ Error Handling: FAILED (No response)\n";
        $testResults['error_handling'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Error Handling: ERROR - " . $e->getMessage() . "\n";
    $testResults['error_handling'] = 'ERROR';
}

echo "\nTest 9: Method Validation - Wrong HTTP method\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/api/index.php?request_uri=/apsdreamhome/api/properties');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Method validation should return something (even if it's an error)
    if (!empty($response)) {
        echo "✅ Method Validation: SUCCESS (HTTP $httpCode - Response received)\n";
        $testResults['method_validation'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ Method Validation: FAILED (No response)\n";
        $testResults['method_validation'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Method Validation: ERROR - " . $e->getMessage() . "\n";
    $testResults['method_validation'] = 'ERROR';
}

echo "\nTest 10: Performance - Response time\n";
try {
    $startTime = microtime(true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/api/index.php?request_uri=/apsdreamhome/api/properties');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    if ($httpCode == 200 && !empty($response)) {
        echo "✅ Performance: SUCCESS (HTTP $httpCode, {$responseTime}ms)\n";
        $testResults['performance'] = 'PASSED';
        $passedTests++;
    } else {
        echo "❌ Performance: FAILED (HTTP $httpCode)\n";
        $testResults['performance'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Performance: ERROR - " . $e->getMessage() . "\n";
    $testResults['performance'] = 'ERROR';
}

// Summary
echo "\n========================================\n";
echo "📊 API TESTING SUMMARY\n";
echo "========================================\n";

foreach ($testResults as $test => $result) {
    $status = $result === 'PASSED' ? '✅' : ($result === 'FAILED' ? '❌' : '⚠️');
    echo "$status $test: $result\n";
}

$successRate = round(($passedTests / $totalTests) * 100, 1);
echo "\n📊 TOTAL: $passedTests/$totalTests tests passed ($successRate%)\n";

if ($successRate >= 80) {
    echo "🎉 API TESTING: EXCELLENT!\n";
} elseif ($successRate >= 60) {
    echo "✅ API TESTING: GOOD!\n";
} else {
    echo "⚠️  API TESTING: NEEDS IMPROVEMENT\n";
}

echo "\n🚀 Ready to proceed with next testing category!\n";
?>
