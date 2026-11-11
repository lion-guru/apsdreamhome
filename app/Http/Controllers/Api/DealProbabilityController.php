<?php
/**
 * DealProbabilityController — REST API endpoints for AI deal probability
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\DealProbabilityService;

class DealProbabilityController extends BaseController
{
    private $dealProbabilityService;

    public function __construct()
    {
        parent::__construct();
        $this->dealProbabilityService = new DealProbabilityService();
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
     * Calculate deal probability for a booking
     * POST /api/ai/deal-probability
     * Body: { booking_id: int }
     */
    public function calculateProbability(): void
    {
        $this->requireAuth(['associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['booking_id'])) {
            $this->jsonError('booking_id required', 400);
            return;
        }

        $bookingId = (int)$input['booking_id'];
        $result = $this->dealProbabilityService->calculateProbability($bookingId);
        
        if ($result['success']) {
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 404);
        }
    }

    /**
     * Bulk calculate deal probabilities
     * POST /api/ai/deal-probability/bulk
     * Body: { booking_ids: [1,2,3] }
     */
    public function bulkCalculate(): void
    {
        $this->requireAuth(['associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['booking_ids']) || !is_array($input['booking_ids'])) {
            $this->jsonError('booking_ids array required', 400);
            return;
        }

        $result = $this->dealProbabilityService->bulkCalculate($input['booking_ids']);
        
        $this->jsonSuccess($result);
    }
}