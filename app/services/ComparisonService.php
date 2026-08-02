<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class ComparisonService
{
    use ServiceTenantTrait;

    private $db;
    private $maxCompare = 4;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get user's comparison list (stored in session)
     */
    public function getList(): array
    {
        if (!isset($_SESSION['compare_list'])) {
            $_SESSION['compare_list'] = [];
        }
        return $_SESSION['compare_list'];
    }

    /**
     * Add plot to comparison list
     */
    public function add(int $plotId): array
    {
        $list = $this->getList();
        if (count($list) >= $this->maxCompare) {
            return ['success' => false, 'message' => 'Maximum ' . $this->maxCompare . ' plots can be compared'];
        }
        if (in_array($plotId, $list)) {
            return ['success' => false, 'message' => 'Plot already in comparison list'];
        }
        $list[] = $plotId;
        $_SESSION['compare_list'] = $list;
        return ['success' => true, 'count' => count($list)];
    }

    /**
     * Remove plot from comparison list
     */
    public function remove(int $plotId): array
    {
        $list = $this->getList();
        $_SESSION['compare_list'] = array_values(array_diff($list, [$plotId]));
        return ['success' => true, 'count' => count($_SESSION['compare_list'])];
    }

    /**
     * Clear comparison list
     */
    public function clear(): void
    {
        $_SESSION['compare_list'] = [];
    }

    /**
     * Get comparison data for all plots in list
     */
    public function getComparisonData(): array
    {
        $list = $this->getList();
        if (empty($list)) return [];

        $placeholders = implode(',', array_fill(0, count($list), '?'));
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as colony_name, c.slug as colony_slug,
                   c.district_id,
                   d.name as district_name, s.name as state_name
            FROM plots p
            JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE p.id IN ($placeholders)" . $this->tenantSql() . "
        ");
        $stmt->execute($list);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get count of items in list
     */
    public function getCount(): int
    {
        return count($this->getList());
    }
}
