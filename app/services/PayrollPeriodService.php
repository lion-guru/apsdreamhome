<?php
/**
 * PayrollPeriodService
 * Manages payroll period lifecycle: open -> locked -> closed
 * Prevents modifications to locked/closed periods
 */

namespace App\Services;

use App\Traits\ServiceTenantTrait;

class PayrollPeriodService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    /**
     * Get period status
     */
    public function getPeriod(int $month, int $year): ?array
    {
        $sql = "SELECT * FROM payroll_periods WHERE tenant_id=? AND period_month=? AND period_year=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->tenantId(), $month, $year]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Check if period allows modifications
     */
    public function canModify(int $month, int $year): bool
    {
        $period = $this->getPeriod($month, $year);
        if (!$period) return true; // Period doesn't exist yet = open
        return $period['status'] === 'open';
    }

    /**
     * Lock a period (prevent new entries, allow corrections)
     */
    public function lockPeriod(int $month, int $year, int $lockedBy): bool
    {
        $period = $this->getPeriod($month, $year);
        if (!$period) {
            $this->createPeriod($month, $year);
            $period = $this->getPeriod($month, $year);
        }
        
        if ($period['status'] === 'closed') {
            throw new \Exception("Cannot lock a closed period");
        }

        $sql = "UPDATE payroll_periods SET status='locked', locked_by=?, locked_at=NOW() WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$lockedBy, $period['id']]);
    }

    /**
     * Close a period (fully finalized, no changes allowed)
     */
    public function closePeriod(int $month, int $year, int $closedBy): bool
    {
        $period = $this->getPeriod($month, $year);
        if (!$period) {
            throw new \Exception("Period $month/$year does not exist");
        }

        if ($period['status'] === 'open') {
            throw new \Exception("Cannot close an open period directly. Lock it first.");
        }

        $sql = "UPDATE payroll_periods SET status='closed', closed_by=?, closed_at=NOW() WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$closedBy, $period['id']]);
    }

    /**
     * Reopen a locked period (admin only)
     */
    public function reopenPeriod(int $month, int $year): bool
    {
        $period = $this->getPeriod($month, $year);
        if (!$period) return false;
        
        if ($period['status'] === 'closed') {
            throw new \Exception("Cannot reopen a closed period. Contact system administrator.");
        }

        $sql = "UPDATE payroll_periods SET status='open', locked_by=NULL, locked_at=NULL WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$period['id']]);
    }

    /**
     * Create period if not exists
     */
    public function createPeriod(int $month, int $year): bool
    {
        $sql = "INSERT IGNORE INTO payroll_periods (tenant_id, period_month, period_year, status) VALUES (?,?,?,'open')";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$this->tenantId(), $month, $year]);
    }

    /**
     * Get all periods for tenant
     */
    public function getAllPeriods(): array
    {
        $sql = "SELECT * FROM payroll_periods WHERE tenant_id=? ORDER BY period_year DESC, period_month DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->tenantId()]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get current open period
     */
    public function getCurrentPeriod(): ?array
    {
        $sql = "SELECT * FROM payroll_periods WHERE tenant_id=? AND status='open' ORDER BY period_year DESC, period_month DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->tenantId()]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Auto-lock periods older than N months (run via cron)
     */
    public function autoLockOldPeriods(int $monthsOld = 2): int
    {
        $cutoffMonth = (int)date('n');
        $cutoffYear = (int)date('Y');
        
        // Subtract months
        for ($i = 0; $i < $monthsOld; $i++) {
            $cutoffMonth--;
            if ($cutoffMonth === 0) {
                $cutoffMonth = 12;
                $cutoffYear--;
            }
        }

        $sql = "UPDATE payroll_periods 
                SET status='locked', locked_by=0, locked_at=NOW() 
                WHERE tenant_id=? AND status='open' 
                AND (period_year < ? OR (period_year = ? AND period_month <= ?))";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->tenantId(), $cutoffYear, $cutoffYear, $cutoffMonth]);
        return $stmt->rowCount();
    }

    /**
     * Auto-close locked periods older than N months (run via cron)
     */
    public function autoCloseOldPeriods(int $monthsOld = 6): int
    {
        $cutoffMonth = (int)date('n');
        $cutoffYear = (int)date('Y');
        
        for ($i = 0; $i < $monthsOld; $i++) {
            $cutoffMonth--;
            if ($cutoffMonth === 0) {
                $cutoffMonth = 12;
                $cutoffYear--;
            }
        }

        $sql = "UPDATE payroll_periods 
                SET status='closed', closed_by=0, closed_at=NOW() 
                WHERE tenant_id=? AND status='locked' 
                AND (period_year < ? OR (period_year = ? AND period_month <= ?))";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->tenantId(), $cutoffYear, $cutoffYear, $cutoffMonth]);
        return $stmt->rowCount();
    }
}