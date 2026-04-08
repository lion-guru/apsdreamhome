<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$keys = $pdo->query("SELECT id, provider, model_name, is_active, is_free, monthly_limit, used_this_month, last_used_at, expires_at FROM api_keys")->fetchAll(PDO::FETCH_ASSOC);
echo "=== API Keys in Database ===\n\n";
foreach ($keys as $k) {
    $status = $k['is_active'] ? '✅ Active' : '❌ Inactive';
    $free = $k['is_free'] ? ' (FREE)' : '';
    echo "{$k['id']}. {$k['provider']} - {$k['model_name']} $free\n";
    echo "   Status: $status | Used: {$k['used_this_month']}/{$k['monthly_limit']} | Last: {$k['last_used_at']} | Expires: {$k['expires_at']}\n\n";
}
echo "Total: " . count($keys) . "\n";
