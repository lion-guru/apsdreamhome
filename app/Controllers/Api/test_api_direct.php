<?php
/**
 * Test API Direct Access
 */

// Simulate API request
$_SERVER['REQUEST_URI'] = '/apsdreamhome/api/';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "Testing API Root:\n";
include 'api/index.php';

echo "\n\nTesting API Health:\n";
$_SERVER['REQUEST_URI'] = '/apsdreamhome/api/health';
include 'api/index.php';

echo "\n\nTesting API Properties:\n";
$_SERVER['REQUEST_URI'] = '/apsdreamhome/api/properties';
include 'api/index.php';
?>
