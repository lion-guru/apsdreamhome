<?php
// Test NocRegistryService directly
require_once 'C:/xampp/htdocs/apsdreamhome/app/Services/NocRegistryService.php';

try {
    $service = new \App\Services\NocRegistryService(null);
    $stats = $service->getDashboardStats();
    echo "Stats: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n";
    
    $nocs = $service->listNocs();
    echo "NOCs count: " . count($nocs) . "\n";
    
    $regs = $service->listRegistries();
    echo "Registries count: " . count($regs) . "\n";
    
    echo "SUCCESS!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}