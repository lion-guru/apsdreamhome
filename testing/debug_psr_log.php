<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/../config/bootstrap.php';
echo "LoggerInterface exists: " . (interface_exists('Psr\Log\LoggerInterface') ? 'YES' : 'NO') . "\n";
