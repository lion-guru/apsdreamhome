<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== AI_USER_PREFERENCES SCHEMA ===\n\n";
$cols = $pdo->query("DESCRIBE ai_user_preferences")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ')' . ($c['Key'] ? " [KEY: {$c['Key']}]" : "") . "\n";
}

echo "\n=== CONSTRAINTS ===\n";
try {
    $constraints = $pdo->query("SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = 'ai_user_preferences'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($constraints as $c) {
        echo $c['CONSTRAINT_NAME'] . ' - ' . $c['CONSTRAINT_TYPE'] . "\n";
    }
} catch (Exception $e) {
    echo "No constraints found\n";
}

echo "\n=== CHECK CONSTRAINTS ===\n";
try {
    $checkConstraints = $pdo->query("SELECT CONSTRAINT_NAME, CHECK_CLAUSE 
FROM information_schema.CHECK_CONSTRAINTS 
WHERE CONSTRAINT_SCHEMA = 'apsdreamhome'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($checkConstraints as $c) {
        echo $c['CONSTRAINT_NAME'] . ': ' . $c['CHECK_CLAUSE'] . "\n";
    }
} catch (Exception $e) {
    echo "No check constraints\n";
}
