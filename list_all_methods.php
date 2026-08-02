<?php
$files = [
    'app/services/CommissionService.php',
    'app/services/WalletService.php',
    'app/services/EMIAutomationService.php',
];

foreach ($files as $f) {
    $c = file_get_contents($f);
    $methods = [];
    if (preg_match_all('/public function (\w+)\(/', $c, $m)) {
        foreach ($m[1] as $method) {
            echo basename($f) . ": " . $method . "\n";
        }
    }
    echo "\n";
}