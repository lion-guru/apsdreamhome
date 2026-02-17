<?php
/**
 * Legacy MLM Migration Script
 * Phase 1.2 - Migrate existing MLM data into unified schema
 * 
 * Usage:
 *   php database/migrate_legacy_mlm.php
 */

require_once __DIR__ . '/../includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

if (!$conn) {
    die("Database connection failed\n");
}

echo "Starting legacy MLM migration...\n";

function userExists(mysqli $conn, $userId): bool
{
    if (empty($userId)) {
        return false;
    }

    $stmt = $conn->prepare("SELECT 1 FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $exists = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    return $exists;
}

// 1. Migrate mlm_agents → mlm_profiles
$stmt = $conn->query("SELECT * FROM mlm_agents");
$agents = $stmt->fetch_all(MYSQLI_ASSOC);

$migrated = 0;
foreach ($agents as $agent) {
    $userId = (int) $agent['id'];
    $referralCode = $agent['referral_code'];
    $sponsorId = isset($agent['sponsor_id']) ? (int) $agent['sponsor_id'] : null;
    if ($sponsorId && !userExists($conn, $sponsorId)) {
        $sponsorId = null;
    }
    $sponsorCode = $sponsorId ? $agent['referrer_code'] : null;
    $currentLevel = $agent['current_level'] ?? 'Associate';
    $totalTeamSize = (int) ($agent['total_team_size'] ?? 0);
    $directReferrals = (int) ($agent['direct_referrals'] ?? 0);
    $totalBusiness = (float) ($agent['total_business'] ?? 0.0);
    $statusValue = $agent['status'] ?? 'active';
    $verificationStatus = $statusValue === 'active' ? 'verified' : 'pending';
    
    $sql = "
        INSERT INTO mlm_profiles (
            user_id, referral_code, sponsor_user_id, sponsor_code, user_type,
            current_level, total_team_size, direct_referrals, total_commission,
            verification_status, status
        ) VALUES (?, ?, ?, ?, 'associate', ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            referral_code = VALUES(referral_code),
            sponsor_user_id = VALUES(sponsor_user_id),
            sponsor_code = VALUES(sponsor_code),
            current_level = VALUES(current_level),
            total_team_size = VALUES(total_team_size),
            direct_referrals = VALUES(direct_referrals),
            total_commission = VALUES(total_commission),
            verification_status = VALUES(verification_status),
            status = VALUES(status)
    ";
    
    $stmtInsert = $conn->prepare($sql);
    $stmtInsert->bind_param(
        'isissiidss',
        $userId,
        $referralCode,
        $sponsorId,
        $sponsorCode,
        $currentLevel,
        $totalTeamSize,
        $directReferrals,
        $totalBusiness,
        $verificationStatus,
        $statusValue
    );
    
    if ($stmtInsert->execute()) {
        $migrated++;
    }
}

echo "✅ Migrated {$migrated} agents to mlm_profiles\n";

// 2. Migrate mlm_tree → mlm_network_tree
$stmt = $conn->query("SELECT * FROM mlm_tree");
$trees = $stmt->fetch_all(MYSQLI_ASSOC);

$migratedTree = 0;
foreach ($trees as $tree) {
    $ancestorId = (int) $tree['parent_id'];
    $descendantId = (int) $tree['user_id'];
    $level = (int) $tree['level'];
    $createdAt = $tree['join_date'] ?? date('Y-m-d');

    if (!$ancestorId || !userExists($conn, $ancestorId)) {
        continue;
    }

    if (!$descendantId || !userExists($conn, $descendantId)) {
        continue;
    }

    $sql = "
        INSERT INTO mlm_network_tree (ancestor_user_id, descendant_user_id, level, created_at)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE level = VALUES(level)
    ";
    
    $stmtInsert = $conn->prepare($sql);
    $stmtInsert->bind_param(
        'iiis',
        $ancestorId,
        $descendantId,
        $level,
        $createdAt
    );
    
    if ($stmtInsert->execute()) {
        $migratedTree++;
    }
}

echo "✅ Migrated {$migratedTree} tree relations to mlm_network_tree\n";

// 3. Migrate mlm_commissions → mlm_commission_ledger
$stmt = $conn->query("SELECT * FROM mlm_commissions");
$commissions = $stmt->fetch_all(MYSQLI_ASSOC);

$migratedCommissions = 0;
foreach ($commissions as $commission) {
    $beneficiaryId = (int) $commission['associate_id'];
    $sourceId = (int) $commission['associate_id'];
    $amount = (float) $commission['commission_amount'];
    $level = (int) $commission['level'];
    $createdAt = $commission['created_at'] ?? date('Y-m-d H:i:s');

    $sql = "
        INSERT INTO mlm_commission_ledger (
            beneficiary_user_id, source_user_id, commission_type, amount, level,
            status, created_at
        ) VALUES (?, ?, 'referral', ?, ?, 'approved', ?)
    ";
    
    $stmtInsert = $conn->prepare($sql);
    $stmtInsert->bind_param(
        'iidis',
        $beneficiaryId,
        $sourceId,
        $amount,
        $level,
        $createdAt
    );
    
    if ($stmtInsert->execute()) {
        $migratedCommissions++;
    }
}

echo "✅ Migrated {$migratedCommissions} commissions to mlm_commission_ledger\n";

// 4. Generate referral codes for existing users without MLM profile
$stmt = $conn->query("
    SELECT u.id, u.name, u.type 
    FROM users u 
    LEFT JOIN mlm_profiles mp ON u.id = mp.user_id 
    WHERE mp.user_id IS NULL
");
$users = $stmt->fetch_all(MYSQLI_ASSOC);

$generated = 0;
foreach ($users as $user) {
    $referralCode = generateReferralCode($user['name'], $user['id']);
    $userType = $user['type'] ?? 'customer';
    
    $sql = "
        INSERT INTO mlm_profiles (user_id, referral_code, user_type, verification_status, status)
        VALUES (?, ?, ?, 'verified', 'active')
    ";
    
    $stmtInsert = $conn->prepare($sql);
    $stmtInsert->bind_param("iss", $user['id'], $referralCode, $userType);
    if ($stmtInsert->execute()) {
        $generated++;
    }
}

echo "✅ Generated {$generated} new referral codes for existing users\n";

// 5. Rebuild network tree for complete ancestry
rebuildNetworkTree($conn);

echo "🎉 Migration completed successfully!\n";

// Helper function to generate unique referral codes
function generateReferralCode($name, $id) {
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
    $suffix = str_pad($id % 9999, 4, '0', STR_PAD_LEFT);
    return $prefix . $suffix;
}

// Helper function to rebuild network tree
function rebuildNetworkTree($conn) {
    echo "Rebuilding network tree...\n";
    
    // Clear existing tree
    $conn->query("TRUNCATE TABLE mlm_network_tree");
    
    // Get all profiles with sponsors
    $stmt = $conn->query("
        SELECT user_id, sponsor_user_id FROM mlm_profiles 
        WHERE sponsor_user_id IS NOT NULL
    ");
    $profiles = $stmt->fetch_all(MYSQLI_ASSOC);
    
    $inserted = 0;
    foreach ($profiles as $profile) {
        $descendant = $profile['user_id'];
        $ancestor = $profile['sponsor_user_id'];
        $level = 1;

        if (!$ancestor || !userExists($conn, $ancestor) || !userExists($conn, $descendant)) {
            continue;
        }

        // Insert direct relationship
        $sql = "
            INSERT INTO mlm_network_tree (ancestor_user_id, descendant_user_id, level, created_at)
            VALUES (?, ?, ?, NOW())
        ";
        $stmtInsert = $conn->prepare($sql);
        $stmtInsert->bind_param("iii", $ancestor, $descendant, $level);
        $stmtInsert->execute();
        $inserted++;
        
        // Build up the chain (ancestors of ancestors)
        $current = $ancestor;
        while ($current) {
            if (!userExists($conn, $current)) {
                break;
            }
            $stmt = $conn->prepare("
                SELECT sponsor_user_id FROM mlm_profiles WHERE user_id = ?
            ");
            $stmt->bind_param("i", $current);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result && $result['sponsor_user_id']) {
                $level++;
                $ancestor = $result['sponsor_user_id'];
                $stmtInsert->bind_param("iii", $ancestor, $descendant, $level);
                $stmtInsert->execute();
                $inserted++;
                $current = $ancestor;
            } else {
                break;
            }
        }
    }
    
    echo "✅ Rebuilt network tree with {$inserted} relationships\n";
}

?>