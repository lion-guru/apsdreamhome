<?php
header('Content-Type: text/plain');
define('APP_ROOT_CHECK', dirname(__DIR__));
echo "APP_ROOT_CHECK: " . APP_ROOT_CHECK . "\n";
echo "defined('APP_ROOT'): " . (defined('APP_ROOT') ? APP_ROOT : 'NOT DEFINED') . "\n";

$vendorAutoload = APP_ROOT_CHECK . '/vendor/autoload.php';
echo "Vendor autoload file: " . $vendorAutoload . "\n";
echo "file_exists: " . (file_exists($vendorAutoload) ? 'YES' : 'NO') . "\n";

$res = include($vendorAutoload);
echo "Include result: " . ($res ? 'SUCCESS' : 'FAILED') . "\n";

echo "LoggerInterface exists after manual include: " . (interface_exists('Psr\Log\LoggerInterface') ? 'YES' : 'NO') . "\n";

echo "spl_autoload_functions:\n";
print_r(spl_autoload_functions());?>