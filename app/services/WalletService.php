<?php
namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

class WalletService
{
    use \App\Traits\ServiceTenantTrait;

    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function ensureWallet(int $userId): bool
    {
        try {
            $existing = $this->db->fetchOne("SELECT id FROM wallet_points WHERE user_id = ? LIMIT 1" . $this->tenantSql(), array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            if ($existing) return true;
            $insertData = $this->tenantInsertData();
            $this->db->insert('wallet_points', array_merge([
                'user_id' => $userId,
                'points_balance' => 0.00,
                'total_earned' => 0.00,
                'total_used' => 0.00,
                'total_transferred_to_emi' => 0.00,
                'referral_earnings' => 0.00,
                'commission_earnings' => 0.00,
                'bonus_earnings' => 0.00,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], $insertData));
            return true;
        } catch (\Throwable $e) {
            error_log("WalletService::ensureWallet error: " . $e->getMessage());
            return false;
        }
    }

    public function getBalance(int $userId): float
    {
        try {
            $w = $this->db->fetchOne("SELECT points_balance FROM wallet_points WHERE user_id = ?" . $this->tenantSql(), array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            return $w ? (float)$w['points_balance'] : 0.0;
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private const VALID_CATEGORIES = ['referral', 'commission', 'bonus', 'emi_transfer', 'withdrawal', 'adjustment'];

    public function credit(int $userId, float $amount, string $category, string $description, ?int $referenceId = null, string $referenceType = 'user'): bool
    {
        // Fail closed BEFORE any DB write: non-positive amounts and unknown
        // categories must never move money (ledger ENUM would throw after the
        // balance UPDATE, leaving money without a ledger row).
        if ($amount <= 0) {
            error_log("WalletService::credit rejected: non-positive amount {$amount} for user {$userId}");
            return false;
        }
        if (!in_array($category, self::VALID_CATEGORIES, true)) {
            error_log("WalletService::credit rejected: invalid category '{$category}' for user {$userId}");
            return false;
        }
        try {
            $this->ensureWallet($userId);
            $this->db->beginTransaction();
            try {
                // S455: Lock row with FOR UPDATE inside transaction to prevent race conditions & double-credit overwrite
                $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1 FOR UPDATE" . $this->tenantSql(), array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
                if (!$wallet) {
                    $this->db->rollBack();
                    return false;
                }
                $balanceBefore = (float)$wallet['points_balance'];
                $newBalance = $balanceBefore + $amount;
                $newTotalEarned = (float)$wallet['total_earned'] + $amount;
                $field = $category === 'referral' ? 'referral_earnings' : ($category === 'commission' ? 'commission_earnings' : 'bonus_earnings');

                $this->db->query(
                    "UPDATE wallet_points SET points_balance = ?, total_earned = ?, {$field} = {$field} + ?, updated_at = NOW() WHERE user_id = ?" . $this->tenantSql(),
                    array_merge([$newBalance, $newTotalEarned, $amount, $userId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
                );
                $txn = array_merge([
                    'user_id' => $userId,
                    'transaction_type' => 'credit',
                    'transaction_category' => $category,
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $newBalance,
                    'description' => $description,
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s')
                ], $this->tenantInsertData());
                if ($referenceId) {
                    $txn['reference_id'] = $referenceId;
                    $txn['reference_type'] = $referenceType;
                    if ($referenceType === 'user') {
                        $txn['related_user_id'] = $referenceId;
                    }
                }
                $this->db->insert('wallet_transactions', $txn);
                $this->db->commit();
                return true;
            } catch (\Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                throw $e;
            }
        } catch (\Throwable $e) {
            error_log("WalletService::credit error: " . $e->getMessage());
            return false;
        }
    }

    public function debit(int $userId, float $amount, string $description, string $category = 'withdrawal'): bool
    {
        // Fail closed: negative/zero amounts would ADD money (balance check
        // passes since balance < negative is false); unknown categories would
        // throw on the ledger ENUM after the balance UPDATE.
        if ($amount <= 0) {
            error_log("WalletService::debit rejected: non-positive amount {$amount} for user {$userId}");
            return false;
        }
        if (!in_array($category, self::VALID_CATEGORIES, true)) {
            error_log("WalletService::debit rejected: invalid category '{$category}' for user {$userId}");
            return false;
        }
        try {
            $this->db->beginTransaction();
            try {
                // S455: Lock row with FOR UPDATE inside transaction to prevent concurrent overdraft / race conditions
                $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1 FOR UPDATE" . $this->tenantSql(), array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
                if (!$wallet || (float)$wallet['points_balance'] < $amount) {
                    $this->db->rollBack();
                    return false;
                }
                $balanceBefore = (float)$wallet['points_balance'];
                $newBalance = $balanceBefore - $amount;
                $newTotalUsed = (float)$wallet['total_used'] + $amount;

                // Atomic conditional debit prevents negative balance under all circumstances
                $this->db->query(
                    "UPDATE wallet_points SET points_balance = ?, total_used = ?, updated_at = NOW() WHERE user_id = ? AND points_balance >= ?" . $this->tenantSql(),
                    array_merge([$newBalance, $newTotalUsed, $userId, $amount], $this->tenantId() > 1 ? [$this->tenantId()] : [])
                );
                $this->db->insert('wallet_transactions', array_merge([
                    'user_id' => $userId,
                    'transaction_type' => 'debit',
                    'transaction_category' => $category,
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $newBalance,
                    'description' => $description,
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s')
                ], $this->tenantInsertData()));
                $this->db->commit();
                return true;
            } catch (\Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                throw $e;
            }
        } catch (\Throwable $e) {
            error_log("WalletService::debit error: " . $e->getMessage());
            return false;
        }
    }

    public function getTransactions(int $userId, int $limit = 20): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM wallet_transactions WHERE user_id = ?" . $this->tenantSql() . " ORDER BY created_at DESC LIMIT ?",
                array_merge([$userId, $limit], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function transferToEmi(int $userId, float $amount, int $bookingId, string $description = ''): bool
    {
        // Fail closed: reject non-positive amounts to prevent balance inflation
        if ($amount <= 0) {
            error_log("WalletService::transferToEmi rejected: non-positive amount {$amount} for user {$userId}");
            return false;
        }
        try {
            // S264 + S455: atomic + FOR UPDATE + conditional balance check
            $this->db->beginTransaction();
            try {
                $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1 FOR UPDATE" . $this->tenantSql(), array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
                if (!$wallet || (float)$wallet['points_balance'] < $amount) {
                    $this->db->rollBack();
                    return false;
                }
                $newBalance = (float)$wallet['points_balance'] - $amount;
                $newEmiTransfer = (float)$wallet['total_transferred_to_emi'] + $amount;
                $this->db->query(
                    "UPDATE wallet_points SET points_balance = ?, total_transferred_to_emi = ?, updated_at = NOW() WHERE user_id = ? AND points_balance >= ?" . $this->tenantSql(),
                    array_merge([$newBalance, $newEmiTransfer, $userId, $amount], $this->tenantId() > 1 ? [$this->tenantId()] : [])
                );
                $this->db->insert('wallet_transactions', array_merge([
                    'user_id' => $userId,
                    'transaction_type' => 'debit',
                    'transaction_category' => 'emi_transfer',
                    'amount' => $amount,
                    'balance_before' => $wallet['points_balance'],
                    'balance_after' => $newBalance,
                    'description' => $description ?: "Transferred to EMI for booking #{$bookingId}",
                    'reference_id' => $bookingId,
                    'reference_type' => 'booking',
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s')
                ], $this->tenantInsertData()));
                $this->db->commit();
                return true;
            } catch (\Throwable $e) {
                try { $this->db->rollBack(); } catch (\Throwable $ignored) {}
                throw $e;
            }
        } catch (\Throwable $e) {
            error_log("WalletService::transferToEmi error: " . $e->getMessage());
            return false;
        }
    }
}
