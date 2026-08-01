<?php
chdir(dirname(__DIR__));
$files = [
    'app/Services/LiveChatService.php',
    'app/Services/OcrService.php',
    'app/Services/Queue/QueueService.php',
    'app/Services/TenantService.php',
    'app/Services/Analytics/AdvancedAnalyticsService.php',
    'app/Services/SaaSBillingService.php',
];
foreach ($files as $f) {
    $c = file_get_contents($f);
    echo basename($f) . ": ";
    echo "trait:" . (strpos($c, 'ServiceTenantTrait') !== false ? 'yes' : 'no');
    echo " ctx:" . (strpos($c, 'TenantContext') !== false ? 'yes' : 'no');
    echo " tenant_id:" . substr_count($c, 'tenant_id');
    echo " tenantSql:" . (strpos($c, 'tenantSql') !== false ? 'yes' : 'no');
    echo "\n";
}
