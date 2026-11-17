<?php

namespace App\Http\Controllers\Api;

use PDO;
use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\HR\GeoCheckinService;
use App\Traits\TenantAwareTrait;

class MobileWorkZonesController extends BaseController
{
    use TenantAwareTrait;

    private GeoCheckinService $service;

    private const WORK_ZONE_ADMIN_ROLES = ['super_admin', 'admin', 'manager', 'hr_manager', 'hr_director', 'operations_manager', 'coo', 'director', 'ceo'];

    public function __construct()
    {
        parent::__construct();
        $this->service = new GeoCheckinService();
    }

    protected function skipCsrfProtection(): bool
    {
        // Mobile API uses Bearer token auth (ApiAuthMiddleware), not CSRF tokens
        return true;
    }

    /** @return array<string, mixed>|null */
    private function apiUser(): ?array
    {
        $userId = (int) ($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            return null;
        }
        try {
            $stmt = $this->db->prepare('SELECT id, role FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('MobileWorkZonesController apiUser error: ' . $e->getMessage());
            return null;
        }
    }

    private function requireAdminUser(): ?array
    {
        $user = $this->apiUser();
        if (!$user) {
            $this->jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
            return null;
        }
        $role = (string) ($user['role'] ?? 'associate');
        if (!in_array($role, self::WORK_ZONE_ADMIN_ROLES, true)) {
            $this->jsonResponse(['success' => false, 'error' => 'This desk is for admin/HR roles only'], 403);
            return null;
        }
        return $user;
    }

    public function index()
    {
        $this->setCorsHeaders();
        if (!$this->requireAdminUser()) {
            return;
        }
        $this->jsonResponse(['success' => true, 'data' => $this->service->listAllZones()]);
    }

    public function store()
    {
        $this->setCorsHeaders();
        if (!$this->requireAdminUser()) {
            return;
        }

        $body = $this->body();
        $res = $this->service->createZone($body);
        if (empty($res['success'])) {
            $this->jsonResponse(['success' => false, 'error' => $res['error'] ?? 'Failed to add zone'], 422);
            return;
        }
        $this->jsonResponse(['success' => true, 'id' => $res['id'] ?? null, 'message' => 'Zone added']);
    }

    public function update($id = 0)
    {
        $this->setCorsHeaders();
        if (!$this->requireAdminUser()) {
            return;
        }

        $zoneId = (int) $id;
        if ($zoneId <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid zone id'], 400);
            return;
        }

        $body = $this->body();
        $res = $this->service->updateZone($zoneId, $body);
        if (empty($res['success'])) {
            $status = (($res['error'] ?? '') === 'Zone not found') ? 404 : 422;
            $this->jsonResponse(['success' => false, 'error' => $res['error'] ?? 'Failed to update zone'], $status);
            return;
        }
        $this->jsonResponse(['success' => true, 'message' => 'Zone updated']);
    }

    public function toggle($id = 0)
    {
        $this->setCorsHeaders();
        if (!$this->requireAdminUser()) {
            return;
        }

        $zoneId = (int) $id;
        $body = $this->body();
        $active = !empty($body['active']);

        $res = $this->service->setZoneActive($zoneId, $active);
        if (empty($res['success'])) {
            $this->jsonResponse(['success' => false, 'error' => $res['error'] ?? 'Failed to change zone state'], 400);
            return;
        }
        $this->jsonResponse(['success' => true, 'message' => $active ? 'Zone enabled' : 'Zone disabled']);
    }

    /** @return array<string, mixed> */
    private function body(): array
    {
        $raw = file_get_contents('php://input');
        $body = json_decode((string) $raw, true);
        if (!is_array($body)) {
            $body = $_POST;
        }
        return $body;
    }
}
