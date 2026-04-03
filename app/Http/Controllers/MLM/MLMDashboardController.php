<?php

namespace App\Http\Controllers\MLM;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
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
            header('Location: /login');
            exit;
        }
    }

    /**
     * Main MLM Dashboard
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'] ?? 'associate';

        try {
            $pdo = $this->db->getConnection();

            // Get associate details
            $stmt = $pdo->prepare("SELECT * FROM mlm_associates WHERE user_id = ?");
            $stmt->execute([$userId]);
            $associate = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$associate) {
                // Not registered as associate yet
                $this->view('mlm/register', [
                    'title' => 'Become an Associate'
                ]);
                return;
            }

            // Get network statistics
            $stats = $this->getNetworkStats($associate['id']);

            // Get commission summary
            $commissions = $this->getCommissionSummary($associate['id']);

            // Get recent payouts
            $payouts = $this->getRecentPayouts($associate['id']);

            // Get downline count by level
            $downline = $this->getDownlineByLevel($associate['id']);

            // Get current rank details
            $rank = $this->getCurrentRank($associate['rank_id']);

            $this->view('mlm/dashboard', [
                'title' => 'MLM Dashboard',
                'associate' => $associate,
                'stats' => $stats,
                'commissions' => $commissions,
                'payouts' => $payouts,
                'downline' => $downline,
                'rank' => $rank,
                'userRole' => $userRole
            ]);
        } catch (\Exception $e) {
            $this->logger->error("MLM Dashboard error: " . $e->getMessage());
            $this->view('error', ['message' => 'Failed to load MLM dashboard']);
        }
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
            $stmt = $pdo->prepare("
                SELECT p.*, b.account_number, b.bank_name, b.ifsc_code
                FROM mlm_payouts p
                LEFT JOIN mlm_bank_details b ON p.bank_detail_id = b.id
                WHERE p.associate_id = ?
                ORDER BY p.created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$associate['id']]);
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
                INSERT INTO mlm_payouts (associate_id, amount, bank_detail_id, status, created_at)
                VALUES (?, ?, ?, 'requested', NOW())
            ");
            $stmt->execute([$associate['id'], $amount, $bank['id']]);
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
                (user_id, name, email, phone, address, referral_code, sponsor_id, level, rank_id, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'active', NOW())
            ");
            $stmt->execute([
                $userId,
                $user['name'],
                $user['email'],
                $phone,
                $address,
                $referralCode,
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

        $stmt = $pdo->prepare("
            SELECT * FROM mlm_payouts
            WHERE associate_id = ?
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$associateId]);
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

        // Insert self
        $stmt = $pdo->prepare("
            INSERT INTO mlm_network_tree (ancestor_id, associate_id, level, distance)
            VALUES (?, ?, 0, 0)
        ");
        $stmt->execute([$associateId, $associateId]);

        // Get all ancestors of sponsor
        $stmt = $pdo->prepare("
            SELECT ancestor_id, level FROM mlm_network_tree
            WHERE associate_id = ?
        ");
        $stmt->execute([$sponsorId]);
        $ancestors = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Insert connections to all ancestors
        foreach ($ancestors as $ancestor) {
            $stmt = $pdo->prepare("
                INSERT INTO mlm_network_tree (ancestor_id, associate_id, level, distance)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $ancestor['ancestor_id'],
                $associateId,
                $ancestor['level'] + 1,
                $ancestor['level'] + 1
            ]);
        }
    }

    /**
     * Helper: Generate Referral Code
     */
    private function generateReferralCode()
    {
        return 'APS' . strtoupper(substr(uniqid(), -6));
    }
}
