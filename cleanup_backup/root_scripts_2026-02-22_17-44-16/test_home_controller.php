<?php

// Define constants
define('APP_ROOT', __DIR__);
define('BASE_URL', 'http://localhost/apsdreamhome/');
define('APP_NAME', 'APS Dream Home');

// Mock necessary classes
class App {
    public static function getInstance($path) {
        return new self();
    }
    public function request() { return new Request(); }
    public function response() { return new Response(); }
    public function session() { return new Session(); }
}

class Request {
    public $headers;
    public function __construct() { $this->headers = new Headers(); }
}

class Headers {
    public function get($key, $default = null) { return $default; }
}

class Response {
    public static function redirect($url, $code) { echo "Redirect to $url"; }
}

class Session {
    public function getFlashBag() { return new FlashBag(); }
}

class FlashBag {
    public function all() { return []; }
    public function clear() {}
}

// Autoloader simulation
require_once 'app/Core/Controller.php';
require_once 'app/Core/View/View.php';
require_once 'app/Http/Controllers/BaseController.php';
require_once 'app/Http/Controllers/HomeController.php';
require_once 'app/Models/Property.php';

// Mock DB for Property model
namespace App\Models;
class Property {
    public function getFeaturedProperties() {
        return [
            ['title' => 'Test Property 1', 'price' => 5000000, 'location' => 'Gorakhpur', 'type' => 'House', 'image' => 'test.jpg', 'bedrooms' => 3, 'bathrooms' => 2, 'area' => 1200, 'id' => 1],
            ['title' => 'Test Property 2', 'price' => 3500000, 'location' => 'Lucknow', 'type' => 'Apartment', 'image' => 'test2.jpg', 'bedrooms' => 2, 'bathrooms' => 1, 'area' => 900, 'id' => 2]
        ];
    }
}

namespace App\Core\View;
class View {
    public function render($view, $data) {
        return "Rendered View: $view with data count: " . count($data);
    }
    public function layout($layout) {}
}

// Run test
use App\Http\Controllers\HomeController;

echo "Testing HomeController::index()...\n";
$controller = new HomeController();

// Start output buffering to capture echo
ob_start();
$result = $controller->index();
$output = ob_get_clean();

echo "Output length (should be 0): " . strlen($output) . "\n";
echo "Return value: " . $result . "\n";

if (strlen($output) === 0 && !empty($result)) {
    echo "SUCCESS: No echo, content returned.\n";
} else {
    echo "FAILURE: Echo detected or no return value.\n";
}
