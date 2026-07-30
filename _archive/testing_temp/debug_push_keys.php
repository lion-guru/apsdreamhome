<?php
header('Content-Type: text/plain');
define('APP_ROOT', dirname(__DIR__));

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!preg_match('/^([A-Z_][A-Z0-9_]*)\s*=\s*(.*)$/i', $line, $m)) continue;
        $key = $m[1]; $val = trim($m[2], " \t\"'");
        $_ENV[$key] = $val;
        putenv("$key=$val");
    }
}

require_once __DIR__ . '/../app/Core/Autoloader.php';
require_once __DIR__ . '/../app/Services/Communication/PushSender.php';

$sender = new \App\Services\Communication\PushSender();
$ref = new ReflectionClass($sender);

$pubProp = $ref->getProperty('vapidPublicKey');
$pubProp->setAccessible(true);
$privProp = $ref->getProperty('vapidPrivateKey');
$privProp->setAccessible(true);

echo "Vapid Public Key Binary Length: " . strlen($pubProp->getValue($sender)) . "\n";
echo "Vapid Private Key Binary Length: " . strlen($privProp->getValue($sender)) . "\n";
echo "isConfigured: " . ($sender->isConfigured() ? "TRUE" : "FALSE") . "\n";
echo "getVapidPublicKey: '" . $sender->getVapidPublicKey() . "' (len: " . strlen($sender->getVapidPublicKey()) . ")\n";
