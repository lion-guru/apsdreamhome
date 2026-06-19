<?php
header('Content-Type: text/plain');
// Script is in testing/ subdirectory - go up one level to find vendor
$rootPath = dirname(__DIR__);
$envFile = $rootPath . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"\'');
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

$privRaw = $_ENV['VAPID_PRIVATE_KEY'] ?? getenv('VAPID_PRIVATE_KEY') ?: '';
$pubRaw = $_ENV['VAPID_PUBLIC_KEY'] ?? getenv('VAPID_PUBLIC_KEY') ?: '';

echo "VAPID_PRIVATE_KEY Raw: '$privRaw'\n";
echo "VAPID_PUBLIC_KEY Raw: '$pubRaw'\n";

if (strpos($privRaw, 'PEM:') === 0) {
    $pemFile = dirname(__DIR__) . '/' . substr($privRaw, 4);
    echo "Resolving PEM: file at: '$pemFile'\n";
    echo "File exists: " . (file_exists($pemFile) ? "YES" : "NO") . "\n";
    $pemContent = @file_get_contents($pemFile);
    echo "PEM Content Length: " . (is_string($pemContent) ? strlen($pemContent) : "FALSE") . "\n";
}
