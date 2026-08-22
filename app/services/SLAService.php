<?php
/**
 * SLAService — SLA compliance tracking and breach alerts
 */
namespace App\Services;

use App\Core\Database;

use \App\Traits\ServiceTenantTrait;

class SLAService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllRules() {
        try {
            return $this->db->fetchAll("SELECT * FROM crm_sla_rules ORDER BY rule_type ASC, target_minutes ASC" . $this->tenantSql()) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getActiveRules() {
        try {
            return $this->db->fetchAll("SELECT * FROM crm_sla_rules WHERE is_active = 1" . $this->tenantSql()) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getRuleById($id) {
        try {
            return $this->db->fetch("SELECT * FROM crm_sla_rules WHERE id = ?" . $this->tenantSql(), array_merge([$id], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }

    public function createRule($data) {
        try {
            $insertCols = array_merge(['name', 'rule_type', 'target_minutes', 'applies_to_roles', 'applies_to_stages', 'is_active'], array_keys($this->tenantInsertData()));
            $insertVals = array_merge([$data['name'], $data['rule_type'], $data['target_minutes'], $data['applies_to_roles'] ?? 'all', $data['applies_to_stages'] ?? 'all', $data['is_active'] ?? 1], array_values($this->tenantInsertData()));
            $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
            $this->db->query(
                "INSERT INTO crm_sla_rules (" . implode(', ', $insertCols) . ") VALUES (" . $placeholders . ")",
                $insertVals
            );
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function updateRule($id, $data) {
        try {
            $this->db->query(
                "UPDATE crm_sla_rules SET name = ?, rule_type = ?, target_minutes = ?, applies_to_roles = ?, applies_to_stages = ?, is_active = ?" . $this->tenantSql() . " AND id = ?",
                array_merge([$data['name'], $data['rule_type'], $data['target_minutes'], $data['applies_to_roles'] ?? 'all', $data['applies_to_stages'] ?? 'all', $data['is_active'] ?? 1, $id], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function deleteRule($id) {
        try {
            $this->db->query("DELETE FROM crm_sla_logs WHERE sla_rule_id = ?" . $this->tenantSql(), array_merge([$id], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            $this->db->query("DELETE FROM crm_sla_rules WHERE id = ?" . $this->tenantSql(), array_merge([$id], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            return ['success' => true];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function startSLA($leadId, $ruleId) {
        try {
            $existing = $this->db->fetch("SELECT id FROM crm_sla_logs WHERE lead_id = ? AND sla_rule_id = ? AND status = 'pending'" . $this->tenantSql(), array_merge([$leadId, $ruleId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            if ($existing) return ['success' => true, 'log_id' => $existing['id']];

            $insertCols = array_merge(['lead_id', 'sla_rule_id', 'started_at', 'status'], array_keys($this->tenantInsertData()));
            $insertVals = array_merge([$leadId, $ruleId, 'NOW()', 'pending'], array_values($this->tenantInsertData()));
            $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
            $this->db->query(
                "INSERT INTO crm_sla_logs (" . implode(', ', $insertCols) . ") VALUES (" . $placeholders . ")",
                $insertVals
            );
            return ['success' => true, 'log_id' => $this->db->lastInsertId()];
        } catch (\Exception $e) { return ['success' => false, 'error' => $e->getMessage()]; }
    }

    public function completeSLA($logId, $status = 'met', $notes = '') {
        try {
            $log = $this->db->fetch("SELECT * FROM crm_sla_logs WHERE id = ?" . $this->tenantSql(), [$logId]);
            if (!$log) return ['success' => false, 'error' => 'SLA log not found'];

            $started = strtotime($log['started_at']);
            $responseTime = time() - $started;

            $this->db->query(
                "UPDATE crm_sla_logs SET ended_at = NOW(), status = ?, response_time_seconds = ?, notes = ?" . $this->tenantSql() . " AND id = ?",
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
                 FROM crm_sla_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)" . $this->tenantSql(),
                array_merge([$days], $this->tenantId() > 1 ? [$this->tenantId()] : [])
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
                 JOIN crm_sla_rules sr ON sr.id = sl.sla_rule_id" . $this->tenantSqlForAlias('sr') . "
                 LEFT JOIN leads l ON l.id = sl.lead_id" . $this->tenantSqlForAlias('l') . "
                 WHERE sl.status IN ('missed','breached')" . $this->tenantSqlForAlias('sl') . "
                 ORDER BY sl.created_at DESC LIMIT ?",
                [$limit]
            ) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }

    public function getPendingSLAs() {
        try {
            return $this->db->fetchAll(
                "SELECT sl.*, sr.name as rule_name, sr.rule_type, sr.target_minutes, l.name as lead_name, l.phone as lead_phone,
                    TIMESTAMPDIFF(MINUTE, sl.started_at, NOW()) as elapsed_minutes
                 FROM crm_sla_logs sl
                 JOIN crm_sla_rules sr ON sr.id = sl.sla_rule_id" . $this->tenantSqlForAlias('sr') . "
                 LEFT JOIN leads l ON l.id = sl.lead_id" . $this->tenantSqlForAlias('l') . "
                 WHERE sl.status = 'pending'" . $this->tenantSqlForAlias('sl') . "
                 ORDER BY sl.started_at ASC"
            ) ?: [];
        } catch (\Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
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
