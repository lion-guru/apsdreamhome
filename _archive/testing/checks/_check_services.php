<?php
// Check which services are missing that controllers depend on
$controllers = [
    'app/Http/Controllers/SecurityController.php',
    'app/Http/Controllers/EventController.php',
    'app/Http/Controllers/PerformanceController.php',
    'app/Http/Controllers/MarketingController.php',
    'app/Http/Controllers/LocalizationController.php',
    'app/Http/Controllers/FarmerController.php',
    'app/Http/Controllers/LandController.php',
    'app/Http/Controllers/CustomFeatures/CustomFeaturesController.php',
    'app/Http/Controllers/Utility/AlertController.php',
    'app/Http/Controllers/Communication/MediaController.php',
    'app/Http/Controllers/Communication/SmsController.php',
];

echo "=== CONTROLLER + SERVICE EXISTENCE ===\n\n";
foreach ($controllers as $ctrl) {
    if (!file_exists($ctrl)) {
        echo "NOT FOUND: $ctrl\n";
        continue;
    }
    
    $content = file_get_contents($ctrl);
    // Find all 'use App\...' statements
    preg_match_all('/use (App\\\\[A-Za-z\\\\]+);/', $content, $uses);
    
    $ctrlName = basename($ctrl);
    echo "FILE: $ctrlName\n";
    foreach ($uses[1] as $class) {
        // Convert namespace to file path
        $path = 'app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        $exists = file_exists($path);
        if (!$exists) {
            echo "  MISSING SERVICE: $class => $path\n";
        }
    }
    echo "\n";
}

// Also scan all services directory  
echo "\n=== SERVICES DIRECTORY ===\n";
$serviceCount = 0;
if (is_dir('app/Services')) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Services'));
    foreach ($it as $f) {
        if ($f->getExtension() === 'php') $serviceCount++;
    }
}
echo "Total service files: $serviceCount\n";

// Check if SecurityService exists
$toCheck = [
    'App\Services\SecurityService' => 'app/Services/SecurityService.php',
    'App\Services\Security\SecurityService' => 'app/Services/Security/SecurityService.php',
    'App\Services\EventService' => 'app/Services/EventService.php',
    'App\Services\EventBus' => 'app/Services/EventBus.php',
    'App\Services\Performance\PerformanceCacheService' => 'app/Services/Performance/PerformanceCacheService.php',
    'App\Services\MarketingService' => 'app/Services/MarketingService.php',
];

echo "\n=== KEY SERVICE CHECKS ===\n";
foreach ($toCheck as $class => $path) {
    echo (file_exists($path) ? "  EXISTS" : "MISSING") . ": $class\n";
}
