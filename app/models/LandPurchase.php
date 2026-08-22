<?php

namespace App\Models;

use App\Models\Model;
use PDO;

/**
 * LandPurchase Model
 * Handles land purchase transactions and payments
 */
class LandPurchase extends Model
{
    protected static $table = 'land_purchases';
    protected static $primaryKey = 'id';
    protected static $tenantScoped = true;

    public function getAllPurchases()
    {
        [$tSql, $tParams] = static::tenantClause();
        $sql = "SELECT lp.*, fh.khasra_number, fh.land_area, fh.land_area_unit, fh.location,
                       f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email,
                       f.state as state_name, f.district as district_name
                FROM {$this->table()} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                WHERE 1=1{$tSql}
                ORDER BY lp.purchase_date DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($tParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPurchaseById($id)
    {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge(['id' => $id], $tParams);
        $sql = "SELECT lp.*, fh.khasra_number, fh.land_area, fh.land_area_unit, fh.location,
                       f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email, f.bank_account, f.ifsc_code,
                       f.state as state_name, f.district as district_name
                FROM {$this->table()} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                WHERE lp.id = :id{$tSql}";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createPurchase($data)
    {
        $tid = static::getTenantId();
        if ($tid > 1) $data['tenant_id'] = $tid;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table()} ({$columns}) VALUES ({$placeholders})";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($data);
        return static::getDb()->lastInsertId();
    }

    public function updatePurchase($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $setParts = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'created_at') {
                $setParts[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
        }

        if (empty($setParts)) return false;

        [$tSql, $tParams] = static::tenantClause();
        $sql = "UPDATE {$this->table()} SET " . implode(', ', $setParts) . " WHERE id = :id{$tSql}";
        $params = array_merge($params, $tParams);

        $stmt = static::getDb()->prepare($sql);
        return $stmt->execute($params);
    }

    public function getPurchasesByStatus($status)
    {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge(['status' => $status], $tParams);
        $sql = "SELECT lp.*, fh.khasra_number, fh.land_area, f.name as farmer_name
                FROM {$this->table()} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                WHERE lp.status = :status{$tSql}
                ORDER BY lp.purchase_date DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPurchasesByLandManager($landManagerId)
    {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge(['land_manager_id' => $landManagerId], $tParams);
        $sql = "SELECT lp.*, fh.khasra_number, fh.land_area, f.name as farmer_name, f.phone as farmer_phone
                FROM {$this->table()} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                WHERE lp.land_manager_id = :land_manager_id{$tSql}
                ORDER BY lp.purchase_date DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPurchaseStatistics()
    {
        [$tSql, $tParams] = static::tenantClause();
        $sql = "SELECT
                    COUNT(*) as total_purchases,
                    SUM(price) as total_value,
                    SUM(advance_amount) as total_advance_paid,
                    SUM(balance_amount) as total_balance_pending,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_purchases,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_purchases,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_purchases,
                    AVG(price) as avg_purchase_price
                FROM {$this->table()}
                WHERE 1=1{$tSql}";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($tParams);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchPurchases($searchTerm, $filters = [])
    {
        [$tSql, $tParams] = static::tenantClause();
        $searchParam = "%{$searchTerm}%";
        $conditions = ["(f.name LIKE ? OR fh.khasra_number LIKE ? OR fh.location LIKE ?)"];
        $params = [$searchParam, $searchParam, $searchParam];

        if (!empty($filters['status'])) {
            $conditions[] = "lp.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['state_id'])) {
            $conditions[] = "f.state_id = :state_id";
            $params['state_id'] = $filters['state_id'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "lp.agreement_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "lp.agreement_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $conditions);
        $params = array_merge($params, $tParams);

        $sql = "SELECT lp.*, fh.khasra_number, fh.land_area, fh.land_area_unit, fh.location,
                       f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email,
                       s.name as state_name, d.name as district_name
                FROM {$this->table()} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN states s ON f.state_id = s.id
                LEFT JOIN districts d ON f.district_id = d.id
                WHERE {$whereClause}{$tSql}
                ORDER BY lp.purchase_date DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingPayments()
    {
        [$tSql, $tParams] = static::tenantClause();
        $sql = "SELECT lp.*, fh.khasra_number, f.name as farmer_name, f.phone as farmer_phone,
                       DATEDIFF(CURDATE(), lp.possession_date) as days_since_possession,
                       lp.balance_amount as pending_amount
                FROM {$this->table()} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                WHERE lp.balance_amount > 0 AND lp.status = 'in_progress'{$tSql}
                ORDER BY lp.possession_date ASC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($tParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function table(): string
    {
        return static::$table;
    }
}
