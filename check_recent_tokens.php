<?php
require 'vendor/autoload.php';
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

// Check recent tokens
$stmt = $pdo->query("SELECT id, user_id, token, expires_at, created_at FROM api_tokens ORDER BY created_at DESC LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}, User: {$row['user_id']}, Token: " . substr($row['token'], 0, 20) . "..., Expires: {$row['expires_at']}, Created: {$row['created_at']}\n";
}