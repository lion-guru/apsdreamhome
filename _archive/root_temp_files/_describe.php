<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
echo "admin_menu_items columns:\n";
foreach ($pdo->query('DESCRIBE admin_menu_items') as $r) {
    echo "  {$r['Field']} ({$r['Type']}) {$r['Key']}\n";
}
