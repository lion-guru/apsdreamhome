<?php
require_once 'vendor/autoload.php';
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$services = [
    'ProgressiveRegistrationService' => 'app/Services/ProgressiveRegistrationService.php',
    'PayrollService' => 'app/Services/PayrollService.php',
    'ResellPropertyService' => 'app/Services/ResellPropertyService.php',
    'CommissionService' => 'app/Services/CommissionService.php',
    'NotificationService' => 'app/Services/NotificationService.php',
    'SecurityService' => 'app/Services/SecurityService.php',
    'FinanceService' => 'app/Services/FinanceService.php',
    'AnalyticsService' => 'app/Services/AnalyticsService.php',
    'AgentOrchestrator' => 'app/Services/AgentOrchestrator.php',
    'OcrService' => 'app/Services/OcrService.php',
    'PropertyMarketplaceService' => 'app/Services/PropertyMarketplaceService.php',
];

$ok = 0; $fail = 0;
foreach ($services as $class => $file) {
    try {
        require_once $file;
        $fqcn = "App\\Services\\$class";
        $svc = new $fqcn($pdo);
        $method = new ReflectionMethod($svc, '__construct');
        echo "[OK]   $class loaded - " . count(get_class_methods($svc)) . " methods\n";
        $ok++;
    } catch (Throwable $e) {
        echo "[FAIL] $class: " . $e->getMessage() . "\n";
        $fail++;
    }
}
echo "\nResult: $ok OK / $fail FAIL\n";?>