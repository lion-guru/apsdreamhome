<?php
require_once 'C:/xampp/htdocs/apsdreamhome/app/Core/Autoloader.php';

$service = new \App\Services\Land\ColonyPricingService();
$result = $service->calculateColonyPricing(6);
print_r($result);