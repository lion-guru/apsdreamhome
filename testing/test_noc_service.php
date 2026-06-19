<?php
header('Content-Type: text/plain; charset=UTF-8');
require_once __DIR__ . '/../config/bootstrap.php';

$logFile = dirname(__DIR__) . '/logs/php_error.log';
echo "=== LOG FILE: $logFile ===\n\n";

if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -100);
    echo implode("", $lastLines);
} else {
    echo "Log file does not exist.";
}


