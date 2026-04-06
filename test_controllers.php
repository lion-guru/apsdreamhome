<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
    
    // Try to load the controller
    $controllerFile = 'C:/xampp/htdocs/apsdreamhome/app/Http/Controllers/Admin/BookingController.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        echo "BookingController file loaded\n";
        
        // Check if class exists
        if (class_exists('App\Http\Controllers\Admin\BookingController')) {
            echo "BookingController class exists\n";
        } else {
            echo "BookingController class NOT found\n";
        }
    } else {
        echo "BookingController file NOT found\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
