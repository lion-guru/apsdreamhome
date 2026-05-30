<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Find the 1 remaining INT column
$stmt = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'apsdreamhome'
    AND DATA_TYPE = 'int'
    AND COLUMN_NAME IN ('user_id','assigned_to','created_by','updated_by','deleted_by',
        'approved_by','rejected_by','registered_by','verified_by','handled_by',
        'processed_by','modified_by','added_by','creator_id','owner_id','customer_id','admin_id')
");
$remaining = $stmt->fetchAll();
echo "Remaining INT columns:\n";
foreach ($remaining as $r) {
    echo "  {$r['TABLE_NAME']}.{$r['COLUMN_NAME']} ({$r['COLUMN_TYPE']})\n";
}

// What table is booking_summary?
$stmt = $pdo->query("SHOW TABLE STATUS WHERE Name = 'booking_summary'");
$row = $stmt->fetch();
echo "\nbooking_summary type: " . ($row ? $row['Comment'] . ' / ' . json_encode($row) : 'not found') . "\n";
