<?php
/**
 * Debug Bootstrap
 * 
 * Debug the bootstrap initialization step by step
 */

// Enable maximum error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>Bootstrap Debug - APS Dream Home</title>\n";
echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "</head>\n";
echo "<body>\n";

echo "<div class='container mt-4'>\n";
echo "<div class='card'>\n";
echo "<div class='card-header bg-danger text-white'>\n";
echo "<h2>🔍 Bootstrap Debug - Step by Step</h2>\n";
echo "</div>\n";
echo "<div class='card-body'>\n";

echo "<div class='alert alert-info'>\n";
echo "<h4>🐛 Debug Information</h4>\n";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>\n";
echo "<p><strong>Error Reporting:</strong> " . error_reporting() . "</p>\n";
echo "<p><strong>Display Errors:</strong> " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</p>\n";
echo "</div>\n";

// Step 1: Test basic constants
echo "<div class='alert alert-primary'>\n";
echo "<h5>Step 1: Testing Constants Definition</h5>\n";

try {
    echo "<p>Defining APP_NAME...</p>\n";
    if (!defined('APP_NAME')) {
        define('APP_NAME', 'APSDreamHome');
        echo "<p>✅ APP_NAME defined: " . APP_NAME . "</p>\n";
    } else {
        echo "<p>✅ APP_NAME already defined: " . APP_NAME . "</p>\n";
    }
    
    echo "<p>Defining APP_ROOT...</p>\n";
    if (!defined('APP_ROOT')) {
        define('APP_ROOT', dirname(__DIR__));
        echo "<p>✅ APP_ROOT defined: " . APP_ROOT . "</p>\n";
    } else {
        echo "<p>✅ APP_ROOT already defined: " . APP_ROOT . "</p>\n";
    }
    
    echo "<p>Defining other paths...</p>\n";
    $paths = [
        'CONFIG_PATH' => APP_ROOT . '/config',
        'APP_PATH' => APP_ROOT . '/app',
        'CORE_PATH' => APP_ROOT . '/app/core'
    ];
    
    foreach ($paths as $const => $value) {
        if (!defined($const)) {
            define($const, $value);
            echo "<p>✅ $const defined: $value</p>\n";
        } else {
            echo "<p>✅ $const already defined: " . constant($const) . "</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Constants definition failed: " . $e->getMessage() . "</p>\n";
}

echo "</div>\n";

// Step 2: Test file existence
echo "<div class='alert alert-secondary'>\n";
echo "<h5>Step 2: Testing File Existence</h5>\n";

$requiredFiles = [
    'Autoloader.php' => CORE_PATH . '/Autoloader.php',
    'App.php' => APP_PATH . '/Core/App.php',
    'Database.php' => APP_PATH . '/Core/Database/Database.php'
];

foreach ($requiredFiles as $name => $file) {
    echo "<p><strong>$name:</strong> ";
    if (file_exists($file)) {
        echo "✅ Exists";
        if (is_readable($file)) {
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

// Step 3: Test Autoloader inclusion
echo "<div class='alert alert-warning'>\n";
echo "<h5>Step 3: Testing Autoloader Inclusion</h5>\n";

try {
    $autoloaderFile = CORE_PATH . '/Autoloader.php';
    echo "<p>Including Autoloader.php...</p>\n";
    
    if (file_exists($autoloaderFile)) {
        require_once $autoloaderFile;
        echo "<p>✅ Autoloader.php included successfully</p>\n";
        
        // Test if Autoloader class exists
        if (class_exists('App\Core\Autoloader')) {
            echo "<p>✅ App\\Core\\Autoloader class exists</p>\n";
            
            // Test autoloader instantiation
            $autoloader = \App\Core\Autoloader::getInstance();
            echo "<p>✅ Autoloader instance created</p>\n";
            
            // Test autoloader registration
            if (method_exists($autoloader, 'register')) {
                $autoloader->register();
                echo "<p>✅ Autoloader registered</p>\n";
            } else {
                echo "<p>❌ Autoloader register method not found</p>\n";
            }
        } else {
            echo "<p>❌ App\\Core\\Autoloader class not found</p>\n";
        }
    } else {
        echo "<p>❌ Autoloader.php not found at: $autoloaderFile</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Autoloader test failed: " . $e->getMessage() . "</p>\n";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>\n";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>\n";
}

echo "</div>\n";

// Step 4: Test App class
echo "<div class='alert alert-success'>\n";
echo "<h5>Step 4: Testing App Class</h5>\n";

try {
    echo "<p>Including App.php...</p>\n";
    $appFile = APP_PATH . '/Core/App.php';
    
    if (file_exists($appFile)) {
        require_once $appFile;
        echo "<p>✅ App.php included successfully</p>\n";
        
        // Test if App class exists
        if (class_exists('App\Core\App')) {
            echo "<p>✅ App\\Core\\App class exists</p>\n";
            
            // Test App instantiation
            echo "<p>Creating App instance...</p>\n";
            $app = \App\Core\App::getInstance();
            echo "<p>✅ App instance created successfully</p>\n";
            
            // Test App methods
            if (method_exists($app, 'run')) {
                echo "<p>✅ App::run() method exists</p>\n";
            } else {
                echo "<p>❌ App::run() method not found</p>\n";
            }
            
            if (method_exists($app, 'db')) {
                echo "<p>✅ App::db() method exists</p>\n";
            } else {
                echo "<p>❌ App::db() method not found</p>\n";
            }
            
        } else {
            echo "<p>❌ App\\Core\\App class not found</p>\n";
        }
    } else {
        echo "<p>❌ App.php not found at: $appFile</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p>❌ App class test failed: " . $e->getMessage() . "</p>\n";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>\n";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>\n";
    echo "<p><strong>Trace:</strong><pre>" . $e->getTraceAsString() . "</pre></p>\n";
}

echo "</div>\n";

// Step 5: Test Database class
echo "<div class='alert alert-info'>\n";
echo "<h5>Step 5: Testing Database Class</h5>\n";

try {
    echo "<p>Testing Database class...</p>\n";
    
    if (class_exists('App\Core\Database\Database')) {
        echo "<p>✅ App\\Core\\Database\\Database class exists</p>\n";
        
        // Test Database instantiation
        echo "<p>Creating Database instance...</p>\n";
        $db = new \App\Core\Database\Database();
        echo "<p>✅ Database instance created successfully</p>\n";
        
    } else {
        echo "<p>❌ App\\Core\\Database\\Database class not found</p>\n";
        
        // Try to include it
        $dbFile = APP_PATH . '/Core/Database/Database.php';
        if (file_exists($dbFile)) {
            echo "<p>Including Database.php...</p>\n";
            require_once $dbFile;
            
            if (class_exists('App\Core\Database\Database')) {
                echo "<p>✅ Database class exists after inclusion</p>\n";
            } else {
                echo "<p>❌ Database class still not found</p>\n";
            }
        } else {
            echo "<p>❌ Database.php not found</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database test failed: " . $e->getMessage() . "</p>\n";
}

echo "</div>\n";

// Step 6: Test Model class
echo "<div class='alert alert-dark'>\n";
echo "<h5>Step 6: Testing Model Class</h5>\n";

try {
    echo "<p>Testing Model class...</p>\n";
    
    if (class_exists('App\Core\Database\Model')) {
        echo "<p>✅ App\\Core\\Database\\Model class exists</p>\n";
        
        // Test Model instantiation (abstract class test)
        echo "<p>Testing Model class structure...</p>\n";
        $reflection = new ReflectionClass('App\Core\Database\Model');
        echo "<p>✅ Model class reflection successful</p>\n";
        echo "<p>✅ Model is abstract: " . ($reflection->isAbstract() ? 'YES' : 'NO') . "</p>\n";
        
        // Test methods
        $methods = ['offsetExists', 'offsetGet', 'offsetSet', 'offsetUnset', 'jsonSerialize'];
        foreach ($methods as $method) {
            if ($reflection->hasMethod($method)) {
                echo "<p>✅ Method $method exists</p>\n";
            } else {
                echo "<p>❌ Method $method not found</p>\n";
            }
        }
        
    } else {
        echo "<p>❌ App\\Core\\Database\\Model class not found</p>\n";
        
        // Try to include it
        $modelFile = APP_PATH . '/Core/Database/Model.php';
        if (file_exists($modelFile)) {
            echo "<p>Including Model.php...</p>\n";
            require_once $modelFile;
            
            if (class_exists('App\Core\Database\Model')) {
                echo "<p>✅ Model class exists after inclusion</p>\n";
            } else {
                echo "<p>❌ Model class still not found</p>\n";
            }
        } else {
            echo "<p>❌ Model.php not found</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Model test failed: " . $e->getMessage() . "</p>\n";
}

echo "</div>\n";

echo "</div>\n"; // card-body
echo "</div>\n"; // card
echo "</div>\n"; // container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>\n";
echo "</body>\n";
echo "</html>\n";
?>
