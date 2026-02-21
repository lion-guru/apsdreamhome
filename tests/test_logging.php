<?php
$logFile = __DIR__ . '/../logs/debug_output.log';
file_put_contents($logFile, "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n", FILE_APPEND);
file_put_contents($logFile, "dirname(SCRIPT_NAME): " . dirname($_SERVER['SCRIPT_NAME']) . "\n", FILE_APPEND);
echo "Logged to $logFile\n";
