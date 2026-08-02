<?php
function check($file) {
    if (!file_exists($file)) { echo basename($file) . ": NOT FOUND\n"; return; }
    $c = file_get_contents($file);
    $hasTrait = strpos($c, 'ServiceTenantTrait') !== false || strpos($c, 'TenantAwareTrait') !== false;
    $tidCount = substr_count($c, 'tenant_id');
    $prepareCount = substr_count($c, '->prepare');
    if ($prepareCount > 0 && $tidCount < 3) {
        echo basename($file) . ": prep:" . $prepareCount . " tid:" . $tidCount . " trait:" . ($hasTrait?'Y':'N') . "\n";
    }
}

$dirs = ['app/Services', 'app/Services/Communication', 'app/Services/Legal', 'app/Services/Loan',
         'app/Services/Finance', 'app/Services/Sales', 'app/Services/MLM', 'app/Services/AI',
         'app/Services/Voice', 'app/Services/Operations', 'app/Services/Marketing', 'app/Services/Security',
         'app/Services/CRM', 'app/Services/Events', 'app/Services/Performance', 'app/Services/Monitoring',
         'app/Services/Gateway', 'app/Services/Storage', 'app/Services/File', 'app/Services/KYC',
         'app/Services/Land', 'app/Services/Payroll', 'app/Services/Payment', 'app/Services/SEO',
         'app/Services/UI', 'app/Services/I18n', 'app/Services/Gamification', 'app/Services/Analytics',
         'app/Services/Map', 'app/Services/Form', 'app/Services/Customer', 'app/Services/Employee',
         'app/Services/Directory', 'app/Services/Business', 'app/Services/Property',
         'app/Services/Backoffice', 'app/Services/Auth'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    foreach (glob("$dir/*.php") as $f) check($f);
    // Check subdirectories
    foreach (glob("$dir/*/", GLOB_ONLYDIR) as $sub) {
        foreach (glob("$sub*.php") as $f) check($f);
    }
}
