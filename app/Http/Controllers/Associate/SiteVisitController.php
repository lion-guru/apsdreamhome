<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateSiteVisitController
 * Handles site visit scheduling and management
 */
class SiteVisitController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Site visits list
     */
    public function siteVisits()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND sv.tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $tab = $_GET['tab'] ?? 'upcoming';
            $status = $_GET['status'] ?? '';
            $today = date('Y-m-d');

            // 1. Fetch all visits for this associate with complete details
            $allVisits = $db->fetchAll("
                SELECT sv.*,
                       COALESCE(NULLIF(sv.visitor_name, ''), u.name, l.name, 'Valued Visitor') as visitor_name,
                       COALESCE(NULLIF(sv.visitor_phone, ''), u.phone, l.phone, '') as visitor_phone,
                       l.name as lead_name,
                       c.name as colony_name,
                       pl.plot_number,
                       u.name as customer_name
                FROM site_visits sv
                LEFT JOIN plots pl ON pl.id = sv.plot_id
                LEFT JOIN colonies c ON c.id = sv.colony_id
                LEFT JOIN users u ON u.id = sv.user_id
                LEFT JOIN leads l ON l.id = sv.lead_id
                WHERE sv.agent_id = ?{$tidSql}
                ORDER BY sv.visit_date DESC, sv.visit_time DESC
            ", $params) ?: [];

            // 2. Compute accurate stats across all visits
            $stats = [
                'total' => count($allVisits),
                'today' => 0,
                'upcoming' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'overdue' => 0,
            ];

            foreach ($allVisits as $v) {
                $vDate = $v['visit_date'] ?? '';
                $vStatus = $v['status'] ?? 'scheduled';
                $isCompleted = ($vStatus === 'completed');
                $isCancelled = ($vStatus === 'cancelled');

                if ($isCompleted) {
                    $stats['completed']++;
                }
                if ($isCancelled) {
                    $stats['cancelled']++;
                }
                if ($vDate === $today) {
                    $stats['today']++;
                }
                if ($vDate >= $today && !$isCompleted && !$isCancelled) {
                    $stats['upcoming']++;
                }
                if ($vDate < $today && !$isCompleted && !$isCancelled) {
                    $stats['overdue']++;
                }
            }

            // 3. Filter visits according to selected tab or explicit status
            $filteredVisits = [];
            foreach ($allVisits as $v) {
                $vDate = $v['visit_date'] ?? '';
                $vStatus = $v['status'] ?? 'scheduled';
                $isCompleted = ($vStatus === 'completed');
                $isCancelled = ($vStatus === 'cancelled');

                if ($status && $vStatus !== $status) {
                    continue;
                }

                if ($tab === 'today') {
                    if ($vDate === $today) $filteredVisits[] = $v;
                } elseif ($tab === 'completed') {
                    if ($isCompleted) $filteredVisits[] = $v;
                } elseif ($tab === 'overdue') {
                    if ($vDate < $today && !$isCompleted && !$isCancelled) $filteredVisits[] = $v;
                } elseif ($tab === 'all') {
                    $filteredVisits[] = $v;
                } else {
                    // Default tab: 'upcoming'
                    if ($vDate >= $today && !$isCompleted && !$isCancelled) {
                        $filteredVisits[] = $v;
                    }
                }
            }

            $this->render('associate/site_visits', [
                'page_title' => 'Site Visits - Associate Portal',
                'page_description' => 'Manage site visits',
                'visits' => $filteredVisits,
                'site_visits' => $filteredVisits,
                'stats' => $stats,
                'active_tab' => $tab,
                'status' => $status,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateSiteVisitController error: ' . $e->getMessage());
            $this->render('associate/site_visits', [
                'page_title' => 'Site Visits - Associate Portal',
                'page_description' => 'Manage site visits',
                'visits' => [],
                'site_visits' => [],
                'stats' => ['total'=>0,'today'=>0,'upcoming'=>0,'completed'=>0,'cancelled'=>0,'overdue'=>0],
                'active_tab' => 'upcoming',
                'status' => '',
            ], 'layouts/associate');
        }
    }

    /**
     * My Schedule page (List & Calendar view)
     */
    public function schedule()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();
        $db = \App\Core\Database\Database::getInstance();
        $events = [];

        try {
            $tidSql = $tid > 1 ? " AND sv.tenant_id = ?" : "";
            $params = [$userId];
            if ($tid > 1) $params[] = $tid;

            // 1. Site visits
            $visits = $db->fetchAll("
                SELECT sv.id, sv.visit_date as event_date, sv.visit_time as event_time, 'site_visit' as event_type,
                       CONCAT('Plot ', COALESCE(pl.plot_number, 'N/A'), ' - ', COALESCE(u.name, 'Customer')) as title,
                       'high' as priority, sv.status, COALESCE(sv.notes, '') as notes
                FROM site_visits sv
                JOIN plots pl ON pl.id = sv.plot_id
                LEFT JOIN users u ON u.id = sv.user_id
                WHERE sv.agent_id = ?{$tidSql}
                ORDER BY sv.visit_date ASC, sv.visit_time ASC
            ", $params) ?: [];
            foreach ($visits as $v) {
                $events[] = $v;
            }

            // 2. Follow-ups
            $fTidSql = $tid > 1 ? " AND f.tenant_id = ?" : "";
            $fParams = [$userId];
            if ($tid > 1) $fParams[] = $tid;

            $followups = $db->fetchAll("
                SELECT f.id, f.followup_date as event_date, COALESCE(f.followup_time, '10:00:00') as event_time, 'task' as event_type,
                       CONCAT('Follow-up: ', COALESCE(l.name, 'Lead')) as title,
                       'medium' as priority, f.status, COALESCE(f.notes, '') as notes
                FROM followups f
                JOIN leads l ON l.id = f.lead_id
                WHERE f.assigned_to = ?{$fTidSql}
                ORDER BY f.followup_date ASC
            ", $fParams) ?: [];
            foreach ($followups as $f) {
                $events[] = $f;
            }

            // Sort combined events
            usort($events, function($a, $b) {
                $dtA = ($a['event_date'] ?? '') . ' ' . ($a['event_time'] ?? '00:00:00');
                $dtB = ($b['event_date'] ?? '') . ' ' . ($b['event_time'] ?? '00:00:00');
                return strcmp($dtA, $dtB);
            });
        } catch (\Throwable $e) {
            error_log('SiteVisitController::schedule error: ' . $e->getMessage());
        }

        $month = (int)($_GET['month'] ?? date('m'));
        $year = (int)($_GET['year'] ?? date('Y'));

        $this->render('associate/schedule', [
            'page_title' => 'My Schedule - Associate Portal',
            'events' => $events,
            'month' => $month,
            'year' => $year,
        ], 'layouts/associate');
    }

    /**
     * Schedule site visit (GET form & POST submission)
     */
    public function scheduleSiteVisit()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            try {
                $db = \App\Core\Database\Database::getInstance();
                $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = " . (int)$tid : "";

                $leads = $db->fetchAll("
                    SELECT id, name, phone, email, status
                    FROM leads
                    WHERE (assigned_to = ? OR created_by = ?){$tidSql}
                    ORDER BY name ASC
                ", [$userId, $userId]) ?: [];

                $colonies = $db->fetchAll("
                    SELECT id, name, location
                    FROM colonies
                    WHERE status = 'active'{$tidSql}
                    ORDER BY name ASC
                ") ?: [];

                $selectedLead = (int)($_GET['lead_id'] ?? 0);

                return $this->render('associate/schedule_site_visit', [
                    'page_title' => 'Schedule Site Visit - Associate Portal',
                    'leads' => $leads,
                    'colonies' => $colonies,
                    'selected_lead' => $selectedLead,
                ], 'layouts/associate');
            } catch (\Throwable $e) {
                error_log('AssociateSiteVisitController::scheduleSiteVisit GET error: ' . $e->getMessage());
                $this->redirect('/associate/site-visits');
                return;
            }
        }

        try {
            $db = \App\Core\Database\Database::getInstance();

            $visitorName = trim($_POST['visitor_name'] ?? '');
            $visitorPhone = trim($_POST['visitor_phone'] ?? '');
            $visitDate = trim($_POST['visit_date'] ?? '');
            $visitTime = trim($_POST['visit_time'] ?? '10:00:00');
            $duration = (int)($_POST['duration'] ?? 60);
            $colonyId = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;
            $plotId = !empty($_POST['plot_id']) ? (int)$_POST['plot_id'] : null;
            $leadId = !empty($_POST['lead_id']) ? (int)$_POST['lead_id'] : null;
            $customerId = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
            $notes = trim($_POST['notes'] ?? '');

            // Auto-fill from lead if selected
            if ($leadId && (empty($visitorName) || empty($visitorPhone))) {
                $lead = $db->fetchOne("SELECT name, phone FROM leads WHERE id = ? LIMIT 1", [$leadId]);
                if ($lead) {
                    if (empty($visitorName)) $visitorName = $lead['name'] ?? '';
                    if (empty($visitorPhone)) $visitorPhone = $lead['phone'] ?? '';
                }
            }

            if (empty($visitorName) && empty($customerId)) {
                throw new Exception('Visitor or Customer name is required');
            }
            if (empty($visitorPhone) && empty($customerId)) {
                throw new Exception('Phone number is required');
            }
            if (empty($visitDate)) {
                throw new Exception('Visit date is required');
            }

            // Resolve linked user if visitor phone matches
            $userIdForVisit = $customerId;
            if ($userIdForVisit === null && $visitorPhone !== '') {
                $matchedUser = $db->fetchOne("SELECT id, name FROM users WHERE phone = ? LIMIT 1", [$visitorPhone]);
                if ($matchedUser) {
                    $userIdForVisit = (int)$matchedUser['id'];
                    if ($visitorName === '') {
                        $visitorName = (string)$matchedUser['name'];
                    }
                }
            }

            $data = [
                'agent_id' => $userId,
                'user_id' => $userIdForVisit,
                'lead_id' => $leadId,
                'colony_id' => $colonyId,
                'plot_id' => $plotId,
                'visitor_name' => $visitorName,
                'visitor_phone' => $visitorPhone,
                'visit_date' => $visitDate,
                'visit_time' => $visitTime,
                'duration_minutes' => $duration,
                'status' => 'scheduled',
                'notes' => $notes,
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $db->insert('site_visits', $data);
            $visitId = (int)$db->lastInsertId();

            if ($leadId) {
                try {
                    $db->insert('lead_activities', [
                        'lead_id' => $leadId,
                        'activity_type' => 'site_visit_scheduled',
                        'description' => "Site visit scheduled for {$visitDate} {$visitTime}",
                        'created_by' => $userId,
                        'tenant_id' => $tid,
                    ]);
                } catch (\Throwable $e) {
                    error_log('lead_activities insert skipped: ' . $e->getMessage());
                }
            }

            $this->logActivity('site_visit_scheduled', json_encode(['visit_id' => $visitId]));

            $_SESSION['success'] = 'Site visit scheduled successfully!';
            $this->redirect('/associate/site-visits');
        } catch (\Throwable $e) {
            error_log('AssociateSiteVisitController::scheduleSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to schedule: ' . $e->getMessage();
            $this->redirect('/associate/site-visits/schedule');
        }
    }

    /**
     * Complete site visit
     */
    public function completeSiteVisit($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/site-visits');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$id, $userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $feedback = trim($_POST['feedback'] ?? '');
            $rating = (int)($_POST['rating'] ?? 5);
            if ($rating < 1 || $rating > 5) $rating = 5;

            $stmt = $db->prepare("UPDATE site_visits SET status = 'completed', feedback = ?, rating = ?, completed_at = NOW() WHERE id = ? AND agent_id = ?{$tidSql}");
            $params = array_merge([$feedback, $rating, $id, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                // Advance the linked lead to site_visit (never demote, never touch closed_*).
                // A completed visit is also a definite response — stamp the SLA clock.
                $visit = $db->fetchOne("SELECT lead_id FROM site_visits WHERE id = ?", [$id]);
                if (!empty($visit['lead_id'])) {
                    $lParams = [$visit['lead_id'], $userId, $userId];
                    if (TenantContext::getId() > 1) $lParams[] = TenantContext::getId();
                    $lead = $db->fetchOne("SELECT id, status FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} LIMIT 1", $lParams);
                    if ($lead && in_array($lead['status'], ['new', 'contacted', 'qualified'], true)) {
                        $db->execute("UPDATE leads SET status = 'site_visit', updated_at = NOW() WHERE id = ?", [$lead['id']]);
                        $db->insert('lead_activities', [
                            'lead_id' => $lead['id'],
                            'activity_type' => 'status_change',
                            'description' => 'Site visit completed',
                            'old_value' => $lead['status'],
                            'new_value' => 'site_visit',
                            'created_by' => $userId,
                            'tenant_id' => $tid,
                        ]);
                    }
                    $db->execute("UPDATE leads SET first_response_at = NOW() WHERE id = ? AND first_response_at IS NULL", [$visit['lead_id']]);
                }

                // Visit loop: thank-you draft + NOC hint for Sahil (AI employee)
                if (!empty($visit['lead_id'])) {
                    try {
                        $sahil = $db->fetchOne("SELECT id FROM ai_employees WHERE name = 'Sahil' AND status = 'active' LIMIT 1");
                        if ($sahil) {
                            $db->execute(
                                "INSERT INTO ai_employee_tasks (employee_id, type, lead_id, payload, status) VALUES (?, 'followup_draft', ?, ?, 'pending')",
                                [(int) $sahil['id'], (int) $visit['lead_id'], json_encode(['visit_id' => (int) $id, 'reason' => 'visit_completed', 'noc_hint' => true])]
                            );
                        }
                    } catch (\Throwable $e) {
                        error_log('Visit loop enqueue skipped: ' . $e->getMessage());
                    }
                }
                $this->logActivity('site_visit_completed', json_encode(['visit_id' => $id]));
                $_SESSION['success'] = 'Site visit marked as completed!';
            } else {
                $_SESSION['error'] = 'Site visit not found or access denied';
            }

            $this->redirect('/associate/site-visits');
        } catch (\Throwable $e) {
            error_log('AssociateSiteVisitController::completeSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to complete: ' . $e->getMessage();
            $this->redirect('/associate/site-visits');
        }
    }

    /**
     * Cancel site visit
     */
    public function cancelSiteVisit($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/site-visits');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$id, $userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $reason = trim($_POST['reason'] ?? '');

            // Extra $reason param had no placeholder (HY093: every cancel 500'd) —
            // reason now persists into notes.
            $stmt = $db->prepare("UPDATE site_visits SET status = 'cancelled', notes = ? WHERE id = ? AND agent_id = ?{$tidSql}");
            $params = array_merge([$reason, $id, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $this->logActivity('site_visit_cancelled', json_encode(['visit_id' => $id, 'reason' => $reason]));
                $_SESSION['success'] = 'Site visit cancelled!';
            } else {
                $_SESSION['error'] = 'Site visit not found or access denied';
            }

            $this->redirect('/associate/site-visits');
        } catch (\Throwable $e) {
            error_log('AssociateSiteVisitController::cancelSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to cancel: ' . $e->getMessage();
            $this->redirect('/associate/site-visits');
        }
    }

    /**
     * Reschedule site visit
     */
    public function rescheduleSiteVisit($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/site-visits');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$id, $userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $newDate = $_POST['new_date'] ?? '';
            $newTime = $_POST['new_time'] ?? '10:00:00';

            if (empty($newDate)) throw new Exception('New date is required');

            $stmt = $db->prepare("UPDATE site_visits SET visit_date = ?, visit_time = ?, status = 'rescheduled', rescheduled_at = NOW() WHERE id = ? AND agent_id = ?{$tidSql}");
            $params = array_merge([$newDate, $newTime, $id, $userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $this->logActivity('site_visit_rescheduled', json_encode(['visit_id' => $id, 'new_date' => $newDate]));
                $_SESSION['success'] = 'Site visit rescheduled!';
            } else {
                $_SESSION['error'] = 'Site visit not found or access denied';
            }

            $this->redirect('/associate/site-visits');
        } catch (\Throwable $e) {
            error_log('AssociateSiteVisitController::rescheduleSiteVisit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to reschedule: ' . $e->getMessage();
            $this->redirect('/associate/site-visits');
        }
    }

    /**
     * Calendar data for site visits
     */
    public function calendarData()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');

        $params = array_merge([$userId, $start, $end], $tid > 1 ? [$tid] : []);
        try {
            $siteVisits = $db->fetchAll("
                SELECT sv.id, sv.visit_date, sv.visit_time, sv.status, pl.plot_number, u.name as customer_name
                FROM site_visits sv
                JOIN plots pl ON pl.id = sv.plot_id
                LEFT JOIN users u ON u.id = sv.user_id
                WHERE sv.agent_id = ? AND sv.visit_date BETWEEN ? AND ?{$tidSql}
            ", $params) ?: [];
        } catch (\Throwable $e) {
            error_log('SiteVisitController::calendarData error: ' . $e->getMessage());
            $siteVisits = [];
        }

        $events = [];
        foreach ($siteVisits as $sv) {
            $color = match($sv['status']) {
                'scheduled' => '#3b82f6',
                'completed' => '#22c55e',
                'cancelled' => '#ef4444',
                'rescheduled' => '#f59e0b',
                default => '#6b7280'
            };
            $events[] = [
                'id' => $sv['id'],
                'title' => $sv['plot_number'] . ' - ' . $sv['customer_name'],
                'start' => $sv['visit_date'] . 'T' . $sv['visit_time'],
                'color' => $color,
                'extendedProps' => [
                    'status' => $sv['status'],
                    'customer' => $sv['customer_name'],
                    'plot' => $sv['plot_number'],
                ],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }
}

