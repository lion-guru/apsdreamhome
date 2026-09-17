<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use \App\Traits\TenantAwareTrait;

class ReferralController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Public endpoint to verify a referral code (no auth required)
     * GET /api/v2/verify-referral?code=CODE
     */
    public function verify()
    {
        header('Content-Type: application/json');
        
        $code = trim($_GET['code'] ?? '');
        if (empty($code)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Referral code is required']);
            return;
        }

        $code = strtoupper($code);
        $tid = (int)$this->tenantId();
        $tidScope = $tid > 1 ? ' AND u.tenant_id = ?' : '';
        $params = [$code];
        if ($tid > 1) $params[] = $tid;

        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.email, u.phone, u.referral_code, u.mlm_rank,
                       p.current_level as rank_name
                FROM users u
                JOIN mlm_profiles p ON u.id = p.user_id
                WHERE u.referral_code = ? AND u.role IN ('associate','agent') AND p.status = 'active' {$tidScope}
                LIMIT 1
            ");
            $stmt->execute($params);
            $assoc = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($assoc) {
                echo json_encode([
                    'success' => true,
                    'associate' => [
                        'id' => (int)$assoc['id'],
                        'name' => $assoc['name'],
                        'email' => $assoc['email'],
                        'phone' => $assoc['phone'],
                        'referral_code' => $assoc['referral_code'],
                        'rank' => $assoc['rank_name'] ?? $assoc['mlm_rank'] ?? 'Associate'
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid or inactive referral code']);
            }
        } catch (\Throwable $e) {
            error_log('ReferralController::verify error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    public function dashboard()
    {
        header('Content-Type: application/json');
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthenticated']);
            return;
        }
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?{$tidSql}");
            $stmt->execute(array_merge([$userId], $tidParams));
            $total = (int)$stmt->fetchColumn();
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ? AND DATE(created_at) = CURDATE(){$tidSql}");
            $stmt->execute(array_merge([$userId], $tidParams));
            $today = (int)$stmt->fetchColumn();

            echo json_encode(['success' => true, 'data' => [
                'total_referrals' => $total,
                'today_referrals' => $today,
                'referral_code' => 'REF' . str_pad($userId, 6, '0', STR_PAD_LEFT)
            ]]);
        } catch (\Throwable $e) {
            error_log('ReferralController::dashboard error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    public function stats()
    {
        header('Content-Type: application/json');
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthenticated']);
            return;
        }
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM users WHERE referred_by = ?{$tidSql} GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30");
            $stmt->execute(array_merge([$userId], $tidParams));
            $referrals = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $referrals]);
        } catch (\Throwable $e) {
            error_log('ReferralController::stats error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    public function index()
    {
        header('Content-Type: application/json');
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthenticated']);
            return;
        }
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE referred_by = ?{$tidSql} ORDER BY created_at DESC");
            $stmt->execute(array_merge([$userId], $tidParams));
            $referrals = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $referrals]);
        } catch (\Throwable $e) {
            error_log('ReferralController::index error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
}
