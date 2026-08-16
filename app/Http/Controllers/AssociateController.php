<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Http\Controllers;

require_once __DIR__ . '/BaseController.php';

use App\Traits\TenantAwareTrait;

/**
 * AssociateController - Property Associate management
 */
class AssociateController extends BaseController
{
    use TenantAwareTrait;
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Associate registration page
     */
    public function register()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/associate/dashboard');
            return;
        }

        @session_start();
        $csrf_token = $_SESSION['csrf_token'] ?? '';
        $errors = [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);

        $flashError = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        if ($flashError) {
            $errors[] = $flashError;
        }

        $this->render('auth/associate_register', [
            'page_title' => 'Associate Registration - APS Dream Home',
            'page_description' => 'Register as a Property Associate',
            'csrf_token' => $csrf_token,
            'errors' => $errors,
            'old' => $old,
        ], 'layouts/base');
    }

    /**
     * Store associate registration
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/register');
            return;
        }

        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $sponsorCode = trim($_POST['sponsor_code'] ?? '');
        $errors = [];

        // Validation
        if (empty($name)) $errors[] = 'Full name is required';
        if (empty($email)) $errors[] = 'Email is required';
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = 'Valid 10-digit phone number is required';
        if (empty($password)) $errors[] = 'Password is required';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/associate/register');
            return;
        }

        if (!$this->tenantEnforce('add_user')) {
            $_SESSION['error'] = $_SESSION['error'] ?? 'User limit reached for your plan';
            $this->redirect('/associate/register');
            return;
        }

        try {
            // Check duplicate email
            [$tidSql, $tidParams] = $this->tenantWhere();
            $existing = $this->db->fetchOne("SELECT id FROM users WHERE email = ?{$tidSql}", array_merge([$email], $tidParams));
            if ($existing) {
                $_SESSION['error'] = 'This email is already registered. Please login.';
                $this->redirect('/associate/login');
                return;
            }

            // Check duplicate phone
            [$tidSql, $tidParams] = $this->tenantWhere();
            $existing = $this->db->fetchOne("SELECT id FROM users WHERE phone = ?{$tidSql}", array_merge([$phone], $tidParams));
            if ($existing) {
                $_SESSION['error'] = 'This phone number is already registered. Please login.';
                $this->redirect('/associate/login');
                return;
            }

            // Resolve referrer from sponsor code
            $referredBy = null;
            if (!empty($sponsorCode)) {
                [$tidSql, $tidParams] = $this->tenantWhere();
                $referrer = $this->db->fetchOne("SELECT id FROM users WHERE referral_code = ?{$tidSql}", array_merge([$sponsorCode], $tidParams));
                if ($referrer) {
                    $referredBy = $referrer['id'];
                }
            }

            // Generate IDs
            $prefix = 'ASS';
            $customerId = $prefix . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $referralCode = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $tidInsert = $this->tenantInsertData();
            $this->db->execute(
                "INSERT INTO users (customer_id, name, email, phone, password, referral_code, referred_by, role, status, created_at, updated_at" . ($tidInsert ? ", tenant_id" : "") . ")
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'associate', 'active', NOW(), NOW()" . ($tidInsert ? ", ?" : "") . ")",
                $tidInsert ? array_merge([$customerId, $name, $email, $phone, $passwordHash, $referralCode, $referredBy], [$tidInsert['tenant_id']]) : [$customerId, $name, $email, $phone, $passwordHash, $referralCode, $referredBy]
            );

            $newUserId = (int)$this->db->lastInsertId();

            $this->tenantTrackUsage('users');

            // Create wallet entry
            try {
                $this->db->execute(
                    "INSERT INTO wallet_points (user_id, points_balance, total_earned, total_used, referral_earnings, status, created_at, updated_at)
                     VALUES (?, 0, 0, 0, 0, 'active', NOW(), NOW())",
                    [$newUserId]
                );
            } catch (\Exception $e) { error_log('Wallet creation: ' . $e->getMessage()); }

            // Auto-login
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['role'] = 'associate';
            $_SESSION['logged_in'] = true;
            $_SESSION['success'] = 'Registration successful! Welcome to APS Dream Home.';

            $this->redirect('/associate/dashboard');
        } catch (\Exception $e) {
            error_log('Associate registration error: ' . $e->getMessage());
            $_SESSION['error'] = 'Registration failed. Please try again.';
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/associate/register');
        }
    }

    /**
     * Associate dashboard
     */
    public function dashboard()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';

        $userId = $_SESSION['user_id'] ?? 0;
        $associateName = $_SESSION['user_name'] ?? 'Associate';
        $referralCode = $_SESSION['referral_code'] ?? '';

        // Fetch real DB data
        $totalLeads = 0; $propertiesSold = 0; $totalCommission = 0; $pendingCommission = 0; $commissionThisMonth = 0;
        $networkSize = 0; $directReferrals = 0; $level2Count = 0; $level3Count = 0;
        $mlmLevel = 'Associate'; $teamSales = 0;
        $recentLeads = []; $recentCommissions = []; $activities = [];

        // Lead counts from `leads` table (CRM)
        try { $totalLeads = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM leads WHERE created_by = ? AND deleted_at IS NULL", [$userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        try { $propertiesSold = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM user_properties WHERE user_id = ? AND status = 'approved'", [$userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        // Commission stats from mlm_commission_ledger
        try { $totalCommission = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ?", [$userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        try { $pendingCommission = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status = 'pending'", [$userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        try { $commissionThisMonth = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status IN ('paid','approved') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [$userId]); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        // MLM profile data
        try {
            $profile = $this->db->fetchOne("SELECT current_level, total_team_size, direct_referrals, lifetime_sales FROM mlm_profiles WHERE user_id = ?", [$userId]);
            if ($profile) {
                $mlmLevel = $profile['current_level'] ?? 'associate';
                $networkSize = (int)($profile['total_team_size'] ?? 0);
                $directReferrals = (int)($profile['direct_referrals'] ?? 0);
                $teamSales = (float)($profile['lifetime_sales'] ?? 0);
            }
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        // Recalculate rank from 12-month ledger (same logic as GamificationService)
        // and auto-update stale mlm_profiles.current_level
        try {
            $ledgerSales12mo = (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)",
                [$userId]
            );
            $rankBenefits = $this->db->fetchAll(
                "SELECT rank_name, min_qualifying_volume FROM mlm_rank_benefits ORDER BY rank_order ASC"
            );
            $computedRank = 'associate';
            $nextRank = null;
            $nextRankVolume = 0;
            
            for ($i = 0; $i < count($rankBenefits); $i++) {
                $rb = $rankBenefits[$i];
                if ($ledgerSales12mo >= (float)$rb['min_qualifying_volume']) {
                    $computedRank = $rb['rank_name'];
                    if (isset($rankBenefits[$i + 1])) {
                        $nextRank = $rankBenefits[$i + 1]['rank_name'];
                        $nextRankVolume = (float)$rankBenefits[$i + 1]['min_qualifying_volume'];
                    } else {
                        $nextRank = 'Max Rank';
                        $nextRankVolume = $ledgerSales12mo;
                    }
                }
            }
            
            // If they haven't hit the first rank
            if ($computedRank === 'associate' && $nextRank === null && isset($rankBenefits[0])) {
                $nextRank = $rankBenefits[0]['rank_name'];
                $nextRankVolume = (float)$rankBenefits[0]['min_qualifying_volume'];
            }

            // Use ledger-based sales for rank progress (consistent with gamification widget)
            $teamSales = max($teamSales, $ledgerSales12mo);
            // Auto-update if stale
            if (strtolower($computedRank) !== strtolower($mlmLevel)) {
                $this->db->execute(
                    "UPDATE mlm_profiles SET current_level = ? WHERE user_id = ?",
                    [$computedRank, $userId]
                );
                $mlmLevel = $computedRank;
                error_log("AssociateController: Updated mlm_profiles.current_level for user_id=$userId from '{$profile['current_level']}' to '$computedRank'");
            }
        } catch (\Exception $e) { error_log('AssociateController rank recalc error: ' . $e->getMessage()); }

        // Network tree level breakdown
        try {
            $myTree = $this->db->fetchOne("SELECT id FROM network_tree WHERE associate_id = ?", [$userId]);
            if ($myTree) {
                $levelCounts = $this->db->fetchAll(
                    "SELECT level, COUNT(*) as cnt FROM network_tree WHERE associate_id IN (SELECT associate_id FROM network_tree WHERE parent_id = ?) AND level <= 3 GROUP BY level ORDER BY level",
                    [$userId]
                );
                foreach ($levelCounts as $row) {
                    if ((int)$row['level'] == 1) $directReferrals = max($directReferrals, (int)$row['cnt']);
                    elseif ((int)$row['level'] == 2) $level2Count = (int)$row['cnt'];
                    elseif ((int)$row['level'] == 3) $level3Count = (int)$row['cnt'];
                }
            }
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        // Also count direct referrals from users table
        if ($directReferrals == 0) {
            try { [$tidSql1, $tidParams1] = $this->tenantWhere(); $directReferrals = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE referred_by = ?{$tidSql1}", array_merge([$userId], $tidParams1)); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        }
        if ($networkSize == 0) {
            try { [$tidSql2, $tidParams2] = $this->tenantWhere(); $networkSize = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE referred_by = ?{$tidSql2}", array_merge([$userId], $tidParams2)); } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        }

        // Recent leads from `leads` table (CRM)
        try {
            $recentLeads = $this->db->fetchAll(
                "SELECT name, phone, property_interest as type, status, DATE(created_at) as date FROM leads WHERE created_by = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 5",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        // Recent commissions from mlm_commission_ledger
        try {
            $recentCommissions = $this->db->fetchAll(
                "SELECT id, commission_type, amount, status, notes as description, DATE(created_at) as date FROM mlm_commission_ledger WHERE beneficiary_user_id = ? ORDER BY created_at DESC LIMIT 5",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }
        // Recent activities
        try {
            $rawActivities = $this->db->fetchAll(
                "SELECT action, created_at FROM activity_logs_unified WHERE user_id = ? AND user_type = 'associate' ORDER BY created_at DESC LIMIT 5",
                [$userId]
            );
            foreach ($rawActivities as $a) {
                $activities[] = ['icon' => 'fa-clock', 'text' => $a['action'], 'time' => $this->timeAgo($a['created_at']), 'color' => 'blue'];
            }
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        // ──── Enhanced Data: Bookings, EMI, Wallet, Property Views ────
        $walletBalance = 0;
        $recentBookings = [];
        $emiSummary = ['total_emi' => 0, 'paid_emi' => 0, 'pending_emi' => 0, 'overdue_emi' => 0];
        $propertyViews = 0;
        $totalInquiries = 0;

        // Wallet balance
        try {
            $walletBalance = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(points), 0) FROM wallet_points WHERE user_id = ? AND status = 'active'", [$userId]);
        } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        // Bookings by associate's referred users
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $recentBookings = $this->db->fetchAll(
                "SELECT pb.id, pb.booking_number, pb.total_plot_value, pb.status as booking_status, pb.created_at,
                        u.name as customer_name, p.plot_number as plot_name, c.name as colony_name
                 FROM plot_bookings pb
                 LEFT JOIN users u ON u.id = pb.customer_id
                 LEFT JOIN plots p ON p.id = pb.plot_id
                 LEFT JOIN colonies c ON c.id = pb.colony_id
                 WHERE pb.customer_id IN (SELECT id FROM users WHERE referred_by = ?{$tidSql})
                 ORDER BY pb.created_at DESC LIMIT 5",
                array_merge([$userId], $tidParams)
            ) ?: [];
        } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        // EMI summary for associate's referrals
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $emiRow = $this->db->fetchOne(
                "SELECT 
                    COUNT(*) as total_emi,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_emi,
                    SUM(CASE WHEN status IN ('pending','overdue') THEN 1 ELSE 0 END) as pending_emi,
                    SUM(CASE WHEN status = 'overdue' OR (status = 'pending' AND due_date < CURDATE()) THEN 1 ELSE 0 END) as overdue_emi
                 FROM booking_payment_schedules 
                 WHERE booking_id IN (SELECT id FROM plot_bookings WHERE customer_id IN (SELECT id FROM users WHERE referred_by = ?{$tidSql}))",
                array_merge([$userId], $tidParams)
            );
            if ($emiRow) {
                $emiSummary['total_emi'] = (int)($emiRow['total_emi'] ?? 0);
                $emiSummary['paid_emi'] = (int)($emiRow['paid_emi'] ?? 0);
                $emiSummary['pending_emi'] = (int)($emiRow['pending_emi'] ?? 0);
                $emiSummary['overdue_emi'] = (int)($emiRow['overdue_emi'] ?? 0);
            }
        } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        // Property views on associate's listings
        try {
            $propertyViews = (int)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(views), 0) FROM user_properties WHERE posted_by = ?",
                [$userId]
            );
        } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        // Total inquiries on associate's properties
        try {
            $totalInquiries = (int)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(inquiries), 0) FROM user_properties WHERE posted_by = ?",
                [$userId]
            );
        } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        // Fallbacks
        if (empty($recentLeads)) {
            $recentLeads = [
                ['name' => 'Rajesh Kumar', 'phone' => '98765xxxxx', 'type' => 'Residential Plot', 'status' => 'hot', 'date' => date('Y-m-d', strtotime('-1 day'))],
                ['name' => 'Priya Sharma', 'phone' => '98765xxxxx', 'type' => 'Commercial Shop', 'status' => 'warm', 'date' => date('Y-m-d', strtotime('-3 days'))],
            ];
        }
        if (empty($recentCommissions)) {
            $recentCommissions = [
                ['property' => 'Direct Referral Bonus', 'amount' => 25000, 'status' => 'paid', 'date' => date('Y-m-d', strtotime('-5 days'))],
                ['property' => 'Level Bonus', 'amount' => 18000, 'status' => 'pending', 'date' => date('Y-m-d', strtotime('-10 days'))],
            ];
        }
        if (empty($activities)) {
            $activities = [
                ['icon' => 'fa-user-plus', 'text' => 'Welcome to associate dashboard', 'time' => 'Just now', 'color' => 'blue'],
                ['icon' => 'fa-building', 'text' => 'Share your referral code to grow your network', 'time' => '-', 'color' => 'orange'],
            ];
        }

        // Rank progress data
        $rankProgress = [
            'current_rank' => $mlmLevel,
            'current_gbv' => $teamSales,
            'next_rank' => null,
            'next_rank_gbv' => 0,
            'next_rank_legs' => 0,
            'current_legs' => $directReferrals,
            'progress_pct' => 0,
            'all_ranks' => [],
        ];
        try {
            $benefits = $this->db->fetchAll("SELECT rank_name, min_leg_count, min_qualifying_volume, direct_sale_pct, rank_order FROM mlm_rank_benefits ORDER BY rank_order ASC");
            $rankProgress['all_ranks'] = $benefits;
            $currentOrder = 0;
            foreach ($benefits as $b) {
                if (strtolower($b['rank_name']) === strtolower($mlmLevel)) {
                    $currentOrder = (int)$b['rank_order'];
                    break;
                }
            }
            foreach ($benefits as $b) {
                if ((int)$b['rank_order'] === $currentOrder + 1) {
                    $rankProgress['next_rank'] = $b['rank_name'];
                    $rankProgress['next_rank_gbv'] = (float)$b['min_qualifying_volume'];
                    $rankProgress['next_rank_legs'] = (int)$b['min_leg_count'];
                    break;
                }
            }
            if ($rankProgress['next_rank']) {
                $gbvProg = $rankProgress['next_rank_gbv'] > 0 ? min(100, ($teamSales / $rankProgress['next_rank_gbv']) * 100) : 0;
                $legsProg = $rankProgress['next_rank_legs'] > 0 ? min(100, ($directReferrals / $rankProgress['next_rank_legs']) * 100) : 0;
                $rankProgress['progress_pct'] = round(min($gbvProg, $legsProg), 1);
            }
        } catch (\Exception $e) { error_log('Rank progress fetch error: ' . $e->getMessage()); }

        $stats = [
            'total_leads' => $totalLeads,
            'active_leads' => 0,
            'properties_sold' => $propertiesSold,
            'total_commission' => $totalCommission,
            'pending_commission' => $pendingCommission,
            'commission_this_month' => $commissionThisMonth,
            'network_size' => max($networkSize, 0),
            'direct_referrals' => $directReferrals,
            'level2_count' => $level2Count,
            'level3_count' => $level3Count,
            'mlm_level' => $mlmLevel,
            'team_sales' => $teamSales,
            'referral_code' => $referralCode,
            'conversion_rate' => 0,
            'monthly_growth' => 0
        ];

        $this->render('dashboard/associate_dashboard', [
            'page_title' => 'Associate Dashboard - APS Dream Home',
            'page_description' => 'Manage your property listings and client relationships',
            'stats' => $stats,
            'recent_leads' => $recentLeads,
            'recent_commissions' => $recentCommissions,
            'activities' => $activities,
            'referral_code' => $referralCode,
            'associate_name' => $associateName,
            'rank_progress' => $rankProgress,
            'gamify' => $this->safeGamify('forAssociate', (int)$userId, (int)($_SESSION['associate_id'] ?? 0)),
            'wallet_balance' => $walletBalance,
            'recent_bookings' => $recentBookings,
            'emi_summary' => $emiSummary,
            'property_views' => $propertyViews,
            'total_inquiries' => $totalInquiries,
        ], 'layouts/associate');
    }

    private function timeAgo($datetime)
    {
        if (!$datetime) return 'ago';
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        return floor($diff / 86400) . ' days ago';
    }

    /**
     * Add property form
     */
    public function addProperty()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        // Load states for dropdown
        $states = [];
        try {
            $states = $this->db->fetchAll("SELECT id, name FROM states ORDER BY name LIMIT 50");
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        $this->render('associate/add_property', [
            'page_title' => 'Add Property - APS Dream Home',
            'page_description' => 'Add a new property listing',
            'states' => $states,
            'userId' => $userId,
            'current_page' => 'add-property'
        ], 'layouts/associate');
    }

    /**
     * Store new property from add-property form
     */
    public function storeAddProperty()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/add-property');
            return;
        }

        @session_start();
        $associateId = $_SESSION['user_id'] ?? null;
        $associateName = $_SESSION['user_name'] ?? '';

        $title = trim($_POST['title'] ?? '');
        $propertyType = trim($_POST['property_type'] ?? '');
        $listingType = trim($_POST['listing_type'] ?? 'sell');
        $price = (float)($_POST['price'] ?? 0);
        $area = (int)($_POST['area'] ?? 0);
        $stateId = (int)($_POST['state_id'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || empty($propertyType) || empty($location) || $price <= 0) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            $this->redirect('/associate/add-property');
            return;
        }

        try {
            // Handle image upload
            $imagePath = null;
            if (!empty($_FILES['property_image']['name']) && $_FILES['property_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../assets/images/properties/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = pathinfo($_FILES['property_image']['name'], PATHINFO_EXTENSION);
                $newName = 'prop_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetPath = $uploadDir . $newName;
                if (move_uploaded_file($_FILES['property_image']['tmp_name'], $targetPath)) {
                    $imagePath = 'properties/' . $newName;
                }
            }

            $db = \App\Core\Database\Database::getInstance();

            $fullAddress = trim(($address ? $address . ', ' : '') . $location);

            $stmt = $db->prepare("
                INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, image, status, created_at)
                VALUES (?, ?, 'associate', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $associateId,
                $associateId,
                $associateName,
                $_SESSION['user_phone'] ?? '',
                $_SESSION['user_email'] ?? '',
                $propertyType,
                $listingType,
                $fullAddress,
                $area,
                $price,
                $listingType === 'rent' ? 'month' : 'lakh',
                $description,
                $imagePath
            ]);

            $_SESSION['success'] = 'Property submitted successfully! It will be verified before publishing.';
        } catch (\Exception $e) {
            error_log("Add property error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to submit. Please try again.';
        }

        $this->redirect('/associate/properties');
    }

    /**
     * CRM Dashboard — Stats, pipeline summary, pending tasks
     */
    public function crmDashboard()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;
        $stats = [];
        $recentActivity = [];
        $pendingTasks = [];

        try {
            $db = \App\Core\Database\Database::getInstance();
            $whereUser = "(l.created_by = ? OR l.assigned_to = ?)";
            $params = [$userId, $userId];

            // Total leads
            $stats['total_leads'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM leads l WHERE $whereUser AND l.deleted_at IS NULL", $params);

            // By status
            $statusRows = $db->fetchAll("SELECT l.status, COUNT(*) as cnt FROM leads l WHERE $whereUser AND l.deleted_at IS NULL GROUP BY l.status", $params);
            $stats['by_status'] = [];
            foreach ($statusRows as $r) $stats['by_status'][$r['status']] = (int)$r['cnt'];

            // Hot leads (score >= 70)
            $stats['hot_leads'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM leads l WHERE $whereUser AND l.lead_score >= 70 AND l.deleted_at IS NULL", $params);

            // Converted
            $stats['converted'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM leads l WHERE $whereUser AND l.is_converted = 1 AND l.deleted_at IS NULL", $params);

            // Conversion rate
            $stats['conversion_rate'] = $stats['total_leads'] > 0 ? round(($stats['converted'] / $stats['total_leads']) * 100, 1) : 0;

            // Today's leads
            $stats['today_leads'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM leads l WHERE $whereUser AND DATE(l.created_at) = CURDATE() AND l.deleted_at IS NULL", $params);

            // Pending tasks
            $stats['pending_tasks'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM crm_tasks ct WHERE ct.assigned_to = ? AND ct.status IN ('pending','in_progress')", [$userId]);

            // Overdue tasks
            $stats['overdue_tasks'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM crm_tasks ct WHERE ct.assigned_to = ? AND ct.status IN ('pending','in_progress') AND ct.due_date < CURDATE()", [$userId]);

            // Recent activity (last 5 interactions on user's leads)
            $recentActivity = $db->fetchAll(
                "SELECT ci.*, l.name as lead_name FROM crm_interactions ci
                 JOIN leads l ON l.id = ci.lead_id
                 WHERE ci.user_id = ? ORDER BY ci.created_at DESC LIMIT 5",
                [$userId]
            ) ?: [];

            // Pending tasks list (next 5)
            $pendingTasks = $db->fetchAll(
                "SELECT ct.*, l.name as lead_name FROM crm_tasks ct
                 LEFT JOIN leads l ON l.id = ct.lead_id
                 WHERE ct.assigned_to = ? AND ct.status IN ('pending','in_progress')
                 ORDER BY ct.due_date ASC LIMIT 5",
                [$userId]
            ) ?: [];

            // Top sources
            $stats['by_source'] = $db->fetchAll(
                "SELECT l.source, COUNT(*) as cnt FROM leads l WHERE $whereUser AND l.deleted_at IS NULL GROUP BY l.source ORDER BY cnt DESC LIMIT 5",
                $params
            ) ?: [];

            // Site visit stats
            $stats['total_visits'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM site_visits WHERE assigned_to = ? OR user_id = ?", [$userId, $userId]);
            $stats['today_visits'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM site_visits WHERE (assigned_to = ? OR user_id = ?) AND visit_date = CURDATE() AND status NOT IN ('cancelled','completed')", [$userId, $userId]);
            $stats['upcoming_visits'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM site_visits WHERE (assigned_to = ? OR user_id = ?) AND visit_date > CURDATE() AND status NOT IN ('cancelled','completed')", [$userId, $userId]);

            // Upcoming site visits (next 5)
            $upcomingVisits = $db->fetchAll(
                "SELECT sv.*, l.name as lead_name FROM site_visits sv
                 LEFT JOIN leads l ON l.id = sv.lead_id
                 WHERE (sv.assigned_to = ? OR sv.user_id = ?) AND sv.visit_date >= CURDATE() AND sv.status NOT IN ('cancelled','completed')
                 ORDER BY sv.visit_date ASC, sv.visit_time ASC LIMIT 5",
                [$userId, $userId]
            ) ?: [];

        } catch (\Exception $e) {
            error_log('AssociateController::crmDashboard error: ' . $e->getMessage());
        }

        $this->render('associate/crm_dashboard', [
            'page_title' => 'CRM Dashboard - APS Dream Home',
            'page_description' => 'Your lead management dashboard',
            'current_page' => 'crm',
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'pending_tasks' => $pendingTasks,
            'upcoming_visits' => $upcomingVisits ?? [],
        ], 'layouts/associate');
    }

    /**
     * View leads
     */
    public function leads()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        // Filters
        $statusFilter = $_GET['status'] ?? '';
        $search = trim($_GET['q'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE (l.created_by = ? OR l.assigned_to = ?) AND l.deleted_at IS NULL";
        $params = [$userId, $userId];

        if ($statusFilter !== '' && in_array($statusFilter, ['new','contacted','qualified','site_visit','proposal','negotiation','closed_won','closed_lost','nurture'])) {
            $where .= " AND l.status = ?";
            $params[] = $statusFilter;
        }
        if ($search !== '') {
            $where .= " AND (l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $leads = [];
        $totalCount = 0;
        $pipelineCounts = [];

        try {
            $totalCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM leads l $where", $params);
            $leads = $this->db->fetchAll(
                "SELECT l.id, l.name, l.email, l.phone, l.property_interest, l.budget_range, l.location_preference,
                        l.status, l.priority, l.lead_score, l.source, l.notes,
                        DATE(l.created_at) as date, l.next_activity_date,
                        (SELECT COUNT(*) FROM lead_activities la WHERE la.lead_id = l.id) as activity_count
                 FROM leads l $where
                 ORDER BY l.created_at DESC LIMIT $perPage OFFSET $offset",
                $params
            );
            $cntRows = $this->db->fetchAll(
                "SELECT status, COUNT(*) as cnt FROM leads WHERE created_by = ? AND deleted_at IS NULL GROUP BY status",
                [$userId]
            );
            foreach ($cntRows as $r) $pipelineCounts[$r['status']] = (int)$r['cnt'];
        } catch (\Exception $e) { error_log('AssociateController leads exception: ' . $e->getMessage()); }

        $totalPages = max(1, ceil($totalCount / $perPage));
        $queryParams = $_GET;
        unset($queryParams['page']);
        $baseQuery = http_build_query($queryParams);

        $this->render('associate/leads', [
            'page_title' => 'My Leads - APS Dream Home',
            'page_description' => 'Manage your client leads',
            'leads' => $leads,
            'total_count' => $totalCount,
            'pipeline_counts' => $pipelineCounts,
            'current_page' => 'leads',
            'status_filter' => $statusFilter,
            'search' => $search,
            'current_page_no' => $page,
            'total_pages' => $totalPages,
            'pagination_url' => BASE_URL . '/associate/leads' . ($baseQuery ? '?' . $baseQuery . '&' : '?'),
        ], 'layouts/associate');
    }

    /**
     * View commissions
     */
    public function commissions()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        // Filters
        $statusFilter = $_GET['status'] ?? '';
        $typeFilter = $_GET['type'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE l.beneficiary_user_id = ?";
        $params = [$userId];

        if ($statusFilter !== '' && in_array($statusFilter, ['pending', 'paid', 'approved', 'cancelled'])) {
            $where .= " AND l.status = ?";
            $params[] = $statusFilter;
        }
        if ($typeFilter !== '') {
            $where .= " AND l.commission_type = ?";
            $params[] = $typeFilter;
        }
        if ($dateFrom !== '') {
            $where .= " AND DATE(l.created_at) >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where .= " AND DATE(l.created_at) <= ?";
            $params[] = $dateTo;
        }

        $commissions = [];
        $totalEarned = 0;
        $totalPending = 0;
        $totalCount = 0;

        try {
            $totalCount = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM mlm_commission_ledger l $where",
                $params
            );

            $commissions = $this->db->fetchAll(
                "SELECT l.id, l.commission_type,
                 COALESCE(p.name, CONCAT('Property #', l.property_id)) as property,
                 l.amount, l.commission_percentage as percentage, l.status, l.notes as description,
                 DATE(l.created_at) as date, l.level, l.rank_at_time, l.source_user_name
                 FROM mlm_commission_ledger l
                 LEFT JOIN user_properties p ON l.property_id = p.id
                 $where
                 ORDER BY l.created_at DESC LIMIT $perPage OFFSET $offset",
                 $params
            );

            $totalEarned = (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status IN ('paid','approved')",
                [$userId]
            );
            $totalPending = (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status = 'pending'",
                [$userId]
            );

            // Commission breakdown by type
            $breakdown = $this->db->fetchAll(
                "SELECT commission_type,
                        COUNT(*) as count,
                        COALESCE(SUM(amount), 0) as total_amount,
                        SUM(CASE WHEN status IN ('paid','approved') THEN amount ELSE 0 END) as paid_amount,
                        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount
                 FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status != 'cancelled'
                 GROUP BY commission_type ORDER BY total_amount DESC",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController commission exception: ' . $e->getMessage()); }

        $totalPages = max(1, ceil($totalCount / $perPage));

        // Preserve query string for pagination (without page param)
        $queryParams = $_GET;
        unset($queryParams['page']);
        $baseQuery = http_build_query($queryParams);
        $paginationUrl = BASE_URL . '/associate/commissions' . ($baseQuery ? '?' . $baseQuery . '&' : '?');

        $this->render('associate/commissions', [
            'page_title' => 'My Commissions - APS Dream Home',
            'page_description' => 'View your commission earnings',
            'commissions' => $commissions,
            'total_earned' => $totalEarned,
            'total_pending' => $totalPending,
            'breakdown' => $breakdown ?? [],
            'current_page' => 'commissions',
            'status_filter' => $statusFilter,
            'type_filter' => $typeFilter,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'current_page_no' => $page,
            'total_pages' => $totalPages,
            'pagination_url' => $paginationUrl,
        ], 'layouts/associate');
    }

    /**
     * View properties (My Properties - associate's own listings)
     */
    public function properties()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $properties = [];
        try {
            $properties = $this->db->fetchAll(
                "SELECT id, name as title, property_type, listing_type, price, address, 
                 status, image, DATE(created_at) as date, views, area_sqft
                 FROM user_properties WHERE user_id = ? AND posted_by_type = 'associate'
                 ORDER BY created_at DESC LIMIT 20",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        $this->render('associate/properties', [
            'page_title' => 'My Properties - APS Dream Home',
            'page_description' => 'Manage your property listings',
            'properties' => $properties,
            'current_page' => 'properties'
        ], 'layouts/associate');
    }

    /**
     * Edit property form
     */
    public function editProperty($id)
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $property = null;
        try {
            $property = $this->db->fetchOne(
                "SELECT * FROM user_properties WHERE id = ? AND user_id = ? AND posted_by_type = 'associate'",
                [$id, $userId]
            );
        } catch (\Exception $e) { error_log('Edit property error: ' . $e->getMessage()); }

        if (!$property) {
            $_SESSION['error'] = 'Property not found or access denied.';
            $this->redirect('/associate/properties');
            return;
        }

        $states = [];
        try {
            $states = $this->db->fetchAll("SELECT id, name FROM states ORDER BY name LIMIT 50");
        } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $this->render('associate/edit_property', [
            'page_title' => 'Edit Property - APS Dream Home',
            'property' => $property,
            'states' => $states,
            'current_page' => 'properties',
            'success' => $success,
            'error' => $error,
        ], 'layouts/associate');
    }

    /**
     * Update property
     */
    public function updateProperty($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/associate/properties/edit/{$id}");
            return;
        }

        @session_start();
        $userId = $_SESSION['user_id'] ?? 0;

        // Verify ownership
        $property = $this->db->fetchOne(
            "SELECT id, image FROM user_properties WHERE id = ? AND user_id = ? AND posted_by_type = 'associate'",
            [$id, $userId]
        );
        if (!$property) {
            $_SESSION['error'] = 'Property not found.';
            $this->redirect('/associate/properties');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $propertyType = trim($_POST['property_type'] ?? '');
        $listingType = trim($_POST['listing_type'] ?? 'sell');
        $price = (float)str_replace([',', ' '], '', $_POST['price'] ?? 0);
        $area = (int)str_replace([',', ' '], '', $_POST['area'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || empty($propertyType) || $price <= 0) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            $this->redirect("/associate/properties/edit/{$id}");
            return;
        }

        try {
            // Handle new image upload
            $imagePath = $property['image'];
            if (!empty($_FILES['property_image']['name']) && $_FILES['property_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../assets/images/properties/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = pathinfo($_FILES['property_image']['name'], PATHINFO_EXTENSION);
                $newName = 'prop_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetPath = $uploadDir . $newName;
                if (move_uploaded_file($_FILES['property_image']['tmp_name'], $targetPath)) {
                    $imagePath = 'properties/' . $newName;
                }
            }

            $this->db->execute(
                "UPDATE user_properties SET name = ?, property_type = ?, listing_type = ?, price = ?, 
                 area_sqft = ?, address = ?, description = ?, image = ?, updated_at = NOW()
                 WHERE id = ? AND user_id = ?",
                [$title, $propertyType, $listingType, $price, $area, $location, $description, $imagePath, $id, $userId]
            );

            $_SESSION['success'] = 'Property updated successfully!';
        } catch (\Exception $e) {
            error_log("Update property error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to update property.';
        }

        $this->redirect('/associate/properties');
    }

    /**
     * Delete property (soft delete)
     */
    public function deleteProperty($id)
    {
        $this->requireAuth();
        @session_start();
        $userId = $_SESSION['user_id'] ?? 0;

        try {
            $result = $this->db->execute(
                "UPDATE user_properties SET status = 'archived', updated_at = NOW() WHERE id = ? AND user_id = ? AND posted_by_type = 'associate'",
                [$id, $userId]
            );
            if ($result) {
                $_SESSION['success'] = 'Property archived successfully.';
            } else {
                $_SESSION['error'] = 'Property not found.';
            }
        } catch (\Exception $e) {
            error_log("Delete property error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to archive property.';
        }

        $this->redirect('/associate/properties');
    }

    /**
     * Browse all properties (stays inside associate portal)
     */
    public function browse()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';

        $page = max(1, (int)($_GET['page'] ?? 1));
        $type = $_GET['type'] ?? '';
        $listingType = $_GET['listing'] ?? '';
        $location = $_GET['location'] ?? '';
        $minPrice = (int)($_GET['min_price'] ?? 0);
        $maxPrice = (int)($_GET['max_price'] ?? 0);
        $keyword = trim($_GET['q'] ?? '');
        $sortBy = $_GET['sort'] ?? 'newest';
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE status = 'approved'";
        $params = [];

        if ($keyword !== '') {
            $where .= " AND (name LIKE ? OR address LIKE ? OR description LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        if ($type !== '') {
            $where .= " AND property_type = ?";
            $params[] = $type;
        }
        if ($listingType !== '') {
            $where .= " AND listing_type = ?";
            $params[] = $listingType;
        }
        if ($location !== '') {
            $where .= " AND address LIKE ?";
            $params[] = "%$location%";
        }
        if ($minPrice > 0) {
            $where .= " AND price >= ?";
            $params[] = $minPrice;
        }
        if ($maxPrice > 0) {
            $where .= " AND price <= ?";
            $params[] = $maxPrice;
        }

        $orderBy = match($sortBy) {
            'price_low' => 'price ASC',
            'price_high' => 'price DESC',
            'oldest' => 'created_at ASC',
            default => 'created_at DESC',
        };

        $properties = [];
        $total = 0;
        try {
            $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM user_properties $where", $params);
            $properties = $this->db->fetchAll(
                "SELECT id, name, property_type, listing_type, price, address, area_sqft,
                        bedrooms, furnished, image, views, DATE(created_at) as date
                 FROM user_properties $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset",
                $params
            );
        } catch (\Exception $e) {
            error_log('AssociateController::browse exception: ' . $e->getMessage());
        }

        $totalPages = max(1, ceil($total / $perPage));
        $queryParams = $_GET;
        unset($queryParams['page']);
        $baseQuery = http_build_query($queryParams);

        $this->render('associate/browse', [
            'page_title' => 'Browse Properties - APS Dream Home',
            'page_description' => 'Browse all available properties',
            'properties' => $properties,
            'total' => $total,
            'page' => $page,
            'total_pages' => $totalPages,
            'current_page' => 'browse',
            'current_filters' => $_GET,
            'pagination_url' => BASE_URL . '/associate/browse' . ($baseQuery ? '?' . $baseQuery . '&' : '?'),
        ], 'layouts/associate');
    }

    /**
     * View sold properties
     */
    public function sold()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $properties = [];
        try {
            $properties = $this->db->fetchAll(
                "SELECT id, name as title, property_type, price, address, DATE(created_at) as date, views
                 FROM user_properties WHERE user_id = ? AND status = 'sold' AND posted_by_type = 'associate'
                 ORDER BY created_at DESC LIMIT 20",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        $this->render('associate/sold', [
            'page_title' => 'Sold Properties - APS Dream Home',
            'page_description' => 'View your sold properties',
            'properties' => $properties,
            'current_page' => 'sold'
        ], 'layouts/associate');
    }

    /**
     * View pending deals
     */
    public function pending()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $properties = [];
        try {
            $properties = $this->db->fetchAll(
                "SELECT id, name as title, property_type, price, address, status, DATE(created_at) as date
                 FROM user_properties WHERE user_id = ? AND status = 'pending' AND posted_by_type = 'associate'
                 ORDER BY created_at DESC LIMIT 20",
                [$userId]
            );
        } catch (\Exception $e) { error_log('AssociateController exception: ' . $e->getMessage()); }

        $this->render('associate/pending', [
            'page_title' => 'Pending Deals - APS Dream Home',
            'page_description' => 'Manage your pending deals',
            'properties' => $properties,
            'current_page' => 'pending'
        ], 'layouts/associate');
    }

    /**
     * View & Update profile (GET = view, POST = update)
     */
    public function profile()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';

        $userId = $_SESSION['user_id'] ?? null;

        // Handle POST — profile update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$userId) {
                $_SESSION['error'] = 'Session expired';
                header('Location: ' . BASE_URL . '/associate/login');
                exit;
            }

            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if (empty($name)) {
                $_SESSION['error'] = 'Name is required';
                header('Location: ' . BASE_URL . '/associate/profile');
                exit;
            }

            try {
                $svc = new \App\Services\UserRegistrationService();
                $result = $svc->updateProfile($userId, [
                    'name' => $name,
                    'phone' => $phone,
                    'address' => $address,
                ]);

                if ($result['success']) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            } catch (\Exception $e) {
                error_log("Associate profile update error: " . $e->getMessage());
                $_SESSION['error'] = 'Failed to update profile';
            }

            header('Location: ' . BASE_URL . '/associate/profile');
            exit;
        }

        // GET — show profile
        $user = [];
        if ($userId) {
            try {
                $user = $this->db->fetchOne(
                    "SELECT * FROM users WHERE id = ? AND status = 'active'",
                    [$userId]
                );
            } catch (\Exception $e) {
                error_log("Error getting associate: " . $e->getMessage());
            }
        }

        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 3));
        }

        $userRole = 'associate';
        $profileUrl = BASE_URL . '/associate/profile';
        $securityUrl = null;
        $canEdit = true;

        $this->render('shared/profile', [
            'user' => $user,
            'userRole' => $userRole,
            'profileUrl' => $profileUrl,
            'securityUrl' => $securityUrl,
            'canEdit' => $canEdit,
        ]);
    }

    /**
     * Associate Settings Page
     */
    public function settings()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';

        @session_start();

        // Get associate info
        $associateId = $_SESSION['user_id'] ?? null;
        $associateName = $_SESSION['user_name'] ?? '';
        $associateEmail = $_SESSION['user_email'] ?? '';
        $associatePhone = $_SESSION['user_phone'] ?? '';

        // Get notification preferences (if table exists)
        $notifications = [
            'email_leads' => true,
            'email_commissions' => true,
            'sms_important' => false,
            'marketing_emails' => true
        ];

        try {
            $db = \App\Core\Database\Database::getInstance();
            $prefs = $db->fetchOne("SELECT * FROM user_notification_preferences WHERE user_id = ? AND user_type = 'associate' LIMIT 1", [$associateId]);
            if ($prefs) {
                $notifications = [
                    'email_leads' => $prefs['email_leads'] ?? true,
                    'email_commissions' => $prefs['email_commissions'] ?? true,
                    'sms_important' => $prefs['sms_important'] ?? false,
                    'marketing_emails' => $prefs['marketing_emails'] ?? true
                ];
            }
        } catch (\Exception $e) {
            error_log('AssociateController settings notification prefs: ' . $e->getMessage());
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $data = [
            'page_title' => 'Settings - Associate Dashboard',
            'page_description' => 'Manage your account settings and preferences',
            'associate_name' => $associateName,
            'associate_email' => $associateEmail,
            'associate_phone' => $associatePhone,
            'notifications' => $notifications,
            'success' => $success,
            'error' => $error
        ];

        $this->render('pages/associate_settings', $data, 'layouts/associate');
    }

    /**
     * List Property page for users
     */
    public function listProperty()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';

        @session_start();

        // Get associate info
        $associateId = $_SESSION['user_id'] ?? null;
        $associateName = $_SESSION['user_name'] ?? '';
        $associatePhone = $_SESSION['user_phone'] ?? '';
        $associateEmail = $_SESSION['user_email'] ?? '';

        // Load states for dropdown
        $db = \App\Core\Database\Database::getInstance();
        $states = $db->fetchAll("SELECT id, name FROM states ORDER BY name LIMIT 50");

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $data = [
            'page_title' => 'Post Property - Associate Dashboard',
            'page_description' => 'List properties as an associate',
            'associate_name' => $associateName,
            'associate_phone' => $associatePhone,
            'associate_email' => $associateEmail,
            'states' => $states,
            'success' => $success,
            'error' => $error
        ];

        $this->render('pages/associate_list_property', $data, 'layouts/associate');
    }

    /**
     * Submit property listing from Associate
     */
    public function submitProperty()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/list-property');
            return;
        }

        @session_start();

        $associateId = $_SESSION['user_id'] ?? null;
        $associateName = $_SESSION['user_name'] ?? '';
        $associatePhone = $_SESSION['user_phone'] ?? '';

        // Get form data
        $name = trim($_POST['name'] ?? $associateName);
        $phone = trim($_POST['phone'] ?? $associatePhone);
        $email = trim($_POST['email'] ?? '');
        $propertyType = trim($_POST['property_type'] ?? '');
        $listingType = trim($_POST['listing_type'] ?? 'sell');
        $price = (float)str_replace([',', ' '], '', $_POST['price'] ?? 0);
        $priceUnit = trim($_POST['price_unit'] ?? 'total');
        $location = trim($_POST['location'] ?? '');
        $stateId = (int)($_POST['state_id'] ?? 0);
        $districtId = (int)($_POST['district_id'] ?? 0);
        $cityName = trim($_POST['city_name'] ?? '');
        $area = (int)str_replace([',', ' '], '', $_POST['area'] ?? 0);
        $areaUnit = trim($_POST['area_unit'] ?? 'sqft');
        $description = trim($_POST['description'] ?? '');
        $bedrooms = (int)($_POST['bedrooms'] ?? 0);
        $bathrooms = (int)($_POST['bathrooms'] ?? 0);
        $furnishing = trim($_POST['furnishing'] ?? '');
        $facing = trim($_POST['facing'] ?? '');
        $floor = trim($_POST['floor'] ?? '');
        $ownershipType = trim($_POST['ownership_type'] ?? 'freehold');
        $possession = trim($_POST['possession'] ?? 'ready');

        if (empty($name) || empty($phone) || empty($propertyType)) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            $this->redirect('/associate/list-property');
            return;
        }

        try {
            // Handle image upload
            $imagePath = null;
            if (!empty($_FILES['property_image']['name']) && $_FILES['property_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../assets/images/properties/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $v = \UploadValidator::validate($_FILES['property_image'], ['types' => 'images', 'max_size' => 5]);
                if ($v['valid']) {
                    $safeName = \UploadValidator::safeFilename($_FILES['property_image']['name']);
                    $newName = 'prop_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($safeName, PATHINFO_EXTENSION);
                    $targetPath = $uploadDir . $newName;
                    if (move_uploaded_file($_FILES['property_image']['tmp_name'], $targetPath)) {
                        \App\Core\ImageOptimizer::optimizeStatic($targetPath);
                        $imagePath = 'properties/' . $newName;
                        // Mirror to StorageManager (S3 or local fallback).
                        try {
                            \App\Services\Storage\StorageManager::getInstance()->put(
                                'assets/images/' . $imagePath,
                                file_get_contents($targetPath),
                                [
                                    'ContentType'   => mime_content_type($targetPath) ?: 'image/jpeg',
                                    'Cache-Control' => 'public, max-age=31536000, immutable',
                                ]
                            );
                        } catch (\Throwable $e) {
                            error_log('AssociateController::listProperty storage mirror: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Save to user_properties table with associate tracking
            $db = \App\Core\Database\Database::getInstance();

            $metadata = json_encode([
                'bedrooms' => $bedrooms,
                'bathrooms' => $bathrooms,
                'furnishing' => $furnishing,
                'facing' => $facing,
                'floor' => $floor,
                'ownership_type' => $ownershipType,
                'possession' => $possession,
                'price_unit' => $priceUnit,
                'area_unit' => $areaUnit,
            ]);

            $stmt = $db->prepare("
                INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, image, status, metadata, created_at)
                VALUES (?, ?, 'associate', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
            ");
            $stmt->execute([
                $associateId,
                $associateId,
                $name,
                $phone,
                $email,
                $propertyType,
                $listingType,
                $location,
                $area,
                $price,
                $priceUnit === 'rent' ? 'month' : 'lakh',
                $description,
                $imagePath,
                $metadata
            ]);

            // Also save to inquiries for CRM tracking
            $message = "Posted by Associate: {$associateName}\n";
            $message .= "Property Type: " . ucfirst($propertyType) . "\n";
            $message .= "Listing Type: " . ucfirst($listingType) . "\n";
            $message .= "Price: " . $price . "\n";
            $message .= "Area: " . $area . " sq ft\n";
            $message .= "Location: " . $location . "\n";
            $message .= "Description: " . $description;

            try {
                $inqStmt = $db->prepare("
                    INSERT INTO inquiries (name, email, phone, message, type, status, priority, posted_by, posted_by_type, created_at) 
                    VALUES (?, ?, ?, ?, 'property_listing', 'new', 'medium', ?, 'associate', NOW())
                ");
                $inqStmt->execute([$name, $email, $phone, $message, $associateId]);
            } catch (\Exception $e2) {
                error_log("Inquiry save error: " . $e2->getMessage());
            }

            // Auto-wire to CRM lead
            try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>$message,'type'=>'property_listing','created_by'=>$associateId]); } catch (\Exception $e3) { error_log("AssociateController::" . __FUNCTION__ . " lead wiring failed: " . $e3->getMessage()); }

            $_SESSION['success'] = 'Thank you! Your property listing has been submitted. Our team will verify and publish it soon.';
        } catch (\Exception $e) {
            error_log("Associate property listing error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to submit. Please try again or contact support.';
        }

        $this->redirect('/associate/properties');
    }

    /**
     * Associate Team Management
     */
    public function team()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        @session_start();
        $associateId = $_SESSION['user_id'] ?? null;
        $associateName = $_SESSION['user_name'] ?? '';

        $db = \App\Core\Database\Database::getInstance();
        $teamMembers = [];
        $teamStats = ['total' => 0, 'active' => 0, 'total_sales' => 0, 'total_commission' => 0];
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $teamMembers = $db->fetchAll(
                "SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at,
                        COALESCE(ml.current_level, 'associate') as rank,
                        COALESCE(ml.lifetime_sales, 0) as lifetime_sales,
                        COALESCE(ml.total_team_size, 0) as team_size,
                        (SELECT COUNT(*) FROM mlm_commission_ledger WHERE beneficiary_user_id = u.id) as commission_count,
                        (SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = u.id) as total_earned
                 FROM users u
                 LEFT JOIN mlm_profiles ml ON ml.user_id = u.id
                 WHERE u.referred_by = ? AND u.role = 'associate'{$tidSql}
                 ORDER BY u.created_at DESC",
                array_merge([$associateId], $tidParams)
            );
            $teamStats['total'] = count($teamMembers);
            foreach ($teamMembers as $m) {
                if (($m['status'] ?? '') === 'active') $teamStats['active']++;
                $teamStats['total_sales'] += (float)($m['lifetime_sales'] ?? 0);
                $teamStats['total_commission'] += (float)($m['total_earned'] ?? 0);
            }
        } catch (\Exception $e) {
            error_log('AssociateController::team error: ' . $e->getMessage());
        }

        $this->render('associate/team', [
            'page_title' => 'My Team - Associate Dashboard',
            'page_description' => 'Manage your team members',
            'associate_name' => $associateName,
            'team' => $teamMembers,
            'team_count' => count($teamMembers),
            'team_stats' => $teamStats,
            'current_page' => 'team'
        ], 'layouts/associate');
    }

    /**
     * MLM Plan & Commission Structure
     */
    public function mlmPlan()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $levels = [];
        $currentPlan = null;
        $currentRank = 'Associate';
        $nextRank = null;
        $userProfile = null;
        $rankBenefits = [];
        $mlmSettings = [];
        $userCommissionTotal = 0;
        $userTeamSize = 0;

        try {
            $currentPlan = $this->db->fetchOne("SELECT * FROM mlm_commission_plans WHERE status = 'active' LIMIT 1");
        } catch (\Exception $e) { error_log('AssociateController mlmPlan: ' . $e->getMessage()); }
        try {
            $levels = $this->db->fetchAll("SELECT * FROM mlm_levels ORDER BY level_order");
        } catch (\Exception $e) { error_log('AssociateController mlmPlan: ' . $e->getMessage()); }
        try {
            $rankBenefits = $this->db->fetchAll("SELECT * FROM mlm_rank_benefits WHERE is_active = 1 ORDER BY rank_order");
        } catch (\Exception $e) { error_log('AssociateController mlmPlan: ' . $e->getMessage()); }
        try {
            $userProfile = $this->db->fetchOne("SELECT current_level, total_team_size, direct_referrals, lifetime_sales FROM mlm_profiles WHERE user_id = ?", [$userId]);
        } catch (\Exception $e) { error_log('AssociateController mlmPlan: ' . $e->getMessage()); }
        try {
            $mlmSettings = $this->db->fetchAll("SELECT setting_key, setting_value FROM mlm_settings");
            $mlmSettings = array_column($mlmSettings, 'setting_value', 'setting_key');
        } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        // Get user's actual commission data
        try {
            $userCommissionTotal = (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ?",
                [$userId]
            );
            [$tidSql, $tidParams] = $this->tenantWhere();
            $userTeamSize = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE referred_by = ?{$tidSql}",
                array_merge([$userId], $tidParams)
            );
        } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        // Recalculate rank from 12-month ledger (consistent with dashboard)
        try {
            $ledgerSales12mo = (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)",
                [$userId]
            );
            $computedRank = 'associate';
            foreach ($rankBenefits as $rb) {
                if ($ledgerSales12mo >= (float)$rb['min_qualifying_volume']) {
                    $computedRank = $rb['rank_name'];
                }
            }
            $currentRank = ucwords(str_replace('_', ' ', $computedRank));
            // Auto-update if stale
            if ($userProfile && strtolower($computedRank) !== strtolower($userProfile['current_level'] ?? '')) {
                $tid = (int)$this->tenantId();
                $this->db->execute("UPDATE mlm_profiles SET current_level = ? WHERE user_id = ? AND tenant_id = ?", [$computedRank, $userId, $tid]);
            }
        } catch (\Exception $e) { error_log('MLM Plan rank recalc: ' . $e->getMessage()); }

        if (!empty($rankBenefits)) {
            foreach ($rankBenefits as $rb) {
                if (strtolower($rb['rank_name']) === strtolower($currentRank)) {
                    $currentRank = ucwords(str_replace('_', ' ', $rb['rank_name']));
                }
            }
        }
        if (!empty($levels)) {
            $found = false;
            foreach ($levels as $l) {
                if ($found) { $nextRank = $l; break; }
                if (($l['level_name'] ?? '') === $currentRank) $found = true;
            }
        }

        $this->render('associate/mlm_plan', [
            'page_title' => 'MLM Plan & Commission Structure',
            'page_description' => 'Understand your earning potential',
            'levels' => $levels,
            'current_plan' => $currentPlan,
            'current_rank' => $currentRank,
            'next_rank' => $nextRank,
            'user_profile' => $userProfile,
            'rank_benefits' => $rankBenefits,
            'mlm_settings' => $mlmSettings,
            'user_commission_total' => $userCommissionTotal,
            'user_team_size' => $userTeamSize,
            'current_page' => 'mlm-plan'
        ], 'layouts/associate');
    }

    /**
     * Document Locker
     */
    public function documents()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $documents = [];
        $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        try {
            $locker = new \App\Services\DocumentLockerService();
            $documents = $locker->getUserDocuments($userId) ?? [];
            foreach ($documents as $doc) {
                $stats['total']++;
                $stats[$doc['status'] ?? 'pending'] = ($stats[$doc['status'] ?? 'pending'] ?? 0) + 1;
            }
        } catch (\Exception $e) {
            error_log('AssociateController documents: ' . $e->getMessage());
        }

        $this->render('associate/documents', [
            'page_title' => 'Document Locker - APS Dream Home',
            'page_description' => 'Manage your documents',
            'current_page' => 'documents',
            'documents' => $documents,
            'stats' => $stats,
        ], 'layouts/associate');
    }

    /**
     * Handle document upload for associate document locker
     */
    public function uploadDocument()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/documents');
            return;
        }

        try {
            $title = trim($_POST['title'] ?? '');
            $docType = $_POST['document_type'] ?? 'general';
            $allowedTypes = ['aadhaar', 'pan', 'agreement', 'payment_receipt', 'general', 'photo', 'bank_statement', 'salary_slip'];
            if (!in_array($docType, $allowedTypes)) $docType = 'general';

            if (empty($title)) {
                $_SESSION['error'] = 'Document title is required.';
                $this->redirect('/associate/documents');
                return;
            }

            $fileUrl = '';
            if (!empty($_FILES['document_file']['tmp_name']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['document_file'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                if (!in_array($ext, $allowedExts)) {
                    $_SESSION['error'] = 'Invalid file type. Allowed: PDF, JPG, PNG, DOC/DOCX.';
                    $this->redirect('/associate/documents');
                    return;
                }
                if ($file['size'] > 10 * 1024 * 1024) {
                    $_SESSION['error'] = 'File too large. Maximum size is 10MB.';
                    $this->redirect('/associate/documents');
                    return;
                }

                $uploadDir = 'uploads/documents/' . $userId . '/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $uploadDir . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $fileUrl = $dest;
                }
            }

            $locker = new \App\Services\DocumentLockerService();
            $result = $locker->addDocument($userId, $title, $docType, $fileUrl);

            if ($result['success'] ?? false) {
                $_SESSION['success'] = 'Document uploaded successfully.';
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Failed to save document.';
            }
        } catch (\Exception $e) {
            error_log('AssociateController uploadDocument: ' . $e->getMessage());
            $_SESSION['error'] = 'Upload failed. Please try again.';
        }

        $this->redirect('/associate/documents');
    }

    /**
     * Add lead form
     */
    public function addLead()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        
        @session_start();
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        // Load available properties for interest dropdown
        $properties = [];
        try {
            $properties = $this->db->fetchAll("SELECT id, name, property_type, colony_id FROM user_properties WHERE status = 'approved' ORDER BY name LIMIT 50");
        } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $this->render('associate/add_lead', [
            'page_title' => 'Add Lead - APS Dream Home',
            'page_description' => 'Add a new client lead',
            'current_page' => 'leads',
            'success' => $success,
            'error' => $error,
            'properties' => $properties,
        ], 'layouts/associate');
    }

    /**
     * Store new lead in `leads` table (CRM)
     */
    public function storeLead()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/leads');
            return;
        }

        @session_start();
        $userId = $_SESSION['user_id'] ?? 0;
        
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $propertyInterest = trim($_POST['property_interest'] ?? '');
        $budgetRange = trim($_POST['budget_range'] ?? '');
        $locationPref = trim($_POST['location_preference'] ?? '');
        $source = trim($_POST['source'] ?? 'associate');
        $priority = trim($_POST['priority'] ?? 'medium');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($phone)) {
            $_SESSION['error'] = 'Name and Phone Number are required.';
            $this->redirect('/associate/leads/add');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();

            // Duplicate detection: check if lead with same phone already exists
            $dupCheck = $db->prepare("SELECT id, name, created_by FROM leads WHERE phone = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1");
            $dupCheck->execute([$phone]);
            $existingLead = $dupCheck->fetch(\PDO::FETCH_ASSOC);
            if ($existingLead) {
                $creator = $existingLead['created_by'] == $userId ? 'you' : 'another user';
                $_SESSION['error'] = "A lead with phone {$phone} already exists ({$existingLead['name']}, created by {$creator}). View it instead.";
                $this->redirect('/associate/leads/' . $existingLead['id']);
                return;
            }
            $stmt = $db->prepare("
                INSERT INTO leads (name, email, phone, property_interest, budget_range, location_preference, 
                    source, status, priority, notes, created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'new', ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$name, $email, $phone, $propertyInterest, $budgetRange, $locationPref, $source, $priority, $notes, $userId]);
            
            // Log activity
            $leadId = $db->lastInsertId();
            try {
                $db->prepare("INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at) VALUES (?, 'created', 'Lead created by associate', ?, NOW())")
                    ->execute([$leadId, $userId]);
            } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

            $_SESSION['success'] = 'Lead added successfully!';
            $this->redirect('/associate/leads');
        } catch (\Exception $e) {
            error_log('Error storing lead: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to save lead. Please try again.';
            $this->redirect('/associate/leads/add');
        }
    }

    /**
     * Lead detail / CRM view
     */
    public function leadDetail($id)
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $lead = null;
        $activities = [];
        try {
            $lead = $this->db->fetchOne("SELECT * FROM leads WHERE id = ? AND (created_by = ? OR assigned_to = ?) AND deleted_at IS NULL", [$id, $userId, $userId]);
            if ($lead) {
                $activities = $this->db->fetchAll(
                    "SELECT * FROM lead_activities WHERE lead_id = ? ORDER BY created_at DESC LIMIT 20",
                    [$id]
                );
                $siteVisits = $this->getLeadSiteVisits($id);
            }
        } catch (\Exception $e) { error_log('Lead detail error: ' . $e->getMessage()); }

        if (!$lead) {
            $this->redirect('/associate/leads');
            return;
        }

        @session_start();
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        // Get colonies for site visit form
        $colonies = [];
        try {
            $colonies = $this->db->getConnection()->query("SELECT id, name FROM colonies WHERE status = 'active' ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        // ── Commission Calculator Data ──
        $commissionEstimate = [
            'rank' => 'Associate',
            'rate' => 5,
            'budget_mid' => 0,
            'estimated_commission' => 0,
            'breakdown' => [],
        ];
        try {
            // Get associate's current rank and rate from mlm_profiles and mlm_rank_benefits
            $profile = $this->db->fetchOne(
                "SELECT mp.current_level, rb.direct_sale_pct
                 FROM mlm_profiles mp
                 LEFT JOIN mlm_rank_benefits rb ON rb.rank_name = mp.current_level
                 WHERE mp.user_id = ?",
                [$userId]
            );
            if ($profile) {
                $commissionEstimate['rank'] = $profile['current_level'] ?? 'Associate';
                $commissionEstimate['rate'] = (float)($profile['direct_sale_pct'] ?? 5);
            } else {
                // Fallback: get default rates
                $defaultRank = $this->db->fetchOne(
                    "SELECT rank_name, direct_sale_pct FROM mlm_rank_benefits ORDER BY rank_order ASC LIMIT 1"
                );
                if ($defaultRank) {
                    $commissionEstimate['rank'] = $defaultRank['rank_name'];
                    $commissionEstimate['rate'] = (float)$defaultRank['direct_sale_pct'];
                }
            }

            // Parse budget_range to extract a numeric mid-point
            $budgetStr = $lead['budget_range'] ?? '';
            $budgetMid = 0;
            if (preg_match('/[\d,.]+/', $budgetStr, $m)) {
                $nums = [];
                preg_match_all('/[\d,.]+/', $budgetStr, $matches);
                foreach ($matches[0] as $n) {
                    $clean = (float)str_replace(',', '', $n);
                    if ($clean > 0) $nums[] = $clean;
                }
                if (!empty($nums)) {
                    $budgetMid = count($nums) > 1 ? (min($nums) + max($nums)) / 2 : $nums[0];
                }
            }
            // If budget_range contains lakh/crore clues, scale accordingly
            if ($budgetMid < 1000 && stripos($budgetStr, 'lakh') !== false) {
                $budgetMid *= 100000;
            } elseif ($budgetMid < 1000 && stripos($budgetStr, 'cr') !== false) {
                $budgetMid *= 10000000;
            }
            $commissionEstimate['budget_mid'] = $budgetMid;

            // Calculate estimated commission: direct_sale at associate's rate
            $directCommission = $budgetMid * ($commissionEstimate['rate'] / 100);
            $commissionEstimate['estimated_commission'] = $directCommission;

            // Build breakdown of all commission types
            $levels = $this->db->fetchAll("SELECT * FROM mlm_levels ORDER BY level_order");
            $settings = $this->db->fetchAll("SELECT setting_key, setting_value FROM mlm_settings");
            $settingsMap = [];
            foreach ($settings as $s) {
                $settingsMap[$s['setting_key']] = $s['setting_value'];
            }

            $commissionEstimate['breakdown'][] = [
                'label' => 'Direct Sale (' . $commissionEstimate['rate'] . '%)',
                'amount' => $directCommission,
                'pct' => $commissionEstimate['rate'],
            ];

            // Track A (Slab Differential) — estimate roughly 15% of direct
            if (!empty($settingsMap['track_a_percent'])) {
                $trackAPct = (float)$settingsMap['track_a_percent'];
                $commissionEstimate['breakdown'][] = [
                    'label' => 'Track A (Slab Differential ~' . $trackAPct . '%)',
                    'amount' => $budgetMid * ($trackAPct / 100),
                    'pct' => $trackAPct,
                ];
            }

            // Track B (Performance Rollup) — 3% hardcoded as per docs
            $commissionEstimate['breakdown'][] = [
                'label' => 'Track B (Performance Rollup 3%)',
                'amount' => $budgetMid * 0.03,
                'pct' => 3,
            ];

            // Track C (Milestone Escrow) — 2% hardcoded as per docs
            $commissionEstimate['breakdown'][] = [
                'label' => 'Track C (Milestone Escrow 2%)',
                'amount' => $budgetMid * 0.02,
                'pct' => 2,
            ];
        } catch (\Exception $e) {
            error_log('Commission calculator error: ' . $e->getMessage());
        }

        $this->render('associate/lead_detail', [
            'page_title' => htmlspecialchars($lead['name']) . ' - Lead Details',
            'page_description' => 'Lead CRM details',
            'lead' => $lead,
            'activities' => $activities,
            'site_visits' => $siteVisits ?? [],
            'colonies' => $colonies,
            'current_page' => 'leads',
            'success' => $success,
            'error' => $error,
            'commission_estimate' => $commissionEstimate,
        ], 'layouts/associate');
    }

    /**
     * Update lead status (AJAX or POST)
     */
    public function updateLeadStatus($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false]);
            return;
        }

        $newStatus = trim($_POST['status'] ?? '');
        $validStatuses = ['new','contacted','qualified','proposal','negotiation','closed_won','closed_lost','nurture'];
        if (!in_array($newStatus, $validStatuses)) {
            $_SESSION['error'] = 'Invalid status.';
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            // Verify ownership
            $lead = $db->fetchOne("SELECT id, status FROM leads WHERE id = ? AND (created_by = ? OR assigned_to = ?) AND deleted_at IS NULL", [$id, $userId, $userId]);
            if (!$lead) {
                $this->redirect('/associate/leads');
                return;
            }
            $oldStatus = $lead['status'];
            $db->execute("UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);
            // Log activity
            try {
                $db->prepare("INSERT INTO lead_activities (lead_id, activity_type, description, old_value, new_value, created_by, created_at) VALUES (?, 'status_change', ?, ?, ?, ?, NOW())")
                    ->execute([$id, "Status changed from $oldStatus to $newStatus", $oldStatus, $newStatus, $userId]);
            } catch (\Exception $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
            $_SESSION['success'] = 'Lead status updated to ' . ucfirst(str_replace('_', ' ', $newStatus));
        } catch (\Exception $e) {
            error_log('Update lead status error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update status.';
        }
        $this->redirect("/associate/leads/{$id}");
    }

    /**
     * Add follow-up note to a lead (and optionally schedule a follow-up task)
     */
    public function addLeadNote($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        $note = trim($_POST['note'] ?? '');
        if (empty($note)) {
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $lead = $db->fetchOne("SELECT id FROM leads WHERE id = ? AND (created_by = ? OR assigned_to = ?) AND deleted_at IS NULL", [$id, $userId, $userId]);
            if ($lead) {
                // Add note activity
                $db->prepare("INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at) VALUES (?, 'note', ?, ?, NOW())")
                    ->execute([$id, $note, $userId]);
                $db->execute("UPDATE leads SET notes = CONCAT(COALESCE(notes,''), CHAR(10), CHAR(10), '[', NOW(), '] ', ?), updated_at = NOW() WHERE id = ?", [$note, $id]);

                // Schedule follow-up task if requested
                if (!empty($_POST['schedule_followup']) && !empty($_POST['followup_date'])) {
                    $followupDate = $_POST['followup_date'];
                    $followupTime = $_POST['followup_time'] ?? null;
                    $taskType = $_POST['task_type'] ?? 'follow_up';
                    $taskPriority = $_POST['task_priority'] ?? 'medium';

                    $db->query(
                        "INSERT INTO crm_tasks (lead_id, assigned_to, created_by, task_type, title, description, priority, status, due_date, due_time, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())",
                        [
                            $id,
                            $userId,
                            $userId,
                            $taskType,
                            "Follow-up: " . mb_substr($note, 0, 80),
                            $note,
                            $taskPriority,
                            $followupDate,
                            $followupTime,
                        ]
                    );

                    // Update lead's next_activity_date
                    $tid = (int)$this->tenantId();
                    $db->query("UPDATE leads SET next_activity_date = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?", [$followupDate, $id, $tid]);

                    $_SESSION['success'] = 'Note added & follow-up scheduled for ' . date('M d, Y', strtotime($followupDate)) . '.';
                } else {
                    $_SESSION['success'] = 'Note added.';
                }

                // Update last activity
                $tid2 = (int)$this->tenantId();
                $db->query("UPDATE leads SET last_activity_date = NOW(), updated_at = NOW() WHERE id = ? AND tenant_id = ?", [$id, $tid2]);
            }
        } catch (\Exception $e) {
            error_log('Add lead note error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to add note.';
        }
        $this->redirect("/associate/leads/{$id}");
    }

    public function deleteLead($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;

        $guard = \App\Services\CRMGuard::getInstance();
        if (!$guard->isCrmEnabled() || !$guard->canDeleteLead('associate')) {
            $_SESSION['error'] = 'You do not have permission to delete leads';
            $this->redirect('/associate/leads');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $lead = $db->fetchOne("SELECT id, assigned_to, created_by FROM leads WHERE id = ? AND (created_by = ? OR assigned_to = ?) AND deleted_at IS NULL", [$id, $userId, $userId]);
            if (!$lead) {
                $_SESSION['error'] = 'Lead not found or access denied';
                $this->redirect('/associate/leads');
                return;
            }

            $db->execute("UPDATE leads SET deleted_at = NOW() WHERE id = ?", [$id]);
            $db->execute(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                 VALUES (?, 'delete', 'Lead soft-deleted by associate', ?, NOW())",
                [$id, $userId]
            );

            $_SESSION['success'] = 'Lead moved to trash';
        } catch (\Throwable $e) {
            error_log('Associate deleteLead error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to delete lead';
        }

        $this->redirect('/associate/leads');
    }

    private function safeGamify(string $method, int ...$args): array
    {
        try {
            $role = strtolower(str_replace('for', '', $method));
            $cacheKey1 = $args[0] ?? 0;
            $cacheKey2 = $args[1] ?? 0;
            return \App\Services\CacheService::getGamification(
                $role,
                (int)$cacheKey1,
                (int)$cacheKey2,
                function () use ($method, $args) {
                    $svc = new \App\Services\GamificationService();
                    return $svc->{$method}(...$args);
                }
            );
        } catch (\Throwable $e) {
            error_log('Gamification error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Follow-ups / Tasks page
     */
    public function followups()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $followups = [];
        try {
            $stmt = $pdo->prepare("
                SELECT t.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email
                FROM crm_tasks t
                LEFT JOIN leads l ON l.id = t.lead_id
                WHERE t.assigned_to = ? OR t.created_by = ?
                ORDER BY t.due_date ASC, t.priority DESC
            ");
            $stmt->execute([$userId, $userId]);
            $followups = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('Followups error: ' . $e->getMessage());
        }

        $stats = [
            'total' => count($followups),
            'pending' => 0,
            'completed' => 0,
            'overdue' => 0,
        ];
        foreach ($followups as $f) {
            if ($f['status'] === 'completed') $stats['completed']++;
            elseif (strtotime($f['due_date'] ?? '') < time()) $stats['overdue']++;
            else $stats['pending']++;
        }

        $this->render('associate/followups', [
            'page_title' => 'Follow-ups - APS Dream Home',
            'current_page' => 'followups',
            'followups' => $followups,
            'stats' => $stats,
        ]);
    }

    /**
     * Update a follow-up status
     */
    public function updateFollowup($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();
        $status = $_POST['status'] ?? 'completed';

        try {
            $stmt = $pdo->prepare("UPDATE crm_tasks SET status = ?, updated_at = NOW() WHERE id = ? AND (assigned_to = ? OR created_by = ?)");
            $stmt->execute([$status, $id, $userId, $userId]);
            $_SESSION['success'] = 'Follow-up updated successfully';
        } catch (\Throwable $e) {
            error_log('Update followup error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update follow-up';
        }

        $this->redirect('/associate/followups');
    }

    /**
     * My Schedule / Calendar page
     */
    public function schedule()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $events = [];
        try {
            // Tasks
            $stmt = $pdo->prepare("
                SELECT t.id, t.title, t.description, t.due_date as event_date, t.due_time as event_time,
                       t.priority, t.status, l.name as lead_name, 'task' as event_type
                FROM crm_tasks t
                LEFT JOIN leads l ON l.id = t.lead_id
                WHERE (t.assigned_to = ? OR t.created_by = ?)
                  AND t.due_date IS NOT NULL
                  AND t.due_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY t.due_date ASC
                LIMIT 50
            ");
            $stmt->execute([$userId, $userId]);
            $events = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // Site visits
            $stmt2 = $pdo->prepare("
                SELECT sv.id, sv.visitor_name as title, sv.notes as description,
                       sv.visit_date as event_date, sv.visit_time as event_time,
                       sv.status, l.name as lead_name, 'site_visit' as event_type
                FROM site_visits sv
                LEFT JOIN leads l ON l.id = sv.lead_id
                WHERE (sv.assigned_to = ? OR sv.user_id = ?)
                  AND sv.visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  AND sv.status NOT IN ('cancelled')
                ORDER BY sv.visit_date ASC
                LIMIT 50
            ");
            $stmt2->execute([$userId, $userId]);
            $visits = $stmt2->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $events = array_merge($events, $visits);

            // Sort by date
            usort($events, function($a, $b) {
                return strcmp($a['event_date'] ?? '', $b['event_date'] ?? '');
            });
        } catch (\Throwable $e) {
            error_log('Schedule error: ' . $e->getMessage());
        }

        $this->render('associate/schedule', [
            'page_title' => 'My Schedule - APS Dream Home',
            'current_page' => 'schedule',
            'events' => $events,
        ]);
    }

    /**
     * Referral page - share code, track referrals, and show analytics
     */
    public function referral()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $referralCode = '';
        $referralCount = 0;
        $referralEarnings = 0;
        $shareClicks = [];
        $referredUsers = [];
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $pdo->prepare("SELECT referral_code, share_clicks FROM users WHERE id = ?{$tidSql}");
            $stmt->execute(array_merge([$userId], $tidParams));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $referralCode = $row['referral_code'] ?? '';
            $shareClicks = json_decode($row['share_clicks'] ?? '{}', true) ?: [];

            $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?{$tidSql}");
            $stmt2->execute(array_merge([$referralCode], $tidParams));
            $referralCount = (int) $stmt2->fetchColumn();

            $stmt3 = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE user_id = ? AND type = 'referral_bonus'");
            $stmt3->execute([$userId]);
            $referralEarnings = (float) $stmt3->fetchColumn();

            $stmt4 = $pdo->prepare("
                SELECT cr.*, u.name as referred_name, u.email as referred_email, u.phone as referred_phone
                FROM customer_referrals cr
                LEFT JOIN users u ON cr.referred_user_id = u.id
                WHERE cr.referrer_user_id = ?
                ORDER BY cr.created_at DESC
                LIMIT 50
            ");
            $stmt4->execute([$userId]);
            $referredUsers = $stmt4->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Referral error: ' . $e->getMessage());
        }

        $tierInfo = ['tier' => 'bronze', 'label' => 'Bronze', 'color' => '#CD7F32', 'icon' => 'fas fa-medal', 'total_referrals' => 0, 'next_tier' => 'Silver', 'next_tier_min' => 5, 'progress' => 0, 'referrals_needed' => 5, 'perks' => [], 'bonus_per_referral' => 100, 'bonus_on_booking' => 500];
        try {
            $referralService = new \App\Services\ReferralService();
            $tierInfo = $referralService->getUserTier($userId);
        } catch (\Throwable $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        $this->render('associate/referral', [
            'page_title' => 'Refer & Earn - APS Dream Home',
            'current_page' => 'referral',
            'referral_code' => $referralCode,
            'referral_count' => $referralCount,
            'referral_earnings' => $referralEarnings,
            'share_clicks' => $shareClicks,
            'referred_users' => $referredUsers,
            'tier_info' => $tierInfo,
        ]);
    }

    /**
     * Compare properties side by side
     */
    public function compareProperties()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $properties = [];
        try {
            $ids = $_GET['ids'] ?? [];
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("SELECT * FROM properties WHERE id IN ($placeholders) ORDER BY id DESC LIMIT 4");
                $stmt->execute($ids);
                $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $stmt = $pdo->query("SELECT id, title, city, price, area_sqft, bedrooms, bathrooms, property_type FROM properties ORDER BY id DESC LIMIT 20");
                $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Throwable $e) {
            error_log('Compare error: ' . $e->getMessage());
        }

        $this->render('associate/compare', [
            'page_title' => 'Compare Properties - APS Dream Home',
            'current_page' => 'compare',
            'properties' => $properties,
            'selected' => $_GET['ids'] ?? [],
        ]);
    }

    /**
     * My Bookings - All bookings made by this associate
     */
    public function myBookings()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $bookings = [];
        $stats = ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'total_value' => 0];
        try {
            $stmt = $pdo->prepare("
                SELECT b.*, p.title as property_title, p.price as property_price, p.city,
                       u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                       (SELECT COALESCE(SUM(amount), 0) FROM booking_payment_receipts WHERE booking_id = b.id) as total_paid
                FROM plot_bookings b
                LEFT JOIN properties p ON p.id = b.property_id
                LEFT JOIN users u ON u.id = b.customer_id
                WHERE b.associate_id = ? OR b.created_by = ?
                ORDER BY b.created_at DESC
            ");
            $stmt->execute([$userId, $userId]);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stats['total'] = count($bookings);
            foreach ($bookings as $b) {
                $status = strtolower($b['status'] ?? '');
                if ($status === 'confirmed' || $status === 'completed') $stats['confirmed']++;
                else $stats['pending']++;
                $stats['total_value'] += (float)($b['property_price'] ?? $b['total_amount'] ?? 0);
            }
        } catch (\Throwable $e) {
            error_log('MyBookings error: ' . $e->getMessage());
        }

        $this->render('associate/my_bookings', [
            'page_title' => 'My Bookings - APS Dream Home',
            'current_page' => 'my-bookings',
            'bookings' => $bookings,
            'stats' => $stats,
        ]);
    }

    /**
     * My Customers - All customers associated with this associate
     */
    public function myCustomers()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $customers = [];
        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id, u.name, u.phone, u.email, u.address, u.created_at as registered_date,
                       COUNT(DISTINCT b.id) as booking_count,
                       COALESCE(SUM(b.total_amount), 0) as total_business,
                        (SELECT COALESCE(SUM(r.amount), 0) FROM booking_payment_receipts r 
                        INNER JOIN plot_bookings pb ON pb.id = r.booking_id WHERE pb.customer_id = u.id) as total_paid,
                       (SELECT MAX(created_at) FROM plot_bookings WHERE customer_id = u.id) as last_booking_date,
                       (SELECT role FROM users WHERE id = u.id) as user_role
                FROM users u
                INNER JOIN plot_bookings b ON b.customer_id = u.id
                WHERE b.associate_id = ? OR b.created_by = ?
                GROUP BY u.id, u.name, u.phone, u.email, u.address, u.created_at
                ORDER BY total_business DESC
            ");
            $stmt->execute([$userId, $userId]);
            $customers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Add plot details and associate status
            foreach ($customers as &$c) {
                $c['is_associate'] = (($c['user_role'] ?? '') === 'associate') ? 1 : 0;
                $c['total_paid'] = $c['total_paid'] ?? 0;
                
                // Get plot details
                $plotStmt = $pdo->prepare("
                    SELECT p.plot_number, col.name as colony_name 
                    FROM plot_bookings b 
                    INNER JOIN plots p ON p.id = b.plot_id 
                    LEFT JOIN colonies col ON col.id = p.colony_id 
                    WHERE b.customer_id = ?
                    ORDER BY b.created_at DESC LIMIT 5
                ");
                $plotStmt->execute([$c['id']]);
                $c['plots'] = $plotStmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            unset($c);
        } catch (\Throwable $e) {
            error_log('MyCustomers error: ' . $e->getMessage());
        }

        $this->render('associate/my_customers', [
            'page_title' => 'My Customers - APS Dream Home',
            'current_page' => 'my-customers',
            'customers' => $customers,
        ]);
    }

    /**
     * Customer Detail - Full customer info with bookings, payments
     */
    public function customerDetail($id)
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();
        $customerId = (int)$id;

        $customer = null;
        $bookings = [];
        $receipts = [];

        try {
            // Get customer info (must have booking with this associate)
            $stmt = $pdo->prepare("
                SELECT u.*, 
                       (SELECT role FROM users WHERE id = u.id) as user_role,
                       (SELECT referral_code FROM users WHERE id = u.id) as referral_code
                FROM users u
                WHERE u.id = ? AND EXISTS (
                    SELECT 1 FROM plot_bookings WHERE customer_id = u.id AND (associate_id = ? OR created_by = ?)
                )
            ");
            $stmt->execute([$customerId, $userId, $userId]);
            $customer = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($customer) {
                // Get bookings
                $bookStmt = $pdo->prepare("
                    SELECT b.*, p.plot_number, col.name as colony_name, p.area_sqft
                    FROM plot_bookings b
                    LEFT JOIN plots p ON p.id = b.plot_id
                    LEFT JOIN colonies col ON col.id = p.colony_id
                    WHERE b.customer_id = ? AND (b.associate_id = ? OR b.created_by = ?)
                    ORDER BY b.created_at DESC
                ");
                $bookStmt->execute([$customerId, $userId, $userId]);
                $bookings = $bookStmt->fetchAll(\PDO::FETCH_ASSOC);

                // Get receipts
                $receiptStmt = $pdo->prepare("
                    SELECT r.* FROM booking_payment_receipts r
                    INNER JOIN plot_bookings b ON b.id = r.booking_id
                    WHERE b.customer_id = ? AND (b.associate_id = ? OR b.created_by = ?)
                    ORDER BY r.receipt_date DESC
                ");
                $receiptStmt->execute([$customerId, $userId, $userId]);
                $receipts = $receiptStmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Throwable $e) {
            error_log('CustomerDetail error: ' . $e->getMessage());
        }

        if (!$customer) {
            $_SESSION['error'] = 'Customer not found or access denied';
            $this->redirect('/associate/my-customers');
            return;
        }

        $this->render('associate/customer_detail', [
            'page_title' => $customer['name'] . ' - Customer Detail',
            'current_page' => 'my-customers',
            'customer' => $customer,
            'bookings' => $bookings,
            'receipts' => $receipts,
        ]);
    }

    /**
     * EMI Tracker - Track customer EMI payments
     */
    public function emiTracker()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $emiData = [];
        $stats = ['total_pending' => 0, 'overdue' => 0, 'collected' => 0, 'total_amount' => 0];
        try {
            $stmt = $pdo->prepare("
                SELECT s.*, b.id as booking_id, p.title as property_title, p.city,
                       u.name as customer_name, u.phone as customer_phone,
                       (SELECT COALESCE(SUM(amount), 0) FROM booking_payment_receipts WHERE booking_id = b.id AND installment_id = s.id) as paid_amount
                FROM booking_payment_schedules s
                INNER JOIN plot_bookings b ON b.id = s.booking_id
                LEFT JOIN properties p ON p.id = b.property_id
                LEFT JOIN users u ON u.id = b.customer_id
                WHERE (b.associate_id = ? OR b.created_by = ?)
                  AND s.status != 'completed'
                ORDER BY s.due_date ASC
            ");
            $stmt->execute([$userId, $userId]);
            $emiData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($emiData as $emi) {
                $dueDate = strtotime($emi['due_date'] ?? '');
                if ($dueDate < time() && $emi['status'] !== 'paid') {
                    $stats['overdue']++;
                } else {
                    $stats['total_pending']++;
                }
                $stats['total_amount'] += (float)($emi['amount'] ?? 0);
                $stats['collected'] += (float)($emi['paid_amount'] ?? 0);
            }
        } catch (\Throwable $e) {
            error_log('EMITracker error: ' . $e->getMessage());
        }

        $this->render('associate/emi_tracker', [
            'page_title' => 'EMI Tracker - APS Dream Home',
            'current_page' => 'emi-tracker',
            'emiData' => $emiData,
            'stats' => $stats,
        ]);
    }

    /**
     * Payment History - All receipts and payments
     */
    public function paymentHistory()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $receipts = [];
        try {
            $stmt = $pdo->prepare("
                SELECT r.*, b.id as booking_id, p.title as property_title, p.city,
                       u.name as customer_name, u.phone as customer_phone
                FROM booking_payment_receipts r
                INNER JOIN plot_bookings b ON b.id = r.booking_id
                LEFT JOIN properties p ON p.id = b.property_id
                LEFT JOIN users u ON u.id = b.customer_id
                WHERE b.associate_id = ? OR b.created_by = ?
                ORDER BY r.receipt_date DESC, r.id DESC
            ");
            $stmt->execute([$userId, $userId]);
            $receipts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('PaymentHistory error: ' . $e->getMessage());
        }

        $this->render('associate/payment_history', [
            'page_title' => 'Payment History - APS Dream Home',
            'current_page' => 'payment-history',
            'receipts' => $receipts,
        ]);
    }

    /**
     * Booking Receipt - View/Download receipt for a specific booking
     */
    public function bookingReceipt($id)
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $booking = null;
        $receipts = [];
        try {
            $stmt = $pdo->prepare("
                SELECT b.*, p.title as property_title, p.price as property_price, p.area_sqft,
                       p.city, p.state, u.name as customer_name, u.phone as customer_phone, u.email as customer_email
                FROM plot_bookings b
                LEFT JOIN properties p ON p.id = b.property_id
                LEFT JOIN users u ON u.id = b.customer_id
                WHERE b.id = ? AND (b.associate_id = ? OR b.created_by = ?)
            ");
            $stmt->execute([$id, $userId, $userId]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($booking) {
                $stmt2 = $pdo->prepare("SELECT * FROM booking_payment_receipts WHERE booking_id = ? ORDER BY receipt_date DESC");
                $stmt2->execute([$id]);
                $receipts = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Throwable $e) {
            error_log('BookingReceipt error: ' . $e->getMessage());
        }

        if (!$booking) {
            $_SESSION['error'] = 'Booking not found or access denied';
            $this->redirect('/associate/my-bookings');
            return;
        }

        $this->render('associate/booking_receipt', [
            'page_title' => 'Booking Receipt - APS Dream Home',
            'current_page' => 'my-bookings',
            'booking' => $booking,
            'receipts' => $receipts,
        ]);
    }

    /**
     * Rank Eligibility Dashboard
     */
    public function rankEligibility()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $currentRank = 'associate';
        $lifetimeVolume = 0;
        $monthlyVolume = 0;
        $teamSize = 0;
        $directLegs = 0;
        $allRanks = [];

        try {
            // Get current rank from mlm_profiles
            $stmt = $pdo->prepare("SELECT * FROM mlm_profiles WHERE user_id = ?");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);
            $currentRank = strtolower($profile['rank'] ?? 'associate');

            // Get lifetime volume
            $stmt2 = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM plot_bookings WHERE (associate_id = ? OR created_by = ?) AND status NOT IN ('cancelled', 'refunded')");
            $stmt2->execute([$userId, $userId]);
            $lifetimeVolume = (float)$stmt2->fetchColumn();

            // Get monthly volume
            $stmt3 = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM plot_bookings WHERE (associate_id = ? OR created_by = ?) AND status NOT IN ('cancelled', 'refunded') AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
            $stmt3->execute([$userId, $userId]);
            $monthlyVolume = (float)$stmt3->fetchColumn();

            // Get direct legs (team size)
            $stmt4 = $pdo->prepare("SELECT COUNT(*) FROM mlm_network_tree WHERE parent_id = ?");
            $stmt4->execute([$userId]);
            $directLegs = (int)$stmt4->fetchColumn();

            // Get total team size (recursive count)
            $stmt5 = $pdo->prepare("SELECT COUNT(*) FROM mlm_network_tree WHERE parent_id = ?");
            $stmt5->execute([$userId]);
            $teamSize = (int)$stmt5->fetchColumn();

            // Get all ranks
            $allRanks = $pdo->query("SELECT * FROM mlm_rank_benefits ORDER BY rank_order")->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Throwable $e) {
            error_log('RankEligibility error: ' . $e->getMessage());
        }

        // Find next rank
        $nextRank = null;
        $rankOrder = ['associate', 'senior_associate', 'bdm', 'sr_bdm', 'vice_president', 'president', 'site_manager'];
        $currentIdx = array_search($currentRank, $rankOrder);
        if ($currentIdx !== false && $currentIdx < count($rankOrder) - 1) {
            $nextRank = $rankOrder[$currentIdx + 1];
        }

        $this->render('associate/rank_eligibility', [
            'page_title' => 'My Rank & Eligibility - APS Dream Home',
            'current_page' => 'rank-eligibility',
            'current_rank' => $currentRank,
            'next_rank' => $nextRank,
            'all_ranks' => $allRanks,
            'lifetime_volume' => $lifetimeVolume,
            'monthly_volume' => $monthlyVolume,
            'team_size' => $teamSize,
            'direct_legs' => $directLegs,
        ]);
    }

    /**
     * Book Plot - Form for associates to book plots for customers (GET=form, POST=save)
     */
    public function bookPlot()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        // GET: Load plots and colonies for form
        $plots = [];
        $colonies = [];
        try {
            // Get available plots
            $plots = $pdo->query("
                SELECT p.id, p.plot_number, p.area_sqft, p.price, p.colony_id, c.name as colony_name
                FROM plots p
                JOIN colonies c ON p.colony_id = c.id
                WHERE p.status = 'available' AND c.status = 'active'
                ORDER BY c.name, p.plot_number
            ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // Get active colonies
            $colonies = $pdo->query("SELECT id, name FROM colonies WHERE status = 'active' ORDER BY name ASC")
                ->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('bookPlot form data error: ' . $e->getMessage());
        }

        // POST: Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize inputs
            $plotId = (int)($_POST['plot_id'] ?? 0);
            $customerName = trim($_POST['customer_name'] ?? '');
            $customerPhone = trim($_POST['customer_phone'] ?? '');
            $customerEmail = trim($_POST['customer_email'] ?? '');
            $customerAddress = trim($_POST['customer_address'] ?? '');
            $aadharNumber = trim($_POST['aadhar_number'] ?? '');
            $panNumber = trim($_POST['pan_number'] ?? '');
            $bookingAmount = (float)($_POST['booking_amount'] ?? 0);
            $paymentMode = trim($_POST['payment_mode'] ?? 'cash');
            $notes = trim($_POST['notes'] ?? '');

            // Validation
            if (empty($plotId) || empty($customerName) || empty($customerPhone)) {
                $_SESSION['error'] = 'Please fill all required fields (Plot, Customer Name, Phone)';
                $this->redirect('/associate/book-plot');
                return;
            }

            try {
                // Check plot availability
                $plot = $pdo->prepare("SELECT * FROM plots WHERE id = ? AND status = 'available'");
                $plot->execute([$plotId]);
                $plotData = $plot->fetch(\PDO::FETCH_ASSOC);

                if (!$plotData) {
                    $_SESSION['error'] = 'Selected plot is not available';
                    $this->redirect('/associate/book-plot');
                    return;
                }

                // Find or create customer
                [$tidSql, $tidParams] = $this->tenantWhere();
                $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?{$tidSql} LIMIT 1");
                $stmt->execute(array_merge([$customerPhone], $tidParams));
                $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($existing) {
                    $customerId = $existing['id'];
                } else {
                    // Create new customer with secure random password
                    // Customer will receive a "Complete Registration" email to set their own password
                    $tempPassword = bin2hex(random_bytes(12)); // 24-char secure random
                    $tidInsert = $this->tenantInsertData();
                    $pdo->prepare("INSERT INTO users (name, phone, email, role, password, created_at" . ($tidInsert ? ", tenant_id" : "") . ") VALUES (?, ?, ?, 'customer', ?, NOW()" . ($tidInsert ? ", ?" : "") . ")")
                        ->execute($tidInsert ? array_merge([$customerName, $customerPhone, $customerEmail, password_hash($tempPassword, PASSWORD_DEFAULT)], [$tidInsert['tenant_id']]) : [$customerName, $customerPhone, $customerEmail, password_hash($tempPassword, PASSWORD_DEFAULT)]);
                    $customerId = $pdo->lastInsertId();

                    // Generate password reset token so customer can set their own password
                    try {
                        $resetToken = bin2hex(random_bytes(32));
                        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                        $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())")
                            ->execute([$customerId, $resetToken, $expiresAt]);
                        $resetLink = BASE_URL . '/reset-password?token=' . $resetToken;

                        // Send "Complete Your Registration" email
                        if (class_exists('App\Services\EmailService')) {
                            $emailService = new \App\Services\EmailService();
                            $emailService->send([
                                'to' => $customerEmail,
                                'subject' => 'Welcome to APS Dream Home - Complete Your Registration',
                                'template' => 'welcome_enhanced',
                                'data' => [
                                    'name' => $customerName,
                                    'reset_link' => $resetLink,
                                    'temp_password' => $tempPassword,
                                ]
                            ]);
                        }
                    } catch (\Throwable $e) {
                        error_log("Failed to send welcome email to $customerEmail: " . $e->getMessage());
                    }
                }

                // Generate booking number
                $bookingNumber = 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

                // Create booking
                $totalAmount = (float)$plotData['price'];
                $pdo->prepare("
                    INSERT INTO plot_bookings 
                    (plot_id, customer_id, booking_number, booking_date, total_plot_value, booking_amount, status, associate_id, created_by, notes, created_at)
                    VALUES (?, ?, ?, CURDATE(), ?, ?, 'pending_approval', ?, ?, ?, NOW())
                ")->execute([$plotId, $customerId, $bookingNumber, $totalAmount, $bookingAmount, $userId, $userId, $notes]);

                $bookingId = $pdo->lastInsertId();

                // Update plot status
                $pdo->prepare("UPDATE plots SET status = 'booked', customer_id = ?, booking_date = CURDATE(), updated_at = NOW() WHERE id = ?")->execute([$customerId, $plotId]);

                // Handle document uploads
                $uploadDir = __DIR__ . '/../../../uploads/booking_documents/' . $bookingId . '/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $uploadedFiles = [];
                $files = ['aadhar_doc', 'pan_doc', 'form_copy'];
                foreach ($files as $fileKey) {
                    if (!empty($_FILES[$fileKey]['tmp_name']) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
                        $newName = $fileKey . '_' . time() . '.' . $ext;
                        $target = $uploadDir . $newName;
                        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $target)) {
                            $uploadedFiles[$fileKey] = 'booking_documents/' . $bookingId . '/' . $newName;
                        }
                    }
                }

                // Save document references
                if (!empty($uploadedFiles)) {
                    foreach ($uploadedFiles as $docType => $docPath) {
                        $pdo->prepare("INSERT INTO booking_documents (booking_id, document_type, document_path, uploaded_at) VALUES (?, ?, ?, NOW())")
                            ->execute([$bookingId, $docType, $docPath]);
                    }
                }

                $_SESSION['success'] = 'Booking submitted successfully! Booking ID: ' . $bookingNumber;
                $this->redirect('/associate/book-plot');
                return;
            } catch (\Throwable $e) {
                error_log('bookPlot error: ' . $e->getMessage());
                $_SESSION['error'] = 'Failed to submit booking. Please try again.';
            }
        }

$this->render('associate/book_plot', [
            'page_title' => 'Book Plot for Customer - APS Dream Home',
            'current_page' => 'book-plot',
            'plots' => $plots,
            'colonies' => $colonies,
        ], 'layouts/associate');
    }

    /**
     * Associate Tools Hub - Smart calculators for associates
     */
    public function tools()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $base = BASE_URL;
        
        $this->render('associate/tools', [
            'page_title' => 'Smart Tools - APS Dream Home Associate',
            'current_page' => 'tools',
            'base' => $base,
        ], 'layouts/associate');
    }

    /**
     * EMI Calculator for associates
     */
    public function emiCalculator()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
        }

        $principal = (float)($_POST['principal'] ?? 0);
        $rate = (float)($_POST['rate'] ?? 8.5);
        $tenure = (int)($_POST['tenure'] ?? 240);

        if ($principal <= 0 || $rate <= 0 || $tenure <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid inputs'], 400);
        }

        $monthlyRate = $rate / 12 / 100;
        $emi = $principal * $monthlyRate * pow(1 + $monthlyRate, $tenure) / (pow(1 + $monthlyRate, $tenure) - 1);
        $totalPayable = $emi * $tenure;
        $totalInterest = $totalPayable - $principal;

        return $this->jsonResponse([
            'success' => true,
            'emi' => round($emi, 2),
            'total_payable' => round($totalPayable, 2),
            'total_interest' => round($totalInterest, 2),
        ]);
    }

    /**
     * Stamp Duty Calculator for associates
     */
    public function stampDutyCalculator()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
        }

        $propertyValue = (float)($_POST['property_value'] ?? 0);
        $state = $_POST['state'] ?? 'UP';
        $gender = $_POST['gender'] ?? 'male';
        $propertyType = $_POST['property_type'] ?? 'residential';

        if ($propertyValue <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid property value'], 400);
        }

        // UP Stamp Duty Rates (simplified)
        $rates = [
            'UP' => ['male' => 0.07, 'female' => 0.06, 'joint' => 0.065],
            'DL' => ['male' => 0.06, 'female' => 0.04, 'joint' => 0.05],
            'MH' => ['male' => 0.05, 'female' => 0.04, 'joint' => 0.045],
        ];

        $stateRates = $rates[$state] ?? $rates['UP'];
        $rate = $stateRates[$gender] ?? $stateRates['male'];
        
        $stampDuty = $propertyValue * $rate;
        $registration = $propertyValue * 0.01; // 1% registration
        $total = $stampDuty + $registration;

        return $this->jsonResponse([
            'success' => true,
            'stamp_duty' => round($stampDuty, 2),
            'registration' => round($registration, 2),
            'total' => round($total, 2),
            'rate_used' => ($rate * 100) . '%',
        ]);
    }

    /**
     * Plot Size Converter
     */
    public function plotConverter()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
        }

        $value = (float)($_POST['value'] ?? 0);
        $from = $_POST['from'] ?? 'sqft';
        $to = $_POST['to'] ?? 'sqyd';

        $conversions = [
            'sqft' => 1,
            'sqyd' => 9,
            'sqm' => 10.764,
            'acre' => 43560,
            'bigha' => 27000, // UP bigha
            'biswa' => 1350,
            'hectare' => 107639,
            'guntha' => 1089,
        ];

        if (!isset($conversions[$from]) || !isset($conversions[$to]) || $value <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid conversion'], 400);
        }

        $sqft = $value * $conversions[$from];
        $result = $sqft / $conversions[$to];

        return $this->jsonResponse([
            'success' => true,
            'input' => $value,
            'from' => $from,
            'to' => $to,
            'result' => round($result, 4),
        ]);
    }

    /**
     * Commission Calculator for associates
     */
    public function commissionCalculator()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
        }

        $saleValue = (float)($_POST['sale_value'] ?? 0);
        $rank = $_POST['rank'] ?? 'associate';
        $track = $_POST['track'] ?? 'A'; // A, B, or C

        if ($saleValue <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid sale value'], 400);
        }

        // Rank-based commission rates
        $rankRates = [
            'associate' => 0.05,
            'sr_associate' => 0.07,
            'bdm' => 0.10,
            'sr_bdm' => 0.12,
            'vice_president' => 0.15,
            'president' => 0.18,
            'site_manager' => 0.20,
        ];

        $rate = $rankRates[$rank] ?? 0.05;
        
        // Track multipliers
        $trackMult = ['A' => 1.0, 'B' => 0.5, 'C' => 0.3];
        $mult = $trackMult[$track] ?? 1.0;

        $commission = $saleValue * $rate * $mult;
        $effectiveRate = $rate * $mult * 100;

        return $this->jsonResponse([
            'success' => true,
            'commission' => round($commission, 2),
            'effective_rate' => round($effectiveRate, 2),
            'base_rate' => $rate * 100,
            'track' => $track,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    // SITE VISITS — Full CRUD
    // ═══════════════════════════════════════════════════════════════════

    /**
     * List site visits with tabs: today / upcoming / past / all
     */
    public function siteVisits()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;
        $tab = $_GET['tab'] ?? 'upcoming';
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $stats = [
            'total' => 0, 'today' => 0, 'upcoming' => 0,
            'completed' => 0, 'cancelled' => 0, 'overdue' => 0,
        ];
        $visits = [];

        try {
            $where = "(sv.assigned_to = ? OR sv.user_id = ?)";

            // Stats
            $st = $pdo->prepare("SELECT COUNT(*) FROM site_visits sv WHERE $where");
            $st->execute([$userId, $userId]);
            $stats['total'] = (int)$st->fetchColumn();

            $st = $pdo->prepare("SELECT COUNT(*) FROM site_visits sv WHERE $where AND sv.visit_date = CURDATE() AND sv.status NOT IN ('cancelled','completed')");
            $st->execute([$userId, $userId]);
            $stats['today'] = (int)$st->fetchColumn();

            $st = $pdo->prepare("SELECT COUNT(*) FROM site_visits sv WHERE $where AND sv.visit_date > CURDATE() AND sv.status NOT IN ('cancelled','completed')");
            $st->execute([$userId, $userId]);
            $stats['upcoming'] = (int)$st->fetchColumn();

            $st = $pdo->prepare("SELECT COUNT(*) FROM site_visits sv WHERE $where AND sv.status = 'completed'");
            $st->execute([$userId, $userId]);
            $stats['completed'] = (int)$st->fetchColumn();

            $st = $pdo->prepare("SELECT COUNT(*) FROM site_visits sv WHERE $where AND sv.visit_date < CURDATE() AND sv.status NOT IN ('completed','cancelled')");
            $st->execute([$userId, $userId]);
            $stats['overdue'] = (int)$st->fetchColumn();

            // Visit list
            $sql = "SELECT sv.*, l.name as lead_name, l.phone as lead_phone
                    FROM site_visits sv
                    LEFT JOIN leads l ON l.id = sv.lead_id
                    WHERE $where";

            if ($tab === 'today') {
                $sql .= " AND sv.visit_date = CURDATE() AND sv.status NOT IN ('cancelled','completed')";
            } elseif ($tab === 'upcoming') {
                $sql .= " AND sv.visit_date >= CURDATE() AND sv.status NOT IN ('cancelled','completed')";
            } elseif ($tab === 'past') {
                $sql .= " AND (sv.visit_date < CURDATE() OR sv.status IN ('completed','cancelled'))";
            } elseif ($tab === 'completed') {
                $sql .= " AND sv.status = 'completed'";
            } elseif ($tab === 'overdue') {
                $sql .= " AND sv.visit_date < CURDATE() AND sv.status NOT IN ('completed','cancelled')";
            }

            $sql .= " ORDER BY sv.visit_date DESC, sv.visit_time DESC LIMIT 50";
            $st = $pdo->prepare($sql);
            $st->execute([$userId, $userId]);
            $visits = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('siteVisits error: ' . $e->getMessage());
        }

        $this->render('associate/site_visits', [
            'page_title' => 'Site Visits - APS Dream Home',
            'current_page' => 'site_visits',
            'visits' => $visits,
            'stats' => $stats,
            'active_tab' => $tab,
        ], 'layouts/associate');
    }

    /**
     * Schedule a site visit (GET=form, POST=save)
     */
    public function scheduleSiteVisit()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        // Get leads for dropdown
        $leads = [];
        try {
            $st = $pdo->prepare("SELECT id, name, phone FROM leads WHERE (created_by = ? OR assigned_to = ?) AND deleted_at IS NULL ORDER BY name ASC");
            $st->execute([$userId, $userId]);
            $leads = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        // Get colonies for dropdown
        $colonies = [];
        try {
            $colonies = $pdo->query("SELECT id, name FROM colonies WHERE status = 'active' ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        // Handle POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadId = (int)($_POST['lead_id'] ?? 0);
            $visitorName = trim($_POST['visitor_name'] ?? '');
            $visitorPhone = trim($_POST['visitor_phone'] ?? '');
            $visitDate = $_POST['visit_date'] ?? '';
            $visitTime = $_POST['visit_time'] ?? '';
            $colonyId = (int)($_POST['colony_id'] ?? 0);
            $duration = (int)($_POST['duration'] ?? 60);
            $notes = trim($_POST['notes'] ?? '');

            if (empty($visitorName) || empty($visitorPhone) || empty($visitDate) || empty($visitTime)) {
                $_SESSION['error'] = 'Please fill all required fields.';
                $this->redirect('/associate/site-visits/schedule');
                return;
            }

            try {
                $st = $pdo->prepare("
                    INSERT INTO site_visits (lead_id, colony_id, assigned_to, user_id, visitor_name, visitor_phone, visit_date, visit_time, duration_minutes, status, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', ?, NOW())
                ");
                $st->execute([
                    $leadId ?: null, $colonyId ?: null, $userId, $userId,
                    $visitorName, $visitorPhone, $visitDate, $visitTime,
                    $duration, $notes
                ]);

                // Update lead status to site_visit if linked
                if ($leadId) {
                    try {
                        $pdo->prepare("UPDATE leads SET status = 'site_visit', updated_at = NOW() WHERE id = ? AND status NOT IN ('closed_won','closed_lost')")->execute([$leadId]);
                    } catch (\Throwable $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
                }

                $_SESSION['success'] = 'Site visit scheduled for ' . date('M d, Y', strtotime($visitDate)) . ' at ' . date('h:i A', strtotime($visitTime)) . '!';
                $this->redirect('/associate/site-visits');
                return;
            } catch (\Throwable $e) {
                error_log('scheduleSiteVisit error: ' . $e->getMessage());
                $_SESSION['error'] = 'Failed to schedule visit.';
            }
        }

        $this->render('associate/schedule_site_visit', [
            'page_title' => 'Schedule Site Visit - APS Dream Home',
            'current_page' => 'site_visits',
            'leads' => $leads,
            'colonies' => $colonies,
            'selected_lead' => $_GET['lead_id'] ?? '',
        ], 'layouts/associate');
    }

    /**
     * Complete a site visit with feedback
     */
    public function completeSiteVisit($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/site-visits');
            return;
        }

        $feedback = trim($_POST['feedback'] ?? '');
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));

        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();

            $st = $pdo->prepare("UPDATE site_visits SET status = 'completed', feedback = ?, rating = ?, completed_at = NOW() WHERE id = ? AND (assigned_to = ? OR user_id = ?)");
            $st->execute([$feedback, $rating, $id, $userId, $userId]);

            if ($st->rowCount() > 0) {
                // Log activity if linked to a lead
                $visit = $pdo->prepare("SELECT lead_id FROM site_visits WHERE id = ?");
                $visit->execute([$id]);
                $v = $visit->fetch(\PDO::FETCH_ASSOC);
                if ($v && !empty($v['lead_id'])) {
                    try {
                        $pdo->prepare("INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at) VALUES (?, 'site_visit', ?, ?, NOW())")
                            ->execute([$v['lead_id'], "Site visit completed. Rating: $rating/5. " . mb_substr($feedback, 0, 200), $userId]);
                    } catch (\Throwable $e) { error_log("AssociateController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
                }

                $_SESSION['success'] = 'Site visit marked as completed!';
            } else {
                $_SESSION['error'] = 'Visit not found.';
            }
        } catch (\Throwable $e) {
            error_log('completeSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to complete visit.';
        }
        $this->redirect('/associate/site-visits');
    }

    /**
     * Cancel a site visit
     */
    public function cancelSiteVisit($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/site-visits');
            return;
        }

        $reason = trim($_POST['reason'] ?? 'Cancelled');

        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();
            $st = $pdo->prepare("UPDATE site_visits SET status = 'cancelled', notes = CONCAT(COALESCE(notes,''), '\n\nCancelled: ', ?) WHERE id = ? AND (assigned_to = ? OR user_id = ?)");
            $st->execute([$reason, $id, $userId, $userId]);

            $_SESSION['success'] = $st->rowCount() > 0 ? 'Site visit cancelled.' : 'Visit not found.';
        } catch (\Throwable $e) {
            error_log('cancelSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to cancel visit.';
        }
        $this->redirect('/associate/site-visits');
    }

    /**
     * Reschedule a site visit
     */
    public function rescheduleSiteVisit($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/site-visits');
            return;
        }

        $newDate = $_POST['new_date'] ?? '';
        $newTime = $_POST['new_time'] ?? '';

        if (empty($newDate) || empty($newTime)) {
            $_SESSION['error'] = 'Please select new date and time.';
            $this->redirect('/associate/site-visits');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();
            $st = $pdo->prepare("UPDATE site_visits SET visit_date = ?, visit_time = ?, status = 'rescheduled' WHERE id = ? AND (assigned_to = ? OR user_id = ?)");
            $st->execute([$newDate, $newTime, $id, $userId, $userId]);

            $_SESSION['success'] = $st->rowCount() > 0 ? 'Visit rescheduled to ' . date('M d, Y', strtotime($newDate)) . '!' : 'Visit not found.';
        } catch (\Throwable $e) {
            error_log('rescheduleSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to reschedule.';
        }
        $this->redirect('/associate/site-visits');
    }

    /**
     * Calendar data AJAX endpoint — returns tasks + site visits for FullCalendar-style rendering
     */
    public function calendarData()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $month = max(1, min(12, (int)($_GET['month'] ?? date('m'))));
        $year = max(2024, (int)($_GET['year'] ?? date('Y')));
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $events = [];

        try {
            // Tasks
            $st = $pdo->prepare("
                SELECT t.id, t.title, t.due_date as date, t.priority, t.status,
                       l.name as lead_name, 'task' as type
                FROM crm_tasks t
                LEFT JOIN leads l ON l.id = t.lead_id
                WHERE (t.assigned_to = ? OR t.created_by = ?)
                  AND t.due_date BETWEEN ? AND ?
                ORDER BY t.due_date ASC
            ");
            $st->execute([$userId, $userId, $startDate, $endDate]);
            foreach ($st->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $events[] = [
                    'id' => 'task_' . $row['id'],
                    'title' => $row['title'],
                    'date' => $row['date'],
                    'type' => 'task',
                    'priority' => $row['priority'],
                    'status' => $row['status'],
                    'lead_name' => $row['lead_name'] ?? '',
                ];
            }

            // Site visits
            $st = $pdo->prepare("
                SELECT sv.id, sv.visitor_name, sv.visit_date as date, sv.visit_time, sv.status,
                       l.name as lead_name, 'site_visit' as type
                FROM site_visits sv
                LEFT JOIN leads l ON l.id = sv.lead_id
                WHERE (sv.assigned_to = ? OR sv.user_id = ?)
                  AND sv.visit_date BETWEEN ? AND ?
                ORDER BY sv.visit_date ASC, sv.visit_time ASC
            ");
            $st->execute([$userId, $userId, $startDate, $endDate]);
            foreach ($st->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $events[] = [
                    'id' => 'visit_' . $row['id'],
                    'title' => 'Visit: ' . $row['visitor_name'],
                    'date' => $row['date'],
                    'time' => $row['visit_time'],
                    'type' => 'site_visit',
                    'status' => $row['status'],
                    'lead_name' => $row['lead_name'] ?? '',
                ];
            }
        } catch (\Throwable $e) {
            error_log('calendarData error: ' . $e->getMessage());
        }

        header('Content-Type: application/json');
        echo json_encode($events);
    }

    // ═══════════════════════════════════════════════════════════════════
    // ENHANCED LEAD DETAIL — site visits for a lead
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Get site visits for a lead (used in lead detail page)
     */
    private function getLeadSiteVisits($leadId): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();
            $st = $pdo->prepare("SELECT * FROM site_visits WHERE lead_id = ? ORDER BY visit_date DESC, visit_time DESC");
            $st->execute([$leadId]);
            return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // CSV LEAD IMPORT — bulk import leads from CSV file
    // ═══════════════════════════════════════════════════════════════════

    public function importLeads()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $preview = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['csv_file']['tmp_name'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, 'r');
            if ($handle !== false) {
                $db = \App\Core\Database\Database::getInstance();
                $pdo = $db->getConnection();

                // Skip header
                $header = fgetcsv($handle);
                $lineNum = 1;

                while (($row = fgetcsv($handle)) !== false) {
                    $lineNum++;
                    // Expected columns: name, phone, email, source, budget, location, notes
                    $name = trim($row[0] ?? '');
                    $phone = trim($row[1] ?? '');
                    $email = trim($row[2] ?? '');
                    $source = trim($row[3] ?? 'csv_import');
                    $budget = trim($row[4] ?? '');
                    $location = trim($row[5] ?? '');
                    $notes = trim($row[6] ?? '');

                    if (empty($name) || empty($phone)) {
                        $skipped++;
                        $errors[] = "Line $lineNum: Missing name or phone";
                        continue;
                    }

                    // Check duplicate by phone
                    $dup = $pdo->prepare("SELECT id FROM leads WHERE phone = ? AND (created_by = ? OR assigned_to = ?) AND deleted_at IS NULL");
                    $dup->execute([$phone, $userId, $userId]);
                    if ($dup->fetch()) {
                        $skipped++;
                        $errors[] = "Line $lineNum: Phone $phone already exists";
                        continue;
                    }

                    try {
                        $st = $pdo->prepare("
                            INSERT INTO leads (name, phone, email, source, budget_range, location_preference, notes, status, priority, created_by, assigned_to, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'new', 'medium', ?, ?, NOW(), NOW())
                        ");
                        $st->execute([$name, $phone, $email, $source ?: 'csv_import', $budget, $location, $notes, $userId, $userId]);
                        $imported++;
                    } catch (\Throwable $e) {
                        $skipped++;
                        $errors[] = "Line $lineNum: " . $e->getMessage();
                    }
                }
                fclose($handle);

                if ($imported > 0) {
                    $_SESSION['success'] = "Imported $imported leads successfully!" . ($skipped > 0 ? " $skipped skipped." : '');
                } else {
                    $_SESSION['error'] = "No leads imported. " . ($skipped > 0 ? "$skipped rows skipped." : 'Check CSV format.');
                }
                $this->redirect('/associate/leads');
                return;
            }
        }

        $this->render('associate/import_leads', [
            'page_title' => 'Import Leads - APS Dream Home',
            'current_page' => 'leads',
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ], 'layouts/associate');
    }

    // ═══════════════════════════════════════════════════════════════════
    // BULK WHATSAPP — send WhatsApp to multiple leads at once
    // ═══════════════════════════════════════════════════════════════════

    public function bulkWhatsApp()
    {
        $this->requireAuth();
        $this->layout = 'layouts/associate';
        $userId = $_SESSION['user_id'] ?? 0;

        $leads = [];
        $sent = 0;
        $selectedIds = $_POST['lead_ids'] ?? ($_GET['ids'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Get selected leads
        if (!empty($selectedIds)) {
            $ids = array_map('intval', is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds));
            $ids = array_filter($ids);
            if (!empty($ids)) {
                $db = \App\Core\Database\Database::getInstance();
                $pdo = $db->getConnection();
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $st = $pdo->prepare("SELECT id, name, phone FROM leads WHERE id IN ($placeholders) AND (created_by = ? OR assigned_to = ?) AND deleted_at IS NULL");
                $params = array_merge($ids, [$userId, $userId]);
                $st->execute($params);
                $leads = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            }
        }

        // If POST with message, generate WhatsApp links
        $whatsappLinks = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($message) && !empty($leads)) {
            foreach ($leads as $l) {
                $phone = preg_replace('/[^0-9]/', '', $l['phone'] ?? '');
                if (strlen($phone) >= 10) {
                    $personalized = str_replace(
                        ['{name}', '{phone}'],
                        [$l['name'] ?? '', $l['phone'] ?? ''],
                        $message
                    );
                    $whatsappLinks[] = [
                        'name' => $l['name'],
                        'phone' => $phone,
                        'url' => 'https://wa.me/91' . $phone . '?text=' . urlencode($personalized),
                    ];
                    $sent++;
                }
            }
        }

        $this->render('associate/bulk_whatsapp', [
            'page_title' => 'Bulk WhatsApp - APS Dream Home',
            'current_page' => 'leads',
            'leads' => $leads,
            'whatsappLinks' => $whatsappLinks,
            'sent' => $sent,
            'message' => $message,
        ], 'layouts/associate');
    }

    // ═══════════════════════════════════════════════════════════════════
    // LEAD ASSIGNMENT — share leads with team members
    // ═══════════════════════════════════════════════════════════════════

    public function assignLead($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        $assignTo = (int)($_POST['assign_to'] ?? 0);
        if ($assignTo <= 0) {
            $_SESSION['error'] = 'Please select a team member.';
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();

            // Verify lead ownership
            $lead = $pdo->prepare("SELECT id, name FROM leads WHERE id = ? AND (created_by = ? OR assigned_to = ?) AND deleted_at IS NULL");
            $lead->execute([$id, $userId, $userId]);
            if (!$lead->fetch()) {
                $_SESSION['error'] = 'Lead not found.';
                $this->redirect('/associate/leads');
                return;
            }

            // Verify assignee is in same team (simplified: same role = associate)
            $assignee = $pdo->prepare("SELECT id, name FROM users WHERE id = ? AND role = 'associate'");
            $assignee->execute([$assignTo]);
            if (!$assignee->fetch()) {
                $_SESSION['error'] = 'Invalid team member.';
                $this->redirect("/associate/leads/{$id}");
                return;
            }

            // Assign
            $pdo->prepare("UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?")->execute([$assignTo, $id]);

            // Log activity
            $assigneeName = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $assigneeName->execute([$assignTo]);
            $aName = $assigneeName->fetchColumn() ?: 'Unknown';

            $pdo->prepare("INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at) VALUES (?, 'assignment', ?, ?, NOW())")
                ->execute([$id, "Lead assigned to $aName", $userId]);

            $_SESSION['success'] = "Lead assigned to $aName successfully!";
        } catch (\Throwable $e) {
            error_log('assignLead error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to assign lead.';
        }
        $this->redirect("/associate/leads/{$id}");
    }

    // ═══════════════════════════════════════════════════════════════════
    // LEAD SCORE RECALCULATION — AI scoring via LeadScoringService
    // ═══════════════════════════════════════════════════════════════════

    public function recalculateScore($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/associate/leads/{$id}");
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();

            // Verify lead ownership
            $lead = $pdo->prepare("SELECT id, name FROM leads WHERE id = ? AND (created_by = ? OR assigned_to = ?) AND deleted_at IS NULL");
            $lead->execute([$id, $userId, $userId]);
            if (!$lead->fetch()) {
                $_SESSION['error'] = 'Lead not found.';
                $this->redirect('/associate/leads');
                return;
            }

            $scoringService = new \App\Services\LeadScoringService();
            $scores = $scoringService->calculateScore($id);
            if ($scores) {
                $scoringService->saveScore($id, $scores);
                $_SESSION['success'] = "Score recalculated: {$scores['total']}/100 ({$scores['rank']})";
            } else {
                $_SESSION['error'] = 'Could not calculate score for this lead.';
            }
        } catch (\Throwable $e) {
            error_log('recalculateScore error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to recalculate score.';
        }
        $this->redirect("/associate/leads/{$id}");
    }

    // ═══════════════════════════════════════════════════════════════════
    // BATCH RECALCULATE SCORES — recalculate AI scores for all leads
    // ═══════════════════════════════════════════════════════════════════

    public function recalculateAllScores()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/leads');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();

            $leads = $pdo->prepare("SELECT id FROM leads WHERE (created_by = ? OR assigned_to = ?) AND deleted_at IS NULL");
            $leads->execute([$userId, $userId]);
            $leadIds = $leads->fetchAll(\PDO::FETCH_COLUMN) ?: [];

            $scoringService = new \App\Services\LeadScoringService();
            $processed = 0;
            foreach ($leadIds as $lid) {
                $scores = $scoringService->calculateScore($lid);
                if ($scores) {
                    $scoringService->saveScore($lid, $scores);
                    $processed++;
                }
            }

            $_SESSION['success'] = "Scores recalculated for {$processed} leads.";
        } catch (\Throwable $e) {
            error_log('recalculateAllScores error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to recalculate scores.';
        }
        $this->redirect('/associate/leads');
    }

    /**
     * Associate Colony Map — Leaflet plot map for associates
     * GET /associate/colonies/{id}/map
     */
    public function colonyMap($id)
    {
        $this->requireAuth();
        try {
            $colony = $this->db->fetchOne(
                "SELECT c.*, d.name as district_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id WHERE c.id = ?",
                [$id]
            );
            if (!$colony) {
                $_SESSION['error'] = 'Colony not found';
                $this->redirect('/associate/browse');
                return;
            }
            $plots = $this->db->fetchAll(
                "SELECT id, plot_number, block, area_sqft, width_ft, length_ft,
                        status, price_per_sqft, total_price, corner_plot, park_facing,
                        road_facing, gata_number
                 FROM plots WHERE colony_id = ?
                 ORDER BY block, plot_number",
                [$id]
            );
            $this->render('associate/colony_map', [
                'page_title' => $colony['name'] . ' — Plot Map',
                'colony' => $colony,
                'plots' => $plots,
            ], 'layouts/associate');
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to load map';
            $this->redirect('/associate/browse');
        }
    }

    /**
     * Export leads as CSV
     */
    public function exportLeads()
    {
        $this->requireAuth();
        @session_start();
        $userId = $_SESSION['user_id'] ?? 0;

        $status = $_GET['status'] ?? '';
        $where = "(created_by = ? OR assigned_to = ?) AND deleted_at IS NULL";
        $params = [$userId, $userId];
        if ($status) { $where .= " AND status = ?"; $params[] = $status; }

        $db = \App\Core\Database\Database::getInstance();
        $leads = $db->fetchAll("SELECT * FROM leads WHERE $where ORDER BY created_at DESC", $params);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.csv"');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, ['ID', 'Name', 'Phone', 'Email', 'Status', 'Priority', 'Score', 'Source', 'Property Interest', 'Budget', 'Location', 'Notes', 'Created At']);

        foreach ($leads as $lead) {
            fputcsv($fp, [
                $lead['id'], $lead['name'], $lead['phone'], $lead['email'] ?? '',
                $lead['status'], $lead['priority'] ?? '', $lead['lead_score'] ?? 0,
                $lead['source'] ?? '', $lead['property_interest'] ?? '',
                $lead['budget_range'] ?? '', $lead['location_preference'] ?? '',
                $lead['notes'] ?? '', $lead['created_at'],
            ]);
        }
        fclose($fp);
        exit;
    }
}
