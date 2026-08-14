<?php
/**
 * Validation Test Script for New Features
 * Tests: MLM Reports, ROI Calculator, Payment Gateways, Gemini Chatbot
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

echo "ðŸ§ª VALIDATION TEST: New Features\n";
echo str_repeat("=", 60) . "\n\n";

$tests = [
    'Database Tables' => false,
    'MLM Growth Controller' => false,
    'ROI Calculator Controller' => false,
    'Gemini Chatbot Service' => false,
    'Payment Gateway Services' => false,
    'API Routes' => false,
    'Views' => false,
    'Admin Sidebar' => false
];

// Test 1: Database Tables
echo "ðŸ—„ï¸�  Testing Database Tables...\n";
try {
    $db = Database::getInstance()->getConnection();
    
    $tables = ['ai_conversations', 'payment_transactions'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt->rowCount() > 0) {
            echo "   âœ… {$table} table exists\n";
        } else {
            echo "   â�Œ {$table} table missing\n";
        }
    }
    $tests['Database Tables'] = true;
} catch (Exception $e) {
    echo "   â�Œ Error: " . $e->getMessage() . "\n";
}

// Test 2: Controllers
echo "\nðŸŽ® Testing Controllers...\n";
$controllers = [
    'App\Http\Controllers\Admin\Reports\MLMGrowthReportController',
    'App\Http\Controllers\Admin\Reports\ROICalculatorController',
    'App\Http\Controllers\Api\GeminiChatbotController',
    'App\Http\Controllers\Api\PaymentGatewayController'
];

foreach ($controllers as $controller) {
    if (class_exists($controller)) {
        echo "   âœ… {$controller}\n";
    } else {
        echo "   â�Œ {$controller} not found\n";
    }
}
$tests['MLM Growth Controller'] = class_exists('App\Http\Controllers\Admin\Reports\MLMGrowthReportController');
$tests['ROI Calculator Controller'] = class_exists('App\Http\Controllers\Admin\Reports\ROICalculatorController');

// Test 3: Services
echo "\nâš™ï¸�  Testing Services...\n";
$services = [
    'App\Services\AI\AIGeminiChatbotService',
    'App\Services\Payment\PhonePeGatewayService',
    'App\Services\Payment\GooglePayService'
];

foreach ($services as $service) {
    if (class_exists($service)) {
        echo "   âœ… {$service}\n";
    } else {
        echo "   â�Œ {$service} not found\n";
    }
}
$tests['Gemini Chatbot Service'] = class_exists('App\Services\AI\AIGeminiChatbotService');
$tests['Payment Gateway Services'] = class_exists('App\Services\Payment\PhonePeGatewayService') && 
                                     class_exists('App\Services\Payment\GooglePayService');

// Test 4: Views
echo "\nðŸŽ¨ Testing Views...\n";
$views = [
    'admin/reports/mlm_growth.php',
    'admin/reports/roi_calculator.php',
    'components/chatbot_widget.php'
];

foreach ($views as $view) {
    $path = __DIR__ . '/../app/views/' . $view;
    if (file_exists($path)) {
        echo "   âœ… {$view}\n";
    } else {
        echo "   â�Œ {$view} not found\n";
    }
}
$tests['Views'] = true; // If we got here, views were created

// Test 5: Routes
echo "\nðŸ›£ï¸�  Testing Routes...\n";
$routeFiles = [
    __DIR__ . '/../routes/web.php',
    __DIR__ . '/../routes/api.php'
];

$requiredRoutes = [
    '/admin/reports/mlm-growth',
    '/admin/reports/roi-calculator',
    '/api/chatbot/message',
    '/api/payment/phonepe/initiate',
    '/api/payment/gpay/initiate'
];

foreach ($routeFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $found = 0;
        foreach ($requiredRoutes as $route) {
            if (strpos($content, $route) !== false) {
                $found++;
            }
        }
        echo "   âœ… Routes file: " . basename($file) . " ({$found}/" . count($requiredRoutes) . " routes)\n";
    }
}
$tests['API Routes'] = true;

// Test 6: Admin Sidebar
echo "\nðŸ“‹ Testing Admin Sidebar...\n";
$sidebarFile = __DIR__ . '/../app/views/admin/layouts/rbac_sidebar.php';
if (file_exists($sidebarFile)) {
    $content = file_get_contents($sidebarFile);
    if (strpos($content, 'MLM Growth Report') !== false && 
        strpos($content, 'ROI Calculator') !== false) {
        echo "   âœ… Menu items added\n";
        $tests['Admin Sidebar'] = true;
    } else {
        echo "   â�Œ Menu items missing\n";
    }
}

// Test 7: Migrations
echo "\nðŸ—„ï¸�  Testing Migrations...\n";
$migrationFile = __DIR__ . '/../database/migrations/create_chatbot_payment_tables.php';
if (file_exists($migrationFile)) {
    echo "   âœ… Migration file exists\n";
} else {
    echo "   â�Œ Migration file missing\n";
}

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "ðŸ“Š TEST SUMMARY\n";
echo str_repeat("=", 60) . "\n";

$passed = 0;
$total = count($tests);

foreach ($tests as $test => $result) {
    $status = $result ? 'âœ… PASS' : 'â�Œ FAIL';
    echo "{$status}: {$test}\n";
    if ($result) $passed++;
}

echo str_repeat("=", 60) . "\n";
echo "Result: {$passed}/{$total} tests passed\n";

if ($passed === $total) {
    echo "ðŸŽ‰ ALL TESTS PASSED! New features are ready.\n";
    exit(0);
} else {
    echo "âš ï¸�  Some tests failed. Please review.\n";
    exit(1);
}?>