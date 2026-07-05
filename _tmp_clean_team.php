<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->exec("DELETE FROM team_members WHERE id IN (2,3,4,5,6,7,8,9)");
$count = $pdo->query("SELECT COUNT(*) FROM team_members")->fetchColumn();
echo "Deleted. Remaining: $count\n";
$q = $pdo->query("SELECT id, name, position, category FROM team_members ORDER BY sort_order");
foreach ($q as $r) {
    echo $r['id'] . ': ' . $r['name'] . ' | ' . $r['position'] . ' | ' . $r['category'] . "\n";
}
