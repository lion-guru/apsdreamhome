<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Starting Associate Data Migration...\n";
echo "----------------------------------------\n";

// Encryption Key
$key = getenv('APP_KEY') ?: 'aps_dream_home_default_key_32_bytes';
if (strlen($key) < 32) $key = str_pad($key, 32, '0');

function encrypt_data($data, $key) {
    if (empty($data)) return null;
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// 1. Get all associates
$stmt = $conn->query("SELECT * FROM associates");
$associates = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($associates) . " associates to process.\n";

foreach ($associates as $assoc) {
    $userId = $assoc['user_id'];
    $associateId = $assoc['id'];
    
    echo "Processing Associate ID: $associateId (User ID: $userId)...\n";

    // 1. Sync to mlm_profiles
    // Check if profile exists
    $check = $conn->prepare("SELECT id FROM mlm_profiles WHERE user_id = ?");
    $check->execute([$userId]);
    $profile = $check->fetch(PDO::FETCH_ASSOC);

    $referralCode = $assoc['associate_code'] ?? 'AGT' . str_pad($userId, 6, '0', STR_PAD_LEFT);
    $sponsorId = $assoc['sponsor_id'] ?? null;
    
    // Get Sponsor User ID if sponsor_id is from associates table
    $sponsorUserId = null;
    $sponsorCode = null;
    if ($sponsorId) {
        $sponStmt = $conn->prepare("SELECT user_id, associate_code FROM associates WHERE id = ?");
        $sponStmt->execute([$sponsorId]);
        $sponData = $sponStmt->fetch(PDO::FETCH_ASSOC);
        if ($sponData) {
            $sponsorUserId = $sponData['user_id'];
            $sponsorCode = $sponData['associate_code'];
        }
    }

    $lifetimeSales = $assoc['total_business'] ?? 0;
    $directReferrals = 0; // Need to calculate?
    $totalTeam = 0; // Need to calculate?

    if (!$profile) {
        echo "  -> Creating new MLM Profile...\n";
        $ins = $conn->prepare("
            INSERT INTO mlm_profiles 
            (user_id, referral_code, sponsor_user_id, sponsor_code, user_type, current_level, lifetime_sales, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'associate', ?, ?, ?, NOW(), NOW())
        ");
        $ins->execute([
            $userId, 
            $referralCode, 
            $sponsorUserId, 
            $sponsorCode, 
            $assoc['current_level'] ?? 'Associate', 
            $lifetimeSales,
            $assoc['status'] ?? 'active'
        ]);
    } else {
        echo "  -> Updating existing MLM Profile...\n";
        // Update only if values are 0 or null in profile
        $upd = $conn->prepare("
            UPDATE mlm_profiles 
            SET lifetime_sales = GREATEST(lifetime_sales, ?),
                referral_code = COALESCE(referral_code, ?),
                sponsor_user_id = COALESCE(sponsor_user_id, ?),
                sponsor_code = COALESCE(sponsor_code, ?)
            WHERE id = ?
        ");
        $upd->execute([$lifetimeSales, $referralCode, $sponsorUserId, $sponsorCode, $profile['id']]);
    }

    // 2. Sync to banking_details
    if (!empty($assoc['account_number'])) {
        echo "  -> Syncing Banking Details...\n";
        $bankCheck = $conn->prepare("SELECT id FROM banking_details WHERE user_id = ?");
        $bankCheck->execute([$userId]);
        
        if ($bankCheck->rowCount() == 0) {
            $encAcc = encrypt_data($assoc['account_number'], $key);
            $insBank = $conn->prepare("
                INSERT INTO banking_details 
                (user_id, bank_name, account_holder_name, encrypted_account_number, ifsc_code, branch_name, account_type, is_primary, verification_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, 'savings', 1, 'pending', NOW(), NOW())
            ");
            $insBank->execute([
                $userId,
                $assoc['bank_name'] ?? '',
                $assoc['account_holder_name'] ?? '',
                $encAcc,
                $assoc['ifsc_code'] ?? '',
                $assoc['branch_name'] ?? ''
            ]);
        }
    }

    // 3. Sync to kyc_details
    if (!empty($assoc['pan_number'])) {
        echo "  -> Syncing KYC Details...\n";
        $kycCheck = $conn->prepare("SELECT id FROM kyc_details WHERE user_id = ?");
        $kycCheck->execute([$userId]);
        
        if ($kycCheck->rowCount() == 0) {
            $encPan = encrypt_data($assoc['pan_number'], $key);
            $insKyc = $conn->prepare("
                INSERT INTO kyc_details 
                (user_id, encrypted_pan_number, pan_status, overall_status, created_at, updated_at)
                VALUES (?, ?, 'pending', 'pending', NOW(), NOW())
            ");
            $insKyc->execute([$userId, $encPan]);
        }
    }
}

echo "Migration Completed.\n";
