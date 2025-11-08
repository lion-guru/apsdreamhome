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
    protected $table = 'land_purchases';
    protected $primaryKey = 'id';

    /**
     * Get all land purchases with details
     */
    public function getAllPurchases()
    {
        $sql = "SELECT lp.*, fh.survey_number, fh.area, fh.area_unit, fh.location_address,
                       f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email,
                       lm.name as land_manager_name, lm.phone as land_manager_phone,
                       s.name as state_name, d.name as district_name
                FROM {$this->table} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN associates lm ON lp.land_manager_id = lm.associate_id
                LEFT JOIN states s ON f.state_id = s.id
                LEFT JOIN districts d ON f.district_id = d.id
                ORDER BY lp.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get purchase by ID
     */
    public function getPurchaseById($id)
    {
        $sql = "SELECT lp.*, fh.survey_number, fh.area, fh.area_unit, fh.location_address, fh.latitude, fh.longitude,
                       f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email, f.bank_account, f.ifsc_code,
                       lm.name as land_manager_name, lm.phone as land_manager_phone, lm.email as land_manager_email,
                       s.name as state_name, d.name as district_name
                FROM {$this->table} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN associates lm ON lp.land_manager_id = lm.associate_id
                LEFT JOIN states s ON f.state_id = s.id
                LEFT JOIN districts d ON f.district_id = d.id
                WHERE lp.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new land purchase
     */
    public function createPurchase($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    land_holding_id, land_manager_id, farmer_id, price, advance_amount,
                    balance_amount, payment_terms, agreement_date, possession_date,
                    registration_date, status, notes, created_at, updated_at
                ) VALUES (
                    :land_holding_id, :land_manager_id, :farmer_id, :price, :advance_amount,
                    :balance_amount, :payment_terms, :agreement_date, :possession_date,
                    :registration_date, :status, :notes, NOW(), NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    /**
     * Update purchase
     */
    public function updatePurchase($id, $data)
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
     * Get purchases by status
     */
    public function getPurchasesByStatus($status)
    {
        $sql = "SELECT lp.*, fh.survey_number, fh.area, f.name as farmer_name
                FROM {$this->table} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                WHERE lp.status = :status
                ORDER BY lp.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get purchases by land manager
     */
    public function getPurchasesByLandManager($landManagerId)
    {
        $sql = "SELECT lp.*, fh.survey_number, fh.area, f.name as farmer_name, f.phone as farmer_phone
                FROM {$this->table} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                WHERE lp.land_manager_id = :land_manager_id
                ORDER BY lp.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['land_manager_id' => $landManagerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get purchase statistics
     */
    public function getPurchaseStatistics()
    {
        $sql = "SELECT
                    COUNT(*) as total_purchases,
                    SUM(price) as total_value,
                    SUM(advance_amount) as total_advance_paid,
                    SUM(balance_amount) as total_balance_pending,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_purchases,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_purchases,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_purchases,
                    AVG(price) as avg_purchase_price
                FROM {$this->table}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Search purchases
     */
    public function searchPurchases($searchTerm, $filters = [])
    {
        $conditions = ["(f.name LIKE :search OR fh.survey_number LIKE :search OR fh.location_address LIKE :search)"];
        $params = ['search' => "%{$searchTerm}%"];

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

        $sql = "SELECT lp.*, fh.survey_number, fh.area, fh.area_unit, fh.location_address,
                       f.name as farmer_name, f.phone as farmer_phone, f.email as farmer_email,
                       lm.name as land_manager_name, s.name as state_name, d.name as district_name
                FROM {$this->table} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                LEFT JOIN associates lm ON lp.land_manager_id = lm.associate_id
                LEFT JOIN states s ON f.state_id = s.id
                LEFT JOIN districts d ON f.district_id = d.id
                WHERE {$whereClause}
                ORDER BY lp.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get pending payments
     */
    public function getPendingPayments()
    {
        $sql = "SELECT lp.*, fh.survey_number, f.name as farmer_name, f.phone as farmer_phone,
                       DATEDIFF(CURDATE(), lp.possession_date) as days_since_possession,
                       lp.balance_amount as pending_amount
                FROM {$this->table} lp
                JOIN farmer_land_holdings fh ON lp.land_holding_id = fh.id
                JOIN farmers f ON fh.farmer_id = f.id
                WHERE lp.balance_amount > 0 AND lp.status = 'in_progress'
                ORDER BY lp.possession_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
