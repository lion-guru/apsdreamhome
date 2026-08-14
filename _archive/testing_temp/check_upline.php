<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Check network tree
echo "=== Network Tree (mlm_network_tree) ===\n";
$rows = $pdo->query("SELECT * FROM mlm_network_tree ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo sprintf("  Parent %d -> Child %d (sponsor_id=%s, parent_id=%s, level=%s)\n", 
        $r['parent_id'], $r['associate_id'], $r['sponsor_id'] ?? 'NULL', $r['parent_id'] ?? 'NULL', $r['level'] ?? 'NULL');
}

// Check associates
echo "\n=== Associates (users with associate record) ===\n";
$rows = $pdo->query("SELECT a.id, a.user_id, a.level, u.name, u.referred_by FROM associates a JOIN users u ON u.id = a.user_id WHERE a.status='active'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo sprintf("  User %d (%s): level=%s, referred_by=%s\n", 
        $r['user_id'], $r['name'], $r['level'], $r['referred_by'] ?? 'NULL');
}

// Check mlm_profiles
echo "\n=== MLM Profiles ===\n";
$rows = $pdo->query("SELECT user_id, current_level FROM mlm_profiles")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo sprintf("  User %d: current_level=%s\n", $r['user_id'], $r['current_level']);
}

// Check getUpline logic for user 3
echo "\n=== Upline for User 3 (booking #9003 source) ===\n";
$upline = [];
$current = 3;
for ($level = 1; $level <= 7; $level++) {
    $stmt = $pdo->prepare("SELECT id, name, referred_by FROM users WHERE id = ?");
    $stmt->execute([$current]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || empty($row['referred_by'])) break;
    $parentId = (int)$row['referred_by'];
    $parentStmt = $pdo->prepare("SELECT id, name, referred_by FROM users WHERE id = ?");
    $parentStmt->execute([$parentId]);
    $parent = $parentStmt->fetch(PDO::FETCH_ASSOC);
    if (!$parent) break;
    $upline[$level] = $parent;
    $current = $parentId;
}
echo "Upline for user 3:\n";
foreach ($upline as $lvl => $up) {
    echo "  Level $lvl: User {$up['id']} ({$up['name']})\n";
}

// Also check booking #9003 details
echo "\n=== Booking #9003 Details ===\n";
$stmt = $pdo->prepare("SELECT id, sales_manager_id, associate_id, customer_id, agreement_value FROM plot_bookings WHERE id = 9003");
$stmt->execute();
$booking = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Booking: " . json_encode($booking, JSON_PRETTY_PRINT) . "\n";?>