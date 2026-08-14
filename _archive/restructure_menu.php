<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== STEP 1: Remove exact URL duplicates ===" . PHP_EOL;
$sql = "DELETE m1 FROM admin_menu_items m1
        INNER JOIN admin_menu_items m2 
        ON m1.url = m2.url AND m1.id > m2.id
        WHERE m1.is_active = 1 AND m2.is_active = 1";
$stmt = $pdo->exec($sql);
echo "Deleted $stmt duplicate rows" . PHP_EOL;

echo "=== STEP 2: Merge bookings INTO sales (renaming bookings to sales) ===" . PHP_EOL;
$stmt = $pdo->exec("UPDATE admin_menu_items SET section = 'sales', order_index = order_index + 10 WHERE section = 'bookings'");
echo "Updated $stmt rows from bookings to sales" . PHP_EOL;

echo "=== STEP 3: Move CRM items OUT of marketing INTO crm ===" . PHP_EOL;
$crmUrls = [
    '/admin/crm/sla',
    '/admin/meetings',
    '/admin/crm/voice',
    '/admin/crm/email-tracking/stats',
    '/admin/crm/drip',
    '/admin/crm/custom-fields',
    '/admin/crm/dedup',
    '/admin/crm/segments',
    '/admin/crm/forms',
    '/admin/crm/agentic',
    '/admin/crm/role-dashboard',
    '/admin/crm/routing',
    '/admin/crm/assignments',
    '/admin/crm/bulk-send',
    '/admin/crm/templates',
    '/admin/crm/analytics',
    '/admin/crm/settings',
];
$placeholders = str_repeat('?,', count($crmUrls) - 1) . '?';
$sql = "UPDATE admin_menu_items SET section = 'crm' WHERE section = 'marketing' AND url IN ($placeholders)";
$stmt = $pdo->prepare($sql);
$stmt->execute($crmUrls);
echo "Moved {$stmt->rowCount()} CRM items from marketing to crm" . PHP_EOL;

echo "=== STEP 4: Move Notification Dashboard to operations ===" . PHP_EOL;
$stmt = $pdo->exec("UPDATE admin_menu_items SET section = 'operations' WHERE url = '/admin/notification-dashboard'");
echo "Updated $stmt rows" . PHP_EOL;

echo "=== STEP 5: Add missing items ===" . PHP_EOL;
$missingItems = [
    ['Live Chat', 'fas fa-comments', '/admin/live-chat', 'crm', 21, 1, 'crm.view'],
    ['KYC Verification', 'fas fa-id-card', '/admin/kyc', 'crm', 22, 1, 'crm.view'],
    ['Departments', 'fas fa-building', '/admin/departments', 'hrm', 9, 1, 'hrm.view'],
    ['Designations', 'fas fa-user-tag', '/admin/designations', 'hrm', 10, 1, 'hrm.view'],
    ['Company Loans', 'fas fa-hand-holding-usd', '/admin/company-loans', 'finance', 21, 1, 'financial.view'],
    ['Leave Management', 'fas fa-calendar-times', '/admin/backoffice/leaves', 'hrm', 11, 1, 'hrm.view'],
    ['Payslips', 'fas fa-file-invoice-dollar', '/admin/backoffice/payslips', 'hrm', 12, 1, 'hrm.view'],
    ['Cash Flow Forecast', 'fas fa-chart-line', '/admin/finance/cash-flow', 'finance', 15, 1, 'financial.view'],
];
$inserted = 0;
foreach ($missingItems as $item) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO admin_menu_items (name, icon, url, section, order_index, is_active, permission_key) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute($item);
    $inserted += $stmt->rowCount();
}
echo "Inserted $inserted missing items" . PHP_EOL;

echo "=== STEP 6: Rename confusing items ===" . PHP_EOL;
$renames = [
    ['name' => 'Campaigns', 'url' => '/admin/campaigns'],
    ['name' => 'Network Tree', 'url' => '/admin/network/tree'],
    ['name' => 'SuperAdmin Console', 'url' => '/admin/godmode'],
    ['name' => 'Tax E-Filing', 'url' => '/admin/efiling'],
    ['name' => 'Commission Clawbacks', 'url' => '/admin/mlm/clawbacks'],
    ['name' => 'Document Generator', 'url' => '/admin/pdfs'],
    ['name' => 'User Registrations', 'url' => '/admin/features/registrations'],
    ['name' => 'Genealogy Tree', 'url' => '/admin/mlm/genealogy'],
    ['name' => 'Booking Approvals', 'url' => '/admin/sales/approvals'],
];
$updated = 0;
foreach ($renames as $r) {
    $stmt = $pdo->prepare("UPDATE admin_menu_items SET name = ? WHERE url = ? AND name != ?");
    $stmt->execute([$r['name'], $r['url'], $r['name']]);
    $updated += $stmt->rowCount();
}
echo "Renamed $updated items" . PHP_EOL;

echo "=== STEP 7: Rename technology section to ai_tech ===" . PHP_EOL;
$stmt = $pdo->exec("UPDATE admin_menu_items SET section = 'ai_tech' WHERE section = 'technology'");
echo "Updated $stmt rows" . PHP_EOL;

echo "=== STEP 8: Rename employee section to hrm_portal (or merge?) ===" . PHP_EOL;
// Keep employee separate for now as it's a portal

echo "=== VERIFICATION: Check for remaining duplicates ===" . PHP_EOL;
$stmt = $pdo->query("SELECT url, COUNT(*) as cnt FROM admin_menu_items WHERE is_active = 1 GROUP BY url HAVING cnt > 1 ORDER BY cnt DESC");
$dups = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($dups) === 0) {
    echo "No duplicate URLs found!" . PHP_EOL;
} else {
    foreach ($dups as $d) {
        echo "DUPLICATE: {$d['url']} ({$d['cnt']} times)" . PHP_EOL;
    }
}

echo "=== VERIFICATION: Section counts ===" . PHP_EOL;
$stmt = $pdo->query("SELECT section, COUNT(*) as items FROM admin_menu_items WHERE is_active = 1 GROUP BY section ORDER BY section");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "{$row['section']}: {$row['items']} items" . PHP_EOL;
}

echo "=== DONE ===" . PHP_EOL;?>