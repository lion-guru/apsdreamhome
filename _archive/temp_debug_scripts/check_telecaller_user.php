<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// 1. Check telecaller users
echo "=== TELECALLER USERS ===\n";
$stmt = $pdo->query("SELECT id, email, role, status FROM users WHERE role = 'telecaller'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Count: " . count($rows) . "\n";
foreach ($rows as $r) echo "  ID={$r['id']} email={$r['email']} role={$r['role']} status={$r['status']}\n";

// 2. Check what test_login=3 would find
echo "\n=== TEST_LOGIN=3 QUERY ===\n";
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'telecaller' ORDER BY id LIMIT 1");
$fallback = $stmt->fetch(PDO::FETCH_ASSOC);
if ($fallback) {
    echo "Found: ID={$fallback['id']} email={$fallback['email']} role={$fallback['role']}\n";
} else {
    echo "NOT FOUND! Will fall back to admin.\n";
    $stmt2 = $pdo->query("SELECT id, email, role FROM users WHERE role IN ('super_admin','admin') ORDER BY id LIMIT 1");
    $fb = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "Fallback user: ID={$fb['id']} email={$fb['email']} role={$fb['role']}\n";
}

// 3. Check all unique roles
echo "\n=== ALL UNIQUE ROLES IN users TABLE ===\n";
$stmt = $pdo->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role ORDER BY cnt DESC");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['role']}: {$r['cnt']}\n";
}

// 4. Check employees table for telecaller
echo "\n=== EMPLOYEES WITH TELECALLER ROLE ===\n";
$stmt = $pdo->query("SELECT e.id, e.user_id, e.designation, e.department FROM employees e WHERE e.designation LIKE '%telecall%' OR e.designation LIKE '%Telecall%'");
$emps = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Count: " . count($emps) . "\n";
foreach ($emps as $e) echo "  emp_id={$e['id']} user_id={$e['user_id']} designation={$e['designation']} dept={$e['department']}\n";

// 5. Check the user with test_login=3 (id=69)
echo "\n=== USER ID=69 ===\n";
$stmt = $pdo->query("SELECT id, email, role, name FROM users WHERE id = 69");
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if ($u) {
    echo "  ID={$u['id']} email={$u['email']} role={$u['role']} name={$u['name']}\n";
} else {
    echo "  NOT FOUND\n";
}?>