<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$tables = ['lead_engagement_metrics', 'lead_files', 'lead_notes'];
foreach ($tables as $t) {
    echo "$t:\n";
    try {
        $indexes = $pdo->query("SHOW INDEX FROM $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach($indexes as $idx) {
            if ($idx['Key_name'] !== 'PRIMARY') {
                echo "  {$idx['Key_name']} ({$idx['Column_name']}) - Unique: " . ($idx['Non_unique'] == 0 ? 'YES' : 'NO') . "\n";
            }
        }
    } catch (Exception $e) { echo "  ERROR: {$e->getMessage()}\n"; }
}
