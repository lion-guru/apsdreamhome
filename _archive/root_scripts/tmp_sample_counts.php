<?php
$groups = [
    'MLM' => 'mlm_%',
    'Property' => 'property_%',
    'Leads' => 'leads%',
    'Users' => 'user%',
    'AI/Workflows' => 'workflow%',
    'System' => 'system%',
    'Notification' => 'notification%',
    'Mobile' => 'mobile%',
    'OCR' => 'ocr%',
    'Analytics' => '%analytics%',
    'Salary/Payroll' => 'salary%'
];

try {
    $db = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    
    foreach ($groups as $name => $pattern) {
        $stmt = $db->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$pattern]);
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "\n--- Group: $name (" . count($tables) . " tables) ---\n";
        
        $total_rows = 0;
        $active_tables = 0;
        
        foreach (array_slice($tables, 0, 10) as $table) {
            $countStmt = $db->query("SELECT COUNT(*) FROM `$table` LIMIT 1");
            $count = $countStmt->fetchColumn();
            echo str_pad($table, 30) . ": $count rows\n";
            if ($count > 0) $active_tables++;
            $total_rows += $count;
        }
        
        if (count($tables) > 10) echo "... (showing first 10)\n";
        echo "Active in sample: $active_tables/10\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
