<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== DEBUG: UPLINE CHAIN ===\n";

// Check our test users
$stmt = $pdo->query("SELECT id, name, referred_by FROM users WHERE name LIKE 'Deep SM%' ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) echo "  user={$u['id']} name={$u['name']} referred_by={$u['referred_by']}\n";

echo "\n=== DEBUG: BOOKING 99945 ===\n";
$stmt = $pdo->prepare("SELECT id, customer_id, sales_manager_id, associate_id, channel FROM plot_bookings WHERE id = 99945");
$stmt->execute();
$b = $stmt->fetch(PDO::FETCH_ASSOC);
echo "  id={$b['id']} customer={$b['customer_id']} sales_mgr={$b['sales_manager_id']} assoc={$b['associate_id']} channel={$b['channel']}\n";

echo "\n=== DEBUG: ASSOCIATE for assoc_id={$b['associate_id']} ===\n";
$stmt = $pdo->prepare("SELECT id, user_id, level FROM associates WHERE user_id = ?");
$stmt->execute([$b['associate_id']]);
$a = $stmt->fetch(PDO::FETCH_ASSOC);
echo "  assoc: " . ($a ? "id={$a['id']} user_id={$a['user_id']} level={$a['level']}" : 'NOT FOUND') . "\n";

// Also check by id
$stmt = $pdo->prepare("SELECT id, user_id, level FROM associates WHERE id = ?");
$stmt->execute([$b['associate_id']]);
$a2 = $stmt->fetch(PDO::FETCH_ASSOC);
echo "  assoc by id: " . ($a2 ? "id={$a2['id']} user_id={$a2['user_id']} level={$a2['level']}" : 'NOT FOUND') . "\n";

// Check which user the engine resolves to
echo "\n=== DEBUG: ENGINE SOURCE USER RESOLUTION ===\n";
if (!empty($b['sales_manager_id'])) {
    echo "  source = sales_manager_id = {$b['sales_manager_id']}\n";
} elseif (!empty($b['associate_id'])) {
    $stmt2 = $pdo->prepare("SELECT user_id FROM associates WHERE user_id = ? LIMIT 1");
    $stmt2->execute([$b['associate_id']]);
    $ar = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($ar) {
        echo "  source = associates.user_id = {$ar['user_id']} (for associate_id={$b['associate_id']})\n";
    } else {
        echo "  associate_id={$b['associate_id']} NOT found in associates table by user_id!\n";
        echo "  Trying by id...\n";
        $stmt3 = $pdo->prepare("SELECT id, user_id FROM associates WHERE id = ? LIMIT 1");
        $stmt3->execute([$b['associate_id']]);
        $ar2 = $stmt3->fetch(PDO::FETCH_ASSOC);
        echo "  by id: " . ($ar2 ? "id={$ar2['id']} user_id={$ar2['user_id']}" : 'NOT FOUND') . "\n";
    }
}
echo "  customer_id fallback = {$b['customer_id']}\n";

echo "\n=== BUG DIAGNOSIS ===\n";
echo "  The engine resolves associate_id via: SELECT user_id FROM associates WHERE user_id = ?\n";
echo "  But associate_id={$b['associate_id']} is the associates TABLE id, not user_id!\n";
echo "  associates id=319 has user_id=2112, but engine looks for user_id=319 â†’ NOT FOUND â†’ falls to customer_id=3\n";?>