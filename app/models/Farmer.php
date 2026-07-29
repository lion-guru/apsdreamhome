<?php

namespace App\Models;

use App\Models\Model;
use PDO;

/**
 * Farmer Model
 * Handles all farmer-related database operations
 */
class Farmer extends Model
{
    protected static $table = 'farmers';
    protected static $tenantScoped = true;

    /**
     * Get PDO connection
     */
    protected static function getPdo()
    {
        return static::getDb()->getConnection();
    }

    /**
     * Get all farmers with their land holdings
     */
    public function getAllFarmers()
    {
        $db = \App\Core\Database\Database::getInstance();
        $table = static::$table;
        $sql = "SELECT f.*, s.name as state_name, d.name as district_name,
                       COUNT(fh.id) as total_holdings,
                       COALESCE(SUM(fh.land_area), 0) as total_area
                FROM {$table} f
                LEFT JOIN farmer_land_holdings fh ON f.id = fh.farmer_id
                LEFT JOIN states s ON f.state_id = s.id
                LEFT JOIN districts d ON f.district_id = d.id
                WHERE f.status = 'active'
                GROUP BY f.id
                ORDER BY f.created_at DESC";

        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get farmer by ID with complete details
     */
    public function getFarmerById($id)
    {
        $db = \App\Core\Database\Database::getInstance();
        $table = static::$table;
        $sql = "SELECT f.*, s.name as state_name, d.name as district_name
                FROM {$table} f
                LEFT JOIN states s ON f.state_id = s.id
                LEFT JOIN districts d ON f.district_id = d.id
                WHERE f.id = :id";

        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get farmer's land holdings
     */
    public function getFarmerLandHoldings($farmerId)
    {
        $db = \App\Core\Database\Database::getInstance();
        $sql = "SELECT fh.*, lp.status as purchase_status, lp.purchase_date, lp.price as purchase_price
                FROM farmer_land_holdings fh
                LEFT JOIN land_purchases lp ON fh.id = lp.land_holding_id
                WHERE fh.farmer_id = :farmer_id
                ORDER BY fh.created_at DESC";

        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute(['farmer_id' => $farmerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create new farmer
     */
    public function createFarmer($data)
    {
        $db = \App\Core\Database\Database::getInstance();
        $table = static::$table;
        $sql = "INSERT INTO {$table} (
                    name, email, phone, address, state, district,
                    aadhar_number, pan_number, bank_account, ifsc_code,
                    status, created_at, updated_at
                ) VALUES (
                    :name, :email, :phone, :address, :state, :district,
                    :aadhar_number, :pan_number, :bank_account, :ifsc_code,
                    :status, NOW(), NOW()
                )";

        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'state' => $data['state'] ?? null,
            'district' => $data['district'] ?? null,
            'aadhar_number' => $data['aadhar_number'] ?? null,
            'pan_number' => $data['pan_number'] ?? null,
            'bank_account' => $data['bank_account'] ?? null,
            'ifsc_code' => $data['ifsc_code'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);
        return $db->getConnection()->lastInsertId();
    }

    /**
     * Update farmer
     */
    public function updateFarmer($id, $data)
    {
        $db = \App\Core\Database\Database::getInstance();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $setParts = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'created_at') {
                $setParts[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
        }

        if (empty($setParts)) {
            return false;
        }

        $table = static::$table;
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE id = :id";

        $stmt = $db->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete farmer (soft delete)
     */
    public function deleteFarmer($id)
    {
        $db = \App\Core\Database\Database::getInstance();
        $table = static::$table;
        $sql = "UPDATE {$table} SET status = 'inactive', updated_at = NOW() WHERE id = :id";
        $stmt = $db->getConnection()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get farmers by state
     */
    public function getFarmersByState($stateId)
    {
        $db = \App\Core\Database\Database::getInstance();
        $table = static::$table;
        $sql = "SELECT f.*, f.district as district_name, COUNT(fh.id) as total_holdings
                FROM {$table} f
                LEFT JOIN farmer_land_holdings fh ON f.id = fh.farmer_id
                WHERE f.status = 'active' AND f.state_id = :state_id
                GROUP BY f.id
                ORDER BY f.name";

        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute(['state_id' => $stateId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Search farmers
     */
    public function searchFarmers($searchTerm)
    {
        $db = \App\Core\Database\Database::getInstance();
        $table = static::$table;
        $searchParam = "%{$searchTerm}%";
        $sql = "SELECT f.*, f.state as state_name, f.district as district_name,
                       COUNT(fh.id) as total_holdings
                FROM {$table} f
                LEFT JOIN farmer_land_holdings fh ON f.id = fh.farmer_id
                WHERE f.status = 'active'
                AND (f.name LIKE :s1 OR f.phone LIKE :s2 OR f.email LIKE :s3)
                GROUP BY f.id
                ORDER BY f.created_at DESC";

        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute(['s1' => $searchParam, 's2' => $searchParam, 's3' => $searchParam]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get farmer statistics
     */
    public function getFarmerStatistics()
    {
        $db = \App\Core\Database\Database::getInstance();
        $table = static::$table;
        $sql = "SELECT
                    COUNT(*) as total_farmers,
                    COUNT(CASE WHEN state_id IS NOT NULL THEN 1 END) as farmers_with_state,
                    COUNT(DISTINCT state_id) as unique_states,
                    COUNT(DISTINCT district_id) as unique_districts
                FROM {$table}
                WHERE status = 'active'";

        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
