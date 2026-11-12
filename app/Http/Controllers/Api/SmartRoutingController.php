<?php
/**
 * SmartRoutingController - Smart Routing for Leads & Tasks
 * Auto-assigns to best associate/team based on expertise, load, geography, language
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\AI\FreeAIEngines;

class SmartRoutingController extends BaseController
{
    protected $aiEngines;

    public function __construct()
    {
        parent::__construct();
        $this->aiEngines = FreeAIEngines::getInstance();
    }

    protected function skipCsrfProtection(): bool
    {
        return true; // Bearer-token API; CSRF handled by auth
    }

    /**
     * POST /api/ai/routing/lead
     * Route a new lead to best associate
     */
    public function routeLead()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            $input = $this->getJsonInput();
            $leadId = (int)($input['lead_id'] ?? 0);
            $forceReassign = (bool)($input['force_reassign'] ?? false);

            if (!$leadId) {
                return $this->jsonError('Lead ID required', 400);
            }

            $lead = $this->getLead($leadId);
            if (!$lead) {
                return $this->jsonError('Lead not found', 404);
            }

            // Check if already assigned
            if ($lead['assigned_to'] && !$forceReassign) {
                return $this->jsonSuccess([
                    'already_assigned' => true,
                    'assigned_to' => $lead['assigned_to'],
                    'associate_name' => $this->getUserName($lead['assigned_to'])
                ]);
            }

            // Find best associate
            $assignment = $this->findBestAssociate($lead);

            if ($assignment['assigned_to']) {
                $this->assignLead($leadId, $assignment['assigned_to'], $userId);
            }

            return $this->jsonSuccess([
                'lead_id' => $leadId,
                'assigned_to' => $assignment['assigned_to'],
                'associate_name' => $assignment['associate_name'] ?? 'Unassigned',
                'reason' => $assignment['reason'],
                'sla_hours' => $assignment['sla_hours'] ?? 4,
                'score_breakdown' => $assignment['score_breakdown'] ?? []
            ]);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Route Lead');
        }
    }

    /**
     * POST /api/ai/routing/bulk-leads
     * Bulk route multiple leads
     */
    public function bulkRouteLeads()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            $input = $this->getJsonInput();
            $leadIds = $input['lead_ids'] ?? [];

            if (empty($leadIds) || !is_array($leadIds)) {
                return $this->jsonError('Lead IDs array required', 400);
            }

            $results = [];
            $assigned = 0;
            $failed = 0;

            foreach ($leadIds as $leadId) {
                try {
                    $lead = $this->getLead((int)$leadId);
                    if (!$lead) {
                        $results[] = ['lead_id' => $leadId, 'status' => 'failed', 'reason' => 'Lead not found'];
                        $failed++;
                        continue;
                    }

                    if ($lead['assigned_to']) {
                        $results[] = ['lead_id' => $leadId, 'status' => 'skipped', 'reason' => 'Already assigned'];
                        continue;
                    }

                    $assignment = $this->findBestAssociate($lead);
                    if ($assignment['assigned_to']) {
                        $this->assignLead($leadId, $assignment['assigned_to'], $userId);
                        $results[] = [
                            'lead_id' => $leadId,
                            'status' => 'assigned',
                            'assigned_to' => $assignment['assigned_to'],
                            'reason' => $assignment['reason']
                        ];
                        $assigned++;
                    } else {
                        $results[] = ['lead_id' => $leadId, 'status' => 'failed', 'reason' => 'No available associate'];
                        $failed++;
                    }
                } catch (\Throwable $e) {
                    $results[] = ['lead_id' => $leadId, 'status' => 'failed', 'reason' => $e->getMessage()];
                    $failed++;
                }
            }

            return $this->jsonSuccess([
                'total' => count($leadIds),
                'assigned' => $assigned,
                'failed' => $failed,
                'results' => $results
            ]);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Bulk Route Leads');
        }
    }

    /**
     * POST /api/ai/routing/task
     * Route a task to best employee
     */
    public function routeTask()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            $input = $this->getJsonInput();
            $taskData = $input['task'] ?? [];

            if (empty($taskData)) {
                return $this->jsonError('Task data required', 400);
            }

            $assignment = $this->findBestEmployeeForTask($taskData);

            if ($assignment['assigned_to']) {
                $this->createTaskAssignment($taskData, $assignment['assigned_to'], $userId);
            }

            return $this->jsonSuccess([
                'assigned_to' => $assignment['assigned_to'],
                'employee_name' => $assignment['employee_name'] ?? 'Unassigned',
                'reason' => $assignment['reason'],
                'estimated_completion' => $assignment['estimated_completion'] ?? null
            ]);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Route Task');
        }
    }

    /**
     * GET /api/ai/routing/associates
     * Get available associates with load info
     */
    public function getAssociates()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            $department = $this->input('department');
            $category = $this->input('category');

            $associates = $this->getAvailableAssociates($department, $category);

            return $this->jsonSuccess(['associates' => $associates]);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Get Associates');
        }
    }

    /**
     * GET /api/ai/routing/load-balance
     * Get load balancing recommendations
     */
    public function loadBalance()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            if (!$this->isAdminOrManager($userId)) {
                return $this->jsonError('Insufficient permissions', 403);
            }

            $recommendations = $this->getLoadBalancingRecommendations();

            return $this->jsonSuccess(['recommendations' => $recommendations]);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Load Balance');
        }
    }

    /**
     * POST /api/ai/routing/rebalance
     * Trigger load rebalancing
     */
    public function rebalance()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            if (!$this->isAdminOrManager($userId)) {
                return $this->jsonError('Insufficient permissions', 403);
            }

            $input = $this->getJsonInput();
            $dryRun = (bool)($input['dry_run'] ?? true);

            $result = $this->performRebalancing($dryRun, $userId);

            return $this->jsonSuccess($result);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Rebalance');
        }
    }

    // ==================== Private Methods ====================

    private function getLead($leadId)
    {
        $stmt = $this->db->prepare("SELECT l.*, u.name as creator_name FROM leads l LEFT JOIN users u ON l.created_by = u.id WHERE l.id = ? LIMIT 1");
        $stmt->execute([$leadId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function findBestAssociate($lead)
    {
        // Get lead details
        $source = $lead['source'] ?? '';
        $colonyId = $lead['colony_id'] ?? null;
        $budget = (float)($lead['budget'] ?? 0);
        $language = $lead['language'] ?? 'en';

        // Get available associates
        $associates = $this->getAvailableAssociatesForLead($lead);

        if (empty($associates)) {
            return [
                'assigned_to' => null,
                'reason' => 'No available associates',
                'sla_hours' => 4
            ];
        }

        $best = null;
        $bestScore = -1;
        $bestBreakdown = [];

        foreach ($associates as $assoc) {
            $breakdown = [];
            $score = 0;

            // 1. Current load (0-30 points) - lower load = higher score
            $currentLoad = (int)($assoc['current_load'] ?? 0);
            $loadScore = max(0, 30 - $currentLoad * 3);
            $score += $loadScore;
            $breakdown['load'] = $loadScore;

            // 2. Geographic match (0-25 points)
            $geoScore = $this->calculateGeoMatch($assoc, $lead);
            $score += $geoScore;
            $breakdown['geography'] = $geoScore;

            // 3. Source expertise (0-20 points)
            $sourceScore = $this->getSourceExpertiseScore($assoc, $lead['source'] ?? '');
            $score += $sourceScore;
            $breakdown['source_expertise'] = $sourceScore;

            // 4. Budget match (0-15 points)
            $budgetScore = $this->getBudgetMatchScore($assoc, (float)($lead['budget'] ?? 0));
            $score += $budgetScore;
            $breakdown['budget_match'] = $budgetScore;

            // 5. Language match (0-10 points)
            $langScore = $this->getLanguageMatch($assoc, $lead['language'] ?? 'en');
            $score += $langScore;
            $breakdown['language'] = $langScore;

            if ($score > ($bestScore ?? -1)) {
                $bestScore = $score;
                $best = $assoc;
                $bestBreakdown = $breakdown;
            }
        }

        if (!$best) {
            return [
                'assigned_to' => null,
                'reason' => 'No suitable associate found',
                'sla_hours' => 4
            ];
        }

        // Assign lead
        return [
            'assigned_to' => (int)$best['id'],
            'associate_name' => $best['name'],
            'reason' => $this->generateAssignmentReason($best, $bestBreakdown),
            'sla_hours' => 4,
            'score_breakdown' => $bestBreakdown
        ];
    }

    private function findBestEmployeeForTask($taskData)
    {
        $department = $taskData['department'] ?? '';
        $requiredSkills = $taskData['skills'] ?? [];
        $priority = $taskData['priority'] ?? 'medium';
        $location = $taskData['location'] ?? '';

        $sql = "SELECT u.id, u.name, e.department, e.skills, e.location, e.availability,
                (SELECT COUNT(*) FROM tasks t WHERE t.assigned_to = u.id AND t.status IN ('pending', 'in_progress')) as current_load
                FROM users u
                LEFT JOIN employees e ON e.user_id = u.id
                WHERE u.role IN ('employee', 'agent', 'manager')
                AND u.status = 'active'
                AND (e.department = ? OR ? = '')
                AND u.id NOT IN (
                    SELECT assigned_to FROM tasks WHERE status IN ('pending', 'in_progress') AND assigned_to IS NOT NULL
                    GROUP BY assigned_to HAVING COUNT(*) >= 10
                )";

        $params = [$department, $department];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $employees = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($employees)) {
            return ['assigned_to' => null, 'reason' => 'No available employees'];
        }

        $best = null;
        $bestScore = -1;

        foreach ($employees as $emp) {
            $score = 0;
            $skills = json_decode($emp['skills'] ?? '[]', true) ?: [];
            
            // Skill match
            foreach ($requiredSkills as $skill) {
                if (in_array(strtolower($skill), array_map('strtolower', $skills))) {
                    $score += 20;
                }
            }

            // Load (lower is better)
            $load = (int)($emp['current_load'] ?? 0);
            $score += max(0, 50 - $load * 5);

            // Department match
            if (strtolower($emp['department'] ?? '') === strtolower($department)) {
                $score += 20;
            }

            // Availability
            if (($emp['availability'] ?? 1) > 0) {
                $score += 10;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $emp;
            }
        }

        if (!$best) {
            return ['assigned_to' => null, 'reason' => 'No suitable employee'];
        }

        $estimatedHours = 2 + (5 - min(5, (int)($taskData['complexity'] ?? 1))) * 4;
        
        return [
            'assigned_to' => (int)$best['id'],
            'employee_name' => $best['name'],
            'reason' => "Best skill match (score: {$bestScore}), current load: " . ($best['current_load'] ?? 0),
            'estimated_completion' => date('Y-m-d H:i:s', strtotime("+{$estimatedHours} hours"))
        ];
    }

    private function getAvailableAssociates($department = '', $category = '')
    {
        // Real schema: employees has department but no expertise/languages/territory;
        // specializations live on associate_profiles, department on employee_profiles.
        $sql = "SELECT u.id, u.name, u.role, COALESCE(e.department, ep.department) as department, ap.specializations as expertise, ap.preferred_locations as territory,
                (SELECT COUNT(*) FROM leads l WHERE l.assigned_to = u.id AND l.status IN ('new', 'contacted', 'qualified')) as current_load
                FROM users u
                LEFT JOIN employees e ON e.user_id = u.id
                LEFT JOIN associate_profiles ap ON ap.user_id = u.id
                LEFT JOIN employee_profiles ep ON ep.user_id = u.id
                WHERE u.role IN ('associate', 'agent', 'manager')
                AND u.status = 'active'
                AND u.id NOT IN (
                    SELECT assigned_to FROM leads WHERE status IN ('new', 'contacted', 'qualified') AND assigned_to IS NOT NULL
                    GROUP BY assigned_to HAVING COUNT(*) >= 15
                )";

        $params = [];
        if ($department) {
            $sql .= " AND e.department = ?";
            $params[] = $department;
        }

        $sql .= " ORDER BY current_load ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getAvailableAssociatesForLead($lead)
    {
        $colonyId = $lead['colony_id'] ?? null;
        $source = $lead['source'] ?? '';

        // Real schema: employees has department but no expertise/languages/territory;
        // specializations live on associate_profiles, department on employee_profiles.
        $sql = "SELECT u.id, u.name, u.role, COALESCE(e.department, ep.department) as department, ap.specializations as expertise, ap.preferred_locations as territory,
                (SELECT COUNT(*) FROM leads l WHERE l.assigned_to = u.id AND l.status IN ('new', 'contacted', 'qualified')) as current_load
                FROM users u
                LEFT JOIN employees e ON e.user_id = u.id
                LEFT JOIN associate_profiles ap ON ap.user_id = u.id
                LEFT JOIN employee_profiles ep ON ep.user_id = u.id
                WHERE u.role IN ('associate', 'agent', 'manager')
                AND u.status = 'active'
                AND u.id NOT IN (
                    SELECT assigned_to FROM leads WHERE status IN ('new', 'contacted', 'qualified') AND assigned_to IS NOT NULL
                    GROUP BY assigned_to HAVING COUNT(*) >= 15
                )";

        // Prefer associates with matching territory
        if ($colonyId) {
            $sql .= " ORDER BY 
                CASE WHEN COALESCE(ap.preferred_locations, '') LIKE ? THEN 0 ELSE 1 END,
                current_load ASC";
            $params = ["%{$colonyId}%"];
        } else {
            $sql .= " ORDER BY current_load ASC";
            $params = [];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function calculateGeoMatch($assoc, $lead)
    {
        $assocTerritory = $assoc['territory'] ?? '';
        $leadColony = $lead['colony_id'] ?? '';

        if (empty($assocTerritory) || empty($leadColony)) {
            return 5; // Neutral
        }

        // Check if associate territory includes lead colony
        $territories = array_map('trim', explode(',', $assocTerritory));
        if (in_array($leadColony, $territories)) {
            return 25; // Perfect match
        }

        // Check if same city/region (would need colony->city mapping)
        return 10; // Partial
    }

    private function getSourceExpertiseScore($assoc, $source)
    {
        $sourceExpertise = [
            'website' => ['digital', 'online', 'web'],
            'referral' => ['network', 'referral', 'word_of_mouth'],
            'walk_in' => ['offline', 'walkin', 'showroom'],
            'call' => ['phone', 'call', 'telecalling'],
            'social' => ['social', 'facebook', 'instagram', 'whatsapp'],
            'email' => ['email', 'digital'],
            'event' => ['event', 'exhibition', 'offline']
        ];

        $expertise = strtolower($assoc['expertise'] ?? '');
        $keywords = $sourceExpertise[strtolower($source)] ?? [];

        foreach ($keywords as $kw) {
            if (strpos($expertise, $kw) !== false) {
                return 20;
            }
        }

        return 5; // Neutral
    }

    private function getBudgetMatchScore($assoc, $budget)
    {
        if ($budget <= 0) return 10; // Neutral

        $expertise = strtolower($assoc['expertise'] ?? '');
        
        if ($budget < 1000000) { // < 10L
            return (strpos($expertise, 'affordable') !== false || strpos($expertise, 'budget') !== false) ? 15 : 10;
        } elseif ($budget < 5000000) { // 10L-50L
            return (strpos($expertise, 'mid') !== false || strpos($expertise, 'standard') !== false) ? 15 : 10;
        } else { // > 50L
            return (strpos($expertise, 'luxury') !== false || strpos($expertise, 'premium') !== false || strpos($expertise, 'villa') !== false) ? 15 : 10;
        }
    }

    private function getLanguageMatch($assoc, $language)
    {
        $languages = array_map('trim', explode(',', strtolower($assoc['languages'] ?? 'en,hi')));
        return in_array(strtolower($language), $languages) ? 10 : 0;
    }

    private function generateAssignmentReason($assoc, $breakdown)
    {
        $reasons = [];
        if ($breakdown['geography'] >= 20) $reasons[] = 'territory match';
        if ($breakdown['source_expertise'] >= 15) $reasons[] = 'source expertise';
        if ($breakdown['budget_match'] >= 12) $reasons[] = 'budget expertise';
        if ($breakdown['language'] >= 10) $reasons[] = 'language match';
        if ($breakdown['load'] >= 20) $reasons[] = 'available capacity';

        return 'Assigned based on: ' . implode(', ', $reasons ?: ['availability']);
    }

    private function assignLead($leadId, $associateId, $assignedBy)
    {
        $stmt = $this->db->prepare("UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$associateId, $leadId]);

        // Log activity
        $stmt = $this->db->prepare("INSERT INTO lead_activities (lead_id, created_by, activity_type, description, created_at) VALUES (?, ?, 'assigned', ?, NOW())");
        $stmt->execute([$leadId, $assignedBy, json_encode(['assigned_to' => $associateId])]);
    }

    private function getLoadBalancingRecommendations()
    {
        // Get all associates with their loads
        $stmt = $this->db->prepare("SELECT u.id, u.name, e.department,
            (SELECT COUNT(*) FROM leads l WHERE l.assigned_to = u.id AND l.status IN ('new', 'contacted', 'qualified')) as current_load
            FROM users u
            LEFT JOIN employees e ON e.user_id = u.id
            WHERE u.role IN ('associate', 'agent', 'manager') AND u.status = 'active'
            ORDER BY current_load DESC");
        $stmt->execute();
        $associates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($associates)) return [];

        $loads = array_column($associates, 'current_load');
        $maxLoad = max($loads);
        $minLoad = min($loads);
        $avgLoad = array_sum($loads) / count($loads);

        $recommendations = [];

        // Overloaded associates
        foreach ($associates as $assoc) {
            if ($assoc['current_load'] > $avgLoad * 1.5) {
                $recommendations[] = [
                    'type' => 'overloaded',
                    'associate_id' => $assoc['id'],
                    'associate_name' => $assoc['name'],
                    'current_load' => $assoc['current_load'],
                    'avg_load' => round($avgLoad, 1),
                    'action' => 'Redistribute leads to underloaded associates',
                    'priority' => 'high'
                ];
            }
        }

        // Underloaded associates
        foreach ($associates as $assoc) {
            if ($assoc['current_load'] < $avgLoad * 0.5 && $assoc['current_load'] < 5) {
                $recommendations[] = [
                    'type' => 'underloaded',
                    'associate_id' => $assoc['id'],
                    'associate_name' => $assoc['name'],
                    'current_load' => $assoc['current_load'],
                    'action' => 'Assign new leads to this associate',
                    'priority' => 'medium'
                ];
            }
        }

        return $recommendations;
    }

    private function performRebalancing($dryRun, $userId)
    {
        $recommendations = $this->getLoadBalancingRecommendations();
        $moved = 0;

        foreach ($recommendations as $rec) {
            if ($rec['type'] !== 'overloaded') continue;

            // Find leads to move
            $stmt = $this->db->prepare("SELECT id FROM leads WHERE assigned_to = ? AND status IN ('new', 'contacted') ORDER BY created_at ASC LIMIT 3");
            $stmt->execute([$rec['associate_id']]);
            $leadsToMove = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($leadsToMove)) continue;

            // Find underloaded associate
            $underloaded = $this->getUnderloadedAssociate($rec['associate_id']);
            if (!$underloaded) continue;

            foreach ($leadsToMove as $leadId) {
                if (!$dryRun) {
                    $this->assignLead($leadId, $underloaded['id'], $userId);
                }
                $moved++;
            }
        }

        return [
            'dry_run' => $dryRun,
            'leads_moved' => $moved,
            'recommendations_considered' => count($recommendations)
        ];
    }

    private function getUnderloadedAssociate($excludeId)
    {
        $stmt = $this->db->prepare("SELECT u.id, u.name,
            (SELECT COUNT(*) FROM leads l WHERE l.assigned_to = u.id AND l.status IN ('new', 'contacted', 'qualified')) as current_load
            FROM users u
            LEFT JOIN employees e ON e.user_id = u.id
            WHERE u.role IN ('associate', 'agent', 'manager') AND u.status = 'active' AND u.id != ?
            ORDER BY current_load ASC LIMIT 1");
        $stmt->execute([$excludeId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function createTaskAssignment($taskData, $employeeId, $assignedBy)
    {
        $stmt = $this->db->prepare("INSERT INTO tasks (title, description, department, assigned_to, assigned_by, priority, status, due_date, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
        $stmt->execute([
            $taskData['title'] ?? 'Task',
            $taskData['description'] ?? '',
            $taskData['department'] ?? 'general',
            $employeeId,
            $assignedBy,
            $taskData['priority'] ?? 'medium',
            $taskData['due_date'] ?? date('Y-m-d', strtotime('+7 days'))
        ]);
        return $this->db->lastInsertId();
    }

    private function isAdminOrManager($userId)
    {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user && in_array($user['role'] ?? '', ['admin', 'super_admin', 'manager', 'ceo', 'coo', 'director']);
    }

    private function getUserName($userId)
    {
        if (!$userId) return 'Unknown';
        $stmt = $this->db->prepare("SELECT name FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user['name'] ?? 'Unknown';
    }
}