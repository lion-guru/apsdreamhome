<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use PDO;
use App\Traits\TenantAwareTrait;

class MobileMLMApiController extends BaseController
{
    use TenantAwareTrait;
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function getMlmSummary()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                return;
            }
            $summary = $this->getMlmUserSummary($userId);
            echo json_encode(['success' => true, 'data' => $summary]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'MLM Summary API error');
        }
    }

    public function getMlmPayouts()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                return;
            }
            $payouts = $this->getMlmPayoutHistory($userId);
            echo json_encode(['success' => true, 'data' => $payouts]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'MLM Payouts API error');
        }
    }

    public function getMlmIncentives()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                return;
            }
            $incentives = $this->getMlmIncentiveDashboard($userId);
            echo json_encode(['success' => true, 'data' => $incentives]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'MLM Incentives API error');
        }
    }

    public function getPendingPayouts()
    {
        $this->setCorsHeaders();
        try {
            $pending = $this->getPendingMlmPayouts();
            echo json_encode(['success' => true, 'data' => $pending]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Pending Payouts API error');
        }
    }

    private function getMlmUserSummary($userId)
    {
        try {
            $summary = [
                'user_id' => $userId,
                'rank' => 'Customer',
                'total_team_size' => 0,
                'active_members' => 0,
                'inactive_members' => 0,
                'total_earnings' => 0,
                'pending_payouts' => 0,
                'paid_payouts' => 0,
                'direct_referrals' => 0,
                'binary_left' => 0,
                'binary_right' => 0,
                'matching_bonus' => 0,
                'rank_progress' => [],
            ];

            // Get rank
            $stmt = $this->db->prepare("SELECT current_level FROM mlm_profiles WHERE user_id = ?");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($profile) {
                $summary['rank'] = $profile['current_level'] ?? 'Customer';
            }

            // Get team size
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM mlm_referrals WHERE referrer_user_id = ?");
            $stmt->execute([$userId]);
            $summary['direct_referrals'] = (int)$stmt->fetchColumn();

            // Get earnings
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(gross_amount), 0) FROM mlm_payouts WHERE associate_user_id = ?");
            $stmt->execute([$userId]);
            $summary['total_earnings'] = (float)$stmt->fetchColumn();

            $stmt = $this->db->prepare("SELECT COALESCE(SUM(gross_amount), 0) FROM mlm_payouts WHERE associate_user_id = ? AND status = 'pending'");
            $stmt->execute([$userId]);
            $summary['pending_payouts'] = (float)$stmt->fetchColumn();

            $stmt = $this->db->prepare("SELECT COALESCE(SUM(gross_amount), 0) FROM mlm_payouts WHERE associate_user_id = ? AND status = 'paid'");
            $stmt->execute([$userId]);
            $summary['paid_payouts'] = (float)$stmt->fetchColumn();

            return $summary;
        } catch (\Exception $e) {
            error_log("[MobileMLMApiController] getMlmUserSummary error: " . $e->getMessage());
            return [
                'user_id' => $userId,
                'rank' => 'Customer',
                'total_team_size' => 0,
                'active_members' => 0,
                'inactive_members' => 0,
                'total_earnings' => 0,
                'pending_payouts' => 0,
                'paid_payouts' => 0,
                'direct_referrals' => 0,
                'binary_left' => 0,
                'binary_right' => 0,
                'matching_bonus' => 0,
                'rank_progress' => [],
            ];
        }
    }

    private function getMlmPayoutHistory($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT id, amount, level, payment_method, status, processed_at, created_at FROM mlm_payouts WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[MobileMLMApiController] getMlmPayoutHistory error: " . $e->getMessage());
            return [];
        }
    }

    private function getMlmIncentiveDashboard($userId)
    {
        try {
            $incentives = [
                'total_incentives' => 0,
                'paid_incentives' => 0,
                'pending_incentives' => 0,
                'recent_incentives' => [],
            ];

            $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM mlm_payouts WHERE user_id = ? AND level = 'incentive'");
            $stmt->execute([$userId]);
            $incentives['total_incentives'] = (float)$stmt->fetchColumn();

            $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM mlm_payouts WHERE user_id = ? AND level = 'incentive' AND status = 'paid'");
            $stmt->execute([$userId]);
            $incentives['paid_incentives'] = (float)$stmt->fetchColumn();

            $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM mlm_payouts WHERE user_id = ? AND level = 'incentive' AND status = 'pending'");
            $stmt->execute([$userId]);
            $incentives['pending_incentives'] = (float)$stmt->fetchColumn();

            return $incentives;
        } catch (\Exception $e) {
            error_log("[MobileMLMApiController] getMlmIncentiveDashboard error: " . $e->getMessage());
            return [
                'total_incentives' => 0,
                'paid_incentives' => 0,
                'pending_incentives' => 0,
                'recent_incentives' => [],
            ];
        }
    }

    public function processPayouts()
    {
        $this->setCorsHeaders();
        try {
            $result = $this->processMlmPayouts();
            echo json_encode(['success' => true, 'message' => 'Payouts processed successfully', 'processed_count' => $result]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Process Payouts API error');
        }
    }

    private function getMlmNetworkTree($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT mr.referred_user_id as user_id, u.name, u.email, mr.created_at as joined_at
                FROM mlm_referrals mr
                LEFT JOIN users u ON u.id = mr.referred_user_id
                WHERE mr.referrer_user_id = ?
                ORDER BY mr.created_at DESC
            ");
            $stmt->execute([$userId]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'user_id' => $userId,
                'total_referrals' => count($members),
                'members' => $members,
            ];
        } catch (\Exception $e) {
            error_log("[MobileMLMApiController] getMlmNetworkTree error: " . $e->getMessage());
            return ['user_id' => $userId, 'total_referrals' => 0, 'members' => []];
        }
    }

    private function getMlmBusinessBreakdown($userId)
    {
        try {
            $breakdown = [
                'total_payouts' => 0,
                'paid_amount' => 0,
                'pending_amount' => 0,
                'by_status' => [],
                'recent_payouts' => [],
            ];

            $stmt = $this->db->prepare("SELECT status, COUNT(*) as count, COALESCE(SUM(gross_amount), 0) as total FROM mlm_payouts WHERE associate_user_id = ? GROUP BY status");
            $stmt->execute([$userId]);
            $byStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $breakdown['by_status'] = $byStatus;

            foreach ($byStatus as $row) {
                $breakdown['total_payouts'] += $row['total'];
                if ($row['status'] === 'paid') $breakdown['paid_amount'] = $row['total'];
                if ($row['status'] === 'pending') $breakdown['pending_amount'] = $row['total'];
            }

            $stmt = $this->db->prepare("SELECT id, gross_amount, status, created_at FROM mlm_payouts WHERE associate_user_id = ? ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$userId]);
            $breakdown['recent_payouts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $breakdown;
        } catch (\Exception $e) {
            error_log("[MobileMLMApiController] getMlmBusinessBreakdown error: " . $e->getMessage());
            return ['total_payouts' => 0, 'paid_amount' => 0, 'pending_amount' => 0, 'by_status' => [], 'recent_payouts' => []];
        }
    }

    public function getGlobalPayoutHistory()
    {
        try {
            $stmt = $this->db->query("SELECT id, gross_amount, status, payment_mode, created_at FROM mlm_payouts ORDER BY created_at DESC LIMIT 50");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[MobileMLMApiController] getGlobalPayoutHistory error: " . $e->getMessage());
            return [];
        }
    }

    public function getPayoutHistory()
    {
        $this->setCorsHeaders();
        try {
            $history = $this->getGlobalPayoutHistory();
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Payout History API error');
        }
    }

    public function getGenealogy()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                return;
            }
            $tree = $this->getMlmNetworkTree($userId);
            echo json_encode(['success' => true, 'data' => $tree]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Genealogy API error');
        }
    }

    public function getBusinessBreakdown()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                return;
            }
            $breakdown = $this->getMlmBusinessBreakdown($userId);
            echo json_encode(['success' => true, 'data' => $breakdown]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Business Breakdown API error');
        }
    }

    public function getMyTeam()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            return;
        }

        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

            $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM mlm_network_tree WHERE parent_id = ?");
            $totalStmt->execute([$userId]);
            $total = (int)$totalStmt->fetchColumn();

            $directSql = "SELECT nt.associate_id, u.name, u.email, u.phone, u.profile_image, nt.level, nt.created_at as joined_at
                FROM mlm_network_tree nt
                JOIN users u ON u.id = nt.associate_id
                WHERE nt.parent_id = ?
                ORDER BY nt.created_at DESC
                LIMIT ? OFFSET ?";
            $directReferrals = $this->db->fetchAll($directSql, [$userId, $limit, $offset]) ?? [];

            $mlmService = new \App\Services\MLMNetworkService();
            $teamSize = $mlmService->getTeamSize($userId);
            $directCount = $mlmService->getDirectCount($userId);
            $activeCount = (int)$this->db->fetchOne("SELECT COUNT(*) as cnt FROM mlm_network_tree nt JOIN users u ON u.id = nt.associate_id WHERE nt.parent_id = ? AND u.status = 'active'", [$userId])['cnt'] ?? 0;
            $inactiveCount = (int)$this->db->fetchOne("SELECT COUNT(*) as cnt FROM mlm_network_tree nt JOIN users u ON u.id = nt.associate_id WHERE nt.parent_id = ? AND u.status != 'active'", [$userId])['cnt'] ?? 0;
            $recentCount = (int)$this->db->fetchOne("SELECT COUNT(*) as cnt FROM mlm_network_tree WHERE parent_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [$userId])['cnt'] ?? 0;
            $totalBusiness = (float)$this->db->fetchOne("SELECT COALESCE(SUM(pb.total_plot_value), 0) as total_business FROM plot_bookings pb WHERE pb.associate_id IN (SELECT nt.associate_id FROM mlm_network_tree nt WHERE nt.parent_id = ?)", [$userId])['total_business'] ?? 0;

            echo json_encode([
                'success' => true,
                'data' => [
                    'direct_referrals' => $directReferrals,
                    'stats' => [
                        'total_team_size' => $teamSize,
                        'direct_referrals' => $directCount,
                        'active_members' => $activeCount,
                        'inactive_members' => $inactiveCount,
                        'recent_joinings_30d' => $recentCount,
                        'total_team_business' => $totalBusiness
                    ],
                    'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
                ]
            ]);
        } catch (\Exception $e) {
            error_log("[MobileMLMApiController] exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    public static function getPendingPayoutsData($db, $userId)
    {
        $stmt = $db->prepare("SELECT id, gross_amount, status, created_at FROM mlm_payouts WHERE associate_user_id = ? AND status = 'pending' ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function processPayoutsAction($db, $userId, $paymentMethod)
    {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT id FROM mlm_payouts WHERE associate_user_id = ? AND status = 'pending' ORDER BY created_at ASC FOR UPDATE");
            $stmt->execute([$userId]);
            $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($payouts as $payout) {
                $stmt = $db->prepare("UPDATE mlm_payouts SET status = 'processing', payment_mode = ? WHERE id = ?");
                $stmt->execute([$paymentMethod, $payout['id']]);
            }

            $db->commit();
            return count($payouts);
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getPayoutHistoryData($db, $userId)
    {
        $stmt = $db->prepare("SELECT id, gross_amount, status, payment_mode, created_at FROM mlm_payouts WHERE associate_user_id = ? ORDER BY created_at DESC LIMIT 100");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trackReferral()
    {
        $this->setCorsHeaders();
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $userId = (int)($input['user_id'] ?? $GLOBALS['api_user_id'] ?? 0);
            $referralCode = \App\Core\Security::sanitize($input['referral_code'] ?? '');
            $source = \App\Core\Security::sanitize($input['source'] ?? 'mobile');

            if (!$userId || empty($referralCode)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID and referral code are required']);
                return;
            }

            $result = $this->trackReferralData($userId, $referralCode, $source);
            echo json_encode(['success' => true, 'message' => 'Referral tracked', 'data' => $result]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Track Referral API error');
        }
    }

    public function punchIn()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $location = \App\Core\Security::sanitize($input['location'] ?? '');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $stmt = $this->db->prepare("INSERT INTO attendance (user_id, punch_in_time, punch_in_location, punch_in_ip, date, created_at) VALUES (?, NOW(), ?, ?, CURDATE(), NOW())");
            $stmt->execute([$userId, $location, $ip]);
            echo json_encode(['success' => true, 'message' => 'Punched in successfully']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Punch In API error');
        }
    }

    public function punchOut()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("SELECT id FROM attendance WHERE user_id = ? AND date = CURDATE() AND punch_out_time IS NULL ORDER BY punch_in_time DESC LIMIT 1");
            $stmt->execute([$userId]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No active punch-in found']);
                return;
            }

            $stmt = $this->db->prepare("UPDATE attendance SET punch_out_time = NOW(), updated_at = NOW() WHERE id = ?");
            $stmt->execute([$record['id']]);
            echo json_encode(['success' => true, 'message' => 'Punched out successfully']);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Punch Out API error');
        }
    }

    public function attendanceStatus()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = CURDATE() ORDER BY punch_in_time DESC LIMIT 1");
            $stmt->execute([$userId]);
            $status = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $status]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Attendance Status API error');
        }
    }

    private function trackReferralData($userId, $referralCode, $source)
    {
        $stmt = $this->db->prepare("SELECT id, name FROM users WHERE referral_code = ? LIMIT 1");
        $stmt->execute([$referralCode]);
        $referrer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$referrer) {
            return ['status' => 'error', 'message' => 'Invalid referral code'];
        }

        $stmt = $this->db->prepare("INSERT INTO referral_tracking (referrer_id, referred_user_id, source, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$referrer['id'], $userId, $source]);

        return ['status' => 'success', 'referrer_id' => $referrer['id'], 'referrer_name' => $referrer['name']];
    }

    public function getRankProgress()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            return;
        }

        try {
            // Get current rank from mlm_profiles
            $profileSql = "SELECT current_level, lifetime_sales as total_sales, total_commission FROM mlm_profiles WHERE user_id = ?";
            $profileStmt = $this->db->prepare($profileSql);
            $profileStmt->execute([$userId]);
            $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                echo json_encode([
                    'success' => true,
                    'data' => ['current_rank' => 'Associate', 'overall_progress_pct' => 0],
                ]);
                return;
            }

            $currentRank = $profile['current_level'] ?? 'associate';
            $totalSales = (float)($profile['total_sales'] ?? 0);
            $totalCommission = (float)($profile['total_commission'] ?? 0);

            // Define rank thresholds (matching mlm_rank_benefits)
            $rankThresholds = [
                'associate' => ['sales' => 0, 'commission' => 0, 'directs' => 0],
                'senior_associate' => ['sales' => 25000, 'commission' => 5000, 'directs' => 1],
                'bdm' => ['sales' => 100000, 'commission' => 25000, 'directs' => 2],
                'sr_bdm' => ['sales' => 300000, 'commission' => 75000, 'directs' => 3],
                'vice_president' => ['sales' => 800000, 'commission' => 250000, 'directs' => 4],
                'president' => ['sales' => 2000000, 'commission' => 750000, 'directs' => 5],
                'site_manager' => ['sales' => 5000000, 'commission' => 2000000, 'directs' => 6],
            ];

            $rankOrder = array_keys($rankThresholds);
            $currentIndex = array_search(strtolower($currentRank), $rankOrder);
            if ($currentIndex === false) $currentIndex = 0;

            $nextRank = $currentIndex < count($rankOrder) - 1 ? $rankOrder[$currentIndex + 1] : null;
            
            $progress = [
                'current_rank' => $currentRank,
                'total_sales' => $totalSales,
                'total_commission' => $totalCommission,
                'direct_count' => (int)($this->db->fetchOne("SELECT COUNT(*) FROM mlm_network_tree WHERE parent_id = ?", [$userId])['COUNT(*)'] ?? 0),
                'next_rank' => $nextRank,
            ];

            if ($nextRank) {
                $nextThreshold = $rankThresholds[$nextRank];
                $salesProgress = min(100, ($totalSales / max(1, $nextThreshold['sales'])) * 100);
                $commissionProgress = min(100, ($totalCommission / max(1, $nextThreshold['commission'])) * 100);
                $directsProgress = min(100, ($progress['direct_count'] / max(1, $nextThreshold['directs'])) * 100);
                
                $progress['next_rank_thresholds'] = $nextThreshold;
                $progress['sales_progress_pct'] = round($salesProgress, 1);
                $progress['commission_progress_pct'] = round($commissionProgress, 1);
                $progress['directs_progress_pct'] = round($directsProgress, 1);
                $progress['overall_progress_pct'] = round(($salesProgress + $commissionProgress + $directsProgress) / 3, 1);
                $progress['sales_remaining'] = max(0, $nextThreshold['sales'] - $totalSales);
                $progress['commission_remaining'] = max(0, $nextThreshold['commission'] - $totalCommission);
                $progress['directs_remaining'] = max(0, $nextThreshold['directs'] - $progress['direct_count']);
            } else {
                $progress['next_rank_thresholds'] = null;
                $progress['sales_progress_pct'] = 100;
                $progress['commission_progress_pct'] = 100;
                $progress['directs_progress_pct'] = 100;
                $progress['overall_progress_pct'] = 100;
                $progress['sales_remaining'] = 0;
                $progress['commission_remaining'] = 0;
                $progress['directs_remaining'] = 0;
            }

            echo json_encode(['success' => true, 'data' => $progress]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    public function requestPayout()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? Security::sanitize($_POST['user_id']) ?? 0);
        $amount = Security::sanitize($_POST['amount']) ?? 0;
        $remarks = Security::sanitize($_POST['remarks'] ?? 'Mobile app request');

        if (!$userId || $amount <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
            return;
        }

        try {
            // Check if user has enough pending balance
            $check = $this->db->fetchOne("SELECT SUM(amount) FROM mlm_commission_ledger WHERE user_id = ? AND status = 'pending'", [$userId]);
            $pending = $check[0] ?? 0;

            if ($amount > $pending) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Requested amount exceeds pending balance (₹' . $pending . ')']);
                return;
            }

            $sql = "INSERT INTO mlm_payout_requests (user_id, amount, status, remarks, tenant_id) VALUES (?, ?, 'pending', ?, ?)";
            $this->db->query($sql, [$userId, $amount, $remarks, (int)$this->tenantId()]);

            echo json_encode(['success' => true, 'message' => 'Payout request submitted successfully']);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    public function processMlmSale() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $saleAmount = (float)($input['sale_amount'] ?? 0);
        $plotId = (int)($input['plot_id'] ?? 0);

        // Validate input
        if ($saleAmount <= 0 || !$plotId) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Sale amount and plot ID required']); return;
        }
        if ($saleAmount > 100000000) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Invalid sale amount']); return;
        }
        try {
            $tid = (int)$this->tenantId();
            $commissionPct = 5.00;
            $commissionAmt = $saleAmount * ($commissionPct / 100);
            $stmt = $this->db->prepare("INSERT INTO mlm_commission_ledger (beneficiary_user_id, source_user_id, commission_type, amount, sale_amount, commission_percentage, status, notes, tenant_id, created_at) VALUES (?, ?, 'direct_sale', ?, ?, ?, 'pending', 'Mobile app sale submission', ?, NOW())");
            $stmt->execute([$userId, $userId, $commissionAmt, $saleAmount, $commissionPct, $tid]);
            echo json_encode(['success'=>true,'message'=>'Sale submitted for commission processing','commission_estimate'=>round($saleAmount * 0.05, 2)]);
        } catch (\Throwable $e) {
            error_log('MobileMLMApiController::processMlmSale error: ' . $e->getMessage());
            http_response_code(500); echo json_encode(['success'=>false,'error'=>'Internal server error']);
        }
    }

    public function upgradeMlmRank() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }

        // Validate user_id exists in users table (already validated by $userId)
        if ($userId <= 0) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Valid user ID required']); return;
        }
        try {
            // Resolve associate row for this user
            $stmt = $this->db->prepare("SELECT id FROM associates WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $assoc = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$assoc) {
                http_response_code(404); echo json_encode(['success'=>false,'error'=>'Associate record not found']); return;
            }
            $associateId = (int)$assoc['id'];

            // Current rank = latest to_rank in rank history (default 'associate')
            $stmt = $this->db->prepare("SELECT to_rank FROM mlm_rank_history WHERE associate_id = ? ORDER BY promoted_at DESC, id DESC LIMIT 1");
            $stmt->execute([$associateId]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentRank = $current['to_rank'] ?? 'associate';

            $ranks = ['associate','sr_associate','bdm','sr_bdm','vice_president','president','site_manager'];
            $idx = array_search($currentRank, $ranks);
            if ($idx === false) {
                $newRank = 'associate';
                $fromRank = null;
            } elseif ($idx < count($ranks) - 1) {
                $newRank = $ranks[$idx + 1];
                $fromRank = $currentRank;
            } else {
                http_response_code(400); echo json_encode(['success'=>false,'error'=>'Already at highest rank']); return;
            }

            $tid = (int)$this->tenantId();
            $this->db->prepare("INSERT INTO mlm_rank_history (associate_id, from_rank, to_rank, qualifying_volume_at_promotion, leg_count_at_promotion, promoted_by, is_manual, reason, tenant_id, created_at) VALUES (?, ?, ?, 0, 0, ?, 1, 'Requested via mobile app', ?, NOW())")
                ->execute([$associateId, $fromRank, $newRank, $userId, $tid]);
            echo json_encode(['success'=>true,'message'=>"Rank upgraded to $newRank",'from_rank'=>$fromRank,'to_rank'=>$newRank]);
        } catch (\Throwable $e) {
            error_log('MobileMLMApiController::upgradeMlmRank error: ' . $e->getMessage());
            http_response_code(500); echo json_encode(['success'=>false,'error'=>'Internal server error']);
        }
    }

    public function getForm16() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $year = (int)($_GET['year'] ?? date('Y'));
            // Ledger has no TDS column; TDS is deducted at payout-batch level. Report gross commission here.
            $stmt = $this->db->prepare("SELECT SUM(amount) as total_commission FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND YEAR(created_at) = ? AND status IN ('approved','paid')");
            $stmt->execute([$userId, $year]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $gross = (float)($data['total_commission'] ?? 0);
            // Pull actual TDS from payout batches linked to this user's ledger entries, if any
            $tds = 0.0;
            try {
                $t = $this->db->prepare("SELECT COALESCE(SUM(pe.tds_amount),0) as tds FROM payout_entries pe WHERE pe.beneficiary_user_id = ? AND YEAR(pe.created_at) = ?");
                $t->execute([$userId, $year]);
                $tds = (float)($t->fetch(PDO::FETCH_ASSOC)['tds'] ?? 0);
            } catch (\Throwable $e) { $tds = 0.0; }
            echo json_encode(['success'=>true,'data'=>[
                'financial_year' => "$year-" . ($year + 1),
                'total_commission' => $gross,
                'tds_deducted' => $tds,
                'net_payout' => $gross - $tds,
            ]]);
        } catch (\Throwable $e) {
            $year = (int)($_GET['year'] ?? date('Y'));
            echo json_encode(['success'=>true,'data'=>['financial_year'=>"$year-".($year+1),'total_commission'=>0,'tds_deducted'=>0,'net_payout'=>0]]);
        }
    }

    public function getTaxSummary() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT commission_type, SUM(amount) as total FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status IN ('approved','paid') GROUP BY commission_type");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalTds = 0.0;
            try {
                $t = $this->db->prepare("SELECT COALESCE(SUM(tds_amount),0) as total_tds FROM payout_entries WHERE beneficiary_user_id = ?");
                $t->execute([$userId]);
                $totalTds = (float)($t->fetch(PDO::FETCH_ASSOC)['total_tds'] ?? 0);
            } catch (\Throwable $e) { $totalTds = 0.0; }
            echo json_encode(['success'=>true,'data'=>[
                'breakdown' => $rows,
                'total_tds' => $totalTds,
                'total_commission' => array_sum(array_column($rows, 'total')),
            ]]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>true,'data'=>['breakdown'=>[],'total_tds'=>0,'total_commission'=>0]]);
        }
    }

    public function getIncentiveSummary()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); return; }
        try {
            $service = new \App\Services\MLMIncentiveService();
            $summary = $service->getIncentiveSummary($userId);
            echo json_encode(['success'=>true,'data'=>$summary]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'error'=>'Failed to get incentive summary']);
        }
    }

    public function getMonthlyTargets()
    {
        $this->setCorsHeaders();
        try {
            $service = new \App\Services\MLMIncentiveService();
            echo json_encode(['success'=>true,'data'=>$service->getMonthlyTargets()]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'error'=>'Failed to get targets']);
        }
    }

    public function listPackages()
    {
        $this->setCorsHeaders();
        try {
            $engine = new \App\Services\MlmInvestmentEngine();
            echo json_encode(['success'=>true,'data'=>$engine->listPackages()]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'error'=>'Failed to list packages']);
        }
    }

    public function purchasePackage()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); return; }
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $packageId = (int)($input['package_id'] ?? 0);
            if (!$packageId) { echo json_encode(['success'=>false,'error'=>'package_id required']); return; }
            $engine = new \App\Services\MlmInvestmentEngine();
            $result = $engine->processJoiningPackage($packageId, $userId, [
                'payment_method' => $input['payment_method'] ?? null,
                'payment_reference' => $input['payment_reference'] ?? null,
            ]);
            echo json_encode($result);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'error'=>'Failed to purchase package']);
        }
    }

    public function getGoals()
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); return; }
        try {
            $service = new \App\Services\EngagementService();
            $goals = $service->getGoals(['user_id'=>$userId], 20);
            echo json_encode(['success'=>true,'data'=>$goals]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'error'=>'Failed to get goals']);
        }
    }

    public function getLeaderboard($metricType = 'sales')
    {
        $this->setCorsHeaders();
        try {
            $service = new \App\Services\EngagementService();
            $leaderboard = $service->getLeaderboardSnapshot($metricType);
            echo json_encode(['success'=>true,'data'=>$leaderboard]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'error'=>'Failed to get leaderboard']);
        }
    }

    public function getFormData($type = 'colonies')
    {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); return; }
        try {
            $service = new \App\Services\FormSelectDataService();
            switch ($type) {
                case 'colonies': $data = $service->getColonies(); break;
                case 'properties': $data = $service->getProperties(); break;
                case 'plots': $data = $service->getPlots(); break;
                case 'states': $data = $service->getStates(); break;
                case 'districts': $data = $service->getDistricts(); break;
                case 'customers': $data = $service->getCustomers(); break;
                case 'associates': $data = $service->getAssociates(); break;
                case 'agents': $data = $service->getAgents(); break;
                case 'employees': $data = $service->getEmployees(); break;
                default: $data = [];
            }
            echo json_encode(['success'=>true,'data'=>$data]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'error'=>'Failed to get form data']);
        }
    }
}
