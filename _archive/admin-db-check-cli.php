<?php
/**
 * Admin Panel — DB Verification Script (CLI Version)
 * Run: php public\admin-db-check-cli.php
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== APS DREAM HOME — DB VERIFICATION REPORT ===\n\n";

$checks = [
    'leads'                      => ['CRM Leads', 1],
    'plot_bookings'              => ['Plot Bookings', 1],
    'plot_agreements'            => ['Agreements', 0],
    'booking_payment_schedules'  => ['EMI Schedules', 1],
    'mlm_commission_ledger'      => ['Commission Ledger', 100],
    'mlm_network_tree'           => ['MLM Tree', 10],
    'associates'                 => ['Associates', 10],
    'plots'                      => ['Plots Inventory', 100],
    'colonies'                   => ['Colonies', 4],
    'properties'                 => ['Properties', 1],
    'users'                      => ['Users', 5],
    'cash_book_entries'          => ['Cash Book', 1],
    'campaigns'                  => ['Campaigns', 0],
    'marketing_campaigns'        => ['Marketing Campaigns', 0],
    'inquiries'                  => ['Inquiries', 0],
    'site_visits'                => ['Site Visits', 0],
    'support_tickets'            => ['Support Tickets', 0],
    'live_chat_sessions'         => ['Live Chat Sessions', 0],
    'kyc_submissions'            => ['KYC Submissions', 0],
    'drip_campaigns'             => ['Drip Campaigns', 0],
    'crm_sla_rules'              => ['SLA Rules', 4],
    'crm_meetings'               => ['CRM Meetings', 0],
    'nps_surveys'                => ['NPS Surveys', 0],
    'payroll_entries'            => ['Payroll Entries', 0],
    'attendance_records'         => ['Attendance', 0],
    'leave_applications'         => ['Leave Applications', 0],
    'company_loans'              => ['Company Loans', 0],
    'ad_placements'              => ['Ad Placements', 1],
    'blog_posts'                 => ['Blog Posts', 0],
    'user_activity_logs_unified' => ['Audit Logs', 10],
    'user_properties'            => ['User Submitted Properties', 0],
];

$tablesToCheck = array_keys($checks);
$tablesToCheck[] = 'bookings'; // check old wrong table

echo "TABLE STATUS CHECK\n";
echo str_repeat('─', 70) . "\n";
printf("%-35s %-10s %-12s %s\n", 'TABLE', 'EXISTS', 'ROWS', 'STATUS');
echo str_repeat('─', 70) . "\n";

$issues = [];
$passing = [];
$missing = [];

foreach ($tablesToCheck as $table) {
    $info = $checks[$table] ?? [$table, 0];
    [$desc, $minRows] = $info;
    
    try {
        $count = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $status = $count >= $minRows ? 'OK' : ($minRows === 0 ? 'OK' : 'LOW');
        printf("%-35s %-10s %-12s %s\n", $table, 'YES', "$count rows", $status);
        if ($count < $minRows) {
            $issues[] = "$table has only $count rows (expected $minRows+)";
        } else {
            $passing[] = $table;
        }
    } catch (Exception $e) {
        printf("%-35s %-10s %-12s %s\n", $table, 'MISSING', '—', 'MISSING');
        $missing[] = $table;
    }
}

echo "\nCRITICAL TABLE CHECK\n";
echo str_repeat('─', 70) . "\n";

$hasBookings = false;
$hasPlotBookings = false;
try { $pdo->query("SELECT 1 FROM bookings LIMIT 1"); $hasBookings = true; } catch (Exception $e) {}
try { $pdo->query("SELECT 1 FROM plot_bookings LIMIT 1"); $hasPlotBookings = true; } catch (Exception $e) {}

echo "bookings table (legacy):     " . ($hasBookings ? "EXISTS" : "MISSING") . "\n";
echo "plot_bookings table (real):  " . ($hasPlotBookings ? "EXISTS" : "MISSING") . "\n";

if ($hasBookings && !$hasPlotBookings) {
    echo "\nCRITICAL: Only 'bookings' exists but code uses 'plot_bookings'!\n";
} elseif (!$hasBookings && $hasPlotBookings) {
    echo "\nWARNING: 'bookings' table missing — BookingController will fail!\n";
    echo "   FIX: CREATE VIEW bookings AS SELECT * FROM plot_bookings;\n";
} elseif ($hasBookings && $hasPlotBookings) {
    echo "\nBoth exist — check if data is consistent\n";
    $b = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $pb = (int)$pdo->query("SELECT COUNT(*) FROM plot_bookings")->fetchColumn();
    echo "   bookings rows: $b | plot_bookings rows: $pb\n";
}

echo "\nSAMPLE DATA CHECK\n";
echo str_repeat('─', 70) . "\n";

$roles = $pdo->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
echo "Users by role:\n";
foreach ($roles as $r) echo "  {$r['role']}: {$r['cnt']}\n";

try {
    $comm = $pdo->query("SELECT type, COUNT(*) as cnt, SUM(amount) as total FROM mlm_commission_ledger GROUP BY type ORDER BY total DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nTop 5 Commission Types:\n";
    foreach ($comm as $c) echo "  {$c['type']}: {$c['cnt']} entries = ₹" . number_format($c['total']) . "\n";
} catch(Exception $e) { echo "Commission query failed: " . $e->getMessage() . "\n"; }

try {
    $props = $pdo->query("SELECT status, COUNT(*) as cnt FROM properties GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nProperties by status:\n";
    foreach ($props as $p) echo "  {$p['status']}: {$p['cnt']}\n";
} catch(Exception $e) {}

try {
    $plots = $pdo->query("SELECT status, COUNT(*) as cnt FROM plots GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nPlots by status:\n";
    foreach ($plots as $p) echo "  {$p['status']}: {$p['cnt']}\n";
} catch(Exception $e) {}

echo "\nSUMMARY\n";
echo str_repeat('─', 70) . "\n";
echo "✅ Working tables: " . count($passing) . "\n";
echo "⚠️  Low data tables: " . count($issues) . "\n";
echo "❌ Missing tables: " . count($missing) . "\n";

if (!empty($missing)) {
    echo "\nMISSING TABLES (need to CREATE):\n";
    foreach ($missing as $t) echo "  - $t\n";
}

if (!empty($issues)) {
    echo "\nISSUES:\n";
    foreach ($issues as $i) echo "  $i\n";
}

echo "\n✅ DB Verification Complete!\n";