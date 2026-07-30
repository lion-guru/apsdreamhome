<?php

namespace App\Services\Associate;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\LoggingService;
use Exception;

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

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    private function tenantWhere(): array
    {
        $tid = $this->getTenantId();
        if ($tid > 1) {
            return [" AND tenant_id = ?", [$tid]];
        }
        return ["", []];
    }

    /**
     * Get all users
     */
    public function getAllAssociates()
    {
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $sql = "SELECT * FROM users WHERE 1=1 $tSql ORDER BY created_at DESC";
            $stmt = $this->database->prepare($sql);
            $stmt->execute($tParams);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting all users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get associate by ID
     */
    public function getAssociateById($id)
    {
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $sql = "SELECT * FROM users WHERE id = :id $tSql";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            foreach ($tParams as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
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
            $tid = $this->getTenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", :tid" : "";
            $sql = "INSERT INTO users (name, email, phone, address, commission_rate, status, created_at$tenantCol) 
                    VALUES (:name, :email, :phone, :address, :commission_rate, :status, NOW()$tenantVal)";
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':commission_rate', $data['commission_rate']);
            $stmt->bindParam(':status', $data['status'] ?? 'active');
            if ($tid > 1) $stmt->bindParam(':tid', $tid);
            
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
            [$tSql, $tParams] = $this->tenantWhere();
            $tid = $this->getTenantId();
            $sql = "UPDATE users SET 
                        name = :name, 
                        email = :email, 
                        phone = :phone, 
                        address = :address, 
                        commission_rate = :commission_rate, 
                        status = :status, 
                        updated_at = NOW() 
                    WHERE id = :id $tSql";
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':commission_rate', $data['commission_rate']);
            $stmt->bindParam(':status', $data['status']);
            foreach ($tParams as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
            
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
            [$tSql, $tParams] = $this->tenantWhere();
            $sql = "DELETE FROM users WHERE id = :id $tSql";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            foreach ($tParams as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
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
            [$tSql, $tParams] = $this->tenantWhere();
            $sql = "SELECT commission_rate FROM users WHERE id = :id $tSql";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $associateId);
            foreach ($tParams as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
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
     * Get active users
     */
    public function getActiveAssociates()
    {
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $sql = "SELECT * FROM users WHERE status = 'active' $tSql ORDER BY name";
            $stmt = $this->database->prepare($sql);
            $stmt->execute($tParams);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting active users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update associate status
     */
    public function updateAssociateStatus($id, $status)
    {
        try {
            [$tSql, $tParams] = $this->tenantWhere();
            $tid = $this->getTenantId();
            $sql = "UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id $tSql";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            foreach ($tParams as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
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
