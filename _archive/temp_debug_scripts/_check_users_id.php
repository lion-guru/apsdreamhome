<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$cols = $pdo->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    if (in_array($c['Field'], ['id', 'email', 'role'])) {
        echo "  {$c['Field']} ({$c['Type']}) {$c['Null']} {$c['Key']}\n";
    }
}?>