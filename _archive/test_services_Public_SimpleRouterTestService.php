<?php
// Simple router test
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "<br>";

require_once '../routes/index.php';

$router = new Router();

// Add a simple test route
$router->get('/simple-test', function() {
    echo "Simple router working!";
    exit;
});

// Dispatch
$router->dispatch();
?>
