<?php
/**
 * Simple test for AdminController
 */

require_once 'config/bootstrap.php';

try {
    $_SESSION['user_role'] = 'admin';
    $router = new App\Core\Router();
    $router->dispatch('admin');
    echo 'Admin dashboard loaded successfully!';
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine();
}
?>
