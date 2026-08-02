<?php
/**
 * RERA Project Verification Service
 * Verifies real estate projects against RERA registrations
 */

namespace App\Services\Legal;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

class RERAVerificationService
{
    use ServiceTenantTrait;
    /** @var PDO */
    protected $db;

    /** @var string */
    protected $apiUrl = 'https://rera.up.gov.in/api'; // UP RERA API endpoint

    /** @var array */
    protected $stateReraUrls = [
        'UP' => 'https://rera.up.gov.in',
        'DL' => 'https://rera.delhi.gov.in',
        'MH' => 'https://maharera.mahaonline.gov.in',
        'KA' => 'https://rera.karnataka.gov.in',
        'HR' => 'https://haryanarera.gov.in',
        'RJ' => 'https://rera.rajasthan.gov.in',
        'GJ' => 'https://rera.gujarat.gov.in',
        'TN' => 'https://rera.tn.gov.in',
        'WB' => 'https://rera.wb.gov.in',
    ];

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                $pdo = null;
            }
        }
        if (!$pdo instanceof PDO) {
            $pdo = null;
        }
        $this->db = $pdo;
    }

    /**
     * Verify project by RERA number
     */
    public function verifyByReraNumber(string $reraNumber, string $stateCode = 'UP'): array
    {
        $stateCode = strtoupper($stateCode);
        
        // First check local database
        if ($this->db) {
            try {
            $stmt = $this->db->prepare("
                SELECT * FROM rera_projects 
                WHERE rera_number = ? AND state_code = ? AND is_active = 1" . $this->tenantSql() . "
            ");
            $stmt->execute([$reraNumber, $stateCode]);
                $project = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($project) {
                    return [
                        'success' => true,
                        'source' => 'local_db',
                        'project' => $project,
                    ];
                }
            } catch (Exception $e) {
                error_log('[RERAVerificationService::verifyByReraNumber] DB error: ' . $e->getMessage());
            }
        }

        // Try to fetch from state RERA portal (mock implementation)
        $result = $this->fetchFromReraPortal($reraNumber, $stateCode);
        
        if ($result['success']) {
            // Save to local DB for future lookups
            if ($this->db) {
                $this->saveProject($result['project']);
            }
        }

        return $result;
    }

    /**
     * Search projects by builder/location
     */
    public function searchProjects(array $criteria): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
             $sql = "SELECT * FROM rera_projects WHERE is_active = 1" . $this->tenantSql();
            $params = [];

            if (!empty($criteria['builder_name'])) {
                $sql .= " AND builder_name LIKE ?";
                $params[] = "%{$criteria['builder_name']}%";
            }

            if (!empty($criteria['project_name'])) {
                $sql .= " AND project_name LIKE ?";
                $params[] = "%{$criteria['project_name']}%";
            }

            if (!empty($criteria['city'])) {
                $sql .= " AND city LIKE ?";
                $params[] = "%{$criteria['city']}%";
            }

            if (!empty($criteria['state_code'])) {
                $sql .= " AND state_code = ?";
                $params[] = strtoupper($criteria['state_code']);
            }

            if (!empty($criteria['status'])) {
                $sql .= " AND status = ?";
                $params[] = $criteria['status'];
            }

            $sql .= " ORDER BY created_at DESC LIMIT 50";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'projects' => $projects];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get project details with full info
     */
    public function getProjectDetails(string $reraNumber, string $stateCode = 'UP'): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM rera_projects 
                WHERE rera_number = ? AND state_code = ? AND is_active = 1" . $this->tenantSql() . "
            ");
            $stmt->execute([$reraNumber, strtoupper($stateCode)]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$project) {
                return ['success' => false, 'error' => 'Project not found'];
            }

            // Get related documents
            $stmt = $this->db->prepare("
                SELECT * FROM rera_documents 
                WHERE project_id = ?" . $this->tenantSql() . " ORDER BY document_type, created_at DESC
            ");
            $stmt->execute([$project['id']]);
            $project['documents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get timeline/milestones
            $stmt = $this->db->prepare("
                SELECT * FROM rera_milestones 
                WHERE project_id = ?" . $this->tenantSql() . " ORDER BY milestone_date ASC
            ");
            $stmt->execute([$project['id']]);
            $project['milestones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'project' => $project];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Fetch from RERA portal (placeholder for actual API integration)
     */
    protected function fetchFromReraPortal(string $reraNumber, string $stateCode): array
    {
        // In production, this would call the actual state RERA API
        // For now, return mock data for known projects
        
        $mockProjects = [
            'UPRERAPRJ12345' => [
                'rera_number' => 'UPRERAPRJ12345',
                'state_code' => 'UP',
                'project_name' => 'Suryoday Enclave',
                'builder_name' => 'APS Dream Home Developers',
                'builder_license' => 'UPBL12345',
                'project_type' => 'Residential Plotted',
                'status' => 'Registered',
                'registration_date' => '2024-01-15',
                'validity_date' => '2029-01-14',
                'city' => 'Gorakhpur',
                'district' => 'Gorakhpur',
                'area_sqm' => 50000,
                'total_units' => 200,
                'address' => 'NH-29, Gorakhpur',
                'latitude' => 26.7606,
                'longitude' => 83.3732,
                'completion_percentage' => 65,
            ],
            'UPRERAPRJ67890' => [
                'rera_number' => 'UPRERAPRJ67890',
                'state_code' => 'UP',
                'project_name' => 'Braj Radha Nagri',
                'builder_name' => 'APS Dream Home Developers',
                'builder_license' => 'UPBL12345',
                'project_type' => 'Residential Plotted',
                'status' => 'Registered',
                'registration_date' => '2024-03-20',
                'validity_date' => '2029-03-19',
                'city' => 'Gorakhpur',
                'district' => 'Gorakhpur',
                'area_sqm' => 40000,
                'total_units' => 150,
                'address' => 'Deoria Road, Gorakhpur',
                'latitude' => 26.7200,
                'longitude' => 83.3500,
                'completion_percentage' => 40,
            ],
            'UPRERAPRJ11111' => [
                'rera_number' => 'UPRERAPRJ11111',
                'state_code' => 'UP',
                'project_name' => 'Raghunath Nagri',
                'builder_name' => 'APS Dream Home Developers',
                'builder_license' => 'UPBL12345',
                'project_type' => 'Residential Plotted',
                'status' => 'Registered',
                'registration_date' => '2023-11-10',
                'validity_date' => '2028-11-09',
                'city' => 'Gorakhpur',
                'district' => 'Gorakhpur',
                'area_sqm' => 200000,
                'total_units' => 800,
                'address' => 'Kushinagar Road, Gorakhpur',
                'latitude' => 26.8000,
                'longitude' => 83.4000,
                'completion_percentage' => 80,
            ],
        ];

        if (isset($mockProjects[$reraNumber])) {
            return ['success' => true, 'source' => 'mock_api', 'project' => $mockProjects[$reraNumber]];
        }

        return ['success' => false, 'error' => 'Project not found in RERA records'];
    }

    /**
     * Save project to local DB
     */
    protected function saveProject(array $project): bool
    {
        if (!$this->db) return false;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO rera_projects 
                (rera_number, state_code, project_name, builder_name, builder_license, 
                 project_type, status, registration_date, validity_date, city, district, 
                 area_sqm, total_units, address, latitude, longitude, completion_percentage, 
                 created_at, updated_at, is_active, tenant_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 1, ?)
                ON DUPLICATE KEY UPDATE
                    project_name = VALUES(project_name),
                    builder_name = VALUES(builder_name),
                    builder_license = VALUES(builder_license),
                    project_type = VALUES(project_type),
                    status = VALUES(status),
                    registration_date = VALUES(registration_date),
                    validity_date = VALUES(validity_date),
                    city = VALUES(city),
                    district = VALUES(district),
                    area_sqm = VALUES(area_sqm),
                    total_units = VALUES(total_units),
                    address = VALUES(address),
                    latitude = VALUES(latitude),
                    longitude = VALUES(longitude),
                    completion_percentage = VALUES(completion_percentage),
                    updated_at = NOW()
            ");
            return $stmt->execute([
                $project['rera_number'],
                $project['state_code'],
                $project['project_name'],
                $project['builder_name'],
                $project['builder_license'] ?? '',
                $project['project_type'],
                $project['status'],
                $project['registration_date'],
                $project['validity_date'] ?? '',
                $project['city'],
                $project['district'] ?? '',
                $project['area_sqm'] ?? 0,
                $project['total_units'] ?? 0,
                $project['address'] ?? '',
                $project['latitude'] ?? 0,
                $project['longitude'] ?? 0,
                $project['completion_percentage'] ?? 0,
                $this->tenantId(),
            ]);
        } catch (Exception $e) {
            error_log('[RERAVerificationService::saveProject] ' . $e->getMessage());
            return false;
        }
    }
}