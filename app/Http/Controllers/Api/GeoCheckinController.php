<?php

namespace App\Http\Controllers\Api;

use App\Services\HR\GeoCheckinService;

/**
 * Mobile endpoints for the Geo Check-In system.
 *
 * All endpoints use the Bearer token + API middleware already applied in routes.
 * userId comes from $GLOBALS['api_user_id'] (set by middleware).
 */
class GeoCheckinController extends BaseApiController
{
    private GeoCheckinService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new GeoCheckinService();
    }

    private function apiUserId(): int
    {
        return (int) ($GLOBALS['api_user_id'] ?? 0);
    }

    private function body(): array
    {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        return is_array($json) ? $json : ($_POST ?: []);
    }

    public function zones()
    {
        $userId = $this->apiUserId();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }
        $this->jsonSuccess($this->service->getZones($userId), 'Zones fetched');
    }

    public function checkIn()
    {
        $userId = $this->apiUserId();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }
        $pos = $this->body();
        $result = $this->service->checkIn($userId, [
            'latitude' => $pos['latitude'] ?? null,
            'longitude' => $pos['longitude'] ?? null,
            'accuracy_m' => $pos['accuracy_m'] ?? null,
            'altitude_m' => $pos['altitude_m'] ?? null,
            'speed_kmh' => $pos['speed_kmh'] ?? null,
            'is_mock_gps' => !empty($pos['is_mock_gps']),
            'selfie_base64' => $pos['selfie_base64'] ?? null,
        ]);
        if (empty($result['success'])) {
            $this->jsonError($result['error'] ?? 'Check-in failed', 400);
            return;
        }
        $this->jsonSuccess($result, $result['message'] ?? 'Checked in');
    }

    public function checkOut()
    {
        $userId = $this->apiUserId();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }
        $pos = $this->body();
        $result = $this->service->checkOut($userId, [
            'latitude' => $pos['latitude'] ?? null,
            'longitude' => $pos['longitude'] ?? null,
        ], $pos['work_log'] ?? null);
        if (empty($result['success'])) {
            $this->jsonError($result['error'] ?? 'Check-out failed', 400);
            return;
        }
        $this->jsonSuccess($result);
    }

    public function ping()
    {
        $userId = $this->apiUserId();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }
        $pos = $this->body();
        $result = $this->service->recordPing($userId, [
            'latitude' => $pos['latitude'] ?? null,
            'longitude' => $pos['longitude'] ?? null,
            'accuracy_m' => $pos['accuracy_m'] ?? null,
            'speed_kmh' => $pos['speed_kmh'] ?? null,
            'is_mock_gps' => !empty($pos['is_mock_gps']),
            'source' => $pos['source'] ?? 'field',
        ]);
        if (empty($result['success'])) {
            $this->jsonError($result['error'] ?? 'Ping failed', 400);
            return;
        }
        $this->jsonSuccess($result);
    }

    public function meToday()
    {
        $userId = $this->apiUserId();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }
        $employeeId = $this->service->resolveEmployeeId($userId);
        $report = $this->service->dayReport($employeeId, $_GET['date'] ?? null);
        if (empty($report['success'])) {
            $this->jsonError($report['error'] ?? 'Report failed', 400);
            return;
        }
        $ai = $this->service->aiDailySummary($employeeId, $_GET['date'] ?? null);
        $report['ai_summary'] = $ai['summary'] ?? null;
        $this->jsonSuccess($report);
    }

    public function teamToday()
    {
        $userId = $this->apiUserId();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }
        // Admin / HR / compliance only
        $u = \App\Core\Database\Database::getInstance()->fetchOne('SELECT role FROM users WHERE id = ? LIMIT 1', [$userId]);
        $role = strtolower((string)($u['role'] ?? ''));
        $allowed = ['super_admin','admin','hr_manager','hr_director','operations_manager','coo','director','manager','ceo'];
        if (!in_array($role, $allowed, true)) {
            $this->jsonError('Team visibility requires HR/admin role', 403);
            return;
        }
        $report = $this->service->teamStatusForDate($_GET['date'] ?? null);
        if (empty($report['success'])) {
            $this->jsonError($report['error'] ?? 'Report failed', 400);
            return;
        }
        $this->jsonSuccess($report);
    }
}
