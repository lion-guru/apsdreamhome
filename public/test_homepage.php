<?php
/**
 * Test Homepage Direct Loading
 */

// Define constants
define('APS_ROOT', dirname(__DIR__));
define('BASE_URL', 'http://localhost:8000');

// Include the homepage view directly
try {
    $page_title = 'Welcome to APS Dream Home';
    $page_description = 'Discover premium properties and find your dream home with APS Dream Home';
    
    // Load the homepage view
    $viewFile = APS_ROOT . '/app/views/pages/index.php';
    if (file_exists($viewFile)) {
        include $viewFile;
    } else {
        echo "<h1>View file not found</h1>";
        echo "<p>Looking for: " . $viewFile . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h1>Error Loading Homepage</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}
?>
