<?php
/**
 * Enable Error Debug
 * 
 * Enable detailed error reporting to identify the root cause
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set error handler to capture all errors
set_error_handler(function($severity, $message, $file, $line) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px; border-radius: 5px;'>";
    echo "<h4 style='color: #721c24;'>Error Detected:</h4>";
    echo "<strong>Severity:</strong> $severity<br>";
    echo "<strong>Message:</strong> $message<br>";
    echo "<strong>File:</strong> $file<br>";
    echo "<strong>Line:</strong> $line<br>";
    echo "</div>";
    return true;
});

// Set exception handler
set_exception_handler(function($exception) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px; border-radius: 5px;'>";
    echo "<h4 style='color: #721c24;'>Exception Caught:</h4>";
    echo "<strong>Message:</strong> " . $exception->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
    echo "<strong>Trace:</strong><pre>" . $exception->getTraceAsString() . "</pre>";
    echo "</div>";
});

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>Error Debug - APS Dream Home</title>\n";
echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "</head>\n";
echo "<body>\n";

echo "<div class='container mt-4'>\n";
echo "<div class='card'>\n";
echo "<div class='card-header bg-danger text-white'>\n";
echo "<h2>🔍 Error Debug Mode Enabled</h2>\n";
echo "</div>\n";
echo "<div class='card-body'>\n";

echo "<div class='alert alert-info'>\n";
echo "<h4>🐛 Debug Information</h4>\n";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>\n";
echo "<p><strong>Error Reporting:</strong> " . error_reporting() . "</p>\n";
echo "<p><strong>Display Errors:</strong> " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</p>\n";
echo "</div>\n";

echo "<div class='alert alert-warning'>\n";
echo "<h4>🔧 Testing Bootstrap Loading</h4>\n";

try {
    echo "<p>Testing bootstrap.php loading...</p>\n";
    
    // Check if bootstrap exists
    $bootstrapPath = __DIR__ . '/config/bootstrap.php';
    if (file_exists($bootstrapPath)) {
        echo "<p>✅ bootstrap.php exists</p>\n";
        
        // Try to include bootstrap
        echo "<p>Attempting to include bootstrap.php...</p>\n";
        include $bootstrapPath;
        echo "<p>✅ bootstrap.php included successfully</p>\n";
        
        // Check if constants are defined
        echo "<p>Checking constants...</p>\n";
        if (defined('APP_NAME')) {
            echo "<p>✅ APP_NAME defined: " . APP_NAME . "</p>\n";
        } else {
            echo "<p>❌ APP_NAME not defined</p>\n";
        }
        
        if (defined('APP_PATH')) {
            echo "<p>✅ APP_PATH defined: " . APP_PATH . "</p>\n";
        } else {
            echo "<p>❌ APP_PATH not defined</p>\n";
        }
        
        // Test App class
        echo "<p>Testing App class...</p>\n";
        if (class_exists('App\Core\App')) {
            echo "<p>✅ App\\Core\\App class exists</p>\n";
            
            try {
                $app = \App\Core\App::getInstance();
                echo "<p>✅ App instance created</p>\n";
            } catch (Exception $e) {
                echo "<p>❌ App instance failed: " . $e->getMessage() . "</p>\n";
            }
        } else {
            echo "<p>❌ App\\Core\\App class not found</p>\n";
        }
        
    } else {
        echo "<p>❌ bootstrap.php not found</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Bootstrap test failed: " . $e->getMessage() . "</p>\n";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>\n";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>\n";
}

echo "</div>\n";

echo "<div class='alert alert-secondary'>\n";
echo "<h4>🔍 Testing Direct File Access</h4>\n";

// Test direct admin file access
$adminFiles = [
    'admin/dashboard.php',
    'admin/user_management.php',
    'admin/property_management.php',
    'admin/unified_key_management.php'
];

foreach ($adminFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    echo "<p><strong>$file:</strong> ";
    if (file_exists($filePath)) {
        echo "✅ Exists";
        
        // Try to get file info
        $fileSize = filesize($filePath);
        echo " (Size: $fileSize bytes)";
        
        // Check if it's readable
        if (is_readable($filePath)) {
            echo " ✅ Readable";
        } else {
            echo " ❌ Not readable";
        }
    } else {
        echo "❌ Not found";
    }
    echo "</p>\n";
}

echo "</div>\n";

echo "<div class='alert alert-dark'>\n";
echo "<h4>🔍 Testing MVC Components</h4>\n";

$mvcFiles = [
    'app/Controllers/AdminController.php',
    'app/Models/User.php',
    'app/Models/Property.php',
    'app/Core/Security.php',
    'app/Core/Validator.php',
    'app/Core/Database/Model.php'
];

foreach ($mvcFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    echo "<p><strong>$file:</strong> ";
    if (file_exists($filePath)) {
        echo "✅ Exists";
        
        // Try to check syntax
        $output = shell_exec("php -l \"$filePath\" 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo " ✅ Valid syntax";
        } else {
            echo " ❌ Syntax error: $output";
        }
    } else {
        echo "❌ Not found";
    }
    echo "</p>\n";
}

echo "</div>\n";

echo "<div class='alert alert-primary'>\n";
echo "<h4>🔍 Testing Database Connection</h4>\n";

try {
    $dbConfigPath = __DIR__ . '/config/database.php';
    if (file_exists($dbConfigPath)) {
        echo "<p>✅ database.php exists</p>\n";
        
        // Try to include database config
        include $dbConfigPath;
        echo "<p>✅ database.php included</p>\n";
        
        // Check if Database class exists
        if (class_exists('App\Core\Database\Database')) {
            echo "<p>✅ Database class exists</p>\n";
            
            try {
                $db = new \App\Core\Database\Database();
                echo "<p>✅ Database instance created</p>\n";
            } catch (Exception $e) {
                echo "<p>❌ Database instance failed: " . $e->getMessage() . "</p>\n";
            }
        } else {
            echo "<p>❌ Database class not found</p>\n";
        }
    } else {
        echo "<p>❌ database.php not found</p>\n";
    }
} catch (Exception $e) {
    echo "<p>❌ Database test failed: " . $e->getMessage() . "</p>\n";
}

echo "</div>\n";

echo "</div>\n"; // card-body
echo "</div>\n"; // card
echo "</div>\n"; // container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>\n";
echo "</body>\n";
echo "</html>\n";
?>
