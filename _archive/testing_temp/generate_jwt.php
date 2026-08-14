<?php
require __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
$secret = trim(shell_exec('cd .. && php -r "echo $_ENV[\"JWT_SECRET\"] ?? getenv(\"JWT_SECRET\") ?? \"\";" 2>nul'));
if (!$secret) {
    foreach (['.env', 'database/.env'] as $f) {
        if (file_exists(__DIR__ . '/../' . $f)) {
            $lines = file(__DIR__ . '/../' . $f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (preg_match('/^JWT_SECRET\s*=\s*(.+)$/', trim($line), $m)) {
                    $secret = trim($m[1], "\"' \t");
                    break 2;
                }
            }
        }
    }
}
if (!$secret) {
    $secret = 'apsdreamhome-jwt-secret-key-2026-must-be-32-chars-or-more-padding-padding';
}
$token = JWT::encode(['user_id' => 1, 'role' => 'admin', 'exp' => time() + 3600], $secret, 'HS256');
file_put_contents(__DIR__ . '/jwt_token.txt', $token);
echo "Token (secret len: " . strlen($secret) . "): " . substr($token, 0, 60) . "...\n";?>