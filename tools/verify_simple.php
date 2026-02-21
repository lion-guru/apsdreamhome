<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Http\Controllers\AssociateController;

echo "Start\n";
try {
    $c = new AssociateController();
    echo "AssociateController instantiated.\n";
    if (method_exists($c, 'register')) echo "register method exists.\n";
    if (method_exists($c, 'dashboard')) echo "dashboard method exists.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
