<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Re-link user_ids by email (using binary comparison to bypass collation mismatch)
$updated = $pdo->exec("
    UPDATE employees e
    JOIN users u ON u.email COLLATE utf8mb4_unicode_ci = e.email COLLATE utf8mb4_unicode_ci
    SET e.user_id = u.id
    WHERE e.user_id IS NULL OR e.user_id != u.id
");
echo "Re-linked $updated employees by email\n";

echo "\n=== Final state ===\n";
$total = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
echo "Total employees: $total\n";
$linked = $pdo->query("SELECT COUNT(*) FROM employees WHERE user_id IS NOT NULL")->fetchColumn();
echo "Linked to users: $linked\n";

$rows = $pdo->query("SELECT id, user_id, name, email, employee_code FROM employees ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  ID={$r['id']} user_id={$r['user_id']} code={$r['employee_code']} email={$r['email']}\n";
}?>