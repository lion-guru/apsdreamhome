<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class SustainableTechService
{
    use ServiceTenantTrait;
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ==================== CERTIFICATIONS ====================

    public function getCertifications(array $filters = []): array
    {
        $sql = "SELECT * FROM sustainability_certifications WHERE 1=1" . $this->tenantSql();
        $params = [];
        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = $filters['is_active'];
        }
        $sql .= " ORDER BY name";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getCertification(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM sustainability_certifications WHERE id = ?" . $this->tenantSql(), [$id]);
    }

    public function saveCertification(array $data): int
    {
        if (!empty($data['id'])) {
            $this->db->execute(
                "UPDATE sustainability_certifications SET name=?, code=?, authority=?, level=?, description=?, icon=?, color=?, is_active=?, updated_at=NOW() WHERE id=?" . $this->tenantSql(),
                [$data['name'], $data['code'], $data['authority'], $data['level'], $data['description'], $data['icon'], $data['color'], $data['is_active'] ?? 1, $data['id']]
            );
            return (int)$data['id'];
        }
        $insertData = $this->tenantInsertData();
        $cols = "name, code, authority, level, description, icon, color, is_active";
        $vals = "?, ?, ?, ?, ?, ?, ?, ?";
        $params = [$data['name'], $data['code'], $data['authority'], $data['level'], $data['description'], $data['icon'], $data['color'], $data['is_active'] ?? 1];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $vals .= ", " . str_repeat('?,', count($insertData) - 1) . '?';
            $params = array_merge($params, array_values($insertData));
        }
        $this->db->execute("INSERT INTO sustainability_certifications ($cols) VALUES ($vals)", $params);
        return (int)$this->db->lastInsertId();
    }

    public function deleteCertification(int $id): void
    {
        $this->db->execute("DELETE FROM sustainability_certifications WHERE id = ?" . $this->tenantSql(), [$id]);
    }

    // ==================== GREEN FEATURES ====================

    public function getFeatures(array $filters = []): array
    {
        $sql = "SELECT * FROM green_features WHERE 1=1" . $this->tenantSql();
        $params = [];
        if (!empty($filters['category'])) { $sql .= " AND category = ?"; $params[] = $filters['category']; }
        if (isset($filters['is_active'])) { $sql .= " AND is_active = ?"; $params[] = $filters['is_active']; }
        $sql .= " ORDER BY category, name";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getFeature(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM green_features WHERE id = ?" . $this->tenantSql(), [$id]);
    }

    public function saveFeature(array $data): int
    {
        if (!empty($data['id'])) {
            $this->db->execute(
                "UPDATE green_features SET name=?, category=?, description=?, co2_saved_kg_yr=?, cost_estimate=?, icon=?, is_active=?, updated_at=NOW() WHERE id=?" . $this->tenantSql(),
                [$data['name'], $data['category'], $data['description'], $data['co2_saved_kg_yr'] ?? 0, $data['cost_estimate'], $data['icon'], $data['is_active'] ?? 1, $data['id']]
            );
            return (int)$data['id'];
        }
        $insertData = $this->tenantInsertData();
        $cols = "name, category, description, co2_saved_kg_yr, cost_estimate, icon, is_active";
        $vals = "?, ?, ?, ?, ?, ?, ?";
        $params = [$data['name'], $data['category'], $data['description'], $data['co2_saved_kg_yr'] ?? 0, $data['cost_estimate'], $data['icon'], $data['is_active'] ?? 1];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $vals .= ", " . str_repeat('?,', count($insertData) - 1) . '?';
            $params = array_merge($params, array_values($insertData));
        }
        $this->db->execute("INSERT INTO green_features ($cols) VALUES ($vals)", $params);
        return (int)$this->db->lastInsertId();
    }

    public function deleteFeature(int $id): void
    {
        $this->db->execute("DELETE FROM green_features WHERE id = ?" . $this->tenantSql(), [$id]);
    }

    // ==================== ENERGY AUDITS ====================

    public function getAudits(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $sql = "SELECT * FROM energy_audits WHERE 1=1" . $this->tenantSql();
        $params = [];
        if (!empty($filters['status'])) { $sql .= " AND status = ?"; $params[] = $filters['status']; }
        if (!empty($filters['project_id'])) { $sql .= " AND project_id = ?"; $params[] = $filters['project_id']; }
        $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET " . (($page - 1) * $limit);
        $data = $this->db->fetchAll($sql, $params) ?: [];

        $total = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM energy_audits" . $this->tenantSql())['c'] ?? 0);
        return ['data' => $data, 'total' => $total, 'page' => $page, 'limit' => $limit];
    }

    public function getAudit(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM energy_audits WHERE id = ?" . $this->tenantSql(), [$id]);
    }

    public function saveAudit(array $data): int
    {
        if (!empty($data['id'])) {
            $this->db->execute(
                "UPDATE energy_audits SET project_id=?, project_name=?, audit_date=?, auditor_name=?, energy_score=?, annual_kwh=?, solar_capacity_kwp=?, water_savings_kl=?, renewable_pct=?, estimated_co2_tonnes_yr=?, notes=?, recommendations=?, status=?, updated_at=NOW() WHERE id=?" . $this->tenantSql(),
                [$data['project_id'], $data['project_name'], $data['audit_date'], $data['auditor_name'], $data['energy_score'], $data['annual_kwh'], $data['solar_capacity_kwp'], $data['water_savings_kl'], $data['renewable_pct'], $data['estimated_co2_tonnes_yr'], $data['notes'], isset($data['recommendations']) ? json_encode($data['recommendations']) : null, $data['status'], $data['id']]
            );
            return (int)$data['id'];
        }
        $insertData = $this->tenantInsertData();
        $cols = "project_id, project_name, audit_date, auditor_name, energy_score, annual_kwh, solar_capacity_kwp, water_savings_kl, renewable_pct, estimated_co2_tonnes_yr, notes, recommendations, status";
        $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
        $params = [$data['project_id'], $data['project_name'], $data['audit_date'], $data['auditor_name'], $data['energy_score'], $data['annual_kwh'], $data['solar_capacity_kwp'], $data['water_savings_kl'], $data['renewable_pct'], $data['estimated_co2_tonnes_yr'], $data['notes'], isset($data['recommendations']) ? json_encode($data['recommendations']) : null, $data['status']];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $vals .= ", " . str_repeat('?,', count($insertData) - 1) . '?';
            $params = array_merge($params, array_values($insertData));
        }
        $this->db->execute("INSERT INTO energy_audits ($cols) VALUES ($vals)", $params);
        return (int)$this->db->lastInsertId();
    }

    public function deleteAudit(int $id): void
    {
        $this->db->execute("DELETE FROM energy_audits WHERE id = ?" . $this->tenantSql(), [$id]);
    }

    // ==================== CARBON CREDITS ====================

    public function getCarbonLedger(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $sql = "SELECT * FROM carbon_credit_ledger WHERE 1=1" . $this->tenantSql();
        $params = [];
        if (!empty($filters['reference_type'])) { $sql .= " AND reference_type = ?"; $params[] = $filters['reference_type']; }
        if (isset($filters['verified'])) { $sql .= " AND verified = ?"; $params[] = $filters['verified']; }
        $sql .= " ORDER BY credit_date DESC LIMIT $limit OFFSET " . (($page - 1) * $limit);
        $data = $this->db->fetchAll($sql, $params) ?: [];

        $total = (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM carbon_credit_ledger" . $this->tenantSql())['c'] ?? 0);
        $summary = $this->db->fetchOne("SELECT COALESCE(SUM(credits_earned),0) as total_credits, COALESCE(SUM(total_value),0) as total_value FROM carbon_credit_ledger" . $this->tenantSql()) ?: ['total_credits' => 0, 'total_value' => 0];

        return ['data' => $data, 'total' => $total, 'page' => $page, 'limit' => $limit, 'summary' => $summary];
    }

    public function saveCarbonEntry(array $data): int
    {
        if (!empty($data['id'])) {
            $this->db->execute(
                "UPDATE carbon_credit_ledger SET reference_type=?, reference_id=?, credit_type=?, credits_earned=?, credit_date=?, value_per_credit=?, total_value=?, verified=?, notes=?, updated_at=NOW() WHERE id=?" . $this->tenantSql(),
                [$data['reference_type'], $data['reference_id'], $data['credit_type'], $data['credits_earned'], $data['credit_date'], $data['value_per_credit'], $data['total_value'], $data['verified'] ?? 0, $data['notes'], $data['id']]
            );
            return (int)$data['id'];
        }
        $insertData = $this->tenantInsertData();
        $cols = "reference_type, reference_id, credit_type, credits_earned, credit_date, value_per_credit, total_value, verified, notes";
        $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?";
        $params = [$data['reference_type'], $data['reference_id'], $data['credit_type'], $data['credits_earned'], $data['credit_date'], $data['value_per_credit'], $data['total_value'], $data['verified'] ?? 0, $data['notes']];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $vals .= ", " . str_repeat('?,', count($insertData) - 1) . '?';
            $params = array_merge($params, array_values($insertData));
        }
        $this->db->execute("INSERT INTO carbon_credit_ledger ($cols) VALUES ($vals)", $params);
        return (int)$this->db->lastInsertId();
    }

    public function deleteCarbonEntry(int $id): void
    {
        $this->db->execute("DELETE FROM carbon_credit_ledger WHERE id = ?" . $this->tenantSql(), [$id]);
    }
}
