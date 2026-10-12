<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Services\IoTService;

/**
 * Field-device telemetry ingest (Session 222).
 * Auth = per-device `device_uid` (unique column on iot_devices) — gateways and
 * solar controllers have no admin session. Lives under /api/ (CSRF-exempt).
 * Readings flow through IoTService::recordReading so the alert hook fires too.
 */
class IotIngestController
{
    public function ingest()
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($data)) {
            $post = $_POST;
            $data = $post ?: null;
        }
        if (!is_array($data)) {
            ApiResponse::fail('JSON or form body required', 400);
            return;
        }

        $uid = trim((string)($data['device_uid'] ?? ''));
        if ($uid === '') {
            ApiResponse::fail('device_uid is required', 400);
            return;
        }

        $pdo = \App\Core\Database\Database::getInstance()->getConnection();
        $st = $pdo->prepare("SELECT id, status FROM iot_devices WHERE device_uid = ? LIMIT 1");
        $st->execute([$uid]);
        $device = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$device) {
            ApiResponse::fail('Unknown device_uid', 401);
            return;
        }
        if (($device['status'] ?? '') === 'inactive') {
            ApiResponse::fail('Device is deactivated', 403);
            return;
        }

        // Optional status flip from the device (online/offline/fault enum).
        $status = $data['status'] ?? null;
        if (in_array($status, ['online', 'offline', 'fault', 'configuring'], true)) {
            try {
                $pdo->prepare("UPDATE iot_devices SET status = ?, updated_at = NOW() WHERE id = ?")
                    ->execute([$status, (int)$device['id']]);
            } catch (\Throwable $e) {
                error_log('[IotIngestController status] ' . $e->getMessage());
            }
        }

        $service = new IoTService();
        $readings = $data['readings'] ?? [];
        if (!is_array($readings) || !$readings) {
            ApiResponse::fail('readings[] array required (metric, value, unit?)', 400);
            return;
        }

        $stored = [];
        foreach ($readings as $r) {
            if (!is_array($r)) {
                continue;
            }
            $metric = trim((string)($r['metric'] ?? ''));
            if ($metric === '' || !is_numeric($r['value'] ?? null)) {
                continue;
            }
            $readingId = $service->recordReading(
                (int)$device['id'],
                substr($metric, 0, 60),
                (float)$r['value'],
                isset($r['unit']) ? substr((string)$r['unit'], 0, 20) : null,
                !empty($r['recorded_at']) ? substr((string)$r['recorded_at'], 0, 19) : null
            );
            $stored[] = ['id' => $readingId, 'metric' => $metric, 'value' => (float)$r['value']];
        }

        if (!$stored) {
            ApiResponse::fail('No valid readings (each needs metric + numeric value)', 400);
            return;
        }

        $openAlerts = 0;
        try {
            $st = $pdo->prepare("SELECT COUNT(*) FROM iot_alerts WHERE device_id = ? AND is_resolved = 0");
            $st->execute([(int)$device['id']]);
            $openAlerts = (int)$st->fetchColumn();
        } catch (\Throwable $ignore) {
        }

        ApiResponse::ok([
            'device_id' => (int)$device['id'],
            'stored' => $stored,
            'open_alerts' => $openAlerts,
        ], count($stored) . ' reading(s) stored');
    }
}
