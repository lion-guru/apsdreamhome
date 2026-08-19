<?php
/**
 * CRMService — Full lead lifecycle management
 * Pipeline, scoring, assignment, follow-ups, interactions, analytics
 */

namespace App\Services;

use App\Core\Database;
use App\Services\TenantScopeService;
use \App\Traits\ServiceTenantTrait;

class CRMService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get tenant_id for current request. Returns null if isolation disabled.
     */
    private function tid(): ?int
    {
        return TenantScopeService::isolationEnabled() ? TenantScopeService::tenantId() : null;
    }

    // ─────────── Pipeline Stages ───────────────────────────────────────

    public function getPipelineStages($role = 'all') {
        try {
            $stmt = $this->db->query(
                "SELECT * FROM crm_pipeline_stages WHERE is_active = 1 AND (role = ? OR role = 'all') ORDER BY order_index ASC",
                [$role]
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return $this->getDefaultStages();
        }
    }

    private function getDefaultStages() {
        return [
            ['id'=>1,'name'=>'New Lead','slug'=>'new','color'=>'#10b981','order_index'=>1],
            ['id'=>2,'name'=>'Contacted','slug'=>'contacted','color'=>'#3b82f6','order_index'=>2],
            ['id'=>3,'name'=>'Qualified','slug'=>'qualified','color'=>'#14b8a6','order_index'=>3],
            ['id'=>4,'name'=>'Site Visit','slug'=>'site_visit','color'=>'#f59e0b','order_index'=>4],
            ['id'=>5,'name'=>'Proposal','slug'=>'proposal','color'=>'#ec4899','order_index'=>5],
            ['id'=>6,'name'=>'Negotiation','slug'=>'negotiation','color'=>'#ef4444','order_index'=>6],
            ['id'=>7,'name'=>'Booking','slug'=>'booking','color'=>'#06b6d4','order_index'=>7],
            ['id'=>8,'name'=>'Closed Won','slug'=>'won','color'=>'#22c55e','order_index'=>8],
            ['id'=>9,'name'=>'Closed Lost','slug'=>'lost','color'=>'#64748b','order_index'=>9],
            ['id'=>10,'name'=>'Nurture','slug'=>'nurture','color'=>'#f97316','order_index'=>10],
        ];
    }

    // ─────────── Lead CRUD ─────────────────────────────────────────────

    public function getLeads($filters = [], $userId = null, $role = 'admin') {
        try {
            $where = ["l.deleted_at IS NULL"];
            $params = [];
            if ($tid = $this->tid()) { $where[] = "l.tenant_id = ?"; $params[] = $tid; }

            // Phase 2: Role-based visibility filter
            if ($userId && !in_array($role, ['admin', 'super_admin'])) {
                if ($role === 'manager') {
                    $where[] = "l.assigned_to IN (SELECT id FROM users WHERE reports_to = ?)";
                    $params[] = $userId;
                } else {
                    $where[] = "l.assigned_to = ?";
                    $params[] = $userId;
                }
            }

            if (!empty($filters['search'])) {
                $where[] = "(l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ? OR l.company LIKE ?)";
                $s = '%' . $filters['search'] . '%';
                $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
            }
            if (!empty($filters['status'])) {
                $where[] = "l.status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['source'])) {
                $where[] = "l.source = ?";
                $params[] = $filters['source'];
            }
            if (!empty($filters['assigned_to'])) {
                $where[] = "l.assigned_to = ?";
                $params[] = $filters['assigned_to'];
            }
            if (!empty($filters['priority'])) {
                $where[] = "l.priority = ?";
                $params[] = $filters['priority'];
            }
            if (!empty($filters['lead_category'])) {
                $where[] = "l.lead_category = ?";
                $params[] = $filters['lead_category'];
            }
            if (!empty($filters['min_score'])) {
                $where[] = "l.lead_score >= ?";
                $params[] = (int)$filters['min_score'];
            }
            if (!empty($filters['date_from'])) {
                $where[] = "l.created_at >= ?";
                $params[] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = "l.created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
            }
            if (isset($filters['is_converted']) && $filters['is_converted'] !== null) {
                $where[] = "l.is_converted = ?";
                $params[] = $filters['is_converted'] ? 1 : 0;
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $page = max(1, $filters['page'] ?? 1);
            $perPage = min(100, max(10, $filters['per_page'] ?? 25));
            $offset = ($page - 1) * $perPage;
            $orderBy = $filters['sort'] ?? 'l.created_at';
            $orderDir = strtoupper($filters['direction'] ?? 'DESC');
            if (!in_array($orderDir, ['ASC', 'DESC'])) $orderDir = 'DESC';
            $allowedSort = ['l.created_at','l.updated_at','l.lead_score','l.name','l.status','l.source','l.priority','l.budget'];
            if (!in_array($orderBy, $allowedSort)) $orderBy = 'l.created_at';

            $countStmt = $this->db->query("SELECT COUNT(*) as total FROM leads l $whereClause", $params);
            $total = (int)($countStmt->fetch()['total'] ?? 0);

            $sql = "SELECT l.*, 
                    u.name as assigned_to_name,
                    (SELECT COUNT(*) FROM crm_interactions WHERE lead_id = l.id) as interaction_count,
                    (SELECT created_at FROM crm_interactions WHERE lead_id = l.id ORDER BY created_at DESC LIMIT 1) as last_interaction,
                    (SELECT COUNT(*) FROM crm_tasks WHERE lead_id = l.id AND status = 'pending') as pending_tasks
                FROM leads l
                LEFT JOIN users u ON u.id = l.assigned_to
                $whereClause
                ORDER BY $orderBy $orderDir
                LIMIT $offset, $perPage";

            $stmt = $this->db->query($sql, $params);
            $leads = $stmt->fetchAll() ?: [];

            return [
                'leads' => $leads,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int)ceil($total / $perPage),
            ];
        } catch (\Exception $e) {
            error_log('CRMService::getLeads error: ' . $e->getMessage());
            return ['leads' => [], 'total' => 0, 'page' => 1, 'per_page' => 25, 'total_pages' => 0];
        }
    }

    public function getLeadById($id) {
        try {
            $where = "l.id = ?";
            $params = [$id];
            if ($tid = $this->tid()) { $where .= " AND l.tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->query(
                "SELECT l.*, u.name as assigned_to_name, c.name as created_by_name
                 FROM leads l
                 LEFT JOIN users u ON u.id = l.assigned_to
                 LEFT JOIN users c ON c.id = l.created_by
                 WHERE $where", $params
            );
            $lead = $stmt->fetch();
            if (!$lead) return null;

            $lead['interactions'] = $this->getLeadInteractions($id, 50);
            $lead['tasks'] = $this->getLeadTasks($id);
            $lead['assignments'] = $this->getLeadAssignments($id);
            $lead['source_details'] = $this->getLeadSourceDetails($id);
            $lead['score_history'] = $this->getLeadScoreHistory($id);
            return $lead;
        } catch (\Exception $e) {
            error_log('CRMService::getLeadById error: ' . $e->getMessage());
            return null;
        }
    }

    public function createLead($data) {
        $guard = \App\Services\CRMGuard::getInstance();
        if (!$guard->isCrmEnabled()) {
            return ['success' => false, 'error' => 'CRM is currently disabled by administrator'];
        }
        $role = $data['creator_role'] ?? ($data['role'] ?? 'admin');
        if (!$guard->canCreateLead($role)) {
            return ['success' => false, 'error' => 'Your role does not have permission to create leads'];
        }

        // Tenant plan enforcement
        $tid = $this->tid();
        if ($tid) {
            $enforcement = \App\Services\TenantEnforcement::getInstance();
            $check = $enforcement->canPerform($tid, 'create_lead');
            if (!$check['allowed']) {
                return ['success' => false, 'error' => $check['reason']];
            }
        }

        try {
            $leadNumber = 'CR-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $tid = $this->tid();

            // Sanitize lead data to prevent stored XSS
            $s = function($v) { return \App\Core\Security::sanitize($v ?? ''); };
            $stmt = $this->db->query(
                "INSERT INTO leads (lead_number, name, email, phone, company, address, city, state, pincode,
                 source, property_interest, budget, budget_range, location_preference, notes, tags,
                 assigned_to, created_by, status, priority, lead_score, lead_category" . ($tid ? ", tenant_id" : "") . ")
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', ?, ?, ?" . ($tid ? ", ?" : "") . ")",
                [
                    $leadNumber,
                    $s($data['name']),
                    $data['email'] ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : null,
                    $data['phone'] ? preg_replace('/[^0-9+\-\s()]/', '', $data['phone']) : null,
                    $s($data['company']),
                    $s($data['address']),
                    $s($data['city']),
                    $s($data['state']),
                    $s($data['pincode']),
                    $s($data['source']) ?: 'website',
                    $s($data['property_interest']),
                    (float)($data['budget'] ?? 0),
                    $s($data['budget_range']),
                    $s($data['location_preference']),
                    $s($data['notes']),
                    $s($data['tags']),
                    (int)($data['assigned_to'] ?? 0) ?: null,
                    (int)($data['created_by'] ?? 0) ?: null,
                    $s($data['priority']) ?: 'medium',
                    (int)($data['lead_score'] ?? 0),
                    $s($data['lead_category']) ?: 'cold',
                    ...($tid ? [$tid] : []),
                ]
            );
            $leadId = $this->db->lastInsertId();

            // Log assignment if assigned
            if (!empty($data['assigned_to'])) {
                $this->logAssignment($leadId, null, $data['assigned_to'], $data['created_by'] ?? null, 'Auto-assigned on creation');
            }

            // Log system interaction
            $this->addInteraction($leadId, $data['created_by'] ?? 1, 'system', [
                'subject' => 'Lead created',
                'body' => 'Lead created from ' . ($data['source'] ?? 'website'),
                'outcome' => null,
            ]);

            // Source tracking
            if (!empty($data['source_type'])) {
                $this->addSourceDetail($leadId, $data);
            }

            // Phase 3: Auto-route lead if no manual assignment
            if (empty($data['assigned_to'])) {
                try {
                    $routingService = new LeadRoutingService();
                    $routingService->routeLead($leadId);
                } catch (\Exception $routeEx) {
                    error_log('CRMService::createLead routing error: ' . $routeEx->getMessage());
                }
            }

            // Track tenant usage
            if ($tid) {
                try {
                    \App\Services\TenantService::getInstance()->incrementUsage($tid, 'leads_created');
                } catch (\Throwable $useEx) {
                    error_log('CRMService::createLead usage tracking error: ' . $useEx->getMessage());
                }
            }

            return ['success' => true, 'lead_id' => $leadId, 'lead_number' => $leadNumber];
        } catch (\Exception $e) {
            error_log('CRMService::createLead error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateLead($id, $data) {
        try {
            $fields = [];
            $params = [];
            $allowed = ['name','email','phone','company','address','city','state','pincode','source',
                         'property_interest','budget','budget_range','location_preference','notes','tags',
                         'assigned_to','status','priority','lead_score','lead_category','is_converted',
                         'conversion_probability','total_purchase_value'];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) {
                    $fields[] = "$f = ?";
                    $params[] = $data[$f];
                }
            }
            if (empty($fields)) return ['success' => false, 'error' => 'No fields to update'];
            $params[] = $id;
            $whereClause = "id = ?";
            if ($tid = $this->tid()) { $whereClause .= " AND tenant_id = ?"; $params[] = $tid; }

            $this->db->query("UPDATE leads SET " . implode(', ', $fields) . " WHERE $whereClause", $params);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('CRMService::updateLead error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteLead($id, string $role = 'admin') {
        $guard = \App\Services\CRMGuard::getInstance();
        if (!$guard->isCrmEnabled()) {
            return ['success' => false, 'error' => 'CRM is currently disabled by administrator'];
        }
        if (!$guard->canDeleteLead($role)) {
            return ['success' => false, 'error' => 'Your role does not have permission to delete leads'];
        }
        try {
            $where = "id = ?";
            $params = [$id];
            if ($tid = $this->tid()) { $where .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->query("UPDATE leads SET deleted_at = NOW() WHERE $where", $params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function restoreLead($id) {
        try {
            $where = "id = ?";
            $params = [$id];
            if ($tid = $this->tid()) { $where .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->query("UPDATE leads SET deleted_at = NULL WHERE $where", $params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getDeletedLeads($filters = []) {
        try {
            $page = max(1, (int)($filters['page'] ?? 1));
            $perPage = max(1, min(100, (int)($filters['per_page'] ?? 25)));
            $offset = ($page - 1) * $perPage;

            $where = ["l.deleted_at IS NOT NULL"];
            $params = [];
            if ($tid = $this->tid()) { $where[] = "l.tenant_id = ?"; $params[] = $tid; }

            if (!empty($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $where[] = "(l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }

            $whereSql = implode(' AND ', $where);

            $countResult = $this->db->query("SELECT COUNT(*) as total FROM leads l WHERE $whereSql", $params)->fetch();
            $total = (int)($countResult['total'] ?? 0);
            $totalPages = max(1, (int)ceil($total / $perPage));

            $params[] = $perPage;
            $params[] = $offset;
            $leads = $this->db->query(
                "SELECT l.*, u.name as assigned_by_name, creator.name as created_by_name
                 FROM leads l
                 LEFT JOIN users u ON u.id = l.assigned_to
                 LEFT JOIN users creator ON creator.id = l.created_by
                 WHERE $whereSql
                 ORDER BY l.deleted_at DESC
                 LIMIT ? OFFSET ?",
                $params
            )->fetchAll() ?: [];

            return [
                'leads' => $leads,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $totalPages,
            ];
        } catch (\Exception $e) {
            error_log('CRMService::getDeletedLeads error: ' . $e->getMessage());
            return ['leads' => [], 'total' => 0, 'page' => 1, 'per_page' => 25, 'total_pages' => 1];
        }
    }

    public function permanentDeleteLead($id) {
        try {
            $where = "id = ? AND deleted_at IS NOT NULL";
            $params = [$id];
            if ($tid = $this->tid()) { $where .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->query("DELETE FROM leads WHERE $where", $params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────── Pipeline (Kanban) ─────────────────────────────────────

    public function getPipelineBoard($filters = []) {
        try {
            $stages = $this->getPipelineStages($filters['role'] ?? 'all');
            $board = [];

            foreach ($stages as $stage) {
                $where = ["l.deleted_at IS NULL", "l.status = ?"];
                $params = [$stage['slug']];
                if ($tid = $this->tid()) { $where[] = "l.tenant_id = ?"; $params[] = $tid; }

                if (!empty($filters['assigned_to'])) {
                    $where[] = "l.assigned_to = ?";
                    $params[] = $filters['assigned_to'];
                }
                if (!empty($filters['source'])) {
                    $where[] = "l.source = ?";
                    $params[] = $filters['source'];
                }

                $whereClause = implode(' AND ', $where);
                $stmt = $this->db->query(
                    "SELECT l.*, u.name as assigned_to_name,
                     (SELECT COUNT(*) FROM crm_interactions WHERE lead_id = l.id) as interaction_count
                     FROM leads l
                     LEFT JOIN users u ON u.id = l.assigned_to
                     WHERE $whereClause
                     ORDER BY l.lead_score DESC, l.created_at ASC
                     LIMIT 100",
                    $params
                );
                $leads = $stmt->fetchAll() ?: [];

                $board[] = [
                    'stage' => $stage,
                    'leads' => $leads,
                    'count' => count($leads),
                    'total_value' => array_sum(array_map(fn($l) => (float)($l['budget'] ?? 0), $leads)),
                ];
            }

            return $board;
        } catch (\Exception $e) {
            error_log('CRMService::getPipelineBoard error: ' . $e->getMessage());
            return [];
        }
    }

    public function moveLeadToStage($leadId, $newStatus, $userId = null) {
        try {
            $where = "id = ?";
            $params = [$leadId];
            if ($tid = $this->tid()) { $where .= " AND tenant_id = ?"; $params[] = $tid; }
            $old = $this->db->fetchOne("SELECT status FROM leads WHERE $where", $params);
            if (!$old) return ['success' => false, 'error' => 'Lead not found'];

            $this->db->query("UPDATE leads SET status = ? WHERE $where", array_merge([$newStatus], $params));

            $this->addInteraction($leadId, $userId, 'system', [
                'subject' => "Stage changed: {$old['status']} → {$newStatus}",
                'body' => "Lead moved from {$old['status']} to {$newStatus}",
            ]);

            return ['success' => true, 'old_status' => $old['status'], 'new_status' => $newStatus];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────── Interactions (Follow-ups) ─────────────────────────────

    public function addInteraction($leadId, $userId, $type, $data = []) {
        try {
            $cols = "lead_id, user_id, interaction_type, direction, subject, body, duration_seconds, outcome, next_action, next_action_date";
            $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
            $params = [
                $leadId,
                $userId,
                $type,
                $data['direction'] ?? 'outbound',
                $data['subject'] ?? null,
                $data['body'] ?? null,
                $data['duration_seconds'] ?? null,
                $data['outcome'] ?? null,
                $data['next_action'] ?? null,
                $data['next_action_date'] ?? null,
            ];
            if ($tid = $this->tid()) { $cols .= ", tenant_id"; $vals .= ", ?"; $params[] = $tid; }
            $stmt = $this->db->query(
                "INSERT INTO crm_interactions ($cols) VALUES ($vals)",
                $params
            );

            // Update lead's last_activity_date
            $actWhere = "id = ?"; $actParams = [$leadId];
            if ($tid = $this->tid()) { $actWhere .= " AND tenant_id = ?"; $actParams[] = $tid; }
            $this->db->query("UPDATE leads SET last_activity_date = NOW() WHERE $actWhere", $actParams);

            // Auto-score on interaction
            $this->recalculateScore($leadId);

            return ['success' => true, 'interaction_id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            error_log('CRMService::addInteraction error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getLeadInteractions($leadId, $limit = 50) {
        try {
            $where = "ci.lead_id = ?"; $params = [$leadId];
            if ($tid = $this->tid()) { $where .= " AND ci.tenant_id = ?"; $params[] = $tid; }
            $params[] = (int)$limit;
            $stmt = $this->db->query(
                "SELECT ci.*, u.name as user_name
                 FROM crm_interactions ci
                 LEFT JOIN users u ON u.id = ci.user_id
                 WHERE $where
                 ORDER BY ci.created_at DESC
                 LIMIT ?",
                $params
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getMyInteractions($userId, $limit = 20) {
        try {
            $where = "ci.user_id = ?"; $params = [$userId];
            if ($tid = $this->tid()) { $where .= " AND ci.tenant_id = ?"; $params[] = $tid; }
            $params[] = (int)$limit;
            $stmt = $this->db->query(
                "SELECT ci.*, l.name as lead_name, l.phone as lead_phone
                 FROM crm_interactions ci
                 LEFT JOIN leads l ON l.id = ci.lead_id
                 WHERE $where
                 ORDER BY ci.created_at DESC
                 LIMIT ?",
                $params
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────── Tasks (Follow-up scheduling) ──────────────────────────

    public function createTask($data) {
        try {
            $cols = "lead_id, assigned_to, created_by, task_type, title, description, priority, status, due_date, due_time, reminder_at";
            $vals = "?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?";
            $params = [
                $data['lead_id'] ?? null,
                $data['assigned_to'],
                $data['created_by'] ?? $data['assigned_to'],
                $data['task_type'] ?? 'follow_up',
                $data['title'],
                $data['description'] ?? null,
                $data['priority'] ?? 'medium',
                $data['due_date'],
                $data['due_time'] ?? null,
                $data['reminder_at'] ?? null,
            ];
            if ($tid = $this->tid()) { $cols .= ", tenant_id"; $vals .= ", ?"; $params[] = $tid; }
            $stmt = $this->db->query(
                "INSERT INTO crm_tasks ($cols) VALUES ($vals)",
                $params
            );
            return ['success' => true, 'task_id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getLeadTasks($leadId) {
        try {
            $where = "ct.lead_id = ?"; $params = [$leadId];
            if ($tid = $this->tid()) { $where .= " AND ct.tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->query(
                "SELECT ct.*, u.name as assigned_to_name
                 FROM crm_tasks ct
                 LEFT JOIN users u ON u.id = ct.assigned_to
                 WHERE $where
                 ORDER BY ct.due_date ASC, ct.due_time ASC",
                $params
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getMyTasks($userId, $status = 'pending', $date = null) {
        try {
            $where = ["ct.assigned_to = ?"];
            $params = [$userId];
            if ($tid = $this->tid()) { $where[] = "ct.tenant_id = ?"; $params[] = $tid; }
            if ($status && $status !== 'all') {
                $where[] = "ct.status = ?";
                $params[] = $status;
            }
            if ($date) {
                $where[] = "ct.due_date = ?";
                $params[] = $date;
            }

            $whereClause = implode(' AND ', $where);
            $stmt = $this->db->query(
                "SELECT ct.*, l.name as lead_name, l.phone as lead_phone, l.status as lead_status
                 FROM crm_tasks ct
                 LEFT JOIN leads l ON l.id = ct.lead_id
                 WHERE $whereClause
                 ORDER BY 
                    CASE ct.priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END,
                    ct.due_date ASC, ct.due_time ASC
                 LIMIT 100",
                $params
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function completeTask($taskId, $userId, $notes = null) {
        try {
            $where = "id = ? AND assigned_to = ?";
            $params = [$taskId, $userId];
            if ($tid = $this->tid()) { $where .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->query(
                "UPDATE crm_tasks SET status = 'completed', completed_at = NOW(), completed_notes = ? WHERE $where",
                array_merge([$notes], $params)
            );
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getOverdueTasks($userId = null) {
        try {
            $where = ["ct.status IN ('pending','in_progress')", "ct.due_date < CURDATE()"];
            $params = [];
            if ($userId) {
                $where[] = "ct.assigned_to = ?";
                $params[] = $userId;
            }
            if ($tid = $this->tid()) { $where[] = "ct.tenant_id = ?"; $params[] = $tid; }
            $whereClause = implode(' AND ', $where);
            $stmt = $this->db->query(
                "SELECT ct.*, l.name as lead_name, l.phone as lead_phone, u.name as assigned_to_name
                 FROM crm_tasks ct
                 LEFT JOIN leads l ON l.id = ct.lead_id
                 LEFT JOIN users u ON u.id = ct.assigned_to
                 WHERE $whereClause
                 ORDER BY ct.due_date ASC
                 LIMIT 50",
                $params
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────── Assignments ───────────────────────────────────────────

    public function assignLead($leadId, $assignedTo, $assignedBy, $reason = null, $notes = null) {
        try {
            $leadWhere = "id = ?";
            $leadParams = [$leadId];
            if ($tid = $this->tid()) { $leadWhere .= " AND tenant_id = ?"; $leadParams[] = $tid; }
            $old = $this->db->fetchOne("SELECT assigned_to FROM leads WHERE $leadWhere", $leadParams);
            $oldAssignee = $old['assigned_to'] ?? null;

            $this->db->query("UPDATE leads SET assigned_to = ? WHERE $leadWhere", array_merge([$assignedTo], $leadParams));
            $this->logAssignment($leadId, $oldAssignee, $assignedTo, $assignedBy, $reason, $notes);

            $this->addInteraction($leadId, $assignedBy, 'system', [
                'subject' => 'Lead assigned',
                'body' => "Lead assigned to user #$assignedTo" . ($reason ? " — $reason" : ''),
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function logAssignment($leadId, $from, $to, $by, $reason = null, $notes = null) {
        try {
            $cols = "lead_id, assigned_from, assigned_to, assigned_by, reason, notes";
            $vals = "?, ?, ?, ?, ?, ?";
            $params = [$leadId, $from, $to, $by, $reason, $notes];
            if ($tid = $this->tid()) { $cols .= ", tenant_id"; $vals .= ", ?"; $params[] = $tid; }
            $this->db->query(
                "INSERT INTO crm_assignments ($cols) VALUES ($vals)",
                $params
            );
        } catch (\Exception $e) {
            error_log('CRMService::logAssignment error: ' . $e->getMessage());
        }
    }

    public function getLeadAssignments($leadId) {
        try {
            $where = "ca.lead_id = ?";
            $params = [$leadId];
            if ($tid = $this->tid()) { $where .= " AND ca.tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->query(
                "SELECT ca.*, u1.name as from_name, u2.name as to_name, u3.name as by_name
                 FROM crm_assignments ca
                 LEFT JOIN users u1 ON u1.id = ca.assigned_from
                 LEFT JOIN users u2 ON u2.id = ca.assigned_to
                 LEFT JOIN users u3 ON u3.id = ca.assigned_by
                 WHERE $where
                 ORDER BY ca.created_at DESC",
                $params
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function autoAssignLeads($strategy = 'round_robin') {
        $guard = \App\Services\CRMGuard::getInstance();
        if (!$guard->isAutoAssignEnabled()) {
            return ['success' => false, 'error' => 'Auto-assignment is disabled'];
        }
        try {
            $assignWhere = "assigned_to IS NULL AND deleted_at IS NULL";
            $assignParams = [];
            if ($tid = $this->tid()) { $assignWhere .= " AND tenant_id = ?"; $assignParams[] = $tid; }
            $unassigned = $this->db->query(
                "SELECT id FROM leads WHERE $assignWhere ORDER BY created_at ASC LIMIT 50",
                $assignParams
            )->fetchAll() ?: [];

            if (empty($unassigned)) return ['success' => true, 'assigned' => 0];

            $agentWhere = "u.role IN ('agent','associate','employee')";
            $agentParams = [];
            if ($tid = $this->tid()) { $agentWhere .= " AND (l.tenant_id = ? OR l.tenant_id IS NULL)"; $agentParams[] = $tid; }
            $agents = $this->db->query(
                "SELECT u.id, COUNT(l.id) as lead_count
                 FROM users u
                 LEFT JOIN leads l ON l.assigned_to = u.id AND l.deleted_at IS NULL
                 WHERE $agentWhere
                 GROUP BY u.id
                 ORDER BY lead_count ASC
                 LIMIT 20",
                $agentParams
            )->fetchAll() ?: [];

            if (empty($agents)) return ['success' => false, 'error' => 'No agents available'];

            $assigned = 0;
            $agentIndex = 0;
            foreach ($unassigned as $lead) {
                $agent = $agents[$agentIndex % count($agents)];
                $this->assignLead($lead['id'], $agent['id'], 1, 'Auto-assigned (' . $strategy . ')');
                $assigned++;
                $agentIndex++;
            }

            return ['success' => true, 'assigned' => $assigned];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────── Scoring ───────────────────────────────────────────────

    public function recalculateScore($leadId) {
        $guard = \App\Services\CRMGuard::getInstance();
        if (!$guard->isScoringEnabled()) {
            return 0;
        }
        try {
            $leadWhere = "id = ?";
            $leadParams = [$leadId];
            if ($tid = $this->tid()) { $leadWhere .= " AND tenant_id = ?"; $leadParams[] = $tid; }
            $lead = $this->db->fetchOne("SELECT * FROM leads WHERE $leadWhere", $leadParams);
            if (!$lead) return 0;

            $score = 0;
            $factors = [];

            // Contact completeness
            if (!empty($lead['phone'])) { $score += 10; $factors['phone'] = 10; }
            if (!empty($lead['email'])) { $score += 5; $factors['email'] = 5; }
            if (!empty($lead['company'])) { $score += 5; $factors['company'] = 5; }

            // Budget indicator
            if ((float)$lead['budget'] > 0) { $score += 15; $factors['budget'] = 15; }
            if ((float)$lead['budget'] >= 1000000) { $score += 10; $factors['high_budget'] = 10; }

            // Location preference
            if (!empty($lead['location_preference'])) { $score += 5; $factors['location'] = 5; }
            if (!empty($lead['property_interest'])) { $score += 5; $factors['property_interest'] = 5; }

            // Source quality
            $sourceScores = ['referral' => 15, 'walk_in' => 20, 'call_in' => 15, 'website' => 8, 'google_ads' => 10, 'facebook_ads' => 7, 'other' => 3];
            $src = $lead['source'] ?? 'other';
            $score += $sourceScores[$src] ?? 3;
            $factors['source'] = $sourceScores[$src] ?? 3;

            // Interaction recency
            $interactionCount = (int)($this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM crm_interactions WHERE lead_id = ?", [$leadId]
            )['cnt'] ?? 0);
            if ($interactionCount >= 3) { $score += 15; $factors['engagement'] = 15; }
            elseif ($interactionCount >= 1) { $score += 8; $factors['engagement'] = 8; }

            // Recency (last activity within 7 days)
            if (!empty($lead['last_activity_date'])) {
                $daysSince = (int)((time() - strtotime($lead['last_activity_date'])) / 86400);
                if ($daysSince <= 1) { $score += 15; $factors['recency'] = 15; }
                elseif ($daysSince <= 3) { $score += 10; $factors['recency'] = 10; }
                elseif ($daysSince <= 7) { $score += 5; $factors['recency'] = 5; }
            }

            // Status progression
            $statusScores = ['new' => 0, 'contacted' => 5, 'qualified' => 10, 'site_visit' => 15, 'proposal' => 20, 'negotiation' => 25, 'booking' => 30, 'won' => 40, 'lost' => 0, 'nurture' => 3];
            $score += $statusScores[$lead['status']] ?? 0;
            $factors['status'] = $statusScores[$lead['status']] ?? 0;

            $score = min(100, $score);

            // Determine category
            $category = 'cold';
            if ($score >= 70) $category = 'hot';
            elseif ($score >= 40) $category = 'warm';
            elseif ($score >= 20) $category = 'lukewarm';

            // Store history
            $this->db->query(
                "INSERT INTO crm_lead_scores_history (lead_id, old_score, new_score, score_factors, scored_by, reason)
                 VALUES (?, ?, ?, ?, 'system', 'Auto-calculated')",
                [$leadId, $lead['lead_score'] ?? 0, $score, json_encode($factors)]
            );

            $this->db->query(
                "UPDATE leads SET lead_score = ?, lead_category = ?, score_factors = ?, last_scored_at = NOW(), conversion_probability = ? WHERE $leadWhere",
                array_merge([$score, $category, json_encode($factors), min(100, $score)], $leadParams)
            );

            return $score;
        } catch (\Exception $e) {
            error_log('CRMService::recalculateScore error: ' . $e->getMessage());
            return 0;
        }
    }

    public function rescoreAllLeads() {
        try {
            $where = "deleted_at IS NULL";
            $params = [];
            if ($tid = $this->tid()) { $where .= " AND tenant_id = ?"; $params[] = $tid; }
            $leads = $this->db->query("SELECT id FROM leads WHERE $where", $params)->fetchAll() ?: [];
            $count = 0;
            foreach ($leads as $lead) {
                $this->recalculateScore($lead['id']);
                $count++;
            }
            return ['success' => true, 'scored' => $count];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getLeadScoreHistory($leadId) {
        try {
            $where = "lead_id = ?"; $params = [$leadId];
            if ($tid = $this->tid()) { $where .= " AND tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->query(
                "SELECT * FROM crm_lead_scores_history WHERE $where ORDER BY created_at DESC LIMIT 20",
                $params
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────── Source Details ────────────────────────────────────────

    public function addSourceDetail($leadId, $data) {
        try {
            $this->db->query(
                "INSERT INTO crm_lead_sources_extended (lead_id, campaign_id, form_id, source_type, source_detail, medium,
                 utm_source, utm_medium, utm_campaign, utm_term, utm_content, gclid, fbclid, landing_page, referrer, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $leadId,
                    $data['campaign_id'] ?? null,
                    $data['form_id'] ?? null,
                    $data['source_type'] ?? 'website',
                    $data['source_detail'] ?? null,
                    $data['medium'] ?? null,
                    $data['utm_source'] ?? null,
                    $data['utm_medium'] ?? null,
                    $data['utm_campaign'] ?? null,
                    $data['utm_term'] ?? null,
                    $data['utm_content'] ?? null,
                    $data['gclid'] ?? null,
                    $data['fbclid'] ?? null,
                    $data['landing_page'] ?? null,
                    $data['referrer'] ?? null,
                    $data['ip_address'] ?? null,
                ]
            );
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false];
        }
    }

    public function getLeadSourceDetails($leadId) {
        try {
            $stmt = $this->db->query(
                "SELECT cles.*, crm.name as campaign_name
                 FROM crm_lead_sources_extended cles
                 LEFT JOIN crm_campaigns crm ON crm.id = cles.campaign_id
                 WHERE cles.lead_id = ?
                 ORDER BY cles.created_at DESC LIMIT 5",
                [$leadId]
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────── Analytics / Dashboard ─────────────────────────────────

    public function getDashboardStats($userId = null, $role = 'admin') {
        try {
            $whereLead = $role !== 'admin' && $userId ? "AND l.assigned_to = $userId" : "";
            $whereTask = $role !== 'admin' && $userId ? "AND ct.assigned_to = $userId" : "";
            $tid = $this->tid();
            $tenantFilter = $tid ? "AND l.tenant_id = $tid" : "";
            $tenantFilterTask = $tid ? "AND ct.lead_id IN (SELECT id FROM leads WHERE tenant_id = $tid)" : "";

            $stats = [];

            // Lead counts by status
            $stmt = $this->db->query("SELECT l.status, COUNT(*) as cnt FROM leads l WHERE l.deleted_at IS NULL $whereLead $tenantFilter GROUP BY l.status");
            $stats['by_status'] = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $stats['by_status'][$row['status']] = (int)$row['cnt'];
            }
            $stats['total_leads'] = array_sum($stats['by_status']);

            // Today's leads
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM leads l WHERE DATE(l.created_at) = CURDATE() $whereLead $tenantFilter");
            $stats['today_leads'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // This week
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM leads l WHERE l.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) $whereLead $tenantFilter");
            $stats['week_leads'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Hot leads
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM leads l WHERE l.lead_score >= 70 AND l.deleted_at IS NULL $whereLead $tenantFilter");
            $stats['hot_leads'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Converted
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM leads l WHERE l.is_converted = 1 $whereLead $tenantFilter");
            $stats['converted'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Conversion rate
            $stats['conversion_rate'] = $stats['total_leads'] > 0
                ? round(($stats['converted'] / $stats['total_leads']) * 100, 1)
                : 0;

            // Pending tasks
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM crm_tasks ct WHERE ct.status IN ('pending','in_progress') $whereTask $tenantFilterTask");
            $stats['pending_tasks'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Overdue tasks
            $stats['overdue_tasks'] = count($this->getOverdueTasks($userId));

            // Interactions today
            $tenantFilterInter = $tid ? "AND ci.tenant_id = $tid" : "";
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM crm_interactions ci WHERE DATE(ci.created_at) = CURDATE() $tenantFilterInter");
            $stats['today_interactions'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Interactions this week
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM crm_interactions ci WHERE ci.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) $tenantFilterInter");
            $stats['week_interactions'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Lead sources breakdown
            $stmt = $this->db->query("SELECT source, COUNT(*) as cnt FROM leads l WHERE l.deleted_at IS NULL $whereLead $tenantFilter GROUP BY source ORDER BY cnt DESC");
            $stats['by_source'] = $stmt->fetchAll() ?: [];

            // Score distribution
            $stmt = $this->db->query("SELECT lead_category, COUNT(*) as cnt FROM leads l WHERE l.deleted_at IS NULL $whereLead $tenantFilter GROUP BY lead_category");
            $stats['by_category'] = $stmt->fetchAll() ?: [];

            // Top assignees
            if ($role === 'admin') {
                $stmt = $this->db->query(
                    "SELECT u.name, l.assigned_to, COUNT(*) as lead_count,
                     SUM(CASE WHEN l.status IN ('won','booking') THEN 1 ELSE 0 END) as won_count
                     FROM leads l JOIN users u ON u.id = l.assigned_to
                     WHERE l.deleted_at IS NULL AND l.assigned_to IS NOT NULL $tenantFilter
                     GROUP BY l.assigned_to ORDER BY lead_count DESC LIMIT 10"
                );
                $stats['top_assignees'] = $stmt->fetchAll() ?: [];
            }

            // 7-day trend
            $stmt = $this->db->query(
                "SELECT DATE(l.created_at) as date, COUNT(*) as cnt
                 FROM leads l
                 WHERE l.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) $whereLead $tenantFilter
                 GROUP BY DATE(l.created_at) ORDER BY date ASC"
            );
            $stats['weekly_trend'] = $stmt->fetchAll() ?: [];

            return $stats;
        } catch (\Exception $e) {
            error_log('CRMService::getDashboardStats error: ' . $e->getMessage());
            return [];
        }
    }

    // ─────────── Campaigns ─────────────────────────────────────────────

    public function getCampaigns() {
        try {
            $where = ""; $params = [];
            if ($tid = $this->tid()) { $where = " WHERE tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->query("SELECT * FROM crm_campaigns $where ORDER BY created_at DESC", $params);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createCampaign($data) {
        try {
            $cols = "name, campaign_type, platform, budget, target_audience, target_locations, start_date, end_date, landing_page_url, status, created_by";
            $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
            $params = [
                $data['name'],
                $data['campaign_type'] ?? 'other',
                $data['platform'] ?? null,
                $data['budget'] ?? 0,
                $data['target_audience'] ?? null,
                $data['target_locations'] ?? null,
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $data['landing_page_url'] ?? null,
                $data['status'] ?? 'draft',
                $data['created_by'] ?? null,
            ];
            if ($tid = $this->tid()) { $cols .= ", tenant_id"; $vals .= ", ?"; $params[] = $tid; }
            $stmt = $this->db->query(
                "INSERT INTO crm_campaigns ($cols) VALUES ($vals)",
                $params
            );
            return ['success' => true, 'campaign_id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────── Forms ─────────────────────────────────────────────────

    public function getForms() {
        try {
            $where = ""; $params = [];
            if ($tid = $this->tid()) { $where = " WHERE tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->query("SELECT * FROM crm_lead_forms $where ORDER BY created_at DESC", $params);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function submitForm($formCode, $data, $meta = []) {
        try {
            $where = "form_code = ? AND is_active = 1"; $fParams = [$formCode];
            if ($tid = $this->tid()) { $where .= " AND tenant_id = ?"; $fParams[] = $tid; }
            $form = $this->db->fetchOne("SELECT * FROM crm_lead_forms WHERE $where", $fParams);
            if (!$form) return ['success' => false, 'error' => 'Form not found'];

            // Create lead
            $leadResult = $this->createLead([
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'budget' => $data['budget'] ?? 0,
                'budget_range' => $data['budget_range'] ?? null,
                'location_preference' => $data['location'] ?? $data['location_preference'] ?? null,
                'property_interest' => $data['property_type'] ?? $data['property_interest'] ?? null,
                'notes' => $data['message'] ?? $data['notes'] ?? null,
                'source' => $form['source_tag'] ?? 'website',
                'assigned_to' => $form['default_assign_to'],
                'created_by' => null,
            ]);

            if (!$leadResult['success']) return $leadResult;

            // Log form submission (sanitize UTM and page fields to prevent stored XSS)
            $sanitizedMeta = [
                'utm_source'    => \App\Core\Security::sanitize($meta['utm_source'] ?? ''),
                'utm_medium'    => \App\Core\Security::sanitize($meta['utm_medium'] ?? ''),
                'utm_campaign'  => \App\Core\Security::sanitize($meta['utm_campaign'] ?? ''),
                'page_url'      => filter_var($meta['page_url'] ?? '', FILTER_SANITIZE_URL),
                'device_type'   => \App\Core\Security::sanitize($meta['device_type'] ?? ''),
            ];
            $this->db->query(
                "INSERT INTO crm_form_submissions (form_id, lead_id, submitted_data, ip_address, user_agent, utm_source, utm_medium, utm_campaign, page_url, device_type)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $form['id'],
                    $leadResult['lead_id'],
                    json_encode($data),
                    $meta['ip'] ?? null,
                    $meta['user_agent'] ?? null,
                    $sanitizedMeta['utm_source'],
                    $sanitizedMeta['utm_medium'],
                    $sanitizedMeta['utm_campaign'],
                    $sanitizedMeta['page_url'],
                    $sanitizedMeta['device_type'],
                ]
            );

            // Update submission count
            $ucWhere = "id = ?"; $ucParams = [$form['id']];
            if ($tid = $this->tid()) { $ucWhere .= " AND tenant_id = ?"; $ucParams[] = $tid; }
            $this->db->query("UPDATE crm_lead_forms SET submission_count = submission_count + 1 WHERE $ucWhere", $ucParams);

            // Add source detail (using already-sanitized meta)
            $this->addSourceDetail($leadResult['lead_id'], [
                'source_type' => $form['source_tag'] ?? 'website',
                'form_id' => $form['id'],
                'utm_source' => $sanitizedMeta['utm_source'],
                'utm_medium' => $sanitizedMeta['utm_medium'],
                'utm_campaign' => $sanitizedMeta['utm_campaign'],
                'landing_page' => $sanitizedMeta['page_url'],
                'ip_address' => $meta['ip'] ?? null,
            ]);

            return [
                'success' => true,
                'lead_id' => $leadResult['lead_id'],
                'lead_number' => $leadResult['lead_number'],
                'message' => $form['thank_you_message'],
            ];
        } catch (\Exception $e) {
            error_log('CRMService::submitForm error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────── CSV Lead Import ──────────────────────────────────────

    public function importLeadsFromCsv(array $rows, int $userId): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $duplicates = 0;
        $tid = $this->tid();

        foreach ($rows as $idx => $row) {
            $name = trim($row['name'] ?? $row['Name'] ?? '');
            $phone = trim($row['phone'] ?? $row['Phone'] ?? $row['mobile'] ?? '');
            $email = trim($row['email'] ?? $row['Email'] ?? '');
            $source = trim($row['source'] ?? $row['Source'] ?? 'csv_import');
            $budget = (float)($row['budget'] ?? $row['Budget'] ?? 0);
            $notes = trim($row['notes'] ?? $row['Notes'] ?? '');

            if (!$name && !$phone && !$email) {
                $skipped++;
                $errors[] = "Row " . ($idx + 1) . ": No name, phone, or email";
                continue;
            }

            $leadNum = 'LEAD-CSV-' . date('Ymd') . '-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT);

            try {
                $existingPhone = $phone ? $this->db->query("SELECT id FROM leads WHERE phone = ?" . ($tid ? " AND tenant_id = $tid" : ""), $tid ? [$phone, $tid] : [$phone])->fetch() : null;
                $existingEmail = $email ? $this->db->query("SELECT id FROM leads WHERE email = ?" . ($tid ? " AND tenant_id = $tid" : ""), $tid ? [$email, $tid] : [$email])->fetch() : null;

                if ($existingPhone || $existingEmail) {
                    $duplicates++;
                    continue;
                }

                $this->db->query(
                    "INSERT INTO leads (lead_number, name, phone, email, source, budget_min, status, assigned_to, created_by, lead_score, created_at, updated_at" . ($tid ? ", tenant_id" : "") . ")
                     VALUES (?, ?, ?, ?, ?, ?, 'new', ?, ?, 0, NOW(), NOW()" . ($tid ? ", ?" : "") . ")",
                    array_merge([$leadNum, $name, $phone, $email, $source, $budget, $userId, $userId], $tid ? [$tid] : [])
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($idx + 1) . ": " . $e->getMessage();
            }
        }

        return [
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'duplicates' => $duplicates,
            'errors' => $errors,
            'total_rows' => count($rows),
        ];
    }

    // ─────────── Deal Pipeline ────────────────────────────────────────

    public function getDeals(array $filters = []): array
    {
        $where = "1=1";
        $params = [];
        if ($tid = $this->tid()) { $where .= " AND d.tenant_id = ?"; $params[] = $tid; }

        if (!empty($filters['stage'])) {
            $where .= " AND d.stage = ?";
            $params[] = $filters['stage'];
        }
        if (!empty($filters['assigned_to'])) {
            $where .= " AND d.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        if (!empty($filters['date_from'])) {
            $where .= " AND d.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where .= " AND d.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = min(100, max(1, (int)($filters['per_page'] ?? 25)));
        $offset = ($page - 1) * $perPage;

        $total = $this->db->query("SELECT COUNT(*) FROM lead_deals d WHERE $where", $params)->fetchColumn();

        $stmt = $this->db->query(
            "SELECT d.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email,
                    l.lead_score, u.name as assigned_name
             FROM lead_deals d
             LEFT JOIN leads l ON d.lead_id = l.id
             LEFT JOIN users u ON d.assigned_to = u.id
             WHERE $where
             ORDER BY d.expected_close_date ASC, d.deal_value DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return [
            'deals' => $stmt->fetchAll() ?: [],
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    public function getDealById(int $id): ?array
    {
        $where = "d.id = ?";
        $params = [$id];
        if ($tid = $this->tid()) { $where .= " AND d.tenant_id = ?"; $params[] = $tid; }
        $stmt = $this->db->query(
            "SELECT d.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email,
                    l.lead_score, u.name as assigned_name
             FROM lead_deals d
             LEFT JOIN leads l ON d.lead_id = l.id
             LEFT JOIN users u ON d.assigned_to = u.id
             WHERE $where",
            $params
        );
        return $stmt->fetch() ?: null;
    }

    public function createDeal(array $data): array
    {
        try {
            $tid = $this->tid();
            $this->db->query(
                "INSERT INTO lead_deals (lead_id, deal_name, deal_value, stage, assigned_to, created_by,
                    expected_close_date, probability, notes, property_type, colony_id, plot_id, tenant_id, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $data['lead_id'],
                    $data['deal_name'] ?? 'New Deal',
                    $data['deal_value'] ?? 0,
                    $data['stage'] ?? 'qualification',
                    $data['assigned_to'] ?? null,
                    $data['created_by'] ?? null,
                    $data['expected_close_date'] ?? null,
                    $data['probability'] ?? 25,
                    $data['notes'] ?? null,
                    $data['property_type'] ?? null,
                    $data['colony_id'] ?? null,
                    $data['plot_id'] ?? null,
                    $tid,
                ]
            );
            $dealId = $this->db->lastInsertId();
            return ['success' => true, 'deal_id' => (int)$dealId, 'message' => 'Deal created'];
        } catch (\Exception $e) {
            error_log('CRMService::createDeal error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateDeal(int $id, array $data): array
    {
        $allowed = ['deal_name', 'deal_value', 'stage', 'assigned_to', 'expected_close_date', 'probability', 'notes', 'property_type', 'colony_id', 'plot_id'];
        $sets = [];
        $params = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($sets)) {
            return ['success' => false, 'error' => 'No fields to update'];
        }

        $sets[] = "updated_at = NOW()";
        if ($tid = $this->tid()) { $sets[] = "tenant_id = ?"; $params[] = $tid; }
        $params[] = $id;

        try {
            $this->db->query("UPDATE lead_deals SET " . implode(', ', $sets) . " WHERE id = ?", $params);
            return ['success' => true, 'message' => 'Deal updated'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function moveDealStage(int $id, string $stage): array
    {
        try {
            $extra = ""; $params = [$stage, $id];
            if ($tid = $this->tid()) { $extra = " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->query("UPDATE lead_deals SET stage = ?, updated_at = NOW() WHERE id = ?$extra", $params);
            if ($stage === 'won') {
                $this->db->query("UPDATE lead_deals SET closed_at = NOW() WHERE id = ?", [$id]);
            }
            return ['success' => true, 'message' => "Deal moved to $stage"];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteDeal(int $id): array
    {
        try {
            $where = "id = ?"; $params = [$id];
            if ($tid = $this->tid()) { $where .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->query("DELETE FROM lead_deals WHERE $where", $params);
            return ['success' => true, 'message' => 'Deal deleted'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getDealPipelineSummary(): array
    {
        try {
            $where = ""; $params = [];
            if ($tid = $this->tid()) { $where = " WHERE tenant_id = ?"; $params[] = $tid; }
            $stages = $this->db->query(
                "SELECT stage, COUNT(*) as count, COALESCE(SUM(deal_value),0) as total_value,
                        COALESCE(AVG(probability),0) as avg_probability
                 FROM lead_deals $where GROUP BY stage ORDER BY FIELD(stage, 'qualification','proposal','negotiation','commitment','won','lost')",
                $params
            )->fetchAll() ?: [];

            $totalValue = array_sum(array_column($stages, 'total_value'));
            $weightedValue = 0;
            foreach ($stages as $s) {
                $weightedValue += $s['total_value'] * ($s['avg_probability'] / 100);
            }

            return [
                'stages' => $stages,
                'total_pipeline_value' => $totalValue,
                'weighted_pipeline_value' => $weightedValue,
                'total_deals' => array_sum(array_column($stages, 'count')),
            ];
        } catch (\Exception $e) {
            return ['stages' => [], 'total_pipeline_value' => 0, 'weighted_pipeline_value' => 0, 'total_deals' => 0];
        }
    }

    // ─────────── Lead Score Breakdown ─────────────────────────────────

    public function getScoreBreakdown(int $leadId): array
    {
        try {
            $lead = $this->getLeadById($leadId);
            if (!$lead) return ['error' => 'Lead not found'];

            $breakdown = [];

            $budgetScore = 0;
            $budget = (float)($lead['budget_max'] ?? $lead['budget_min'] ?? 0);
            if ($budget >= 10000000) $budgetScore = 25;
            elseif ($budget >= 5000000) $budgetScore = 20;
            elseif ($budget >= 3000000) $budgetScore = 15;
            elseif ($budget >= 1000000) $budgetScore = 10;
            else $budgetScore = 5;
            $breakdown['budget'] = ['score' => $budgetScore, 'max' => 25, 'label' => "Budget: ₹" . number_format($budget)];

            $sourceScores = ['referral' => 20, 'website' => 12, 'walk_in' => 15, 'call' => 10, 'social_media' => 8, 'csv_import' => 3, 'other' => 5];
            $source = strtolower($lead['source'] ?? 'other');
            $sourceScore = $sourceScores[$source] ?? 5;
            $breakdown['source'] = ['score' => $sourceScore, 'max' => 20, 'label' => "Source: $source"];

            $interactionCount = 0;
            try {
                $icWhere = "lead_id = ?"; $icParams = [$leadId];
                if ($tid = $this->tid()) { $icWhere .= " AND tenant_id = ?"; $icParams[] = $tid; }
                $interactionCount = (int)$this->db->query(
                    "SELECT COUNT(*) FROM crm_interactions WHERE $icWhere", $icParams
                )->fetchColumn();
            } catch (\Exception $e) { error_log($e->getMessage()); }
            $engagementScore = min(25, $interactionCount * 5);
            $breakdown['engagement'] = ['score' => $engagementScore, 'max' => 25, 'label' => "Interactions: $interactionCount"];

            $recencyScore = 0;
            if (!empty($lead['last_contacted_at'])) {
                $daysSince = (int)((time() - strtotime($lead['last_contacted_at'])) / 86400);
                if ($daysSince <= 1) $recencyScore = 15;
                elseif ($daysSince <= 3) $recencyScore = 12;
                elseif ($daysSince <= 7) $recencyScore = 8;
                elseif ($daysSince <= 30) $recencyScore = 4;
                else $recencyScore = 0;
            }
            $breakdown['recency'] = ['score' => $recencyScore, 'max' => 15, 'label' => "Last contact recency"];

            $profileScore = 0;
            if (!empty($lead['phone'])) $profileScore += 3;
            if (!empty($lead['email'])) $profileScore += 3;
            if (!empty($lead['budget_min'])) $profileScore += 3;
            if (!empty($lead['preferred_location'])) $profileScore += 3;
            if (!empty($lead['property_type'])) $profileScore += 3;
            $breakdown['profile'] = ['score' => $profileScore, 'max' => 15, 'label' => "Profile completeness"];

            $totalScore = array_sum(array_column($breakdown, 'score'));

            return [
                'lead_id' => $leadId,
                'total_score' => $totalScore,
                'breakdown' => $breakdown,
                'grade' => $totalScore >= 80 ? 'S' : ($totalScore >= 65 ? 'A' : ($totalScore >= 50 ? 'B' : ($totalScore >= 30 ? 'C' : 'D'))),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // ─────────── Follow-up Reminders ──────────────────────────────────

    public function getFollowUpReminders(int $userId, string $role = 'associate'): array
    {
        try {
            $where = "t.status = 'pending' AND t.due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
            $params = [];
            if ($tid = $this->tid()) { $where .= " AND t.tenant_id = ?"; $params[] = $tid; }

            if (!in_array($role, ['admin', 'manager'])) {
                $where .= " AND t.assigned_to = ?";
                $params[] = $userId;
            }

            $stmt = $this->db->query(
                "SELECT t.*, l.name as lead_name, l.phone as lead_phone, l.lead_score,
                        u.name as assigned_name
                 FROM crm_tasks t
                 LEFT JOIN leads l ON t.lead_id = l.id
                 LEFT JOIN users u ON t.assigned_to = u.id
                 WHERE $where
                 ORDER BY t.due_date ASC, t.due_time ASC
                 LIMIT 50",
                $params
            );

            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getOverdueRemindersCount(int $userId, string $role = 'associate'): int
    {
        try {
            $where = "t.status = 'pending' AND t.due_date < CURDATE()";
            $params = [];
            if ($tid = $this->tid()) { $where .= " AND t.tenant_id = ?"; $params[] = $tid; }

            if (!in_array($role, ['admin', 'manager'])) {
                $where .= " AND t.assigned_to = ?";
                $params[] = $userId;
            }

            return (int)$this->db->query(
                "SELECT COUNT(*) FROM crm_tasks t WHERE $where",
                $params
            )->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    // ─────────── Bulk Lead Update ─────────────────────────────────────

    public function bulkUpdateLeads(array $leadIds, array $updates, int $userId): array
    {
        $allowed = ['status', 'assigned_to', 'priority', 'lead_category', 'source'];
        $sets = [];
        $params = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $updates)) {
                $sets[] = "$field = ?";
                $params[] = $updates[$field];
            }
        }

        if (empty($sets) || empty($leadIds)) {
            return ['success' => false, 'error' => 'No valid fields or leads'];
        }

        $sets[] = "updated_at = NOW()";
        $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
        $tenantWhere = "";
        $tenantParams = [];
        if ($tid = $this->tid()) { $tenantWhere = " AND tenant_id = ?"; $tenantParams[] = $tid; }

        try {
            $this->db->query(
                "UPDATE leads SET " . implode(', ', $sets) . " WHERE id IN ($placeholders) $tenantWhere",
                array_merge($params, $leadIds, $tenantParams)
            );
            return ['success' => true, 'updated' => count($leadIds)];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────── Commission Calculator ────────────────────────────────

    public function estimateCommission(int $leadId): array
    {
        try {
            $lead = $this->getLeadById($leadId);
            if (!$lead) return ['error' => 'Lead not found'];

            $budget = (float)($lead['budget_max'] ?? $lead['budget_min'] ?? 0);
            if ($budget <= 0) return ['error' => 'No budget set for this lead'];

            $rankSlabs = [
                'associate' => 5, 'sr_associate' => 7, 'bdm' => 10,
                'sr_bdm' => 12, 'vice_president' => 15, 'president' => 18, 'site_manager' => 20,
            ];

            $results = [];
            foreach ($rankSlabs as $rank => $pct) {
                $commission = round($budget * $pct / 100);
                $trackA = round($commission * 0.75);
                $trackB = round($commission * 0.15);
                $trackC = round($commission * 0.10);

                $results[] = [
                    'rank' => $rank,
                    'rate_pct' => $pct,
                    'total_commission' => $commission,
                    'track_a_slab_differential' => $trackA,
                    'track_b_performance_rollup' => $trackB,
                    'track_c_milestone_escrow' => $trackC,
                ];
            }

            return [
                'lead_id' => $leadId,
                'lead_name' => $lead['name'] ?? '',
                'budget' => $budget,
                'commission_by_rank' => $results,
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // ─────────── Activity Auto-Logging ───────────────────────────────────

    public function logActivity(int $leadId, int $userId, string $type, string $subject, string $body = '', array $meta = []): array
    {
        try {
            $actCols = "lead_id, created_by, activity_type, subject, description, metadata, created_at";
            $actVals = "?, ?, ?, ?, ?, ?, NOW()";
            $actParams = [$leadId, $userId, $type, $subject, $body, json_encode($meta)];
            if ($tid = $this->tid()) { $actCols .= ", tenant_id"; $actVals .= ", ?"; $actParams[] = $tid; }
            $this->db->query(
                "INSERT INTO lead_activities ($actCols) VALUES ($actVals)",
                $actParams
            );
            
            // Also add to crm_interactions if it's an interaction type
            if (in_array($type, ['call', 'email', 'whatsapp', 'meeting', 'site_visit', 'sms'])) {
                $intCols = "lead_id, user_id, interaction_type, direction, subject, body, tenant_id";
                $intVals = "?, ?, ?, ?, ?, ?, ?";
                $intParams = [$leadId, $userId, $type, $meta['direction'] ?? 'outbound', $subject, $body, $tid ?: null];
                $this->db->query(
                    "INSERT INTO crm_interactions ($intCols) VALUES ($intVals)",
                    $intParams
                );
            }
            
            // Update lead's last_activity_date
            $actWhere = "id = ?"; $actParams2 = [$leadId];
            if ($tid) { $actWhere .= " AND tenant_id = ?"; $actParams2[] = $tid; }
            $this->db->query("UPDATE leads SET last_activity_date = NOW() WHERE $actWhere", $actParams2);
            
            // Auto-score
            $this->recalculateScore($leadId);
            
            return ['success' => true, 'activity_id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            error_log('CRMService::logActivity error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getLeadTimeline(int $leadId, int $limit = 100): array
    {
        try {
            $tid = $this->tid();
            $actWhere = "lead_id = ?"; $actParams = [$leadId];
            $intWhere = "lead_id = ?"; $intParams = [$leadId];
            $taskWhere = "lead_id = ?"; $taskParams = [$leadId];
            if ($tid) {
                $actWhere .= " AND tenant_id = ?"; $actParams[] = $tid;
                $intWhere .= " AND tenant_id = ?"; $intParams[] = $tid;
                $taskWhere .= " AND tenant_id = ?"; $taskParams[] = $tid;
            }
            $actParams[] = $limit;
            $intParams[] = $limit;
            $taskParams[] = $limit;

            // Lead activities (status changes, notes, updates, assignments)
            $activities = $this->db->fetchAll(
                "SELECT id, lead_id, 'activity' as timeline_type, type, subject as title, details as description, created_at, user_id as actor_id
                 FROM lead_activities WHERE $actWhere ORDER BY created_at DESC LIMIT ?",
                $actParams
            ) ?: [];

            // Interactions (calls, emails, WhatsApp, meetings)
            $interactions = $this->db->fetchAll(
                "SELECT id, lead_id, 'interaction' as timeline_type, type, subject, body as description, created_at, user_id as actor_id
                 FROM crm_interactions WHERE $intWhere ORDER BY created_at DESC LIMIT ?",
                $intParams
            ) ?: [];

            // Tasks (follow-ups, to-dos)
            $tasks = $this->db->fetchAll(
                "SELECT id, lead_id, 'task' as timeline_type, type, title, description, created_at, assigned_to as actor_id
                 FROM crm_tasks WHERE $taskWhere ORDER BY created_at DESC LIMIT ?",
                $taskParams
            ) ?: [];

            // Merge all sources and sort by created_at DESC
            $all = array_merge($activities, $interactions, $tasks);
            usort($all, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return array_slice($all, 0, $limit);
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────── Deal Won/Lost Reason Tracking ────────────────────────────

    public function closeDeal(int $dealId, string $outcome, string $reason = '', string $reasonDetail = '', int $closedBy = 0): array
    {
        try {
            $validOutcomes = ['won', 'lost'];
            if (!in_array($outcome, $validOutcomes)) {
                return ['success' => false, 'error' => 'Invalid outcome'];
            }
            
            $validReasons = [
                'price' => 'Pricing/Budget',
                'competitor' => 'Lost to Competitor',
                'timing' => 'Timing/Not Ready',
                'budget' => 'Budget Constraints',
                'product' => 'Product/Feature Gap',
                'authority' => 'Decision Maker Changed',
                'no_response' => 'No Response/Ghosted',
                'other' => 'Other'
            ];
            
            $this->db->query(
                "UPDATE lead_deals SET stage = ?, closed_at = NOW(), close_reason = ?, close_reason_detail = ?, closed_by = ? WHERE id = ?",
                [$outcome, $reason, $reasonDetail, $closedBy, $dealId]
            );
            
            // Get deal info for activity log
            $deal = $this->db->fetchOne("SELECT lead_id, deal_name, deal_value FROM lead_deals WHERE id = ?", [$dealId]);
            if ($deal) {
                $this->logActivity(
                    $deal['lead_id'],
                    $closedBy,
                    'deal_' . $outcome,
                    'Deal ' . ucfirst($outcome) . ': ' . $deal['deal_name'],
                    "Value: ₹" . number_format($deal['deal_value']) . " | Reason: " . ($validReasons[$reason] ?? $reason) . ($reasonDetail ? " — $reasonDetail" : ''),
                    ['deal_id' => $dealId, 'value' => $deal['deal_value'], 'reason' => $reason]
                );
            }
            
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getWinLossReasons(string $period = '30d'): array
    {
        $days = (int)str_replace('d', '', $period);
        try {
            $tid = $this->tid();
            $tenantWhere = $tid ? " AND ld.tenant_id = ?" : "";

            $wonParams = [$days];
            $lostParams = [$days];
            if ($tid) { $wonParams[] = $tid; $lostParams[] = $tid; }

            $won = $this->db->fetchAll(
                "SELECT ld.close_reason, COUNT(*) as cnt, SUM(ld.deal_value) as total_value
                 FROM lead_deals ld
                 WHERE ld.stage = 'won' AND ld.closed_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) $tenantWhere
                 GROUP BY ld.close_reason",
                $wonParams
            ) ?: [];
            
            $lost = $this->db->fetchAll(
                "SELECT ld.close_reason, COUNT(*) as cnt, SUM(ld.deal_value) as total_value
                 FROM lead_deals ld
                 WHERE ld.stage = 'lost' AND ld.closed_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) $tenantWhere
                 GROUP BY ld.close_reason",
                $lostParams
            ) ?: [];
            
            return ['won' => $won, 'lost' => $lost];
        } catch (\Exception $e) {
            return ['won' => [], 'lost' => []];
        }
    }

    // ─────────── Revenue Forecasting ──────────────────────────────────────

    public function getRevenueForecast(int $months = 3): array
    {
        try {
            $tid = $this->tid();
            $tenantWhere = $tid ? " AND tenant_id = ?" : "";

            // Current weighted pipeline
            $weightedPipeline = 0;
            $byStage = [];
            $stageParams = [];
            if ($tid) { $stageParams[] = $tid; }
            $stages = $this->db->fetchAll(
                "SELECT stage, SUM(deal_value) as total_value, COUNT(*) as cnt,
                        AVG(probability) as avg_prob
                 FROM lead_deals
                 WHERE stage IN ('qualified','site_visit','proposal','negotiation','booking') $tenantWhere
                 GROUP BY stage",
                $stageParams
            ) ?: [];
            
            foreach ($stages as $s) {
                $prob = (float)($s['avg_prob'] ?? 0) / 100;
                $weighted = (float)$s['total_value'] * $prob;
                $weightedPipeline += $weighted;
                $byStage[] = [
                    'stage' => $s['stage'],
                    'total_value' => (float)$s['total_value'],
                    'weighted_value' => $weighted,
                    'count' => (int)$s['cnt'],
                    'probability' => (float)$s['avg_prob'],
                ];
            }
            
            // Monthly trend (last 6 months actual)
            $trendParams = [];
            if ($tid) { $trendParams[] = $tid; }
            $trend = $this->db->fetchAll(
                "SELECT DATE_FORMAT(closed_at, '%Y-%m') as month, 
                        SUM(CASE WHEN stage='won' THEN deal_value ELSE 0 END) as won_value,
                        COUNT(CASE WHEN stage='won' THEN 1 END) as won_count
                 FROM lead_deals
                 WHERE closed_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) $tenantWhere
                 GROUP BY month
                 ORDER BY month ASC",
                $trendParams
            ) ?: [];
            
            // Simple forecast: average monthly close rate × weighted pipeline
            $avgMonthlyClose = $trend ? array_sum(array_column($trend, 'won_value')) / count($trend) : 0;
            $forecast = [];
            for ($i = 1; $i <= $months; $i++) {
                $month = date('Y-m', strtotime("+$i months"));
                $forecast[] = [
                    'month' => $month,
                    'forecast' => round($avgMonthlyClose * $i / $months),
                    'best_case' => round($avgMonthlyClose * 1.5 * $i / $months),
                    'worst_case' => round($avgMonthlyClose * 0.5 * $i / $months),
                ];
            }
            
            return [
                'weighted_pipeline' => $weightedPipeline,
                'by_stage' => $byStage,
                'monthly_trend' => $trend,
                'forecast' => $forecast,
                'avg_monthly_close' => $avgMonthlyClose,
            ];
        } catch (\Exception $e) {
            return ['weighted_pipeline' => 0, 'by_stage' => [], 'monthly_trend' => [], 'forecast' => []];
        }
    }

    // ─────────── Lead Segmentation ────────────────────────────────────────

    public function getSegments(): array
    {
        try {
            $segments = $this->db->fetchAll("SELECT * FROM crm_segments ORDER BY created_at DESC") ?: [];
            foreach ($segments as &$seg) {
                $criteria = json_decode($seg['filter_criteria'] ?? '{}', true) ?? [];
                $where = ["deleted_at IS NULL"];
                $params = [];
                if ($tid = $this->tid()) { $where[] = "tenant_id = ?"; $params[] = $tid; }
                if (!empty($criteria['status'])) { $where[] = "status = ?"; $params[] = $criteria['status']; }
                if (!empty($criteria['source'])) { $where[] = "source = ?"; $params[] = $criteria['source']; }
                if (!empty($criteria['min_score'])) { $where[] = "lead_score >= ?"; $params[] = (int)$criteria['min_score']; }
                if (!empty($criteria['city'])) { $where[] = "city = ?"; $params[] = $criteria['city']; }
                if (!empty($criteria['min_budget'])) { $where[] = "budget >= ?"; $params[] = (float)$criteria['min_budget']; }
                if (!empty($criteria['max_budget'])) { $where[] = "budget <= ?"; $params[] = (float)$criteria['max_budget']; }
                $seg['lead_count'] = (int)$this->db->fetchOne("SELECT COUNT(*) as cnt FROM leads WHERE " . implode(' AND ', $where), $params)['cnt'] ?? 0;
            }
            return $segments;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createSegment(string $name, string $description, array $criteria, int $createdBy): array
    {
        try {
            $cols = "name, description, filter_criteria, created_by, created_at";
            $vals = "?, ?, ?, ?, NOW()";
            $params = [$name, $description, json_encode($criteria), $createdBy];
            if ($tid = $this->tid()) { $cols .= ", tenant_id"; $vals .= ", ?"; $params[] = $tid; }
            $this->db->query(
                "INSERT INTO crm_segments ($cols) VALUES ($vals)",
                $params
            );
            return ['success' => true, 'segment_id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getSegmentLeads(int $segmentId, int $limit = 200): array
    {
        try {
            $seg = $this->db->fetchOne("SELECT * FROM crm_segments WHERE id = ?", [$segmentId]);
            if (!$seg) return [];
            
            $criteria = json_decode($seg['filter_criteria'] ?? '{}', true) ?? [];
            $where = ["l.deleted_at IS NULL"];
            $params = [];
            if ($tid = $this->tid()) { $where[] = "l.tenant_id = ?"; $params[] = $tid; }
            if (!empty($criteria['status'])) { $where[] = "l.status = ?"; $params[] = $criteria['status']; }
            if (!empty($criteria['source'])) { $where[] = "l.source = ?"; $params[] = $criteria['source']; }
            if (!empty($criteria['min_score'])) { $where[] = "l.lead_score >= ?"; $params[] = (int)$criteria['min_score']; }
            if (!empty($criteria['city'])) { $where[] = "l.city = ?"; $params[] = $criteria['city']; }
            if (!empty($criteria['min_budget'])) { $where[] = "l.budget >= ?"; $params[] = (float)$criteria['min_budget']; }
            if (!empty($criteria['max_budget'])) { $where[] = "l.budget <= ?"; $params[] = (float)$criteria['max_budget']; }
            
            $stmt = $this->db->query(
                "SELECT l.*, u.name as assignee_name FROM leads l LEFT JOIN users u ON l.assigned_to=u.id 
                 WHERE " . implode(' AND ', $where) . " ORDER BY l.created_at DESC LIMIT ?",
                array_merge($params, [$limit])
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getSourceAnalytics(string $period = '30d'): array
    {
        $days = (int)str_replace('d', '', $period);
        try {
            $where = "created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
            $params = [$days];
            if ($tid = $this->tid()) { $where .= " AND tenant_id = ?"; $params[] = $tid; }
            $sources = $this->db->fetchAll(
                "SELECT source, COUNT(*) as count,
                        SUM(CASE WHEN status IN ('qualified','proposal','won') THEN 1 ELSE 0 END) as converted,
                        ROUND(AVG(lead_score),1) as avg_score,
                        MIN(created_at) as first_lead, MAX(created_at) as last_lead
                 FROM leads
                 WHERE $where
                 GROUP BY source
                 ORDER BY count DESC",
                $params
            ) ?: [];

            foreach ($sources as &$s) {
                $s['conversion_rate'] = $s['count'] > 0 ? round($s['converted'] / $s['count'] * 100, 1) : 0;
            }

            return ['sources' => $sources, 'period_days' => $days];
        } catch (\Exception $e) {
            return ['sources' => [], 'period_days' => $days];
        }
    }

    public function getConversionFunnel(): array
    {
        try {
            $where = ""; $params = [];
            if ($tid = $this->tid()) { $where = " WHERE tenant_id = ?"; $params[] = $tid; }
            $stages = $this->db->fetchAll(
                "SELECT status, COUNT(*) as count FROM leads $where GROUP BY status ORDER BY FIELD(status, 'new','contacted','qualified','site_visit','proposal','negotiation','won','lost','nurture')",
                $params
            ) ?: [];

            $total = array_sum(array_column($stages, 'count'));
            $funnel = [];
            foreach ($stages as $s) {
                $funnel[] = [
                    'stage' => $s['status'],
                    'count' => (int)$s['count'],
                    'pct_of_total' => $total > 0 ? round($s['count'] / $total * 100, 1) : 0,
                ];
            }

            return ['funnel' => $funnel, 'total_leads' => $total];
        } catch (\Exception $e) {
            return ['funnel' => [], 'total_leads' => 0];
        }
    }

    public function getAgentPerformance(): array
    {
        try {
            $leadWhere = "l.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            $leadParams = [];
            if ($tid = $this->tid()) { $leadWhere .= " AND l.tenant_id = ?"; $leadParams[] = $tid; }
            $performance = $this->db->fetchAll(
                "SELECT u.id, u.name,
                        COUNT(l.id) as total_leads,
                        SUM(CASE WHEN l.status IN ('qualified','proposal','won') THEN 1 ELSE 0 END) as converted,
                        SUM(CASE WHEN l.lead_category = 'hot' THEN 1 ELSE 0 END) as hot_leads,
                        ROUND(AVG(l.lead_score),1) as avg_score,
                        MAX(l.updated_at) as last_activity
                 FROM users u
                 LEFT JOIN leads l ON l.assigned_to = u.id AND $leadWhere
                 WHERE u.role IN ('associate','employee','agent') AND u.deleted_at IS NULL
                 GROUP BY u.id, u.name
                 ORDER BY converted DESC
                 LIMIT 50",
                $leadParams
            ) ?: [];

            foreach ($performance as &$p) {
                $p['conversion_rate'] = $p['total_leads'] > 0 ? round($p['converted'] / $p['total_leads'] * 100, 1) : 0;
            }

            return $performance;
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────── Lead Deduplication ──────────────────────────────────────

    public function findDuplicates(): array
    {
        try {
            $tenantWhere = ""; $tenantParams = [];
            if ($tid = $this->tid()) { $tenantWhere = " AND l1.tenant_id = ? AND l2.tenant_id = ?"; $tenantParams = [$tid, $tid]; }
            // Find leads sharing the same phone or email
            $phoneDupes = $this->db->fetchAll(
                "SELECT l1.id as id1, l1.name as name1, l1.phone as phone1, l1.email as email1, l1.lead_score as score1, l1.created_at as created1,
                        l2.id as id2, l2.name as name2, l2.phone as phone2, l2.email as email2, l2.lead_score as score2, l2.created_at as created2,
                        'phone' as match_type, l1.phone as match_value
                 FROM leads l1
                 JOIN leads l2 ON l1.phone = l2.phone AND l1.id < l2.id
                 WHERE l1.phone IS NOT NULL AND l1.phone != '' AND l1.deleted_at IS NULL AND l2.deleted_at IS NULL $tenantWhere",
                $tenantParams
            ) ?: [];

            $emailDupes = $this->db->fetchAll(
                "SELECT l1.id as id1, l1.name as name1, l1.phone as phone1, l1.email as email1, l1.lead_score as score1, l1.created_at as created1,
                        l2.id as id2, l2.name as name2, l2.phone as phone2, l2.email as email2, l2.lead_score as score2, l2.created_at as created2,
                        'email' as match_type, l1.email as match_value
                 FROM leads l1
                 JOIN leads l2 ON l1.email = l2.email AND l1.id < l2.id
                 WHERE l1.email IS NOT NULL AND l1.email != '' AND l1.deleted_at IS NULL AND l2.deleted_at IS NULL $tenantWhere",
                $tenantParams
            ) ?: [];

            // Deduplicate pairs (same pair might match on both phone + email)
            $seen = [];
            $duplicates = [];
            foreach (array_merge($phoneDupes, $emailDupes) as $d) {
                $key = min($d['id1'], $d['id2']) . '-' . max($d['id1'], $d['id2']);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $duplicates[] = $d;
                }
            }

            return $duplicates;
        } catch (\Exception $e) {
            error_log('CRMService::findDuplicates error: ' . $e->getMessage());
            return [];
        }
    }

    public function mergeLeads(int $keepId, int $removeId): array
    {
        try {
            $keep = $this->getLeadById($keepId);
            $remove = $this->getLeadById($removeId);
            if (!$keep || !$remove) {
                return ['success' => false, 'error' => 'One or both leads not found'];
            }

            $db = $this->db->getConnection();

            // Merge: keep the better data from both
            $updates = [];
            $fields = ['email', 'phone', 'company', 'budget', 'location_preference', 'notes', 'source', 'assigned_to'];
            foreach ($fields as $f) {
                if (empty($keep[$f]) && !empty($remove[$f])) {
                    $updates[$f] = $remove[$f];
                }
            }
            // Keep higher score
            if (($remove['lead_score'] ?? 0) > ($keep['lead_score'] ?? 0)) {
                $updates['lead_score'] = $remove['lead_score'];
            }
            // Keep higher priority
            $priorityRank = ['urgent' => 5, 'high' => 4, 'medium' => 3, 'low' => 2];
            $keepPri = $priorityRank[$keep['priority'] ?? 'medium'] ?? 3;
            $removePri = $priorityRank[$remove['priority'] ?? 'medium'] ?? 3;
            if ($removePri > $keepPri) {
                $updates['priority'] = $remove['priority'];
            }

            if (!empty($updates)) {
                $setClauses = [];
                $params = [];
                foreach ($updates as $k => $v) {
                    $setClauses[] = "$k = ?";
                    $params[] = $v;
                }
                $mergeWhere = "id = ?";
                $params[] = $keepId;
                if ($tid = $this->tid()) { $mergeWhere .= " AND tenant_id = ?"; $params[] = $tid; }
                $db->prepare("UPDATE leads SET " . implode(', ', $setClauses) . ", updated_at = NOW() WHERE $mergeWhere")->execute($params);
            }

            // Move interactions from remove → keep
            $moveTid = $this->tid();
            if ($moveTid) {
                $db->prepare("UPDATE crm_interactions SET lead_id = ? WHERE lead_id = ? AND tenant_id = ?")->execute([$keepId, $removeId, $moveTid]);
                $db->prepare("UPDATE crm_tasks SET lead_id = ? WHERE lead_id = ? AND tenant_id = ?")->execute([$keepId, $removeId, $moveTid]);
                $db->prepare("UPDATE lead_activities SET lead_id = ? WHERE lead_id = ?")->execute([$keepId, $removeId]);
                $db->prepare("UPDATE lead_deals SET lead_id = ? WHERE lead_id = ? AND tenant_id = ?")->execute([$keepId, $removeId, $moveTid]);
            } else {
                $db->prepare("UPDATE crm_interactions SET lead_id = ? WHERE lead_id = ?")->execute([$keepId, $removeId]);
                $db->prepare("UPDATE crm_tasks SET lead_id = ? WHERE lead_id = ?")->execute([$keepId, $removeId]);
                $db->prepare("UPDATE lead_activities SET lead_id = ? WHERE lead_id = ?")->execute([$keepId, $removeId]);
                $db->prepare("UPDATE lead_deals SET lead_id = ? WHERE lead_id = ?")->execute([$keepId, $removeId]);
            }
            // Move notes
            try {
                $db->prepare("UPDATE lead_notes SET lead_id = ? WHERE lead_id = ?")->execute([$keepId, $removeId]);
            } catch (\Throwable $e) { /* table may not exist */ error_log($e->getMessage()); }
            // Move scores
            try {
                $db->prepare("UPDATE lead_scores SET lead_id = ? WHERE lead_id = ?")->execute([$keepId, $removeId]);
            } catch (\Throwable $e) { /* table may not exist */ error_log($e->getMessage()); }

            // Soft-delete the removed lead
            $delWhere = "id = ?"; $delParams = [$removeId];
            if ($tid = $this->tid()) { $delWhere .= " AND tenant_id = ?"; $delParams[] = $tid; }
            $db->prepare("UPDATE leads SET deleted_at = NOW(), name = CONCAT(name, ' [MERGED INTO #$keepId]') WHERE $delWhere")->execute($delParams);

            // Log merge activity
            $this->logActivity($keepId, 1, 'merge', "Merged duplicate lead #$removeId into this lead");

            return [
                'success' => true,
                'kept' => $keep['name'] ?? "Lead #$keepId",
                'removed' => $remove['name'] ?? "Lead #$removeId",
                'fields_merged' => array_keys($updates),
            ];
        } catch (\Exception $e) {
            error_log('CRMService::mergeLeads error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────── Role-Based Dashboard ───────────────────────────────────

    public function getRoleDashboardData(int $userId, string $role): array
    {
        try {
            $data = ['role' => $role, 'user_id' => $userId];

            // Role-specific lead filter
            $leadFilter = '';
            $taskFilter = '';
            $tid = $this->tid();
            $tidFilter = $tid ? " AND l.tenant_id = $tid" : '';
            $tidFilterT = $tid ? " AND ct.tenant_id = $tid" : '';
            switch ($role) {
                case 'admin':
                case 'super_admin':
                    // See everything
                    break;
                case 'manager':
                    // See team leads (assigned to their reports)
                    $leadFilter = "AND l.assigned_to IN (SELECT id FROM users WHERE reports_to = $userId)";
                    break;
                case 'employee':
                case 'agent':
                case 'telecaller':
                    // See own leads only
                    $leadFilter = "AND l.assigned_to = $userId";
                    $taskFilter = "AND ct.assigned_to = $userId";
                    break;
                case 'associate':
                    $leadFilter = "AND l.assigned_to = $userId";
                    $taskFilter = "AND ct.assigned_to = $userId";
                    break;
                case 'customer':
                    // Customers don't see CRM dashboard
                    return ['role' => $role, 'error' => 'Customers do not have CRM access'];
            }

            // Lead counts
            $data['total_leads'] = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM leads l WHERE l.deleted_at IS NULL $leadFilter $tidFilter")['cnt'];
            $data['today_leads'] = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM leads l WHERE DATE(l.created_at) = CURDATE() $leadFilter $tidFilter")['cnt'];
            $data['hot_leads'] = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM leads l WHERE l.lead_score >= 70 AND l.deleted_at IS NULL $leadFilter $tidFilter")['cnt'];
            $data['converted'] = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM leads l WHERE l.is_converted = 1 $leadFilter $tidFilter")['cnt'];
            $data['conversion_rate'] = $data['total_leads'] > 0 ? round(($data['converted'] / $data['total_leads']) * 100, 1) : 0;

            // Pipeline by status
            $data['pipeline'] = $this->db->fetchAll(
                "SELECT l.status, COUNT(*) as cnt FROM leads l WHERE l.deleted_at IS NULL $leadFilter $tidFilter GROUP BY l.status ORDER BY cnt DESC"
            ) ?: [];

            // Tasks
            $data['pending_tasks'] = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM crm_tasks ct WHERE ct.status IN ('pending','in_progress') $taskFilter $tidFilterT")['cnt'];
            $data['overdue_tasks'] = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM crm_tasks ct WHERE ct.status = 'pending' AND ct.due_date < CURDATE() $taskFilter $tidFilterT")['cnt'];

            // Upcoming follow-ups (next 7 days)
            $data['upcoming_followups'] = $this->db->fetchAll(
                "SELECT l.id, l.name, l.phone, l.lead_score, l.next_activity_date, u.name as assignee_name
                 FROM leads l LEFT JOIN users u ON l.assigned_to = u.id
                 WHERE l.next_activity_date IS NOT NULL AND l.next_activity_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                   AND l.status NOT IN ('converted','closed','dead') AND l.deleted_at IS NULL $leadFilter $tidFilter
                 ORDER BY l.next_activity_date ASC LIMIT 10"
            ) ?: [];

            // Recent leads
            $data['recent_leads'] = $this->db->fetchAll(
                "SELECT l.id, l.name, l.phone, l.email, l.status, l.lead_score, l.source, l.created_at, u.name as assigned_to_name
                 FROM leads l LEFT JOIN users u ON l.assigned_to = u.id
                 WHERE l.deleted_at IS NULL $leadFilter $tidFilter
                 ORDER BY l.created_at DESC LIMIT 10"
            ) ?: [];

            // My interactions (last 7 days)
            $intWhere = "ci.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            $intParams = [];
            if ($tid) { $intWhere .= " AND ci.tenant_id = ?"; $intParams[] = $tid; }
            $data['recent_interactions'] = $this->db->fetchAll(
                "SELECT ci.*, l.name as lead_name FROM crm_interactions ci
                 LEFT JOIN leads l ON ci.lead_id = l.id
                 WHERE $intWhere
                 ORDER BY ci.created_at DESC LIMIT 10",
                $intParams
            ) ?: [];

            // Deals (if applicable)
            if (in_array($role, ['admin', 'super_admin', 'manager', 'associate', 'agent'])) {
                $dealWhere = "ld.stage NOT IN ('won','lost')";
                $dealParams = [];
                if ($tid) { $dealWhere .= " AND ld.tenant_id = ?"; $dealParams[] = $tid; }
                $data['deals'] = $this->db->fetchAll(
                    "SELECT ld.*, l.name as lead_name FROM lead_deals ld
                     LEFT JOIN leads l ON ld.lead_id = l.id
                     WHERE $dealWhere
                     ORDER BY ld.updated_at DESC LIMIT 10",
                    $dealParams
                ) ?: [];
                $data['deal_summary'] = $this->getDealPipelineSummary();
            }

            // Team performance (admin/manager only)
            if (in_array($role, ['admin', 'super_admin', 'manager'])) {
                $data['team_performance'] = $this->getAgentPerformance();
                $topWhere = "l.deleted_at IS NULL AND l.assigned_to IS NOT NULL";
                $topParams = [];
                if ($tid) { $topWhere .= " AND l.tenant_id = ?"; $topParams[] = $tid; }
                $data['top_assignees'] = $this->db->fetchAll(
                    "SELECT u.name, l.assigned_to, COUNT(*) as lead_count,
                            SUM(CASE WHEN l.status IN ('won','booking') THEN 1 ELSE 0 END) as won_count
                     FROM leads l JOIN users u ON u.id = l.assigned_to
                     WHERE $topWhere
                     GROUP BY l.assigned_to ORDER BY lead_count DESC LIMIT 5",
                    $topParams
                ) ?: [];
            }

            // Source breakdown
            $srcWhere = "l.deleted_at IS NULL";
            $srcParams = [];
            if ($tid) { $srcWhere .= " AND l.tenant_id = ?"; $srcParams[] = $tid; }
            $data['by_source'] = $this->db->fetchAll(
                "SELECT source, COUNT(*) as cnt FROM leads l WHERE $srcWhere $leadFilter GROUP BY source ORDER BY cnt DESC",
                $srcParams
            ) ?: [];

            // 7-day trend
            $trendWhere = "l.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            $trendParams = [];
            if ($tid) { $trendWhere .= " AND l.tenant_id = ?"; $trendParams[] = $tid; }
            $data['weekly_trend'] = $this->db->fetchAll(
                "SELECT DATE(l.created_at) as date, COUNT(*) as cnt
                 FROM leads l WHERE $trendWhere $leadFilter
                 GROUP BY DATE(l.created_at) ORDER BY date ASC",
                $trendParams
            ) ?: [];

            return $data;
        } catch (\Exception $e) {
            error_log('CRMService::getRoleDashboardData error: ' . $e->getMessage());
            return ['role' => $role, 'error' => $e->getMessage()];
        }
    }
}
