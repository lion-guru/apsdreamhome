<?php
namespace App\Models;
use App\Core\Database;
use Exception;
class Commission
{
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }
    public function getByUserId($userId) {
        try { return $this->db->fetchAll("SELECT * FROM mlm_commission_ledger WHERE beneficiary_user_id = ? ORDER BY created_at DESC", [$userId]); }
        catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
    public function getStats($userId) {
        try {
            $total = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ?", [$userId]);
            $pending = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status = 'pending'", [$userId]);
            $paid = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status = 'paid'", [$userId]);
            $month = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [$userId]);
            return ["total" => $total, "pending" => $pending, "paid" => $paid, "this_month" => $month];
        } catch (Exception $e) { return ["total" => 0, "pending" => 0, "paid" => 0, "this_month" => 0]; }
    }
    public function getRecent($userId, $limit = 5) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM mlm_commission_ledger WHERE beneficiary_user_id = ? ORDER BY created_at DESC LIMIT ?",
                [$userId, $limit]
            );
        } catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
    public function getByType($userId, $type) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type = ? ORDER BY created_at DESC",
                [$userId, $type]
            );
        } catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
}
