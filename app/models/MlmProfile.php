<?php
namespace App\Models;
use App\Core\Database;
use Exception;
class MlmProfile
{
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }
    public function getByUserId($userId) {
        try { return $this->db->fetchOne("SELECT * FROM mlm_profiles WHERE user_id = ?", [$userId]); }
        catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }
    public function getByReferralCode($code) {
        try { return $this->db->fetchOne("SELECT * FROM mlm_profiles WHERE referral_code = ?", [$code]); }
        catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }
    public function create($data) {
        try { return $this->db->insert("mlm_profiles", $data); }
        catch (Exception $e) { error_log("MlmProfile create: " . $e->getMessage()); return false; }
    }
    public function update($userId, $data) {
        try { return $this->db->update("mlm_profiles", $data, "user_id = ?", [$userId]); }
        catch (Exception $e) { error_log("MlmProfile update: " . $e->getMessage()); return false; }
    }
    public function getRank($userId) {
        try {
            $p = $this->db->fetchOne("SELECT current_level, total_team_size, lifetime_sales FROM mlm_profiles WHERE user_id = ?", [$userId]);
            return $p ? $p["current_level"] ?? "Associate" : "Associate";
        } catch (Exception $e) { return "Associate"; }
    }
    public function updateTeamStats($userId) {
        try {
            $tree = $this->db->fetchOne("SELECT id FROM network_tree WHERE associate_id = ?", [$userId]);
            if (!$tree) return false;
            $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM network_tree WHERE root_id = (SELECT root_id FROM network_tree WHERE associate_id = ?)", [$userId]);
            $direct = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM network_tree WHERE parent_id = ?", [$userId]);
            $sales = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status = 'paid'", [$userId]);
            return $this->update($userId, ["total_team_size" => $total, "direct_referrals" => $direct, "lifetime_sales" => $sales]);
        } catch (Exception $e) { error_log("MlmProfile updateTeamStats: " . $e->getMessage()); return false; }
    }
}
