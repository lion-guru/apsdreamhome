<?php
function check($file) {
    if (!file_exists($file)) { echo basename($file) . ": NOT FOUND\n"; return; }
    $c = file_get_contents($file);
    $hasTrait = strpos($c, 'ServiceTenantTrait') !== false;
    $hasCtx = strpos($c, 'TenantContext') !== false;
    $tidCount = substr_count($c, 'tenant_id');
    $prepareCount = substr_count($c, '->prepare');
    preg_match_all('/function (\w+)\(/', $c, $m);
    $unscopedMethods = [];
    foreach ($m[1] as $fn) {
        $pos = strpos($c, "function $fn");
        $nextFunc = strpos($c, 'function ', $pos + strlen("function $fn"));
        $snippet = substr($c, $pos, $nextFunc !== false ? $nextFunc - $pos : 500);
        $hasPrepare = strpos($snippet, 'prepare') !== false;
        $hasTid = strpos($snippet, 'tenant_id') !== false;
        $hasCtx = strpos($snippet, 'TenantContext') !== false;
        if ($hasPrepare && !$hasTid && !$hasCtx) {
            $unscopedMethods[] = $fn;
        }
    }
    if ($prepareCount > 0 && $tidCount < 3) {
        echo basename($file) . ": prepare:$prepareCount tid:$tidCount trait:" . ($hasTrait?'Y':'N') . " ctx:" . ($hasCtx?'Y':'N');
        if ($unscopedMethods) echo " UNSCOPED:[" . implode(',', $unscopedMethods) . "]";
        echo "\n";
    }
}

$dirs = ['app/Services', 'app/Services/Communication', 'app/Services/Legal', 'app/Services/Loan',
         'app/Services/Finance', 'app/Services/Sales', 'app/Services/MLM', 'app/Services/AI',
         'app/Services/Voice', 'app/Services/Operations', 'app/Services/Marking', 'app/Services/Security',
         'app/Services/CRM', 'app/Services/Events', 'app/Services/Performance', 'app/Services/Monitoring',
         'app/Services/Gateway', 'app/Services/Storage', 'app/Services/File', 'app/Services/KYC',
         'app/Services/Land', 'app/Services/Payroll', 'app/Services/Payment', 'app/Services/SEO',
         'app/Services/UI', 'app/Services/Localization', 'app/Services/I18n', 'app/Services/Gamification',
         'app/Services/Analytics', 'app/Services/Map', 'app/Services/Form', 'app/Services/Customer',
         'app/Services/Employee'];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    foreach (glob("$dir/*.php") as $f) {
        check($f);
    }
}
