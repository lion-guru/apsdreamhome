<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;
$db = Database::getInstance();

$attempts = $db->fetchAll("SELECT * FROM login_attempts ORDER BY created_at DESC LIMIT 10");
echo "=== LOGIN ATTEMPTS ===\n";
foreach ($attempts as $a) {
    echo "{$a['created_at']} | {$a['identifier']} | Success: {$a['success']}\n";
}

// Clear any lockout for test accounts
$db->execute("DELETE FROM login_attempts WHERE identifier LIKE '%apsdreamhome.test%' OR identifier LIKE '%admin@apsdreamhome.com%'");
echo "CLEARED TEST LOGIN ATTEMPTS.\n";
