<?php
/**
 * Security Test Runner
 * Execute comprehensive security tests for APS Dream Home
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Security Test Runner Class
 */
class SecurityTestRunner {

    private $test_suite;
    private $start_time;

    /**
     * Constructor
     */
    public function __construct() {
        require_once __DIR__ . '/security_test_suite.php';
        $this->test_suite = new SecurityTestSuite();
        $this->start_time = microtime(true);
    }

    /**
     * Run all security tests
     */
    public function runAllTests() {
        echo "<h1>🔒 APS Dream Home - Security Test Suite</h1>\n";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
        echo "<h2>🧪 Running Comprehensive Security Tests</h2>\n";
        echo "<p>Testing all implemented security measures...</p>\n";
        echo "<div id='test-progress' style='background: #e9ecef; height: 20px; border-radius: 10px; margin: 10px 0;'>\n";
        echo "<div id='progress-bar' style='background: #007bff; height: 100%; width: 0%; border-radius: 10px; transition: width 0.3s;'></div>\n";
        echo "</div>\n";
        echo "<p id='progress-text'>Initializing tests...</p>\n";
        echo "</div>\n";
        // Run tests
        $results = $this->test_suite->runSecurityTests();

        $this->displayResults($results);

        // Generate HTML report
        $html_report = $this->test_suite->generateHTMLReport();
        $report_filename = 'security_test_report_' . date('Y-m-d_H-i-s') . '.html';
        $report_path = __DIR__ . '/../logs/' . $report_filename;
        file_put_contents($report_path, $html_report);

        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #28a745;'>\n";
        echo "<h3>📊 Test Report Generated</h3>\n";
        echo "<p>Full HTML report saved to: <strong>$report_filename</strong></p>\n";
        echo "<p><a href='../logs/$report_filename' target='_blank' style='color: #007bff;'>View Detailed Report</a></p>\n";
        echo "</div>\n";

        return $results;
    }

    /**
     * Display test results
     */
    private function displayResults($results) {
        $passed = count(array_filter($results, function($r) { return $r['status'] === 'PASS'; }));
        $failed = count(array_filter($results, function($r) { return $r['status'] === 'FAIL'; }));
        $total = count($results);
        $score = $total > 0 ? round(($passed / $total) * 100, 2) : 0;

        echo "<div style='background: " . ($failed === 0 ? '#d4edda' : '#f8d7da') . "; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid " . ($failed === 0 ? '#28a745' : '#dc3545') . ";'>\n";
        echo "<h3>📈 Test Results Summary</h3>\n";
        echo "<div style='display: flex; justify-content: space-between; margin: 10px 0;'>\n";
        echo "<div><strong>Total Tests:</strong> $total</div>\n";
        echo "<div><strong style='color: #28a745;'>Passed:</strong> $passed</div>\n";
        echo "<div><strong style='color: #dc3545;'>Failed:</strong> $failed</div>\n";
        echo "<div><strong>Security Score:</strong> <span style='font-size: 24px; color: " . ($score >= 90 ? '#28a745' : ($score >= 70 ? '#ffc107' : '#dc3545')) . ";'>$score%</span></div>\n";
        echo "</div>\n";
        echo "</div>\n";

        echo "<div style='margin: 20px 0;'>\n";
        echo "<h3>🔍 Detailed Test Results</h3>\n";

        foreach ($results as $result) {
            $color = $result['status'] === 'PASS' ? '#28a745' : '#dc3545';
            $icon = $result['status'] === 'PASS' ? '✅' : '❌';

            echo "<div style='background: " . ($result['status'] === 'PASS' ? '#f8fff8' : '#fff8f8') . "; border-left: 4px solid $color; padding: 15px; margin: 10px 0; border-radius: 4px;'>\n";
            echo "<h4 style='margin: 0; color: $color;'>$icon {$result['test']}</h4>\n";
            echo "<p style='margin: 5px 0;'><strong>Status:</strong> {$result['status']}</p>\n";
            echo "<p style='margin: 5px 0;'><strong>Message:</strong> {$result['message']}</p>\n";
            echo "<p style='margin: 5px 0; color: #666; font-size: 12px;'><strong>Time:</strong> {$result['timestamp']}</p>\n";
            echo "</div>\n";
        }

        echo "</div>\n";

        // Security recommendations
        if ($failed > 0) {
            echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #ffc107;'>\n";
            echo "<h3>⚠️ Security Recommendations</h3>\n";
            echo "<p>Some security tests failed. Please review the following areas:</p>\n";
            echo "<ul>\n";
            foreach ($results as $result) {
                if ($result['status'] === 'FAIL') {
                    echo "<li><strong>{$result['test']}:</strong> {$result['message']}</li>\n";
                }
            }
            echo "</ul>\n";
            echo "<p><em>Note: All failed tests should be addressed to ensure maximum security.</em></p>\n";
            echo "</div>\n";
        }

        // Security status
        echo "<div style='background: " . ($score >= 90 ? '#d1ecf1' : ($score >= 70 ? '#fff3cd' : '#f8d7da')) . "; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid " . ($score >= 90 ? '#17a2b8' : ($score >= 70 ? '#ffc107' : '#dc3545')) . ";'>\n";
        echo "<h3>🏆 Security Status: " . ($score >= 90 ? 'EXCELLENT' : ($score >= 70 ? 'GOOD' : 'NEEDS_IMPROVEMENT')) . "</h3>\n";

        if ($score >= 90) {
            echo "<p>🎉 <strong>Excellent!</strong> Your application has comprehensive security measures in place.</p>\n";
        } elseif ($score >= 70) {
            echo "<p>👍 <strong>Good!</strong> Your application has good security measures, but some areas need attention.</p>\n";
        } else {
            echo "<p>⚠️ <strong>Needs Improvement!</strong> Several security measures need to be addressed.</p>\n";
        }

        echo "</div>\n";
    }

    /**
     * Get quick security status
     */
    public function getHTMLReport() {
        return $this->test_suite->generateHTMLReport();
    }

    /**
     * Get quick security status
     */
    public function getQuickStatus() {
        $results = $this->test_suite->runSecurityTests();

        $passed = count(array_filter($results, function($r) { return $r['status'] === 'PASS'; }));
        $failed = count(array_filter($results, function($r) { return $r['status'] === 'FAIL'; }));
        $total = count($results);
        $score = $total > 0 ? round(($passed / $total) * 100, 2) : 0;

        return [
            'status' => $failed === 0 ? 'SECURE' : 'VULNERABILITIES_FOUND',
            'passed' => $passed,
            'failed' => $failed,
            'total' => $total,
            'score' => $score,
            'rating' => $score >= 90 ? 'EXCELLENT' : ($score >= 70 ? 'GOOD' : 'NEEDS_IMPROVEMENT')
        ];
    }
}

/**
 * Run Security Tests
 */
if (isset($_GET['run_tests']) && $_GET['run_tests'] === '1') {
    $runner = new SecurityTestRunner();
    $results = $runner->runAllTests();

    echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #17a2b8;'>\n";
    echo "<h3>✅ Security Tests Completed</h3>\n";
    echo "<p>All security tests have been executed. Review the results above and address any failed tests.</p>\n";
    echo "<p><a href='?run_tests=1&download_report=1' style='color: #007bff;'>Download Full Report</a></p>\n";
    echo "</div>\n";
}

/**
 * Download Report
 */
if (isset($_GET['download_report']) && $_GET['download_report'] === '1') {
    $runner = new SecurityTestRunner();
    $results = $runner->runAllTests();
    $html_report = $runner->getHTMLReport();

    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="security_test_report_' . date('Y-m-d_H-i-s') . '.html"');
    echo $html_report;
    exit();
}

/**
 * Quick Security Check
 */
if (isset($_GET['quick_check']) && $_GET['quick_check'] === '1') {
    $runner = new SecurityTestRunner();
    $status = $runner->getQuickStatus();

    echo "<div style='max-width: 600px; margin: 50px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>\n";
    echo "<h2 style='text-align: center; color: #333;'>🔒 Quick Security Check</h2>\n";

    $status_color = $status['score'] >= 90 ? '#28a745' : ($status['score'] >= 70 ? '#ffc107' : '#dc3545');
    $status_icon = $status['score'] >= 90 ? '🛡️' : ($status['score'] >= 70 ? '⚠️' : '🚨');

    echo "<div style='text-align: center; margin: 30px 0;'>\n";
    echo "<div style='font-size: 48px; margin-bottom: 10px;'>$status_icon</div>\n";
    echo "<h3 style='color: $status_color; margin: 10px 0;'>Security Status: {$status['rating']}</h3>\n";
    echo "<div style='font-size: 36px; font-weight: bold; color: $status_color;'>{$status['score']}%</div>\n";
    echo "</div>\n";

    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
    echo "<h4>Test Summary:</h4>\n";
    echo "<p><strong>Tests Passed:</strong> {$status['passed']}/{$status['total']}</p>\n";
    echo "<p><strong>Tests Failed:</strong> {$status['failed']}</p>\n";
    echo "</div>\n";

    if ($status['failed'] > 0) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;'>\n";
        echo "<p><strong>⚠️ Action Required:</strong> {$status['failed']} security tests failed. Run full test suite for detailed analysis.</p>\n";
        echo "</div>\n";
    }

    echo "<div style='text-align: center; margin-top: 30px;'>\n";
    echo "<a href='?run_tests=1' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Run Full Tests</a>\n";
    echo "<a href='?quick_check=1' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Refresh Check</a>\n";
    echo "</div>\n";

    echo "</div>\n";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Test Suite - APS Dream Home</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 2.5em;
        }
        .header p {
            color: #666;
            margin: 10px 0 0 0;
            font-size: 1.2em;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 10px 10px 0;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #28a745;
        }
        .btn-secondary:hover {
            background: #1e7e34;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .feature {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .feature h3 {
            margin: 0 0 10px 0;
            color: #007bff;
        }
        .info-box {
            background: #d1ecf1;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 5px solid #17a2b8;
        }
        .warning-box {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 5px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Security Test Suite</h1>
            <p>Comprehensive Security Validation for APS Dream Home</p>
        </div>

        <div class="info-box">
            <h3>🛡️ About This Security Test Suite</h3>
            <p>This comprehensive security testing suite validates all implemented security measures across your APS Dream Home application. It tests:</p>
            <ul>
                <li>HTTPS and security headers</li>
                <li>Input validation and sanitization</li>
                <li>Session security configuration</li>
                <li>Database security measures</li>
                <li>File upload security</li>
                <li>API security implementation</li>
                <li>Rate limiting functionality</li>
                <li>CSRF protection</li>
                <li>Authentication security</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 40px 0;">
            <a href="?quick_check=1" class="btn">Quick Security Check</a>
            <a href="?run_tests=1" class="btn btn-secondary">Run Full Test Suite</a>
            <a href="?run_tests=1&download_report=1" class="btn btn-warning">Download Full Report</a>
        </div>

        <div class="features">
            <div class="feature">
                <h3>🔍 Comprehensive Testing</h3>
                <p>Tests all security layers including authentication, authorization, input validation, and data protection measures.</p>
            </div>
            <div class="feature">
                <h3>📊 Detailed Reporting</h3>
                <p>Generates detailed HTML reports with pass/fail status, recommendations, and security scores.</p>
            </div>
            <div class="feature">
                <h3>⚡ Quick Assessment</h3>
                <p>Provides instant security status with overall score and immediate feedback on security posture.</p>
            </div>
            <div class="feature">
                <h3>🛠️ Actionable Insights</h3>
                <p>Identifies specific security issues with clear recommendations for remediation.</p>
            </div>
        </div>

        <div class="warning-box">
            <h3>⚠️ Security Testing Notice</h3>
            <p><strong>Important:</strong> This security test suite is designed to validate the security measures that have been implemented in your application. It does not perform penetration testing or vulnerability scanning.</p>
            <p>For production environments, consider engaging professional security auditors for comprehensive penetration testing and vulnerability assessments.</p>
        </div>

        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <p><strong>APS Dream Home Security Testing Suite</strong></p>
            <p style="color: #666; font-size: 14px;">Comprehensive security validation for enterprise applications</p>
        </div>
    </div>

    <script>
        // Simple progress simulation for UI
        let progress = 0;
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');

        if (progressBar && progressText) {
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress >= 100) {
                    progress = 100;
                    progressText.textContent = 'Tests completed!';
                    clearInterval(interval);
                } else {
                    progressText.textContent = `Running security tests... ${Math.round(progress)}%`;
                }
                progressBar.style.width = progress + '%';
            }, 200);
        }
    </script>
</body>
</html>
