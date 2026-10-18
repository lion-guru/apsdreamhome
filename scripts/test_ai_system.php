<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Http\Controllers\Admin\AISystemController;

$controller = new AISystemController();
// Test the qualifier
echo "Testing AI System Qualifier...\n";