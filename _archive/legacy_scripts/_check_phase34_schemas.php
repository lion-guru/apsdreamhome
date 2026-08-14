<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = ['notification_templates', 'sms_templates', 'tax_types', 'tax_slabs', 'gst_settings', 'mlm_rank_rates', 'kpis', 'performance_benchmarks', 'agent_commission_rates', 'resell_commission_structure', 'workflow_automations', 'admin_menu_items', 'farmer_commission_structures', 'ocr_templates'];

echo "=== SCHEMA INSPECTION ===\n";
foreach ($tables as $t) {
    echo "\n$t:\n";
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
        }
    } catch (\Throwable $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}?>