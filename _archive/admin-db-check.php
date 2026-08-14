<?php
/**
 * Admin Panel â€” DB Verification Script
 * Access: http://localhost/apsdreamhome/public/admin-db-check.php
 * LOCALHOST ONLY â€” DELETE AFTER USE
 */
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) die('Access denied');

require_once __DIR__ . '/../config/bootstrap.php';
$pdo = \App\Core\Database::getInstance()->getConnection();

echo "<pre style='background:#0f172a;color:#94a3b8;padding:20px;font-family:monospace;font-size:13px;'>";
echo "<span style='color:#38bdf8;font-size:16px;'>=== APS DREAM HOME â€” DB VERIFICATION REPORT ===</span>\n\n";

$checks = [
    // Table name => [description, expected_min_rows, column_to_show]
    'leads'                      => ['CRM Leads', 1, 'name, phone, status'],
    'plot_bookings'              => ['Plot Bookings', 1, 'booking_number, status, total_plot_value'],
    'plot_agreements'            => ['Agreements', 0, 'id, booking_id, status'],
    'booking_payment_schedules'  => ['EMI Schedules', 1, 'booking_id, due_date, amount, accrued_penalty'],
    'mlm_commission_ledger'      => ['Commission Ledger', 100, 'type, amount, status'],
    'mlm_network_tree'           => ['MLM Tree', 10, 'associate_id, parent_id, level'],
    'associates'                 => ['Associates', 10, 'user_id, status'],
    'plots'                      => ['Plots Inventory', 100, 'plot_number, colony_id, status'],
    'colonies'                   => ['Colonies', 4, 'name, is_active'],
    'properties'                 => ['Properties', 1, 'title, status'],
    'users'                      => ['Users', 5, 'name, role, email'],
    'cash_book_entries'          => ['Cash Book', 1, 'type, amount, description'],
    'campaigns'                  => ['Campaigns', 0, 'name, type, status'],
    'marketing_campaigns'        => ['Marketing Campaigns', 0, 'name, type, status'],
    'inquiries'                  => ['Inquiries', 0, 'name, email, message'],
    'site_visits'                => ['Site Visits', 0, 'name, visit_date, status'],
    'support_tickets'            => ['Support Tickets', 0, 'subject, status'],
    'live_chat_sessions'         => ['Live Chat Sessions', 0, 'status, created_at'],
    'kyc_submissions'            => ['KYC Submissions', 0, 'user_id, status'],
    'drip_campaigns'             => ['Drip Campaigns', 0, 'name, status'],
    'crm_sla_rules'              => ['SLA Rules', 4, 'name, breach_hours'],
    'crm_meetings'               => ['CRM Meetings', 0, 'title, scheduled_at'],
    'nps_surveys'                => ['NPS Surveys', 0, 'title, status'],
    'payroll_entries'            => ['Payroll Entries', 0, 'user_id, month, net_salary'],
    'attendance_records'         => ['Attendance', 0, 'user_id, date, status'],
    'leave_applications'         => ['Leave Applications', 0, 'user_id, from_date, status'],
    'company_loans'              => ['Company Loans', 0, 'amount, status'],
    'ad_placements'              => ['Ad Placements', 1, 'name, zone, is_active'],
    'blog_posts'                 => ['Blog Posts', 0, 'title, status'],
    'user_activity_logs_unified' => ['Audit Logs', 10, 'action, created_at'],
    'user_properties'            => ['User Submitted Properties', 0, 'title, status'],
];

// Also check if 'bookings' table exists (the old/wrong one)
$tablesToCheck = array_keys($checks);
$tablesToCheck[] = 'bookings'; // check old wrong table

echo "<span style='color:#f0abfc;'>TABLE STATUS CHECK</span>\n";
echo str_repeat('â”€', 70) . "\n";
printf("%-35s %-10s %-12s %s\n", 'TABLE', 'EXISTS', 'ROWS', 'STATUS');
echo str_repeat('â”€', 70) . "\n";

$issues = [];
$passing = [];
$missing = [];

foreach ($tablesToCheck as $table) {
    $info = $checks[$table] ?? [$table, 0, 'id'];
    [$desc, $minRows] = $info;
    
    try {
        $count = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $status = $count >= $minRows ? 'âœ… OK' : ($minRows === 0 ? 'âœ… OK' : 'âš ï¸� LOW');
        $color = $count >= $minRows ? '#4ade80' : '#fb923c';
        printf("<span style='color:$color'>%-35s %-10s %-12s %s</span>\n", 
            $table, 'YES', "$count rows", $status);
        if ($count < $minRows) {
            $issues[] = "âš ï¸�  $table has only $count rows (expected $minRows+)";
        } else {
            $passing[] = $table;
        }
    } catch (\Exception $e) {
        printf("<span style='color:#f87171'>%-35s %-10s %-12s %s</span>\n", 
            $table, 'MISSING', 'â€”', 'â�Œ MISSING');
        $missing[] = $table;
    }
}

echo "\n<span style='color:#f0abfc;'>CRITICAL TABLE CHECK</span>\n";
echo str_repeat('â”€', 70) . "\n";

// Check if bookings table exists vs plot_bookings
$hasBookings = false;
$hasPlotBookings = false;
try { $pdo->query("SELECT 1 FROM bookings LIMIT 1"); $hasBookings = true; } catch (\Exception $e) {}
try { $pdo->query("SELECT 1 FROM plot_bookings LIMIT 1"); $hasPlotBookings = true; } catch (\Exception $e) {}

echo "bookings table (legacy):     " . ($hasBookings ? "âœ… EXISTS" : "â�Œ MISSING") . "\n";
echo "plot_bookings table (real):  " . ($hasPlotBookings ? "âœ… EXISTS" : "â�Œ MISSING") . "\n";

if ($hasBookings && !$hasPlotBookings) {
    echo "\n<span style='color:#f87171'>â�Œ CRITICAL: Only 'bookings' exists but code uses 'plot_bookings'!</span>\n";
} elseif (!$hasBookings && $hasPlotBookings) {
    echo "\n<span style='color:#fb923c'>âš ï¸�  'bookings' table missing â€” BookingController will fail!</span>\n";
    echo "   FIX: CREATE VIEW bookings AS SELECT * FROM plot_bookings;\n";
} elseif ($hasBookings && $hasPlotBookings) {
    echo "\n<span style='color:#4ade80'>âœ… Both exist â€” check if data is consistent</span>\n";
    $b = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $pb = (int)$pdo->query("SELECT COUNT(*) FROM plot_bookings")->fetchColumn();
    echo "   bookings rows: $b | plot_bookings rows: $pb\n";
}

echo "\n<span style='color:#f0abfc;'>SAMPLE DATA CHECK</span>\n";
echo str_repeat('â”€', 70) . "\n";

// Users by role
$roles = $pdo->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC);
echo "Users by role:\n";
foreach ($roles as $r) echo "  {$r['role']}: {$r['cnt']}\n";

// Commission summary
try {
    $comm = $pdo->query("SELECT type, COUNT(*) as cnt, SUM(amount) as total FROM mlm_commission_ledger GROUP BY type ORDER BY total DESC LIMIT 5")->fetchAll(\PDO::FETCH_ASSOC);
    echo "\nTop 5 Commission Types:\n";
    foreach ($comm as $c) echo "  {$c['type']}: {$c['cnt']} entries = â‚¹" . number_format($c['total']) . "\n";
} catch(\Exception $e) { echo "Commission query failed: " . $e->getMessage() . "\n"; }

// Properties status
try {
    $props = $pdo->query("SELECT status, COUNT(*) as cnt FROM properties GROUP BY status")->fetchAll(\PDO::FETCH_ASSOC);
    echo "\nProperties by status:\n";
    foreach ($props as $p) echo "  {$p['status']}: {$p['cnt']}\n";
} catch(\Exception $e) {}

// Plots status
try {
    $plots = $pdo->query("SELECT status, COUNT(*) as cnt FROM plots GROUP BY status")->fetchAll(\PDO::FETCH_ASSOC);
    echo "\nPlots by status:\n";
    foreach ($plots as $p) echo "  {$p['status']}: {$p['cnt']}\n";
} catch(\Exception $e) {}

echo "\n<span style='color:#f0abfc;'>SUMMARY</span>\n";
echo str_repeat('â”€', 70) . "\n";
echo "âœ… Working tables: " . count($passing) . "\n";
echo "âš ï¸�  Low data tables: " . count($issues) . "\n";
echo "â�Œ Missing tables: " . count($missing) . "\n";

if (!empty($missing)) {
    echo "\n<span style='color:#f87171;'>MISSING TABLES (need to CREATE):</span>\n";
    foreach ($missing as $t) echo "  - $t\n";
}

if (!empty($issues)) {
    echo "\n<span style='color:#fb923c;'>ISSUES:</span>\n";
    foreach ($issues as $i) echo "  $i\n";
}

echo "\nâœ… DB Verification Complete!\n";
echo "</pre>";?>