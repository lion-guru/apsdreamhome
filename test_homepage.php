<?php
/**
 * Test the new MVC structure
 */

require_once 'config/bootstrap.php';

try {
    $router = new App\Core\Router();
    $router->dispatch('home');
    echo 'SUCCESS: Homepage loaded successfully!';
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
    echo "\nFile: " . $e->getFile() . " Line: " . $e->getLine();
}
?>
