<?php

namespace App\Services\AI;
/**
 * Investment Manager
 * Handles Investment Plans, ROI Calculations, and Plan Automation.
 */
class InvestmentManager {
    private $db;

    public function __construct() {
        $this->db = \App\Core\App::database();
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
            $sql = "UPDATE investment_plans SET name=?, description=?, min_amount=?, expected_roi_percentage=?, duration_months=?, plan_type=?, start_date=?, end_date=?, document_path=? WHERE id=?";
            return $this->db->execute($sql, [$name, $desc, $min, $roi, $duration, $type, $start, $end, $doc, $id]);
        } else {
            $sql = "INSERT INTO investment_plans (name, description, min_amount, expected_roi_percentage, duration_months, plan_type, start_date, end_date, document_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            return $this->db->execute($sql, [$name, $desc, $min, $roi, $duration, $type, $start, $end, $doc]);
        }
    }

    /**
     * Toggle Plan Status
     */
    public function toggleStatus($planId, $status, $userId, $reason = '') {
        $active = ($status == 'active') ? 1 : 0;
        $planIdInt = intval($planId);
        $this->db->execute("UPDATE investment_plans SET is_active = ? WHERE id = ?", [$active, $planIdInt]);
        
        $sql = "INSERT INTO plan_status_history (plan_id, status, changed_by, reason) VALUES (?, ?, ?, ?)";
        return $this->db->execute($sql, [$planId, $status, $userId, $reason]);
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
        $sql = "SELECT * FROM investment_plans WHERE is_active = 1 AND (end_date IS NULL OR end_date >= CURDATE())";
        return $this->db->fetchAll($sql);
    }
}
?>
