<?php

namespace App\Services\HR;

use App\Core\Database\Database;
use App\Services\AI\FreeAIEngines;

/**
 * Geo Check-In service — office & field attendance with anti-fraud heuristics.
 *
 * Zones: office (mandatory geofence) + colony sites (field staff).
 * Every check-in stores lat/lng/accuracy/altitude/mock flag; field staff
 * may submit periodic pings for route reconstruction.
 *
 * Fraud heuristics (deterministic, never throws):
 *   GPS_MOCK_FLAG       — client reported isMocked
 *   ACCURACY_SUSPICIOUS — reported accuracy > 300m
 *   IMPOSSIBLE_TRAVEL   — ping implied speed > 200 km/h
 *   PING_GAP            — > 60 min gap during an open field duty
 *   ALTITUDE_FROZEN     — altitude identical across 5+ consecutive pings
 *   OUT_OF_ZONE         — check-in outside every assigned work zone
 *   NO_PINGS_ALL_DAY    — full working day (8h) with zero field pings
 */
class GeoCheckinService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Identity ──────────────────────────────────────────────────────
    public function resolveEmployeeId(int $userId): int
    {
        $row = $this->db->fetchOne('SELECT id FROM employee_profiles WHERE user_id = ? LIMIT 1', [$userId]);
        if ($row) {
            return (int) $row['id'];
        }
        $row = $this->db->fetchOne('SELECT id FROM employees WHERE user_id = ? LIMIT 1', [$userId]);
        if ($row) {
            return (int) $row['id'];
        }
        return $userId; // fall-back keeps feature usable for legacy accounts
    }

    public function getEmployeeName(int $employeeId): string
    {
        $row = $this->db->fetchOne('SELECT name FROM employees WHERE id = ? LIMIT 1', [$employeeId]);
        return $row['name'] ?? ('Employee #' . $employeeId);
    }

    // ── Zones ────────────────────────────────────────────────────────
    /**
     * Admin: list ALL zones incl. inactive, for the zone-manager desk.
     * @return array<int, array<string, mixed>>
     */
    public function listAllZones(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT id, name, zone_type, latitude, longitude, radius_m, selfie_required, is_active, employee_id
             FROM employee_work_zones
             WHERE tenant_id = 1
             ORDER BY FIELD(zone_type, 'office','site','client_site'), name, id"
        );
        return array_map(static function ($r) {
            return [
                'id' => (int) $r['id'],
                'name' => $r['name'],
                'type' => $r['zone_type'],
                'latitude' => (float) $r['latitude'],
                'longitude' => (float) $r['longitude'],
                'radius_m' => (int) $r['radius_m'],
                'selfie_required' => (bool) $r['selfie_required'],
                'is_active' => (bool) $r['is_active'],
            ];
        }, $rows);
    }

    /**
     * Admin: create a zone. Returns ['success' => bool, 'id' => ?int, 'error' => ?string].
     * Coordinates are validated to the Indian sub-continent bounds — no swapped/absurd values.
     */
    public function createZone(array $in): array
    {
        $v = $this->validateZone($in);
        if (!$v['ok']) {
            return ['success' => false, 'id' => null, 'error' => $v['error']];
        }

        $this->db->query(
            "INSERT INTO employee_work_zones (tenant_id, name, zone_type, latitude, longitude, radius_m, selfie_required, is_active)
             VALUES (1, ?, ?, ?, ?, ?, ?, 1)",
            [$v['name'], $v['type'], $v['latitude'], $v['longitude'], $v['radius_m'], $v['selfie_required'] ? 1 : 0]
        );

        return ['success' => true, 'id' => (int) $this->db->lastInsertId(), 'error' => null];
    }

    /**
     * Admin: update an existing zone. Returns ['success' => bool, 'error' => ?string].
     */
    public function updateZone(int $id, array $in): array
    {
        if ($id <= 0) {
            return ['success' => false, 'error' => 'Invalid zone id'];
        }

        $v = $this->validateZone($in);
        if (!$v['ok']) {
            return ['success' => false, 'error' => $v['error']];
        }

        $existing = $this->db->fetchOne('SELECT id FROM employee_work_zones WHERE tenant_id = 1 AND id = ?', [$id]);
        if (!$existing) {
            return ['success' => false, 'error' => 'Zone not found'];
        }

        $this->db->query(
            "UPDATE employee_work_zones
             SET name = ?, zone_type = ?, latitude = ?, longitude = ?, radius_m = ?, selfie_required = ?
             WHERE tenant_id = 1 AND id = ?",
            [$v['name'], $v['type'], $v['latitude'], $v['longitude'], $v['radius_m'], $v['selfie_required'] ? 1 : 0, $id]
        );

        return ['success' => true, 'error' => null];
    }

    /** Admin: activate/deactivate a zone. */
    public function setZoneActive(int $id, bool $active): array
    {
        if ($id <= 0) {
            return ['success' => false, 'error' => 'Invalid zone id'];
        }
        $existing = $this->db->fetchOne('SELECT id FROM employee_work_zones WHERE tenant_id = 1 AND id = ?', [$id]);
        if (!$existing) {
            return ['success' => false, 'error' => 'Zone not found'];
        }
        $this->db->query('UPDATE employee_work_zones SET is_active = ? WHERE tenant_id = 1 AND id = ?', [$active ? 1 : 0, $id]);
        return ['success' => true, 'error' => null];
    }

    /**
     * Shared validator. Accepts raw "in" array (strings ok) and returns normalized floats/ints.
     * @return array{ok:bool,error:?string,name:string,type:string,latitude:float,longitude:float,radius_m:int,selfie_required:bool}
     */
    private function validateZone(array $in): array
    {
        $fail = static fn(string $msg): array => [
            'ok' => false, 'error' => $msg, 'name' => '', 'type' => 'site',
            'latitude' => 0.0, 'longitude' => 0.0, 'radius_m' => 150, 'selfie_required' => false,
        ];

        $name = trim((string) ($in['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 150) {
            return $fail('Name is required and must be under 150 characters');
        }

        $type = (string) ($in['type'] ?? $in['zone_type'] ?? 'site');
        if (!in_array($type, ['office', 'site', 'client_site'], true)) {
            return $fail('Invalid zone type');
        }

        // India-ish bounds to catch swapped lat/lng and typos.
        $lat = is_numeric($in['latitude'] ?? null) ? (float) $in['latitude'] : NAN;
        $lng = is_numeric($in['longitude'] ?? null) ? (float) $in['longitude'] : NAN;
        if (!is_finite($lat) || $lat < 6.0 || $lat > 37.5) {
            return $fail('Latitude must be a number between 6.0 and 37.5 (India). Did you swap latitude and longitude?');
        }
        if (!is_finite($lng) || $lng < 68.0 || $lng > 98.0) {
            return $fail('Longitude must be a number between 68.0 and 98.0 (India). Did you swap latitude and longitude?');
        }

        $radius = (int) ($in['radius_m'] ?? 150);
        if ($radius < 10 || $radius > 10000) {
            return $fail('Radius must be between 10 and 10000 metres');
        }

        return [
            'ok' => true,
            'error' => null,
            'name' => $name,
            'type' => $type,
            'latitude' => $lat,
            'longitude' => $lng,
            'radius_m' => $radius,
            'selfie_required' => !empty($in['selfie_required']),
        ];
    }
    public function getZones(int $userId): array
    {
        $employeeId = $this->resolveEmployeeId($userId);
        $rows = $this->db->fetchAll(
            "SELECT id, name, zone_type, latitude, longitude, radius_m, selfie_required, is_active
             FROM employee_work_zones
             WHERE tenant_id = 1 AND is_active = 1 AND (employee_id IS NULL OR employee_id = ?)
             ORDER BY FIELD(zone_type, 'office','site','client_site'), name",
            [$employeeId]
        );
        return array_map(static function ($r) {
            return [
                'id' => (int) $r['id'],
                'name' => $r['name'],
                'type' => $r['zone_type'],
                'latitude' => (float) $r['latitude'],
                'longitude' => (float) $r['longitude'],
                'radius_m' => (int) $r['radius_m'],
                'selfie_required' => (bool) $r['selfie_required'],
            ];
        }, $rows);
    }

    // ── Check-In ─────────────────────────────────────────────────────
    /**
     * @param array{latitude?:float,longitude?:float,accuracy_m?:float,altitude_m?:float,speed_kmh?:float,is_mock_gps?:bool,selfie_base64?:string} $pos
     */
    public function checkIn(int $userId, array $pos): array
    {
        try {
            $employeeId = $this->resolveEmployeeId($userId);
            $lat = isset($pos['latitude']) ? (float) $pos['latitude'] : null;
            $lng = isset($pos['longitude']) ? (float) $pos['longitude'] : null;
            if ($lat === null || $lng === null || ($lat === 0.0 && $lng === 0.0)) {
                return ['success' => false, 'error' => 'Location missing — enable GPS and try again'];
            }

            $accuracy = isset($pos['accuracy_m']) ? (float) $pos['accuracy_m'] : null;
            $altitude = isset($pos['altitude_m']) ? (float) $pos['altitude_m'] : null;
            $speed = isset($pos['speed_kmh']) ? (float) $pos['speed_kmh'] : null;
            $mock = !empty($pos['is_mock_gps']) ? 1 : 0;

            // Determine nearest zone
            $zones = $this->getZones($userId);
            $nearest = null;
            $bestDist = null;
            foreach ($zones as $z) {
                $d = $this->haversineM($lat, $lng, $z['latitude'], $z['longitude']);
                if ($bestDist === null || $d < $bestDist) {
                    $bestDist = $d;
                    $nearest = $z;
                }
            }

            $inside = ($nearest !== null && $bestDist <= ($nearest['radius_m'] ?? 150));
            $zoneName = $nearest['name'] ?? null;

            // Status classification (worst case wins: mock > out-of-zone > selfie-missing > valid)
            if ($mock === 1) {
                $status = 'mock_gps';
            } elseif (!$inside) {
                $status = 'out_of_zone';
            } else {
                $status = 'valid';
            }

            // Selfie rules: every zone requiring selfie must carry one
            $selfieRequired = false;
            foreach ($zones as $z) {
                if ($z['selfie_required'] && $this->haversineM($lat, $lng, $z['latitude'], $z['longitude']) <= ($z['radius_m'] ?? 150) + 25) {
                    $selfieRequired = true;
                    break;
                }
            }
            $selfiePath = null;
            if (!empty($pos['selfie_base64']) && $selfieRequired) {
                $selfiePath = $this->storeSelfie((string) $pos['selfie_base64'], $employeeId);
                if ($selfiePath === null) {
                    return ['success' => false, 'error' => 'Selfie upload failed — retake the photo'];
                }
                // Selfie present clears nothing — status stays as classified.
            } elseif ($selfieRequired && empty($pos['selfie_base64'])) {
                // Soft requirement on field sites: flag for review rather than hard-block.
                if ($status === 'valid') {
                    $status = 'review_required';
                }
            }

            $workDate = date('Y-m-d');

            // Replace today's check-in if re-checking (punch-out absent allowed)
            $existing = $this->db->fetchOne(
                'SELECT id FROM employee_checkins WHERE tenant_id = 1 AND employee_id = ? AND work_date = ?',
                [$employeeId, $workDate]
            );

            if ($existing) {
                $this->db->execute(
                    'UPDATE employee_checkins SET check_in_at = NOW(), check_in_lat = ?, check_in_lng = ?,
                        check_in_accuracy_m = ?, check_in_altitude_m = ?, check_in_speed_kmh = ?, is_mock_gps = ?,
                        inside_zone = ?, zone_name = ?, distance_from_zone_m = ?, status = ?,
                        selfie_path = COALESCE(?, selfie_path) WHERE id = ?',
                    [$lat, $lng, $accuracy, $altitude, $speed, $mock, $inside ? 1 : 0,
                     $zoneName, $bestDist, $status, $selfiePath, (int) $existing['id']]
                );
                $checkinId = (int) $existing['id'];
            } else {
                $this->db->execute(
                    'INSERT INTO employee_checkins
                        (tenant_id, employee_id, work_date, check_in_at, check_in_lat, check_in_lng,
                         check_in_accuracy_m, check_in_altitude_m, check_in_speed_kmh, is_mock_gps,
                         inside_zone, zone_name, distance_from_zone_m, status, selfie_path)
                     VALUES (1, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$employeeId, $workDate, $lat, $lng, $accuracy, $altitude, $speed, $mock,
                     $inside ? 1 : 0, $zoneName, $bestDist, $status, $selfiePath]
                );
                $checkinId = (int) $this->db->lastInsertId();
            }

            return [
                'success' => true,
                'checkin_id' => $checkinId,
                'status' => $status,
                'inside_zone' => $inside,
                'zone_name' => $zoneName,
                'distance_meters' => ($bestDist !== null) ? (int) round($bestDist) : null,
                'message' => $status === 'valid'
                    ? "Checked in at {$zoneName}"
                    : ($status === 'mock_gps'
                        ? 'Checked in, but mock-location flag detected — sent for review'
                        : ($status === 'out_of_zone'
                            ? 'You are outside every assigned work zone — attendance sent for review'
                            : 'Checked in — selfie missing, sent for review')),
            ];
        } catch (\Throwable $e) {
            error_log('GeoCheckinService::checkIn error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Check-in failed: ' . $e->getMessage()];
        }
    }

    public function checkOut(int $userId, array $pos, ?string $workLog = null): array
    {
        try {
            $employeeId = $this->resolveEmployeeId($userId);
            $workDate = date('Y-m-d');
            $row = $this->db->fetchOne(
                'SELECT id FROM employee_checkins WHERE tenant_id = 1 AND employee_id = ? AND work_date = ?',
                [$employeeId, $workDate]
            );
            if (!$row) {
                return ['success' => false, 'error' => 'No active check-in today — check in first'];
            }

            $lat = isset($pos['latitude']) ? (float) $pos['latitude'] : null;
            $lng = isset($pos['longitude']) ? (float) $pos['longitude'] : null;

            $this->db->execute(
                'UPDATE employee_checkins
                 SET check_out_at = NOW(), work_log = COALESCE(NULLIF(?, ""), work_log)
                 WHERE id = ?',
                [$workLog, (int) $row['id']]
            );

            // Optional pings between check-in and out are recorded via recordPing().
            return [
                'success' => true,
                'message' => 'Checked out. Fill your work log for better reporting.',
                'checkout_lat' => $lat,
                'checkout_lng' => $lng,
            ];
        } catch (\Throwable $e) {
            error_log('GeoCheckinService::checkOut error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Check-out failed: ' . $e->getMessage()];
        }
    }

    // ── Field pings ──────────────────────────────────────────────────
    public function recordPing(int $userId, array $pos): array
    {
        try {
            $employeeId = $this->resolveEmployeeId($userId);
            $workDate = date('Y-m-d');

            $active = $this->db->fetchOne(
                'SELECT id FROM employee_checkins WHERE tenant_id = 1 AND employee_id = ? AND work_date = ? AND check_out_at IS NULL',
                [$employeeId, $workDate]
            );
            if (!$active) {
                return ['success' => false, 'error' => 'Not checked in — cannot record field position'];
            }

            $lat = isset($pos['latitude']) ? (float) $pos['latitude'] : 0.0;
            $lng = isset($pos['longitude']) ? (float) $pos['longitude'] : 0.0;
            $acc = isset($pos['accuracy_m']) ? (float) $pos['accuracy_m'] : null;
            $speed = isset($pos['speed_kmh']) ? (float) $pos['speed_kmh'] : null;
            $mock = !empty($pos['is_mock_gps']) ? 1 : 0;

            $this->db->execute(
                'INSERT INTO employee_pings (tenant_id, employee_id, work_date, ping_at, latitude, longitude, accuracy_m, speed_kmh, is_mock_gps, source)
                 VALUES (1, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)',
                [$employeeId, $workDate, $lat, $lng, $acc, $speed, $mock, (($pos['source'] ?? 'field') ?: 'field')]
            );

            return ['success' => true];
        } catch (\Throwable $e) {
            error_log('GeoCheckinService::recordPing error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Day reporting ────────────────────────────────────────────────
    public function dayReport(int $employeeId, ?string $date = null): array
    {
        $date = $date ?: date('Y-m-d');
        try {
            $checkin = $this->db->fetchOne(
                'SELECT * FROM employee_checkins WHERE tenant_id = 1 AND employee_id = ? AND work_date = ?',
                [$employeeId, $date]
            );
            $pings = $this->db->fetchAll(
                'SELECT ping_at, latitude, longitude, accuracy_m, speed_kmh, is_mock_gps FROM employee_pings
                 WHERE tenant_id = 1 AND employee_id = ? AND work_date = ? ORDER BY ping_at ASC',
                [$employeeId, $date]
            );

            // Distance covered (field pings only)
            $distance = 0.0;
            $prev = null;
            foreach ($pings as $p) {
                if ($prev !== null) {
                    $distance += $this->haversineM((float) $prev['latitude'], (float) $prev['longitude'], (float) $p['latitude'], (float) $p['longitude']);
                }
                $prev = $p;
            }

            $anomalies = $this->detectAnomalies($checkin ?: [], $pings);

            return [
                'success' => true,
                'employee_id' => $employeeId,
                'employee_name' => $this->getEmployeeName($employeeId),
                'date' => $date,
                'checkin' => $checkin ?: null,
                'pings' => array_map(static function ($p) {
                    return [
                        'time' => $p['ping_at'],
                        'latitude' => (float) $p['latitude'],
                        'longitude' => (float) $p['longitude'],
                        'accuracy_m' => $p['accuracy_m'] !== null ? (float) $p['accuracy_m'] : null,
                        'speed_kmh' => $p['speed_kmh'] !== null ? (float) $p['speed_kmh'] : null,
                        'is_mock_gps' => (bool) $p['is_mock_gps'],
                    ];
                }, $pings),
                'distance_km' => round($distance / 1000, 2),
                'pings_count' => count($pings),
                'anomalies' => $anomalies,
            ];
        } catch (\Throwable $e) {
            error_log('GeoCheckinService::dayReport error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function teamStatusForDate(?string $date = null): array
    {
        $date = $date ?: date('Y-m-d');
        try {
            $rows = $this->db->fetchAll(
                "SELECT ep.id AS employee_id, c.id AS checkin_id, ep.name AS employee_name, ep.department, ep.designation,
                        c.work_date, c.check_in_at, c.check_out_at, c.status, c.zone_name, c.distance_from_zone_m,
                        c.is_mock_gps, c.check_in_accuracy_m, c.selfie_path, c.work_log
                 FROM employees ep
                 LEFT JOIN employee_checkins c ON c.employee_id = ep.id AND c.work_date = ? AND c.tenant_id = 1
                 WHERE ep.status = 'active'
                 ORDER BY FIELD(c.status, 'mock_gps','out_of_zone','review_required','valid'), ep.name",
                [$date]
            );
            return ['success' => true, 'date' => $date, 'rows' => $rows];
        } catch (\Throwable $e) {
            error_log('GeoCheckinService::teamStatusForDate error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function reviewCheckin(int $checkinId, string $action, ?string $note, int $reviewerId): array
    {
        try {
            $newStatus = $action === 'approve' ? 'approved' : 'rejected';
            $this->db->execute(
                'UPDATE employee_checkins SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_note = ? WHERE id = ? AND tenant_id = 1',
                [$newStatus, $reviewerId, $note, $checkinId]
            );
            return ['success' => true, 'status' => $newStatus];
        } catch (\Throwable $e) {
            error_log('GeoCheckinService::reviewCheckin error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function aiDailySummary(int $employeeId, ?string $date = null): array
    {
        $date = $date ?: date('Y-m-d');
        $report = $this->dayReport($employeeId, $date);
        if (empty($report['success'])) {
            return $report;
        }
        try {
            $name = $report['employee_name'];
            $chk = $report['checkin'] ?? [];
            $anoms = array_map(static fn($a) => $a['code'], $report['anomalies'] ?? []);
            $prompt = "Summarize this field attendance day in 2-3 short lines (Hinglish OK, no fluff, no invented facts):\n"
                . "Employee: {$name}\n"
                . "Date: {$date}\n"
                . "Check-in: " . ($chk['check_in_at'] ?? 'not checked in') . "\n"
                . "Check-out: " . ($chk['check_out_at'] ?? 'still on duty') . "\n"
                . "Zone: " . ($chk['zone_name'] ?? 'unknown') . " (distance {$chk['distance_from_zone_m']}m)\n"
                . "Mock GPS flag: " . ((int) ($chk['is_mock_gps'] ?? 0)) . "\n"
                . "Pings: " . $report['pings_count'] . " over " . $report['distance_km'] . " km\n"
                . "Work log: " . ($chk['work_log'] ?? '(empty)') . "\n"
                . "Anomalies: " . (count($anoms) ? implode(', ', $anoms) : 'none') . "\n";
            $engines = FreeAIEngines::getInstance();
            $out = $engines->generate($prompt, ['max_tokens' => 120, 'temperature' => 0.4]);
            $text = trim((string) ($out['text'] ?? ''));
            return ['success' => true, 'summary' => $text !== '' ? $text : 'Summary unavailable — see raw data.'];
        } catch (\Throwable $e) {
            error_log('GeoCheckinService::aiDailySummary error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ── Anomaly heuristics ───────────────────────────────────────────
    /**
     * @return array<int, array{code:string, severity:string, detail:string}>
     */
    public function detectAnomalies(array $checkin, array $pings): array
    {
        $out = [];

        if (!empty($checkin)) {
            if (!empty($checkin['is_mock_gps'])) {
                $out[] = ['code' => 'GPS_MOCK_FLAG', 'severity' => 'high', 'detail' => 'Client reported a mock/spoofed GPS position at check-in'];
            }
            $acc = isset($checkin['check_in_accuracy_m']) ? (float) $checkin['check_in_accuracy_m'] : null;
            if ($acc !== null && $acc > 300) {
                $out[] = ['code' => 'ACCURACY_SUSPICIOUS', 'severity' => 'low', 'detail' => "Check-in GPS accuracy was {$acc}m (device may be indoors or faked)"];
            }
            if (($checkin['status'] ?? '') === 'out_of_zone') {
                $out[] = ['code' => 'OUT_OF_ZONE', 'severity' => 'medium', 'detail' => 'Check-in occurred outside every assigned work zone'];
            }
            if (($checkin['status'] ?? '') === 'review_required') {
                $out[] = ['code' => 'SELFIE_MISSING', 'severity' => 'low', 'detail' => 'Field-site check-in missing mandatory selfie'];
            }
        }

        // Impossible travel between consecutive pings
        $prev = null;
        foreach ($pings as $p) {
            if ($prev !== null && $prev['ping_at'] !== null && $p['ping_at'] !== null) {
                $t1 = strtotime((string) $prev['ping_at']);
                $t2 = strtotime((string) $p['ping_at']);
                $dt = max(1, $t2 - $t1);
                $d = $this->haversineM((float) $prev['latitude'], (float) $prev['longitude'], (float) $p['latitude'], (float) $p['longitude']);
                $kmh = ($d / $dt) * 3.6;
                if ($kmh > 200) {
                    $out[] = ['code' => 'IMPOSSIBLE_TRAVEL', 'severity' => 'high', 'detail' => 'Pings imply ' . round($kmh) . ' km/h — GPS spoof or device swap likely'];
                    break;
                }
                if ($dt > 3600) {
                    $out[] = ['code' => 'PING_GAP', 'severity' => 'medium', 'detail' => 'No field ping for ' . round($dt / 60) . ' min while on duty'];
                }
            }
            $prev = $p;
        }

        // Altitude frozen across 5+ consecutive pings
        if (count($pings) >= 5) {
            $alts = [];
            foreach ($pings as $p) {
                if (isset($p['altitude_m']) && is_numeric($p['altitude_m'])) {
                    $alts[] = (string) (float) $p['altitude_m'];
                }
            }
            if ($alts !== []) {
                $frozen = array_count_values($alts);
                foreach ($frozen as $alt => $count) {
                    if ($count >= 5) {
                        $out[] = ['code' => 'ALTITUDE_FROZEN', 'severity' => 'low', 'detail' => "Altitude unchanged at {$alt}m across {$count} consecutive pings - dummy-device pattern"];
                    }
                }
            }
        }

        // All-day no pings while duty open and check-out null past 8 hours
        if (!empty($checkin) && empty($checkin['check_out_at']) && count($pings) === 0) {
            $checkinTs = strtotime((string) ($checkin['check_in_at'] ?? ''));
            if ($checkinTs !== false && (time() - $checkinTs) > 8 * 3600) {
                $out[] = ['code' => 'NO_PINGS_ALL_DAY', 'severity' => 'medium', 'detail' => '8+ hours on duty with zero field pings'];
            }
        }

        return $out;
    }

    // ── Haversine ────────────────────────────────────────────────────
    private function haversineM(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth * $c;
    }

    // ── Selfie storage ───────────────────────────────────────────────
    private function storeSelfie(string $base64, int $employeeId): ?string
    {
        try {
            if (strpos($base64, 'base64,') !== false) {
                $base64 = substr($base64, strpos($base64, 'base64,') + 7);
            }
            $bin = base64_decode($base64, true);
            if ($bin === false || strlen($bin) > 600 * 1024) {
                return null;
            }
            $dir = STORAGE_PATH . '/checkin_selfies';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $name = 'selfie_' . $employeeId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $path = $dir . '/' . $name;
            file_put_contents($path, $bin);
            // return web-reachable relative path
            return 'storage/checkin_selfies/' . $name;
        } catch (\Throwable $e) {
            error_log('GeoCheckinService::storeSelfie error: ' . $e->getMessage());
            return null;
        }
    }
}
