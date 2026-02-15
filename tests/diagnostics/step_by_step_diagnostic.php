<?php
/**
 * Step-by-Step Error Diagnostic
 * Tests each component individually to identify the exact failure point
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Step-by-Step Diagnostic - APS Dream Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 30px; }
        .step { margin: 15px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; border-radius: 5px; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        .info { border-left-color: #17a2b8; background: #d1ecf1; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔍 STEP-BY-STEP ERROR DIAGNOSTIC</h1>
            <p>Testing each component individually to identify the failure point</p>
        </div>";

echo "<div class='info'>";
echo "<h3>🔧 System Information</h3>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current File:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

$steps = [];
$step_counter = 1;

// Step 1: Test basic PHP functionality
echo "<div class='step'>";
echo "<h4>Step $step_counter: Basic PHP Functionality</h4>";
try {
    echo "<p>✅ PHP is working correctly</p>";
    echo "<p>✅ Error reporting is enabled</p>";
    echo "<p>✅ Output buffering is working</p>";
    $steps[] = "Basic PHP - PASSED";
    echo "<div class='success'>✅ PASSED</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ FAILED: " . $e->getMessage() . "</div>";
    $steps[] = "Basic PHP - FAILED: " . $e->getMessage();
}
echo "</div>";
$step_counter++;

// Step 2: Test file includes
echo "<div class='step'>";
echo "<h4>Step $step_counter: File System Access</h4>";
try {
    $test_files = [
        'index.php',
        'includes/db_config.php',
        'includes/db_settings.php',
        'includes/security/security_manager.php'
    ];

    $missing_files = [];
    foreach ($test_files as $file) {
        if (file_exists($file)) {
            echo "<p>✅ $file exists</p>";
        } else {
            echo "<p>❌ $file missing</p>";
            $missing_files[] = $file;
        }
    }

    if (empty($missing_files)) {
        echo "<div class='success'>✅ All required files exist</div>";
        $steps[] = "File System - PASSED";
    } else {
        echo "<div class='error'>❌ Missing files: " . implode(', ', $missing_files) . "</div>";
        $steps[] = "File System - FAILED: Missing " . implode(', ', $missing_files);
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ FAILED: " . $e->getMessage() . "</div>";
    $steps[] = "File System - FAILED: " . $e->getMessage();
}
echo "</div>";
$step_counter++;

// Step 3: Test database configuration
echo "<div class='step'>";
echo "<h4>Step $step_counter: Database Configuration</h4>";
try {
    if (file_exists('includes/db_config.php')) {
        require_once 'includes/db_config.php';
        echo "<p>✅ Database config loaded</p>";

        // Test database connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        echo "<p>✅ Database connection established</p>";
        echo "<p>✅ Database: $DB_NAME on $DB_HOST</p>";
        $conn->close();

        echo "<div class='success'>✅ PASSED</div>";
        $steps[] = "Database Config - PASSED";
    } else {
        throw new Exception("Database config file missing");
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ FAILED: " . $e->getMessage() . "</div>";
    $steps[] = "Database Config - FAILED: " . $e->getMessage();
}
echo "</div>";
$step_counter++;

// Step 4: Test security manager
echo "<div class='step'>";
echo "<h4>Step $step_counter: Security Manager</h4>";
try {
    require_once 'includes/security/security_manager.php';
    $security = new SecurityManager();
    echo "<p>✅ Security Manager instantiated</p>";
    echo "<div class='success'>✅ PASSED</div>";
    $steps[] = "Security Manager - PASSED";
} catch (Exception $e) {
    echo "<div class='error'>❌ FAILED: " . $e->getMessage() . "</div>";
    echo "<div class='info'>File: " . $e->getFile() . " Line: " . $e->getLine() . "</div>";
    $steps[] = "Security Manager - FAILED: " . $e->getMessage();
}
echo "</div>";
$step_counter++;

// Step 5: Test template files
echo "<div class='step'>";
echo "<h4>Step $step_counter: Template Files</h4>";
try {
    $template_files = [
        'includes/templates/dynamic_header.php',
        'includes/templates/dynamic_footer.php',
        'includes/templates/static_header.php',
        'includes/templates/static_footer.php'
    ];

    foreach ($template_files as $template) {
        if (file_exists($template)) {
            echo "<p>✅ $template exists</p>";
        } else {
            echo "<p>⚠️ $template missing</p>";
        }
    }
    echo "<div class='success'>✅ Template files check complete</div>";
    $steps[] = "Template Files - PASSED";
} catch (Exception $e) {
    echo "<div class='error'>❌ FAILED: " . $e->getMessage() . "</div>";
    $steps[] = "Template Files - FAILED: " . $e->getMessage();
}
echo "</div>";
$step_counter++;

// Step 6: Test index.php loading
echo "<div class='step'>";
echo "<h4>Step $step_counter: Index.php Loading Test</h4>";
try {
    ob_start();
    include 'index.php';
    $output = ob_get_clean();

    if (strlen($output) > 0) {
        echo "<p>✅ Index.php loaded successfully</p>";
        echo "<p>✅ Output size: " . number_format(strlen($output)) . " characters</p>";

        // Show first 200 characters of output
        echo "<div class='info'>";
        echo "<h5>Output Preview (first 200 chars):</h5>";
        echo "<div class='code'>" . htmlspecialchars(substr($output, 0, 200)) . "...</div>";
        echo "</div>";

        echo "<div class='success'>✅ PASSED</div>";
        $steps[] = "Index.php Loading - PASSED";
    } else {
        echo "<div class='warning'>⚠️ Index.php loaded but produced no output</div>";
        $steps[] = "Index.php Loading - WARNING: No output";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ FAILED: " . $e->getMessage() . "</div>";
    echo "<div class='info'>Error in file: " . $e->getFile() . " at line " . $e->getLine() . "</div>";
    $steps[] = "Index.php Loading - FAILED: " . $e->getMessage();
}
echo "</div>";
$step_counter++;

// Step 7: Test session handling
echo "<div class='step'>";
echo "<h4>Step $step_counter: Session Handling</h4>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "<p>✅ Session started successfully</p>";
    } else {
        echo "<p>✅ Session already active</p>";
    }

    $_SESSION['test_session'] = 'working';
    echo "<p>✅ Session variables can be set</p>";
    echo "<div class='success'>✅ PASSED</div>";
    $steps[] = "Session Handling - PASSED";
} catch (Exception $e) {
    echo "<div class='error'>❌ FAILED: " . $e->getMessage() . "</div>";
    $steps[] = "Session Handling - FAILED: " . $e->getMessage();
}
echo "</div>";
$step_counter++;

// Step 8: Test directory permissions
echo "<div class='step'>";
echo "<h4>Step $step_counter: Directory Permissions</h4>";
try {
    $directories = ['logs', 'includes', 'includes/security', 'includes/templates', 'api'];
    $writable_dirs = 0;

    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            if (is_writable($dir)) {
                echo "<p>✅ $dir is writable</p>";
                $writable_dirs++;
            } else {
                echo "<p>⚠️ $dir is not writable</p>";
            }
        } else {
            echo "<p>⚠️ $dir does not exist</p>";
        }
    }

    if ($writable_dirs >= 3) {
        echo "<div class='success'>✅ Directory permissions OK</div>";
        $steps[] = "Directory Permissions - PASSED";
    } else {
        echo "<div class='warning'>⚠️ Some directories may not be writable</div>";
        $steps[] = "Directory Permissions - WARNING: $writable_dirs/" . count($directories) . " writable";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ FAILED: " . $e->getMessage() . "</div>";
    $steps[] = "Directory Permissions - FAILED: " . $e->getMessage();
}
echo "</div>";

echo "<div class='step'>";
echo "<h3>🎯 FINAL DIAGNOSIS</h3>";
echo "<div class='info'>";
echo "<h4>Test Results Summary:</h4>";
echo "<ul>";
foreach ($steps as $step) {
    echo "<li>$step</li>";
}
echo "</ul>";

$passed = count(array_filter($steps, function($step) {
    return strpos($step, 'PASSED') !== false;
}));
$total = count($steps);

echo "<h4>Overall Status: $passed/$total tests passed</h4>";

if ($passed == $total) {
    echo "<div class='success'>✅ ALL TESTS PASSED - System should be working</div>";
} elseif ($passed >= $total * 0.8) {
    echo "<div class='warning'>⚠️ MOST TESTS PASSED - Minor issues detected</div>";
} else {
    echo "<div class='error'>❌ MULTIPLE FAILURES - System has significant issues</div>";
}
echo "</div>";
echo "</div>";

echo "<div class='step'>";
echo "<h3>🛠️ RECOMMENDED SOLUTIONS</h3>";
echo "<div class='info'>";
echo "<p>Based on the test results above:</p>";
echo "<ol>";
echo "<li><strong>Check Missing Files:</strong> Ensure all required PHP files exist</li>";
echo "<li><strong>Database Connection:</strong> Verify database credentials and connectivity</li>";
echo "<li><strong>File Permissions:</strong> Make sure directories are writable</li>";
echo "<li><strong>Restart XAMPP:</strong> Restart Apache and MySQL services</li>";
echo "<li><strong>Clear Cache:</strong> Clear browser cache and try again</li>";
echo "</ol>";
echo "</div>";
echo "</div>";

echo "</div>
</body>
</html>";
?>
