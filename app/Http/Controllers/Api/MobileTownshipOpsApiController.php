<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use PDO;

/**
 * MobileTownshipOpsApiController — REST API for the Township Operations &
 * Utilities suite that exists on the web ERP but had no mobile surface:
 *
 *   STP, Borewell/Water, DG Genset, Security Roster, Security Incidents,
 *   CCTV, Solid Waste, Horticulture, Street Lighting, Rainwater Harvesting,
 *   Fire Safety, and the customer's PM Surya Ghar solar requests.
 *
 * All endpoints are read-only (monitoring logs are written from the web admin
 * desks). `type` is validated against a hardcoded whitelist so the table name
 * can never come from user input.
 */
class MobileTownshipOpsApiController extends BaseController
{
    private PDO $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = Database::getInstance()->getConnection();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Whitelisted utility desks. `table` and `date` are interpolated into SQL
     * but can only ever be one of these literal values.
     */
    private const UTILITY_DESKS = [
        'stp' => [
            'table' => 'township_stp_logs',
            'date' => 'log_date',
            'label' => 'Sewage Treatment Plant',
            'icon' => 'water',
            'unit' => 'kL treated',
            'headline' => "CONCAT(COALESCE(stp_plant_code,''), ' — ', COALESCE(uppcb_compliance_status,''))",
        ],
        'water' => [
            'table' => 'township_water_logs',
            'date' => 'log_date',
            'label' => 'Borewell & Water Supply',
            'icon' => 'water_drop',
            'unit' => 'kL pumped',
            'headline' => "CONCAT(COALESCE(pump_number,''), ' — ', COALESCE(pump_health,''))",
        ],
        'genset' => [
            'table' => 'township_genset_logs',
            'date' => 'log_date',
            'label' => 'DG Power Backup',
            'icon' => 'electrical_services',
            'unit' => 'kWh generated',
            'headline' => "CONCAT(COALESCE(genset_number,''), ' — ', COALESCE(mechanical_status,''))",
        ],
        'security' => [
            'table' => 'township_security_rosters',
            'date' => 'roster_date',
            'label' => 'Security Roster',
            'icon' => 'security',
            'unit' => 'guard shifts',
            'headline' => "CONCAT(COALESCE(gate_name,''), ' — ', COALESCE(status,''))",
        ],
        'security_incidents' => [
            'table' => 'township_security_incidents',
            'date' => 'incident_date',
            'label' => 'Security Incidents',
            'icon' => 'report',
            'unit' => 'incidents',
            'headline' => "CONCAT(COALESCE(category,''), ' — ', COALESCE(severity,''))",
        ],
        'cctv' => [
            'table' => 'township_cctv_logs',
            'date' => 'audit_date',
            'label' => 'CCTV Surveillance',
            'icon' => 'videocam',
            'unit' => 'cameras',
            'headline' => "CONCAT(COALESCE(nvr_identifier,''), ' — ', COALESCE(hdd_storage_status,''))",
        ],
        'waste' => [
            'table' => 'township_waste_logs',
            'date' => 'collection_date',
            'label' => 'Solid Waste & Sanitation',
            'icon' => 'delete',
            'unit' => 'kg collected',
            'headline' => "CONCAT(COALESCE(disposal_destination,''), ' — ', COALESCE(drain_cleaning_status,''))",
        ],
        'horticulture' => [
            'table' => 'township_horticulture_logs',
            'date' => 'census_date',
            'label' => 'Park & Horticulture',
            'icon' => 'park',
            'unit' => 'trees',
            'headline' => "CONCAT(COALESCE(park_name,''), ' — ', COALESCE(survival_rate_pct,0), '% survival')",
        ],
        'lighting' => [
            'table' => 'township_lighting_logs',
            'date' => 'audit_date',
            'label' => 'Street Lighting',
            'icon' => 'light_mode',
            'unit' => 'poles',
            'headline' => "CONCAT(COALESCE(feeder_pillar_code,''), ' — ', COALESCE(uptime_percent,0), '% uptime')",
        ],
        'rwh' => [
            'table' => 'township_rwh_logs',
            'date' => 'audit_date',
            'label' => 'Rainwater Harvesting',
            'icon' => 'water_damage',
            'unit' => 'recharge pits',
            'headline' => "CONCAT(COALESCE(pit_identifier,''), ' — ', COALESCE(monsoon_readiness_score,''))",
        ],
        'fire' => [
            'table' => 'township_fire_safety_logs',
            'date' => 'inspection_date',
            'label' => 'Fire Safety & NOC',
            'icon' => 'local_fire_department',
            'unit' => 'fire zones',
            'headline' => "CONCAT(COALESCE(fire_zone_identifier,''), ' — ', COALESCE(up_fire_noc_status,''))",
        ],
    ];

    private function getUserId(): ?int
    {
        if (!empty($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
            // NOTE (S447): `personal_access_tokens` does not exist -> 1146.
            // The real table is `api_tokens` and the token is stored RAW.
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT user_id FROM api_tokens WHERE token = ? AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1"
                );
                $stmt->execute([$token]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) return (int)$row['user_id'];
            } catch (\Throwable $e) {
                error_log('[TownshipOpsApi] getUserId: ' . $e->getMessage());
            }
        }

        return null;
    }

    public function jsonResponse($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /api/v2/mobile/township/operations
     * Consolidated dashboard: every utility desk with live counts + latest reading.
     */
    public function getOperations(): void
    {
        $desks = [];

        foreach (self::UTILITY_DESKS as $type => $cfg) {
            $table = $cfg['table'];
            $dateCol = $cfg['date'];

            $count = 0;
            $colonies = 0;
            $latestDate = null;
            $headline = null;

            try {
                $stmt = $this->pdo->query(
                    "SELECT COUNT(*) AS c, COUNT(DISTINCT colony_id) AS colonies, MAX(`{$dateCol}`) AS latest
                     FROM `{$table}`"
                );
                $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                $count = (int)($row['c'] ?? 0);
                $colonies = (int)($row['colonies'] ?? 0);
                $latestDate = $row['latest'] ?? null;

                if ($count > 0) {
                    $stmt = $this->pdo->query(
                        "SELECT {$cfg['headline']} AS headline FROM `{$table}`
                         ORDER BY `{$dateCol}` DESC, id DESC LIMIT 1"
                    );
                    $headline = $stmt->fetchColumn();
                }
            } catch (\Throwable $e) {
                // A missing table must never take down the whole dashboard.
                error_log("MobileTownshipOps: {$type} failed: " . $e->getMessage());
            }

            $desks[] = [
                'type' => $type,
                'label' => $cfg['label'],
                'icon' => $cfg['icon'],
                'unit' => $cfg['unit'],
                'record_count' => $count,
                'colonies_covered' => $colonies,
                'latest_date' => $latestDate,
                'latest_headline' => $headline,
            ];
        }

        $this->jsonResponse([
            'success' => true,
            'generated_at' => date('c'),
            'desks' => $desks,
        ]);
    }

    /**
     * GET /api/v2/mobile/township/operations/{type}
     * Detail for one utility desk: summary + latest records across colonies.
     */
    public function getUtilityDetail(string $type): void
    {
        if (!isset(self::UTILITY_DESKS[$type])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Unknown utility desk.',
                'valid_types' => array_keys(self::UTILITY_DESKS),
            ], 404);
        }

        $cfg = self::UTILITY_DESKS[$type];
        $table = $cfg['table'];
        $dateCol = $cfg['date'];

        try {
            $summaryStmt = $this->pdo->query(
                "SELECT COUNT(*) AS c, COUNT(DISTINCT colony_id) AS colonies, MAX(`{$dateCol}`) AS latest
                 FROM `{$table}`"
            );
            $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $recordsStmt = $this->pdo->query(
                "SELECT t.*, c.name AS colony_name
                 FROM `{$table}` t
                 LEFT JOIN colonies c ON t.colony_id = c.id
                 ORDER BY t.`{$dateCol}` DESC, t.id DESC
                 LIMIT 50"
            );
            $records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("MobileTownshipOps detail {$type} failed: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Utility desk is not available yet.',
            ], 503);
        }

        // Colony name can be null when the referenced colony was removed.
        foreach ($records as &$r) {
            $r['colony_name'] = $r['colony_name'] ?? '—';
        }
        unset($r);

        $this->jsonResponse([
            'success' => true,
            'type' => $type,
            'label' => $cfg['label'],
            'icon' => $cfg['icon'],
            'unit' => $cfg['unit'],
            'summary' => [
                'record_count' => (int)($summary['c'] ?? 0),
                'colonies_covered' => (int)($summary['colonies'] ?? 0),
                'latest_date' => $summary['latest'] ?? null,
            ],
            'records' => $records,
        ]);
    }

    /**
     * GET /api/v2/mobile/township/colonies/{id}/utilities
     * Per-colony utility coverage — powers the colony detail page.
     */
    public function getColonyUtilities(string $id): void
    {
        $colonyId = (int)$id;
        if ($colonyId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid colony id.'], 400);
        }

        $stmt = $this->pdo->prepare("SELECT id, name, location FROM colonies WHERE id = ? LIMIT 1");
        $stmt->execute([$colonyId]);
        $colony = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$colony) {
            $this->jsonResponse(['success' => false, 'message' => 'Colony not found.'], 404);
        }

        $desks = [];
        foreach (self::UTILITY_DESKS as $type => $cfg) {
            $table = $cfg['table'];
            $dateCol = $cfg['date'];
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT COUNT(*) AS c, MAX(`{$dateCol}`) AS latest FROM `{$table}` WHERE colony_id = ?"
                );
                $stmt->execute([$colonyId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                $desks[] = [
                    'type' => $type,
                    'label' => $cfg['label'],
                    'icon' => $cfg['icon'],
                    'record_count' => (int)($row['c'] ?? 0),
                    'latest_date' => $row['latest'] ?? null,
                ];
            } catch (\Throwable $e) {
                $desks[] = [
                    'type' => $type,
                    'label' => $cfg['label'],
                    'icon' => $cfg['icon'],
                    'record_count' => 0,
                    'latest_date' => null,
                ];
            }
        }

        $this->jsonResponse([
            'success' => true,
            'colony' => $colony,
            'desks' => $desks,
        ]);
    }

    /**
     * GET /api/v2/mobile/solar/requests
     * The logged-in customer's PM Surya Ghar solar subsidy requests.
     */
    public function getSolarRequests(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized: Please log in'], 401);
        }

        $stmt = $this->pdo->prepare(
            "SELECT * FROM customer_solar_requests WHERE customer_id = ? ORDER BY id DESC LIMIT 100"
        );
        $stmt->execute([$userId]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->jsonResponse([
            'success' => true,
            'count' => count($requests),
            'requests' => $requests,
        ]);
    }
}
