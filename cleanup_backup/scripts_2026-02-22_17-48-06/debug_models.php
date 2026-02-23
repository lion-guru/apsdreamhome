<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);
define('APP_NAME', 'APS Dream Home');
define('BASE_URL', 'http://localhost/');

require_once 'app/core/autoload.php';
require_once 'app/Helpers/env.php';

use App\Models\News;
use App\Models\Feedback;
use App\Http\Controllers\Public\PageController;

echo "Debugging Models...\n";

// Test News
echo "Testing News::getPublished(3)...\n";
try {
    $news = News::getPublished(3);
    echo "News count: " . count($news) . "\n";
} catch (Throwable $e) {
    echo "News Error: " . $e->getMessage() . "\n";
}

// Test Feedback
echo "Testing Feedback::query()...\n";
try {
    $feedback = Feedback::query()->limit(1)->get();
    echo "Feedback count: " . count($feedback) . "\n";
} catch (Throwable $e) {
    echo "Feedback Error: " . $e->getMessage() . "\n";
}

// Test PageController::index
echo "Testing PageController::index()...\n";
try {
    $controller = new PageController();
    // We can't easily test index() because it renders view and exits or outputs.
    // But we can check if it throws exception.
    ob_start();
    $controller->index();
    ob_end_clean();
    echo "PageController::index() finished.\n";
} catch (Throwable $e) {
    echo "PageController::index() Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
