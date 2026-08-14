<?php
$files = [
    'app/Services/ProgressiveRegistrationService.php',
    'app/Services/PayrollService.php',
    'app/Services/ResellPropertyService.php',
    'app/Services/CommissionService.php',
    'app/Services/NotificationService.php',
    'app/Services/SecurityService.php',
    'app/Services/FinanceService.php',
    'app/Services/AnalyticsService.php',
    'app/Services/AgentOrchestrator.php',
    'app/Services/OcrService.php',
    'app/Services/PropertyMarketplaceService.php',
];

$ok = 0; $fail = 0;
foreach ($files as $f) {
    $out = shell_exec("php -l $f 2>&1");
    if (strpos($out, 'No syntax errors') !== false) {
        echo "[OK]   $f\n";
        $ok++;
    } else {
        echo "[FAIL] $f: $out\n";
        $fail++;
    }
}
echo "\nResult: $ok OK / $fail FAIL\n";?>