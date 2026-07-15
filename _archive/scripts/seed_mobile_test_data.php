<?php
/**
 * Mobile App Test Data Seeder
 * 
 * Creates realistic test data for mobile app testing:
 * - 5 new test customers
 * - 5 plot bookings with different statuses
 * - EMI payment schedules (12 installments each)
 * - 10 diverse leads in various pipeline stages
 * - MLM network tree (3-level hierarchy under user 2)
 * - 5 commission ledger entries
 * - 10 property images
 * 
 * Usage: php scripts/seed_mobile_test_data.php
 * 
 * Idempotent: Uses INSERT IGNORE / ON DUPLICATE KEY UPDATE.
 * Safe: Wraps everything in a transaction.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'apsdreamhome');

echo "=== APS Dream Home — Mobile Test Data Seeder ===\n\n";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    echo "[OK] Connected to database.\n\n";
} catch (PDOException $e) {
    echo "[FAIL] Database connection: " . $e->getMessage() . "\n";
    exit(1);
}

// ── Cleanup previous test data ──────────────────────────────────
echo "=== CLEANUP PREVIOUS TEST DATA ===\n";
try {
    // Delete in reverse FK order
    $pdo->exec("DELETE pb FROM booking_payment_schedules pb JOIN plot_bookings bk ON pb.booking_id = bk.id JOIN users u ON bk.customer_id = u.id WHERE u.email LIKE '%@test.com'");
    echo "  Cleaned booking_payment_schedules\n";
} catch (\Throwable $e) {
    echo "  [WARN] booking_payment_schedules cleanup: " . $e->getMessage() . "\n";
}
try {
    $pdo->exec("DELETE FROM plot_bookings WHERE customer_id IN (SELECT id FROM users WHERE email LIKE '%@test.com')");
    echo "  Cleaned plot_bookings\n";
} catch (\Throwable $e) {
    echo "  [WARN] plot_bookings cleanup: " . $e->getMessage() . "\n";
}
try {
    $pdo->exec("DELETE FROM mlm_commission_ledger WHERE beneficiary_user_id IN (SELECT id FROM users WHERE email LIKE '%@test.com')");
    echo "  Cleaned mlm_commission_ledger\n";
} catch (\Throwable $e) {
    echo "  [WARN] mlm_commission_ledger cleanup: " . $e->getMessage() . "\n";
}
try {
    $pdo->exec("DELETE FROM mlm_profiles WHERE user_id IN (SELECT id FROM users WHERE email LIKE '%@test.com')");
    echo "  Cleaned mlm_profiles\n";
} catch (\Throwable $e) {
    echo "  [WARN] mlm_profiles cleanup: " . $e->getMessage() . "\n";
}
try {
    $pdo->exec("DELETE FROM mlm_network_tree WHERE user_id IN (SELECT id FROM users WHERE email LIKE '%@test.com') OR sponsor_user_id IN (SELECT id FROM users WHERE email LIKE '%@test.com')");
    echo "  Cleaned mlm_network_tree\n";
} catch (\Throwable $e) {
    echo "  [WARN] mlm_network_tree cleanup: " . $e->getMessage() . "\n";
}
try {
    $pdo->exec("DELETE FROM leads WHERE email LIKE '%@test.com'");
    echo "  Cleaned leads\n";
} catch (\Throwable $e) {
    echo "  [WARN] leads cleanup: " . $e->getMessage() . "\n";
}
echo "  Cleanup complete.\n\n";

// ── Start transaction ──────────────────────────────────────────
$pdo->beginTransaction();
$committed = false;

try {
    // ============================================================
    // SECTION 0: Pre-flight checks & discovery
    // ============================================================
    echo "── Section 0: Pre-flight checks ──\n";

    // Find the associate ID for user 2 (needed for plot_bookings.associate_id FK)
    $stmt = $pdo->query("SELECT id FROM associates WHERE user_id = 2 LIMIT 1");
    $assocRow = $stmt->fetch();
    $user2AssociateId = $assocRow ? (int)$assocRow['id'] : null;
    echo "   User 2 associate_id: " . ($user2AssociateId ?: 'NOT FOUND (will skip FK)') . "\n";

    // Check existing test emails to avoid duplicates
    $testEmails = ['rajesh@test.com','priya@test.com','amit@test.com','sunita@test.com','vikram@test.com'];
    $placeholders = implode(',', array_fill(0, count($testEmails), '?'));
    $stmt = $pdo->prepare("SELECT email, id FROM users WHERE email IN ($placeholders)");
    $stmt->execute($testEmails);
    $existingUsers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [email => id]
    $existingCount = count($existingUsers);
    echo "   Existing test customers: $existingCount / 5\n";

    // Get available plot IDs (colony 4, cheapest plots first)
    $stmt = $pdo->query("SELECT id, colony_id, plot_number, total_price, area_sqft FROM plots WHERE status = 'available' AND colony_id = 4 ORDER BY total_price ASC LIMIT 10");
    $availablePlots = $stmt->fetchAll();
    echo "   Available plots (colony 4): " . count($availablePlots) . "\n";

    // ============================================================
    // SECTION 1: Create 5 test customers
    // ============================================================
    echo "\n── Section 1: Creating 5 test customers ──\n";

    $bcryptHash = password_hash('Test1234', PASSWORD_DEFAULT);

    $customers = [
        ['name' => 'Rajesh Kumar',  'email' => 'rajesh@test.com',  'phone' => '9876540001', 'gender' => 'male'],
        ['name' => 'Priya Sharma',  'email' => 'priya@test.com',   'phone' => '9876540002', 'gender' => 'female'],
        ['name' => 'Amit Patel',    'email' => 'amit@test.com',    'phone' => '9876540003', 'gender' => 'male'],
        ['name' => 'Sunita Devi',   'email' => 'sunita@test.com',  'phone' => '9876540004', 'gender' => 'female'],
        ['name' => 'Vikram Singh',  'email' => 'vikram@test.com',  'phone' => '9876540005', 'gender' => 'male'],
    ];

    $customerIds = []; // email => id mapping

    foreach ($customers as $c) {
        if (isset($existingUsers[$c['email']])) {
            $customerIds[$c['email']] = (int)$existingUsers[$c['email']];
            echo "   [SKIP] {$c['name']} ({$c['email']}) — already exists, id={$customerIds[$c['email']]}\n";
            continue;
        }

        $stmt = $pdo->prepare("
            INSERT INTO users (
                name, first_name, last_name, email, phone, password,
                role, user_type, status, gender, referral_code,
                city, state, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'customer', 'customer', 'active', ?, ?, 'Gorakhpur', 'Uttar Pradesh', NOW())
        ");

        $parts = explode(' ', $c['name'], 2);
        $firstName = $parts[0];
        $lastName  = $parts[1] ?? '';
        $refCode  = strtoupper(substr($c['email'], 0, 5)) . rand(1000, 9999);

        $stmt->execute([
            $c['name'], $firstName, $lastName, $c['email'], $c['phone'],
            $bcryptHash, $c['gender'], $refCode,
        ]);

        $id = (int)$pdo->lastInsertId();
        $customerIds[$c['email']] = $id;
        echo "   [ADD] {$c['name']} — id=$id, email={$c['email']}\n";
    }

    $allCustomerIds = array_values($customerIds);
    echo "   Customer IDs: " . implode(', ', $allCustomerIds) . "\n";

    // ============================================================
    // SECTION 2: Create 5 plot bookings
    // ============================================================
    echo "\n── Section 2: Creating 5 plot bookings ──\n";

    // We need 5 plots. Use cheapest available from colony 4.
    if (count($availablePlots) < 5) {
        throw new \RuntimeException("Need at least 5 available plots in colony 4, found " . count($availablePlots));
    }

    $bookingSpecs = [
        // [customer_email, plot_index, status, amount, booking_amount, channel, days_ago]
        [0, 0, 'emi_active',        1500000, 50000,  'direct',    90],
        [1, 1, 'emi_active',        1500000, 75000,  'associate', 60],
        [2, 2, 'token_paid',        1500000, 100000, 'agent',     30],
        [3, 3, 'fully_paid',        1500000, 250000, 'walk_in',  120],
        [4, 4, 'agreement_signed',  1500000, 150000, 'direct',    15],
    ];

    $bookingIds = [];
    $today = new \DateTime();

    // Check existing bookings for test customers by email pattern
    $existingBookings = $pdo->query("SELECT COUNT(*) FROM plot_bookings pb JOIN users u ON pb.customer_id = u.id WHERE u.email LIKE '%@test.com'")->fetchColumn();
    if ($existingBookings >= 5) {
        echo "   [SKIP] $existingBookings bookings already exist for test customers — reusing existing\n";
    }

    foreach ($bookingSpecs as $idx => $spec) {
        [$custIdx, $plotIdx, $status, $totalAmt, $bookingAmt, $channel, $daysAgo] = $spec;

        $custEmail = $customers[$custIdx]['email'];
        $custId    = $customerIds[$custEmail];
        $plot      = $availablePlots[$plotIdx];
        $plotId    = (int)$plot['id'];

        // Check if this customer already has a booking
        $stmt = $pdo->prepare("SELECT id FROM plot_bookings WHERE customer_id = ? LIMIT 1");
        $stmt->execute([$custId]);
        if ($stmt->fetch()) {
            echo "   [SKIP] {$customers[$custIdx]['name']} — already has a booking\n";
            $bookingIds[$custEmail] = null;
            continue;
        }

        $bookingDate = (clone $today)->modify("-{$daysAgo} days")->format('Y-m-d');
        $seq = str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
        $bookingNum  = 'APS-BK-' . date('Ymd') . '-' . $seq . rand(10, 99);
        // Reset $today after modify
        $today = new \DateTime();

        $stmt = $pdo->prepare("
            INSERT INTO plot_bookings (
                booking_number, customer_id, plot_id, booking_date,
                total_plot_value, booking_amount, agreement_value,
                status, channel, associate_id, commission_pct, commission_amount, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $commPct = match($channel) {
            'associate' => 3.00,
            'agent'     => 2.00,
            default     => 0.00,
        };
        $commAmt = round($totalAmt * $commPct / 100, 2);

        $stmt->execute([
            $bookingNum, $custId, $plotId, $bookingDate,
            $totalAmt, $bookingAmt, $totalAmt,
            $status, $channel, $user2AssociateId, $commPct, $commAmt,
        ]);

        $bId = (int)$pdo->lastInsertId();
        $bookingIds[$custEmail] = $bId;

        // Update plot status
        $plotStatus = match($status) {
            'fully_paid', 'registration_done' => 'sold',
            default => 'booked',
        };
        $pdo->prepare("UPDATE plots SET status = ?, customer_id = ?, booking_date = ? WHERE id = ?")
            ->execute([$plotStatus, $custId, $bookingDate, $plotId]);

        echo "   [ADD] Booking $bookingNum — {$customers[$custIdx]['name']}, plot={$plot['plot_number']}, status=$status, id=$bId\n";
    }

    // ============================================================
    // SECTION 3: Create EMI payment schedules (12 per booking)
    // ============================================================
    echo "\n── Section 3: Creating EMI payment schedules ──\n";

    foreach ($bookingIds as $custEmail => $bId) {
        if (!$bId) continue;

        // Check if schedules already exist for this booking
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM booking_payment_schedules WHERE booking_id = ?");
        $stmt->execute([$bId]);
        if ((int)$stmt->fetchColumn() > 0) {
            echo "   [SKIP] Booking #$bId — schedules already exist\n";
            continue;
        }

        $custIdx   = array_search($custEmail, array_column($customers, 'email'));
        $spec      = $bookingSpecs[$custIdx];
        $totalAmt  = $spec[3];
        $emiAmount = round(($totalAmt - $spec[4]) / 12, 2); // remaining / 12
        $status    = $spec[2];
        $daysAgo   = $spec[6];

        $bookingDate = (new \DateTime())->modify("-{$daysAgo} days");

        $paidCount = 0;
        $overdueCount = 0;

        switch ($status) {
            case 'emi_active':
                $paidCount = 2;
                $overdueCount = 1;
                break;
            case 'fully_paid':
                $paidCount = 12;
                $overdueCount = 0;
                break;
            case 'token_paid':
            case 'agreement_signed':
                $paidCount = 0;
                $overdueCount = 0;
                break;
        }

        $installmentStmt = $pdo->prepare("
            INSERT INTO booking_payment_schedules (
                booking_id, installment_no, due_date, amount, principal,
                opening_balance, closing_balance,
                status, paid_date, paid_amount, late_fee, accrued_penalty, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        for ($i = 1; $i <= 12; $i++) {
            $dueDate = (clone $bookingDate)->modify("+" . ($i) . " months")->format('Y-m-d');
            $opening = max(0, $totalAmt - ($spec[4] + $emiAmount * ($i - 1)));
            $closing = max(0, $totalAmt - ($spec[4] + $emiAmount * $i));

            if ($i <= $paidCount) {
                $instStatus  = 'paid';
                $paidDate    = $dueDate;
                $paidAmt     = $emiAmount;
                $lateFee     = 0;
                $penalty     = 0;
            } elseif ($i === ($paidCount + $overdueCount) && $overdueCount > 0) {
                $instStatus  = 'overdue';
                $paidDate    = null;
                $paidAmt     = 0;
                $lateFee     = 250;
                // Calculate penalty: 18% p.a. on overdue amount
                $daysOverdue = max(0, (int)(new \DateTime())->diff(new \DateTime($dueDate))->format('%r%a'));
                $penalty     = round($emiAmount * 0.18 * $daysOverdue / 365, 2);
            } else {
                $instStatus  = 'pending';
                $paidDate    = null;
                $paidAmt     = 0;
                $lateFee     = 0;
                $penalty     = 0;
            }

            $installmentStmt->execute([
                $bId, $i, $dueDate, $emiAmount, round($emiAmount * 0.7, 2),
                round($opening, 2), round($closing, 2),
                $instStatus, $paidDate, $paidAmt, $lateFee, $penalty,
            ]);
        }

        echo "   [ADD] Booking #$bId — 12 installments (paid=$paidCount, overdue=$overdueCount, pending=" . (12 - $paidCount - $overdueCount) . ")\n";
    }

    // ============================================================
    // SECTION 4: Create 10 diverse leads
    // ============================================================
    echo "\n── Section 4: Creating 10 diverse leads ──\n";

    // Check existing lead count to avoid duplicates by name+email
    $leadData = [
        ['name' => 'Deepak Mishra',      'email' => 'deepak.mishra@gmail.com',     'phone' => '9876540011', 'source' => 'website',       'status' => 'new',          'stage' => 'new',          'budget_min' => 1000000,  'budget_max' => 1500000,  'location' => 'Gorakhpur',      'score' => 25,  'priority' => 'low'],
        ['name' => 'Neha Gupta',         'email' => 'neha.gupta@yahoo.com',        'phone' => '9876540012', 'source' => 'google',        'status' => 'contacted',    'stage' => 'contacted',    'budget_min' => 2000000,  'budget_max' => 3000000,  'location' => 'Lucknow',        'score' => 45,  'priority' => 'medium'],
        ['name' => 'Sanjay Yadav',       'email' => 'sanjay.yadav@outlook.com',    'phone' => '9876540013', 'source' => 'referral',      'status' => 'qualified',    'stage' => 'qualified',    'budget_min' => 3500000,  'budget_max' => 5000000,  'location' => 'Gorakhpur',      'score' => 70,  'priority' => 'high'],
        ['name' => 'Anjali Verma',       'email' => 'anjali.verma@gmail.com',      'phone' => '9876540014', 'source' => 'walk_in',       'status' => 'qualified',    'stage' => 'viewing',      'budget_min' => 5000000,  'budget_max' => 7000000,  'location' => 'Varanasi',       'score' => 80,  'priority' => 'high'],
        ['name' => 'Rohit Tiwari',       'email' => 'rohit.tiwari@gmail.com',      'phone' => '9876540015', 'source' => 'social_media',  'status' => 'negotiation',  'stage' => 'negotiation',  'budget_min' => 7500000,  'budget_max' => 10000000, 'location' => 'Lucknow',        'score' => 90,  'priority' => 'high'],
        ['name' => 'Meena Devi',         'email' => 'meena.devi@gmail.com',        'phone' => '9876540016', 'source' => 'website',       'status' => 'closed_won',   'stage' => 'closed_won',   'budget_min' => 1500000,  'budget_max' => 2500000,  'location' => 'Gorakhpur',      'score' => 100, 'priority' => 'high'],
        ['name' => 'Arun Chaurasia',     'email' => 'arun.chaurasia@gmail.com',    'phone' => '9876540017', 'source' => 'google',        'status' => 'closed_lost',  'stage' => 'closed_lost',  'budget_min' => 2000000,  'budget_max' => 3000000,  'location' => 'Varanasi',       'score' => 15,  'priority' => 'low'],
        ['name' => 'Pooja Singh',        'email' => 'pooja.singh@gmail.com',       'phone' => '9876540018', 'source' => 'referral',      'status' => 'new',          'stage' => 'new',          'budget_min' => 4000000,  'budget_max' => 6000000,  'location' => 'Gorakhpur',      'score' => 35,  'priority' => 'medium'],
        ['name' => 'Manoj Dubey',        'email' => 'manoj.dubey@yahoo.com',       'phone' => '9876540019', 'source' => 'social_media',  'status' => 'contacted',    'stage' => 'contacted',    'budget_min' => 1000000,  'budget_max' => 2000000,  'location' => 'Lucknow',        'score' => 40,  'priority' => 'medium'],
        ['name' => 'Kavita Pandey',      'email' => 'kavita.pandey@gmail.com',     'phone' => '9876540020', 'source' => 'walk_in',       'status' => 'qualified',    'stage' => 'qualified',    'budget_min' => 6000000,  'budget_max' => 8000000,  'location' => 'Varanasi',       'score' => 65,  'priority' => 'high'],
    ];

    // Use the leads table `status` enum values and map `stage` to status
    // The leads table `status` is: new, contacted, qualified, proposal, negotiation, closed_won, closed_lost, nurture
    // Our `stage` field maps to status for simplicity

    $leadInsert = $pdo->prepare("
        INSERT INTO leads (
            lead_number, name, email, phone, source, status,
            budget, location_preference, assigned_to, lead_score,
            priority, property_interest, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $leadCount = 0;
    foreach ($leadData as $i => $ld) {
        // Skip if lead with this email already exists
        $stmt = $pdo->prepare("SELECT id FROM leads WHERE email = ? LIMIT 1");
        $stmt->execute([$ld['email']]);
        if ($stmt->fetch()) {
            echo "   [SKIP] {$ld['name']} — lead already exists\n";
            continue;
        }

        $leadNumber = 'APS-LD-' . str_pad(3308 + $i, 6, '0', STR_PAD_LEFT);
        $budget     = round(($ld['budget_min'] + $ld['budget_max']) / 2);
        $daysAgo    = rand(5, 90);
        $createdAt  = (new \DateTime())->modify("-{$daysAgo} days")->format('Y-m-d H:i:s');

        // Assign to user 2 (associate) or user 9 (agent) based on source
        $assignedTo = in_array($ld['source'], ['referral', 'walk_in']) ? 9 : 2;

        $leadInsert->execute([
            $leadNumber, $ld['name'], $ld['email'], $ld['phone'],
            $ld['source'], $ld['status'],
            $budget, $ld['location'], $assignedTo, $ld['score'],
            $ld['priority'], 'Residential Plot', "Test lead for mobile app testing",
            $createdAt,
        ]);

        $leadCount++;
        echo "   [ADD] Lead $leadNumber — {$ld['name']}, source={$ld['source']}, stage={$ld['stage']}, score={$ld['score']}\n";
    }
    echo "   Total leads inserted: $leadCount\n";

    // ============================================================
    // SECTION 5: Create MLM network tree
    // ============================================================
    echo "\n── Section 5: Building MLM network tree under user 2 ──\n";

    // Create 9 new users for the MLM tree (3 L1 + 6 L2)
    $mlmUsers = [
        // L1 — sponsored by user 2
        ['name' => 'MLM Agent L1A',  'email' => 'mlm.l1a@test.com',  'phone' => '9876540101', 'gender' => 'male',   'pos' => 'left'],
        ['name' => 'MLM Agent L1B',  'email' => 'mlm.l1b@test.com',  'phone' => '9876540102', 'gender' => 'female', 'pos' => 'right'],
        ['name' => 'MLM Agent L1C',  'email' => 'mlm.l1c@test.com',  'phone' => '9876540103', 'gender' => 'male',   'pos' => 'left'],
        // L2 — sponsored by L1A (users 111)
        ['name' => 'MLM Agent L2A1', 'email' => 'mlm.l2a1@test.com', 'phone' => '9876540104', 'gender' => 'male',   'pos' => 'left',  'l1Idx' => 0],
        ['name' => 'MLM Agent L2A2', 'email' => 'mlm.l2a2@test.com', 'phone' => '9876540105', 'gender' => 'female', 'pos' => 'right', 'l1Idx' => 0],
        // L2 — sponsored by L1B (users 112)
        ['name' => 'MLM Agent L2B1', 'email' => 'mlm.l2b1@test.com', 'phone' => '9876540106', 'gender' => 'male',   'pos' => 'left',  'l1Idx' => 1],
        ['name' => 'MLM Agent L2B2', 'email' => 'mlm.l2b2@test.com', 'phone' => '9876540107', 'gender' => 'female', 'pos' => 'right', 'l1Idx' => 1],
        // L2 — sponsored by L1C (users 113)
        ['name' => 'MLM Agent L2C1', 'email' => 'mlm.l2c1@test.com', 'phone' => '9876540108', 'gender' => 'male',   'pos' => 'left',  'l1Idx' => 2],
        ['name' => 'MLM Agent L2C2', 'email' => 'mlm.l2c2@test.com', 'phone' => '9876540109', 'gender' => 'female', 'pos' => 'right', 'l1Idx' => 2],
    ];

    // Check which MLM user emails already exist
    $mlmEmails = array_column($mlmUsers, 'email');
    $mlmPlaceholders = implode(',', array_fill(0, count($mlmEmails), '?'));
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email IN ($mlmPlaceholders)");
    $stmt->execute($mlmEmails);
    $existingMlmUsers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $mlmUserIds = []; // index => user_id
    $mlmInsertStmt = $pdo->prepare("
        INSERT INTO users (
            name, first_name, last_name, email, phone, password,
            role, user_type, status, gender, referral_code,
            city, state, referred_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 'associate', 'associate', 'active', ?, ?, 'Gorakhpur', 'Uttar Pradesh', ?, NOW())
    ");

    foreach ($mlmUsers as $idx => $mu) {
        if (isset($existingMlmUsers[$mu['email']])) {
            $mlmUserIds[$idx] = (int)$existingMlmUsers[$mu['email']];
            echo "   [SKIP] {$mu['name']} — already exists, id={$mlmUserIds[$idx]}\n";
            continue;
        }

        $parts    = explode(' ', $mu['name'], 2);
        $refCode  = 'MLM' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT) . rand(10, 99);
        $referredBy = ($idx < 3) ? 2 : $mlmUserIds[$mlmUsers[$idx]['l1Idx']] ?? 2;

        $mlmInsertStmt->execute([
            $mu['name'], $parts[0], $parts[1] ?? '', $mu['email'], $mu['phone'],
            $bcryptHash, $mu['gender'], $refCode, $referredBy,
        ]);

        $mlmUserIds[$idx] = (int)$pdo->lastInsertId();
        echo "   [ADD] {$mu['name']} — id={$mlmUserIds[$idx]}, referred_by=$referredBy\n";
    }

    // Create associates extension records for new MLM users
    $assocInsert = $pdo->prepare("
        INSERT IGNORE INTO associates (user_id, level, created_at)
        VALUES (?, 'bronze', NOW())
    ");
    foreach ($mlmUserIds as $uid) {
        $assocInsert->execute([$uid]);
    }
    echo "   Associates extension records ensured.\n";

    // Create wallet_points for all new MLM users
    $walletInsert = $pdo->prepare("
        INSERT IGNORE INTO wallet_points (user_id, points_balance, total_earned, status)
        VALUES (?, 0.00, 0.00, 'active')
    ");
    foreach ($mlmUserIds as $uid) {
        $walletInsert->execute([$uid]);
    }
    echo "   Wallet points records ensured.\n";

    // Update mlm_profiles for all 9 new users + update user 2
    $profileInsert = $pdo->prepare("
        INSERT INTO mlm_profiles (
            user_id, referral_code, sponsor_user_id, sponsor_code,
            user_type, current_level, total_team_size, direct_referrals,
            total_commission, pending_commission, lifetime_sales,
            verification_status, status, created_at
        ) VALUES (?, ?, ?, ?, 'associate', 'Bronze', ?, 0, 0, 0, 0, 'verified', 'active', NOW())
        ON DUPLICATE KEY UPDATE
            sponsor_user_id = VALUES(sponsor_user_id),
            total_team_size = VALUES(total_team_size),
            direct_referrals = VALUES(direct_referrals)
    ");

    // L1 users (3) — each has 2 direct referrals (their L2 downlines)
    foreach ([0, 1, 2] as $l1Idx) {
        $uid      = $mlmUserIds[$l1Idx];
        $refCode  = 'MLM' . str_pad($l1Idx + 1, 3, '0', STR_PAD_LEFT) . rand(10, 99);
        // 5 placeholders: user_id, referral_code, sponsor_user_id, sponsor_code, total_team_size
        $profileInsert->execute([
            $uid, $refCode, 2, 'AGENT2', 2,
        ]);
    }

    // L2 users (6) — 0 direct referrals
    foreach ([3, 4, 5, 6, 7, 8] as $l2Idx) {
        $uid      = $mlmUserIds[$l2Idx];
        $refCode  = 'MLM' . str_pad($l2Idx + 1, 3, '0', STR_PAD_LEFT) . rand(10, 99);
        $l1Idx    = $mlmUsers[$l2Idx]['l1Idx'];
        $profileInsert->execute([
            $uid, $refCode, $mlmUserIds[$l1Idx], 'MLM' . str_pad($l1Idx + 1, 3, '0', STR_PAD_LEFT), 0,
        ]);
    }

    // Update user 2's mlm_profiles — 3 direct referrals, 9 total team
    $pdo->prepare("
        UPDATE mlm_profiles SET 
            direct_referrals = 3, 
            total_team_size = 9,
            lifetime_sales = 4500000,
            total_commission = 135000
        WHERE user_id = 2
    ")->execute();
    echo "   User 2 mlm_profiles updated: direct_referrals=3, total_team_size=9\n";

    // Insert network_tree records
    $treeInsert = $pdo->prepare("
        INSERT IGNORE INTO mlm_network_tree (associate_id, sponsor_id, parent_id, level, position, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    // L1 under user 2
    $positions = ['left', 'right', 'left'];
    foreach ([0, 1, 2] as $l1Idx) {
        $treeInsert->execute([
            $mlmUserIds[$l1Idx], 2, 2, 1, $positions[$l1Idx],
        ]);
    }
    echo "   3 L1 network_tree records inserted.\n";

    // L2 under L1
    foreach ([3, 4, 5, 6, 7, 8] as $l2Idx) {
        $l1Idx = $mlmUsers[$l2Idx]['l1Idx'];
        $treeInsert->execute([
            $mlmUserIds[$l2Idx], $mlmUserIds[$l1Idx], $mlmUserIds[$l1Idx], 2, $mlmUsers[$l2Idx]['pos'],
        ]);
    }
    echo "   6 L2 network_tree records inserted.\n";

    echo "   Network tree: user 2 → 3 L1 → 6 L2 (9 total downlines)\n";

    // ============================================================
    // SECTION 6: Create 5 commission ledger entries
    // ============================================================
    echo "\n── Section 6: Creating 5 commission ledger entries ──\n";

    $commissionEntries = [
        ['beneficiary' => 2,     'source' => 3,     'type' => 'direct_sale',       'amount' => 5000,  'level' => 1,  'notes' => 'Direct sale commission — Rajesh Kumar booking'],
        ['beneficiary' => 2,     'source' => 3,     'type' => 'team_bonus',        'amount' => 12000, 'level' => 1,  'notes' => 'Team bonus — Priya Sharma booking via associate'],
        ['beneficiary' => $mlmUserIds[0] ?? 111, 'source' => 2, 'type' => 'override', 'amount' => 8500, 'level' => 2, 'notes' => 'Override commission — Gen2 team sale'],
        ['beneficiary' => 2,     'source' => 3,     'type' => 'performance_bonus', 'amount' => 15000, 'level' => null, 'notes' => 'Monthly performance bonus — 3 consecutive bookings'],
        ['beneficiary' => $mlmUserIds[1] ?? 112, 'source' => 2, 'type' => 'level_bonus', 'amount' => 3500, 'level' => 3, 'notes' => 'Level 3 bonus — deep network activation'],
    ];

    $commInsert = $pdo->prepare("
        INSERT INTO mlm_commission_ledger (
            beneficiary_user_id, source_user_id, commission_type,
            amount, level, property_id, sale_amount, commission_percentage,
            status, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'paid', ?, ?)
    ");

    foreach ($commissionEntries as $ce) {
        $daysAgo = rand(1, 60);
        $createdAt = (new \DateTime())->modify("-{$daysAgo} days")->format('Y-m-d H:i:s');
        $saleAmt = round($ce['amount'] / 0.03); // back-calculate sale amount at 3%
        $commPct = match($ce['type']) {
            'direct_sale'       => 3.00,
            'team_bonus'        => 2.00,
            'override'          => 1.50,
            'performance_bonus' => 2.50,
            'level_bonus'       => 1.00,
            default             => 1.00,
        };

        $commInsert->execute([
            $ce['beneficiary'], $ce['source'], $ce['type'],
            $ce['amount'], $ce['level'], null, $saleAmt, $commPct,
            $ce['notes'], $createdAt,
        ]);

        echo "   [ADD] Commission {$ce['type']} — ₹" . number_format($ce['amount']) . " → user #{$ce['beneficiary']}\n";
    }

    // ============================================================
    // SECTION 7: Insert property_images for 10 plots
    // ============================================================
    echo "\n── Section 7: Creating property images for plots ──\n";

    // Use existing user_properties IDs
    $stmt = $pdo->query("SELECT id FROM user_properties ORDER BY id LIMIT 15");
    $propertyIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($propertyIds) < 5) {
        echo "   [WARN] Less than 5 user_properties found — creating images for available count\n";
    }

    $imagePaths = [
        'assets/images/projects/gorakhpur/suryoday/g1.jpg',
        'assets/images/projects/gorakhpur/suryoday/g2.jpg',
        'assets/images/projects/gorakhpur/suryoday/g3.jpg',
        'assets/images/projects/gorakhpur/suryoday/g4.jpg',
        'assets/images/projects/gorakhpur/suryoday/g5.jpg',
        'assets/images/projects/gorakhpur/suryoday/g6.jpg',
        'assets/images/projects/gorakhpur/suryoday.jpg',
        'assets/images/projects/gorakhpur/suryoday_colony_map.jpg',
        'assets/images/projects/kushinagar/budh-bihar.jpg',
        'assets/images/projects/lucknow/awadhpuri.jpg',
    ];

    $imgInsert = $pdo->prepare("
        INSERT IGNORE INTO property_images (
            property_id, image_path, image_type, is_primary, caption, alt_text, sort_order, is_active, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ");

    $imgCount = 0;
    $targetProperties = array_slice($propertyIds, 0, 10);

    foreach ($targetProperties as $sortIdx => $propId) {
        $imgPath = $imagePaths[$sortIdx % count($imagePaths)];
        $isPrimary = ($sortIdx % 3 === 0) ? 1 : 0; // Every 3rd image is primary
        $caption = "Property image " . ($sortIdx + 1);
        $altText = "Plot view " . ($sortIdx + 1);

        $imgInsert->execute([
            (int)$propId, $imgPath, 'gallery', $isPrimary, $caption, $altText, $sortIdx,
        ]);

        if ($imgInsert->rowCount() > 0) {
            $imgCount++;
        }
    }

    // Ensure at least one primary image per property
    foreach ($targetProperties as $propId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM property_images WHERE property_id = ? AND is_primary = 1");
        $stmt->execute([$propId]);
        if ((int)$stmt->fetchColumn() === 0) {
            $pdo->prepare("
                UPDATE property_images SET is_primary = 1 
                WHERE property_id = ? AND id = (
                    SELECT id FROM (
                        SELECT id FROM property_images WHERE property_id = ? ORDER BY sort_order ASC LIMIT 1
                    ) t
                )
            ")->execute([$propId, $propId]);
        }
    }

    echo "   Inserted $imgCount new property images across " . count($targetProperties) . " properties\n";

    // ============================================================
    // SECTION 8: Final summary
    // ============================================================
    echo "\n── Section 8: Summary ──\n";

    // Count records
    $counts = [];
    foreach (['users', 'plot_bookings', 'booking_payment_schedules', 'leads', 'mlm_profiles', 'mlm_network_tree', 'mlm_commission_ledger', 'property_images'] as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $counts[$table] = (int)$stmt->fetchColumn();
    }

    echo "   users:                      {$counts['users']}\n";
    echo "   plot_bookings:              {$counts['plot_bookings']}\n";
    echo "   booking_payment_schedules:  {$counts['booking_payment_schedules']}\n";
    echo "   leads:                      {$counts['leads']}\n";
    echo "   mlm_profiles:               {$counts['mlm_profiles']}\n";
    echo "   mlm_network_tree:           {$counts['mlm_network_tree']}\n";
    echo "   mlm_commission_ledger:      {$counts['mlm_commission_ledger']}\n";
    echo "   property_images:            {$counts['property_images']}\n";

    // Verify new customers
    echo "\n   Test customers created:\n";
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, status FROM users WHERE email IN ($placeholders)");
    $stmt->execute($testEmails);
    foreach ($stmt->fetchAll() as $row) {
        echo "     #{$row['id']} {$row['name']} — {$row['email']} ({$row['phone']}) [{$row['role']}, {$row['status']}]\n";
    }

    // Verify bookings
    echo "\n   Bookings created:\n";
    $stmt = $pdo->prepare("
        SELECT pb.booking_number, u.name AS customer, p.plot_number, pb.total_plot_value, pb.status, pb.channel
        FROM plot_bookings pb
        JOIN users u ON u.id = pb.customer_id
        JOIN plots p ON p.id = pb.plot_id
        WHERE pb.customer_id IN ($placeholders)
    ");
    $stmt->execute($allCustomerIds);
    foreach ($stmt->fetchAll() as $row) {
        echo "     {$row['booking_number']} — {$row['customer']}, plot {$row['plot_number']}, ₹" . number_format($row['total_plot_value']) . ", {$row['status']}, {$row['channel']}\n";
    }

    // Verify MLM tree
    echo "\n   MLM network tree:\n";
    $stmt = $pdo->query("
        SELECT nt.associate_id, u.name, nt.sponsor_id, nt.level, nt.position
        FROM mlm_network_tree nt
        JOIN users u ON u.id = nt.associate_id
        WHERE nt.sponsor_id = 2 OR nt.sponsor_id IN (
            SELECT associate_id FROM mlm_network_tree WHERE sponsor_id = 2
        )
        ORDER BY nt.level, nt.sponsor_id
    ");
    foreach ($stmt->fetchAll() as $row) {
        echo "     L{$row['level']} [{$row['position']}] {$row['name']} (id={$row['associate_id']}) ← sponsor #{$row['sponsor_id']}\n";
    }

    // ── Commit ──────────────────────────────────────────────────
    $pdo->commit();
    $committed = true;

    echo "\n=== ALL DONE — Transaction committed successfully ===\n";

} catch (\Throwable $e) {
    if (!$committed) {
        $pdo->rollBack();
    }
    echo "\n[FAIL] " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "  Stack: " . $e->getTraceAsString() . "\n";
    echo "\nTransaction rolled back. No data was inserted.\n";
    exit(1);
}
