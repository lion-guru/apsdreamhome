<?php
require_once dirname(__DIR__) . '/app/Core/ConfigService.php';
require_once dirname(__DIR__) . '/app/Core/Database/Database.php';

try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();
    
    $userId = 2; // agent1

    echo "Querying commissions table for user_id = 2 OR associate_id = 2:\n";
    $q = $db->query("SELECT * FROM commissions WHERE user_id = 2 OR associate_id = 2");
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        echo "  - ID: {$row['id']}, user_id: {$row['user_id']}, associate_id: {$row['associate_id']}, amount: {$row['amount']}, status: {$row['status']}, type: {$row['commission_type']}\n";
    }

    echo "\nQuerying mlm_commission_ledger table for beneficiary_user_id = 2:\n";
    $q2 = $db->query("SELECT * FROM mlm_commission_ledger WHERE beneficiary_user_id = 2");
    while ($row = $q2->fetch(PDO::FETCH_ASSOC)) {
        echo "  - ID: {$row['id']}, amount: {$row['amount']}, status: {$row['status']}, type: {$row['commission_type']}\n";
    }

    echo "\nSum of commissions table:\n";
    $sum1 = $db->query("SELECT SUM(amount) FROM commissions WHERE (user_id = 2 OR associate_id = 2)")->fetchColumn();
    $sum2 = $db->query("SELECT SUM(amount) FROM commissions WHERE (user_id = 2 OR associate_id = 2) AND status = 'pending'")->fetchColumn();
    echo "  Total: " . ($sum1 ?? 'NULL') . "\n";
    echo "  Pending: " . ($sum2 ?? 'NULL') . "\n";

    echo "\nSum of mlm_commission_ledger:\n";
    $sum3 = $db->query("SELECT SUM(amount) FROM mlm_commission_ledger WHERE beneficiary_user_id = 2")->fetchColumn();
    $sum4 = $db->query("SELECT SUM(amount) FROM mlm_commission_ledger WHERE beneficiary_user_id = 2 AND status = 'pending'")->fetchColumn();
    echo "  Total: " . ($sum3 ?? 'NULL') . "\n";
    echo "  Pending: " . ($sum4 ?? 'NULL') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
