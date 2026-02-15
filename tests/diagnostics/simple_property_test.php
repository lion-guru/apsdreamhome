<?php
/**
 * Simple test for PropertyController
 */

require_once 'config/bootstrap.php';

try {
    $router = new App\Core\Router();
    $router->dispatch('properties');
    echo 'Property listing loaded successfully!';
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine();
}
?>
