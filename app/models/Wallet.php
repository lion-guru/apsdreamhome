<?php
namespace App\Models;
use App\Core\Database;
use Exception;
class Wallet
{
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }
    public function getByUserId($userId) {
        try { return $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ?", [$userId]); }
        catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }
    public function getBalance($userId) {
        try {
            $w = $this->getByUserId($userId);
            return $w ? (float)$w["points_balance"] : 0;
        } catch (Exception $e) { return 0; }
    }
    public function credit($userId, $amount, $category, $description) {
        try {
            $wallet = $this->getByUserId($userId);
            if (!$wallet) return false;
            $newBalance = (float)$wallet["points_balance"] + $amount;
            $newTotalEarned = (float)$wallet["total_earned"] + $amount;
            $field = $category === "referral" ? "referral_earnings" : ($category === "commission" ? "commission_earnings" : "bonus_earnings");
            $this->db->query(
                "UPDATE wallet_points SET points_balance = ?, total_earned = ?, $field = $field + ?, updated_at = NOW() WHERE user_id = ?",
                [$newBalance, $newTotalEarned, $amount, $userId]
            );
            $this->db->insert("wallet_transactions", [
                "user_id" => $userId, "transaction_type" => "credit", "transaction_category" => $category,
                "amount" => $amount, "balance_before" => $wallet["points_balance"], "balance_after" => $newBalance,
                "description" => $description, "status" => "completed", "created_at" => date("Y-m-d H:i:s")
            ]);
            return true;
        } catch (Exception $e) { error_log("Wallet credit: " . $e->getMessage()); return false; }
    }
    public function debit($userId, $amount, $description) {
        try {
            $wallet = $this->getByUserId($userId);
            if (!$wallet || (float)$wallet["points_balance"] < $amount) return false;
            $newBalance = (float)$wallet["points_balance"] - $amount;
            $newTotalUsed = (float)$wallet["total_used"] + $amount;
            $this->db->query(
                "UPDATE wallet_points SET points_balance = ?, total_used = ?, updated_at = NOW() WHERE user_id = ?",
                [$newBalance, $newTotalUsed, $userId]
            );
            $this->db->insert("wallet_transactions", [
                "user_id" => $userId, "transaction_type" => "debit", "transaction_category" => "withdrawal",
                "amount" => $amount, "balance_before" => $wallet["points_balance"], "balance_after" => $newBalance,
                "description" => $description, "status" => "completed", "created_at" => date("Y-m-d H:i:s")
            ]);
            return true;
        } catch (Exception $e) { error_log("Wallet debit: " . $e->getMessage()); return false; }
    }
    public function getTransactions($userId, $limit = 20) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
                [$userId, $limit]
            );
        } catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
    public function createWallet($userId) {
        try {
            $exists = $this->getByUserId($userId);
            if ($exists) return true;
            return $this->db->insert("wallet_points", [
                "user_id" => $userId, "points_balance" => 0, "total_earned" => 0, "total_used" => 0,
                "total_transferred_to_emi" => 0, "referral_earnings" => 0, "commission_earnings" => 0,
                "bonus_earnings" => 0, "status" => "active", "created_at" => date("Y-m-d H:i:s"), "updated_at" => date("Y-m-d H:i:s")
            ]);
        } catch (Exception $e) { error_log("Wallet create: " . $e->getMessage()); return false; }
    }
}
