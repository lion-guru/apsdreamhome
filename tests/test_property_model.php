<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Core/Autoloader.php';
$loader = \App\Core\Autoloader::getInstance();
$loader->addNamespace('App', APP_ROOT . '/app');
$loader->register();

// Need App for Database
require_once APP_ROOT . '/app/Core/App.php';
// Database singleton depends on App config usually, but Database::getInstance() can read config if needed?
// Database.php uses `config/database.php` if loaded?
// Let's load config manually
$config = require APP_ROOT . '/config/database.php';
// We need to inject config into App or Database manually for this test
// But Database::getInstance() takes config array as argument.
$db = \App\Core\Database::getInstance($config['database']);

echo "Testing Property::getFeaturedProperties()...\n";
$properties = \App\Models\Property::getFeaturedProperties();
echo "Found " . count($properties) . " featured properties.\n";

foreach ($properties as $p) {
    echo " - " . $p->title . " (" . $p->status . ")\n";
}
