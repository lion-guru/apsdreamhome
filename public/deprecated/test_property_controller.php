<?php
// Direct test of PropertyController
require_once '../app/Core/Controller.php';
require_once '../app/Http/Controllers/BaseController.php';
require_once '../app/Http/Controllers/Property/PropertyController.php';

$controller = new \App\Http\Controllers\Property\PropertyController();

echo "PropertyController loaded successfully!<br>";

// Test the index method
if (method_exists($controller, 'index')) {
    echo "Index method exists!<br>";
    $controller->index();
} else {
    echo "Index method NOT found!<br>";
}
?>
