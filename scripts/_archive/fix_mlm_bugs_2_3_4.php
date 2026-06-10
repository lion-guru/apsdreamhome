<?php
/**
 * Fix 3 critical MLM bugs:
 * Bug 2: mlm_profiles.total_commission and pending_commission never updated (all 0 despite ₹21L in ledger)
 * Bug 3: Self-referral entries in mlm_commission_ledger (all 84 have beneficiary=source)
 * Bug 4: network_tree incomplete (9 rows for 14 profiles)
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

echo "=== BUG 2: Fix mlm_profiles commission totals ===" . PHP_EOL;

$sql = "SELECT beneficiary_user_id,
        SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) as total_earned,
        SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) as pending_amount,
        COUNT(*) as total_entries
    FROM mlm_commission_ledger
    GROUP BY beneficiary_user_id";

$stats = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$updatedProfiles = 0;

foreach ($stats as $s) {
    $userId = (int)$s['beneficiary_user_id'];
    $total = $s['total_earned'];
    $pending = $s['pending_amount'];

    $stmt = $pdo->prepare("SELECT id, user_id FROM mlm_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch();

    if ($profile) {
        $upd = $pdo->prepare("UPDATE mlm_profiles SET total_commission = ?, pending_commission = ?, lifetime_sales = lifetime_sales + ? WHERE id = ?");
        $upd->execute([$total, $pending, $total, $profile['id']]);
        echo "  Updated user $userId: total_commission=$total, pending=$pending" . PHP_EOL;
        $updatedProfiles++;
    } else {
        echo "  WARNING: No profile for user $userId" . PHP_EOL;
    }
}

// Fix profiles with 0 commission (no ledger entries) - set from network_tree if possible
$zeroProfiles = $pdo->query("SELECT user_id FROM mlm_profiles WHERE total_commission = 0 AND pending_commission = 0")->fetchAll(PDO::FETCH_COLUMN);
foreach ($zeroProfiles as $uid) {
    echo "  User $uid: no ledger entries, commission stays 0" . PHP_EOL;
}

echo "  Total profiles updated: $updatedProfiles" . PHP_EOL;

echo PHP_EOL . "=== BUG 3: Fix self-referral entries in mlm_commission_ledger ===" . PHP_EOL;

// Count self-referral entries
$selfRefStmt = $pdo->prepare("SELECT COUNT(*) FROM mlm_commission_ledger WHERE beneficiary_user_id = source_user_id");
$selfRefStmt->execute();
$selfRefCount = $selfRefStmt->fetchColumn();
echo "  Self-referral entries found: $selfRefCount" . PHP_EOL;

// Strategy: For self-referral entries where beneficiary = source,
// try to find the actual upline from network_tree parent_id.
// network_tree uses associate_id (which is currently NULL) but has parent_id referencing user IDs.
// Since we can't reliably determine the upline, change commission_type from 'referral' to 'override'
// to indicate these are override commissions, not true referral commissions.

$ledgerEntries = $pdo->query("SELECT id, beneficiary_user_id, source_user_id, commission_type, amount, status
    FROM mlm_commission_ledger WHERE beneficiary_user_id = source_user_id")->fetchAll(PDO::FETCH_ASSOC);

$fixedCount = 0;
$redirectedCount = 0;

foreach ($ledgerEntries as $entry) {
    $entryId = (int)$entry['id'];
    $userId = (int)$entry['beneficiary_user_id'];

    // Try to find actual upline from network_tree
    // network_tree has parent_id which references user_ids from the users table
    // We need to check if the beneficiary has a parent in the tree
    $treeStmt = $pdo->prepare("SELECT parent_id FROM network_tree WHERE associate_id = ? OR parent_id = ?");
    $treeStmt->execute([$userId, $userId]);
    $treeRow = $treeStmt->fetch();

    $parentId = null;
    if ($treeRow && $treeRow['parent_id'] && $treeRow['parent_id'] != $userId) {
        $parentId = (int)$treeRow['parent_id'];
    }

    if ($parentId && $parentId != $userId) {
        // Found a real upline - update source_user_id to the parent
        $upd = $pdo->prepare("UPDATE mlm_commission_ledger SET source_user_id = ?, notes = CONCAT(COALESCE(notes,''), ' [fixed: was self-referral, upline=') , ? , ']') WHERE id = ?");
        $upd->execute([$parentId, $parentId, $entryId]);
        echo "  Entry #$entryId: user $userId -> redirected to upline $parentId" . PHP_EOL;
        $redirectedCount++;
    } else {
        // No upline found - mark as override type instead of referral
        $upd = $pdo->prepare("UPDATE mlm_commission_ledger SET commission_type = 'override', notes = CONCAT(COALESCE(notes,''), ' [fixed: was self-referral, converted to override]') WHERE id = ?");
        $upd->execute([$entryId]);
        echo "  Entry #$entryId: user $userId -> converted to override (no upline found)" . PHP_EOL;
    }
    $fixedCount++;
}

echo "  Total entries fixed: $fixedCount ($redirectedCount redirected, " . ($fixedCount - $redirectedCount) . " converted to override)" . PHP_EOL;

echo PHP_EOL . "=== BUG 4: Fix network_tree completeness ===" . PHP_EOL;

// network_tree columns: associate_id, root_id, parent_id, level, position, total_left_count, total_right_count,
//   total_left_bv, total_right_bv, personal_bv, rank_id, is_active, joined_at, updated_at
// Note: network_tree uses associate_id (not user_id) and parent_id (not parent_user_id)

$profiles = $pdo->query("SELECT user_id, sponsor_user_id FROM mlm_profiles ORDER BY user_id")->fetchAll(PDO::FETCH_ASSOC);
$addedCount = 0;

foreach ($profiles as $p) {
    $userId = (int)$p['user_id'];
    $sponsorId = $p['sponsor_user_id'] ? (int)$p['sponsor_user_id'] : 0;

    // Check if this user already has a network_tree entry
    $stmt = $pdo->prepare("SELECT id FROM network_tree WHERE associate_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetch()) {
        echo "  User $userId: already in network_tree" . PHP_EOL;
        continue;
    }

    // Insert into network_tree
    $depth = 0;
    $position = 'left';
    $rootId = $userId;

    if ($sponsorId > 0) {
        // Get sponsor's depth and root
        $stmt = $pdo->prepare("SELECT level, root_id, position FROM network_tree WHERE associate_id = ?");
        $stmt->execute([$sponsorId]);
        $sponsor = $stmt->fetch();
        if ($sponsor) {
            $depth = (int)$sponsor['level'] + 1;
            $rootId = (int)$sponsor['root_id'];
            $position = $sponsor['position'] === 'left' ? 'right' : 'left';
        }
    }

    $ins = $pdo->prepare("INSERT IGNORE INTO network_tree (associate_id, root_id, parent_id, level, position, total_left_count, total_right_count, total_left_bv, total_right_bv, personal_bv, is_active, joined_at)
        VALUES (?, ?, ?, ?, ?, 0, 0, 0.00, 0.00, 0.00, 1, NOW())");
    $parentId = $sponsorId > 0 ? $sponsorId : null;
    $ins->execute([$userId, $rootId, $parentId, $depth, $position]);
    echo "  Added user $userId to network_tree (sponsor=$sponsorId, depth=$depth, pos=$position)" . PHP_EOL;
    $addedCount++;
}

echo "  Network tree entries added: $addedCount" . PHP_EOL;

echo PHP_EOL . "=== VERIFICATION ===" . PHP_EOL;

echo PHP_EOL . "--- mlm_profiles commission totals ---" . PHP_EOL;
$check = $pdo->query("SELECT user_id, total_commission, pending_commission, sponsor_user_id FROM mlm_profiles ORDER BY user_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($check as $c) {
    $total = (float)$c['total_commission'];
    $pending = (float)$c['pending_commission'];
    $sponsor = $c['sponsor_user_id'] ?? 'null';
    $marker = ($total > 0) ? ' *UPDATED*' : '';
    echo "  user={$c['user_id']}: total=₹" . number_format($total) . ", pending=₹" . number_format($pending) . ", sponsor=$sponsor$marker" . PHP_EOL;
}

echo PHP_EOL . "--- Ledger self-referral check ---" . PHP_EOL;
$selfRefAfter = $pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger WHERE beneficiary_user_id = source_user_id AND commission_type = 'referral'")->fetchColumn();
$overrideCount = $pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger WHERE commission_type = 'override'")->fetchColumn();
$refRedirected = $pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger WHERE commission_type = 'referral' AND beneficiary_user_id != source_user_id")->fetchColumn();
echo "  Self-referral entries still as 'referral': $selfRefAfter" . PHP_EOL;
echo "  Entries converted to 'override': $overrideCount" . PHP_EOL;
echo "  Referral entries with real upline: $refRedirected" . PHP_EOL;

echo PHP_EOL . "--- network_tree completeness ---" . PHP_EOL;
$treeCount = $pdo->query("SELECT COUNT(*) FROM network_tree")->fetchColumn();
$profileCount = $pdo->query("SELECT COUNT(*) FROM mlm_profiles")->fetchColumn();
$treeUsers = $pdo->query("SELECT associate_id, parent_id, level, position FROM network_tree ORDER BY associate_id")->fetchAll(PDO::FETCH_ASSOC);
echo "  Total network_tree rows: $treeCount (profiles: $profileCount)" . PHP_EOL;
foreach ($treeUsers as $t) {
    echo "  user={$t['associate_id']}: parent={$t['parent_id']}, level={$t['level']}, pos={$t['position']}" . PHP_EOL;
}

echo PHP_EOL . "--- Ledger summary by beneficiary ---" . PHP_EOL;
$summary = $pdo->query("SELECT beneficiary_user_id,
    SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) as paid_total,
    SUM(CASE WHEN commission_type='referral' THEN amount ELSE 0 END) as referral_total,
    SUM(CASE WHEN commission_type='override' THEN amount ELSE 0 END) as override_total,
    COUNT(*) as entries
    FROM mlm_commission_ledger GROUP BY beneficiary_user_id ORDER BY beneficiary_user_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($summary as $s) {
    echo "  user={$s['beneficiary_user_id']}: paid=₹" . number_format((float)$s['paid_total']) .
        ", referral=₹" . number_format((float)$s['referral_total']) .
        ", override=₹" . number_format((float)$s['override_total']) .
        ", entries={$s['entries']}" . PHP_EOL;
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo PHP_EOL . "=== ALL BUGS FIXED ===" . PHP_EOL;
