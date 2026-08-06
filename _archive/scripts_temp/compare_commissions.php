<?php
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

echo "=== BOOKING_COMMISSIONS UNIQUE DATA ===\n\n";

// 1. Commission types in booking_commissions
$types = $db->fetchAll("SELECT DISTINCT commission_type, COUNT(*) as cnt FROM booking_commissions GROUP BY commission_type");
echo "Commission types in booking_commissions:\n";
foreach ($types as $t) {
    echo "  {$t['commission_type']}: {$t['cnt']}\n";
}

// 2. Commission types in mlm_commission_ledger
$types = $db->fetchAll("SELECT DISTINCT commission_type, COUNT(*) as cnt FROM mlm_commission_ledger GROUP BY commission_type");
echo "\nCommission types in mlm_commission_ledger:\n";
foreach ($types as $t) {
    echo "  {$t['commission_type']}: {$t['cnt']}\n";
}

// 3. Check for associate_referral type (only in booking_commissions?)
echo "\n=== Checking associate_referral in mlm_commission_ledger ===\n";
$count = $db->fetch("SELECT COUNT(*) as c FROM mlm_commission_ledger WHERE commission_type = 'associate_referral'");
echo "Count: {$count['c']}\n";

// 4. Check for agent_referral type
$count = $db->fetch("SELECT COUNT(*) as c FROM mlm_commission_ledger WHERE commission_type = 'agent_referral'");
echo "agent_referral Count: {$count['c']}\n";

// 5. Check for team_override type
$count = $db->fetch("SELECT COUNT(*) as c FROM mlm_commission_ledger WHERE commission_type = 'team_override'");
echo "team_override Count: {$count['c']}\n";

// 6. Check status values
echo "\n=== Status values in booking_commissions ===\n";
$statuses = $db->fetchAll("SELECT DISTINCT status, COUNT(*) as cnt FROM booking_commissions GROUP BY status");
foreach ($statuses as $s) {
    echo "  {$s['status']}: {$s['cnt']}\n";
}

echo "\n=== Status values in mlm_commission_ledger ===\n";
$statuses = $db->fetchAll("SELECT DISTINCT status, COUNT(*) as cnt FROM mlm_commission_ledger GROUP BY status");
foreach ($statuses as $s) {
    echo "  {$s['status']}: {$s['cnt']}\n";
}

// 7. Check if any booking has commission in booking_commissions but NOT in mlm_commission_ledger
echo "\n=== Bookings with legacy-only commissions ===\n";
$legacyOnly = $db->fetchAll("
    SELECT bc.booking_id, COUNT(*) as cnt 
    FROM booking_commissions bc
    LEFT JOIN mlm_commission_ledger ml ON ml.booking_id = bc.booking_id
    WHERE ml.id IS NULL
    GROUP BY bc.booking_id
");
echo "Bookings with only legacy commissions: " . count($legacyOnly) . "\n";
foreach ($legacyOnly as $l) {
    echo "  Booking {$l['booking_id']}: {$l['cnt']} legacy entries\n";
}

// 8. Check unique columns in booking_commissions
echo "\n=== booking_commissions columns ===\n";
$cols = $db->fetchAll("SHOW COLUMNS FROM booking_commissions");
foreach ($cols as $c) {
    echo "  {$c['Field']}: {$c['Type']}\n";
}

echo "\n=== mlm_commission_ledger columns ===\n";
$cols = $db->fetchAll("SHOW COLUMNS FROM mlm_commission_ledger");
foreach ($cols as $c) {
    echo "  {$c['Field']}: {$c['Type']}\n";
}