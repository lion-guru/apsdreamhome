<?php
require_once dirname(__DIR__) . '/app/Core/ConfigService.php';
require_once dirname(__DIR__) . '/app/Core/Database/Database.php';

try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();
    
    // Get agent1 user ID
    $user = $db->query("SELECT id, name, role FROM users WHERE email = 'agent1@apsdreamhome.com'")->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo "agent1@apsdreamhome.com not found.\n";
        exit;
    }
    
    $userId = $user['id'];
    echo "Agent1 User ID: $userId, Name: {$user['name']}, Role: {$user['role']}\n\n";

    // Query commissions table
    echo "Querying 'commissions' table:\n";
    $q = $db->prepare("SELECT * FROM commissions WHERE user_id = ? OR associate_id = ?");
    $q->execute([$userId, $userId]);
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    echo "  Found " . count($rows) . " rows.\n";
    foreach ($rows as $row) {
        echo "    ID: {$row['id']}, user_id: {$row['user_id']}, associate_id: {$row['associate_id']}, amount: {$row['amount']}, status: {$row['status']}, type: {$row['commission_type']}\n";
    }

    // Query mlm_commission_ledger table
    echo "\nQuerying 'mlm_commission_ledger' table:\n";
    $q2 = $db->prepare("SELECT * FROM mlm_commission_ledger WHERE associate_id = ? OR user_id = ?");
    $q2->execute([$userId, $userId]);
    $rows2 = $q2->fetchAll(PDO::FETCH_ASSOC);
    echo "  Found " . count($rows2) . " rows.\n";
    foreach ($rows2 as $row) {
        echo "    ID: {$row['id']}, associate_id: {$row['associate_id']}, user_id: " . ($row['user_id'] ?? 'NULL') . ", amount: {$row['amount']}, status: " . ($row['status'] ?? 'NULL') . ", type: {$row['commission_type']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
