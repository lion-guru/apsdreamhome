<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== AI_CONTEXT_MEMORY SCHEMA ===\n\n";
$cols = $pdo->query("DESCRIBE ai_context_memory")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ')' . ($c['Key'] ? " [KEY: {$c['Key']}]" : "") . "\n";
}

echo "\n=== CONSTRAINTS ===\n";
try {
    $constraints = $pdo->query("SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = 'ai_context_memory'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($constraints as $c) {
        echo $c['CONSTRAINT_NAME'] . ' - ' . $c['CONSTRAINT_TYPE'] . "\n";
    }
} catch (Exception $e) {
    echo "No constraints found\n";
}
