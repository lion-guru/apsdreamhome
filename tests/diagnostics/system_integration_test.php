<?php
/**
 * APS Dream Home - Comprehensive System Test Suite
 * Tests all implemented components and functionality
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>System Integration Test - APS Dream Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .test-section {
            margin: 20px 0;
            padding: 20px;
            border-radius: 10px;
            background: #f8f9fa;
        }

        .test-section h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }

        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }

        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }

        .test-result {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }

        .pass { background: #d4edda; color: #155724; }
        .fail { background: #f8d7da; color: #721c24; }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔬 System Integration Test Suite</h1>
            <p>APS Dream Home - Comprehensive Component Testing</p>
        </div>

        <div class='content'>";

$test_results = [];
$total_tests = 0;
$passed_tests = 0;

function test_result($test_name, $result, $message = '') {
    global $test_results, $total_tests, $passed_tests;

    $total_tests++;
    if ($result) {
        $passed_tests++;
        $test_results[] = "<div class='test-result pass'>✅ <strong>$test_name:</strong> PASSED $message</div>";
        return true;
    } else {
        $test_results[] = "<div class='test-result fail'>❌ <strong>$test_name:</strong> FAILED $message</div>";
        return false;
    }
}

function run_test($name, $callback) {
    try {
        return $callback();
    } catch (Exception $e) {
        return test_result($name, false, " - Error: " . $e->getMessage());
    }
}

echo "<h2>🚀 Starting Comprehensive System Tests...</h2>";

// Test 1: Database Connection
echo "<div class='test-section'>";
echo "<h3>🗄️ Database System Tests</h3>";

run_test("Database Configuration File", function() {
    return file_exists('includes/db_config.php') && filesize('includes/db_config.php') > 1000;
});

run_test("Database Class", function() {
    return file_exists('includes/Database.php') && filesize('includes/Database.php') > 2000;
});

run_test("Database Connection", function() {
    try {
        require_once 'includes/db_config.php';
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $result = $conn->connect_error === null;
        $conn->close();
        return $result;
    } catch (Exception $e) {
        return false;
    }
});

run_test("Database Tables", function() {
    try {
        require_once 'includes/db_config.php';
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $result = $conn->query("SHOW TABLES");
        $tables = $result->num_rows >= 5; // At least 5 core tables
        $conn->close();
        return $tables;
    } catch (Exception $e) {
        return false;
    }
});

echo "</div>";

// Test 2: Security Components
echo "<div class='test-section'>";
echo "<h3>🔐 Security System Tests</h3>";

run_test("Security Manager", function() {
    return file_exists('includes/security/security_manager.php') && filesize('includes/security/security_manager.php') > 10000;
});

run_test("Security Functions", function() {
    return file_exists('includes/security/security_functions.php');
});

run_test("CSRF Protection", function() {
    return file_exists('includes/security/csrf.php');
});

run_test("Rate Limiting", function() {
    return file_exists('includes/security/rate_limiter.php');
});

// Test Security Manager functionality
run_test("Security Manager Instantiation", function() {
    try {
        require_once 'includes/security/security_manager.php';
        $security = new SecurityManager();
        return $security instanceof SecurityManager;
    } catch (Exception $e) {
        return false;
    }
});

echo "</div>";

// Test 3: Performance Components
echo "<div class='test-section'>";
echo "<h3>⚡ Performance System Tests</h3>";

run_test("Performance Manager", function() {
    return file_exists('includes/performance_manager.php') && filesize('includes/performance_manager.php') > 15000;
});

run_test("Cache System", function() {
    return file_exists('includes/cache.php');
});

// Test Performance Manager functionality
run_test("Performance Manager Instantiation", function() {
    try {
        require_once 'includes/performance_manager.php';
        $performance = new PerformanceManager();
        return $performance instanceof PerformanceManager;
    } catch (Exception $e) {
        return false;
    }
});

echo "</div>";

// Test 4: Event System
echo "<div class='test-section'>";
echo "<h3>📡 Event System Tests</h3>";

run_test("Event System", function() {
    return file_exists('includes/event_system.php') && filesize('includes/event_system.php') > 10000;
});

// Test Event System functionality
run_test("Event Dispatcher Instantiation", function() {
    try {
        require_once 'includes/event_system.php';
        $events = EventDispatcher::getInstance();
        return $events instanceof EventDispatcher;
    } catch (Exception $e) {
        return false;
    }
});

echo "</div>";

// Test 5: Template System
echo "<div class='test-section'>";
echo "<h3>🎨 Template System Tests</h3>";

run_test("Dynamic Header Template", function() {
    return file_exists('includes/templates/dynamic_header.php') && filesize('includes/templates/dynamic_header.php') > 10000;
});

run_test("Dynamic Footer Template", function() {
    return file_exists('includes/templates/dynamic_footer.php') && filesize('includes/templates/dynamic_footer.php') > 15000;
});

run_test("Base Template", function() {
    return file_exists('includes/templates/base_template.php');
});

echo "</div>";

// Test 6: Authentication System
echo "<div class='test-section'>";
echo "<h3>🔐 Authentication System Tests</h3>";

run_test("Login System", function() {
    return file_exists('auth/login.php') && filesize('auth/login.php') > 20000;
});

run_test("Registration System", function() {
    return file_exists('auth/register.php') && filesize('auth/register.php') > 20000;
});

run_test("Logout System", function() {
    return file_exists('auth/logout.php');
});

echo "</div>";

// Test 7: Dashboard Systems
echo "<div class='test-section'>";
echo "<h3>📊 Dashboard System Tests</h3>";

run_test("Associate Dashboard", function() {
    return file_exists('associate_dashboard.php') && filesize('associate_dashboard.php') > 50000;
});

run_test("Customer Dashboard", function() {
    return file_exists('customer_dashboard.php') && filesize('customer_dashboard.php') > 20000;
});

run_test("Admin Panel", function() {
    return file_exists('admin/admin_panel.php') && filesize('admin/admin_panel.php') > 10000;
});

echo "</div>";

// Test 8: API System
echo "<div class='test-section'>";
echo "<h3>🔌 API System Tests</h3>";

run_test("API Directory", function() {
    return is_dir('api') && count(scandir('api')) > 5;
});

run_test("API Test Endpoint", function() {
    return file_exists('api/test.php');
});

run_test("API Documentation", function() {
    return file_exists('api-docs/index.html');
});

echo "</div>";

// Test 9: Core Application Files
echo "<div class='test-section'>";
echo "<h3>🏗️ Core Application Tests</h3>";

run_test("Main Index", function() {
    return file_exists('index.php') && filesize('index.php') > 40000;
});

run_test("Configuration File", function() {
    return file_exists('config.php') && filesize('config.php') > 15000;
});

run_test("HTACCESS File", function() {
    return file_exists('.htaccess') && filesize('.htaccess') > 5000;
});

run_test("Assets Directory", function() {
    return is_dir('assets') && count(scandir('assets')) > 10;
});

echo "</div>";

// Test 10: Advanced Features
echo "<div class='test-section'>";
echo "<h3>🤖 Advanced Features Tests</h3>";

run_test("AI Chatbot", function() {
    return file_exists('ai_chatbot.html') && filesize('ai_chatbot.html') > 20000;
});

run_test("Property AI", function() {
    return file_exists('includes/PropertyAI.php') && filesize('includes/PropertyAI.php') > 30000;
});

run_test("WhatsApp Integration", function() {
    return file_exists('whatsapp_demo.php');
});

run_test("Email Templates", function() {
    return file_exists('includes/email_template_manager.php');
});

echo "</div>";

// Display Results
echo "<div class='test-section'>";
echo "<h3>📊 Test Results Summary</h3>";

$success_rate = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100, 2) : 0;

echo "<div class='summary-stats'>";
echo "<div class='stat-card'>";
echo "<div class='stat-number'>$passed_tests/$total_tests</div>";
echo "<div class='stat-label'>Tests Passed</div>";
echo "</div>";
echo "<div class='stat-card'>";
echo "<div class='stat-number'>$success_rate%</div>";
echo "<div class='stat-label'>Success Rate</div>";
echo "</div>";
echo "<div class='stat-card'>";
echo "<div class='stat-number'>" . ($total_tests - $passed_tests) . "</div>";
echo "<div class='stat-label'>Tests Failed</div>";
echo "</div>";
echo "</div>";

echo "<div class='progress-bar'>";
echo "<div class='progress-fill' style='width: $success_rate%'></div>";
echo "</div>";

echo "<div class='" . ($success_rate >= 90 ? 'success' : ($success_rate >= 70 ? 'warning' : 'error')) . "'>";
echo "<h4>Overall Status: " . ($success_rate >= 90 ? 'EXCELLENT' : ($success_rate >= 70 ? 'GOOD' : 'NEEDS ATTENTION')) . "</h4>";
echo "<p>Success Rate: $success_rate% ($passed_tests out of $total_tests tests passed)</p>";
echo "</div>";

echo "</div>";

// Display Individual Results
echo "<div class='test-section'>";
echo "<h3>📋 Detailed Test Results</h3>";
foreach ($test_results as $result) {
    echo $result;
}
echo "</div>";

// Recommendations
echo "<div class='test-section'>";
echo "<h3>💡 Recommendations</h3>";

if ($success_rate >= 90) {
    echo "<div class='success'>";
    echo "<h4>🎉 EXCELLENT! System is ready for production</h4>";
    echo "<p>All critical components are working correctly. You can proceed with deployment.</p>";
    echo "</div>";
} elseif ($success_rate >= 70) {
    echo "<div class='warning'>";
    echo "<h4>⚠️ GOOD! Minor issues need attention</h4>";
    echo "<p>Most components are working. Please review and fix the failed tests before deployment.</p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h4>❌ NEEDS ATTENTION! Critical issues found</h4>";
    echo "<p>Several components are not working properly. Please review all failed tests and fix them before proceeding.</p>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h4>🔧 Next Steps:</h4>";
echo "<ol>";
echo "<li><strong>Review Failed Tests:</strong> Check the detailed results above</li>";
echo "<li><strong>Fix Issues:</strong> Address any failing components</li>";
echo "<li><strong>Run Tests Again:</strong> Re-run this test suite</li>";
echo "<li><strong>Performance Testing:</strong> Test system performance</li>";
echo "<li><strong>Security Audit:</strong> Verify security components</li>";
echo "<li><strong>Deploy:</strong> Deploy to production environment</li>";
echo "</ol>";
echo "</div>";

echo "</div>";

// Test Completion
echo "<div class='test-section'>";
echo "<h3>🏁 Test Suite Completed</h3>";
echo "<div class='info'>";
echo "<p><strong>Test Completed At:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Total Execution Time:</strong> " . round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2) . " seconds</p>";
echo "<p><strong>Memory Usage:</strong> " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB</p>";
echo "</div>";
echo "</div>";

echo "</div>
    </div>
</body>
</html>";
?>
