<?php
namespace App\Models;
use App\Core\Database;
use Exception;
class NetworkTree
{
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }
    public function getByUserId($userId) {
        try { return $this->db->fetchOne("SELECT * FROM network_tree WHERE associate_id = ?", [$userId]); }
        catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }
    public function getDownline($userId, $maxLevel = 5) {
        try {
            $my = $this->getByUserId($userId);
            if (!$my) return [];
            return $this->db->fetchAll(
                "SELECT nt.*, u.name, u.email, u.phone, u.referral_code, mp.current_level, mp.total_commission
                 FROM network_tree nt
                 JOIN users u ON nt.associate_id = u.id
                 LEFT JOIN mlm_profiles mp ON nt.associate_id = mp.user_id
                 WHERE nt.root_id = ? AND nt.level > 0 AND nt.level <= ? AND nt.associate_id != ?
                 ORDER BY nt.level, nt.position",
                [$my["root_id"], $maxLevel, $userId]
            );
        } catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
    public function countByLevel($userId) {
        try {
            $levels = $this->db->fetchAll(
                "SELECT level, COUNT(*) as cnt FROM network_tree WHERE parent_id = ? AND level <= 3 GROUP BY level ORDER BY level",
                [$userId]
            );
            $result = [1 => 0, 2 => 0, 3 => 0];
            foreach ($levels as $row) { $result[(int)$row["level"]] = (int)$row["cnt"]; }
            return $result;
        } catch (Exception $e) { return [1 => 0, 2 => 0, 3 => 0]; }
    }
    public function getUpline($userId) {
        try {
            $my = $this->getByUserId($userId);
            if (!$my || !$my["parent_id"]) return [];
            return $this->db->fetchAll(
                "SELECT nt.*, u.name, u.email, mp.current_level
                 FROM network_tree nt
                 JOIN users u ON nt.associate_id = u.id
                 LEFT JOIN mlm_profiles mp ON nt.associate_id = mp.user_id
                 WHERE nt.associate_id IN (
                    SELECT parent_id FROM network_tree WHERE associate_id = ?
                    UNION ALL
                    SELECT parent_id FROM network_tree WHERE associate_id IN (SELECT parent_id FROM network_tree WHERE associate_id = ?)
                 )
                 ORDER BY nt.level",
                [$userId, $userId]
            );
        } catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
    public function getRootId($userId) {
        try {
            $my = $this->getByUserId($userId);
            return $my ? $my["root_id"] : null;
        } catch (Exception $e) { error_log('Silent catch: ' . $e->getMessage()); return null; }
    }
    public function getTeamSize($userId) {
        try {
            $my = $this->getByUserId($userId);
            if (!$my) return 0;
            return (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM network_tree WHERE root_id = ? AND associate_id != ?",
                [$my["root_id"], $userId]
            );
        } catch (Exception $e) { return 0; }
    }
}
