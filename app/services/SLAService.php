<?php
/**
 * SLAService — SLA compliance tracking and breach alerts
 */
namespace App\Services;

use App\Core\Database;

class SLAService
{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllRules() {
        try {
            return $this->db->fetchAll("SELECT * FROM crm_sla_rules ORDER BY rule_type ASC, target_minutes ASC") ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getActiveRules() {
        try {
            return $this->db->fetchAll("SELECT * FROM crm_sla_rules WHERE is_active = 1") ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getRuleById($id) {
        try {
            return $this->db->fetch("SELECT * FROM crm_sla_rules WHERE id = ?", [$id]);
        } catch (\Exception $e) { return null; }
    }

    public function createRule($data) {
        try {
            $this->db->query(
                "INSERT INTO crm_sla_rules (name, rule_type, target_minutes, applies_to_roles, applies_to_stages, is_active) VALUES (?, ?, ?, ?, ?, ?)",
                [$data['name'], $data['rule_type'], $data['target_minutes'], $data['applies_to_roles'] ?? 'all', $data['applies_to_stages'] ?? 'all', $data['is_active'] ?? 1]
            );
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function updateRule($id, $data) {
        try {
            $this->db->query(
                "UPDATE crm_sla_rules SET name = ?, rule_type = ?, target_minutes = ?, applies_to_roles = ?, applies_to_stages = ?, is_active = ? WHERE id = ?",
                [$data['name'], $data['rule_type'], $data['target_minutes'], $data['applies_to_roles'] ?? 'all', $data['applies_to_stages'] ?? 'all', $data['is_active'] ?? 1, $id]
            );
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function deleteRule($id) {
        try {
            $this->db->query("DELETE FROM crm_sla_logs WHERE sla_rule_id = ?", [$id]);
            $this->db->query("DELETE FROM crm_sla_rules WHERE id = ?", [$id]);
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function startSLA($leadId, $ruleId) {
        try {
            $existing = $this->db->fetch("SELECT id FROM crm_sla_logs WHERE lead_id = ? AND sla_rule_id = ? AND status = 'pending'", [$leadId, $ruleId]);
            if ($existing) return ['success' => true, 'log_id' => $existing['id']];

            $this->db->query(
                "INSERT INTO crm_sla_logs (lead_id, sla_rule_id, started_at, status) VALUES (?, ?, NOW(), 'pending')",
                [$leadId, $ruleId]
            );
            return ['success' => true, 'log_id' => $this->db->lastInsertId()];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function completeSLA($logId, $status = 'met', $notes = '') {
        try {
            $log = $this->db->fetch("SELECT * FROM crm_sla_logs WHERE id = ?", [$logId]);
            if (!$log) return ['success' => false, 'error' => 'SLA log not found'];

            $started = strtotime($log['started_at']);
            $responseTime = time() - $started;

            $this->db->query(
                "UPDATE crm_sla_logs SET ended_at = NOW(), status = ?, response_time_seconds = ?, notes = ? WHERE id = ?",
                [$status, $responseTime, $notes, $logId]
            );
            return ['success' => true, 'response_time' => $responseTime];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function getComplianceStats($days = 30) {
        try {
            $stats = $this->db->fetch(
                "SELECT COUNT(*) as total,
                    SUM(CASE WHEN status = 'met' THEN 1 ELSE 0 END) as met,
                    SUM(CASE WHEN status = 'missed' THEN 1 ELSE 0 END) as missed,
                    SUM(CASE WHEN status = 'breached' THEN 1 ELSE 0 END) as breached,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    AVG(CASE WHEN status IN ('met','missed') THEN response_time_seconds END) as avg_response
                 FROM crm_sla_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            );
            $stats['compliance_rate'] = ($stats['met'] + $stats['missed']) > 0
                ? round(($stats['met'] / ($stats['met'] + $stats['missed'])) * 100, 1) : 0;
            return $stats ?: ['total' => 0, 'met' => 0, 'missed' => 0, 'breached' => 0, 'pending' => 0, 'avg_response' => 0, 'compliance_rate' => 0];
        } catch (\Exception $e) { return ['total' => 0, 'met' => 0, 'missed' => 0, 'breached' => 0, 'pending' => 0, 'avg_response' => 0, 'compliance_rate' => 0]; }
    }

    public function getBreachedSLAs($limit = 50) {
        try {
            return $this->db->fetchAll(
                "SELECT sl.*, sr.name as rule_name, sr.rule_type, sr.target_minutes, l.name as lead_name, l.phone as lead_phone
                 FROM crm_sla_logs sl
                 JOIN crm_sla_rules sr ON sr.id = sl.sla_rule_id
                 LEFT JOIN leads l ON l.id = sl.lead_id
                 WHERE sl.status IN ('missed','breached')
                 ORDER BY sl.created_at DESC LIMIT ?",
                [$limit]
            ) ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function getPendingSLAs() {
        try {
            return $this->db->fetchAll(
                "SELECT sl.*, sr.name as rule_name, sr.rule_type, sr.target_minutes, l.name as lead_name, l.phone as lead_phone,
                    TIMESTAMPDIFF(MINUTE, sl.started_at, NOW()) as elapsed_minutes
                 FROM crm_sla_logs sl
                 JOIN crm_sla_rules sr ON sr.id = sl.sla_rule_id
                 LEFT JOIN leads l ON l.id = sl.lead_id
                 WHERE sl.status = 'pending'
                 ORDER BY sl.started_at ASC"
            ) ?: [];
        } catch (\Exception $e) { return []; }
    }

    public function autoCheckBreaches() {
        try {
            $pending = $this->getPendingSLAs();
            $breached = 0;
            foreach ($pending as $sla) {
                if ($sla['elapsed_minutes'] > $sla['target_minutes']) {
                    $this->completeSLA($sla['id'], 'breached', 'Auto-detected breach: exceeded ' . $sla['target_minutes'] . ' min target');
                    $breached++;
                }
            }
            return ['success' => true, 'breached' => $breached];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }
}
