<?php

namespace App\Services\Business;

use App\Core\Database;
use App\Traits\ServiceTenantTrait;
use Psr\Log\LoggerInterface;

/**
 * Modern Farmer Service
 * Handles farmer management, land allocation, and agricultural relationships
 */
class FarmerService
{
    use ServiceTenantTrait;
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
                           (SELECT SUM(size_acres) FROM land_allocations WHERE farmer_id = f.id AND status = 'approved' AND tenant_id = f.tenant_id) as total_land_acres,
                           (SELECT COUNT(*) FROM documents WHERE entity_type = 'farmer' AND entity_id = f.id AND tenant_id = f.tenant_id) as document_count
                    FROM farmers f 
                    WHERE f.id = ?" . $this->tenantSql();
            
            $params = [$id];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $farmer = $this->db->fetchOne($sql, $params);
            
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
            $tid = $this->tenantId();
            $sql = "SELECT f.*, 
                           (SELECT SUM(size_acres) FROM land_allocations WHERE farmer_id = f.id AND status = 'approved' AND tenant_id = f.tenant_id) as total_land_acres
                    FROM farmers f 
                    WHERE 1=1";
            $params = [];

            if ($tid > 1) {
                $sql .= " AND f.tenant_id = ?";
                $params[] = $tid;
            }

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
                    WHERE id = ?" . $this->tenantSql();
            $params = [$status, $reason, $id];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            
            $this->db->execute($sql, $params);

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
            $tid = $this->tenantId();
            $params = [];
            $fromSql = "SELECT COUNT(*) as total FROM farmers";
            if ($tid > 1) {
                $fromSql .= " WHERE tenant_id = ?";
                $params[] = $tid;
            }
            if (!empty($filters['date_from'])) {
                $fromSql .= ($tid > 1 ? " AND" : " WHERE") . " created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            $stats['total_farmers'] = $this->db->fetchOne($fromSql, $params) ?? 0;

            // Farmers by status
            $statusParams = [];
            $statusSql = "SELECT status, COUNT(*) as count FROM farmers";
            if ($tid > 1) {
                $statusSql .= " WHERE tenant_id = ?";
                $statusParams[] = $tid;
            }
            if (!empty($filters['date_from'])) {
                $statusSql .= ($tid > 1 ? " AND" : " WHERE") . " created_at >= ?";
                $statusParams[] = $filters['date_from'];
            }
            
            $statusSql .= " GROUP BY status";
            
            $statusStats = $this->db->fetchAll($statusSql, $statusParams);
            $stats['by_status'] = [];
            foreach ($statusStats as $stat) {
                $stats['by_status'][$stat['status']] = $stat['count'];
            }

            // Land allocation statistics
            $landSql = "SELECT COUNT(*) as total_allocations, SUM(size_acres) as total_acres FROM land_allocations WHERE status = 'approved'" . $this->tenantSql();
            $landParams = [];
            if ($tid > 1) $landParams[] = $tid;
            $landStats = $this->db->fetchOne($landSql, $landParams);
            $stats['land_allocations'] = $landStats ?? [
                'total_allocations' => 0,
                'total_acres' => 0
            ];

            // Commission statistics (farmer_commissions may not exist)
            $commissionStats = ['total_commissions' => 0, 'total_amount' => 0];
            try {
                $tid = $this->tenantId();
                $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
                $commissionSql = "SELECT COUNT(*) as total_commissions, SUM(amount) as total_amount FROM farmer_commissions WHERE status = 'paid'" . $tidSql;
                $commissionStats = $this->db->fetchOne($commissionSql, $tid > 1 ? [$tid] : []) ?? $commissionStats;
            } catch (\Throwable $e) {
                error_log("FarmerService commission stats: " . $e->getMessage());
            }
            $stats['commissions'] = $commissionStats;

            // Regional distribution
            $regionSql = "SELECT region, COUNT(*) as count FROM farmers";
            $regionParams = [];
            if ($tid > 1) {
                $regionSql .= " WHERE tenant_id = ?";
                $regionParams[] = $tid;
            }
            $regionSql .= " GROUP BY region ORDER BY count DESC";
            $stats['by_region'] = $this->db->fetchAll($regionSql, $regionParams);

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
            "",
            
            "",
            
            "",
            
            ""
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
        $sql = "SELECT COUNT(*) as count FROM farmers WHERE (email = ? OR phone = ?)" . $this->tenantSql();
        $params = [$email, $phone];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $count = $this->db->fetchOne($sql, $params) ?? 0;
        return $count > 0;
    }

    private function createFarmerRecord(array $data): string
    {
        $columns = ['full_name', 'email', 'phone', 'address', 'region', 'district', 'state', 'pin_code', 'aadhaar_number', 'pan_number', 'bank_account_number', 'bank_ifsc', 'status', 'created_at'];
        $values = [
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
        ];
        if ($this->tenantId() > 1) {
            $columns[] = 'tenant_id';
            $values[] = $this->tenantId();
        }
        $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
        $sql = "INSERT INTO farmers (" . implode(', ', $columns) . ") VALUES ($placeholders)";
        
        $this->db->execute($sql, $values);
        
        return $this->db->lastInsertId();
    }

    private function processFarmerDocuments(int $farmerId, array $documents): void
    {
        foreach ($documents as $docType => $docData) {
            $columns = ['entity_type', 'entity_id', 'document_type', 'url', 'uploaded_on'];
            $values = ['farmer', $farmerId, $docType, $docData['path'] ?? ''];
            if ($this->tenantId() > 1) {
                $columns[] = 'tenant_id';
                $values[] = $this->tenantId();
            }
            $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
            $sql = "INSERT INTO documents (" . implode(', ', $columns) . ") VALUES ($placeholders)";
            
            $this->db->execute($sql, $values);
        }
    }

    private function createCommissionStructure(int $farmerId): void
    {
        try {
            $columns = ['farmer_id', 'commission_type', 'rate', 'effective_from', 'created_at'];
            $values = [$farmerId, self::COMMISSION_LAND_SALE, $this->config['commission_rate']];
            if ($this->tenantId() > 1) {
                $columns[] = 'tenant_id';
                $values[] = $this->tenantId();
            }
            $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
            $sql = "INSERT INTO farmer_commission_structures (" . implode(', ', $columns) . ") VALUES ($placeholders)";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $this->db->execute($sql, $values);
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
        $sql = "SELECT COUNT(*) as count FROM land_allocations WHERE plot_id = ? AND status = 'approved'" . $this->tenantSql();
        $params = [$plotId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $count = $this->db->fetchOne($sql, $params) ?? 0;
        return $count === 0;
    }

    private function checkLandLimit(int $farmerId, float $newSize): bool
    {
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT SUM(size_acres) as total FROM land_allocations WHERE farmer_id = ? AND status = 'approved'" . $tidSql;
        $params = [$farmerId];
        if ($tid > 1) $params[] = $tid;
        $currentTotal = $this->db->fetchOne($sql, $params) ?? 0;
        return ($currentTotal + $newSize) <= $this->config['max_land_per_farmer'];
    }

    private function createLandAllocation(int $farmerId, array $data): string
    {
        $columns = ['farmer_id', 'plot_id', 'survey_number', 'size_acres', 'land_type', 'location', 'coordinates', 'allocation_date', 'expiry_date', 'allocation_amount', 'commission_rate', 'status', 'created_at'];
        $values = [
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
        ];
        if ($this->tenantId() > 1) {
            $columns[] = 'tenant_id';
            $values[] = $this->tenantId();
        }
        $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
        $sql = "INSERT INTO land_allocations (" . implode(', ', $columns) . ") VALUES ($placeholders)";
        
        $this->db->execute($sql, $values);
        
        return $this->db->lastInsertId();
    }

    private function updateLandStatus(string $plotId, string $status): void
    {
        try {
            $sql = "UPDATE land_plots SET status = ?, updated_at = NOW() WHERE plot_id = ?";
            $this->db->execute($sql, [$status, $plotId]);
        } catch (\Throwable $e) {
            error_log("FarmerService::updateLandStatus - " . $e->getMessage());
        }
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
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM documents WHERE entity_type = 'farmer' AND entity_id = ?" . $tidSql;
        return $this->db->fetchAll($sql, $tid > 1 ? [$farmerId, $tid] : [$farmerId]);
    }

    private function getFarmerLandAllocations(int $farmerId): array
    {
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM land_allocations WHERE farmer_id = ?" . $tidSql . " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, $tid > 1 ? [$farmerId, $tid] : [$farmerId]);
    }

    private function getFarmerCommissions(int $farmerId): array
    {
        try {
            $sql = "SELECT * FROM farmer_commissions WHERE farmer_id = ? ORDER BY created_at DESC";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        return $this->db->fetchAll($sql, [$farmerId]);
    }

    private function getFarmerActivities(int $farmerId): array
    {
        try {
            $sql = "SELECT * FROM farmer_activities WHERE farmer_id = ? ORDER BY created_at DESC";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
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
        $sql = "UPDATE land_allocations SET status = ?, updated_at = NOW() WHERE farmer_id = ?" . $this->tenantSql();
        $params = [self::ALLOCATION_REVOKED, $farmerId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $this->db->execute($sql, $params);
    }

    private function logFarmerActivity(int $farmerId, string $type, string $description, string $data = ''): void
    {
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $sql = "INSERT INTO farmer_activities (farmer_id, activity_type, description, data, created_by, created_at" . $tenantCol . ") 
                    VALUES (?, ?, ?, ?, 'system', NOW()" . $tenantVal . ")";
            $params = [$farmerId, $type, $description, $data ? json_encode(['data' => $data]) : null];
            if ($tid > 1) $params[] = $tid;
            $this->db->execute($sql, $params);
        } catch (\Throwable $e) {
            error_log("FarmerService::logFarmerActivity - " . $e->getMessage());
        }
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
        try {
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $sql = "INSERT INTO farmer_commissions 
                    (farmer_id, commission_type, amount, commission_rate, reference_id, reference_data, status, created_at" . $tenantCol . ") 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW()" . $tenantVal . ")";
            $params = [
                $farmerId,
                $type,
                $amount,
                $this->config['commission_rate'],
                $data['reference_id'] ?? null,
                json_encode($data)
            ];
            if ($tid > 1) $params[] = $tid;
            $this->db->execute($sql, $params);
        } catch (\Throwable $e) {
            error_log("FarmerService::createCommissionRecord - " . $e->getMessage());
        }
        
        return $this->db->lastInsertId();
    }

    private function updateFarmerTotalCommission(int $farmerId): void
    {
        try {
            $sql = "UPDATE farmers f 
                    SET total_commission = (
                        SELECT COALESCE(SUM(amount), 0) 
                        FROM farmer_commissions 
                        WHERE farmer_id = ? AND status = 'paid'
                    ),
                    updated_at = NOW()
                    WHERE f.id = ?" . $this->tenantSql();
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $params = [$farmerId, $farmerId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $this->db->execute($sql, $params);
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
        try {
            $tid = $this->tenantId();
            $sql = "SELECT fa.*, f.full_name, f.email 
                    FROM farmer_activities fa 
                    JOIN farmers f ON fa.farmer_id = f.id 
                    WHERE 1=1";
            $params = [];
            if ($tid > 1) {
                $sql .= " AND f.tenant_id = ?";
                $params[] = $tid;
            }
            $sql .= " ORDER BY fa.created_at DESC LIMIT 20";
        } catch (\Throwable $e) {
            error_log("FarmerService::getRecentActivities - " . $e->getMessage());
        }
        
        return $this->db->fetchAll($sql, $params ?? []);
    }
}
