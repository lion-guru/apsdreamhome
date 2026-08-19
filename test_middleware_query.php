<?php
require 'vendor/autoload.php';
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

// Test the middleware query with a recent token
$token = '7ee2eac1a05e9659f561'; // partial token from above
$stmt = $pdo->prepare("
    SELECT t.user_id, u.role, t.expires_at 
    FROM api_tokens t
    JOIN users u ON u.id = t.user_id
    WHERE t.token = ? AND (t.expires_at IS NULL OR t.expires_at > NOW())
");
$stmt->execute([$token]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump($result);

// Also test with full token
$token = '7ee2eac1a05e9659f561'; // This is partial, need full
$stmt = $pdo->prepare("SELECT token FROM api_tokens WHERE id = 232");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Full token: {$row['token']}\n";

$stmt = $pdo->prepare("
    SELECT t.user_id, u.role, t.expires_at 
    FROM api_tokens t
    JOIN users u ON u.id = t.user_id
    WHERE t.token = ? AND (t.expires_at IS NULL OR t.expires_at > NOW())
");
$stmt->execute([$row['token']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump($result);