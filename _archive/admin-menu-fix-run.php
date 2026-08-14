<?php
// Admin Menu Fix - Accessible via web (DELETE THIS FILE AFTER USE)
// Access: http://localhost/apsdreamhome/admin/menu-fix-run.php

// Basic security - only from localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('Access denied');
}

ob_start();

require_once __DIR__ . '/../config/bootstrap.php';

echo "<pre style='font-family:monospace;background:#1a1a2e;color:#e2e8f0;padding:20px;margin:0;min-height:100vh;'>\n";
echo "=== ADMIN MENU STRUCTURE FIX ===\n\n";

try {
    $pdo = \App\Core\Database::getInstance()->getConnection();

    // STEP 1: Remove exact duplicate URLs (keep lowest id)
    echo "STEP 1: Removing duplicate URLs...\n";
    $dupes = $pdo->query("
        SELECT url, GROUP_CONCAT(id ORDER BY id ASC) as ids, COUNT(*) as cnt
        FROM admin_menu_items WHERE is_active = 1
        GROUP BY url HAVING cnt > 1
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $removed = 0;
    foreach ($dupes as $d) {
        $ids = explode(',', $d['ids']);
        $keepId = array_shift($ids);
        foreach ($ids as $delId) {
            $pdo->exec("DELETE FROM admin_menu_items WHERE id = $delId");
            echo "  âœ“ Removed duplicate: {$d['url']} (id=$delId, kept id=$keepId)\n";
            $removed++;
        }
    }
    echo "  â†’ Removed $removed duplicates\n\n";

    // STEP 2: Merge bookings section INTO sales section
    echo "STEP 2: Merging 'bookings' section into 'sales'...\n";
    $maxSalesOrder = (int)$pdo->query("SELECT COALESCE(MAX(order_index), 0) FROM admin_menu_items WHERE section = 'sales'")->fetchColumn();
    $moved = $pdo->exec("UPDATE admin_menu_items SET section = 'sales', order_index = order_index + $maxSalesOrder WHERE section = 'bookings'");
    echo "  âœ“ Moved $moved items from 'bookings' to 'sales'\n\n";

    // STEP 3: Move CRM items from marketing to crm
    echo "STEP 3: Moving CRM items from 'marketing' to 'crm'...\n";
    $crmUrls = [
        '/admin/crm/sla', '/admin/meetings', '/admin/crm/voice',
        '/admin/crm/email-tracking/stats', '/admin/crm/drip',
        '/admin/crm/custom-fields', '/admin/crm/dedup',
        '/admin/crm/segments', '/admin/crm/forms',
        '/admin/crm/agentic', '/admin/crm/role-dashboard',
        '/admin/crm/analytics', '/admin/crm/bulk-send',
        '/admin/notification-dashboard',
        '/admin/referrals/leaderboard', '/admin/referrals/share-analytics', '/admin/referrals/tiers',
    ];
    $movedCrm = 0;
    foreach ($crmUrls as $url) {
        $r = $pdo->prepare("UPDATE admin_menu_items SET section = 'crm' WHERE url = ?");
        $r->execute([$url]);
        if ($r->rowCount() > 0) { echo "  âœ“ Moved to crm: $url\n"; $movedCrm++; }
    }
    echo "  â†’ Moved $movedCrm items\n\n";

    // STEP 4: Fix incorrect URLs
    echo "STEP 4: Fixing wrong URLs...\n";
    $urlFixes = [
        ['/admin/departments', '/admin/hrm/departments'],
        ['/admin/designations', '/admin/hrm/designations'],
        ['/admin/cash-collections', '/admin/finance/collections'],
    ];
    foreach ($urlFixes as [$oldUrl, $newUrl]) {
        $e1 = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
        $e1->execute([$oldUrl]);
        if ($e1->fetch()) {
            $e2 = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
            $e2->execute([$newUrl]);
            if (!$e2->fetch()) {
                $pdo->prepare("UPDATE admin_menu_items SET url = ? WHERE url = ?")->execute([$newUrl, $oldUrl]);
                echo "  âœ“ Fixed: $oldUrl â†’ $newUrl\n";
            } else {
                $pdo->prepare("DELETE FROM admin_menu_items WHERE url = ?")->execute([$oldUrl]);
                echo "  âœ“ Removed old: $oldUrl\n";
            }
        }
    }
    echo "\n";

    // STEP 5: Add missing menu items
    echo "STEP 5: Adding missing menu items...\n";
    $newItems = [
        ['Live Chat', 'fas fa-comments', '/admin/live-chat', 'crm', 50, 'crm.view'],
        ['KYC Verification', 'fas fa-id-card', '/admin/kyc', 'crm', 51, 'crm.view'],
        ['Departments', 'fas fa-building', '/admin/hrm/departments', 'hrm', 9, 'hrm.view'],
        ['Designations', 'fas fa-user-tag', '/admin/hrm/designations', 'hrm', 10, 'hrm.view'],
        ['Company Loans', 'fas fa-hand-holding-usd', '/admin/company-loans', 'finance', 22, 'financial.view'],
        ['Leave Management', 'fas fa-calendar-times', '/admin/backoffice/leaves', 'hrm', 11, 'hrm.view'],
        ['Compliance Scorecard', 'fas fa-shield-alt', '/admin/compliance-scorecard', 'system', 6, 'system.settings'],
        ['Job Applications', 'fas fa-file-alt', '/admin/careers/manage', 'hrm', 13, 'hrm.view'],
    ];
    $addedCount = 0;
    foreach ($newItems as [$name, $icon, $url, $section, $order, $permKey]) {
        $check = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
        $check->execute([$url]);
        if (!$check->fetch()) {
            $pdo->prepare("INSERT INTO admin_menu_items (name, icon, url, section, order_index, permission_key, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())")
                ->execute([$name, $icon, $url, $section, $order, $permKey]);
            echo "  âœ“ Added: $name â†’ $url\n";
            $addedCount++;
        } else {
            echo "  - Exists: $url\n";
        }
    }
    echo "  â†’ Added $addedCount items\n\n";

    // STEP 6: Rename confusing items
    echo "STEP 6: Fixing naming...\n";
    $renames = [
        ['/admin/campaigns', 'Campaigns'],
        ['/admin/network/tree', 'Network Tree'],
        ['/admin/godmode', 'SuperAdmin Console'],
        ['/admin/efiling', 'Tax E-Filing'],
        ['/admin/mlm/clawbacks', 'Commission Clawbacks'],
        ['/admin/pdfs', 'Document Generator'],
        ['/admin/features/registrations', 'User Registrations'],
        ['/admin/analytics', 'Analytics Dashboard'],
        ['/admin/finance/collections', 'Cash Collections'],
    ];
    foreach ($renames as [$url, $newName]) {
        $r = $pdo->prepare("UPDATE admin_menu_items SET name = ? WHERE url = ?");
        $r->execute([$newName, $url]);
        if ($r->rowCount() > 0) echo "  âœ“ Renamed: $url â†’ \"$newName\"\n";
    }
    echo "\n";

    // STEP 7: Rename technology â†’ ai_tech
    echo "STEP 7: Renaming 'technology' to 'ai_tech'...\n";
    $r = $pdo->exec("UPDATE admin_menu_items SET section = 'ai_tech' WHERE section = 'technology'");
    echo "  âœ“ Renamed $r items\n\n";

    // VERIFY
    echo "=== VERIFICATION ===\n\n";
    $remainDupes = $pdo->query("SELECT url, COUNT(*) as cnt FROM admin_menu_items WHERE is_active = 1 GROUP BY url HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
    echo empty($remainDupes) ? "âœ… ZERO duplicate URLs remaining\n\n" : "âš ï¸�  Duplicates still exist!\n\n";
    
    $sections = $pdo->query("SELECT section, COUNT(*) as items FROM admin_menu_items WHERE is_active = 1 GROUP BY section ORDER BY section")->fetchAll(PDO::FETCH_ASSOC);
    echo "SECTIONS (" . count($sections) . " total):\n";
    $total = 0;
    foreach ($sections as $s) {
        echo "  " . str_pad($s['section'], 18) . " â†’ " . str_pad($s['items'], 3) . " items\n";
        $total += $s['items'];
    }
    echo "  " . str_pad('TOTAL', 18) . " â†’ $total items\n\n";
    echo "âœ… COMPLETE! Admin menu structure has been fixed.\n";
    echo "â†’ Reload: <a href='/apsdreamhome/admin/dashboard' style='color:#38bdf8'>Admin Dashboard</a>\n";

} catch (\Exception $e) {
    echo "â�Œ ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";?>