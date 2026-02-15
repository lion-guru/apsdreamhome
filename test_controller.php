<?php
require 'bootstrap.php';
use App\Http\Controllers\User\DashboardController;

try {
    $controller = new DashboardController();
    echo "DashboardController instantiated successfully.\n";
    if (method_exists($controller, 'settings')) {
        echo "settings() method exists.\n";
    } else {
        echo "settings() method NOT found.\n";
    }
} catch (Exception $e) {
    echo "Error instantiating DashboardController: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
