<?php

namespace App\Services;

/**
 * IoT threshold alert engine (Session 222) — reads from the EXISTING
 * iot_readings/iot_devices stack; does not duplicate device/reading CRUD.
 *
 * Triggers:
 *  - battery_low : battery-ish metric below threshold (warning <25%, critical <10%)
 *  - fault       : fault_flag / system_fault reading >= 1 (critical)
 *  - barrier_fault: barrier_fault reading >= 1 (critical)
 *  - device_offline : active device silent beyond offline_minutes (scanOffline)
 * Dedupe: one open alert per (device, type) — repeat triggers do not spam.
 */
class IoTAlertService
{
    private \PDO $pdo;

    public const OFFLINE_MINUTES = 30;
    public const BATTERY_WARN_PCT = 25.0;
    public const BATTERY_CRIT_PCT = 10.0;

    public function __construct()
    {
        $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
    }

    /**
     * Evaluate a fresh reading against thresholds. Best-effort: never throws
     * (callers are mid-write). Returns created alert id or null.
     */
    public function evaluateReading(int $deviceId, string $metric, float $value): ?int
    {
        $m = strtolower($metric);
        if (in_array($m, ['battery_pct', 'battery', 'soc_pct', 'battery_so_c'], true)) {
            if ($value < self::BATTERY_CRIT_PCT) {
                return $this->raise($deviceId, 'battery_low', 'critical', sprintf('Battery critical at %.1f%%', $value));
            }
            if ($value < self::BATTERY_WARN_PCT) {
                return $this->raise($deviceId, 'battery_low', 'warning', sprintf('Battery low at %.1f%% (threshold %.0f%%)', $value, self::BATTERY_WARN_PCT));
            }
            return null;
        }
        if (in_array($m, ['fault_flag', 'system_fault'], true) && $value >= 1) {
            return $this->raise($deviceId, 'fault', 'critical', 'Device reported fault_flag=1');
        }
        if ($m === 'barrier_fault' && $value >= 1) {
            return $this->raise($deviceId, 'barrier_fault', 'critical', 'Boom barrier reported fault');
        }
        return null;
    }

    /** Create device_offline alerts for active devices silent past the window. */
    public function scanOffline(): int
    {
        try {
            $st = $this->pdo->query(
                "SELECT id FROM iot_devices
                 WHERE status IN ('online','configuring')
                   AND (last_seen_at IS NULL OR last_seen_at < NOW() - INTERVAL " . (int)self::OFFLINE_MINUTES . " MINUTE)"
            );
            $created = 0;
            foreach ($st->fetchAll(\PDO::FETCH_COLUMN) as $deviceId) {
                if ($this->raise((int)$deviceId, 'device_offline', 'warning', 'No telemetry for ' . self::OFFLINE_MINUTES . '+ minutes') !== null) {
                    $created++;
                }
            }
            return $created;
        } catch (\Throwable $e) {
            error_log('[IoTAlertService::scanOffline] ' . $e->getMessage());
            return 0;
        }
    }

    /** Dedupe'd alert insert. Returns id when raised, null when suppressed. */
    private function raise(int $deviceId, string $type, string $severity, string $message): ?int
    {
        try {
            $dev = $this->pdo->prepare("SELECT colony_id FROM iot_devices WHERE id = ? LIMIT 1");
            $dev->execute([$deviceId]);
            $row = $dev->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            $dup = $this->pdo->prepare(
                "SELECT id FROM iot_alerts WHERE device_id = ? AND alert_type = ? AND is_resolved = 0 LIMIT 1"
            );
            $dup->execute([$deviceId, $type]);
            if ($dup->fetchColumn()) {
                return null;
            }
            $ins = $this->pdo->prepare(
                "INSERT INTO iot_alerts (tenant_id, device_id, colony_id, alert_type, severity, message)
                 VALUES (1, ?, ?, ?, ?, ?)"
            );
            $ins->execute([$deviceId, $row['colony_id'] ?? 0, $type, $severity, substr($message, 0, 255)]);
            return (int)$this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[IoTAlertService::raise] ' . $e->getMessage());
            return null;
        }
    }

    public function listAlerts(array $f = []): array
    {
        $sql = "SELECT a.*, d.name AS device_name, d.device_uid, d.category
                FROM iot_alerts a
                LEFT JOIN iot_devices d ON d.id = a.device_id
                WHERE 1=1";
        $params = [];
        if (!empty($f['resolved'])) {
            $sql .= " AND a.is_resolved = 1";
        } else {
            $sql .= " AND a.is_resolved = 0";
        }
        if (!empty($f['colony_id'])) {
            $sql .= " AND a.colony_id = ?";
            $params[] = (int)$f['colony_id'];
        }
        $sql .= " ORDER BY FIELD(a.severity,'critical','warning','info'), a.created_at DESC LIMIT 100";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function resolveAlert(int $id, int $userId): bool
    {
        $st = $this->pdo->prepare(
            "UPDATE iot_alerts SET is_resolved = 1, resolved_by = ?, resolved_at = NOW() WHERE id = ? AND is_resolved = 0"
        );
        $st->execute([$userId, $id]);
        return $st->rowCount() > 0;
    }

    public function stats(): array
    {
        try {
            $r = $this->pdo->query(
                "SELECT
                    SUM(is_resolved = 0) AS open_total,
                    SUM(is_resolved = 0 AND severity = 'critical') AS open_critical,
                    SUM(is_resolved = 1) AS resolved_total
                 FROM iot_alerts"
            )->fetch(\PDO::FETCH_ASSOC);
            return [
                'open' => (int)($r['open_total'] ?? 0),
                'critical' => (int)($r['open_critical'] ?? 0),
                'resolved' => (int)($r['resolved_total'] ?? 0),
            ];
        } catch (\Throwable $e) {
            return ['open' => 0, 'critical' => 0, 'resolved' => 0];
        }
    }
}
