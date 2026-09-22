<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Services\RetroactiveRecalculationService;

/**
 * Mobile API - Commission Recalculation (Staff only)
 *
 * List, detail, and request recalculations for historical commission entries.
 */
class CommissionRecalculationApiController extends BaseController
{
    use TenantAwareTrait;

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function staffUser(): ?array
    {
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if ($userId <= 0) return null;
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT id, name, role, status FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('CommissionRecalculationApiController::staffUser: ' . $e->getMessage());
            return null;
        }
        if (!$user || ($user['status'] ?? '') !== 'active') return null;
        if (!in_array(($user['role'] ?? ''), self::ADMIN_ROLES, true)) return null;
        return $user;
    }

    /**
     * GET /api/v2/mobile/commission-recalculations
     * List recalculation requests with pagination
     */
    public function index()
    {
        try {
            if (!$this->staffUser()) {
                return $this->jsonError('Unauthorized', 401);
            }
            $service = new RetroactiveRecalculationService();
            $page = max(1, (int)($_GET['page'] ?? 1));
            $status = $_GET['status'] ?? '';
            $type = $_GET['type'] ?? '';

            $result = $service->getRequests($status, $page, 20);
            $this->jsonResponse([
                'success' => true,
                'data' => $result['items'],
                'stats' => $service->getStats(),
                'pagination' => ['total' => $result['total'], 'page' => $result['page'], 'total_pages' => $result['total_pages']],
            ]);
        } catch (\Throwable $e) {
            error_log('CommissionRecalculationApiController::index: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }

    /**
     * GET /api/v2/mobile/commission-recalculations/{id}
     */
    public function detail($id)
    {
        try {
            if (!$this->staffUser()) {
                return $this->jsonError('Unauthorized', 401);
            }
            $service = new RetroactiveRecalculationService();
            $recalc = $service->getRequest((int)$id);
            if (!$recalc) {
                return $this->jsonError('Request not found', 404);
            }
            $this->jsonResponse(['success' => true, 'data' => $recalc]);
        } catch (\Throwable $e) {
            error_log('CommissionRecalculationApiController::detail: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }

    /**
     * POST /api/v2/mobile/commission-recalculations/request
     * Request recalculation for a single ledger entry
     */
    public function request()
    {
        try {
            if (!$this->staffUser()) {
                return $this->jsonError('Unauthorized', 401);
            }
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $ledgerId = (int)($input['ledger_id'] ?? 0);
            $reason = trim((string)($input['reason'] ?? ''));

            if (!$ledgerId) {
                return $this->jsonError('ledger_id is required', 400);
            }
            if ($reason === '') {
                return $this->jsonError('reason is required', 400);
            }

            $service = new RetroactiveRecalculationService();
            $result = $service->requestRecalculation($ledgerId, $reason);
            $this->jsonResponse($result);
        } catch (\Throwable $e) {
            error_log('CommissionRecalculationApiController::request: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }

    /**
     * POST /api/v2/mobile/commission-recalculations/bulk-request
     * Bulk request recalculation for a commission type within date range
     */
    public function bulkRequest()
    {
        try {
            if (!$this->staffUser()) {
                return $this->jsonError('Unauthorized', 401);
            }
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $type = trim((string)($input['type'] ?? ''));
            $from = trim((string)($input['from'] ?? ''));
            $to = trim((string)($input['to'] ?? ''));
            $reason = trim((string)($input['reason'] ?? ''));

            if (!$type || !$from || !$to) {
                return $this->jsonError('type, from, to are required', 400);
            }
            if ($reason === '') {
                return $this->jsonError('reason is required', 400);
            }

            $service = new RetroactiveRecalculationService();
            $result = $service->bulkRequest($type, $from, $to, $reason);
            $this->jsonResponse($result);
        } catch (\Throwable $e) {
            error_log('CommissionRecalculationApiController::bulkRequest: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }
}