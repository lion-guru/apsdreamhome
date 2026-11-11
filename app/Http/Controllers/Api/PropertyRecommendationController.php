<?php
/**
 * PropertyRecommendationController — REST API endpoints for AI property recommendations
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\PropertyRecommendationService;

class PropertyRecommendationController extends BaseController
{
    private $recommendationService;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->recommendationService = new PropertyRecommendationService();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    protected function skipCsrfProtection(): bool
    {
        return true; // Bearer-token API; CSRF handled by auth
    }

    protected function requireAuth(array $allowedRoles = []): int {
        while (ob_get_level()) {
            ob_end_clean();
        }
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
     * Get property recommendations for a user/lead
     * POST /api/ai/property-recommendations
     * Body: { user_id?: int, lead_id?: int, limit?: int, exclude_booked?: bool }
     */
    public function getRecommendations(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $userId = $input['user_id'] ?? null;
        $leadId = $input['lead_id'] ?? null;
        $limit = isset($input['limit']) ? (int)$input['limit'] : 10;
        $excludeBooked = $input['exclude_booked'] ?? true;

        // If customer, force their own user_id
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
            $userId = $_SESSION['user_id'] ?? null;
        }
        // If associate, they can only see their own leads
        if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['associate', 'agent'])) {
            $leadId = $input['lead_id'] ?? null;
            if ($leadId) {
                // Verify lead belongs to this associate
                $lead = $this->db->fetch("SELECT id FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?)", [$leadId, $_SESSION['user_id'], $_SESSION['user_id']]);
                if (!$lead) {
                    $this->jsonError('Unauthorized', 403);
                    return;
                }
            }
        }

        $result = $this->recommendationService->getRecommendations([
            'user_id' => $userId,
            'lead_id' => $leadId,
            'limit' => $limit,
            'exclude_booked' => $excludeBooked,
        ]);

        if ($result['success']) {
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 500);
        }
    }

    /**
     * Get recommendations for current customer
     * GET /api/ai/property-recommendations/me
     */
    public function getMyRecommendations(): void
    {
        $this->requireAuth(['customer']);
        
        $userId = $_SESSION['user_id'] ?? null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $excludeBooked = !isset($_GET['exclude_booked']) || $_GET['exclude_booked'] !== 'false';

        $result = $this->recommendationService->getRecommendations([
            'user_id' => $userId,
            'limit' => $limit,
            'exclude_booked' => $excludeBooked,
        ]);

        $this->jsonSuccess($result);
    }

    /**
     * Get recommendations for a specific lead (associate/agent)
     * GET /api/ai/property-recommendations/lead/{leadId}
     */
    public function getLeadRecommendations(int $leadId): void
    {
        $this->requireAuth(['associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        // Verify lead belongs to this associate
        $lead = $this->db->fetch("SELECT id FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?)", [$leadId, $_SESSION['user_id'], $_SESSION['user_id']]);
        if (!$lead) {
            $this->jsonError('Unauthorized', 403);
            return;
        }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $excludeBooked = !isset($_GET['exclude_booked']) || $_GET['exclude_booked'] !== 'false';

        $result = $this->recommendationService->getRecommendations([
            'lead_id' => $leadId,
            'limit' => $limit,
            'exclude_booked' => $excludeBooked,
        ]);

        $this->jsonSuccess($result);
    }
}