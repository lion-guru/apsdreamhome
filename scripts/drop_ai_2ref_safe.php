<?php
/**
 * Drop AI tables - 2-ref safe only
 * ai_generated_content: 0 unprotected refs out of 2
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();

try {
    $pdo->exec("DROP TABLE IF EXISTS ai_generated_content");
    echo "✓ DROPPED ai_generated_content (all refs in try/catch)\n";
} catch (Exception $e) {
    echo "✗ FAILED: {$e->getMessage()}\n";
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables: $before → $after\n";
