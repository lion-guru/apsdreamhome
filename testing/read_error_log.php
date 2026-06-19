<?php
header('Content-Type: text/plain');
$logPath = ini_get('error_log');
echo "PHP Error Log Path: " . $logPath . "\n\n";
if ($logPath && file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -50);
    echo implode("", $lastLines);
} else {
    // Try standard apache path
    $apacheLog = "C:/xampp/apache/logs/error.log";
    if (file_exists($apacheLog)) {
        echo "Apache Error Log:\n";
        $lines = file($apacheLog);
        $lastLines = array_slice($lines, -50);
        echo implode("", $lastLines);
    } else {
        echo "No error log file found.";
    }
}
