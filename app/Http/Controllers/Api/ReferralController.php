<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

class ReferralController extends BaseController
{
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
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?");
            $stmt->execute([$userId]);
            $total = (int)$stmt->fetchColumn();
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ? AND DATE(created_at) = CURDATE()");
            $stmt->execute([$userId]);
            $today = (int)$stmt->fetchColumn();

            echo json_encode(['success' => true, 'data' => [
                'total_referrals' => $total,
                'today_referrals' => $today,
                'referral_code' => 'REF' . str_pad($userId, 6, '0', STR_PAD_LEFT)
            ]]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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
            $stmt = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM users WHERE referred_by = ? GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30");
            $stmt->execute([$userId]);
            $referrals = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $referrals]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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
            $stmt = $this->db->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE referred_by = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            $referrals = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $referrals]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
