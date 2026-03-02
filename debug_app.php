<?php
/**
 * APS Dream Home - App Class Debug
 * Test App class construction step by step
 */

echo "<h2>🔧 App Class Debug</h2>";

try {
    echo "<p><strong>Step 1:</strong> Testing BASE_PATH...</p>";
    if (!defined('BASE_PATH')) {
        define('BASE_PATH', dirname(__DIR__));
    }
    echo "<p>BASE_PATH: " . BASE_PATH . "</p>";
    
    echo "<p><strong>Step 2:</strong> Testing autoloader...</p>";
    if (file_exists(BASE_PATH . '/app/core/autoload.php')) {
        require_once BASE_PATH . '/app/core/autoload.php';
        echo "<p style='color: green;'>Autoloader: LOADED</p>";
    } else {
        echo "<p style='color: red;'>Autoloader: NOT FOUND at " . BASE_PATH . "/app/core/autoload.php</p>";
        throw new Exception("Autoloader file not found");
    }
    
    echo "<p><strong>Step 3:</strong> Testing App class...</p>";
    if (file_exists(BASE_PATH . '/app/core/App.php')) {
        require_once BASE_PATH . '/app/core/App.php';
        echo "<p style='color: green;'>App class: LOADED</p>";
    } else {
        echo "<p style='color: red;'>App class: NOT FOUND at " . BASE_PATH . "/app/core/App.php</p>";
        throw new Exception("App class file not found");
    }
    
    echo "<p><strong>Step 4:</strong> Testing configuration files...</p>";
    $configFiles = [
        'config/database.php',
        'config/app.php',
        'config/application.php'
    ];
    
    foreach ($configFiles as $configFile) {
        $fullPath = BASE_PATH . '/' . $configFile;
        if (file_exists($fullPath)) {
            echo "<p style='color: green;'>Config file $configFile: EXISTS</p>";
        } else {
            echo "<p style='color: orange;'>Config file $configFile: NOT FOUND</p>";
        }
    }
    
    echo "<p><strong>Step 5:</strong> Testing database connection...</p>";
    try {
        $conn = new mysqli("localhost", "root", "", "apsdreamhome");
        echo "<p style='color: green;'>Database connection: SUCCESS</p>";
        
        // Test table count
        $result = $conn->query("SHOW TABLES");
        $tableCount = $result->num_rows;
        echo "<p>Database tables: $tableCount found</p>";
        
        $conn->close();
    } catch (Exception $e) {
        echo "<p style='color: red;'>Database connection: FAILED - " . $e->getMessage() . "</p>";
    }
    
    echo "<p><strong>Step 6:</strong> Testing App instantiation...</p>";
    $app = new App();
    echo "<p style='color: green;'>App instantiation: SUCCESS</p>";
    
    echo "<p><strong>Step 7:</strong> Testing App run...</p>";
    $result = $app->run();
    echo "<p style='color: green;'>App run: SUCCESS</p>";
    echo "<p>App run result: " . print_r($result, true) . "</p>";
    
} catch (Error $e) {
    echo "<p style='color: red;'>FATAL ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><strong>🎯 App Class Debug Complete!</strong></p>";
echo "<p><small>If you see this page, the debugging process completed.</small></p>";
?>
