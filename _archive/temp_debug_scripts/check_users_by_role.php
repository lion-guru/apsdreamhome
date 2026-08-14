<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "=== Users by role ===\n";
$stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY role, id");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$currentRole = '';
foreach ($rows as $r) {
    if ($r['role'] !== $currentRole) {
        $currentRole = $r['role'];
        echo "\n[$currentRole]\n";
    }
    echo "  id={$r['id']} name={$r['name']} email={$r['email']}\n";
}?>