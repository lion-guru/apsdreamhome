<?php
header('Content-Type: text/plain');
define('APP_ROOT', dirname(__DIR__));
define('APP_PATH', APP_ROOT . '/app');

echo "APP_ROOT: " . APP_ROOT . "\n";
echo "APP_PATH: " . APP_PATH . "\n";

$fallbackFile = APP_PATH . '/Core/LoggerInterfaceFallback.php';
echo "Fallback file path: " . $fallbackFile . "\n";
echo "Fallback file exists: " . (file_exists($fallbackFile) ? 'YES' : 'NO') . "\n";

echo "Inclusion test:\n";
try {
    require_once $fallbackFile;
    echo "Inclusion successful.\n";
    echo "Interface exists after manual require: " . (interface_exists('Psr\Log\LoggerInterface') ? 'YES' : 'NO') . "\n";
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}?>