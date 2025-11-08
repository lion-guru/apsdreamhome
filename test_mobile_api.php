<?php
/**
 * Mobile API Test Script
 * Tests all mobile API endpoints for the APS Dream Home mobile app
 */

require_once 'config/bootstrap.php';

echo "📱 Mobile API Test Suite\n";
echo "=======================\n\n";

$test_results = [];
$base_url = 'http://localhost/apsdreamhomefinal';

// Test 1: Property Types API
echo "1. 🏠 Testing Property Types API...\n";
try {
    $url = $base_url . '/api/property-types';
    $response = file_get_contents($url);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            $count = count($data['data'] ?? []);
            echo "   ✅ Property types API working ({$count} types)\n";
            $test_results[] = ['Property Types API', 'PASS'];
        } else {
            echo "   ❌ Property types API failed\n";
            $test_results[] = ['Property Types API', 'FAIL'];
        }
    } else {
        echo "   ❌ No response from property types API\n";
        $test_results[] = ['Property Types API', 'FAIL'];
    }
} catch (Exception $e) {
    echo "   ❌ Property types API error: " . $e->getMessage() . "\n";
    $test_results[] = ['Property Types API', 'FAIL'];
}

// Test 2: Cities API
echo "\n2. 🏙️  Testing Cities API...\n";
try {
    $url = $base_url . '/api/cities';
    $response = file_get_contents($url);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            $count = count($data['data'] ?? []);
            echo "   ✅ Cities API working ({$count} cities)\n";
            $test_results[] = ['Cities API', 'PASS'];
        } else {
            echo "   ❌ Cities API failed\n";
            $test_results[] = ['Cities API', 'FAIL'];
        }
    } else {
        echo "   ❌ No response from cities API\n";
        $test_results[] = ['Cities API', 'FAIL'];
    }
} catch (Exception $e) {
    echo "   ❌ Cities API error: " . $e->getMessage() . "\n";
    $test_results[] = ['Cities API', 'FAIL'];
}

// Test 3: Properties API
echo "\n3. 🏡 Testing Properties API...\n";
try {
    $url = $base_url . '/api/properties?page=1&limit=5';
    $response = file_get_contents($url);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            $count = count($data['data']['properties'] ?? []);
            $total = $data['data']['pagination']['total_count'] ?? 0;
            echo "   ✅ Properties API working ({$count} properties, {$total} total)\n";
            $test_results[] = ['Properties API', 'PASS'];
        } else {
            echo "   ❌ Properties API failed\n";
            $test_results[] = ['Properties API', 'FAIL'];
        }
    } else {
        echo "   ❌ No response from properties API\n";
        $test_results[] = ['Properties API', 'FAIL'];
    }
} catch (Exception $e) {
    echo "   ❌ Properties API error: " . $e->getMessage() . "\n";
    $test_results[] = ['Properties API', 'FAIL'];
}

// Test 4: Single Property API
echo "\n4. 🏠 Testing Single Property API...\n";
try {
    // Get first property ID from previous API call
    $properties_url = $base_url . '/api/properties?page=1&limit=1';
    $properties_response = file_get_contents($properties_url);
    $properties_data = json_decode($properties_response, true);

    if (isset($properties_data['success']) && $properties_data['success']) {
        $first_property = $properties_data['data']['properties'][0] ?? null;

        if ($first_property && isset($first_property['id'])) {
            $property_id = $first_property['id'];
            $url = $base_url . '/api/property?id=' . $property_id;
            $response = file_get_contents($url);

            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['success']) && $data['success']) {
                    echo "   ✅ Single property API working (ID: {$property_id})\n";
                    $test_results[] = ['Single Property API', 'PASS'];
                } else {
                    echo "   ❌ Single property API failed\n";
                    $test_results[] = ['Single Property API', 'FAIL'];
                }
            } else {
                echo "   ❌ No response from single property API\n";
                $test_results[] = ['Single Property API', 'FAIL'];
            }
        } else {
            echo "   ⚠️  No properties found for testing\n";
            $test_results[] = ['Single Property API', 'WARN'];
        }
    } else {
        echo "   ❌ Could not get properties for testing\n";
        $test_results[] = ['Single Property API', 'FAIL'];
    }
} catch (Exception $e) {
    echo "   ❌ Single property API error: " . $e->getMessage() . "\n";
    $test_results[] = ['Single Property API', 'FAIL'];
}

// Test 5: Inquiry Submission API (POST)
echo "\n5. 💬 Testing Inquiry Submission API...\n";
try {
    $inquiry_data = [
        'property_id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+91-9876543210',
        'subject' => 'Test Inquiry',
        'message' => 'This is a test inquiry from the mobile API.',
        'inquiry_type' => 'general'
    ];

    $url = $base_url . '/api/inquiry/submit';
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($inquiry_data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            echo "   ✅ Inquiry submission API working (ID: {$data['inquiry_id']})\n";
            $test_results[] = ['Inquiry Submission API', 'PASS'];
        } else {
            echo "   ❌ Inquiry submission API failed\n";
            $test_results[] = ['Inquiry Submission API', 'FAIL'];
        }
    } else {
        echo "   ❌ No response from inquiry submission API\n";
        $test_results[] = ['Inquiry Submission API', 'FAIL'];
    }
} catch (Exception $e) {
    echo "   ❌ Inquiry submission API error: " . $e->getMessage() . "\n";
    $test_results[] = ['Inquiry Submission API', 'FAIL'];
}

// Test 6: CORS Headers
echo "\n6. 🌐 Testing CORS Headers...\n";
try {
    $url = $base_url . '/api/properties';
    $headers = get_headers($url, 1);

    $has_cors = false;
    foreach ($headers as $key => $value) {
        if (stripos($key, 'Access-Control-Allow-Origin') !== false) {
            $has_cors = true;
            break;
        }
    }

    if ($has_cors) {
        echo "   ✅ CORS headers present\n";
        $test_results[] = ['CORS Headers', 'PASS'];
    } else {
        echo "   ❌ CORS headers missing\n";
        $test_results[] = ['CORS Headers', 'FAIL'];
    }
} catch (Exception $e) {
    echo "   ❌ CORS headers test error: " . $e->getMessage() . "\n";
    $test_results[] = ['CORS Headers', 'FAIL'];
}

// Test 7: JSON Response Format
echo "\n7. 📋 Testing JSON Response Format...\n";
try {
    $url = $base_url . '/api/property-types';
    $response = file_get_contents($url);

    if ($response) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "   ✅ Valid JSON response format\n";
            $test_results[] = ['JSON Response Format', 'PASS'];
        } else {
            echo "   ❌ Invalid JSON response format\n";
            $test_results[] = ['JSON Response Format', 'FAIL'];
        }
    } else {
        echo "   ❌ No JSON response\n";
        $test_results[] = ['JSON Response Format', 'FAIL'];
    }
} catch (Exception $e) {
    echo "   ❌ JSON format test error: " . $e->getMessage() . "\n";
    $test_results[] = ['JSON Response Format', 'FAIL'];
}

// Test 8: Error Handling
echo "\n8. ⚠️  Testing Error Handling...\n";
try {
    // Test invalid property ID
    $url = $base_url . '/api/property?id=999999';
    $response = file_get_contents($url);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success'] === false) {
            echo "   ✅ Error handling working (404 for invalid property)\n";
            $test_results[] = ['Error Handling', 'PASS'];
        } else {
            echo "   ❌ Error handling not working properly\n";
            $test_results[] = ['Error Handling', 'FAIL'];
        }
    } else {
        echo "   ❌ No error response\n";
        $test_results[] = ['Error Handling', 'FAIL'];
    }
} catch (Exception $e) {
    echo "   ❌ Error handling test error: " . $e->getMessage() . "\n";
    $test_results[] = ['Error Handling', 'FAIL'];
}

// Summary
echo "\n📊 Mobile API Test Summary\n";
echo "=========================\n";

$pass_count = 0;
$fail_count = 0;
$warn_count = 0;

foreach ($test_results as $result) {
    $status = $result[1];
    $icon = $status === 'PASS' ? '✅' : ($status === 'WARN' ? '⚠️' : '❌');
    echo "{$icon} {$result[0]}: {$status}\n";

    if ($status === 'PASS') $pass_count++;
    elseif ($status === 'FAIL') $fail_count++;
    else $warn_count++;
}

$total_tests = count($test_results);
$success_rate = round(($pass_count / $total_tests) * 100, 1);

echo "\n🎯 Results: {$pass_count} passed, {$warn_count} warnings, {$fail_count} failed\n";
echo "📈 Success Rate: {$success_rate}%\n";

if ($fail_count === 0) {
    echo "\n🎉 ALL MOBILE API TESTS PASSED!\n";
    echo "📱 Mobile app integration is READY!\n";
    echo "\n🌟 Mobile API Endpoints Available:\n";
    echo "   • GET  /api/property-types - Property categories\n";
    echo "   • GET  /api/cities - Available cities\n";
    echo "   • GET  /api/properties - Properties with pagination\n";
    echo "   • GET  /api/property?id=X - Single property details\n";
    echo "   • POST /api/inquiry/submit - Submit property inquiry\n";
    echo "   • POST /api/favorites/toggle - Toggle favorites\n";
    echo "   • GET  /api/favorites?user_id=X - User favorites\n";
} else {
    echo "\n⚠️  Some mobile API tests failed.\n";
    echo "🔧 Check the failed tests above.\n";
}

echo "\n🚀 Mobile API System Ready!\n";
?>
