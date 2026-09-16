<?php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'apsdreamhome';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);

$checks = [
    'plots without colony' => "SELECT count(*) FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id WHERE c.id IS NULL AND p.colony_id IS NOT NULL",
    'plot_bookings without plot' => "SELECT count(*) FROM plot_bookings b LEFT JOIN plots p ON b.plot_id = p.id WHERE p.id IS NULL AND b.plot_id IS NOT NULL",
    'plot_bookings without customer' => "SELECT count(*) FROM plot_bookings b LEFT JOIN customers c ON b.customer_id = c.id WHERE c.id IS NULL AND b.customer_id IS NOT NULL",
    'leads without assigned_to user' => "SELECT count(*) FROM leads l LEFT JOIN users u ON l.assigned_to = u.id WHERE u.id IS NULL AND l.assigned_to IS NOT NULL AND l.assigned_to > 0",
    'booking_payment_schedules without plot_booking' => "SELECT count(*) FROM booking_payment_schedules s LEFT JOIN plot_bookings b ON s.booking_id = b.id WHERE b.id IS NULL",
    'booking_emis without booking' => "SELECT count(*) FROM booking_emis e LEFT JOIN bookings b ON e.booking_id = b.id WHERE b.id IS NULL",
    'mlm_commission_ledger without associate/user' => "SELECT count(*) FROM mlm_commission_ledger l LEFT JOIN users u ON l.user_id = u.id WHERE u.id IS NULL AND l.user_id IS NOT NULL",
    'site_visits without lead' => "SELECT count(*) FROM site_visits s LEFT JOIN leads l ON s.lead_id = l.id WHERE l.id IS NULL AND s.lead_id IS NOT NULL",
];

echo "=== ORPHAN RECORD AUDIT ===\n";
foreach ($checks as $desc => $sql) {
    try {
        $cnt = $pdo->query($sql)->fetchColumn();
        echo sprintf("%-45s : %d orphans\n", $desc, $cnt);
    } catch (Exception $e) {
        echo sprintf("%-45s : ERROR (%s)\n", $desc, $e->getMessage());
    }
}
