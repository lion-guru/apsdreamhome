<?php
/**
 * EMI Foreclosure Logging and Reporting System
 * Provides comprehensive logging and reporting for EMI foreclosure attempts
 */
class EMIForeclosureLogger {
    public function __construct() {
        // No longer needs a connection passed in
    }

    /**
     * Log a detailed foreclosure attempt
     * 
     * @param int $emiPlanId EMI Plan Identifier
     * @param string $status Status of foreclosure (success/failed)
     * @param string $message Detailed message about the foreclosure attempt
     * @param float $foreClosureAmount Total foreclosure amount
     * @param array $additionalData Additional metadata about the foreclosure
     * @return bool Success of logging operation
     */
    public function logForeclosureAttempt(
        int $emiPlanId, 
        string $status, 
        string $message, 
        float $foreClosureAmount = 0.0, 
        array $additionalData = []
    ): bool {
        try {
            $db = \App\Core\App::database();
            $adminId = $_SESSION['admin_id'] ?? 0;
            
            $query = "INSERT INTO foreclosure_logs (
                emi_plan_id, 
                status, 
                message, 
                foreclosure_amount,
                additional_data,
                attempted_by, 
                attempted_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $additionalDataJson = json_encode($additionalData);
            
            return $db->execute($query, [
                $emiPlanId, 
                $status, 
                $message, 
                $foreClosureAmount,
                $additionalDataJson,
                $adminId
            ]);
        } catch (Exception $e) {
            // Log internal error if logging fails
            error_log("EMI Foreclosure Logging Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a comprehensive foreclosure report
     * 
     * @param array $filters Filters for report generation
     * @return array Foreclosure report data
     */
    public function generateForeclosureReport(array $filters = []): array {
        try {
            $db = \App\Core\App::database();
            $query = "SELECT 
                fl.id, 
                fl.emi_plan_id, 
                ep.customer_id,
                c.name AS customer_name,
                fl.status, 
                fl.message, 
                fl.foreclosure_amount,
                fl.attempted_at,
                u.auser AS admin_name
            FROM foreclosure_logs fl
            JOIN emi_plans ep ON fl.emi_plan_id = ep.id
            JOIN customers c ON ep.customer_id = c.id
            JOIN admin u ON fl.attempted_by = u.id
            WHERE 1=1";

            // Apply filters dynamically
            $paramValues = [];

            if (!empty($filters['start_date'])) {
                $query .= " AND fl.attempted_at >= ?";
                $paramValues[] = $filters['start_date'];
            }

            if (!empty($filters['end_date'])) {
                $query .= " AND fl.attempted_at <= ?";
                $paramValues[] = $filters['end_date'];
            }

            if (!empty($filters['status'])) {
                $query .= " AND fl.status = ?";
                $paramValues[] = $filters['status'];
            }

            if (!empty($filters['emi_plan_id'])) {
                $query .= " AND fl.emi_plan_id = ?";
                $paramValues[] = $filters['emi_plan_id'];
            }

            $query .= " ORDER BY fl.attempted_at DESC";

            return $db->fetchAll($query, $paramValues);
        } catch (Exception $e) {
            error_log("EMI Foreclosure Report Generation Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate foreclosure statistics
     * 
     * @return array Foreclosure statistics
     */
    public function getForeclosureStatistics(): array {
        try {
            $query = "SELECT 
                COUNT(*) as total_attempts,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_attempts,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_attempts,
                SUM(foreclosure_amount) as total_foreclosure_amount
            FROM foreclosure_logs";

            $result = $this->conn->query($query);
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Foreclosure Statistics Error: " . $e->getMessage());
            return [];
        }
    }
}
