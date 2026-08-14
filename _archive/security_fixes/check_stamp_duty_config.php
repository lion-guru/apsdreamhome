<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

$stmt = $pdo->query("SHOW TABLES LIKE 'stamp_duty_config'");
echo "stamp_duty_config table exists: " . ($stmt->rowCount() > 0 ? "YES" : "NO") . "\n";

if ($stmt->rowCount() > 0) {
    $stmt = $pdo->query("DESCRIBE stamp_duty_config");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Field']} ({$row['Type']})" . ($row['Key'] ? " [{$row['Key']}]" : "") . "\n";
    }
    
    $stmt = $pdo->query("SELECT state_code, state_name, male_rate, female_rate FROM stamp_duty_config WHERE is_active = 1");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['state_code']} - {$row['state_name']} (M: {$row['male_rate']}%, F: {$row['female_rate']}%)\n";
    }
}?>