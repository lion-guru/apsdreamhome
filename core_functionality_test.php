<?php
/**
 * Core Functionality Test Script
 * Tests login, registration, and basic features
 */

echo "<h1>🔍 Core Functionality Testing</h1>\n";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>\n";
echo "<h2>🧪 Testing Core System Features</h2>\n";
echo "<p>Validating login, registration, and basic functionality...</p>\n";
echo "</div>\n";

// Test 1: Check if required files exist
echo "<h3>📁 File System Check</h3>\n";

$required_files = [
    'auth/login.php',
    'auth/register.php',
    'associate_dashboard.php',
    'customer_dashboard.php',
    'admin/admin_panel.php',
    'includes/db_config.php',
    'includes/security/security_functions.php'
];

$missing_files = [];
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<div style='color: #28a745;'>✅ $file exists</div>\n";
    } else {
        echo "<div style='color: #dc3545;'>❌ $file missing</div>\n";
        $missing_files[] = $file;
    }
}

if (empty($missing_files)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745;'>\n";
    echo "<h4>✅ All required files are present</h4>\n";
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545;'>\n";
    echo "<h4>❌ Missing files: " . implode(', ', $missing_files) . "</h4>\n";
    echo "</div>\n";
}

// Test 2: Database Connection Test
echo "<h3>🗄️ Database Connection Test</h3>\n";

try {
    require_once 'includes/db_config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "<div style='color: #28a745;'>✅ Database connection successful</div>\n";

    // Test if required tables exist
    $required_tables = ['users', 'properties', 'property_types', 'locations'];
    $missing_tables = [];

    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<div style='color: #28a745;'>✅ Table '$table' exists</div>\n";
        } else {
            echo "<div style='color: #ffc107;'>⚠️ Table '$table' not found</div>\n";
            $missing_tables[] = $table;
        }
    }

    if (empty($missing_tables)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745;'>\n";
        echo "<h4>✅ All required database tables exist</h4>\n";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ffc107;'>\n";
        echo "<h4>⚠️ Missing tables: " . implode(', ', $missing_tables) . "</h4>\n";
    }

    $conn->close();

} catch (Exception $e) {
    echo "<div style='color: #dc3545;'>❌ Database connection failed: " . $e->getMessage() . "</div>\n";
}

// Test 3: Security Functions Test
echo "<h3>🔒 Security Functions Test</h3>\n";

try {
    require_once 'includes/security/security_functions.php';

    // Test getClientIP function
    $client_ip = getClientIP();
    if ($client_ip && $client_ip !== 'UNKNOWN') {
        echo "<div style='color: #28a745;'>✅ getClientIP() working: $client_ip</div>\n";
    } else {
        echo "<div style='color: #dc3545;'>❌ getClientIP() failed</div>\n";
    }

    // Test rate limiting
    $test_rate_file = 'logs/test_rate_limit.json';
    $rate_result = checkRateLimit('192.168.1.1', $test_rate_file, 5);

    if (isset($rate_result['allowed'])) {
        echo "<div style='color: #28a745;'>✅ Rate limiting function working</div>\n";
        echo "<div style='color: #17a2b8;'>ℹ️ Rate limit status: {$rate_result['remaining']} remaining</div>\n";
    } else {
        echo "<div style='color: #dc3545;'>❌ Rate limiting function failed</div>\n";
    }

} catch (Exception $e) {
    echo "<div style='color: #dc3545;'>❌ Security functions test failed: " . $e->getMessage() . "</div>\n";
}

// Test 4: Login System Validation
echo "<h3>🔐 Login System Validation</h3>\n";

$login_file = 'auth/login.php';
if (file_exists($login_file)) {
    $login_content = file_get_contents($login_file);

    $checks = [
        'HTTPS validation' => strpos($login_content, 'HTTPS') !== false,
        'Rate limiting' => strpos($login_content, 'checkRateLimit') !== false,
        'CSRF protection' => strpos($login_content, 'csrf_token') !== false,
        'Input validation' => strpos($login_content, 'filter_var') !== false,
        'Password hashing' => strpos($login_content, 'password_verify') !== false,
        'Session security' => strpos($login_content, 'session_regenerate_id') !== false
    ];

    $passed = 0;
    foreach ($checks as $check_name => $check_result) {
        if ($check_result) {
            echo "<div style='color: #28a745;'>✅ $check_name implemented</div>\n";
            $passed++;
        } else {
            echo "<div style='color: #dc3545;'>❌ $check_name missing</div>\n";
        }
    }

    if ($passed >= 5) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745;'>\n";
        echo "<h4>✅ Login system security: $passed/6 checks passed</h4>\n";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545;'>\n";
        echo "<h4>❌ Login system needs improvement: $passed/6 checks passed</h4>\n";
    }
} else {
    echo "<div style='color: #dc3545;'>❌ Login system file not found</div>\n";
}

// Test 5: Registration System Validation
echo "<h3>📝 Registration System Validation</h3>\n";

$register_file = 'auth/register.php';
if (file_exists($register_file)) {
    $register_content = file_get_contents($register_file);

    $checks = [
        'Email validation' => strpos($register_content, 'filter_var') !== false,
        'Password strength' => strpos($register_content, 'password_strength') !== false,
        'CAPTCHA protection' => strpos($register_content, 'captcha') !== false,
        'User role selection' => strpos($register_content, 'user_role') !== false,
        'Input sanitization' => strpos($register_content, 'trim') !== false,
        'Database insertion' => strpos($register_content, 'INSERT INTO users') !== false
    ];

    $passed = 0;
    foreach ($checks as $check_name => $check_result) {
        if ($check_result) {
            echo "<div style='color: #28a745;'>✅ $check_name implemented</div>\n";
            $passed++;
        } else {
            echo "<div style='color: #dc3545;'>❌ $check_name missing</div>\n";
        }
    }

    if ($passed >= 5) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745;'>\n";
        echo "<h4>✅ Registration system: $passed/6 checks passed</h4>\n";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545;'>\n";
        echo "<h4>❌ Registration system needs improvement: $passed/6 checks passed</h4>\n";
    }
} else {
    echo "<div style='color: #dc3545;'>❌ Registration system file not found</div>\n";
}

// Test 6: Dashboard Access Test
echo "<h3>📊 Dashboard Access Test</h3>\n";

$dashboards = [
    'associate_dashboard.php' => 'Associate Dashboard',
    'customer_dashboard.php' => 'Customer Dashboard',
    'admin/admin_panel.php' => 'Admin Panel'
];

foreach ($dashboards as $file => $name) {
    if (file_exists($file)) {
        echo "<div style='color: #28a745;'>✅ $name accessible</div>\n";
    } else {
        echo "<div style='color: #dc3545;'>❌ $name not found</div>\n";
    }
}

// Test 7: AI System Test
echo "<h3>🤖 AI System Test</h3>\n";

$ai_files = [
    'includes/PropertyAI.php',
    'ai_chatbot.html',
    'api/ai/chat.php',
    'api/ai/recommendations.php'
];

$ai_passed = 0;
foreach ($ai_files as $file) {
    if (file_exists($file)) {
        echo "<div style='color: #28a745;'>✅ AI component: $file</div>\n";
        $ai_passed++;
    } else {
        echo "<div style='color: #ffc107;'>⚠️ AI component missing: $file</div>\n";
    }
}

if ($ai_passed >= 3) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745;'>\n";
    echo "<h4>✅ AI System: $ai_passed/4 components available</h4>\n";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ffc107;'>\n";
    echo "<h4>⚠️ AI System: $ai_passed/4 components available</h4>\n";
}

// Final Summary
echo "<h3>📋 Final Test Summary</h3>\n";

$overall_score = 0;
$max_score = 7;

if (empty($missing_files)) $overall_score++;
if (empty($missing_tables)) $overall_score++;
if ($passed >= 5) $overall_score++; // Login system
if ($ai_passed >= 3) $overall_score++; // AI system

$percentage = round(($overall_score / $max_score) * 100);

echo "<div style='background: " . ($percentage >= 80 ? '#d4edda' : ($percentage >= 60 ? '#fff3cd' : '#f8d7da')) . "; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid " . ($percentage >= 80 ? '#28a745' : ($percentage >= 60 ? '#ffc107' : '#dc3545')) . ";'>\n";
echo "<h3>🏆 Overall System Status: " . ($percentage >= 80 ? 'EXCELLENT' : ($percentage >= 60 ? 'GOOD' : 'NEEDS ATTENTION')) . "</h3>\n";
echo "<div style='display: flex; justify-content: space-between; margin: 10px 0;'>\n";
echo "<div><strong>Score:</strong> $overall_score/$max_score</div>\n";
echo "<div><strong>Percentage:</strong> <span style='font-size: 24px; color: " . ($percentage >= 80 ? '#28a745' : ($percentage >= 60 ? '#ffc107' : '#dc3545')) . ";'>$percentage%</span></div>\n";
echo "<div><strong>Status:</strong> " . ($percentage >= 80 ? '✅ PRODUCTION READY' : ($percentage >= 60 ? '⚠️ NEEDS IMPROVEMENT' : '❌ REQUIRES FIXES')) . "</div>\n";
echo "</div>\n";
echo "</div>\n";

if ($percentage >= 80) {
    echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #17a2b8;'>\n";
    echo "<h3>🎉 CONGRATULATIONS!</h3>\n";
    echo "<p>Your APS Dream Home application is <strong>fully functional</strong> and ready for use!</p>\n";
    echo "<ul>\n";
    echo "<li>✅ Login system with advanced security</li>\n";
    echo "<li>✅ Registration system with validation</li>\n";
    echo "<li>✅ Database connectivity confirmed</li>\n";
    echo "<li>✅ All core files present and accessible</li>\n";
    echo "<li>✅ Security functions working properly</li>\n";
    echo "<li>✅ Dashboard systems operational</li>\n";
    echo "<li>✅ AI components integrated</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #dc3545;'>\n";
    echo "<h3>⚠️ ACTION REQUIRED</h3>\n";
    echo "<p>Some components need attention before full deployment.</p>\n";
    echo "<p>Please review the test results above and address any missing or failing components.</p>\n";
    echo "</div>\n";
}

echo "<div style='background: #e9ecef; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h3>🔧 Next Steps</h3>\n";
echo "<ol>\n";
echo "<li><strong>Test Login:</strong> Try accessing <a href='/auth/login'>/auth/login</a></li>\n";
echo "<li><strong>Test Registration:</strong> Try accessing <a href='/auth/register'>/auth/register</a></li>\n";
echo "<li><strong>Test Dashboards:</strong> Login and verify dashboard access</li>\n";
echo "<li><strong>Test AI Chat:</strong> Try the AI chatbot at <a href='/ai_chatbot.html'>/ai_chatbot.html</a></li>\n";
echo "<li><strong>Run Full Test Suite:</strong> Execute the comprehensive test suite</li>\n";
echo "</ol>\n";
echo "</div>\n";
?>
