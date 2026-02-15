<?php
/**
 * Comprehensive Test Runner for APS Dream Home
 * This script runs all available tests and provides a summary report
 */

echo "<h1>🧪 APS Dream Home - Comprehensive Test Suite</h1>\n";
echo "<p>Running all tests for the APS Dream Home application...</p>\n";

// Load database configuration
require_once 'includes/config/constants.php';

// Test results array
$testResults = [
    'database' => ['passed' => 0, 'failed' => 0, 'skipped' => 0],
    'unit' => ['passed' => 0, 'failed' => 0, 'skipped' => 0],
    'feature' => ['passed' => 0, 'failed' => 0, 'skipped' => 0],
    'admin' => ['passed' => 0, 'failed' => 0, 'skipped' => 0],
    'total' => ['passed' => 0, 'failed' => 0, 'skipped' => 0]
];

// Function to run a test file
function runTestFile($filePath, $category) {
    global $testResults;
    
    if (!file_exists($filePath)) {
        echo "<span style='color: orange;'>⚠️ Test file not found: {$filePath}</span><br>\n";
        $testResults[$category]['skipped']++;
        $testResults['total']['skipped']++;
        return false;
    }
    
    try {
        // Include and execute the test file
        ob_start();
        include $filePath;
        $output = ob_get_clean();
        
        // Simple check if test ran without fatal errors
        if (strpos($output, 'Fatal error') === false) {
            echo "<span style='color: green;'>✅ {$filePath}</span><br>\n";
            $testResults[$category]['passed']++;
            $testResults['total']['passed']++;
            return true;
        } else {
            echo "<span style='color: red;'>❌ {$filePath} - Fatal Error</span><br>\n";
            echo "<small style='color: red;'>{$output}</small><br>\n";
            $testResults[$category]['failed']++;
            $testResults['total']['failed']++;
            return false;
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>❌ {$filePath} - Exception: " . $e->getMessage() . "</span><br>\n";
        $testResults[$category]['failed']++;
        $testResults['total']['failed']++;
        return false;
    } catch (Error $e) {
        echo "<span style='color: red;'>❌ {$filePath} - Error: " . $e->getMessage() . "</span><br>\n";
        $testResults[$category]['failed']++;
        $testResults['total']['failed']++;
        return false;
    }
}

// Database Tests
echo "<h2>🗄️ Database Tests</h2>\n";
runTestFile('tests/Feature/DatabaseTest.php', 'database');
runTestFile('test_database_standalone.php', 'database');

// Unit Tests
echo "<h2>🔬 Unit Tests</h2>\n";
runTestFile('tests/Unit/Models/PropertyTest.php', 'unit');
runTestFile('tests/Unit/Models/ProjectTest.php', 'unit');

// Feature Tests
echo "<h2>🌐 Feature Tests</h2>\n";
runTestFile('tests/Feature/HomepageTest.php', 'feature');
runTestFile('tests/Feature/AuthenticationTest.php', 'feature');
runTestFile('tests/Feature/PropertySearchTest.php', 'feature');

// Admin Tests
echo "<h2>👨‍💼 Admin Tests</h2>\n";
runTestFile('tests/Feature/Admin/AdminDashboardTest.php', 'admin');

// Generate Summary Report
echo "<h2>📊 Test Summary</h2>\n";

$totalTests = $testResults['total']['passed'] + $testResults['total']['failed'] + $testResults['total']['skipped'];
$passRate = $totalTests > 0 ? round(($testResults['total']['passed'] / $totalTests) * 100, 2) : 0;

echo "<div style='background-color: #f0f8ff; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0;'>\n";
echo "<h3>Overall Results</h3>\n";
echo "<p><strong>Total Tests:</strong> {$totalTests}</p>\n";
echo "<p><strong>Passed:</strong> <span style='color: green;'>{$testResults['total']['passed']}</span></p>\n";
echo "<p><strong>Failed:</strong> <span style='color: red;'>{$testResults['total']['failed']}</span></p>\n";
echo "<p><strong>Skipped:</strong> <span style='color: orange;'>{$testResults['total']['skipped']}</span></p>\n";
echo "<p><strong>Pass Rate:</strong> <strong>{$passRate}%</strong></p>\n";
echo "</div>\n";

// Category Breakdown
echo "<h3>Category Breakdown</h3>\n";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>\n";
echo "<thead><tr style='background-color: #f0f0f0;'><th>Category</th><th>Passed</th><th>Failed</th><th>Skipped</th><th>Total</th><th>Pass Rate</th></tr></thead>\n";
echo "<tbody>\n";

foreach (['database', 'unit', 'feature', 'admin'] as $category) {
    $categoryTotal = $testResults[$category]['passed'] + $testResults[$category]['failed'] + $testResults[$category]['skipped'];
    $categoryPassRate = $categoryTotal > 0 ? round(($testResults[$category]['passed'] / $categoryTotal) * 100, 2) : 0;
    
    echo "<tr>";
    echo "<td><strong>" . ucfirst($category) . "</strong></td>";
    echo "<td style='color: green;'>{$testResults[$category]['passed']}</td>";
    echo "<td style='color: red;'>{$testResults[$category]['failed']}</td>";
    echo "<td style='color: orange;'>{$testResults[$category]['skipped']}</td>";
    echo "<td>{$categoryTotal}</td>";
    echo "<td><strong>{$categoryPassRate}%</strong></td>";
    echo "</tr>\n";
}

echo "</tbody></table>\n";

// Recommendations
echo "<h3>📋 Recommendations</h3>\n";

if ($testResults['total']['failed'] > 0) {
    echo "<div style='background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;'>\n";
    echo "<strong>⚠️ Action Required:</strong> Some tests failed. Please review the errors above and fix the issues.<br>\n";
    echo "<strong>Common Issues:</strong><br>\n";
    echo "• Missing database tables or columns<br>\n";
    echo "• Incorrect file paths or permissions<br>\n";
    echo "• Missing dependencies or configurations<br>\n";
    echo "</div>\n";
} elseif ($testResults['total']['skipped'] > 0) {
    echo "<div style='background-color: #cce5ff; padding: 10px; border-left: 4px solid #007bff;'>\n";
    echo "<strong>ℹ️ Information:</strong> Some tests were skipped. This might be due to missing files or optional features.<br>\n";
    echo "</div>\n";
} else {
    echo "<div style='background-color: #d4edda; padding: 10px; border-left: 4px solid #28a745;'>\n";
    echo "<strong>✅ Excellent!</strong> All tests are passing. The application is ready for deployment.<br>\n";
    echo "</div>\n";
}

// Next Steps
echo "<h3>🚀 Next Steps</h3>\n";
echo "<ul style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #6c757d;'>\n";
echo "<li><strong>For Failed Tests:</strong> Review error messages and fix underlying issues</li>\n";
echo "<li><strong>For Skipped Tests:</strong> Ensure all required files and dependencies are available</li>\n";
echo "<li><strong>For Passing Tests:</strong> Consider adding more edge cases and integration tests</li>\n";
echo "<li><strong>Continuous Integration:</strong> Set up automated testing in your CI/CD pipeline</li>\n";
echo "<li><strong>Code Coverage:</strong> Consider using code coverage tools to identify untested code</li>\n";
echo "</ul>\n";

// Test Environment Info
echo "<h3>🔧 Test Environment</h3>\n";
echo "<div style='background-color: #e2e3e5; padding: 10px; border-left: 4px solid #6c757d;'>\n";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
echo "<p><strong>Database Host:</strong> " . DB_HOST . "</p>\n";
echo "<p><strong>Database Name:</strong> " . DB_NAME . "</p>\n";
echo "<p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
echo "</div>\n";

echo "<hr>\n";
echo "<p><a href='javascript:history.back()' style='text-decoration: none; padding: 8px 16px; background-color: #007bff; color: white; border-radius: 4px;'>← Go Back</a> | 
        <a href='test_database_standalone.php' style='text-decoration: none; padding: 8px 16px; background-color: #28a745; color: white; border-radius: 4px;'>🔄 Run Database Test</a></p>\n";

?>
