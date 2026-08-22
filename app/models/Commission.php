<?php
namespace App\Models;
use App\Core\Database;
use Exception;
class Commission
{
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }
    public function getByUserId($userId) {
        try { return $this->db->fetchAll("SELECT * FROM commissions WHERE (user_id = ? OR associate_id = ?) ORDER BY created_at DESC", [$userId, $userId]); }
        catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
    public function getStats($userId) {
        try {
            $total = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE (user_id = ? OR associate_id = ?)", [$userId, $userId]);
            $pending = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE (user_id = ? OR associate_id = ?) AND status = 'pending'", [$userId, $userId]);
            $paid = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE (user_id = ? OR associate_id = ?) AND status = 'paid'", [$userId, $userId]);
            $month = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE (user_id = ? OR associate_id = ?) AND status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [$userId, $userId]);
            return ["total" => $total, "pending" => $pending, "paid" => $paid, "this_month" => $month];
        } catch (Exception $e) { return ["total" => 0, "pending" => 0, "paid" => 0, "this_month" => 0]; }
    }
    public function getRecent($userId, $limit = 5) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM commissions WHERE (user_id = ? OR associate_id = ?) ORDER BY created_at DESC LIMIT ?",
                [$userId, $userId, $limit]
            );
        } catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
    public function getByType($userId, $type) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM commissions WHERE (user_id = ? OR associate_id = ?) AND (commission_type = ? OR type = ?) ORDER BY created_at DESC",
                [$userId, $userId, $type, $type]
            );
        } catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
}
