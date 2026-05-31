<?php
/**
 * Check actual schema of ai_conversations table
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== CHECK AI_CONVERSATIONS SCHEMA ===\n\n";

$columns = $pdo->query("DESCRIBE ai_conversations")->fetchAll(PDO::FETCH_ASSOC);

echo "Columns in ai_conversations:\n";
foreach ($columns as $col) {
    echo "- {$col['Field']} ({$col['Type']}) " . ($col['Key'] ? "[KEY: {$col['Key']}]" : "") . "\n";
}

echo "\nSample data:\n";
$sample = $pdo->query("SELECT * FROM ai_conversations LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sample as $row) {
    echo "Row: " . json_encode($row, JSON_PRETTY_PRINT) . "\n\n";
}
