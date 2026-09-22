<?php
require_once __DIR__ . '/../config/bootstrap.php';
$service = new App\Services\IoTService();
print_r($service->getStats());