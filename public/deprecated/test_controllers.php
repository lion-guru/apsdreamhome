<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/app.php';

try {
    $controller = new App\Http\Controllers\Property\PropertyController();
    echo "PropertyController loaded successfully\n";
} catch (Exception $e) {
    echo "Error loading PropertyController: " . $e->getMessage() . "\n";
}

try {
    $homeController = new App\Http\Controllers\HomeController();
    echo "HomeController loaded successfully\n";
} catch (Exception $e) {
    echo "Error loading HomeController: " . $e->getMessage() . "\n";
}
?>
