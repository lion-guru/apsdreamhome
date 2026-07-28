<?php
/**
 * Admin Menu Structure Fix Script
 * 
 * CHANGES:
 * 1. Remove duplicate URLs (same URL in multiple sections)
 * 2. Merge bookings section into sales section
 * 3. Move CRM items out of marketing → crm section
 * 4. Fix wrong URLs (departments = /admin/hrm/departments)
 * 5. Add missing menu items with correct URLs
 * 6. Rename confusing menu items
 * 7. Rename 'technology' section to 'ai_tech'
 * 
 * Run: php scripts/fix_admin_menu_structure.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

try {
    $pdo = \App\Core\Database::getInstance()->getConnection();
    echo "=== ADMIN MENU STRUCTURE FIX ===\n\n";

    // -------------------------------------------------------
    // STEP 1: Remove exact duplicate URLs (keep lowest id)
    // -------------------------------------------------------
    echo "STEP 1: Removing duplicate URLs...\n";
    $dupes = $pdo->query("
        SELECT url, GROUP_CONCAT(id ORDER BY id ASC) as ids, COUNT(*) as cnt
        FROM admin_menu_items 
        WHERE is_active = 1
        GROUP BY url 
        HAVING cnt > 1
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $removed = 0;
    foreach ($dupes as $d) {
        $ids = explode(',', $d['ids']);
        $keepId = array_shift($ids); // keep first (lowest id)
        foreach ($ids as $delId) {
            $pdo->exec("DELETE FROM admin_menu_items WHERE id = $delId");
            echo "  ✓ Removed duplicate: {$d['url']} (id=$delId, kept id=$keepId)\n";
            $removed++;
        }
    }
    echo "  → Removed $removed duplicates\n\n";

    // -------------------------------------------------------
    // STEP 2: Merge bookings section INTO sales section
    // -------------------------------------------------------
    echo "STEP 2: Merging 'bookings' section into 'sales'...\n";
    
    // Get max order_index in sales section
    $maxSalesOrder = $pdo->query("SELECT COALESCE(MAX(order_index), 0) FROM admin_menu_items WHERE section = 'sales'")->fetchColumn();
    
    // Move bookings items to sales (with offset to avoid order collision)
    $moved = $pdo->exec("
        UPDATE admin_menu_items 
        SET section = 'sales', order_index = order_index + $maxSalesOrder
        WHERE section = 'bookings'
    ");
    echo "  ✓ Moved $moved items from 'bookings' to 'sales'\n\n";

    // -------------------------------------------------------
    // STEP 3: Move CRM-specific items OUT of marketing → crm
    // -------------------------------------------------------
    echo "STEP 3: Moving CRM items from 'marketing' to 'crm'...\n";
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
        '/admin/crm/analytics',
        '/admin/crm/bulk-send',
        '/admin/notification-dashboard',
    ];
    
    $movedCrm = 0;
    foreach ($crmUrls as $url) {
        $r = $pdo->prepare("UPDATE admin_menu_items SET section = 'crm' WHERE url = ?");
        $r->execute([$url]);
        if ($r->rowCount() > 0) {
            echo "  ✓ Moved to crm: $url\n";
            $movedCrm++;
        }
    }
    echo "  → Moved $movedCrm items to 'crm' section\n\n";

    // -------------------------------------------------------
    // STEP 4: Fix incorrect department/designation URLs
    // -------------------------------------------------------
    echo "STEP 4: Fixing wrong URLs for departments/designations...\n";
    
    $urlFixes = [
        ['/admin/departments', '/admin/hrm/departments'],
        ['/admin/designations', '/admin/hrm/designations'],
        ['/admin/cash-collections', '/admin/finance/collections'],
    ];
    
    foreach ($urlFixes as [$oldUrl, $newUrl]) {
        // Check if old URL exists
        $exists = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
        $exists->execute([$oldUrl]);
        if ($exists->fetch()) {
            // Check if new URL already exists
            $newExists = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
            $newExists->execute([$newUrl]);
            if (!$newExists->fetch()) {
                $pdo->prepare("UPDATE admin_menu_items SET url = ? WHERE url = ?")->execute([$newUrl, $oldUrl]);
                echo "  ✓ Fixed URL: $oldUrl → $newUrl\n";
            } else {
                // New URL already exists, remove old
                $pdo->prepare("DELETE FROM admin_menu_items WHERE url = ?")->execute([$oldUrl]);
                echo "  ✓ Removed duplicate: $oldUrl (new URL already exists)\n";
            }
        }
    }
    echo "\n";

    // -------------------------------------------------------
    // STEP 5: Add missing menu items with correct URLs
    // -------------------------------------------------------
    echo "STEP 5: Adding missing menu items...\n";
    
    $newItems = [
        ['Live Chat', 'fas fa-comments', '/admin/live-chat', 'crm', 50, 'crm.view'],
        ['KYC Verification', 'fas fa-id-card', '/admin/kyc', 'crm', 51, 'crm.view'],
        ['Departments', 'fas fa-building', '/admin/hrm/departments', 'hrm', 9, 'hrm.view'],
        ['Designations', 'fas fa-user-tag', '/admin/hrm/designations', 'hrm', 10, 'hrm.view'],
        ['Company Loans', 'fas fa-hand-holding-usd', '/admin/company-loans', 'finance', 22, 'financial.view'],
        ['Leave Management', 'fas fa-calendar-times', '/admin/backoffice/leaves', 'hrm', 11, 'hrm.view'],
        ['Compliance Scorecard', 'fas fa-shield-check', '/admin/compliance-scorecard', 'system', 6, 'system.settings'],
        ['Notification Hub', 'fas fa-bell', '/admin/notification-dashboard', 'crm', 52, 'crm.view'],
    ];
    
    $addedCount = 0;
    $addStmt = $pdo->prepare("
        INSERT INTO admin_menu_items (name, icon, url, section, order_index, permission_key, is_active, created_at)
        SELECT ?, ?, ?, ?, ?, ?, 1, NOW()
        WHERE NOT EXISTS (SELECT 1 FROM admin_menu_items WHERE url = ?)
    ");
    
    foreach ($newItems as [$name, $icon, $url, $section, $order, $permKey]) {
        $addStmt->execute([$name, $icon, $url, $section, $order, $permKey, $url]);
        if ($addStmt->rowCount() > 0) {
            echo "  ✓ Added: $name → $url ($section)\n";
            $addedCount++;
        } else {
            echo "  - Skipped (exists): $url\n";
        }
    }
    echo "  → Added $addedCount items\n\n";

    // -------------------------------------------------------
    // STEP 6: Rename confusing menu items
    // -------------------------------------------------------
    echo "STEP 6: Fixing naming inconsistencies...\n";
    
    $renames = [
        ['/admin/campaigns', 'Campaigns'],
        ['/admin/network/tree', 'Network Tree'],
        ['/admin/godmode', 'SuperAdmin Console'],
        ['/admin/efiling', 'Tax E-Filing'],
        ['/admin/mlm/clawbacks', 'Commission Clawbacks'],
        ['/admin/pdfs', 'Document Generator'],
        ['/admin/features/registrations', 'User Registrations'],
        ['/admin/analytics', 'Analytics Dashboard'],
        ['/admin/erp', 'ERP Overview'],
        ['/admin/lead-kanban', 'Lead Kanban Board'],
        ['/admin/finance/collections', 'Cash Collections'],
    ];
    
    foreach ($renames as [$url, $newName]) {
        $r = $pdo->prepare("UPDATE admin_menu_items SET name = ? WHERE url = ?");
        $r->execute([$newName, $url]);
        if ($r->rowCount() > 0) {
            echo "  ✓ Renamed: $url → \"$newName\"\n";
        }
    }
    echo "\n";

    // -------------------------------------------------------
    // STEP 7: Rename 'technology' section to 'ai_tech'
    // -------------------------------------------------------
    echo "STEP 7: Renaming 'technology' section to 'ai_tech'...\n";
    $r = $pdo->exec("UPDATE admin_menu_items SET section = 'ai_tech' WHERE section = 'technology'");
    echo "  ✓ Renamed $r items from 'technology' to 'ai_tech'\n\n";

    // -------------------------------------------------------
    // STEP 8: Fix referral/lead segments in marketing (CRM items from marketing)
    // -------------------------------------------------------
    echo "STEP 8: Moving referral leaderboard/tiers to crm section...\n";
    $referralUrls = [
        '/admin/referrals/leaderboard',
        '/admin/referrals/share-analytics',
        '/admin/referrals/tiers',
    ];
    foreach ($referralUrls as $url) {
        $r = $pdo->prepare("UPDATE admin_menu_items SET section = 'crm' WHERE url = ?");
        $r->execute([$url]);
        if ($r->rowCount() > 0) {
            echo "  ✓ Moved to crm: $url\n";
        }
    }
    echo "\n";

    // -------------------------------------------------------
    // VERIFY: Show final state
    // -------------------------------------------------------
    echo "=== VERIFICATION ===\n\n";
    
    // Check remaining duplicates
    $remainDupes = $pdo->query("
        SELECT url, COUNT(*) as cnt
        FROM admin_menu_items WHERE is_active = 1
        GROUP BY url HAVING cnt > 1
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($remainDupes)) {
        echo "✅ ZERO duplicate URLs remaining\n\n";
    } else {
        echo "⚠️  Still has duplicates:\n";
        foreach ($remainDupes as $d) {
            echo "   {$d['url']} (count={$d['cnt']})\n";
        }
        echo "\n";
    }
    
    // Section summary
    $sections = $pdo->query("
        SELECT section, COUNT(*) as items 
        FROM admin_menu_items WHERE is_active = 1 
        GROUP BY section ORDER BY section
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "SECTION SUMMARY (" . count($sections) . " sections):\n";
    $total = 0;
    foreach ($sections as $s) {
        echo "  " . str_pad($s['section'], 15) . " → " . $s['items'] . " items\n";
        $total += $s['items'];
    }
    echo "  " . str_pad('TOTAL', 15) . " → $total items\n\n";
    
    echo "✅ Admin menu structure fix COMPLETE!\n";
    echo "→ Next: Reload admin panel at http://localhost/apsdreamhome/admin/dashboard\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
