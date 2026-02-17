<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting verification...\n";

// Adjust paths if needed
$appRoot = dirname(__DIR__);

// Load the autoloader - this should now load helpers.php which defines class_basename
if (file_exists($appRoot . '/app/core/autoload.php')) {
    require_once $appRoot . '/app/core/autoload.php';
    
    // Initialize the autoloader manually since we're not using index.php
    $autoloader = \App\Core\Autoloader::getInstance();
    $autoloader->register();
    $autoloader->addNamespace('App\\', $appRoot . '/app/');
} else {
    die("Autoloader not found at " . $appRoot . '/app/core/autoload.php');
}

use App\Models\ConsolidatedProperty;
use App\Models\ConsolidatedUser;
use App\Core\Database\Relations\Pivot;

echo "Classes loaded via autoloader.\n";

try {
    echo "Checking class_basename function...\n";
    if (function_exists('class_basename')) {
        echo "class_basename exists.\n";
        echo "class_basename(ConsolidatedProperty::class) = " . class_basename(ConsolidatedProperty::class) . "\n";
    } else {
        echo "class_basename does NOT exist.\n";
    }

    echo "Instantiating ConsolidatedProperty...\n";
    $property = new ConsolidatedProperty();
    echo "ConsolidatedProperty instantiated successfully.\n";

    echo "Instantiating ConsolidatedUser...\n";
    $user = new ConsolidatedUser();
    echo "ConsolidatedUser instantiated successfully.\n";

    echo "Testing Pivot::getTable()...\n";
    try {
        $table = Pivot::getTable();
        echo "Pivot::getTable() returned: " . $table . "\n";
    } catch (\Throwable $e) {
        echo "Pivot::getTable() threw exception: " . $e->getMessage() . "\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
