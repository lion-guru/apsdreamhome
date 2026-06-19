<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
require $root . '/app/core/Autoloader.php';

$autoloader = new App\Core\Autoloader();
$autoloader->register();

try {
    $svc = new App\Services\Auth\ApiAuthService();
    echo "ApiAuthService created OK\n";
    
    $result = $svc->login('customer1@apsdreamhome.com', 'Test1234');
    print_r($result);
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
