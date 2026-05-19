<?php
/**
 * Complete Enterprise ERP Analysis
 * APS Dream Home - Full System Analysis
 */

$config = require __DIR__ . '/../config/database.php';
$db = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$allTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// ========== ANALYSIS 1: ALL TABLES BY CATEGORY ==========
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║        APS DREAM HOME - ENTERPRISE ERP COMPLETE ANALYSIS         ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

echo "📊 DATABASE: {$config['database']}\n";
echo "📈 TOTAL TABLES: " . count($allTables) . "\n\n";

$cats = [
    'USER & AUTH' => ['users','admin_users','customers','associates','agents','employees','roles','permissions'],
    'PROPERTY' => ['properties','user_properties','property_images','property_inquiries','property_types'],
    'COLONY/PROJECT' => ['colonies','projects','site_master','colony_blocks','colony_plots','plot_booking'],
    'MLM' => ['mlm_members','mlm_tree','mlm_commissions','mlm_payouts','mlm_levels','mlm_ranks','mlm_referrals'],
    'LEADS/CRM' => ['leads','lead_followups','lead_sources','lead_status','inquiries','inquiry_followups'],
    'FINANCE' => ['invoices','invoice_items','payments','expenses','expense_categories','transactions','bank_accounts','ledgers'],
    'HRM' => ['employee_details','attendance','leave_requests','salaries','payroll','departments','designations'],
    'MARKETING' => ['campaigns','campaign_members','newsletter_subscribers','promotions','offers'],
    'REPORTS' => ['reports','daily_sales','monthly_reports','audit_logs','activity_logs','analytics'],
    'SYSTEM' => ['settings','notifications','system_logs','email_templates','sms_templates','api_keys']
];

foreach ($cats as $cat => $tables) {
    $found = array_intersect($tables, $allTables);
    if ($found) {
        echo "┌─ $cat (".count($found).")\n";
        foreach ($found as $f) echo "│  ✓ $f\n";
        echo "└\n";
    }
}

// ========== ANALYSIS 2: USER ROLES ==========
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "👥 USER ROLES & PERMISSIONS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$roles = $db->query("SELECT role, COUNT(*) as cnt FROM users WHERE role IS NOT NULL AND role != '' GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);
echo "System Roles:\n";
foreach ($roles as $r) { echo "  • {$r['role']}: {$r['cnt']} users\n"; }

// Admin roles
$adminRoles = $db->query("SELECT role, COUNT(*) as cnt FROM admin_users GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);
if ($adminRoles) {
    echo "\nAdmin Roles:\n";
    foreach ($adminRoles as $r) { echo "  • {$r['role']}: {$r['cnt']}\n"; }
}

// ========== ANALYSIS 3: CORE BUSINESS DATA ==========
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "🏢 CORE BUSINESS DATA\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$keyTables = [
    'users' => 'All Users',
    'customers' => 'Customers',
    'associates' => 'MLM Associates',
    'agents' => 'Real Estate Agents',
    'employees' => 'Employees',
    'leads' => 'Leads',
    'inquiries' => 'Inquiries',
    'user_properties' => 'Listed Properties',
    'projects' => 'Projects',
    'colonies' => 'Colonies',
    'invoices' => 'Invoices',
    'payments' => 'Payments',
    'expenses' => 'Expenses'
];

foreach ($keyTables as $table => $name) {
    if (in_array($table, $allTables)) {
        try {
            $cnt = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "  $name: $cnt\n";
        } catch (Exception $e) { echo "  $name: -\n"; }
    }
}

// ========== ANALYSIS 4: INQUIRY STATUS ==========
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "📋 INQUIRY STATUS BREAKDOWN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
if (in_array('inquiries', $allTables)) {
    $status = $db->query("SELECT status, COUNT(*) as cnt FROM inquiries GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($status as $s) { echo "  {$s['status']}: {$s['cnt']}\n"; }
}

// ========== ANALYSIS 5: LEADS SOURCE ==========
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "🎯 LEADS SOURCE BREAKDOWN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
if (in_array('leads', $allTables)) {
    try {
        $src = $db->query("SELECT source, COUNT(*) as cnt FROM leads GROUP BY source")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($src as $s) { echo "  {$s['source']}: {$s['cnt']}\n"; }
    } catch (Exception $e) { echo "  No source data\n"; }
}

// ========== ANALYSIS 6: MLM MEMBERS ==========
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "🌐 MLM NETWORK STATUS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
if (in_array('mlm_members', $allTables)) {
    $mlm = $db->query("SELECT status, COUNT(*) as cnt FROM mlm_members GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($mlm as $m) { echo "  {$m['status']}: {$m['cnt']}\n"; }
}

// ========== ANALYSIS 7: FINANCE SUMMARY ==========
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "💰 FINANCE SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
if (in_array('payments', $allTables)) {
    try {
        $inc = $db->query("SELECT SUM(amount) as total FROM payments WHERE type='income'")->fetchColumn();
        echo "  Total Income: ₹" . number_format($inc ?? 0) . "\n";
    } catch (Exception $e) { echo "  Income: ₹0\n"; }
}
if (in_array('expenses', $allTables)) {
    try {
        $exp = $db->query("SELECT SUM(amount) as total FROM expenses")->fetchColumn();
        echo "  Total Expenses: ₹" . number_format($exp ?? 0) . "\n";
    } catch (Exception $e) { echo "  Expenses: ₹0\n"; }
}

// ========== ANALYSIS 8: ADMIN MENU ITEMS ==========
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "🖥️ ADMIN PANEL MENU ITEMS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
if (in_array('admin_menu_items', $allTables)) {
    $menu = $db->query("SELECT module, COUNT(*) as cnt FROM admin_menu_items GROUP BY module ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($menu as $m) { echo "  {$m['module']}: {$m['cnt']} items\n"; }
}

// ========== ANALYSIS 9: RECENT ACTIVITY ==========
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "📝 RECENT ACTIVITY (Last 10)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
if (in_array('activity_logs', $allTables)) {
    $logs = $db->query("SELECT action, user_id, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($logs as $l) { echo "  • {$l['action']} by User#{$l['user_id']} at {$l['created_at']}\n"; }
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "✅ ANALYSIS COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════\n";