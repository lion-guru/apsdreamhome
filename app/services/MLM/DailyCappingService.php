<?php
namespace App\Services\MLM;

use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

class DailyCappingService
{
    use ServiceTenantTrait;
    private $db;
    
    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
    }
    
    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Apply daily capping rule for level rewards
     * Checks SUM(amount) of 'level' transactions today
     * If cap exceeded, partial credit + flush_out the excess
     */
    public function applyDailyCap(int $userId, float $incomingReward, float $dailyCap): float
    {
        if ($dailyCap <= 0) return $incomingReward; // No cap = full credit
        if ($incomingReward <= 0) return 0;
        
        // Get today's accumulated level income
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total_today FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type IN ('level_bonus', 'level') AND DATE(created_at) = CURDATE() AND status = 'approved'" . $this->tenantSql());
        $stmt->execute([$userId]);
        $todayTotal = (float)$stmt->fetch(\PDO::FETCH_ASSOC)['total_today'];
        
        $available = $dailyCap - $todayTotal;
        
        if ($available <= 0) {
            // Full flush_out
            $this->logFlushOut($userId, $incomingReward, 'Daily cap exceeded');
            return 0;
        }
        
        if ($incomingReward > $available) {
            // Partial credit, rest flushed
            $excess = $incomingReward - $available;
            $this->logFlushOut($userId, $excess, 'Partial cap limit exceeded');
            return $available;
        }
        
        return $incomingReward; // Full credit within cap
    }
    
    private function logFlushOut(int $userId, float $amount, string $reason): void
    {
        try {
            $insertData = $this->tenantInsertData();
            $cols = "user_id, amount, retention_reason, notes, created_at";
            $vals = "?, ?, 'daily_cap_flush', ?, NOW()";
            $params = [$userId, $amount, $reason];
            if (!empty($insertData)) {
                $cols .= ", " . implode(', ', array_keys($insertData));
                $vals .= ", " . implode(', ', array_fill(0, count($insertData), '?'));
                $params = array_merge($params, array_values($insertData));
            }
            $stmt = $this->db->prepare("INSERT INTO retained_earnings ({$cols}) VALUES ({$vals})");
            $stmt->execute($params);
            
            error_log("DailyCapping: Flushed ₹$amount from user #$userId. Reason: $reason");
        } catch (\Exception $e) {
            error_log("DailyCapping: Failed to log flush_out: " . $e->getMessage());
        }
    }
    
    /**
     * Get daily cap status for a user
     */
    public function getCapStatus(int $userId): array
    {
        $tid = $this->getTenantId();
        $tenantSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $stmt = $this->db->prepare("SELECT u.current_package_id, p.daily_capping, p.name as package_name FROM users u LEFT JOIN packages p ON p.id = u.current_package_id WHERE u.id = ?{$tenantSql}");
        $stmt->execute($tid > 1 ? [$userId, $tid] : [$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $dailyCap = $user ? (float)$user['daily_capping'] : 0;
        
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as used_today FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type IN ('level_bonus', 'level') AND DATE(created_at) = CURDATE() AND status = 'approved'" . $this->tenantSql());
        $stmt->execute([$userId]);
        $usedToday = (float)$stmt->fetch(\PDO::FETCH_ASSOC)['used_today'];
        
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as flushed_today FROM retained_earnings WHERE user_id = ? AND retention_reason = 'daily_cap_flush' AND DATE(created_at) = CURDATE()" . $this->tenantSql());
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt->execute([$userId]);
        $flushedToday = (float)$stmt->fetch(\PDO::FETCH_ASSOC)['flushed_today'];
        
        return [
            'package' => $user['package_name'] ?? 'None',
            'daily_cap' => $dailyCap,
            'used_today' => $usedToday,
            'available' => max(0, $dailyCap - $usedToday),
            'flushed_today' => $flushedToday,
        ];
    }
}
