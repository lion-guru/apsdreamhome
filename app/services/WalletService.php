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
        } catch (\Exception $e) {
            error_log("WalletService::ensureWallet error: " . $e->getMessage());
            return false;
        }
    }

    public function getBalance(int $userId): float
    {
        try {
            $w = $this->db->fetchOne("SELECT points_balance FROM wallet_points WHERE user_id = ?" . $this->tenantSql(), array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            return $w ? (float)$w['points_balance'] : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    public function credit(int $userId, float $amount, string $category, string $description, ?int $referenceId = null, string $referenceType = 'user'): bool
    {
        try {
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1" . $this->tenantSql(), array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            if (!$wallet) {
                $this->ensureWallet($userId);
                $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1" . $this->tenantSql(), array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
                if (!$wallet) return false;
            }
            $newBalance = (float)$wallet['points_balance'] + $amount;
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
                'balance_before' => $wallet['points_balance'],
                'balance_after' => $newBalance,
                'description' => $description,
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ], $this->tenantInsertData());
            if ($referenceId) {
                $txn['reference_id'] = $referenceId;
                $txn['reference_type'] = $referenceType;
                $txn['related_user_id'] = $referenceId;
            }
            $this->db->insert('wallet_transactions', $txn);
            return true;
        } catch (\Exception $e) {
            error_log("WalletService::credit error: " . $e->getMessage());
            return false;
        }
    }

    public function debit(int $userId, float $amount, string $description, string $category = 'withdrawal'): bool
    {
        try {
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1" . $this->tenantSql(), array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            if (!$wallet || (float)$wallet['points_balance'] < $amount) return false;
            $newBalance = (float)$wallet['points_balance'] - $amount;
            $newTotalUsed = (float)$wallet['total_used'] + $amount;
            $this->db->query(
                "UPDATE wallet_points SET points_balance = ?, total_used = ?, updated_at = NOW() WHERE user_id = ?" . $this->tenantSql(),
                array_merge([$newBalance, $newTotalUsed, $userId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
            $this->db->insert('wallet_transactions', array_merge([
                'user_id' => $userId,
                'transaction_type' => 'debit',
                'transaction_category' => $category,
                'amount' => $amount,
                'balance_before' => $wallet['points_balance'],
                'balance_after' => $newBalance,
                'description' => $description,
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ], $this->tenantInsertData()));
            return true;
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return [];
        }
    }

    public function transferToEmi(int $userId, float $amount, int $bookingId, string $description = ''): bool
    {
        try {
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1" . $this->tenantSql(), array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            if (!$wallet || (float)$wallet['points_balance'] < $amount) return false;
            $newBalance = (float)$wallet['points_balance'] - $amount;
            $newEmiTransfer = (float)$wallet['total_transferred_to_emi'] + $amount;
            $this->db->query(
                "UPDATE wallet_points SET points_balance = ?, total_transferred_to_emi = ?, updated_at = NOW() WHERE user_id = ?" . $this->tenantSql(),
                array_merge([$newBalance, $newEmiTransfer, $userId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
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
            return true;
        } catch (\Exception $e) {
            error_log("WalletService::transferToEmi error: " . $e->getMessage());
            return false;
        }
    }
}
