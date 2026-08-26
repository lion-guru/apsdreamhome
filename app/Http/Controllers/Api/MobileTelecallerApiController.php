<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Middleware\TenantContext;
use App\Traits\TenantAwareTrait;

class MobileTelecallerApiController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    public function dashboard()
    {
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $stats = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT 
                        COUNT(*) as total_calls,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_calls,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_calls,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_calls,
                        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_calls
                    FROM ai_calling_schedule 
                    WHERE ai_agent_id = ?{$tidSql}
                ");
                $stmt->execute($params);
                $stats = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Telecaller dashboard error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $stats]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function report()
    {
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Unauthorized', 401);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$userId, $tid] : [$userId];

            $report = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as total_calls,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                        SUM(duration_seconds) as total_duration
                    FROM ai_calling_schedule
                    WHERE ai_agent_id = ?{$tidSql}
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date DESC
                ");
                $stmt->execute($params);
                $report = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log("Telecaller report error: " . $e->getMessage());
            }

            $this->jsonResponse(['success' => true, 'data' => $report]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }
}