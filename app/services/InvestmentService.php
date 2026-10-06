<?php
namespace App\Services;

use PDO;
use App\Traits\ServiceTenantTrait;

class InvestmentService
{
    use ServiceTenantTrait;
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo) {
            $this->pdo = $pdo;
            return;
        }
        $this->pdo = $this->resolvePdo();
    }

    public function listPlans(?string $category = null, bool $featuredOnly = false): array
    {
        $sql = "SELECT * FROM investment_plans WHERE is_active = 1";
        $params = [];
        if ($category) {
            $sql .= " AND plan_category = ?";
            $params[] = $category;
        }
        if ($featuredOnly) {
            $sql .= " AND is_featured = 1";
        }
        $sql .= " ORDER BY display_order, plan_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['features'] = $r['features'] ? json_decode($r['features'], true) : [];
        }
        return $rows;
    }

    public function getUserInvestments(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT i.*, pl.plan_name, pl.plan_category, pl.expected_return_pct FROM investments i JOIN investment_plans pl ON i.plan_id = pl.id WHERE i.user_id = ? {$this->tenantSqlForAlias('i')} ORDER BY i.created_at DESC");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['returns'] = (float)$r['current_value'] - (float)$r['principal_amount'];
            $r['return_pct'] = $r['principal_amount'] > 0 ? round((($r['current_value'] - $r['principal_amount']) / $r['principal_amount']) * 100, 2) : 0;
        }
        return $rows;
    }

    public function getStats(int $userId): array
    {
        $row = $this->pdo->prepare("SELECT COALESCE(SUM(principal_amount), 0) as total_invested, COALESCE(SUM(current_value), 0) as total_value, COALESCE(SUM(current_value - principal_amount), 0) as total_returns FROM investments WHERE user_id = ? AND status = 'active' {$this->tenantSql()}");
        $row->execute([$userId]);
        $stats = $row->fetch(PDO::FETCH_ASSOC);

        $count = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM investments WHERE user_id = ? AND status = 'active' {$this->tenantSql()}");
        $count->execute([$userId]);
        $active = (int)$count->fetch(PDO::FETCH_ASSOC)['cnt'];

        $total = (float)$stats['total_value'];
        $pct = $stats['total_invested'] > 0 ? round(((($stats['total_value'] - $stats['total_invested']) / $stats['total_invested']) * 100), 2) : 0;
        $level = $this->computeLevel((float)$stats['total_invested']);

        return [
            'total_invested' => (float)$stats['total_invested'],
            'total_value' => (float)$stats['total_value'],
            'total_returns' => (float)$stats['total_returns'],
            'avg_return_pct' => (float)$pct,
            'active_count' => $active,
            'level' => $level['name'],
            'level_progress_pct' => $level['progress_pct'],
            'next_level' => $level['next'],
            'next_threshold' => $level['next_threshold'],
        ];
    }

    public function invest(int $userId, int $planId, array $data): array
    {
        $plan = $this->pdo->prepare("SELECT * FROM investment_plans WHERE id = ? AND is_active = 1");
        $plan->execute([$planId]);
        $planRow = $plan->fetch(PDO::FETCH_ASSOC);
        if (!$planRow) return ['success' => false, 'error' => 'Plan not found or inactive'];

        $amount = (float)($data['amount'] ?? $data['principal_amount'] ?? 0);
        if ($amount < (float)$planRow['min_amount']) {
            return ['success' => false, 'error' => 'Minimum amount is ₹' . number_format((float)$planRow['min_amount'])];
        }
        if ($planRow['max_amount'] !== null && $amount > (float)$planRow['max_amount']) {
            return ['success' => false, 'error' => 'Maximum amount is ₹' . number_format((float)$planRow['max_amount'])];
        }

        $ref = 'APS-INV-' . date('Ymd') . '-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $start = date('Y-m-d');
        $maturity = $planRow['tenure_months'] ? date('Y-m-d', strtotime($start . ' +' . $planRow['tenure_months'] . ' months')) : null;
        $monthly = $data['monthly_amount'] ?? null;
        $sipDate = $data['sip_date'] ?? null;
        $autoInvest = $monthly ? 1 : 0;

        $promisedSqft = (int)($planRow['plot_promised_sqft'] ?? 0);
        $promisedValue = (float)($planRow['plot_promised_value'] ?? 0);
        $companyContrib = $promisedValue > 0 ? max(0.0, $promisedValue - $amount) : 0.0;

        $tid = $this->tenantId();
        $cols = "user_id, plan_id, investment_ref, principal_amount, current_value, monthly_amount, sip_date, start_date, maturity_date, status, auto_invest, company_contribution, plot_promised_sqft, plot_promised_value, maturity_status";
        $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, 'pending'";
        $params = [$userId, $planId, $ref, $amount, $amount, $monthly, $sipDate, $start, $maturity, $autoInvest, $companyContrib, $promisedSqft, $promisedValue];
        if ($tid > 1) {
            $cols .= ", tenant_id";
            $vals .= ", ?";
            $params[] = $tid;
        }
        $stmt = $this->pdo->prepare("INSERT INTO investments ($cols) VALUES ($vals)");
        $stmt->execute($params);
        $investmentId = (int) $this->pdo->lastInsertId();

        // Wire up commission: if a referrer is specified, pay 3% (2% agent + 0.7% L1 + 0.3% L2)
        $referrerUserId = (int)($data['referrer_user_id'] ?? 0);
        $commissionResult = null;
        if ($referrerUserId > 0) {
            try {
                $engine = new HybridCommissionEngine($this->pdo);
                $commissionResult = $engine->investmentSale($investmentId, $userId, $amount, $referrerUserId);
            } catch (\Throwable $e) {
                error_log("[InvestmentService] commission wiring failed for investment #{$investmentId}: " . $e->getMessage());
                // Non-fatal — investment is recorded, commission failure logged
            }
        }

        $this->updateInvestorLevel($userId);
        return [
            'success'        => true,
            'investment_id'  => $investmentId,
            'investment_ref' => $ref,
            'commission'     => $commissionResult,
        ];
    }

    /**
     * Cancel an active investment with a 365-day lock-in period.
     *
     * If cancelled before 365 days: 10% service charge deducted from principal.
     * If cancelled after 365 days: no charge (full refund).
     *
     * Commission reversal is triggered automatically if a referrer was paid.
     *
     * @param int   $userId   users.id
     * @param int   $investmentId  investments.id
     * @param string $reason   Cancellation reason
     * @return array{success: bool, refund_amount: float, service_charge: float, error?: string}
     */
    public function cancelInvestment(int $userId, int $investmentId, string $reason = ''): array
    {
        // Fetch investment
        $stmt = $this->pdo->prepare("SELECT * FROM investments WHERE id = ? AND user_id = ? {$this->tenantSql()}");
        $stmt->execute([$investmentId, $userId]);
        $inv = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$inv) {
            return ['success' => false, 'refund_amount' => 0, 'service_charge' => 0, 'error' => 'Investment not found'];
        }
        if ($inv['status'] !== 'active') {
            return ['success' => false, 'refund_amount' => 0, 'service_charge' => 0, 'error' => 'Investment is not active (status: ' . $inv['status'] . ')'];
        }

        // Calculate lock-in: 365 days from start_date
        $startDate  = new \DateTime($inv['start_date']);
        $now        = new \DateTime();
        $daysHeld   = (int) $startDate->diff($now)->days;
        $lockInDays = 365;

        $principal = (float) $inv['principal_amount'];
        if ($daysHeld < $lockInDays) {
            // Early cancellation: 10% service charge
            $serviceCharge = round($principal * 0.10, 2);
            $refundAmount  = round($principal - $serviceCharge, 2);
        } else {
            // After lock-in: full refund
            $serviceCharge = 0.0;
            $refundAmount  = $principal;
        }

        try {
            $this->pdo->beginTransaction();

            // Update investment status
            $this->pdo->prepare("
                UPDATE investments
                SET status = 'cancelled', updated_at = NOW(), notes = CONCAT(COALESCE(notes,''), '\nCancelled: ', ?)
                WHERE id = ? {$this->tenantSql()}
            ")->execute([$reason ?: 'User requested cancellation', $investmentId]);

            // Reverse any commissions paid for this investment
            $commissionReversal = ['reversed' => 0, 'total_reversed' => 0];
            try {
                $engine = new \App\Services\HybridCommissionEngine($this->pdo);
                $commissionReversal = $engine->reverseInvestmentCommissions(
                    $investmentId,
                    $reason ?: 'Investment cancelled — commission reversed'
                );
            } catch (\Throwable $e) {
                error_log("[InvestmentService] commission reversal failed (non-blocking): " . $e->getMessage());
            }

            $this->pdo->commit();

            return [
                'success'            => true,
                'refund_amount'      => $refundAmount,
                'service_charge'     => $serviceCharge,
                'days_held'          => $daysHeld,
                'principal'          => $principal,
                'commissions_reversed' => $commissionReversal['reversed'] ?? 0,
                'total_reversed'     => $commissionReversal['total_reversed'] ?? 0,
            ];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[InvestmentService] cancelInvestment FAILED: " . $e->getMessage());
            return ['success' => false, 'refund_amount' => 0, 'service_charge' => 0, 'error' => $e->getMessage()];
        }
    }

    public function updateInvestorLevel(int $userId): void
    {
        $stats = $this->getStats($userId);
        $xp = (int)floor($stats['total_invested'] / 100);
        $tid = $this->tenantId();
        $cols = "user_id, level_name, total_invested, total_returns, xp_points, next_level_threshold";
        $vals = "?, ?, ?, ?, ?, ?";
        $params = [$userId, $stats['level'], $stats['total_invested'], $stats['total_returns'], $xp, $stats['next_threshold']];
        if ($tid > 1) {
            $cols .= ", tenant_id";
            $vals .= ", ?";
            $params[] = $tid;
        }
        $this->pdo->prepare("INSERT INTO investor_levels ($cols) VALUES ($vals) ON DUPLICATE KEY UPDATE level_name=VALUES(level_name), total_invested=VALUES(total_invested), total_returns=VALUES(total_returns), xp_points=VALUES(xp_points), next_level_threshold=VALUES(next_level_threshold), last_updated=NOW()")
            ->execute($params);
    }

    private function computeLevel(float $total): array
    {
        $levels = [
            ['name' => 'Bronze',   'min' => 0,        'next' => 'Silver',   'threshold' => 50000],
            ['name' => 'Silver',   'min' => 50000,    'next' => 'Gold',     'threshold' => 200000],
            ['name' => 'Gold',     'min' => 200000,   'next' => 'Platinum', 'threshold' => 500000],
            ['name' => 'Platinum', 'min' => 500000,   'next' => 'Diamond',  'threshold' => 1000000],
            ['name' => 'Diamond',  'min' => 1000000,  'next' => null,      'threshold' => 1000000],
        ];
        $current = $levels[0];
        foreach ($levels as $l) {
            if ($total >= $l['min']) $current = $l;
        }
        if ($current['next'] === null) {
            return ['name' => $current['name'], 'progress_pct' => 100, 'next' => null, 'next_threshold' => $current['threshold']];
        }
        $next = null;
        foreach ($levels as $l) {
            if ($l['name'] === $current['next']) { $next = $l; break; }
        }
        $nextThreshold = $next['min'];
        $prevMin = $current['min'];
        $range = $nextThreshold - $prevMin;
        $progress = $range > 0 ? round((($total - $prevMin) / $range) * 100) : 100;
        $progress = max(0, min(100, $progress));
        return ['name' => $current['name'], 'progress_pct' => $progress, 'next' => $current['next'], 'next_threshold' => $nextThreshold];
    }

    /**
     * Create installment records for SIP investments
     * Run once after investment creation or on demand
     */
    public function createInstallmentsForInvestment(int $investmentId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM investments WHERE id = ? {$this->tenantSql()} LIMIT 1");
        $stmt->execute([$investmentId]);
        $inv = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$inv) return ['success' => false, 'error' => 'Investment not found'];

        if ((int)$inv['auto_invest'] !== 1 || !$inv['monthly_amount'] || !$inv['sip_date']) {
            return ['success' => false, 'error' => 'Investment is not a SIP or missing SIP details'];
        }

        $monthlyAmount = (float)$inv['monthly_amount'];
        $sipDate = (int)$inv['sip_date'];
        $startDate = new \DateTime($inv['start_date']);
        $maturityDate = $inv['maturity_date'] ? new \DateTime($inv['maturity_date']) : null;

        // Calculate total installments
        $totalMonths = 0;
        if ($maturityDate) {
            $diff = $startDate->diff($maturityDate);
            $totalMonths = $diff->y * 12 + $diff->m;
            if ($diff->d >= $sipDate) $totalMonths++;
        } else {
            // Default to plan tenure if available
            $planStmt = $this->pdo->prepare("SELECT tenure_months FROM investment_plans WHERE id = ? LIMIT 1");
            $planStmt->execute([$inv['plan_id']]);
            $plan = $planStmt->fetch(PDO::FETCH_ASSOC);
            $totalMonths = (int)($plan['tenure_months'] ?? 12);
        }

        $this->pdo->beginTransaction();
        try {
            for ($i = 1; $i <= $totalMonths; $i++) {
                $dueDate = clone $startDate;
                $dueDate->setDate($dueDate->format('Y'), $dueDate->format('m'), min($sipDate, cal_days_in_month(CAL_GREGORIAN, $dueDate->format('m'), $dueDate->format('Y'))));
                $dueDate->modify("+{$i} months");
                
                // Skip if due date is in the past
                if ($dueDate < new \DateTime('today')) continue;

                $stmt = $this->pdo->prepare("
                    INSERT INTO investment_installments 
                    (investment_id, installment_number, amount, due_date, status, tenant_id)
                    VALUES (?, ?, ?, ?, 'pending', ?)
                    ON DUPLICATE KEY UPDATE amount = VALUES(amount), due_date = VALUES(due_date)
                ");
                $stmt->execute([$inv['id'], $i, $monthlyAmount, $dueDate->format('Y-m-d'), $this->tenantId()]);
            }
            $this->pdo->commit();
            return ['success' => true, 'created' => $totalMonths];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get due installments for processing
     */
    public function getDueInstallments(\DateTime $date = null): array
    {
        $date = $date ?? new \DateTime('today');
        $dateStr = $date->format('Y-m-d');

        $stmt = $this->pdo->prepare("
            SELECT ii.*, i.user_id, i.monthly_amount, i.plan_id, u.name as user_name, u.email, u.phone
            FROM investment_installments ii
            JOIN investments i ON ii.investment_id = i.id
            JOIN users u ON i.user_id = u.id
            WHERE ii.status = 'pending' 
            AND ii.due_date <= ?
            AND i.status = 'active'
            AND i.auto_invest = 1
            {$this->tenantSqlForAlias('ii')}
            ORDER BY ii.due_date ASC
        ");
        $stmt->execute([$dateStr]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Process a single installment payment
     */
    public function processInstallment(int $installmentId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ii.*, i.user_id, i.monthly_amount, i.auto_invest
            FROM investment_installments ii
            JOIN investments i ON ii.investment_id = i.id
            WHERE ii.id = ? {$this->tenantSqlForAlias('ii')} LIMIT 1
        ");
        $stmt->execute([$installmentId]);
        $inst = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$inst) return ['success' => false, 'error' => 'Installment not found'];

        if ($inst['status'] !== 'pending') {
            return ['success' => false, 'error' => 'Installment already processed'];
        }

        $userId = (int)$inst['user_id'];
        $amount = (float)$inst['amount'];
        $tid = $this->tenantId();

        try {
            $this->pdo->beginTransaction();

            // Check wallet balance
            $wallet = $this->pdo->prepare("SELECT points_balance FROM wallet_points WHERE user_id = ? LIMIT 1");
            $wallet->execute([$userId]);
            $walletRow = $wallet->fetch(PDO::FETCH_ASSOC);
            if (!$walletRow || (float)$walletRow['points_balance'] < $amount) {
                $this->pdo->rollBack();
                $this->markInstallmentFailed($installmentId, 'Insufficient wallet balance');
                return ['success' => false, 'error' => 'Insufficient wallet balance'];
            }

            // Deduct from wallet
            $newBalance = (float)$walletRow['points_balance'] - $amount;
            $this->pdo->query(
                "UPDATE wallet_points SET points_balance = ?, total_used = total_used + ? WHERE user_id = ?",
                [$newBalance, $amount, $userId]
            );

            // Record wallet transaction
            $tid = $this->tenantId();
            $this->pdo->insert('wallet_transactions', array_merge([
                'user_id' => $inst['user_id'],
                'transaction_type' => 'debit',
                'transaction_category' => 'investment',
                'amount' => $amount,
                'balance_before' => (float)$walletRow['points_balance'],
                'balance_after' => $newBalance,
                'description' => "SIP installment for investment #{$inst['investment_id']} (installment #{$inst['installment_number']})",
                'reference_id' => $installmentId,
                'reference_type' => 'investment_installment',
            ], $this->tenantInsertData()));

            // Update investment current_value
            $this->pdo->query(
                "UPDATE investments SET current_value = current_value + ?, updated_at = NOW() WHERE id = ?",
                [$amount, $inst['investment_id']]
            );

            // Mark installment as paid
            $this->markInstallmentPaid($installmentId);

            $this->pdo->commit();

            return ['success' => true, 'new_balance' => $newBalance];

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            $this->markInstallmentFailed($installmentId, $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function markInstallmentPaid(int $installmentId): void
    {
        $this->pdo->query(
            "UPDATE investment_installments SET status = 'paid', paid_at = NOW(), updated_at = NOW() WHERE id = ?",
            [(int)$installmentId]
        );
    }

    private function markInstallmentFailed(int $installmentId, string $reason): void
    {
        $this->pdo->query(
            "UPDATE investment_installments SET status = 'failed', failure_reason = ?, retry_count = retry_count + 1, updated_at = NOW() WHERE id = ?",
            [$reason, (int)$installmentId]
        );
    }

    /**
     * Process all due SIP installments (called by cron)
     */
    public function processDueSipInstallments(): array
    {
        $due = $this->getDueInstallments();
        $results = ['processed' => 0, 'paid' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($due as $inst) {
            $result = $this->processInstallment((int)$inst['id']);
            $results['processed']++;
            if ($result['success']) {
                $results['paid']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Installment #{$inst['id']}: " . ($result['error'] ?? 'Unknown error');
            }
        }

        return $results;
    }

    private function resolvePdo(): PDO
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
    }
}
