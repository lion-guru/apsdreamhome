<?php
/**
 * Advanced Database Duplicity Analysis
 * Groups tables by prefix/category and counts records
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

$analysis = [
    'users_customers' => [],
    'leads_crm' => [],
    'properties' => [],
    'payments_transactions' => [],
    'mlm_network' => [],
    'ai_chatbot' => [],
    'log_temp' => [],
    'other' => []
];

foreach ($tables as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    } catch (Exception $e) {
        $count = -1; // Error reading table
    }
    
    $category = 'other';
    if (preg_match('/user|customer|member|admin_user|agent|associate/i', $table)) $category = 'users_customers';
    elseif (preg_match('/lead|inquiry|crm|contact|callback/i', $table)) $category = 'leads_crm';
    elseif (preg_match('/prop|plot|flat|house|land|inventory/i', $table)) $category = 'properties';
    elseif (preg_match('/pay|trans|invoice|wallet|bank|payout/i', $category == 'other' ? $table : '')) $category = 'payments_transactions';
    elseif (preg_match('/mlm|tree|level|binary|matrix|commission/i', $table)) $category = 'mlm_network';
    elseif (preg_match('/ai_|chat|bot|bot_/i', $table)) $category = 'ai_chatbot';
    elseif (preg_match('/log|tmp|temp|cache|test/i', $table)) $category = 'log_temp';
    
    $analysis[$category][] = [
        'name' => $table,
        'count' => $count
    ];
}

echo "=== DATABASE DUPLICITY REPORT ===\n\n";

foreach ($analysis as $cat => $data) {
    echo "Category: " . strtoupper($cat) . " (" . count($data) . " tables)\n";
    echo "--------------------------------------------------\n";
    
    // Sort by count descending
    usort($data, function($a, $b) { return $b['count'] - $a['count']; });
    
    $empty = 0;
    foreach ($data as $i => $t) {
        if ($t['count'] == 0) {
            $empty++;
            continue;
        }
        if ($i < 10) { // Show top 10 with data
            printf("%-40s | Records: %d\n", $t['name'], $t['count']);
        }
    }
    echo "Empty Tables: $empty\n";
    echo "\n";
}
