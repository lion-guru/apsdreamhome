<?php

namespace App\Http\Controllers\MLM;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\LoggingService;

/**
 * MLM Dashboard Controller
 * Manages Multi-Level Marketing features
 */
class MLMDashboardController extends BaseController
{
    protected $db;
    protected $logger;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
        $this->logger = new LoggingService();

        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }

    /**
     * Main MLM Dashboard
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $base = defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . $base . '/login');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $base   = defined('BASE_URL') ? BASE_URL : '';

        // ── defaults (safe fallback if no associate record) ──────────────
        $data = [
            'page_title'        => 'My MLM Dashboard',
            'associate_name'    => $_SESSION['name'] ?? 'Associate',
            'associate_id'      => 'N/A',
            'referral_code'     => 'APS000000',
            'team_size'         => 0,
            'total_sales'       => 0,
            'commission_earned' => 0,
            'pending_payout'    => 0,
            'downline_members'  => [],
            'commission_history'=> [],
            'payout_history'    => [],
            // rank progress
            'current_rank'          => 'associate',
            'current_rank_label'    => 'Associate',
            'current_rank_color'    => '#94a3b8',
            'current_rank_rate'     => 5,
            'next_rank'             => 'sr_associate',
            'next_rank_label'       => 'Sr. Associate',
            'next_rank_color'       => '#64748b',
            'next_rank_rate'        => 7,
            'next_rank_min_volume'  => 1000000,
            'lifetime_sales_volume' => 0,
            'rank_progress_pct'     => 0,
            'amount_to_next_rank'   => 1000000,
            'commission_by_type'    => [],
            'rank_benefits'         => [],
        ];

        try {
            $pdo = $this->db->getConnection();

            // ── 1. Associate record (from `associates` table — real table) ──
            $stmt = $pdo->prepare("
                SELECT a.*, u.name AS user_name, u.phone
                FROM associates a
                JOIN users u ON u.id = a.user_id
                WHERE a.user_id = ? AND a.status = 'active'
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $associate = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$associate) {
                // Not an associate — show "become associate" prompt
                return $this->render('pages/mlm_dashboard', $data);
            }

            $associateId  = (int)$associate['id'];
            $referralCode = $associate['referral_code'] ?? ('APS' . str_pad($associateId, 6, '0', STR_PAD_LEFT));
            $data['associate_name'] = $associate['user_name'] ?? ($_SESSION['name'] ?? 'Associate');
            $data['associate_id']   = 'APS-' . str_pad($associateId, 5, '0', STR_PAD_LEFT);
            $data['referral_code']  = $referralCode;

            // ── 2. All rank benefits (sorted by volume threshold) ────────────
            $rankRows = $pdo->query("
                SELECT rank_name, rank_order, direct_sale_pct, min_volume, color_code, badge_icon
                FROM mlm_rank_benefits
                ORDER BY rank_order ASC
            ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $data['rank_benefits'] = $rankRows;

            // Build rank lookup: name => row
            $rankMap = [];
            foreach ($rankRows as $r) {
                $rankMap[$r['rank_name']] = $r;
            }

            // ── 3. Associate's current rank from mlm_network_tree or associates ──
            $currentRankName = $associate['level'] ?? 'associate';   // associates.level stores rank name
            if (!isset($rankMap[$currentRankName])) {
                $currentRankName = 'associate';
            }
            $currentRank = $rankMap[$currentRankName] ?? ['rank_name'=>'associate','direct_sale_pct'=>5,'min_volume'=>0,'color_code'=>'#94a3b8','badge_icon'=>'fa-user'];

            // ── 4. Next rank ─────────────────────────────────────────────────
            $nextRank    = null;
            $foundCurrent = false;
            foreach ($rankRows as $r) {
                if ($foundCurrent) { $nextRank = $r; break; }
                if ($r['rank_name'] === $currentRankName) { $foundCurrent = true; }
            }

            // ── 5. Lifetime sales volume (sum of plot_bookings for this associate) ──
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(b.total_amount), 0) AS total_volume
                FROM plot_bookings b
                WHERE b.associate_id = ? AND b.status NOT IN ('cancelled','rejected')
            ");
            $stmt->execute([$associateId]);
            $lifetimeVolume = (float)($stmt->fetchColumn() ?: 0);
            $data['total_sales']            = $lifetimeVolume;
            $data['lifetime_sales_volume']  = $lifetimeVolume;

            // ── 6. Rank progress calculation ─────────────────────────────────
            $currentMin = (float)($currentRank['min_volume'] ?? 0);
            $nextMin    = $nextRank ? (float)$nextRank['min_volume'] : $currentMin;

            if ($nextRank && $nextMin > $currentMin) {
                $progressInSlab = max(0, $lifetimeVolume - $currentMin);
                $slabSize       = $nextMin - $currentMin;
                $progressPct    = min(100, round(($progressInSlab / $slabSize) * 100, 1));
                $amountNeeded   = max(0, $nextMin - $lifetimeVolume);
            } else {
                // At top rank
                $progressPct  = 100;
                $amountNeeded = 0;
            }

            $rankLabels = [
                'associate'      => 'Associate',
                'sr_associate'   => 'Sr. Associate',
                'bdm'            => 'BDM',
                'sr_bdm'         => 'Sr. BDM',
                'vice_president' => 'Vice President',
                'president'      => 'President',
                'site_manager'   => 'Site Manager',
            ];

            $data['current_rank']         = $currentRankName;
            $data['current_rank_label']   = $rankLabels[$currentRankName] ?? ucfirst(str_replace('_', ' ', $currentRankName));
            $data['current_rank_color']   = $currentRank['color_code'] ?? '#94a3b8';
            $data['current_rank_rate']    = (float)($currentRank['direct_sale_pct'] ?? 5);
            $data['next_rank']            = $nextRank['rank_name'] ?? null;
            $data['next_rank_label']      = $nextRank ? ($rankLabels[$nextRank['rank_name']] ?? ucfirst(str_replace('_',' ',$nextRank['rank_name']))) : 'Top Rank';
            $data['next_rank_color']      = $nextRank['color_code'] ?? '#1e40af';
            $data['next_rank_rate']       = $nextRank ? (float)$nextRank['direct_sale_pct'] : (float)($currentRank['direct_sale_pct'] ?? 20);
            $data['next_rank_min_volume'] = $nextMin;
            $data['rank_progress_pct']    = $progressPct;
            $data['amount_to_next_rank']  = $amountNeeded;

            // ── 7. Commission stats from mlm_commission_ledger ───────────────
            $stmt = $pdo->prepare("
                SELECT
                    COALESCE(SUM(amount), 0)                                              AS total_earned,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) AS pending_payout
                FROM mlm_commission_ledger
                WHERE beneficiary_user_id = ?
            ");
            $stmt->execute([$userId]);
            $commTotals = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
            $data['commission_earned'] = (float)($commTotals['total_earned']  ?? 0);
            $data['pending_payout']    = (float)($commTotals['pending_payout'] ?? 0);

            // Commission breakdown by type
            $stmt = $pdo->prepare("
                SELECT commission_type, COALESCE(SUM(amount),0) AS total, COUNT(*) AS cnt
                FROM mlm_commission_ledger
                WHERE beneficiary_user_id = ?
                GROUP BY commission_type
                ORDER BY total DESC
            ");
            $stmt->execute([$userId]);
            $byType = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $data['commission_by_type'] = $byType;

            // Commission history (last 20)
            $stmt = $pdo->prepare("
                SELECT l.*, u.name AS source_name
                FROM mlm_commission_ledger l
                LEFT JOIN users u ON u.id = l.source_user_id
                WHERE l.beneficiary_user_id = ?
                ORDER BY l.created_at DESC
                LIMIT 20
            ");
            $stmt->execute([$userId]);
            $data['commission_history'] = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // ── 8. Downline members (direct + L2) from mlm_network_tree ──────
            $stmt = $pdo->prepare("
                SELECT t.level, u.name, u.id, a.status, a.created_at AS join_date,
                       t.parent_id
                FROM mlm_network_tree t
                JOIN users u ON u.id = t.associate_id
                LEFT JOIN associates a ON a.user_id = t.associate_id
                WHERE t.parent_id = ?
                   OR (t.level = 2 AND t.parent_id IN (
                        SELECT associate_id FROM mlm_network_tree WHERE parent_id = ?
                   ))
                ORDER BY t.level, u.name
                LIMIT 50
            ");
            $stmt->execute([$userId, $userId]);
            $data['downline_members'] = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // Team size
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM mlm_network_tree WHERE parent_id = ?");
            $stmt->execute([$userId]);
            $data['team_size'] = (int)$stmt->fetchColumn();

        } catch (\Exception $e) {
            $this->logger->error('MLMDashboardController error: ' . $e->getMessage());
        }

        return $this->render('pages/mlm_dashboard', $data);
    }

    /**
     * Network Tree View
     */
    public function networkTree()
    {
        $userId = $_SESSION['user_id'];

        try {
            $pdo = $this->db->getConnection();

            // Get associate details
            $stmt = $pdo->prepare("SELECT * FROM mlm_associates WHERE user_id = ?");
            $stmt->execute([$userId]);
            $associate = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$associate) {
                header('Location: /mlm/dashboard');
                exit;
            }

            // Build network tree (up to 5 levels deep)
            $tree = $this->buildNetworkTree($associate['id'], 5);

            $this->view('mlm/network_tree', [
                'title' => 'Network Tree',
                'associate' => $associate,
                'tree' => $tree
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Network tree error: " . $e->getMessage());
            $this->view('error', ['message' => 'Failed to load network tree']);
        }
    }

    /**
     * Commissions View
     */
    public function commissions()
    {
        $userId = $_SESSION['user_id'];

        try {
            $pdo = $this->db->getConnection();

            // Get associate details
            $stmt = $pdo->prepare("SELECT * FROM mlm_associates WHERE user_id = ?");
            $stmt->execute([$userId]);
            $associate = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$associate) {
                header('Location: /mlm/dashboard');
                exit;
            }

            // Get commission history
            $stmt = $pdo->prepare("
                SELECT c.*, s.name as from_associate_name, t.name as to_associate_name,
                       p.title as property_title, b.booking_id
                FROM mlm_commissions c
                LEFT JOIN mlm_associates s ON c.from_associate_id = s.id
                LEFT JOIN mlm_associates t ON c.to_associate_id = t.id
                LEFT JOIN bookings b ON c.booking_id = b.id
                LEFT JOIN properties p ON b.property_id = p.id
                WHERE c.to_associate_id = ?
                ORDER BY c.created_at DESC
                LIMIT 100
            ");
            $stmt->execute([$associate['id']]);
            $commissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Calculate totals
            $totalEarned = array_sum(array_column($commissions, 'amount'));
            $totalPaid = array_sum(array_filter($commissions, fn($c) => $c['status'] === 'paid', ARRAY_FILTER_USE_BOTH));
            $totalPending = $totalEarned - $totalPaid;

            $this->view('mlm/commissions', [
                'title' => 'My Commissions',
                'associate' => $associate,
                'commissions' => $commissions,
                'totalEarned' => $totalEarned,
                'totalPaid' => $totalPaid,
                'totalPending' => $totalPending
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Commissions error: " . $e->getMessage());
            $this->view('error', ['message' => 'Failed to load commissions']);
        }
    }

    /**
     * Payouts View
     */
    public function payouts()
    {
        $userId = $_SESSION['user_id'];

        try {
            $pdo = $this->db->getConnection();

            // Get associate details
            $stmt = $pdo->prepare("SELECT * FROM mlm_associates WHERE user_id = ?");
            $stmt->execute([$userId]);
            $associate = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$associate) {
                header('Location: /mlm/dashboard');
                exit;
            }

            // Get payout history
            $tid = (int)TenantContext::getId();
            $stmt = $pdo->prepare("
                SELECT p.*, b.account_number, b.bank_name, b.ifsc_code
                FROM mlm_payouts p
                LEFT JOIN mlm_bank_details b ON p.bank_detail_id = b.id
                WHERE p.associate_id = ?" . ($tid > 1 ? " AND p.tenant_id = ?" : "") . "
                ORDER BY p.created_at DESC
                LIMIT 50
            ");
            $params = [$associate['id']];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
            $payouts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get pending payout amount
            $stmt = $pdo->prepare("
                SELECT SUM(amount) as pending_amount
                FROM mlm_commissions
                WHERE to_associate_id = ? AND status = 'pending'
            ");
            $stmt->execute([$associate['id']]);
            $pending = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Get bank details
            $stmt = $pdo->prepare("SELECT * FROM mlm_bank_details WHERE associate_id = ?");
            $stmt->execute([$associate['id']]);
            $bankDetails = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->view('mlm/payouts', [
                'title' => 'My Payouts',
                'associate' => $associate,
                'payouts' => $payouts,
                'pendingAmount' => $pending['pending_amount'] ?? 0,
                'bankDetails' => $bankDetails
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Payouts error: " . $e->getMessage());
            $this->view('error', ['message' => 'Failed to load payouts']);
        }
    }

    /**
     * Request Payout
     */
    public function requestPayout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /mlm/payouts');
            exit;
        }

        $userId = $_SESSION['user_id'];

        try {
            $pdo = $this->db->getConnection();

            // Get associate details
            $stmt = $pdo->prepare("SELECT * FROM mlm_associates WHERE user_id = ?");
            $stmt->execute([$userId]);
            $associate = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$associate) {
                $_SESSION['error'] = 'Associate not found';
                header('Location: /mlm/payouts');
                exit;
            }

            // Get pending commission amount
            $stmt = $pdo->prepare("
                SELECT SUM(amount) as total
                FROM mlm_commissions
                WHERE to_associate_id = ? AND status = 'pending'
            ");
            $stmt->execute([$associate['id']]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $amount = $result['total'] ?? 0;

            // Minimum payout check (e.g., ₹1000)
            if ($amount < 1000) {
                $_SESSION['error'] = 'Minimum payout amount is ₹1,000';
                header('Location: /mlm/payouts');
                exit;
            }

            // Get bank details
            $stmt = $pdo->prepare("SELECT id FROM mlm_bank_details WHERE associate_id = ?");
            $stmt->execute([$associate['id']]);
            $bank = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$bank) {
                $_SESSION['error'] = 'Please add bank details first';
                header('Location: /mlm/payouts');
                exit;
            }

            // Create payout request
            $stmt = $pdo->prepare("
                INSERT INTO mlm_payouts (tenant_id, associate_id, gross_amount, status, created_at)
                VALUES (?, ?, ?, 'requested', NOW())
            ");
            $stmt->execute([TenantContext::getId() ?? 1, $associate['id'], $amount]);
            $payoutId = $pdo->lastInsertId();

            // Update commissions to 'processing'
            $stmt = $pdo->prepare("
                UPDATE mlm_commissions
                SET status = 'processing', payout_id = ?
                WHERE to_associate_id = ? AND status = 'pending'
            ");
            $stmt->execute([$payoutId, $associate['id']]);

            $_SESSION['success'] = 'Payout request submitted successfully';
            header('Location: /mlm/payouts');
            exit;
        } catch (\Exception $e) {
            $this->logger->error("Payout request error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to submit payout request';
            header('Location: /mlm/payouts');
            exit;
        }
    }

    /**
     * Register as Associate
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processRegistration();
            return;
        }

        // Get referral code from URL if provided
        $referralCode = $_GET['ref'] ?? '';

        // Get sponsor if referral code provided
        $sponsor = null;
        if ($referralCode) {
            $pdo = $this->db->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM mlm_associates WHERE referral_code = ?");
            $stmt->execute([$referralCode]);
            $sponsor = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        $this->view('mlm/register', [
            'title' => 'Become an Associate',
            'referralCode' => $referralCode,
            'sponsor' => $sponsor
        ]);
    }

    /**
     * Process Associate Registration
     */
    private function processRegistration()
    {
        $userId = $_SESSION['user_id'];

        try {
            $pdo = $this->db->getConnection();

            // Check if already registered
            $stmt = $pdo->prepare("SELECT id FROM mlm_associates WHERE user_id = ?");
            $stmt->execute([$userId]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'You are already registered as an associate';
                header('Location: /mlm/dashboard');
                exit;
            }

            // Validate inputs
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $sponsorCode = trim($_POST['sponsor_code'] ?? '');

            if (empty($phone) || empty($address)) {
                $_SESSION['error'] = 'Phone and address are required';
                header('Location: /mlm/register');
                exit;
            }

            // Find sponsor
            $sponsorId = null;
            $level = 1;
            if ($sponsorCode) {
                $stmt = $pdo->prepare("SELECT id, level FROM mlm_associates WHERE referral_code = ?");
                $stmt->execute([$sponsorCode]);
                $sponsor = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($sponsor) {
                    $sponsorId = $sponsor['id'];
                    $level = $sponsor['level'] + 1;
                }
            }

            // Generate referral code
            $referralCode = $this->generateReferralCode();

            // Get user details
            $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Insert associate
            $stmt = $pdo->prepare("
                INSERT INTO mlm_associates 
                (user_id, name, email, phone, sponsor_id, level, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([
                $userId,
                $user['name'],
                $user['email'],
                $phone,
                $sponsorId,
                $level
            ]);

            $associateId = $pdo->lastInsertId();

            // Update network tree
            if ($sponsorId) {
                $this->updateNetworkTree($associateId, $sponsorId);
            }

            $_SESSION['success'] = 'Registration successful! Welcome to the network.';
            header('Location: /mlm/dashboard');
            exit;
        } catch (\Exception $e) {
            $this->logger->error("Associate registration error: " . $e->getMessage());
            $_SESSION['error'] = 'Registration failed. Please try again.';
            header('Location: /mlm/register');
            exit;
        }
    }

    /**
     * Helper: Get Network Statistics
     */
    private function getNetworkStats($associateId)
    {
        $pdo = $this->db->getConnection();

        // Direct referrals (Level 1)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM mlm_associates WHERE sponsor_id = ?");
        $stmt->execute([$associateId]);
        $direct = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

        // Total team size (all levels)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM mlm_network_tree 
            WHERE ancestor_id = ? AND associate_id != ?
        ");
        $stmt->execute([$associateId, $associateId]);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

        return [
            'direct' => $direct,
            'total' => $total
        ];
    }

    /**
     * Helper: Get Commission Summary
     */
    private function getCommissionSummary($associateId)
    {
        $pdo = $this->db->getConnection();

        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                SUM(amount) as total
            FROM mlm_commissions
            WHERE to_associate_id = ?
        ");
        $stmt->execute([$associateId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Helper: Get Recent Payouts
     */
    private function getRecentPayouts($associateId)
    {
        $pdo = $this->db->getConnection();

        $tid = (int)TenantContext::getId();
        $stmt = $pdo->prepare("
            SELECT * FROM mlm_payouts
            WHERE associate_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . "
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $params = [$associateId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Helper: Get Downline by Level
     */
    private function getDownlineByLevel($associateId)
    {
        $pdo = $this->db->getConnection();

        $stmt = $pdo->prepare("
            SELECT level, COUNT(*) as count
            FROM mlm_network_tree
            WHERE ancestor_id = ?
            GROUP BY level
            ORDER BY level
        ");
        $stmt->execute([$associateId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Helper: Get Current Rank
     */
    private function getCurrentRank($rankId)
    {
        $pdo = $this->db->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM mlm_ranks WHERE id = ?");
        $stmt->execute([$rankId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Helper: Build Network Tree
     */
    private function buildNetworkTree($associateId, $maxDepth = 5)
    {
        $pdo = $this->db->getConnection();

        $tree = [];

        // Get this associate
        $stmt = $pdo->prepare("SELECT * FROM mlm_associates WHERE id = ?");
        $stmt->execute([$associateId]);
        $root = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$root) return $tree;

        $tree = [
            'associate' => $root,
            'children' => $this->getChildren($associateId, 1, $maxDepth)
        ];

        return $tree;
    }

    /**
     * Helper: Get Children Recursively
     */
    private function getChildren($parentId, $currentLevel, $maxDepth)
    {
        if ($currentLevel > $maxDepth) return [];

        $pdo = $this->db->getConnection();

        $stmt = $pdo->prepare("
            SELECT a.*, u.profile_image
            FROM mlm_associates a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.sponsor_id = ?
        ");
        $stmt->execute([$parentId]);
        $children = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($children as &$child) {
            $child['children'] = $this->getChildren($child['id'], $currentLevel + 1, $maxDepth);
        }

        return $children;
    }

    /**
     * Helper: Update Network Tree
     */
    private function updateNetworkTree($associateId, $sponsorId)
    {
        $pdo = $this->db->getConnection();

        // Get sponsor's tree level
        $stmt = $pdo->prepare("
            SELECT level FROM mlm_network_tree
            WHERE associate_id = ?
        ");
        $stmt->execute([$sponsorId]);
        $sponsorRow = $stmt->fetch(\PDO::FETCH_ASSOC);
        $level = $sponsorRow ? ((int)$sponsorRow['level'] + 1) : 1;

        // Insert associate under sponsor (adjacency-list row)
        $stmt = $pdo->prepare("
            INSERT INTO mlm_network_tree (tenant_id, associate_id, sponsor_id, parent_id, level)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([TenantContext::getId() ?? 1, $associateId, $sponsorId, $sponsorId, $level]);
    }

    /**
     * Helper: Generate Referral Code
     */
    private function generateReferralCode()
    {
        return 'APS' . strtoupper(substr(uniqid(), -6));
    }

    public function dashboard()
    {
        $this->index();
    }
}
