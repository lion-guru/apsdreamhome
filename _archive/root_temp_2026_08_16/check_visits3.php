<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Check menu items for visits
$rows = $db->query("SELECT id, name, url, is_active, section FROM admin_menu_items WHERE url LIKE '%visits%'")->fetchAll(\PDO::FETCH_ASSOC);
print_r($rows);

// Check the VisitController index method
echo "\nChecking VisitController...\n";
try {
    require_once 'app/Http/Controllers/Admin/VisitController.php';
    $controller = new \App\Http\Controllers\Admin\VisitController();
    echo "VisitController instantiated OK\n";
} catch (\Throwable $e) {
    echo "VisitController error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}