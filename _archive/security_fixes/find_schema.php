<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

$tables = ['states', 'districts', 'banks', 'user_bank_accounts', 'bank_accounts', 'bank_accounts_master'];
foreach ($tables as $table) {
    echo "=== $table ===\n";
    $stmt = $pdo->query("DESCRIBE `$table`");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Field']} ({$row['Type']})" . ($row['Key'] ? " [{$row['Key']}]" : "") . "\n";
    }
    echo "\n";
}