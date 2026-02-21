<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/core/autoload.php';

echo "Testing Autoloader...\n";
$className = 'App\Http\Controllers\HomeController';

if (class_exists($className)) {
    echo "Class $className found!\n";
} else {
    echo "Class $className NOT found.\n";
    
    // Debugging path resolution
    $loader = \App\Core\Autoloader::getInstance();
    // Use reflection to inspect private properties if needed, or just manual check
    $baseDir = APP_ROOT . '/app/';
    $relativeClass = 'Http/Controllers/HomeController';
    $file = $baseDir . $relativeClass . '.php';
    
    echo "Expected path: $file\n";
    if (file_exists($file)) {
        echo "File exists at expected path.\n";
        require_once $file;
        if (class_exists($className)) {
            echo "Class found after manual require. Autoloader failed to require it?\n";
        } else {
            echo "Class NOT found after manual require. Check namespace/classname in file.\n";
        }
    } else {
        echo "File does NOT exist at expected path.\n";
    }
}
