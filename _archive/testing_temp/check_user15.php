<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Check who is user 15
$stmt = $pdo->prepare('SELECT id, name, referred_by, role FROM users WHERE id = 15');
$stmt->execute();
echo "User 15: "; print_r($stmt->fetch(PDO::FETCH_ASSOC));

// Check associate_id 15
$stmt = $pdo->prepare('SELECT id, user_id, level FROM associates WHERE user_id = 15');
$stmt->execute();
echo "Associate 15: "; print_r($stmt->fetch(PDO::FETCH_ASSOC));

// Check upline for user 15
echo "\nUpline for user 15:\n";
$current = 15;
for ($level = 1; $level <= 7; $level++) {
    $stmt = $pdo->prepare('SELECT id, name, referred_by FROM users WHERE id = ?');
    $stmt->execute([$current]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || empty($row['referred_by'])) break;
    $parentId = (int)$row['referred_by'];
    $parentStmt = $pdo->prepare('SELECT id, name, referred_by FROM users WHERE id = ?');
    $parentStmt->execute([$parentId]);
    $parent = $parentStmt->fetch(PDO::FETCH_ASSOC);
    if (!$parent) break;
    echo "  Level $level: User {$parent['id']} ({$parent['name']}) referred_by={$parent['referred_by']}\n";
    $current = $parentId;
}?>