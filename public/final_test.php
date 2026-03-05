<?php
// Final test - bypass URI processing
require_once '../routes/index.php';

$router = new Router();

// Add test route
$router->get('/properties', function() {
    echo "SUCCESS: Properties route working!";
    exit;
});

// Test direct dispatch
echo "Direct dispatch test:<br>";
$router->dispatch();
?>
