<?php
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

echo "Dropping booking_commissions table...\n";

// Check if any views/controllers still reference it
$refs = $db->fetchAll("
    SELECT TABLE_NAME FROM information_schema.VIEWS 
    WHERE VIEW_DEFINITION LIKE '%booking_commissions%'
");
if (!empty($refs)) {
    echo "WARNING: Views still reference booking_commissions:\n";
    print_r($refs);
    exit;
}

echo "No views reference booking_commissions. Proceeding to drop...\n";

$db->execute("DROP TABLE booking_commissions");
echo "Table dropped successfully.\n";

echo "Verifying...\n";
$exists = $db->fetch("SELECT COUNT(*) as c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'booking_commissions'");
if ($exists['c'] == 0) {
    echo "Confirmed: booking_commissions table no longer exists.\n";
} else {
    echo "ERROR: Table still exists!\n";
}