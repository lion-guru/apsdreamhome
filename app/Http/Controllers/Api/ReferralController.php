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
