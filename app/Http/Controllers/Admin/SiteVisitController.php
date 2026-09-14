<?php

namespace App\Http\Controllers\Admin;

// AdminController resolved via namespace

/**
 * Admin Site Visit Management
 * View and manage all site visits across the platform
 */
class SiteVisitController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List all site visits with stats
     */
    public function index()
    {
        $this->requireAdmin();
        $this->layout = 'layouts/admin';
        $currentPage = 'site-visits';

        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $tab = $_GET['tab'] ?? 'all';
        $search = trim($_GET['q'] ?? '');

        $stats = ['total' => 0, 'today' => 0, 'upcoming' => 0, 'completed' => 0, 'cancelled' => 0];
        $visits = [];

        try {
            // Stats
            $stats['total'] = (int)$pdo->query("SELECT COUNT(*) FROM site_visits")->fetchColumn();
            $stats['today'] = (int)$pdo->query("SELECT COUNT(*) FROM site_visits WHERE visit_date = CURDATE() AND status NOT IN ('cancelled','completed')")->fetchColumn();
            $stats['upcoming'] = (int)$pdo->query("SELECT COUNT(*) FROM site_visits WHERE visit_date > CURDATE() AND status NOT IN ('cancelled','completed')")->fetchColumn();
            $stats['completed'] = (int)$pdo->query("SELECT COUNT(*) FROM site_visits WHERE status = 'completed'")->fetchColumn();
            $stats['cancelled'] = (int)$pdo->query("SELECT COUNT(*) FROM site_visits WHERE status = 'cancelled'")->fetchColumn();

            // Query
            $sql = "SELECT sv.*, l.name as lead_name, l.phone as lead_phone,
                           u.name as associate_name, u.email as associate_email
                    FROM site_visits sv
                    LEFT JOIN leads l ON l.id = sv.lead_id
                    LEFT JOIN users u ON u.id = sv.assigned_to
                    WHERE 1=1";

            $params = [];

            if ($tab === 'today') {
                $sql .= " AND sv.visit_date = CURDATE() AND sv.status NOT IN ('cancelled','completed')";
            } elseif ($tab === 'upcoming') {
                $sql .= " AND sv.visit_date >= CURDATE() AND sv.status NOT IN ('cancelled','completed')";
            } elseif ($tab === 'completed') {
                $sql .= " AND sv.status = 'completed'";
            } elseif ($tab === 'cancelled') {
                $sql .= " AND sv.status = 'cancelled'";
            }

            if ($search) {
                $sql .= " AND (sv.visitor_name LIKE ? OR sv.visitor_phone LIKE ? OR l.name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $sql .= " ORDER BY sv.visit_date DESC, sv.visit_time DESC LIMIT 100";

            $st = $pdo->prepare($sql);
            $st->execute($params);
            $visits = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Admin siteVisits error: ' . $e->getMessage());
        }

        // Executives for the "Assign Executive & Cab" modal (staff, not customers).
        $executives = [];
        try {
            [$eWhere, $eParams] = $this->tenantWhere();
            $execSql = "SELECT id, name, phone, email, role FROM users
                        WHERE status = 'active' AND role NOT IN ('customer','user'){$eWhere}
                        ORDER BY name ASC LIMIT 200";
            $execStmt = $pdo->prepare($execSql);
            $execStmt->execute($eParams);
            $executives = $execStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Admin siteVisits executives error: ' . $e->getMessage());
        }

        $this->render('admin/site_visits/index', [
            'page_title' => 'Site Visits Management',
            'currentPage' => $currentPage,
            'visits' => $visits,
            'stats' => $stats,
            'active_tab' => $tab,
            'search' => $search,
            'executives' => $executives,
        ]);
    }

    /**
     * Update site visit status (AJAX)
     */
    public function updateStatus($id)
    {
        $this->requireAdmin();
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $status = $_POST['status'] ?? '';
        $valid = ['scheduled','completed','cancelled','rescheduled','no_show'];
        if (!in_array($status, $valid)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid status']);
            return;
        }

        try {
            list($tSql, $tParams) = $this->tenantWhere();
            $st = $pdo->prepare("UPDATE site_visits SET status = ? WHERE id = ? $tSql");
            $st->execute(array_merge([$status, $id], $tParams));
            echo json_encode(['ok' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /admin/site-visits/{id}/assign (AJAX JSON)
     *
     * Smart dispatch: assign a sales executive + cab, confirm the visit, and
     * fire WhatsApp cards (customer gets the live colony GPS pin, executive
     * gets pickup + inquiry notes). Always returns click-to-chat URLs so the
     * admin can send manually when the WhatsApp API is not configured.
     *
     * Input: executive_id (required), cab_assigned, pickup_time.
     */
    public function assignExecutive($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        header('Content-Type: application/json');

        $id = (int)$id;
        $executiveId = (int)($_POST['executive_id'] ?? 0);
        $cabAssigned = trim((string)($_POST['cab_assigned'] ?? ''));
        $pickupTime = trim((string)($_POST['pickup_time'] ?? ''));

        if ($id <= 0 || $executiveId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Visit and executive are required.']);
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();
        $this->ensureVisitColumns($pdo);
        $cols = $this->visitColumns($pdo);
        $tid = (int)$this->tenantId();
        $tSql = $tid > 1 ? ' AND tenant_id = ?' : '';
        $tParams = $tid > 1 ? [$tid] : [];

        try {
            $vStmt = $pdo->prepare("SELECT * FROM site_visits WHERE id = ?{$tSql} LIMIT 1");
            $vStmt->execute(array_merge([$id], $tParams));
            $visit = $vStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$visit) {
                echo json_encode(['success' => false, 'error' => 'Site visit not found.']);
                return;
            }
            $execStmt = $pdo->prepare("SELECT id, name, phone, email FROM users WHERE id = ? LIMIT 1");
            $execStmt->execute([$executiveId]);
            $exec = $execStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$exec) {
                echo json_encode(['success' => false, 'error' => 'Executive not found.']);
                return;
            }

            $sets = ['assigned_to = ?', "status = 'confirmed'"];
            $params = [$executiveId];
            if (in_array('cab_details', $cols, true)) {
                $sets[] = 'cab_details = ?';
                $params[] = $cabAssigned !== '' ? $cabAssigned : null;
            }
            if (in_array('pickup_time', $cols, true)) {
                $sets[] = 'pickup_time = ?';
                $params[] = $pickupTime !== '' ? $pickupTime : null;
            }
            $params[] = $id;
            $upd = $pdo->prepare('UPDATE site_visits SET ' . implode(', ', $sets) . " WHERE id = ?{$tSql}");
            $upd->execute(array_merge($params, $tParams));

            try {
                $pdo->prepare("UPDATE site_visits SET confirmation_sent = 1 WHERE id = ?{$tSql}")
                    ->execute(array_merge([$id], $tParams));
            } catch (\Throwable $e) {
                error_log('SiteVisitController::assignExecutive confirm flag: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            error_log('SiteVisitController::assignExecutive save: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Could not assign executive.']);
            return;
        }

        $dispatch = $this->loadVisitForDispatch($pdo, $id, $tid);
        $result = $this->dispatchVisitWhatsApp($dispatch, $cabAssigned, $pickupTime);
        $result['success'] = true;
        $result['message'] = 'Executive assigned & visit confirmed.';
        echo json_encode($result);
    }

    /**
     * POST /admin/site-visits/{id}/send-pin (AJAX JSON)
     *
     * 1-click resend of the WhatsApp colony GPS pin card to the customer
     * (and pickup card to the assigned executive, when one is assigned).
     */
    public function sendPin($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        header('Content-Type: application/json');

        $id = (int)$id;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid visit.']);
            return;
        }

        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();
        $tid = (int)$this->tenantId();

        $dispatch = $this->loadVisitForDispatch($pdo, $id, $tid);
        if (!$dispatch) {
            echo json_encode(['success' => false, 'error' => 'Site visit not found.']);
            return;
        }
        $result = $this->dispatchVisitWhatsApp($dispatch, (string)($dispatch['cab_details'] ?? ''), (string)($dispatch['pickup_time'] ?? ''));
        $result['success'] = true;
        echo json_encode($result);
    }

    /**
     * POST /admin/site-visits/{id}/outcome (AJAX JSON)
     *
     * Record visit completion: completed | interested | token_booked |
     * not_interested | rescheduled. `interested` / `token_booked` auto-create
     * an open Opportunity in CRM (opportunities table).
     *
     * Optional: plot_preference, budget_feedback, outcome_notes, followup_date.
     */
    public function markOutcome($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        header('Content-Type: application/json');

        $id = (int)$id;
        $outcome = strtolower(trim((string)($_POST['outcome'] ?? '')));
        $valid = ['completed', 'interested', 'token_booked', 'not_interested', 'rescheduled'];
        if ($id <= 0 || !in_array($outcome, $valid, true)) {
            echo json_encode(['success' => false, 'error' => 'Valid outcome is required.']);
            return;
        }

        $plotPref = trim((string)($_POST['plot_preference'] ?? ''));
        $budgetFb = trim((string)($_POST['budget_feedback'] ?? ''));
        $notes = trim((string)($_POST['outcome_notes'] ?? $_POST['notes'] ?? ''));
        $followupDate = trim((string)($_POST['followup_date'] ?? ''));

        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();
        $this->ensureVisitColumns($pdo);
        $cols = $this->visitColumns($pdo);
        $tid = (int)$this->tenantId();
        $tSql = $tid > 1 ? ' AND tenant_id = ?' : '';
        $tParams = $tid > 1 ? [$tid] : [];

        try {
            $vStmt = $pdo->prepare("SELECT * FROM site_visits WHERE id = ?{$tSql} LIMIT 1");
            $vStmt->execute(array_merge([$id], $tParams));
            $visit = $vStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$visit) {
                echo json_encode(['success' => false, 'error' => 'Site visit not found.']);
                return;
            }

            $feedbackLine = 'Outcome: ' . $outcome
                . ($plotPref !== '' ? ' | Plot preference: ' . $plotPref : '')
                . ($budgetFb !== '' ? ' | Budget: ' . $budgetFb : '')
                . ($notes !== '' ? ' | Notes: ' . $notes : '');
            $mergedFeedback = trim(trim((string)($visit['feedback'] ?? '')) . "\n" . $feedbackLine);

            $sets = ['status = ?', 'feedback = ?'];
            $params = [$outcome, $mergedFeedback];
            if ($outcome === 'completed') {
                $sets[] = 'completed_at = NOW()';
            }
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
                $params[] = $mergedFeedback;
            }
            $params[] = $id;
            $upd = $pdo->prepare('UPDATE site_visits SET ' . implode(', ', $sets) . " WHERE id = ?{$tSql}");
            $upd->execute(array_merge($params, $tParams));
        } catch (\Throwable $e) {
            error_log('SiteVisitController::markOutcome save: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Could not record outcome.']);
            return;
        }

        $opportunityId = 0;
        if (in_array($outcome, ['interested', 'token_booked'], true)) {
            $opportunityId = $this->createVisitOpportunity($pdo, $visit, $outcome, $plotPref, $budgetFb, $tid);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Visit outcome recorded.',
            'outcome' => $outcome,
            'opportunity_id' => $opportunityId,
            'opportunity_created' => $opportunityId > 0,
        ]);
    }

    /* ---------------- Dispatch helpers (additive, tenant-scoped) ---------------- */

    /**
     * Load a visit with colony GPS + customer + executive identity for messaging.
     */
    private function loadVisitForDispatch(\PDO $pdo, int $id, int $tid): ?array
    {
        try {
            $tSql = $tid > 1 ? ' AND sv.tenant_id = ?' : '';
            $params = $tid > 1 ? [$id, $tid] : [$id];
            $stmt = $pdo->prepare(
                "SELECT sv.*, c.name AS colony_name, c.location AS colony_location,
                        c.map_link AS colony_map_link, c.latitude AS colony_lat, c.longitude AS colony_lng,
                        c.starting_price AS colony_price,
                        e.name AS exec_name, e.phone AS exec_phone,
                        l.name AS lead_name
                 FROM site_visits sv
                 LEFT JOIN colonies c ON c.id = sv.colony_id
                 LEFT JOIN users e ON e.id = sv.assigned_to
                 LEFT JOIN leads l ON l.id = sv.lead_id
                 WHERE sv.id = ?{$tSql} LIMIT 1"
            );
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('SiteVisitController::loadVisitForDispatch: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Colony GPS pin URL: stored map link → lat/lng → name search (in that order).
     */
    private function buildColonyGpsUrl(array $dispatch): string
    {
        $mapLink = trim((string)($dispatch['colony_map_link'] ?? ''));
        if ($mapLink !== '' && preg_match('#^https?://#i', $mapLink)) {
            return $mapLink;
        }
        $lat = $dispatch['colony_lat'] ?? null;
        $lng = $dispatch['colony_lng'] ?? null;
        if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
            return 'https://www.google.com/maps/search/?api=1&query=' . $lat . ',' . $lng;
        }
        $q = trim(trim((string)($dispatch['colony_name'] ?? '')) . ' ' . trim((string)($dispatch['colony_location'] ?? '')));
        if ($q === '') $q = 'APS Dream Home Gorakhpur';
        return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($q);
    }

    private function waDigits(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 10) $digits = '91' . $digits;
        return strlen($digits) >= 12 ? $digits : '';
    }

    /**
     * Build click-to-chat cards + best-effort template sends for both parties.
     */
    private function dispatchVisitWhatsApp(?array $dispatch, string $cab, string $pickupTime): array
    {
        if (!$dispatch) {
            return ['whatsapp_customer_url' => '', 'whatsapp_exec_url' => '', 'api_status' => 'visit not loaded'];
        }
        $gpsUrl = $this->buildColonyGpsUrl($dispatch);
        $customerName = $dispatch['visitor_name'] ?? $dispatch['lead_name'] ?? 'Customer';
        $colonyName = $dispatch['colony_name'] ?? 'our colony';
        $dateStr = !empty($dispatch['visit_date']) ? date('d M Y', strtotime($dispatch['visit_date'])) : 'scheduled date';
        $timeStr = !empty($dispatch['visit_time']) ? date('h:i A', strtotime($dispatch['visit_time'])) : '';
        $execName = $dispatch['exec_name'] ?? 'our executive';
        $execPhone = $dispatch['exec_phone'] ?? '';

        $custMsg = 'Namaste ' . $customerName . '! Your site visit to ' . $colonyName
            . ' is confirmed for ' . $dateStr . ($timeStr !== '' ? ' at ' . $timeStr : '') . '. '
            . 'Your executive ' . $execName . ($execPhone !== '' ? ' (' . $execPhone . ')' : '')
            . ' will receive you.'
            . ($cab !== '' ? ' Cab: ' . $cab . '.' : '')
            . ($pickupTime !== '' ? ' Pickup: ' . $pickupTime . '.' : '')
            . ' Google Maps Location: ' . $gpsUrl;

        $custDigits = $this->waDigits((string)($dispatch['visitor_phone'] ?? ''));
        $custUrl = $custDigits !== '' ? 'https://wa.me/' . $custDigits . '?text=' . urlencode($custMsg) : '';

        $pickupAddr = trim((string)($dispatch['pickup_location'] ?? ''));
        if ($pickupAddr === '' && !empty($dispatch['pickup_required'])) $pickupAddr = 'customer pickup requested (address on call)';
        $execMsg = 'New site visit assigned: ' . $customerName
            . ' (' . ($dispatch['visitor_phone'] ?? '-') . ') → ' . $colonyName
            . ' on ' . $dateStr . ($timeStr !== '' ? ' at ' . $timeStr : '') . '.'
            . ($pickupAddr !== '' ? ' Pickup: ' . $pickupAddr . '.' : '')
            . ($cab !== '' ? ' Cab: ' . $cab . '.' : '')
            . (!empty($dispatch['notes']) ? ' Notes: ' . mb_substr($dispatch['notes'], 0, 200) : '')
            . ' Maps: ' . $gpsUrl;
        $execDigits = $this->waDigits((string)$execPhone);
        $execUrl = $execDigits !== '' ? 'https://wa.me/' . $execDigits . '?text=' . urlencode($execMsg) : '';

        $apiStatus = 'click-to-chat links generated (WhatsApp API not configured)';
        try {
            $wa = new \App\Services\Communication\WhatsAppTemplateService();
            $sentAny = false;
            if ($custDigits !== '') {
                $res = $wa->sendTemplate([
                    'to' => $custDigits,
                    'template_name' => 'site_visit_confirmed',
                    'language' => 'en',
                    'components' => [[
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => (string)$customerName],
                            ['type' => 'text', 'text' => (string)$colonyName],
                            ['type' => 'text', 'text' => $dateStr . ($timeStr !== '' ? ' ' . $timeStr : '')],
                            ['type' => 'text', 'text' => (string)$execName],
                            ['type' => 'text', 'text' => $gpsUrl],
                        ],
                    ]],
                ]);
                if (!empty($res['success'])) $sentAny = true;
            }
            if ($execDigits !== '') {
                $res = $wa->sendTemplate([
                    'to' => $execDigits,
                    'template_name' => 'site_visit_assigned',
                    'language' => 'en',
                    'components' => [[
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => (string)$customerName],
                            ['type' => 'text', 'text' => (string)($dispatch['visitor_phone'] ?? '')],
                            ['type' => 'text', 'text' => (string)$colonyName],
                            ['type' => 'text', 'text' => $dateStr],
                        ],
                    ]],
                ]);
                if (!empty($res['success'])) $sentAny = true;
            }
            $apiStatus = $sentAny ? 'WhatsApp template sent' : 'Template API not configured — use click-to-chat links';
        } catch (\Throwable $e) {
            error_log('SiteVisitController::dispatchVisitWhatsApp template: ' . $e->getMessage());
        }

        return [
            'whatsapp_customer_url' => $custUrl,
            'whatsapp_exec_url' => $execUrl,
            'colony_gps_url' => $gpsUrl,
            'api_status' => $apiStatus,
        ];
    }

    /**
     * Auto-create an open CRM Opportunity for interested / token_booked visits.
     */
    private function createVisitOpportunity(\PDO $pdo, array $visit, string $outcome, string $plotPref, string $budgetFb, int $tid): int
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
                    error_log('SiteVisitController::createVisitOpportunity price: ' . $e->getMessage());
                }
            }
            $oppNo = 'OPP-VISIT-' . (int)$visit['id'] . '-' . date('YmdHis');
            $ins = $pdo->prepare(
                'INSERT INTO opportunities
                    (tenant_id, opportunity_number, lead_id, stage, expected_value, value,
                     probability_percentage, assigned_to, created_by, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'open\', NOW())'
            );
            $ins->execute([
                $tid > 0 ? $tid : 1,
                $oppNo,
                !empty($visit['lead_id']) ? (int)$visit['lead_id'] : null,
                $outcome === 'token_booked' ? 'negotiation' : 'new',
                $expected,
                $expected,
                $outcome === 'token_booked' ? 80 : 40,
                !empty($visit['assigned_to']) ? (int)$visit['assigned_to'] : null,
                (int)($_SESSION['admin_id'] ?? 0) ?: null,
            ]);
            return (int)$pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('SiteVisitController::createVisitOpportunity: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Idempotent schema guard: live site_visits has no cab/outcome columns —
     * the dispatch spec needs cab_details + pickup_time + outcome + outcome_notes.
     * Adds each only when missing; all readers/writers degrade gracefully.
     *
     * @var array|null
     */
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
            error_log('SiteVisitController::visitColumns: ' . $e->getMessage());
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
            error_log('SiteVisitController::ensureVisitColumns: ' . $e->getMessage());
        }
    }
}
