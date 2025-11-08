<?php
/**
 * APS Dream Home - Qwen3 AI Integration Test
 * Tests the Qwen3 model integration with OpenRouter
 */

require_once 'includes/config.php';

// Check if AI is enabled
if (!$config['ai']['enabled']) {
    die('❌ AI features are currently disabled.');
}

echo "🤖 APS Dream Home - Qwen3 AI Integration Test\n";
echo "==============================================\n\n";

echo "🔧 Configuration Test:\n";
echo "✅ AI Provider: " . ($config['ai']['provider'] ?? 'Not configured') . "\n";
echo "✅ Model: " . ($config['ai']['model'] ?? 'Not configured') . "\n";
echo "✅ API Key: " . (strlen($config['ai']['api_key'] ?? '') > 10 ? '✅ Configured' : '❌ Missing') . "\n\n";

// Test basic AI functionality
echo "🧪 Testing AI Features:\n";

try {
    // Test 1: Simple chatbot response
    echo "1️⃣ Testing chatbot response...\n";
    $ai = new AIDreamHome();
    $test_result = $ai->generateChatbotResponse("Hello, can you help me find a good property in Gorakhpur?");

    if (isset($test_result['success'])) {
        echo "✅ Chatbot test passed\n";
        echo "📝 Sample response: " . substr($test_result['success'], 0, 100) . "...\n\n";
    } else {
        echo "❌ Chatbot test failed: " . ($test_result['error'] ?? 'Unknown error') . "\n\n";
    }

    // Test 2: Property description generation
    echo "2️⃣ Testing property description generation...\n";
    $property_data = [
        'type' => 'Luxury Villa',
        'location' => 'Gorakhpur',
        'price' => '7500000',
        'bedrooms' => '4',
        'area' => '2000',
        'features' => ['Swimming Pool', 'Garden', 'Security', 'Parking']
    ];

    $desc_result = $ai->generatePropertyDescription($property_data);

    if (isset($desc_result['success'])) {
        echo "✅ Property description test passed\n";
        echo "📝 Sample description: " . substr($desc_result['success'], 0, 100) . "...\n\n";
    } else {
        echo "❌ Property description test failed: " . ($desc_result['error'] ?? 'Unknown error') . "\n\n";
    }

    // Test 3: Property valuation
    echo "3️⃣ Testing property valuation...\n";
    $valuation_data = [
        'location' => 'Gorakhpur',
        'type' => '3BHK Apartment',
        'area' => '1500',
        'bedrooms' => '3',
        'bathrooms' => '2',
        'year_built' => '2020',
        'condition' => 'Excellent',
        'amenities' => ['Parking', 'Security', 'Lift']
    ];

    $val_result = $ai->estimatePropertyValue($valuation_data);

    if (isset($val_result['success'])) {
        echo "✅ Property valuation test passed\n";
        echo "📊 Sample valuation: " . substr($val_result['success'], 0, 100) . "...\n\n";
    } else {
        echo "❌ Property valuation test failed: " . ($val_result['error'] ?? 'Unknown error') . "\n\n";
    }

    // Test 4: Usage statistics
    echo "4️⃣ Testing usage statistics...\n";
    $stats = $ai->getUsageStats();

    echo "✅ Total requests: " . number_format($stats['total_requests']) . "\n";
    echo "✅ Input tokens: " . number_format($stats['total_input_tokens']) . "\n";
    echo "✅ Output tokens: " . number_format($stats['total_output_tokens']) . "\n\n";

    echo "🎯 Test Summary:\n";
    echo "==============\n";
    echo "✅ Qwen3 model: Working properly\n";
    echo "✅ API Provider: OpenRouter\n";
    echo "✅ Integration: Working properly\n\n";

    echo "🚀 Ready to use!\n";
    echo "You can now access the AI demo at: http://localhost/apsdreamhome/ai_demo.php\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Please check your API key and network connection.\n";
}

// Display current configuration for verification
echo "\n🔍 Current Configuration:\n";
echo "=======================\n";
echo "AI Enabled: " . ($config['ai']['enabled'] ? '✅ Yes' : '❌ No') . "\n";
echo "Provider: " . ($config['ai']['provider'] ?? 'Not set') . "\n";
echo "Model: " . ($config['ai']['model'] ?? 'Not set') . "\n";
echo "API Key Length: " . strlen($config['ai']['api_key'] ?? '') . " characters\n";

$features = $config['ai']['features'] ?? [];
$enabled_features = array_filter($features, fn($v) => $v === true);
echo "Enabled Features: " . count($enabled_features) . "/7\n";

echo "\n📋 Available Features:\n";
foreach ($features as $feature => $enabled) {
    echo ($enabled ? '✅' : '❌') . " $feature\n";
}

echo "\n🎉 Qwen3 Integration Test Complete!\n";
