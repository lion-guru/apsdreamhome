<?php

namespace App\Services\Farmer;

use App\Core\Database\Database;
use App\Services\LoggingService;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Farmer/Kisan Management Service - APS Dream Home
 * Complete management system for farmers and agricultural land relationships
 * Custom MVC implementation without Laravel dependencies
 */
class FarmerServiceEnhanced
{
    private $database;
    private $logger;

    public function __construct($database = null, $logger = null)
    {
        $this->database = $database ?: Database::getInstance();
        $this->logger = $logger ?: LoggingService::getInstance();
        $this->createFarmerTables();
    }

    /**
     * Create farmer management tables
     */
    private function createFarmerTables()
    {
        try {
            // Farmer profiles table
            $sql = "CREATE TABLE IF NOT EXISTS farmer_profiles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_number VARCHAR(50) NOT NULL UNIQUE,
                full_name VARCHAR(100) NOT NULL,
                father_name VARCHAR(100),
                spouse_name VARCHAR(100),
                date_of_birth DATE,
                gender ENUM('male','female','other') DEFAULT 'male',
                phone VARCHAR(15) NOT NULL,
                alternate_phone VARCHAR(15),
                email VARCHAR(100),
                address TEXT,
                village VARCHAR(100),
                post_office VARCHAR(100),
                tehsil VARCHAR(100),
                district VARCHAR(100),
                state VARCHAR(100),
                pin_code VARCHAR(10),
                aadhar_number VARCHAR(12),
                pan_number VARCHAR(10),
                bank_account_number VARCHAR(20),
                bank_name VARCHAR(100),
                ifsc_code VARCHAR(15),
                land_holdings_acres DECIMAL(10,2),
                irrigation_source VARCHAR(100),
                farming_type ENUM('traditional','organic','mixed') DEFAULT 'traditional',
                status ENUM('active','inactive','suspended') DEFAULT 'active',
                associate_id BIGINT(20) UNSIGNED,
                created_by BIGINT(20) UNSIGNED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->database->query($sql);

            // Land holdings table
            $sql = "CREATE TABLE IF NOT EXISTS farmer_land_holdings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_id INT NOT NULL,
                land_area DECIMAL(10,2) NOT NULL,
                land_area_unit VARCHAR(20) DEFAULT 'acres',
                survey_number VARCHAR(50),
                khasra_number VARCHAR(50),
                village VARCHAR(100),
                tehsil VARCHAR(100),
                district VARCHAR(100),
                state VARCHAR(100),
                land_type ENUM('agricultural','residential','commercial','barren') DEFAULT 'agricultural',
                soil_type VARCHAR(100),
                irrigation_available BOOLEAN DEFAULT FALSE,
                electricity_available BOOLEAN DEFAULT FALSE,
                road_access BOOLEAN DEFAULT FALSE,
                ownership_type ENUM('owned','leased','shared') DEFAULT 'owned',
                acquisition_date DATE,
                market_value DECIMAL(15,2),
                status ENUM('active','sold','under_acquisition') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE CASCADE
            )";
            $this->database->query($sql);

            // Farmer agreements table
            $sql = "CREATE TABLE IF NOT EXISTS farmer_agreements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                farmer_id INT NOT NULL,
                agreement_number VARCHAR(50) NOT NULL UNIQUE,
                agreement_type ENUM('land_acquisition','land_lease','joint_venture') DEFAULT 'land_acquisition',
                land_acquisition_id INT,
                total_land_area DECIMAL(10,2) NOT NULL,
                agreement_amount DECIMAL(15,2),
                payment_terms TEXT,
                payment_status ENUM('pending','partial','completed') DEFAULT 'pending',
                agreement_date DATE NOT NULL,
                expiry_date DATE,
                status ENUM('active','expired','terminated','completed') DEFAULT 'active',
                remarks TEXT,
                created_by BIGINT(20) UNSIGNED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE CASCADE,
                FOREIGN KEY (land_acquisition_id) REFERENCES land_acquisitions(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->database->query($sql);

        } catch (Exception $e) {
            $this->logger->log("Error creating farmer tables: " . $e->getMessage(), 'error', 'farmer');
            throw new RuntimeException("Failed to create farmer tables: " . $e->getMessage());
        }
    }

    /**
     * Add new farmer
     */
    public function addFarmer($data)
    {
        if (empty($data['farmer_number']) || empty($data['full_name']) || empty($data['phone'])) {
            throw new InvalidArgumentException("Missing required fields for farmer registration");
        }

        $sql = "INSERT INTO farmer_profiles (
            farmer_number, full_name, father_name, spouse_name, date_of_birth, gender,
            phone, alternate_phone, email, address, village, post_office, tehsil, district, state,
            pin_code, aadhar_number, pan_number, bank_account_number, bank_name, ifsc_code,
            land_holdings_acres, irrigation_source, farming_type, status, associate_id, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['farmer_number'],
            $data['full_name'],
            $data['father_name'] ?? null,
            $data['spouse_name'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? 'male',
            $data['phone'],
            $data['alternate_phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null,
            $data['village'] ?? null,
            $data['post_office'] ?? null,
            $data['tehsil'] ?? null,
            $data['district'] ?? null,
            $data['state'] ?? null,
            $data['pin_code'] ?? null,
            $data['aadhar_number'] ?? null,
            $data['pan_number'] ?? null,
            $data['bank_account_number'] ?? null,
            $data['bank_name'] ?? null,
            $data['ifsc_code'] ?? null,
            $data['land_holdings_acres'] ?? null,
            $data['irrigation_source'] ?? null,
            $data['farming_type'] ?? 'traditional',
            $data['status'] ?? 'active',
            $data['associate_id'] ?? null,
            $data['created_by'] ?? null
        ];

        try {
            $this->database->execute($sql, $params);
            $farmerId = $this->database->lastInsertId();
            $this->logger->log("Farmer added: {$data['full_name']} ({$data['farmer_number']})", 'info', 'farmer');
            return $farmerId;
        } catch (Exception $e) {
            $this->logger->log("Error adding farmer: " . $e->getMessage(), 'error', 'farmer');
            throw new RuntimeException("Failed to add farmer: " . $e->getMessage());
        }
    }

    /**
     * Add land holding for farmer
     */
    public function addLandHolding($farmerId, $landData)
    {
        if (empty($farmerId) || empty($landData['land_area'])) {
            throw new InvalidArgumentException("Farmer ID and land area are required");
        }

        $sql = "INSERT INTO farmer_land_holdings (
            farmer_id, land_area, land_area_unit, survey_number, khasra_number,
            village, tehsil, district, state, land_type, soil_type, irrigation_available,
            electricity_available, road_access, ownership_type, acquisition_date, market_value, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $farmerId,
            $landData['land_area'],
            $landData['land_area_unit'] ?? 'acres',
            $landData['survey_number'] ?? null,
            $landData['khasra_number'] ?? null,
            $landData['village'] ?? null,
            $landData['tehsil'] ?? null,
            $landData['district'] ?? null,
            $landData['state'] ?? null,
            $landData['land_type'] ?? 'agricultural',
            $landData['soil_type'] ?? null,
            $landData['irrigation_available'] ?? false,
            $landData['electricity_available'] ?? false,
            $landData['road_access'] ?? false,
            $landData['ownership_type'] ?? 'owned',
            $landData['acquisition_date'] ?? null,
            $landData['market_value'] ?? null,
            $landData['status'] ?? 'active'
        ];

        try {
            $this->database->execute($sql, $params);
            $landId = $this->database->lastInsertId();
            $this->logger->log("Land holding added for farmer ID: $farmerId", 'info', 'farmer');
            return $landId;
        } catch (Exception $e) {
            $this->logger->log("Error adding land holding: " . $e->getMessage(), 'error', 'farmer');
            throw new RuntimeException("Failed to add land holding: " . $e->getMessage());
        }
    }

    /**
     * Get farmers with filtering
     */
    public function getFarmers($filters = [], $limit = 50, $offset = 0)
    {
        $sql = "SELECT f.*, a.name as associate_name
                FROM farmer_profiles f
                LEFT JOIN associates a ON f.associate_id = a.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND f.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['village'])) {
            $sql .= " AND f.village = ?";
            $params[] = $filters['village'];
        }

        if (!empty($filters['tehsil'])) {
            $sql .= " AND f.tehsil = ?";
            $params[] = $filters['tehsil'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (f.full_name LIKE ? OR f.farmer_number LIKE ? OR f.phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY f.full_name";

        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        try {
            return $this->database->fetchAll($sql, $params);
        } catch (Exception $e) {
            $this->logger->log("Error fetching farmers: " . $e->getMessage(), 'error', 'farmer');
            return [];
        }
    }

    /**
     * Get farmer by ID
     */
    public function getFarmer($id)
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Farmer ID is required');
        }

        $sql = "SELECT f.*, a.name as associate_name
                FROM farmer_profiles f
                LEFT JOIN associates a ON f.associate_id = a.id
                WHERE f.id = ?";
        
        try {
            $farmer = $this->database->fetchOne($sql, [$id]);
            
            if ($farmer) {
                // Get land holdings
                $farmer['land_holdings'] = $this->getFarmerLandHoldings($id);
                // Get agreements
                $farmer['agreements'] = $this->getFarmerAgreements($id);
            }
            
            return $farmer;
        } catch (Exception $e) {
            $this->logger->log("Error fetching farmer $id: " . $e->getMessage(), 'error', 'farmer');
            return null;
        }
    }

    /**
     * Get farmer land holdings
     */
    private function getFarmerLandHoldings($farmerId)
    {
        $sql = "SELECT * FROM farmer_land_holdings WHERE farmer_id = ? AND status = 'active'";
        try {
            return $this->database->fetchAll($sql, [$farmerId]);
        } catch (Exception $e) {
            $this->logger->log("Error fetching land holdings for farmer $farmerId: " . $e->getMessage(), 'error', 'farmer');
            return [];
        }
    }

    /**
     * Get farmer agreements
     */
    private function getFarmerAgreements($farmerId)
    {
        $sql = "SELECT * FROM farmer_agreements WHERE farmer_id = ? ORDER BY agreement_date DESC";
        try {
            return $this->database->fetchAll($sql, [$farmerId]);
        } catch (Exception $e) {
            $this->logger->log("Error fetching agreements for farmer $farmerId: " . $e->getMessage(), 'error', 'farmer');
            return [];
        }
    }

    /**
     * Update farmer information
     */
    public function updateFarmer($id, $data)
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Farmer ID is required');
        }

        $sql = "UPDATE farmer_profiles SET 
                full_name = ?, father_name = ?, spouse_name = ?, date_of_birth = ?, gender = ?,
                phone = ?, alternate_phone = ?, email = ?, address = ?, village = ?, post_office = ?,
                tehsil = ?, district = ?, state = ?, pin_code = ?, aadhar_number = ?, pan_number = ?,
                bank_account_number = ?, bank_name = ?, ifsc_code = ?, land_holdings_acres = ?,
                irrigation_source = ?, farming_type = ?, status = ?, associate_id = ?
                WHERE id = ?";

        $params = [
            $data['full_name'] ?? null,
            $data['father_name'] ?? null,
            $data['spouse_name'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? 'male',
            $data['phone'] ?? null,
            $data['alternate_phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null,
            $data['village'] ?? null,
            $data['post_office'] ?? null,
            $data['tehsil'] ?? null,
            $data['district'] ?? null,
            $data['state'] ?? null,
            $data['pin_code'] ?? null,
            $data['aadhar_number'] ?? null,
            $data['pan_number'] ?? null,
            $data['bank_account_number'] ?? null,
            $data['bank_name'] ?? null,
            $data['ifsc_code'] ?? null,
            $data['land_holdings_acres'] ?? null,
            $data['irrigation_source'] ?? null,
            $data['farming_type'] ?? 'traditional',
            $data['status'] ?? 'active',
            $data['associate_id'] ?? null,
            $id
        ];

        try {
            $this->database->execute($sql, $params);
            $this->logger->log("Farmer updated: $id", 'info', 'farmer');
            return ['success' => true, 'message' => 'Farmer updated successfully'];
        } catch (Exception $e) {
            $this->logger->log("Error updating farmer $id: " . $e->getMessage(), 'error', 'farmer');
            return ['success' => false, 'message' => 'Failed to update farmer'];
        }
    }

    /**
     * Get farmer statistics
     */
    public function getFarmerStats()
    {
        $stats = [];

        try {
            // Total farmers
            $result = $this->database->fetchOne("SELECT COUNT(*) as total FROM farmer_profiles WHERE status = 'active'");
            $stats['total_farmers'] = $result['total'] ?? 0;

            // By state
            $results = $this->database->fetchAll("SELECT state, COUNT(*) as count FROM farmer_profiles WHERE status = 'active' GROUP BY state");
            $stats['by_state'] = [];
            foreach ($results as $row) {
                $stats['by_state'][$row['state']] = $row['count'];
            }

            // Total land holdings
            $result = $this->database->fetchOne("SELECT SUM(land_area) as total_land, COUNT(*) as total_holdings FROM farmer_land_holdings WHERE status = 'active'");
            $stats['total_land_holdings'] = $result['total_land'] ?? 0;
            $stats['total_holdings_count'] = $result['total_holdings'] ?? 0;

            // Recent registrations
            $result = $this->database->fetchOne("SELECT COUNT(*) as recent FROM farmer_profiles WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats['recent_registrations'] = $result['recent'] ?? 0;

        } catch (Exception $e) {
            $this->logger->log("Error fetching farmer stats: " . $e->getMessage(), 'error', 'farmer');
        }

        return $stats;
    }

    /**
     * Create farmer agreement
     */
    public function createAgreement($farmerId, $agreementData)
    {
        if (empty($farmerId) || empty($agreementData['agreement_number'])) {
            throw new InvalidArgumentException("Farmer ID and agreement number are required");
        }

        $sql = "INSERT INTO farmer_agreements (
            farmer_id, agreement_number, agreement_type, land_acquisition_id, total_land_area,
            agreement_amount, payment_terms, payment_status, agreement_date, expiry_date, status, remarks, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $farmerId,
            $agreementData['agreement_number'],
            $agreementData['agreement_type'] ?? 'land_acquisition',
            $agreementData['land_acquisition_id'] ?? null,
            $agreementData['total_land_area'],
            $agreementData['agreement_amount'] ?? null,
            $agreementData['payment_terms'] ?? null,
            $agreementData['payment_status'] ?? 'pending',
            $agreementData['agreement_date'],
            $agreementData['expiry_date'] ?? null,
            $agreementData['status'] ?? 'active',
            $agreementData['remarks'] ?? null,
            $agreementData['created_by'] ?? null
        ];

        try {
            $this->database->execute($sql, $params);
            $agreementId = $this->database->lastInsertId();
            $this->logger->log("Farmer agreement created: {$agreementData['agreement_number']} for farmer ID: $farmerId", 'info', 'farmer');
            return $agreementId;
        } catch (Exception $e) {
            $this->logger->log("Error creating farmer agreement: " . $e->getMessage(), 'error', 'farmer');
            throw new RuntimeException("Failed to create farmer agreement: " . $e->getMessage());
        }
    }
}
