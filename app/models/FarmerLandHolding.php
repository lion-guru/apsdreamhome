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
    protected $table = 'farmer_land_holdings';
    protected $primaryKey = 'id';

    /**
     * Get all land holdings with farmer details
     */
    public function getAllLandHoldings()
    {
        $sql = "SELECT fh.*, f.name as farmer_name, f.phone as farmer_phone,
                       lp.id as purchase_id, lp.purchase_date, lp.price as purchase_price,
                       lp.status as purchase_status
                FROM {$this->table} fh
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN land_purchases lp ON fh.id = lp.land_holding_id
                ORDER BY fh.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get land holdings by farmer ID
     */
    public function getLandHoldingsByFarmer($farmerId)
    {
        $sql = "SELECT fh.*, lp.purchase_date, lp.price as purchase_price, lp.status as purchase_status
                FROM {$this->table} fh
                LEFT JOIN land_purchases lp ON fh.id = lp.land_holding_id
                WHERE fh.farmer_id = :farmer_id
                ORDER BY fh.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['farmer_id' => $farmerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new land holding
     */
    public function createLandHolding($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    farmer_id, survey_number, area, area_unit, land_type,
                    location_address, latitude, longitude, market_value,
                    document_number, status, created_at, updated_at
                ) VALUES (
                    :farmer_id, :survey_number, :area, :area_unit, :land_type,
                    :location_address, :latitude, :longitude, :market_value,
                    :document_number, :status, NOW(), NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    /**
     * Update land holding
     */
    public function updateLandHolding($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $setParts = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $setParts[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get available land holdings for purchase
     */
    public function getAvailableForPurchase()
    {
        $sql = "SELECT fh.*, f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email
                FROM {$this->table} fh
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN land_purchases lp ON fh.id = lp.land_holding_id
                WHERE lp.id IS NULL OR lp.status != 'completed'
                ORDER BY fh.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get land holding statistics
     */
    public function getLandHoldingStatistics()
    {
        $sql = "SELECT
                    COUNT(*) as total_holdings,
                    SUM(area) as total_area,
                    COUNT(CASE WHEN land_type = 'agricultural' THEN 1 END) as agricultural_count,
                    COUNT(CASE WHEN land_type = 'residential' THEN 1 END) as residential_count,
                    COUNT(CASE WHEN land_type = 'commercial' THEN 1 END) as commercial_count,
                    AVG(market_value) as avg_market_value
                FROM {$this->table}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Search land holdings
     */
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

        $sql = "SELECT fh.*, f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email,
                       s.name as state_name, d.name as district_name
                FROM {$this->table} fh
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN states s ON f.state_id = s.id
                LEFT JOIN districts d ON f.district_id = d.id
                WHERE {$whereClause}
                ORDER BY fh.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
