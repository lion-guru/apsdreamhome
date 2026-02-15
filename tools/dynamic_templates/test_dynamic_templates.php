<?php
/**
 * APS Dream Home - Dynamic Template Test Page
 * Test and validate dynamic template functionality
 */

require_once 'includes/config.php';
require_once 'includes/dynamic_templates.php';

// Test results
$testResults = [];

/**
 * Run all tests
 */
function runAllTests() {
    global $testResults;
    
    echo "<h1>🧪 Dynamic Template System Tests</h1>\n";
    echo "<div class='test-container'>\n";
    
    // Test 1: Database Connection
    testDatabaseConnection();
    
    // Test 2: Dynamic Tables Exist
    testDynamicTables();
    
    // Test 3: Header Rendering
    testHeaderRendering();
    
    // Test 4: Footer Rendering
    testFooterRendering();
    
    // Test 5: Content Retrieval
    testContentRetrieval();
    
    // Test 6: Settings Retrieval
    testSettingsRetrieval();
    
    // Test 7: Helper Functions
    testHelperFunctions();
    
    // Test 8: Integration Test
    testIntegration();
    
    // Generate test report
    generateTestReport();
}

/**
 * Test database connection
 */
function testDatabaseConnection() {
    global $testResults;
    
    echo "<h2>🔌 Database Connection Test</h2>\n";
    
    $conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
    
    if ($conn) {
        echo "<div style='color: green;'>✅ Database connection successful</div>\n";
        $testResults['database_connection'] = 'PASS';
    } else {
        echo "<div style='color: red;'>❌ Database connection failed</div>\n";
        $testResults['database_connection'] = 'FAIL';
    }
}

/**
 * Test dynamic tables existence
 */
function testDynamicTables() {
    global $testResults;
    
    echo "<h2>📊 Dynamic Tables Test</h2>\n";
    
    $conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
    if (!$conn) {
        echo "<div style='color: red;'>❌ No database connection</div>\n";
        $testResults['dynamic_tables'] = 'FAIL';
        return;
    }
    
    $tables = ['dynamic_headers', 'dynamic_footers', 'site_content', 'media_library', 'page_templates'];
    $allExist = true;
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<div style='color: green;'>✅ Table '$table' exists</div>\n";
        } else {
            echo "<div style='color: red;'>❌ Table '$table' missing</div>\n";
            $allExist = false;
        }
    }
    
    $testResults['dynamic_tables'] = $allExist ? 'PASS' : 'FAIL';
}

/**
 * Test header rendering
 */
function testHeaderRendering() {
    global $testResults;
    
    echo "<h2>🎨 Header Rendering Test</h2>\n";
    
    try {
        ob_start();
        renderDynamicHeader('main');
        $output = ob_get_clean();
        
        if (strpos($output, '<header') !== false) {
            echo "<div style='color: green;'>✅ Header rendered successfully</div>\n";
            $testResults['header_rendering'] = 'PASS';
        } else {
            echo "<div style='color: orange;'>⚠️ Header rendered but no header tag found</div>\n";
            $testResults['header_rendering'] = 'PARTIAL';
        }
        
        // Show preview
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>\n";
        echo "<strong>Header Preview:</strong><br>\n";
        echo htmlspecialchars(substr($output, 0, 500)) . (strlen($output) > 500 ? '...' : '');
        echo "</div>\n";
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>❌ Header rendering failed: " . $e->getMessage() . "</div>\n";
        $testResults['header_rendering'] = 'FAIL';
    }
}

/**
 * Test footer rendering
 */
function testFooterRendering() {
    global $testResults;
    
    echo "<h2>🎨 Footer Rendering Test</h2>\n";
    
    try {
        ob_start();
        renderDynamicFooter('main');
        $output = ob_get_clean();
        
        if (strpos($output, '<footer') !== false) {
            echo "<div style='color: green;'>✅ Footer rendered successfully</div>\n";
            $testResults['footer_rendering'] = 'PASS';
        } else {
            echo "<div style='color: orange;'>⚠️ Footer rendered but no footer tag found</div>\n";
            $testResults['footer_rendering'] = 'PARTIAL';
        }
        
        // Show preview
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>\n";
        echo "<strong>Footer Preview:</strong><br>\n";
        echo htmlspecialchars(substr($output, 0, 500)) . (strlen($output) > 500 ? '...' : '');
        echo "</div>\n";
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>❌ Footer rendering failed: " . $e->getMessage() . "</div>\n";
        $testResults['footer_rendering'] = 'FAIL';
    }
}

/**
 * Test content retrieval
 */
function testContentRetrieval() {
    global $testResults;
    
    echo "<h2>📝 Content Retrieval Test</h2>\n";
    
    try {
        $content = getDynamicContent('meta', 'site_title');
        
        if ($content) {
            echo "<div style='color: green;'>✅ Content retrieved: " . htmlspecialchars($content) . "</div>\n";
            $testResults['content_retrieval'] = 'PASS';
        } else {
            echo "<div style='color: orange;'>⚠️ No content found (may need to run database setup)</div>\n";
            $testResults['content_retrieval'] = 'PARTIAL';
        }
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>❌ Content retrieval failed: " . $e->getMessage() . "</div>\n";
        $testResults['content_retrieval'] = 'FAIL';
    }
}

/**
 * Test settings retrieval
 */
function testSettingsRetrieval() {
    global $testResults;
    
    echo "<h2>⚙️ Settings Retrieval Test</h2>\n";
    
    try {
        $setting = getSiteSetting('site_name', 'Default Value');
        
        if ($setting !== 'Default Value') {
            echo "<div style='color: green;'>✅ Setting retrieved: " . htmlspecialchars($setting) . "</div>\n";
            $testResults['settings_retrieval'] = 'PASS';
        } else {
            echo "<div style='color: orange;'>⚠️ Using default value (may need to configure settings)</div>\n";
            $testResults['settings_retrieval'] = 'PARTIAL';
        }
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>❌ Settings retrieval failed: " . $e->getMessage() . "</div>\n";
        $testResults['settings_retrieval'] = 'FAIL';
    }
}

/**
 * Test helper functions
 */
function testHelperFunctions() {
    global $testResults;
    
    echo "<h2>🔧 Helper Functions Test</h2>\n";
    
    $allPass = true;
    
    // Test isDynamicTemplatesAvailable
    if (isDynamicTemplatesAvailable()) {
        echo "<div style='color: green;'>✅ isDynamicTemplatesAvailable() returns true</div>\n";
    } else {
        echo "<div style='color: orange;'>⚠️ isDynamicTemplatesAvailable() returns false</div>\n";
        $allPass = false;
    }
    
    // Test BASE_URL availability
    if (defined('BASE_URL')) {
        echo "<div style='color: green;'>✅ BASE_URL is defined: " . BASE_URL . "</div>\n";
    } else {
        echo "<div style='color: red;'>❌ BASE_URL is not defined</div>\n";
        $allPass = false;
    }
    
    $testResults['helper_functions'] = $allPass ? 'PASS' : 'PARTIAL';
}

/**
 * Test integration
 */
function testIntegration() {
    global $testResults;
    
    echo "<h2>🔄 Integration Test</h2>\n";
    
    try {
        ob_start();
        
        // Test complete page rendering
        renderDynamicPage('Test Page', '<p>This is a test page content.</p>', [
            'header_type' => 'main',
            'footer_type' => 'main'
        ]);
        
        $output = ob_get_clean();
        
        if (strpos($output, '<header') !== false && strpos($output, '<footer') !== false) {
            echo "<div style='color: green;'>✅ Complete page rendering successful</div>\n";
            $testResults['integration'] = 'PASS';
        } else {
            echo "<div style='color: orange;'>⚠️ Partial page rendering</div>\n";
            $testResults['integration'] = 'PARTIAL';
        }
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>❌ Integration test failed: " . $e->getMessage() . "</div>\n";
        $testResults['integration'] = 'FAIL';
    }
}

/**
 * Generate test report
 */
function generateTestReport() {
    global $testResults;
    
    echo "<h2>📊 Test Report</h2>\n";
    
    $totalTests = count($testResults);
    $passedTests = count(array_filter($testResults, function($result) { return $result === 'PASS'; }));
    $partialTests = count(array_filter($testResults, function($result) { return $result === 'PARTIAL'; }));
    $failedTests = count(array_filter($testResults, function($result) { return $result === 'FAIL'; }));
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>\n";
    echo "<h3>📈 Test Summary</h3>\n";
    echo "<p><strong>Total Tests:</strong> $totalTests</p>\n";
    echo "<p><strong>Passed:</strong> <span style='color: green;'>$passedTests</span></p>\n";
    echo "<p><strong>Partial:</strong> <span style='color: orange;'>$partialTests</span></p>\n";
    echo "<p><strong>Failed:</strong> <span style='color: red;'>$failedTests</span></p>\n";
    
    $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100) : 0;
    echo "<p><strong>Success Rate:</strong> $successRate%</p>\n";
    
    if ($successRate >= 80) {
        echo "<p style='color: green;'><strong>🎉 System is ready for production!</strong></p>\n";
    } elseif ($successRate >= 60) {
        echo "<p style='color: orange;'><strong>⚠️ System needs minor fixes</strong></p>\n";
    } else {
        echo "<p style='color: red;'><strong>❌ System needs significant fixes</strong></p>\n";
    }
    
    echo "</div>\n";
    
    echo "<h3>📋 Detailed Results</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'><th>Test</th><th>Status</th><th>Details</th></tr>\n";
    
    $testNames = [
        'database_connection' => 'Database Connection',
        'dynamic_tables' => 'Dynamic Tables',
        'header_rendering' => 'Header Rendering',
        'footer_rendering' => 'Footer Rendering',
        'content_retrieval' => 'Content Retrieval',
        'settings_retrieval' => 'Settings Retrieval',
        'helper_functions' => 'Helper Functions',
        'integration' => 'Integration Test'
    ];
    
    foreach ($testResults as $test => $result) {
        $statusColor = $result === 'PASS' ? 'green' : ($result === 'PARTIAL' ? 'orange' : 'red');
        $statusIcon = $result === 'PASS' ? '✅' : ($result === 'PARTIAL' ? '⚠️' : '❌');
        
        echo "<tr>\n";
        echo "<td>" . ($testNames[$test] ?? $test) . "</td>\n";
        echo "<td style='color: $statusColor; font-weight: bold;'>$statusIcon $result</td>\n";
        echo "<td>" . getTestDetails($test) . "</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    echo "<h3>🎯 Next Steps</h3>\n";
    echo "<div class='alert alert-info'>\n";
    if ($failedTests > 0) {
        echo "<strong>❌ Failed Tests:</strong> Fix database connection and table setup issues<br>\n";
    }
    if ($partialTests > 0) {
        echo "<strong>⚠️ Partial Tests:</strong> Configure content and settings in admin panel<br>\n";
    }
    if ($passedTests === $totalTests) {
        echo "<strong>✅ All Tests Passed:</strong> System ready for full deployment<br>\n";
    }
    echo "<strong>🔧 Admin Panel:</strong> <a href='admin/dynamic_content_manager.php'>Manage Dynamic Content</a><br>\n";
    echo "<strong>📖 Documentation:</strong> Review implementation guide<br>\n";
    echo "<strong>🚀 Migration:</strong> Run migration script for existing pages<br>\n";
    echo "</div>\n";
}

/**
 * Get test details
 */
function getTestDetails($test) {
    $details = [
        'database_connection' => 'Check if database connection is available',
        'dynamic_tables' => 'Verify all dynamic template tables exist',
        'header_rendering' => 'Test header template rendering functionality',
        'footer_rendering' => 'Test footer template rendering functionality',
        'content_retrieval' => 'Test dynamic content retrieval from database',
        'settings_retrieval' => 'Test site settings retrieval functionality',
        'helper_functions' => 'Test utility functions and BASE_URL availability',
        'integration' => 'Test complete page integration'
    ];
    
    return $details[$test] ?? 'No details available';
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    runAllTests();
}
?>

<style>
.test-container {
    font-family: Arial, sans-serif;
    max-width: 1000px;
    margin: 20px auto;
    padding: 20px;
}

.test-container h1 {
    color: #333;
    border-bottom: 2px solid #667eea;
    padding-bottom: 10px;
}

.test-container h2 {
    color: #555;
    margin-top: 30px;
    border-left: 4px solid #667eea;
    padding-left: 15px;
}

.test-container table {
    margin: 20px 0;
}

.test-container th,
.test-container td {
    padding: 10px;
    text-align: left;
}

.alert {
    padding: 15px;
    margin: 20px 0;
    border-radius: 5px;
}

.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}
</style>
