<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Check leads.assigned_to
$stmt = $pdo->query("SHOW COLUMNS FROM leads WHERE Field = 'assigned_to'");
echo "leads.assigned_to: " . json_encode($stmt->fetch()) . "\n";

// Check leads FK
$stmt = $pdo->query("
    SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = 'leads'
    AND COLUMN_NAME = 'assigned_to' AND REFERENCED_TABLE_NAME IS NOT NULL
");
$fk = $stmt->fetchAll();
echo "FK on leads.assigned_to: " . (empty($fk) ? 'NONE' : json_encode($fk)) . "\n";

// Count remaining INT FK pattern columns
$stmt = $pdo->query("
    SELECT COUNT(*) as cnt
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'apsdreamhome'
    AND DATA_TYPE = 'int'
    AND COLUMN_NAME IN ('user_id','assigned_to','created_by','updated_by','deleted_by',
        'approved_by','rejected_by','registered_by','verified_by','handled_by',
        'processed_by','modified_by','added_by','creator_id','owner_id','customer_id','admin_id')
");
echo "Remaining INT FK-to-users columns: " . $stmt->fetch()['cnt'] . "\n";

// Count BIGINT pattern columns
$stmt = $pdo->query("
    SELECT COUNT(*) as cnt
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'apsdreamhome'
    AND DATA_TYPE = 'bigint'
    AND COLUMN_NAME IN ('user_id','assigned_to','created_by','updated_by','deleted_by',
        'approved_by','rejected_by','registered_by','verified_by','handled_by',
        'processed_by','modified_by','added_by','creator_id','owner_id','customer_id','admin_id')
");
echo "BIGINT FK-to-users columns now: " . $stmt->fetch()['cnt'] . "\n";

// Verify users.id
$stmt = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'id'");
echo "users.id: " . json_encode($stmt->fetch()) . "\n";

// Check if leads.assigned_to and leads.created_by are correct
$stmt = $pdo->query("SHOW COLUMNS FROM leads WHERE Field IN ('assigned_to','created_by')");
foreach ($stmt as $row) {
    echo "leads.{$row['Field']}: {$row['Type']}\n";
}
