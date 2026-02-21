<?php
$logFile = __DIR__ . '/../logs/php_error.log';
$lines = file($logFile);
$lastLines = array_slice($lines, -50);
echo implode("", $lastLines);
