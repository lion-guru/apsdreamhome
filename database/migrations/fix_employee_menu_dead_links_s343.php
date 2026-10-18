<?php
/**
 * Migration: Repoint dead /employee/* menu items to live pages (Session 343)
 *
 * FINDING
 *   351 active menu items; 15 had URLs with no registered route at all, so every
 *   employee clicking them got a 404. A candidate scan then proved 10 of the 15
 *   have a working page under a different path (all verified HTTP 200 live).
 *
 *   id 199 Reports                  -> /employee/reporting
 *   id 203 Deals Pipeline           -> /employee/sales-dashboard
 *   id 205 Employees                -> /employee/hr-dashboard
 *   id 208 Infrastructure           -> /employee/ops-dashboard
 *   id 213 Construction Dashboard   -> /employee/ca-dashboard
 *   id 214 Projects                 -> /employee/land-dashboard
 *   id 215 Quality Control          -> /employee/self-service
 *   id 217 Campaigns                -> /employee/marketing-dashboard
 *   id 219 Vendors                  -> /employee/ops-dashboard
 *   id 220 Customer Success         -> /employee/dashboard-overview
 *
 * NOT fixed here (no GET page exists — only POST action handlers):
 *   id 200 /employee/tax          -> candidates are POST-only (/employee/ca/tax-compliance)
 *   id 206 /employee/recruitment  -> POST-only (/employee/hr/application)
 *   id 210 /employee/compliance   -> POST-only (/employee/legal/compliance)
 *   id 212 /employee/surveys      -> POST-only (/employee/land/site-visit)
 *   id 221 /employee/complaints   -> POST-only (/employee/legal/dispute)
 *   These stay untouched rather than being pointed at a misleading page.
 *
 * Idempotent: re-run verifies the URL already matches, prints SKIP.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$map = [
    199 => '/employee/reporting',
    203 => '/employee/sales-dashboard',
    205 => '/employee/hr-dashboard',
    208 => '/employee/ops-dashboard',
    213 => '/employee/ca-dashboard',
    214 => '/employee/land-dashboard',
    215 => '/employee/self-service',
    217 => '/employee/marketing-dashboard',
    219 => '/employee/ops-dashboard',
    220 => '/employee/dashboard-overview',
];

$check = $pdo->prepare("SELECT url FROM admin_menu_items WHERE id = ?");
$upd   = $pdo->prepare("UPDATE admin_menu_items SET url = ? WHERE id = ?");

$changed = 0; $skipped = 0;
foreach ($map as $id => $newUrl) {
    $check->execute([$id]);
    $old = $check->fetchColumn();

    if ($old === false) {
        echo "SKIP  #{$id} — row no longer exists\n";
        continue;
    }
    if ($old === $newUrl) {
        echo "SKIP  #{$id} already -> {$newUrl}\n";
        $skipped++;
        continue;
    }
    $upd->execute([$newUrl, $id]);
    echo "FIX   #{$id}: {$old}  ->  {$newUrl}\n";
    $changed++;
}

// Sidebar cache bust so the change is visible without a hard refresh.
try {
    // \App\Core\Cache is the facade class in this repo (app/Core/Cache.php),
    // NOT \App\Core\Cache\Cache — verified against layouts/admin.php usage.
    \App\Core\Cache::delete('admin_sidebar_all');
    echo "\nsidebar cache busted (admin_sidebar_all)\n";
} catch (\Throwable $e) {
    echo "\n(cache bust skipped: " . $e->getMessage() . ")\n";
}

echo "\nDONE: {$changed} repointed, {$skipped} already correct.\n";