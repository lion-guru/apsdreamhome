<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "ai_call_logs:\n";
foreach ($pdo->query("SELECT id, lead_id, agent_id, call_sid, sentiment FROM ai_call_logs")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    print_r($r);
}
echo "\nai_call_sessions:\n";
foreach ($pdo->query("SELECT id, lead_id, ai_agent_id, call_sid, sentiment FROM ai_call_sessions")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    print_r($r);
}?>