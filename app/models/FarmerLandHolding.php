<?php

// TODO: Add proper error handling with try-catch blocks


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

    /**
     * Get all land holdings with farmer details
     */
    public function getAllLandHoldings()
    {
        $table = static::$table;
        $sql = "SELECT fh.*, f.name as farmer_name, f.phone as farmer_phone,
                        lp.id as purchase_id, lp.purchase_date, lp.amount as purchase_price
                FROM {$table} fh
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN land_purchases lp ON fh.id = lp.land_holding_id
                ORDER BY fh.created_at DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLandHoldingsByFarmer($farmerId)
    {
        $table = static::$table;
        $sql = "SELECT fh.*, lp.purchase_date, lp.amount as purchase_price
                FROM {$table} fh
                LEFT JOIN land_purchases lp ON fh.id = lp.land_holding_id
                WHERE fh.farmer_id = :farmer_id
                ORDER BY fh.created_at DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute(['farmer_id' => $farmerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createLandHolding($data)
    {
        $table = static::$table;
        $sql = "INSERT INTO {$table} (
                    farmer_id, survey_number, area, area_unit, land_type,
                    location_address, latitude, longitude, market_value,
                    document_number, status, created_at, updated_at
                ) VALUES (
                    :farmer_id, :survey_number, :area, :area_unit, :land_type,
                    :location_address, :latitude, :longitude, :market_value,
                    :document_number, :status, NOW(), NOW()
                )";

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

        if (empty($setParts)) {
            return false;
        }

        $table = static::$table;
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE id = :id";

        $stmt = static::getDb()->prepare($sql);
        return $stmt->execute($params);
    }

    public function getAvailableForPurchase()
    {
        $table = static::$table;
        $sql = "SELECT fh.*, f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email
                FROM {$table} fh
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN land_purchases lp ON fh.id = lp.land_holding_id
                WHERE lp.id IS NULL
                ORDER BY fh.created_at DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLandHoldingStatistics()
    {
        $table = static::$table;
        $sql = "SELECT
                    COUNT(*) as total_holdings,
                    SUM(area) as total_area,
                    COUNT(CASE WHEN land_type = 'agricultural' THEN 1 END) as agricultural_count,
                    COUNT(CASE WHEN land_type = 'residential' THEN 1 END) as residential_count,
                    COUNT(CASE WHEN land_type = 'commercial' THEN 1 END) as commercial_count,
                    AVG(market_value) as avg_market_value
                FROM {$table}";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchLandHoldings($searchTerm, $filters = [])
    {
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

        $table = static::$table;
        $sql = "SELECT fh.*, f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email,
                       s.name as state_name, d.name as district_name
                FROM {$table} fh
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN states s ON f.state_id = s.id
                LEFT JOIN districts d ON f.district_id = d.id
                WHERE {$whereClause}
                ORDER BY fh.created_at DESC";

        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
