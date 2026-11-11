<?php
/**
 * LeadScoringController — REST API endpoints for AI lead scoring
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\LeadScoringService;

class LeadScoringController extends BaseController
{
    private $scoringService;

    public function __construct()
    {
        parent::__construct();
        $this->scoringService = new LeadScoringService();
    }

    protected function skipCsrfProtection(): bool
    {
        return true; // Bearer-token API; CSRF handled by auth
    }

    protected function requireAuth(array $allowedRoles = []): int {
        while (ob_get_level()) { ob_end_clean(); }
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }
        if (!empty($allowedRoles)) {
            $role = $GLOBALS['api_user_role'] ?? '';
            if (!in_array($role, $allowedRoles, true)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                exit;
            }
        }
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $GLOBALS['api_user_role'] ?? '';
        $_SESSION['role'] = $_SESSION['user_role'];
        return $userId;
    }

    /**
     * Get lead score
     * GET /api/ai/lead-score/{leadId}
     */
    public function getScore(int $leadId): void
    {
        $this->requireAuth(['associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $result = $this->scoringService->calculateScore($leadId);
        
        if ($result['success']) {
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 404);
        }
    }

    /**
     * Get deal probability
     * GET /api/ai/deal-probability/{leadId}
     */
    public function getDealProbability(int $leadId): void
    {
        $this->requireAuth(['associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $result = $this->scoringService->getDealProbability($leadId);
        
        if ($result['success']) {
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 404);
        }
    }

    /**
     * Bulk score multiple leads
     * POST /api/ai/lead-score/bulk
     * Body: { lead_ids: [1,2,3], batch_size?: 50 }
     */
    public function bulkScore(): void
    {
        $this->requireAuth(['associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['lead_ids']) || !is_array($input['lead_ids'])) {
            $this->jsonError('lead_ids array required', 400);
            return;
        }

        $batchSize = isset($input['batch_size']) ? (int)$input['batch_size'] : 50;
        $result = $this->scoringService->bulkScore($input['lead_ids'], $batchSize);
        
        $this->jsonSuccess($result);
    }

    /**
     * Get priority leads needing attention
     * GET /api/ai/lead-score/priority?limit=20&associate_id=1
     */
    public function priorityLeads(): void
    {
        $this->requireAuth(['associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $associateId = isset($_GET['associate_id']) ? (int)$_GET['associate_id'] : null;
        
        // If associate, only show their leads
        if ($associateId === null) {
            // Check if current user is associate
            if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['associate', 'agent'])) {
                $associateId = $_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? null;
            }
        }
        
        $result = $this->scoringService->getPriorityLeads($limit, $associateId);
        
        $this->jsonSuccess($result);
    }

    /**
     * Recalculate score for a lead
     * POST /api/ai/lead-score/{leadId}/recalculate
     */
    public function recalculate(int $leadId): void
    {
        $this->requireAuth(['associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        // Force recalculation by deleting existing score
        $this->db->execute("DELETE FROM lead_scores WHERE lead_id = ?", [$leadId]);
        
        $result = $this->scoringService->calculateScore($leadId);
        
        if ($result['success']) {
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 404);
        }
    }
}