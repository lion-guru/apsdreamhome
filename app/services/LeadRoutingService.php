<?php
/**
 * LeadRoutingService — Department-based lead routing engine
 * Phase 3: Auto-routes leads to departments/users based on rules
 */
namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

class LeadRoutingService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ─────────── Rule CRUD ──────────────────────────────────────────

    public function getAllRules(): array {
        try {
            return $this->db->fetchAll(
                "SELECT rr.*, d.name as department_name, u.name as target_user_name
                 FROM crm_routing_rules rr
                 LEFT JOIN departments d ON d.id = rr.target_department_id
                 LEFT JOIN users u ON u.id = rr.target_user_id
                 ORDER BY rr.priority ASC, rr.created_at DESC"
            ) ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getActiveRules(): array {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM crm_routing_rules WHERE is_active = 1 ORDER BY priority ASC"
            ) ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getRuleById(int $id): ?array {
        try {
            return $this->db->fetch("SELECT * FROM crm_routing_rules WHERE id = ?", [$id]);
        } catch (\Exception $e) { return null; }
    }

    public function createRule(array $data): array {
        try {
            $this->db->query(
                "INSERT INTO crm_routing_rules (name, source_pattern, city_pattern, min_budget, max_budget, target_department_id, target_user_id, priority, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['name'] ?? '',
                    $data['source_pattern'] ?? '*',
                    $data['city_pattern'] ?? '*',
                    $data['min_budget'] ?? 0,
                    $data['max_budget'] ?? 0,
                    $data['target_department_id'] ?? null,
                    $data['target_user_id'] ?? null,
                    $data['priority'] ?? 100,
                    $data['is_active'] ?? 1,
                ]
            );
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateRule(int $id, array $data): array {
        try {
            $sets = [];
            $params = [];
            $allowed = ['name', 'source_pattern', 'city_pattern', 'min_budget', 'max_budget', 'target_department_id', 'target_user_id', 'priority', 'is_active'];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) {
                    $sets[] = "$f = ?";
                    $params[] = $data[$f];
                }
            }
            if (empty($sets)) return ['success' => false, 'error' => 'No fields to update'];
            $sets[] = "updated_at = NOW()";
            $params[] = $id;
            $this->db->query("UPDATE crm_routing_rules SET " . implode(', ', $sets) . " WHERE id = ?", $params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteRule(int $id): array {
        try {
            $this->db->query("DELETE FROM crm_routing_rules WHERE id = ?", [$id]);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────── Routing Engine ──────────────────────────────────────

    /**
     * Route a lead based on matching rules.
     * Matches: source pattern → city pattern → budget range.
     * Returns matched rule or null (no routing).
     */
    public function routeLead(int $leadId): ?array {
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);
        if (!$lead) return null;

        $rules = $this->getActiveRules();
        if (empty($rules)) return null;

        foreach ($rules as $rule) {
            if ($this->matchesRule($lead, $rule)) {
                $this->applyRouting($leadId, $rule);
                return $rule;
            }
        }
        return null;
    }

    private function matchesRule(array $lead, array $rule): bool {
        // Source pattern match
        $sourcePattern = strtolower(trim($rule['source_pattern'] ?? '*'));
        $leadSource = strtolower($lead['source'] ?? '');
        if ($sourcePattern !== '*' && $sourcePattern !== '') {
            $patterns = array_map('trim', explode(',', $sourcePattern));
            $matched = false;
            foreach ($patterns as $p) {
                if ($p === $leadSource || fnmatch($p, $leadSource, FNM_CASEFOLD)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) return false;
        }

        // City pattern match
        $cityPattern = strtolower(trim($rule['city_pattern'] ?? '*'));
        $leadCity = strtolower($lead['city'] ?? '');
        if ($cityPattern !== '*' && $cityPattern !== '') {
            $patterns = array_map('trim', explode(',', $cityPattern));
            $matched = false;
            foreach ($patterns as $p) {
                if ($p === $leadCity || fnmatch($p, $leadCity, FNM_CASEFOLD)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) return false;
        }

        // Budget range match
        $budget = (float)($lead['budget'] ?? 0);
        $minBudget = (float)($rule['min_budget'] ?? 0);
        $maxBudget = (float)($rule['max_budget'] ?? 0);
        if ($minBudget > 0 && $budget < $minBudget) return false;
        if ($maxBudget > 0 && $budget > $maxBudget) return false;

        return true;
    }

    private function applyRouting(int $leadId, array $rule): void {
        $targetUserId = $rule['target_user_id'] ?? null;

        if (!$targetUserId && !empty($rule['target_department_id'])) {
            $targetUserId = $this->getLeastLoadedUser((int)$rule['target_department_id']);
        }

        if ($targetUserId) {
            $crmService = new CRMService();
            $crmService->assignLead($leadId, (int)$targetUserId, 1, "Auto-routed: {$rule['name']}");
        }

        // Log the routing decision
        try {
            $this->db->query(
                "INSERT INTO lead_routing_log (lead_id, rule_id, target_department_id, target_user_id, routed_at)
                 VALUES (?, ?, ?, ?, NOW())",
                [$leadId, $rule['id'], $rule['target_department_id'] ?? null, $targetUserId]
            );
        } catch (\Exception $e) {
            error_log('LeadRoutingService::applyRouting log error: ' . $e->getMessage());
        }
    }

    /**
     * Get the user in a department with the fewest active leads (round-robin by load)
     */
    private function getLeastLoadedUser(int $departmentId): ?int {
        try {
            $row = $this->db->fetch(
                "SELECT u.id, COUNT(l.id) as lead_count
                 FROM users u
                 LEFT JOIN leads l ON l.assigned_to = u.id AND l.deleted_at IS NULL
                 WHERE u.deleted_at IS NULL
                   AND EXISTS (SELECT 1 FROM employee_designation_roles edr WHERE edr.user_id = u.id AND edr.department_id = ?)
                 GROUP BY u.id
                 ORDER BY lead_count ASC
                 LIMIT 1",
                [$departmentId]
            );
            return $row ? (int)$row['id'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // ─────────── Stats ──────────────────────────────────────────────

    public function getRoutingStats(): array {
        try {
            $totalRules = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM crm_routing_rules")['cnt'];
            $activeRules = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM crm_routing_rules WHERE is_active = 1")['cnt'];
            $routedToday = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM lead_routing_log WHERE DATE(routed_at) = CURDATE()")['cnt'];
            $routedTotal = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM lead_routing_log")['cnt'];

            $topRules = $this->db->fetchAll(
                "SELECT rr.name, COUNT(rl.id) as route_count
                 FROM crm_routing_rules rr
                 LEFT JOIN lead_routing_log rl ON rl.rule_id = rr.id
                 GROUP BY rr.id ORDER BY route_count DESC LIMIT 5"
            ) ?: [];

            return [
                'total_rules' => $totalRules,
                'active_rules' => $activeRules,
                'routed_today' => $routedToday,
                'routed_total' => $routedTotal,
                'top_rules' => $topRules,
            ];
        } catch (\Exception $e) {
            return ['total_rules' => 0, 'active_rules' => 0, 'routed_today' => 0, 'routed_total' => 0, 'top_rules' => []];
        }
    }
}
