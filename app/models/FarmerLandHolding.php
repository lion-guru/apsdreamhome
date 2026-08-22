<?php

namespace App\Models;

use App\Models\Model;
use PDO;

/**
 * FarmerLandHolding Model
 * Handles farmer land holdings and land purchases
 */
class FarmerLandHolding extends Model
{
    protected static $table = 'farmer_land_holdings';
    protected static $primaryKey = 'id';
    protected static $tenantScoped = true;

    public function getAllLandHoldings()
    {
        [$tSql, $tParams] = static::tenantClause();
        $sql = "SELECT fh.*, f.name as farmer_name, f.phone as farmer_phone,
                        lp.id as purchase_id, lp.purchase_date, lp.amount as purchase_price
                FROM {$this->table()} fh
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN land_purchases lp ON fh.id = lp.land_holding_id
                WHERE 1=1{$tSql}
                ORDER BY fh.created_at DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($tParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLandHoldingsByFarmer($farmerId)
    {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge(['farmer_id' => $farmerId], $tParams);
        $sql = "SELECT fh.*, lp.purchase_date, lp.amount as purchase_price
                FROM {$this->table()} fh
                LEFT JOIN land_purchases lp ON fh.id = lp.land_holding_id
                WHERE fh.farmer_id = :farmer_id{$tSql}
                ORDER BY fh.created_at DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createLandHolding($data)
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

    public function updateLandHolding($id, $data)
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

    public function getAvailableForPurchase()
    {
        [$tSql, $tParams] = static::tenantClause();
        $sql = "SELECT fh.*, f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email
                FROM {$this->table()} fh
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN land_purchases lp ON fh.id = lp.land_holding_id
                WHERE lp.id IS NULL{$tSql}
                ORDER BY fh.created_at DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($tParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLandHoldingStatistics()
    {
        [$tSql, $tParams] = static::tenantClause();
        $sql = "SELECT
                    COUNT(*) as total_holdings,
                    SUM(area) as total_area,
                    COUNT(CASE WHEN land_type = 'agricultural' THEN 1 END) as agricultural_count,
                    COUNT(CASE WHEN land_type = 'residential' THEN 1 END) as residential_count,
                    COUNT(CASE WHEN land_type = 'commercial' THEN 1 END) as commercial_count,
                    AVG(market_value) as avg_market_value
                FROM {$this->table()}
                WHERE 1=1{$tSql}";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($tParams);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchLandHoldings($searchTerm, $filters = [])
    {
        [$tSql, $tParams] = static::tenantClause();
        $conditions = ["(fh.survey_number LIKE :search OR f.name LIKE :search OR fh.location_address LIKE :search)"];
        $params = ['search' => "%{$searchTerm}%"];

        if (!empty($filters['state_id'])) {
            $conditions[] = "f.state_id = :state_id";
            $params['state_id'] = $filters['state_id'];
        }
        if (!empty($filters['district_id'])) {
            $conditions[] = "f.district_id = :district_id";
            $params['district_id'] = $filters['district_id'];
        }
        if (!empty($filters['land_type'])) {
            $conditions[] = "fh.land_type = :land_type";
            $params['land_type'] = $filters['land_type'];
        }

        $whereClause = implode(' AND ', $conditions);
        $params = array_merge($params, $tParams);

        $sql = "SELECT fh.*, f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email,
                       s.name as state_name, d.name as district_name
                FROM {$this->table()} fh
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN states s ON f.state_id = s.id
                LEFT JOIN districts d ON f.district_id = d.id
                WHERE {$whereClause}{$tSql}
                ORDER BY fh.created_at DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function table(): string
    {
        return static::$table;
    }
}
