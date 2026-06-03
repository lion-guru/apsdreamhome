<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$pdo->query("UPDATE api_keys SET is_active = 1 WHERE key_name = 'HUGGING_FACE_API_KEY'");
echo "Marked Hugging Face as ACTIVE\n";

echo "\n=== All API Keys ===\n";
$data = $pdo->query("SELECT key_name, service_name, is_active, usage_count FROM api_keys")->fetchAll(PDO::FETCH_ASSOC);
foreach ($data as $row) {
    $status = $row['is_active'] ? '✅' : '❌';
    echo "$status {$row['key_name']} | {$row['service_name']}\n";
}
