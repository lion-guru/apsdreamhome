<?php

namespace App\Services\Business;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Farmer Service
 * Handles farmer management, land allocation, and agricultural relationships
 */
class FarmerService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $landTypes = [];
    private array $cropTypes = [];

    // Farmer statuses
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_BLACKLISTED = 'blacklisted';

    // Land allocation statuses
    public const ALLOCATION_PENDING = 'pending';
    public const ALLOCATION_APPROVED = 'approved';
    public const ALLOCATION_REJECTED = 'rejected';
    public const ALLOCATION_TRANSFERRED = 'transferred';
    public const ALLOCATION_REVOKED = 'revoked';

    // Commission types
    public const COMMISSION_LAND_SALE = 'land_sale';
    public const COMMISSION_CROP_SALE = 'crop_sale';
    public const COMMISSION_SERVICE = 'service';
    public const COMMISSION_REFERRAL = 'referral';

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'auto_approve_land' => false,
            'commission_rate' => 5.0, // 5%
            'max_land_per_farmer' => 10, // acres
            'min_land_size' => 0.5, // acres
            'farmer_retention_days' => 365,
            'commission_payment_days' => 30
        ], $config);
        
        $this->initializeFarmerTables();
        $this->loadLandAndCropTypes();
    }

    /**
     * Register new farmer
     */
    public function registerFarmer(array $farmerData, array $documents = []): array
    {
        try {
            // Validate farmer data
            $validation = $this->validateFarmerData($farmerData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Farmer validation failed',
                    'errors' => $validation['errors']
                ];
            }

            // Check for duplicate farmer
            if ($this->isDuplicateFarmer($farmerData['email'], $farmerData['phone'] ?? null)) {
                return [
                    'success' => false,
                    'message' => 'Farmer already exists with this email or phone'
                ];
            }

            // Create farmer record
            $farmerId = $this->createFarmerRecord($farmerData);

            // Process documents
            if (!empty($documents)) {
                $this->processFarmerDocuments($farmerId, $documents);
            }

            // Generate initial commission structure
            $this->createCommissionStructure($farmerId);

            // Send welcome notification
            $this->sendWelcomeNotification($farmerId, $farmerData);

            $this->logger->info("Farmer registered successfully", [
                'farmer_id' => $farmerId,
                'name' => $farmerData['full_name'],
                'email' => $farmerData['email']
            ]);

            return [
                'success' => true,
                'message' => 'Farmer registered successfully',
                'farmer_id' => $farmerId
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to register farmer", [
                'email' => $farmerData['email'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to register farmer: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Allocate land to farmer
     */
    public function allocateLand(int $farmerId, array $landData): array
    {
        try {
            // Validate land data
            $validation = $this->validateLandAllocation($farmerId, $landData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Land allocation validation failed',
                    'errors' => $validation['errors']
                ];
            }

            // Check land availability
            if (!$this->isLandAvailable($landData['plot_id'])) {
                return [
                    'success' => false,
                    'message' => 'Land is not available for allocation'
                ];
            }

            // Check farmer land limit
            if (!$this->checkLandLimit($farmerId, $landData['size_acres'])) {
                return [
                    'success' => false,
                    'message' => 'Land allocation exceeds maximum limit for this farmer'
                ];
            }

            // Create land allocation record
            $allocationId = $this->createLandAllocation($farmerId, $landData);

            // Update land status
            $this->updateLandStatus($landData['plot_id'], 'allocated');

            // Generate commission for land allocation
            $this->generateCommission($farmerId, self::COMMISSION_LAND_SALE, $landData);

            // Send allocation notification
            $this->sendAllocationNotification($farmerId, $allocationId, $landData);

            $this->logger->info("Land allocated successfully", [
                'farmer_id' => $farmerId,
                'allocation_id' => $allocationId,
                'plot_id' => $landData['plot_id'],
                'size_acres' => $landData['size_acres']
            ]);

            return [
                'success' => true,
                'message' => 'Land allocated successfully',
                'allocation_id' => $allocationId
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to allocate land", [
                'farmer_id' => $farmerId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to allocate land: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get farmer by ID
     */
    public function getFarmer(int $id): ?array
    {
        try {
            $sql = "SELECT f.*, 
                           (SELECT SUM(size_acres) FROM land_allocations WHERE farmer_id = f.id AND status = 'approved') as total_land_acres,
                           (SELECT COUNT(*) FROM farmer_documents WHERE farmer_id = f.id) as document_count
                    FROM farmers f 
                    WHERE f.id = ?";
            
            $farmer = $this->db->fetchOne($sql, [$id]);
            
            if ($farmer) {
                $farmer['documents'] = $this->getFarmerDocuments($id);
                $farmer['allocations'] = $this->getFarmerLandAllocations($id);
                $farmer['commissions'] = $this->getFarmerCommissions($id);
                $farmer['activities'] = $this->getFarmerActivities($id);
            }
            
            return $farmer;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get farmer", ['id' => $id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get farmers with filters
     */
    public function getFarmers(array $filters = []): array
    {
        try {
            $sql = "SELECT f.*, 
                           (SELECT SUM(size_acres) FROM land_allocations WHERE farmer_id = f.id AND status = 'approved') as total_land_acres
                    FROM farmers f 
                    WHERE 1=1";
            $params = [];

            // Add filters
            if (!empty($filters['status'])) {
                $sql .= " AND f.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['region'])) {
                $sql .= " AND f.region = ?";
                $params[] = $filters['region'];
            }

            if (!empty($filters['land_size_min'])) {
                $sql .= " AND (SELECT SUM(size_acres) FROM land_allocations WHERE farmer_id = f.id AND status = 'approved') >= ?";
                $params[] = $filters['land_size_min'];
            }

            if (!empty($filters['land_size_max'])) {
                $sql .= " AND (SELECT SUM(size_acres) FROM land_allocations WHERE farmer_id = f.id AND status = 'approved') <= ?";
                $params[] = $filters['land_size_max'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND f.created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND f.created_at <= ?";
                $params[] = $filters['date_to'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (f.full_name LIKE ? OR f.email LIKE ? OR f.phone LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY f.created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }

            $farmers = $this->db->fetchAll($sql, $params);
            
            foreach ($farmers as &$farmer) {
                $farmer['allocations'] = $this->getFarmerLandAllocations($farmer['id']);
                $farmer['commissions'] = $this->getFarmerCommissions($farmer['id']);
            }
            
            return $farmers;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get farmers", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Update farmer status
     */
    public function updateFarmerStatus(int $id, string $status, string $reason = ''): array
    {
        try {
            // Validate status
            if (!in_array($status, $this->getValidStatuses())) {
                return [
                    'success' => false,
                    'message' => 'Invalid status'
                ];
            }

            // Get current farmer
            $farmer = $this->getFarmer($id);
            if (!$farmer) {
                return [
                    'success' => false,
                    'message' => 'Farmer not found'
                ];
            }

            // Update status
            $sql = "UPDATE farmers 
                    SET status = ?, status_reason = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $this->db->execute($sql, [$status, $reason, $id]);

            // Handle status-specific actions
            if ($status === self::STATUS_SUSPENDED || $status === self::STATUS_BLACKLISTED) {
                $this->revokeAllLandAllocations($id);
            }

            // Log status change
            $this->logFarmerActivity($id, 'status_change', "Status changed to {$status}", $reason);

            // Send status notification
            $this->sendStatusNotification($id, $status, $reason);

            $this->logger->info("Farmer status updated", [
                'farmer_id' => $id,
                'old_status' => $farmer['status'],
                'new_status' => $status,
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'message' => 'Farmer status updated successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to update farmer status", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate commission for farmer
     */
    public function generateCommission(int $farmerId, string $type, array $data): array
    {
        try {
            // Validate commission type
            if (!in_array($type, $this->getCommissionTypes())) {
                return [
                    'success' => false,
                    'message' => 'Invalid commission type'
                ];
            }

            // Calculate commission amount
            $amount = $this->calculateCommissionAmount($type, $data);

            // Create commission record
            $commissionId = $this->createCommissionRecord($farmerId, $type, $amount, $data);

            // Update farmer total commission
            $this->updateFarmerTotalCommission($farmerId);

            // Send commission notification
            $this->sendCommissionNotification($farmerId, $commissionId, $amount);

            $this->logger->info("Commission generated", [
                'farmer_id' => $farmerId,
                'commission_id' => $commissionId,
                'type' => $type,
                'amount' => $amount
            ]);

            return [
                'success' => true,
                'message' => 'Commission generated successfully',
                'commission_id' => $commissionId,
                'amount' => $amount
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to generate commission", [
                'farmer_id' => $farmerId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate commission: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get farmer statistics
     */
    public function getFarmerStats(array $filters = []): array
    {
        try {
            $stats = [];

            // Total farmers
            $sql = "SELECT COUNT(*) as total FROM farmers";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            $stats['total_farmers'] = $this->db->fetchOne($sql, $params) ?? 0;

            // Farmers by status
            $statusSql = "SELECT status, COUNT(*) as count FROM farmers";
            $statusParams = [];
            
            if (!empty($filters['date_from'])) {
                $statusSql .= " WHERE created_at >= ?";
                $statusParams[] = $filters['date_from'];
            }
            
            $statusSql .= " GROUP BY status";
            
            $statusStats = $this->db->fetchAll($statusSql, $statusParams);
            $stats['by_status'] = [];
            foreach ($statusStats as $stat) {
                $stats['by_status'][$stat['status']] = $stat['count'];
            }

            // Land allocation statistics
            $landSql = "SELECT COUNT(*) as total_allocations, SUM(size_acres) as total_acres FROM land_allocations WHERE status = 'approved'";
            $landStats = $this->db->fetchOne($landSql);
            $stats['land_allocations'] = $landStats ?? [
                'total_allocations' => 0,
                'total_acres' => 0
            ];

            // Commission statistics
            $commissionSql = "SELECT COUNT(*) as total_commissions, SUM(amount) as total_amount FROM farmer_commissions WHERE status = 'paid'";
            $commissionStats = $this->db->fetchOne($commissionSql);
            $stats['commissions'] = $commissionStats ?? [
                'total_commissions' => 0,
                'total_amount' => 0
            ];

            // Regional distribution
            $regionSql = "SELECT region, COUNT(*) as count FROM farmers GROUP BY region ORDER BY count DESC";
            $stats['by_region'] = $this->db->fetchAll($regionSql);

            // Recent activities
            $stats['recent_activities'] = $this->getRecentActivities($filters);

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get farmer stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Private helper methods
     */
    private function initializeFarmerTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS farmers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                phone VARCHAR(50),
                address TEXT,
                region VARCHAR(100),
                district VARCHAR(100),
                state VARCHAR(100),
                pin_code VARCHAR(10),
                aadhaar_number VARCHAR(12),
                pan_number VARCHAR(10),
                bank_account_number VARCHAR(50),
                bank_ifsc VARCHAR(20),
                status ENUM('active', 'inactive', 'suspended', 'blacklisted') DEFAULT 'active',
                status_reason TEXT,
                total_commission DECIMAL(15,2) DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_phone (phone),
                INDEX idx_status (status),
                INDEX idx_region (region),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS farmer_documents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_id INT NOT NULL,
                document_type VARCHAR(100) NOT NULL,
                document_name VARCHAR(255),
                file_path VARCHAR(500),
                file_size INT,
                mime_type VARCHAR(100),
                verified BOOLEAN DEFAULT FALSE,
                verification_date TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE,
                INDEX idx_farmer_id (farmer_id),
                INDEX idx_document_type (document_type),
                INDEX idx_verified (verified)
            )",
            
            "CREATE TABLE IF NOT EXISTS land_allocations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_id INT NOT NULL,
                plot_id VARCHAR(100) NOT NULL,
                survey_number VARCHAR(100),
                size_acres DECIMAL(10,2) NOT NULL,
                land_type VARCHAR(100),
                location TEXT,
                coordinates VARCHAR(255),
                allocation_date DATE NOT NULL,
                expiry_date DATE,
                status ENUM('pending', 'approved', 'rejected', 'transferred', 'revoked') DEFAULT 'pending',
                allocation_amount DECIMAL(15,2),
                commission_rate DECIMAL(5,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE,
                INDEX idx_farmer_id (farmer_id),
                INDEX idx_plot_id (plot_id),
                INDEX idx_status (status),
                INDEX idx_allocation_date (allocation_date)
            )",
            
            "CREATE TABLE IF NOT EXISTS farmer_commissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_id INT NOT NULL,
                commission_type ENUM('land_sale', 'crop_sale', 'service', 'referral') NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                commission_rate DECIMAL(5,2),
                reference_id VARCHAR(100),
                reference_data JSON,
                status ENUM('pending', 'approved', 'paid', 'rejected') DEFAULT 'pending',
                payment_date TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE,
                INDEX idx_farmer_id (farmer_id),
                INDEX idx_commission_type (commission_type),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS farmer_activities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_id INT NOT NULL,
                activity_type VARCHAR(100) NOT NULL,
                description TEXT,
                data JSON,
                created_by VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE,
                INDEX idx_farmer_id (farmer_id),
                INDEX idx_activity_type (activity_type),
                INDEX idx_created_at (created_at)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function loadLandAndCropTypes(): void
    {
        $this->landTypes = [
            'agricultural' => 'Agricultural Land',
            'residential' => 'Residential Land',
            'commercial' => 'Commercial Land',
            'industrial' => 'Industrial Land',
            'mixed_use' => 'Mixed Use Land'
        ];

        $this->cropTypes = [
            'rice' => 'Rice',
            'wheat' => 'Wheat',
            'cotton' => 'Cotton',
            'sugarcane' => 'Sugarcane',
            'vegetables' => 'Vegetables',
            'fruits' => 'Fruits',
            'pulses' => 'Pulses',
            'oilseeds' => 'Oilseeds'
        ];
    }

    private function validateFarmerData(array $data): array
    {
        $errors = [];

        if (empty($data['full_name']) || strlen($data['full_name']) < 3) {
            $errors[] = 'Full name is required and must be at least 3 characters';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }

        if (empty($data['phone']) || !preg_match('/^[\d\s\-\+\(\)]+$/', $data['phone'])) {
            $errors[] = 'Valid phone number is required';
        }

        if (empty($data['address'])) {
            $errors[] = 'Address is required';
        }

        if (empty($data['region'])) {
            $errors[] = 'Region is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function isDuplicateFarmer(string $email, ?string $phone): bool
    {
        $sql = "SELECT COUNT(*) as count FROM farmers WHERE email = ? OR phone = ?";
        $count = $this->db->fetchOne($sql, [$email, $phone]) ?? 0;
        return $count > 0;
    }

    private function createFarmerRecord(array $data): string
    {
        $sql = "INSERT INTO farmers 
                (full_name, email, phone, address, region, district, state, pin_code, aadhaar_number, pan_number, bank_account_number, bank_ifsc, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        
        $this->db->execute($sql, [
            $data['full_name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['address'],
            $data['region'],
            $data['district'] ?? null,
            $data['state'] ?? null,
            $data['pin_code'] ?? null,
            $data['aadhaar_number'] ?? null,
            $data['pan_number'] ?? null,
            $data['bank_account_number'] ?? null,
            $data['bank_ifsc'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    private function processFarmerDocuments(int $farmerId, array $documents): void
    {
        foreach ($documents as $docType => $docData) {
            $sql = "INSERT INTO farmer_documents 
                    (farmer_id, document_type, document_name, file_path, file_size, mime_type, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->execute($sql, [
                $farmerId,
                $docType,
                $docData['name'] ?? '',
                $docData['path'] ?? '',
                $docData['size'] ?? 0,
                $docData['mime_type'] ?? ''
            ]);
        }
    }

    private function createCommissionStructure(int $farmerId): void
    {
        $sql = "INSERT INTO farmer_commission_structures 
                (farmer_id, commission_type, rate, effective_from, created_at) 
                VALUES (?, ?, ?, NOW(), NOW())";
        
        $this->db->execute($sql, [
            $farmerId,
            self::COMMISSION_LAND_SALE,
            $this->config['commission_rate']
        ]);
    }

    private function sendWelcomeNotification(int $farmerId, array $farmerData): void
    {
        // Mock notification sending
        $this->logger->info("Welcome notification sent", [
            'farmer_id' => $farmerId,
            'email' => $farmerData['email']
        ]);
    }

    private function validateLandAllocation(int $farmerId, array $data): array
    {
        $errors = [];

        if (empty($data['plot_id'])) {
            $errors[] = 'Plot ID is required';
        }

        if (empty($data['size_acres']) || $data['size_acres'] < $this->config['min_land_size']) {
            $errors[] = "Land size must be at least {$this->config['min_land_size']} acres";
        }

        if (empty($data['allocation_date'])) {
            $errors[] = 'Allocation date is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function isLandAvailable(string $plotId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM land_allocations WHERE plot_id = ? AND status = 'approved'";
        $count = $this->db->fetchOne($sql, [$plotId]) ?? 0;
        return $count === 0;
    }

    private function checkLandLimit(int $farmerId, float $newSize): bool
    {
        $sql = "SELECT SUM(size_acres) as total FROM land_allocations WHERE farmer_id = ? AND status = 'approved'";
        $currentTotal = $this->db->fetchOne($sql, [$farmerId]) ?? 0;
        return ($currentTotal + $newSize) <= $this->config['max_land_per_farmer'];
    }

    private function createLandAllocation(int $farmerId, array $data): string
    {
        $sql = "INSERT INTO land_allocations 
                (farmer_id, plot_id, survey_number, size_acres, land_type, location, coordinates, allocation_date, expiry_date, allocation_amount, commission_rate, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $this->db->execute($sql, [
            $farmerId,
            $data['plot_id'],
            $data['survey_number'] ?? null,
            $data['size_acres'],
            $data['land_type'] ?? null,
            $data['location'] ?? null,
            $data['coordinates'] ?? null,
            $data['allocation_date'],
            $data['expiry_date'] ?? null,
            $data['allocation_amount'] ?? 0,
            $this->config['commission_rate'],
            $this->config['auto_approve'] ? self::ALLOCATION_APPROVED : self::ALLOCATION_PENDING
        ]);
        
        return $this->db->lastInsertId();
    }

    private function updateLandStatus(string $plotId, string $status): void
    {
        $sql = "UPDATE land_plots SET status = ?, updated_at = NOW() WHERE plot_id = ?";
        $this->db->execute($sql, [$status, $plotId]);
    }

    private function sendAllocationNotification(int $farmerId, int $allocationId, array $data): void
    {
        // Mock notification sending
        $this->logger->info("Land allocation notification sent", [
            'farmer_id' => $farmerId,
            'allocation_id' => $allocationId,
            'plot_id' => $data['plot_id']
        ]);
    }

    private function getFarmerDocuments(int $farmerId): array
    {
        $sql = "SELECT * FROM farmer_documents WHERE farmer_id = ?";
        return $this->db->fetchAll($sql, [$farmerId]);
    }

    private function getFarmerLandAllocations(int $farmerId): array
    {
        $sql = "SELECT * FROM land_allocations WHERE farmer_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$farmerId]);
    }

    private function getFarmerCommissions(int $farmerId): array
    {
        $sql = "SELECT * FROM farmer_commissions WHERE farmer_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$farmerId]);
    }

    private function getFarmerActivities(int $farmerId): array
    {
        $sql = "SELECT * FROM farmer_activities WHERE farmer_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$farmerId]);
    }

    private function getValidStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_SUSPENDED,
            self::STATUS_BLACKLISTED
        ];
    }

    private function revokeAllLandAllocations(int $farmerId): void
    {
        $sql = "UPDATE land_allocations SET status = ?, updated_at = NOW() WHERE farmer_id = ?";
        $this->db->execute($sql, [self::ALLOCATION_REVOKED, $farmerId]);
    }

    private function logFarmerActivity(int $farmerId, string $type, string $description, string $data = ''): void
    {
        $sql = "INSERT INTO farmer_activities (farmer_id, activity_type, description, data, created_by, created_at) 
                VALUES (?, ?, ?, ?, 'system', NOW())";
        
        $this->db->execute($sql, [
            $farmerId,
            $type,
            $description,
            $data ? json_encode(['data' => $data]) : null
        ]);
    }

    private function sendStatusNotification(int $farmerId, string $status, string $reason): void
    {
        // Mock notification sending
        $this->logger->info("Status notification sent", [
            'farmer_id' => $farmerId,
            'status' => $status,
            'reason' => $reason
        ]);
    }

    private function getCommissionTypes(): array
    {
        return [
            self::COMMISSION_LAND_SALE,
            self::COMMISSION_CROP_SALE,
            self::COMMISSION_SERVICE,
            self::COMMISSION_REFERRAL
        ];
    }

    private function calculateCommissionAmount(string $type, array $data): float
    {
        switch ($type) {
            case self::COMMISSION_LAND_SALE:
                $landValue = $data['allocation_amount'] ?? 0;
                return $landValue * ($this->config['commission_rate'] / 100);
            
            case self::COMMISSION_CROP_SALE:
                $cropValue = $data['crop_value'] ?? 0;
                return $cropValue * ($this->config['commission_rate'] / 100);
            
            case self::COMMISSION_SERVICE:
                return $data['service_amount'] ?? 0;
            
            case self::COMMISSION_REFERRAL:
                return $data['referral_amount'] ?? 0;
            
            default:
                return 0;
        }
    }

    private function createCommissionRecord(int $farmerId, string $type, float $amount, array $data): string
    {
        $sql = "INSERT INTO farmer_commissions 
                (farmer_id, commission_type, amount, commission_rate, reference_id, reference_data, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $this->db->execute($sql, [
            $farmerId,
            $type,
            $amount,
            $this->config['commission_rate'],
            $data['reference_id'] ?? null,
            json_encode($data)
        ]);
        
        return $this->db->lastInsertId();
    }

    private function updateFarmerTotalCommission(int $farmerId): void
    {
        $sql = "UPDATE farmers f 
                SET total_commission = (
                    SELECT COALESCE(SUM(amount), 0) 
                    FROM farmer_commissions 
                    WHERE farmer_id = ? AND status = 'paid'
                ),
                updated_at = NOW()
                WHERE f.id = ?";
        
        $this->db->execute($sql, [$farmerId, $farmerId]);
    }

    private function sendCommissionNotification(int $farmerId, int $commissionId, float $amount): void
    {
        // Mock notification sending
        $this->logger->info("Commission notification sent", [
            'farmer_id' => $farmerId,
            'commission_id' => $commissionId,
            'amount' => $amount
        ]);
    }

    private function getRecentActivities(array $filters): array
    {
        $sql = "SELECT fa.*, f.full_name, f.email 
                FROM farmer_activities fa 
                JOIN farmers f ON fa.farmer_id = f.id 
                ORDER BY fa.created_at DESC 
                LIMIT 20";
        
        return $this->db->fetchAll($sql);
    }
}
