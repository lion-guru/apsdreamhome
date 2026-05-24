<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$items = $pdo->query("SELECT id, name, url, section, icon FROM admin_menu_items ORDER BY section, id")->fetchAll(PDO::FETCH_ASSOC);
$currentSection = '';
foreach ($items as $i) {
    if ($i['section'] !== $currentSection) {
        $currentSection = $i['section'];
        echo "\n=== $currentSection ===\n";
    }
    $icon = $i['icon'] ?: '(none)';
    echo "  {$i['id']}: {$i['name']} [{$icon}] → {$i['url']}\n";
}
