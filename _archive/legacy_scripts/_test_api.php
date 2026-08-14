<?php
require_once 'vendor/autoload.php';
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$controllerFile = 'app/Http/Controllers/Api/NewFeaturesApiController.php';
require_once $controllerFile;

$controller = new App\Http\Controllers\Api\NewFeaturesApiController();

try {
    $r = new ReflectionMethod($controller, 'resellListPublic');
    echo "resellListPublic method exists\n";

    // Set up superglobals
    $_GET = ['limit' => 5];
    $_POST = [];
    $_SESSION = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';

    ob_start();
    $controller->resellListPublic();
    $output = ob_get_clean();
    echo "Output: $output\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}?>