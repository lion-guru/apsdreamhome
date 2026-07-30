<?php
$files = glob("app/Services/**/*.php");
$unscoped = [];
foreach ($files as $f) {
    $c = file_get_contents($f);
    if (strpos($c, "ServiceTenantTrait") === false && strpos($c, "TenantContext") === false) {
        $unscoped[] = $f;
    }
}
echo count($unscoped) . " unscoped files:\n";
foreach ($unscoped as $f) echo "  $f\n";