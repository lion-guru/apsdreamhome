<?php
// Import the SQL dump into MariaDB
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'apsdreamhome';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$db = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Connected to MariaDB successfully.\n";
echo "Importing restore.sql...\n";

$sqlFile = 'C:/xampp/htdocs/apsdreamhome/storage/backups/restore.sql';
$sql = file_get_contents($sqlFile);

// Split into individual statements
$statements = preg_split('/;\s*\n/', $sql);
$total = count($statements);
echo "Found $total SQL statements.\n";

$success = 0;
$errors = 0;
foreach ($statements as $i => $stmt) {
    $stmt = trim($stmt);
    if (empty($stmt) || strlen($stmt) < 5) continue;
    try {
        $db->exec($stmt);
        $success++;
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
        // Skip "already exists" errors
        if (strpos($errorMsg, 'already exists') !== false || strpos($errorMsg, '1050') !== false) {
            $success++;
        } else {
            // Only log first 10 errors
            if ($errors < 10) {
                echo "ERROR at statement $i: $errorMsg\n";
            }
            $errors++;
        }
    }
}

echo "\n=== IMPORT COMPLETE ===\n";
echo "Success: $success | Errors: $errors | Total: $total\n";

// Verify mlm_commission_ledger
$count = $db->query("SELECT COUNT(*) FROM mlm_commission_ledger")->fetchColumn();
echo "\nmlm_commission_ledger rows: $count\n";
echo "\nAll done!\n";
