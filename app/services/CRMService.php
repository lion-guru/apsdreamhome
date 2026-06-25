<?php
/**
 * CRMService — Full lead lifecycle management
 * Pipeline, scoring, assignment, follow-ups, interactions, analytics
 */

namespace App\Services;

use App\Core\Database;

class CRMService
{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
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
            ['id'=>3,'name'=>'Qualified','slug'=>'qualified','color'=>'#8b5cf6','order_index'=>3],
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

    public function getLeads($filters = []) {
        try {
            $where = ["l.deleted_at IS NULL"];
            $params = [];

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
            $stmt = $this->db->query(
                "SELECT l.*, u.name as assigned_to_name, c.name as created_by_name
                 FROM leads l
                 LEFT JOIN users u ON u.id = l.assigned_to
                 LEFT JOIN users c ON c.id = l.created_by
                 WHERE l.id = ?", [$id]
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
        try {
            $leadNumber = 'CR-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $stmt = $this->db->query(
                "INSERT INTO leads (lead_number, name, email, phone, company, address, city, state, pincode,
                 source, property_interest, budget, budget_range, location_preference, notes, tags,
                 assigned_to, created_by, status, priority, lead_score, lead_category)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', ?, ?, ?)",
                [
                    $leadNumber,
                    $data['name'] ?? '',
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                    $data['company'] ?? null,
                    $data['address'] ?? null,
                    $data['city'] ?? null,
                    $data['state'] ?? null,
                    $data['pincode'] ?? null,
                    $data['source'] ?? 'website',
                    $data['property_interest'] ?? null,
                    $data['budget'] ?? 0,
                    $data['budget_range'] ?? null,
                    $data['location_preference'] ?? null,
                    $data['notes'] ?? null,
                    $data['tags'] ?? null,
                    $data['assigned_to'] ?? null,
                    $data['created_by'] ?? null,
                    $data['priority'] ?? 'medium',
                    $data['lead_score'] ?? 0,
                    $data['lead_category'] ?? 'cold',
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

            $this->db->query("UPDATE leads SET " . implode(', ', $fields) . " WHERE id = ?", $params);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('CRMService::updateLead error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteLead($id) {
        try {
            $this->db->query("UPDATE leads SET deleted_at = NOW() WHERE id = ?", [$id]);
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
            $old = $this->db->fetchOne("SELECT status FROM leads WHERE id = ?", [$leadId]);
            if (!$old) return ['success' => false, 'error' => 'Lead not found'];

            $this->db->query("UPDATE leads SET status = ? WHERE id = ?", [$newStatus, $leadId]);

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
            $stmt = $this->db->query(
                "INSERT INTO crm_interactions (lead_id, user_id, interaction_type, direction, subject, body, duration_seconds, outcome, next_action, next_action_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
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
                ]
            );

            // Update lead's last_activity_date
            $this->db->query("UPDATE leads SET last_activity_date = NOW() WHERE id = ?", [$leadId]);

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
            $stmt = $this->db->query(
                "SELECT ci.*, u.name as user_name
                 FROM crm_interactions ci
                 LEFT JOIN users u ON u.id = ci.user_id
                 WHERE ci.lead_id = ?
                 ORDER BY ci.created_at DESC
                 LIMIT ?",
                [$leadId, (int)$limit]
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getMyInteractions($userId, $limit = 20) {
        try {
            $stmt = $this->db->query(
                "SELECT ci.*, l.name as lead_name, l.phone as lead_phone
                 FROM crm_interactions ci
                 LEFT JOIN leads l ON l.id = ci.lead_id
                 WHERE ci.user_id = ?
                 ORDER BY ci.created_at DESC
                 LIMIT ?",
                [$userId, (int)$limit]
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─────────── Tasks (Follow-up scheduling) ──────────────────────────

    public function createTask($data) {
        try {
            $stmt = $this->db->query(
                "INSERT INTO crm_tasks (lead_id, assigned_to, created_by, task_type, title, description, priority, status, due_date, due_time, reminder_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)",
                [
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
                ]
            );
            return ['success' => true, 'task_id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getLeadTasks($leadId) {
        try {
            $stmt = $this->db->query(
                "SELECT ct.*, u.name as assigned_to_name
                 FROM crm_tasks ct
                 LEFT JOIN users u ON u.id = ct.assigned_to
                 WHERE ct.lead_id = ?
                 ORDER BY ct.due_date ASC, ct.due_time ASC",
                [$leadId]
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
            $this->db->query(
                "UPDATE crm_tasks SET status = 'completed', completed_at = NOW(), completed_notes = ? WHERE id = ? AND assigned_to = ?",
                [$notes, $taskId, $userId]
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
            $old = $this->db->fetchOne("SELECT assigned_to FROM leads WHERE id = ?", [$leadId]);
            $oldAssignee = $old['assigned_to'] ?? null;

            $this->db->query("UPDATE leads SET assigned_to = ? WHERE id = ?", [$assignedTo, $leadId]);
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
            $this->db->query(
                "INSERT INTO crm_assignments (lead_id, assigned_from, assigned_to, assigned_by, reason, notes)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$leadId, $from, $to, $by, $reason, $notes]
            );
        } catch (\Exception $e) {
            error_log('CRMService::logAssignment error: ' . $e->getMessage());
        }
    }

    public function getLeadAssignments($leadId) {
        try {
            $stmt = $this->db->query(
                "SELECT ca.*, u1.name as from_name, u2.name as to_name, u3.name as by_name
                 FROM crm_assignments ca
                 LEFT JOIN users u1 ON u1.id = ca.assigned_from
                 LEFT JOIN users u2 ON u2.id = ca.assigned_to
                 LEFT JOIN users u3 ON u3.id = ca.assigned_by
                 WHERE ca.lead_id = ?
                 ORDER BY ca.created_at DESC",
                [$leadId]
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function autoAssignLeads($strategy = 'round_robin') {
        try {
            $unassigned = $this->db->query(
                "SELECT id FROM leads WHERE assigned_to IS NULL AND deleted_at IS NULL ORDER BY created_at ASC LIMIT 50"
            )->fetchAll() ?: [];

            if (empty($unassigned)) return ['success' => true, 'assigned' => 0];

            $agents = $this->db->query(
                "SELECT u.id, COUNT(l.id) as lead_count
                 FROM users u
                 LEFT JOIN leads l ON l.assigned_to = u.id AND l.deleted_at IS NULL
                 WHERE u.role IN ('agent','associate','employee')
                 GROUP BY u.id
                 ORDER BY lead_count ASC
                 LIMIT 20"
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
        try {
            $lead = $this->db->fetchOne("SELECT * FROM leads WHERE id = ?", [$leadId]);
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
                "UPDATE leads SET lead_score = ?, lead_category = ?, score_factors = ?, last_scored_at = NOW(), conversion_probability = ? WHERE id = ?",
                [$score, $category, json_encode($factors), min(100, $score), $leadId]
            );

            return $score;
        } catch (\Exception $e) {
            error_log('CRMService::recalculateScore error: ' . $e->getMessage());
            return 0;
        }
    }

    public function rescoreAllLeads() {
        try {
            $leads = $this->db->query("SELECT id FROM leads WHERE deleted_at IS NULL")->fetchAll() ?: [];
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
            $stmt = $this->db->query(
                "SELECT * FROM crm_lead_scores_history WHERE lead_id = ? ORDER BY created_at DESC LIMIT 20",
                [$leadId]
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

            $stats = [];

            // Lead counts by status
            $stmt = $this->db->query("SELECT l.status, COUNT(*) as cnt FROM leads l WHERE l.deleted_at IS NULL $whereLead GROUP BY l.status");
            $stats['by_status'] = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $stats['by_status'][$row['status']] = (int)$row['cnt'];
            }
            $stats['total_leads'] = array_sum($stats['by_status']);

            // Today's leads
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM leads l WHERE DATE(l.created_at) = CURDATE() $whereLead");
            $stats['today_leads'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // This week
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM leads l WHERE l.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) $whereLead");
            $stats['week_leads'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Hot leads
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM leads l WHERE l.lead_score >= 70 AND l.deleted_at IS NULL $whereLead");
            $stats['hot_leads'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Converted
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM leads l WHERE l.is_converted = 1 $whereLead");
            $stats['converted'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Conversion rate
            $stats['conversion_rate'] = $stats['total_leads'] > 0
                ? round(($stats['converted'] / $stats['total_leads']) * 100, 1)
                : 0;

            // Pending tasks
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM crm_tasks ct WHERE ct.status IN ('pending','in_progress') $whereTask");
            $stats['pending_tasks'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Overdue tasks
            $stats['overdue_tasks'] = count($this->getOverdueTasks($userId));

            // Interactions today
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM crm_interactions ci WHERE DATE(ci.created_at) = CURDATE()");
            $stats['today_interactions'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Interactions this week
            $stmt = $this->db->query("SELECT COUNT(*) as cnt FROM crm_interactions ci WHERE ci.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
            $stats['week_interactions'] = (int)($stmt->fetch()['cnt'] ?? 0);

            // Lead sources breakdown
            $stmt = $this->db->query("SELECT source, COUNT(*) as cnt FROM leads l WHERE l.deleted_at IS NULL $whereLead GROUP BY source ORDER BY cnt DESC");
            $stats['by_source'] = $stmt->fetchAll() ?: [];

            // Score distribution
            $stmt = $this->db->query("SELECT lead_category, COUNT(*) as cnt FROM leads l WHERE l.deleted_at IS NULL $whereLead GROUP BY lead_category");
            $stats['by_category'] = $stmt->fetchAll() ?: [];

            // Top assignees
            if ($role === 'admin') {
                $stmt = $this->db->query(
                    "SELECT u.name, l.assigned_to, COUNT(*) as lead_count,
                     SUM(CASE WHEN l.status IN ('won','booking') THEN 1 ELSE 0 END) as won_count
                     FROM leads l JOIN users u ON u.id = l.assigned_to
                     WHERE l.deleted_at IS NULL AND l.assigned_to IS NOT NULL
                     GROUP BY l.assigned_to ORDER BY lead_count DESC LIMIT 10"
                );
                $stats['top_assignees'] = $stmt->fetchAll() ?: [];
            }

            // 7-day trend
            $stmt = $this->db->query(
                "SELECT DATE(l.created_at) as date, COUNT(*) as cnt
                 FROM leads l
                 WHERE l.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) $whereLead
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
            $stmt = $this->db->query("SELECT * FROM crm_campaigns ORDER BY created_at DESC");
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createCampaign($data) {
        try {
            $stmt = $this->db->query(
                "INSERT INTO crm_campaigns (name, campaign_type, platform, budget, target_audience, target_locations, start_date, end_date, landing_page_url, status, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
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
                ]
            );
            return ['success' => true, 'campaign_id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────── Forms ─────────────────────────────────────────────────

    public function getForms() {
        try {
            $stmt = $this->db->query("SELECT * FROM crm_lead_forms ORDER BY created_at DESC");
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function submitForm($formCode, $data, $meta = []) {
        try {
            $form = $this->db->fetchOne("SELECT * FROM crm_lead_forms WHERE form_code = ? AND is_active = 1", [$formCode]);
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

            // Log form submission
            $this->db->query(
                "INSERT INTO crm_form_submissions (form_id, lead_id, submitted_data, ip_address, user_agent, utm_source, utm_medium, utm_campaign, page_url, device_type)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $form['id'],
                    $leadResult['lead_id'],
                    json_encode($data),
                    $meta['ip'] ?? null,
                    $meta['user_agent'] ?? null,
                    $meta['utm_source'] ?? null,
                    $meta['utm_medium'] ?? null,
                    $meta['utm_campaign'] ?? null,
                    $meta['page_url'] ?? null,
                    $meta['device_type'] ?? null,
                ]
            );

            // Update submission count
            $this->db->query("UPDATE crm_lead_forms SET submission_count = submission_count + 1 WHERE id = ?", [$form['id']]);

            // Add source detail
            $this->addSourceDetail($leadResult['lead_id'], [
                'source_type' => $form['source_tag'] ?? 'website',
                'form_id' => $form['id'],
                'utm_source' => $meta['utm_source'] ?? null,
                'utm_medium' => $meta['utm_medium'] ?? null,
                'utm_campaign' => $meta['utm_campaign'] ?? null,
                'landing_page' => $meta['page_url'] ?? null,
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
}
