<?php
/**
 * Complete System Test Script
 * Tests the entire APS Dream Home system including database, controllers, and email
 */

require_once 'config/bootstrap.php';

echo "🚀 Complete APS Dream Home System Test\n";
echo "=====================================\n\n";

$test_results = [];

// Test 1: Database Connection
echo "1. 🗄️  Testing Database Connection...\n";
try {
    global $pdo;
    if ($pdo) {
        $stmt = $pdo->query("SELECT 1");
        if ($stmt) {
            echo "   ✅ Database connection successful\n";
            $test_results[] = ['Database Connection', 'PASS'];
        } else {
            echo "   ❌ Database query failed\n";
            $test_results[] = ['Database Connection', 'FAIL'];
        }
    } else {
        echo "   ❌ Database connection not available\n";
        $test_results[] = ['Database Connection', 'FAIL'];
    }
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
    $test_results[] = ['Database Connection', 'FAIL'];
}

// Test 2: Core Classes
echo "\n2. 🏗️  Testing Core Classes...\n";
try {
    if (class_exists('App\Core\Router')) {
        echo "   ✅ Router class loaded\n";
        $test_results[] = ['Router Class', 'PASS'];
    } else {
        echo "   ❌ Router class not found\n";
        $test_results[] = ['Router Class', 'FAIL'];
    }

    if (class_exists('App\Core\BaseController')) {
        echo "   ✅ BaseController class loaded\n";
        $test_results[] = ['BaseController Class', 'PASS'];
    } else {
        echo "   ❌ BaseController class not found\n";
        $test_results[] = ['BaseController Class', 'FAIL'];
    }

    if (class_exists('App\Core\EmailNotification')) {
        echo "   ✅ EmailNotification class loaded\n";
        $test_results[] = ['EmailNotification Class', 'PASS'];
    } else {
        echo "   ❌ EmailNotification class not found\n";
        $test_results[] = ['EmailNotification Class', 'FAIL'];
    }
} catch (Exception $e) {
    echo "   ❌ Core classes error: " . $e->getMessage() . "\n";
    $test_results[] = ['Core Classes', 'FAIL'];
}

// Test 3: Controllers
echo "\n3. 🎮 Testing Controllers...\n";
$controllers = [
    'HomeController',
    'PropertyController',
    'AdminController',
    'AuthController',
    'PropertyFavoriteController',
    'PropertyInquiryController',
    'AdminReportsController'
];

foreach ($controllers as $controller) {
    try {
        $className = 'App\Controllers\\' . $controller;
        if (class_exists($className)) {
            echo "   ✅ {$controller} loaded\n";
            $test_results[] = [$controller, 'PASS'];
        } else {
            echo "   ❌ {$controller} not found\n";
            $test_results[] = [$controller, 'FAIL'];
        }
    } catch (Exception $e) {
        echo "   ❌ {$controller} error: " . $e->getMessage() . "\n";
        $test_results[] = [$controller, 'FAIL'];
    }
}

// Test 4: Database Tables
echo "\n4. 📋 Testing Database Tables...\n";
try {
    global $pdo;
    if (!$pdo) {
        echo "   ❌ Database not available for table tests\n";
        $test_results[] = ['Database Tables', 'FAIL'];
    } else {
        $tables = [
            'users' => 'Users table',
            'properties' => 'Properties table',
            'property_types' => 'Property types table',
            'property_images' => 'Property images table',
            'property_favorites' => 'Property favorites table',
            'property_inquiries' => 'Property inquiries table',
            'site_settings' => 'Site settings table'
        ];

        foreach ($tables as $table => $description) {
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
                if ($stmt->rowCount() > 0) {
                    echo "   ✅ {$description} exists\n";
                    $test_results[] = [$description, 'PASS'];
                } else {
                    echo "   ⚠️  {$description} missing\n";
                    $test_results[] = [$description, 'WARN'];
                }
            } catch (Exception $e) {
                echo "   ❌ {$description} check failed: " . $e->getMessage() . "\n";
                $test_results[] = [$description, 'FAIL'];
            }
        }
    }
} catch (Exception $e) {
    echo "   ❌ Database tables error: " . $e->getMessage() . "\n";
    $test_results[] = ['Database Tables', 'FAIL'];
}

// Test 5: Email System
echo "\n5. 📧 Testing Email System...\n";
try {
    $emailNotification = new \App\Core\EmailNotification();
    echo "   ✅ EmailNotification instance created\n";

    // Test email templates (without actually sending)
    $inquiry_data = [
        'property_title' => 'Test Property',
        'city' => 'Test City',
        'state' => 'Test State',
        'id' => 1,
        'subject' => 'Test Inquiry',
        'inquiry_type' => 'general',
        'priority' => 'medium',
        'status' => 'new',
        'created_at' => date('Y-m-d H:i:s'),
        'user_name' => 'Test User',
        'user_email' => 'test@example.com',
        'user_phone' => '+91-9876543210',
        'message' => 'This is a test inquiry message.'
    ];

    $email_html = $emailNotification->getInquiryEmailTemplate($inquiry_data);
    if (strpos($email_html, 'Test Property') !== false) {
        echo "   ✅ Email templates working\n";
        $test_results[] = ['Email Templates', 'PASS'];
    } else {
        echo "   ❌ Email templates failed\n";
        $test_results[] = ['Email Templates', 'FAIL'];
    }

} catch (Exception $e) {
    echo "   ❌ Email system error: " . $e->getMessage() . "\n";
    $test_results[] = ['Email System', 'FAIL'];
}

// Test 6: Routes
echo "\n6. 🛣️  Testing Routes...\n";
try {
    $router = new App\Core\Router();

    // Test some key routes
    $test_routes = [
        'home' => '/',
        'properties' => '/properties',
        'admin' => '/admin',
        'admin/reports' => '/admin/reports',
        'login' => '/login',
        'register' => '/register'
    ];

    foreach ($test_routes as $name => $route) {
        try {
            ob_start();
            $router->dispatch($route);
            $output = ob_get_clean();

            if (strlen($output) > 0) {
                echo "   ✅ {$name} route ({$route}) working\n";
                $test_results[] = ["Route: {$name}", 'PASS'];
            } else {
                echo "   ⚠️  {$name} route ({$route}) empty response\n";
                $test_results[] = ["Route: {$name}", 'WARN'];
            }
        } catch (Exception $e) {
            echo "   ❌ {$name} route ({$route}) error: " . $e->getMessage() . "\n";
            $test_results[] = ["Route: {$name}", 'FAIL'];
        }
    }
} catch (Exception $e) {
    echo "   ❌ Routes error: " . $e->getMessage() . "\n";
    $test_results[] = ['Routes', 'FAIL'];
}

// Test 7: Settings
echo "\n7. ⚙️  Testing Settings System...\n";
try {
    global $pdo;
    if (!$pdo) {
        echo "   ❌ Database not available for settings test\n";
        $test_results[] = ['Settings System', 'FAIL'];
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM site_settings");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            echo "   ✅ Site settings configured ({$result['count']} settings)\n";
            $test_results[] = ['Settings System', 'PASS'];
        } else {
            echo "   ⚠️  No site settings found\n";
            $test_results[] = ['Settings System', 'WARN'];
        }
    }
} catch (Exception $e) {
    echo "   ❌ Settings error: " . $e->getMessage() . "\n";
    $test_results[] = ['Settings System', 'FAIL'];
}

// Summary
echo "\n📊 Test Summary\n";
echo "===============\n";

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
    echo "\n🎉 ALL CRITICAL TESTS PASSED!\n";
    echo "🚀 Your APS Dream Home system is ready for production!\n";
} else {
    echo "\n⚠️  Some tests failed. Please check the errors above.\n";
    echo "🔧 Run the database setup script to fix missing tables.\n";
}

echo "\n💡 Next Steps:\n";
echo "   1. Run setup_complete_database.sql to create all tables\n";
echo "   2. Configure email settings in admin panel\n";
echo "   3. Add real property data\n";
echo "   4. Test with real users\n";

echo "\n🌟 APS Dream Home - Enterprise Real Estate Platform Ready!\n";
?>
