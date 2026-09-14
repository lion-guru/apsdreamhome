<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

/**
 * Mobile API — Smart Site Visit Dispatch & Colony GPS Pin Engine.
 *
 * Staff-only (BaseController::ADMIN_ROLES) JSON parity for the web
 * Module 3 endpoints (Admin\SiteVisitController::assignExecutive /
 * markOutcome / sendPin). Bodies accept form-encoded or JSON.
 */
class SiteVisitDispatchApiController extends BaseController
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
            error_log('SiteVisitDispatchApiController::staffUser: ' . $e->getMessage());
            return null;
        }
        if (!$user || ($user['status'] ?? '') !== 'active') return null;
        if (!in_array(($user['role'] ?? ''), self::ADMIN_ROLES, true)) return null;
        return $user;
    }

    private function apiInput(): array
    {
        $json = json_decode((string)file_get_contents('php://input'), true);
        if (is_array($json) && !empty($json)) return $json;
        return $_POST;
    }

    private function pdo(): \PDO
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
    }

    /**
     * POST /api/v2/mobile/site-visits/{id}/assign
     * {executive_id*, cab_assigned, pickup_time}
     */
    public function assign($id)
    {
        try {
            if (!$this->staffUser()) {
                return $this->jsonError('Unauthorized', 401);
            }
            $in = $this->apiInput();
            $id = (int)$id;
            $executiveId = (int)($in['executive_id'] ?? 0);
            $cab = trim((string)($in['cab_assigned'] ?? ''));
            $pickupTime = trim((string)($in['pickup_time'] ?? ''));
            if ($id <= 0 || $executiveId <= 0) {
                return $this->jsonError('Visit and executive are required', 400);
            }

            $pdo = $this->pdo();
            $this->ensureVisitColumns($pdo);
            $cols = $this->visitColumns($pdo);
            $tid = (int)$this->tenantId();
            $tSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $tParams = $tid > 1 ? [$tid] : [];

            $vStmt = $pdo->prepare("SELECT * FROM site_visits WHERE id = ?{$tSql} LIMIT 1");
            $vStmt->execute(array_merge([$id], $tParams));
            if (!$vStmt->fetch(\PDO::FETCH_ASSOC)) {
                return $this->jsonError('Site visit not found', 404);
            }
            $execStmt = $pdo->prepare("SELECT id, name, phone FROM users WHERE id = ? LIMIT 1");
            $execStmt->execute([$executiveId]);
            if (!$execStmt->fetch(\PDO::FETCH_ASSOC)) {
                return $this->jsonError('Executive not found', 404);
            }

            $sets = ['assigned_to = ?', "status = 'confirmed'"];
            $params = [$executiveId];
            if (in_array('cab_details', $cols, true)) {
                $sets[] = 'cab_details = ?';
                $params[] = $cab !== '' ? $cab : null;
            }
            if (in_array('pickup_time', $cols, true)) {
                $sets[] = 'pickup_time = ?';
                $params[] = $pickupTime !== '' ? $pickupTime : null;
            }
            $params[] = $id;
            $pdo->prepare('UPDATE site_visits SET ' . implode(', ', $sets) . " WHERE id = ?{$tSql}")
                ->execute(array_merge($params, $tParams));
            try {
                $pdo->prepare("UPDATE site_visits SET confirmation_sent = 1 WHERE id = ?{$tSql}")
                    ->execute(array_merge([$id], $tParams));
            } catch (\Throwable $e) {
                error_log('SiteVisitDispatchApiController::assign flag: ' . $e->getMessage());
            }

            $dispatch = $this->loadDispatch($pdo, $id, $tid);
            $result = $this->dispatchWhatsApp($dispatch, $cab, $pickupTime);
            $result['success'] = true;
            $result['message'] = 'Executive assigned & visit confirmed.';
            $this->jsonResponse($result);
        } catch (\Throwable $e) {
            error_log('SiteVisitDispatchApiController::assign: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }

    /**
     * POST /api/v2/mobile/site-visits/{id}/send-pin
     */
    public function sendPin($id)
    {
        try {
            if (!$this->staffUser()) {
                return $this->jsonError('Unauthorized', 401);
            }
            $pdo = $this->pdo();
            $dispatch = $this->loadDispatch($pdo, (int)$id, (int)$this->tenantId());
            if (!$dispatch) {
                return $this->jsonError('Site visit not found', 404);
            }
            $result = $this->dispatchWhatsApp($dispatch, (string)($dispatch['cab_details'] ?? ''), (string)($dispatch['pickup_time'] ?? ''));
            $result['success'] = true;
            $this->jsonResponse($result);
        } catch (\Throwable $e) {
            error_log('SiteVisitDispatchApiController::sendPin: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }

    /**
     * POST /api/v2/mobile/site-visits/{id}/outcome
     * {outcome*, plot_preference, budget_feedback, outcome_notes, followup_date}
     */
    public function outcome($id)
    {
        try {
            if (!$this->staffUser()) {
                return $this->jsonError('Unauthorized', 401);
            }
            $in = $this->apiInput();
            $id = (int)$id;
            $outcome = strtolower(trim((string)($in['outcome'] ?? '')));
            $valid = ['completed', 'interested', 'token_booked', 'not_interested', 'rescheduled'];
            if ($id <= 0 || !in_array($outcome, $valid, true)) {
                return $this->jsonError('Valid outcome is required', 400);
            }
            $plotPref = trim((string)($in['plot_preference'] ?? ''));
            $budgetFb = trim((string)($in['budget_feedback'] ?? ''));
            $notes = trim((string)($in['outcome_notes'] ?? $in['notes'] ?? ''));
            $followupDate = trim((string)($in['followup_date'] ?? ''));

            $pdo = $this->pdo();
            $this->ensureVisitColumns($pdo);
            $cols = $this->visitColumns($pdo);
            $tid = (int)$this->tenantId();
            $tSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $tParams = $tid > 1 ? [$tid] : [];

            $vStmt = $pdo->prepare("SELECT * FROM site_visits WHERE id = ?{$tSql} LIMIT 1");
            $vStmt->execute(array_merge([$id], $tParams));
            $visit = $vStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$visit) {
                return $this->jsonError('Site visit not found', 404);
            }

            $line = 'Outcome: ' . $outcome
                . ($plotPref !== '' ? ' | Plot preference: ' . $plotPref : '')
                . ($budgetFb !== '' ? ' | Budget: ' . $budgetFb : '')
                . ($notes !== '' ? ' | Notes: ' . $notes : '');
            $merged = trim(trim((string)($visit['feedback'] ?? '')) . "\n" . $line);

            $sets = ['status = ?', 'feedback = ?'];
            $params = [$outcome, $merged];
            if ($outcome === 'completed') $sets[] = 'completed_at = NOW()';
            if ($outcome === 'rescheduled' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $followupDate)) {
                $sets[] = 'visit_date = ?';
                $params[] = $followupDate;
            }
            if (in_array('outcome', $cols, true)) {
                $sets[] = 'outcome = ?';
                $params[] = $outcome;
            }
            if (in_array('outcome_notes', $cols, true)) {
                $sets[] = 'outcome_notes = ?';
                $params[] = $merged;
            }
            $params[] = $id;
            $pdo->prepare('UPDATE site_visits SET ' . implode(', ', $sets) . " WHERE id = ?{$tSql}")
                ->execute(array_merge($params, $tParams));

            $oppId = 0;
            if (in_array($outcome, ['interested', 'token_booked'], true)) {
                $oppId = $this->createOpportunity($pdo, $visit, $outcome, $budgetFb, $tid);
            }
            $this->jsonResponse([
                'success' => true, 'message' => 'Visit outcome recorded.',
                'outcome' => $outcome, 'opportunity_id' => $oppId,
                'opportunity_created' => $oppId > 0,
            ]);
        } catch (\Throwable $e) {
            error_log('SiteVisitDispatchApiController::outcome: ' . $e->getMessage());
            return $this->jsonError('Server error', 500);
        }
    }

    /* ---------------- helpers (mirror web dispatch engine) ---------------- */

    private function loadDispatch(\PDO $pdo, int $id, int $tid): ?array
    {
        try {
            $tSql = $tid > 1 ? ' AND sv.tenant_id = ?' : '';
            $stmt = $pdo->prepare(
                "SELECT sv.*, c.name AS colony_name, c.location AS colony_location,
                        c.map_link AS colony_map_link, c.latitude AS colony_lat, c.longitude AS colony_lng,
                        e.name AS exec_name, e.phone AS exec_phone, l.name AS lead_name
                 FROM site_visits sv
                 LEFT JOIN colonies c ON c.id = sv.colony_id
                 LEFT JOIN users e ON e.id = sv.assigned_to
                 LEFT JOIN leads l ON l.id = sv.lead_id
                 WHERE sv.id = ?{$tSql} LIMIT 1"
            );
            $stmt->execute($tid > 1 ? [$id, $tid] : [$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('SiteVisitDispatchApiController::loadDispatch: ' . $e->getMessage());
            return null;
        }
    }

    private function gpsUrl(array $d): string
    {
        $mapLink = trim((string)($d['colony_map_link'] ?? ''));
        if ($mapLink !== '' && preg_match('#^https?://#i', $mapLink)) return $mapLink;
        if (($d['colony_lat'] ?? '') !== '' && ($d['colony_lng'] ?? '') !== '') {
            return 'https://www.google.com/maps/search/?api=1&query=' . $d['colony_lat'] . ',' . $d['colony_lng'];
        }
        $q = trim(trim((string)($d['colony_name'] ?? '')) . ' ' . trim((string)($d['colony_location'] ?? '')));
        return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($q !== '' ? $q : 'APS Dream Home Gorakhpur');
    }

    private function waDigits(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 10) $digits = '91' . $digits;
        return strlen($digits) >= 12 ? $digits : '';
    }

    private function dispatchWhatsApp(?array $d, string $cab, string $pickupTime): array
    {
        if (!$d) return ['whatsapp_customer_url' => '', 'whatsapp_exec_url' => '', 'api_status' => 'visit not loaded'];
        $gps = $this->gpsUrl($d);
        $customerName = $d['visitor_name'] ?? $d['lead_name'] ?? 'Customer';
        $colonyName = $d['colony_name'] ?? 'our colony';
        $dateStr = !empty($d['visit_date']) ? date('d M Y', strtotime($d['visit_date'])) : 'scheduled date';
        $timeStr = !empty($d['visit_time']) ? date('h:i A', strtotime($d['visit_time'])) : '';
        $execName = $d['exec_name'] ?? 'our executive';

        $custMsg = 'Namaste ' . $customerName . '! Your site visit to ' . $colonyName
            . ' is confirmed for ' . $dateStr . ($timeStr !== '' ? ' at ' . $timeStr : '') . '. '
            . 'Your executive ' . $execName . (!empty($d['exec_phone']) ? ' (' . $d['exec_phone'] . ')' : '') . ' will receive you.'
            . ($cab !== '' ? ' Cab: ' . $cab . '.' : '')
            . ($pickupTime !== '' ? ' Pickup: ' . $pickupTime . '.' : '')
            . ' Google Maps Location: ' . $gps;
        $custDigits = $this->waDigits((string)($d['visitor_phone'] ?? ''));
        $custUrl = $custDigits !== '' ? 'https://wa.me/' . $custDigits . '?text=' . urlencode($custMsg) : '';
        $execDigits = $this->waDigits((string)($d['exec_phone'] ?? ''));
        $execUrl = $execDigits !== '' ? 'https://wa.me/' . $execDigits . '?text=' . urlencode('Visit assigned: ' . $customerName . ' → ' . $colonyName . ' on ' . $dateStr . '. Maps: ' . $gps) : '';

        $apiStatus = 'click-to-chat links generated (WhatsApp API not configured)';
        try {
            $wa = new \App\Services\Communication\WhatsAppTemplateService();
            if ($custDigits !== '') {
                $res = $wa->sendTemplate(['to' => $custDigits, 'template_name' => 'site_visit_confirmed', 'language' => 'en',
                    'components' => [['type' => 'body', 'parameters' => [
                        ['type' => 'text', 'text' => (string)$customerName],
                        ['type' => 'text', 'text' => (string)$colonyName],
                        ['type' => 'text', 'text' => $dateStr],
                        ['type' => 'text', 'text' => (string)$execName],
                        ['type' => 'text', 'text' => $gps]]]]]);
                if (!empty($res['success'])) $apiStatus = 'WhatsApp template sent';
            }
        } catch (\Throwable $e) {
            error_log('SiteVisitDispatchApiController::dispatchWhatsApp: ' . $e->getMessage());
        }
        return ['whatsapp_customer_url' => $custUrl, 'whatsapp_exec_url' => $execUrl, 'colony_gps_url' => $gps, 'api_status' => $apiStatus];
    }

    private function createOpportunity(\PDO $pdo, array $visit, string $outcome, string $budgetFb, int $tid): int
    {
        try {
            $expected = 0.0;
            if (preg_match('/([\d,]+(?:\.\d+)?)/', str_replace(['₹', 'Rs', 'rs', ','], ['', '', '', ''], $budgetFb), $m)) {
                $expected = (float)str_replace(',', '', $m[1]);
                if (stripos($budgetFb, 'lakh') !== false || stripos($budgetFb, 'lac') !== false) $expected *= 100000;
                if (stripos($budgetFb, 'cr') !== false) $expected *= 10000000;
            }
            if ($expected <= 0) {
                try {
                    $cStmt = $pdo->prepare('SELECT starting_price FROM colonies WHERE id = ? LIMIT 1');
                    $cStmt->execute([(int)($visit['colony_id'] ?? 0)]);
                    $expected = (float)$cStmt->fetchColumn();
                } catch (\Throwable $e) {
                    error_log('SiteVisitDispatchApiController::createOpportunity price: ' . $e->getMessage());
                }
            }
            $ins = $pdo->prepare(
                'INSERT INTO opportunities (tenant_id, opportunity_number, lead_id, stage, expected_value, value,
                     probability_percentage, assigned_to, created_by, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'open\', NOW())'
            );
            $ins->execute([
                $tid > 0 ? $tid : 1, 'OPP-VISIT-' . (int)$visit['id'] . '-' . date('YmdHis'),
                !empty($visit['lead_id']) ? (int)$visit['lead_id'] : null,
                $outcome === 'token_booked' ? 'negotiation' : 'new',
                $expected, $expected, $outcome === 'token_booked' ? 80 : 40,
                !empty($visit['assigned_to']) ? (int)$visit['assigned_to'] : null,
                (int)($GLOBALS['api_user_id'] ?? 0) ?: null,
            ]);
            return (int)$pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('SiteVisitDispatchApiController::createOpportunity: ' . $e->getMessage());
            return 0;
        }
    }

    /** @var array|null */
    private static $visitColCache = null;

    private function visitColumns(\PDO $pdo): array
    {
        if (self::$visitColCache !== null) return self::$visitColCache;
        try {
            $dbName = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
            $chk = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'site_visits'");
            $chk->execute([$dbName]);
            self::$visitColCache = $chk->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable $e) {
            error_log('SiteVisitDispatchApiController::visitColumns: ' . $e->getMessage());
            self::$visitColCache = [];
        }
        return self::$visitColCache;
    }

    private function ensureVisitColumns(\PDO $pdo): void
    {
        try {
            $have = $this->visitColumns($pdo);
            $missing = [];
            if (!in_array('cab_details', $have, true)) $missing['cab_details'] = 'VARCHAR(255) NULL';
            if (!in_array('pickup_time', $have, true)) $missing['pickup_time'] = 'VARCHAR(50) NULL';
            if (!in_array('outcome', $have, true)) $missing['outcome'] = 'VARCHAR(50) NULL';
            if (!in_array('outcome_notes', $have, true)) $missing['outcome_notes'] = 'TEXT NULL';
            foreach ($missing as $col => $def) {
                $pdo->exec('ALTER TABLE site_visits ADD COLUMN ' . $col . ' ' . $def);
            }
            if (!empty($missing)) self::$visitColCache = null;
        } catch (\Throwable $e) {
            error_log('SiteVisitDispatchApiController::ensureVisitColumns: ' . $e->getMessage());
        }
    }
}
