<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$names = ['Praveen','Prabhat','Abhay','Anuj','Srivastwa','Vijay','Verma','Shushant','Pramod','Sharma','Rachna','Gupta','Praveen Singh'];
echo "=== Searching users/associates by name ===\n";
foreach ($names as $n) {
    $q = $pdo->prepare("SELECT id, name, role, email FROM users WHERE name LIKE ? LIMIT 3");
    $q->execute(['%' . $n . '%']);
    $results = $q->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $r) {
        echo "USER: " . $r['id'] . ': ' . $r['name'] . ' | ' . $r['role'] . ' | ' . $r['email'] . "\n";
    }
    $q2 = $pdo->prepare("SELECT id, name, level FROM associates WHERE name LIKE ? LIMIT 3");
    $q2->execute(['%' . $n . '%']);
    $r2 = $q2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($r2 as $r) {
        echo "ASSOC: " . $r['id'] . ': ' . $r['name'] . ' | ' . $r['level'] . "\n";
    }
}
echo "\n=== Check existing images in assets/images/team/ ===\n";
$dir = __DIR__ . '/assets/images/team/';
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') echo $f . "\n";
    }
} else {
    echo "Directory does not exist\n";
}
echo "\n=== Checking admin team views ===\n";
echo "admin/team/form.php exists: " . (file_exists(__DIR__ . '/app/views/admin/team/form.php') ? 'YES' : 'NO') . "\n";
echo "admin/team/index.php exists: " . (file_exists(__DIR__ . '/app/views/admin/team/index.php') ? 'YES' : 'NO') . "\n";
