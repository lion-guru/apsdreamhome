<?php
require_once dirname(__DIR__) . '/app/Core/ConfigService.php';
require_once dirname(__DIR__) . '/app/Core/Database/Database.php';

try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();
    echo "Columns of mlm_commission_ledger:\n";
    $q = $db->query("DESCRIBE mlm_commission_ledger");
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Field']} - {$row['Type']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
