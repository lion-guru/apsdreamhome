<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Services\AI;
/**
 * Investment Manager
 * Handles Investment Plans, ROI Calculations, and Plan Automation.
 */
class InvestmentManager {
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct() {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * Add or Update Investment Plan
     */
    public function savePlan($data) {
        $id = $data['id'] ?? null;
        $name = $data['name'];
        $desc = $data['description'];
        $min = $data['min_amount'];
        $roi = $data['roi'];
        $duration = $data['duration'];
        $type = $data['type'];
        $start = $data['start_date'] ?? null;
        $end = $data['end_date'] ?? null;
        $doc = $data['document_path'] ?? null;

if ($id) {
            try {
                $tenantSql = $this->tenantSql();
                $tenantVal = $this->tenantId() > 1 ? [$this->tenantId()] : [];
                $sql = "UPDATE investment_plans SET name=?, description=?, min_amount=?, expected_roi_percentage=?, duration_months=?, plan_type=?, start_date=?, end_date=?, document_path=? WHERE id=?" . $tenantSql;
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            return $this->db->execute($sql, array_merge([$name, $desc, $min, $roi, $duration, $type, $start, $end, $doc, $id], $tenantVal));
        } else {
            try {
                $tenantData = $this->tenantInsertData();
                $tenantCols = array_keys($tenantData);
                $tenantVals = array_values($tenantData);
                $columns = array_merge(['name', 'description', 'min_amount', 'expected_roi_percentage', 'duration_months', 'plan_type', 'start_date', 'end_date', 'document_path'], $tenantCols);
                $values  = array_merge([$name, $desc, $min, $roi, $duration, $type, $start, $end, $doc], $tenantVals);
                $colStr = implode(', ', $columns);
                $placeholders = implode(', ', array_fill(0, count($values), '?'));
                $sql = "INSERT INTO investment_plans ($colStr) VALUES ($placeholders)";
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
return $this->db->execute($sql, $values);
        }
    }

    /**
     * Toggle Plan Status
     */
    public function toggleStatus($planId, $status, $userId, $reason = '') {
        $active = ($status == 'active') ? 1 : 0;
        $planIdInt = intval($planId);
        $this->db->execute("UPDATE investment_plans SET is_active = ? WHERE id = ?", [$active, $planIdInt]);

        try {
            $tenantData = $this->tenantInsertData();
            $tenantCols = array_keys($tenantData);
            $tenantVals = array_values($tenantData);
            $columns = array_merge(['plan_id', 'status', 'changed_by', 'reason'], $tenantCols);
            $values  = array_merge([$planIdInt, $active, $userId, $reason], $tenantVals);
            $colStr = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $sql = "INSERT INTO plan_status_history ($colStr) VALUES ($placeholders)";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        return $this->db->execute($sql, $values);
    }

    /**
     * ROI Calculator for Leads
     */
    public function calculateROI($planId, $investAmount) {
        $sql = "SELECT * FROM investment_plans WHERE id = ?";
        $plan = $this->db->fetch($sql, [$planId]);

        if (!$plan) return false;

        $roi = $plan['expected_roi_percentage'];
        $duration = $plan['duration_months'];
        
        $totalReturn = $investAmount + ($investAmount * ($roi / 100) * ($duration / 12));
        $profit = $totalReturn - $investAmount;

        return [
            'plan_name' => $plan['name'],
            'invest_amount' => $investAmount,
            'roi_percentage' => $roi,
            'duration_months' => $duration,
            'total_return' => $totalReturn,
            'profit' => $profit,
            'monthly_payout' => $profit / $duration
        ];
    }

    /**
     * Get Active Plans for Bot Response
     */
    public function getActivePlans() {
        try {
            $sql = "SELECT * FROM investment_plans WHERE is_active = 1 AND (end_date IS NULL OR end_date >= CURDATE())";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        return $this->db->fetchAll($sql);
    }
}
?>
