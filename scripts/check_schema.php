<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$cols = $pdo->query("DESCRIBE ai_context_memory")->fetchAll(PDO::FETCH_ASSOC);
echo "ai_context_memory columns:\n";
foreach($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Null'] . "\n";
}
