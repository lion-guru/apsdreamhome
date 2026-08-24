<?php

namespace App\Services;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

/**
 * SalariedAgentService — Company Payroll Agent Commission Management
 * ──────────────────────────────────────────────────────────────────
 * Handles:
 *   • Salary structure CRUD for salaried sales agents
 *   • Monthly payroll calculation (basic + HRA + TA/DA + allowances)
 *   • Per-plot sale incentive calculation
 *   • Payslip data generation
 *
 * Tables used (read):
 *   salaried_agent_structures, associates, plot_bookings, users
 *
 * Tables written:
 *   (none directly — caller persists payslip / payout records)
 */
class SalariedAgentService
{
    use ServiceTenantTrait;

    /** @var PDO */
    private $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        if ($this->pdo === null) {
            $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
        }
    }

    /* ================================================================
       SALARY STRUCTURE — CRUD
       ================================================================ */

    /**
     * Get the currently active salary structure for a salaried agent.
     *
     * @param int $userId  The associate/agent user_id
     * @return array|null
     */
    public function getSalaryStructure(int $userId): ?array
    {
        try {
            $tid    = (int)$this->tenantId();
            $tWhere = $tid > 1 ? ' AND tenant_id = ?' : '';
            $params = [$userId];
            if ($tid > 1) {
                $params[] = $tid;
            }

            $stmt = $this->pdo->prepare("
                SELECT s.*, u.name AS set_by_name
                FROM salaried_agent_structures s
                LEFT JOIN users u ON u.id = s.set_by_user_id
                WHERE s.user_id = ?
                  {$tWhere}
                  AND s.effective_from <= CURDATE()
                  AND (s.effective_to IS NULL OR s.effective_to >= CURDATE())
                ORDER BY s.effective_from DESC
                LIMIT 1
            ");
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log('[SalariedAgentService] getSalaryStructure: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all historical salary structures for an agent (for HR audit trail).
     *
     * @param int $userId
     * @return array
     */
    public function getSalaryHistory(int $userId): array
    {
        try {
            $tid    = (int)$this->tenantId();
            $tWhere = $tid > 1 ? 'AND tenant_id = ?' : '';
            $params = [$userId];
            if ($tid > 1) {
                $params[] = $tid;
            }

            $stmt = $this->pdo->prepare("
                SELECT s.*, u.name AS set_by_name
                FROM salaried_agent_structures s
                LEFT JOIN users u ON u.id = s.set_by_user_id
                WHERE s.user_id = ? {$tWhere}
                ORDER BY s.effective_from DESC
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[SalariedAgentService] getSalaryHistory: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create or update salary structure for a salaried agent.
     * Automatically closes the previous active structure (sets effective_to = effective_from - 1 day).
     *
     * @param int   $userId        The salaried agent's user_id
     * @param array $data {
     *   basic_salary, hra, ta_da, other_allowance,
     *   incentive_type ('percentage'|'flat_per_plot'),
     *   incentive_value,
     *   tds_applicable (0|1),
     *   effective_from (Y-m-d),
     *   remarks
     * }
     * @param int   $setByUserId   Admin/HR user who is setting this
     * @return array ['success'=>bool, 'id'=>int|null, 'error'=>string|null]
     */
    public function createSalaryStructure(int $userId, array $data, int $setByUserId): array
    {
        try {
            $tid = (int)$this->tenantId();
            $this->pdo->beginTransaction();

            $effectiveFrom = $data['effective_from'] ?? date('Y-m-d');

            // Close any currently open structure for this agent
            $closeSql = "
                UPDATE salaried_agent_structures
                SET effective_to = DATE_SUB(?, INTERVAL 1 DAY),
                    updated_at   = NOW()
                WHERE user_id      = ?
                  AND effective_to IS NULL
                  AND effective_from < ?
            ";
            $closeParams = [$effectiveFrom, $userId, $effectiveFrom];
            if ($tid > 1) {
                $closeSql .= ' AND tenant_id = ?';
                $closeParams[] = $tid;
            }
            $this->pdo->prepare($closeSql)->execute($closeParams);

            // Insert new structure
            $tenantCol = $tid > 1 ? ', tenant_id' : '';
            $tenantVal = $tid > 1 ? ', ?' : '';

            $stmt = $this->pdo->prepare("
                INSERT INTO salaried_agent_structures
                    (user_id, basic_salary, hra, ta_da, other_allowance,
                     incentive_type, incentive_value, tds_applicable,
                     effective_from, effective_to, set_by_user_id, remarks,
                     created_at, updated_at{$tenantCol})
                VALUES
                    (?, ?, ?, ?, ?,
                     ?, ?, ?,
                     ?, NULL, ?, ?,
                     NOW(), NOW(){$tenantVal})
            ");

            $params = [
                $userId,
                (float)($data['basic_salary']    ?? 0),
                (float)($data['hra']              ?? 0),
                (float)($data['ta_da']            ?? 0),
                (float)($data['other_allowance']  ?? 0),
                $data['incentive_type']  ?? 'flat_per_plot',
                (float)($data['incentive_value']  ?? 0),
                (int)($data['tds_applicable']     ?? 1),
                $effectiveFrom,
                $setByUserId,
                $data['remarks'] ?? null,
            ];
            if ($tid > 1) {
                $params[] = $tid;
            }

            $stmt->execute($params);
            $newId = (int)$this->pdo->lastInsertId();

            // Mark associate as salary-active
            $this->pdo->prepare("
                UPDATE associates SET agent_type = 'salaried'
                WHERE user_id = ?
            ")->execute([$userId]);

            $this->pdo->commit();

            return ['success' => true, 'id' => $newId, 'error' => null];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[SalariedAgentService] createSalaryStructure: ' . $e->getMessage());
            return ['success' => false, 'id' => null, 'error' => $e->getMessage()];
        }
    }

    /* ================================================================
       PAYROLL CALCULATION
       ================================================================ */

    /**
     * Calculate a salaried agent's monthly payroll.
     *
     * @param int $userId   The salaried agent's user_id
     * @param int $month    Month number (1-12)
     * @param int $year     Year (e.g. 2026)
     * @return array {
     *   success, user_id, month, year,
     *   basic_salary, hra, ta_da, other_allowance, gross_fixed,
     *   plots_sold, incentive_per_unit, incentive_type, total_incentive,
     *   gross_total, tds_deducted, net_payable
     * }
     */
    public function calculateMonthlyPayroll(int $userId, int $month, int $year): array
    {
        try {
            $structure = $this->getSalaryStructure($userId);
            if (!$structure) {
                return ['success' => false, 'error' => 'No active salary structure found for user ' . $userId];
            }

            $basic     = (float)$structure['basic_salary'];
            $hra       = (float)$structure['hra'];
            $tada      = (float)$structure['ta_da'];
            $other     = (float)$structure['other_allowance'];
            $grossFixed = $basic + $hra + $tada + $other;

            // Count plots sold this month
            $monthStr  = sprintf('%04d-%02d', $year, $month);
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) AS plots_sold, COALESCE(SUM(pb.total_amount), 0) AS total_sale_value
                FROM plot_bookings pb
                WHERE pb.associate_id = (
                    SELECT id FROM associates WHERE user_id = ? LIMIT 1
                )
                AND DATE_FORMAT(pb.booking_date, '%Y-%m') = ?
                AND pb.status IN ('confirmed','completed','registered')
            ");
            $stmt->execute([$userId, $monthStr]);
            $saleRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $plotsSold      = (int)($saleRow['plots_sold']      ?? 0);
            $totalSaleValue = (float)($saleRow['total_sale_value'] ?? 0);

            // Calculate incentive
            $incentiveType  = $structure['incentive_type'];
            $incentiveValue = (float)$structure['incentive_value'];

            if ($incentiveType === 'flat_per_plot') {
                $totalIncentive = $incentiveValue * $plotsSold;
            } else {
                // percentage of total sale value
                $totalIncentive = ($incentiveValue / 100.0) * $totalSaleValue;
            }
            $totalIncentive = round($totalIncentive, 2);

            $grossTotal = round($grossFixed + $totalIncentive, 2);

            // TDS deduction (Section 192B — salary TDS)
            // Simplified: 10% flat TDS on gross if tds_applicable (full year annualised bracket logic is complex)
            $tdsDeducted = 0.0;
            if ((int)$structure['tds_applicable'] === 1) {
                $annualised = $grossTotal * 12;
                if ($annualised > 500000) {
                    $tdsRate     = $annualised > 1000000 ? 20 : 10; // simplified 2-slab
                    $tdsDeducted = round(($grossTotal * $tdsRate) / 100, 2);
                }
            }

            $netPayable = round($grossTotal - $tdsDeducted, 2);

            return [
                'success'          => true,
                'user_id'          => $userId,
                'month'            => $month,
                'year'             => $year,
                'basic_salary'     => $basic,
                'hra'              => $hra,
                'ta_da'            => $tada,
                'other_allowance'  => $other,
                'gross_fixed'      => round($grossFixed, 2),
                'plots_sold'       => $plotsSold,
                'total_sale_value' => $totalSaleValue,
                'incentive_type'   => $incentiveType,
                'incentive_value'  => $incentiveValue,
                'total_incentive'  => $totalIncentive,
                'gross_total'      => $grossTotal,
                'tds_deducted'     => $tdsDeducted,
                'net_payable'      => $netPayable,
                'structure_id'     => (int)$structure['id'],
            ];

        } catch (Exception $e) {
            error_log('[SalariedAgentService] calculateMonthlyPayroll: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Calculate sale incentive for a single plot booking (on booking confirm event).
     *
     * @param int $userId      Salaried agent user_id
     * @param int $bookingId   plot_bookings.id
     * @return array ['success'=>bool, 'incentive_amount'=>float, 'incentive_type'=>string]
     */
    public function calculateSaleIncentive(int $userId, int $bookingId): array
    {
        try {
            $structure = $this->getSalaryStructure($userId);
            if (!$structure) {
                return ['success' => false, 'incentive_amount' => 0.0, 'incentive_type' => 'none'];
            }

            $stmt = $this->pdo->prepare("
                SELECT total_amount FROM plot_bookings WHERE id = ? LIMIT 1
            ");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$booking) {
                return ['success' => false, 'incentive_amount' => 0.0, 'incentive_type' => 'none', 'error' => 'Booking not found'];
            }

            $saleValue      = (float)$booking['total_amount'];
            $incentiveType  = $structure['incentive_type'];
            $incentiveValue = (float)$structure['incentive_value'];

            if ($incentiveType === 'flat_per_plot') {
                $amount = $incentiveValue;
            } else {
                $amount = round(($incentiveValue / 100.0) * $saleValue, 2);
            }

            return [
                'success'          => true,
                'incentive_amount' => round($amount, 2),
                'incentive_type'   => $incentiveType,
                'incentive_value'  => $incentiveValue,
                'sale_value'       => $saleValue,
            ];

        } catch (Exception $e) {
            error_log('[SalariedAgentService] calculateSaleIncentive: ' . $e->getMessage());
            return ['success' => false, 'incentive_amount' => 0.0, 'incentive_type' => 'none', 'error' => $e->getMessage()];
        }
    }

    /* ================================================================
       ALL SALARIED AGENTS LIST (for HR panel)
       ================================================================ */

    /**
     * Get list of all salaried agents with their current salary structure summary.
     *
     * @return array
     */
    public function getAllSalariedAgents(): array
    {
        try {
            $tid    = (int)$this->tenantId();
            $tWhere = $tid > 1 ? 'AND a.tenant_id = ?' : '';
            $params = [];
            if ($tid > 1) {
                $params[] = $tid;
            }

            $stmt = $this->pdo->prepare("
                SELECT
                    a.id AS associate_id,
                    a.user_id,
                    u.name,
                    u.email,
                    u.phone,
                    a.agent_type,
                    a.is_salary_active,
                    s.basic_salary,
                    s.hra,
                    s.ta_da,
                    s.other_allowance,
                    s.incentive_type,
                    s.incentive_value,
                    s.effective_from
                FROM associates a
                JOIN users u ON u.id = a.user_id
                LEFT JOIN salaried_agent_structures s
                    ON s.user_id = a.user_id
                    AND s.effective_from <= CURDATE()
                    AND (s.effective_to IS NULL OR s.effective_to >= CURDATE())
                WHERE a.agent_type = 'salaried'
                  {$tWhere}
                ORDER BY u.name ASC
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[SalariedAgentService] getAllSalariedAgents: ' . $e->getMessage());
            return [];
        }
    }
}
