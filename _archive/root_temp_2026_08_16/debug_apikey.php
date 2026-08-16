<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';
require_once 'app/Services/ApiKeyService.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Check if api_keys table exists
$r = $db->query("SHOW TABLES LIKE 'api_keys'")->fetch();
echo "api_keys table: " . ($r ? 'EXISTS' : 'MISSING') . PHP_EOL;

// Test ApiKeyService
try {
    $svc = new \App\Services\ApiKeyService($db);
    $keys = $svc->list();
    echo "ApiKeyService::list() returned " . count($keys) . " keys\n";
    
    $stats = $svc->getStats();
    print_r($stats);
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}