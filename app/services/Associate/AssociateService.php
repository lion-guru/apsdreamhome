<?php

namespace App\Services\Associate;

use App\Core\Database\Database;
use App\Services\LoggingService;

/**
 * Associate Service - APS Dream Home
 * Associate management and relationship tracking
 * Custom MVC implementation without Laravel dependencies
 */
class AssociateService
{
    private $database;
    private $logger;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new LoggingService();
    }

    /**
     * Get all associates
     */
    public function getAllAssociates()
    {
        try {
            $sql = "SELECT * FROM associates ORDER BY created_at DESC";
            $stmt = $this->database->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting all associates: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get associate by ID
     */
    public function getAssociateById($id)
    {
        try {
            $sql = "SELECT * FROM associates WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->logger->error("Error getting associate by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new associate
     */
    public function createAssociate($data)
    {
        try {
            $sql = "INSERT INTO associates (name, email, phone, address, commission_rate, status, created_at) 
                    VALUES (:name, :email, :phone, :address, :commission_rate, :status, NOW())";
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':commission_rate', $data['commission_rate']);
            $stmt->bindParam(':status', $data['status'] ?? 'active');
            
            $result = $stmt->execute();
            
            if ($result) {
                $associateId = $this->database->lastInsertId();
                $this->logger->info("Associate created successfully with ID: " . $associateId);
                return $associateId;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error creating associate: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update associate
     */
    public function updateAssociate($id, $data)
    {
        try {
            $sql = "UPDATE associates SET 
                        name = :name, 
                        email = :email, 
                        phone = :phone, 
                        address = :address, 
                        commission_rate = :commission_rate, 
                        status = :status, 
                        updated_at = NOW() 
                    WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':commission_rate', $data['commission_rate']);
            $stmt->bindParam(':status', $data['status']);
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("Associate updated successfully with ID: " . $id);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error updating associate: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete associate
     */
    public function deleteAssociate($id)
    {
        try {
            $sql = "DELETE FROM associates WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("Associate deleted successfully with ID: " . $id);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error deleting associate: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate commission for associate
     */
    public function calculateCommission($associateId, $propertyId, $saleAmount)
    {
        try {
            $sql = "SELECT commission_rate FROM associates WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $associateId);
            $stmt->execute();
            $associate = $stmt->fetch();
            
            if ($associate) {
                $commission = $saleAmount * ($associate['commission_rate'] / 100);
                $this->logger->info("Commission calculated for associate {$associateId}: {$commission}");
                return $commission;
            }
            
            return 0;
        } catch (Exception $e) {
            $this->logger->error("Error calculating commission: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get associate performance metrics
     */
    public function getAssociateMetrics($associateId, $startDate, $endDate)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_sales,
                        SUM(sale_amount) as total_revenue,
                        AVG(sale_amount) as average_sale,
                        MAX(sale_amount) as max_sale
                    FROM sales 
                    WHERE associate_id = :associate_id 
                    AND sale_date BETWEEN :start_date AND :end_date";
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':associate_id', $associateId);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->logger->error("Error getting associate metrics: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get active associates
     */
    public function getActiveAssociates()
    {
        try {
            $sql = "SELECT * FROM associates WHERE status = 'active' ORDER BY name";
            $stmt = $this->database->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting active associates: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update associate status
     */
    public function updateAssociateStatus($id, $status)
    {
        try {
            $sql = "UPDATE associates SET status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("Associate status updated to {$status} for ID: " . $id);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error updating associate status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get associate sales history
     */
    public function getAssociateSalesHistory($associateId, $limit = 10)
    {
        try {
            $sql = "SELECT * FROM sales 
                    WHERE associate_id = :associate_id 
                    ORDER BY sale_date DESC 
                    LIMIT :limit";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':associate_id', $associateId);
            $stmt->bindParam(':limit', $limit);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting associate sales history: " . $e->getMessage());
            return [];
        }
    }
}
