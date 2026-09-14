<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;
use Exception;

class CampaignTemplateApiController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->skipCsrfProtection();
    }

    protected function requireAdmin(): int
    {
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }
        $role = $GLOBALS['api_user_role'] ?? '';
        if (!in_array($role, ['admin', 'employee', 'super_admin', 'superadmin'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Admin access required']);
            exit;
        }
        return $userId;
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $tid = $this->tenantId();
            $where = '';
            $params = [];
            if ($tid > 1) {
                $where = 'WHERE tenant_id = ?';
                $params = [$tid];
            }

            $stmt = Database::getInstance()->getConnection()->prepare("
                SELECT * FROM marketing_campaign_templates 
                $where 
                ORDER BY created_at DESC
            ");
            $stmt->execute($params);
            $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $this->json(['success' => true, 'data' => $templates]);
        } catch (Exception $e) {
            error_log("CampaignTemplateApiController::index error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to fetch templates'], 500);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $tid = $this->tenantId();
            $where = 'id = ?';
            $params = [$id];
            if ($tid > 1) {
                $where .= ' AND tenant_id = ?';
                $params[] = $tid;
            }

            $stmt = Database::getInstance()->getConnection()->prepare("
                SELECT * FROM marketing_campaign_templates 
                WHERE $where
            ");
            $stmt->execute($params);
            $template = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$template) {
                $this->json(['success' => false, 'error' => 'Template not found'], 404);
                return;
            }

            $this->json(['success' => true, 'data' => $template]);
        } catch (Exception $e) {
            error_log("CampaignTemplateApiController::show error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to fetch template'], 500);
        }
    }
}