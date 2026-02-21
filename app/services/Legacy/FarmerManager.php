<?php

namespace App\Services\Legacy;

/**
 * Farmer/Kisan Management System
 * Complete management system for farmers and agricultural land relationships
 */

class FarmerManager
{
    private $db;
    private $logger;

    public function __construct($db = null, $logger = null)
    {
        $this->db = $db ?: \App\Core\App::database();
        $this->logger = $logger;
        $this->createFarmerTables();
    }

    /**
     * Create farmer management tables
     */
    private function createFarmerTables()
    {
        $logError = function ($message, $error) {
            $errorMsg = "Error: $message - " . $error;
            if (PHP_SAPI === 'cli') {
                echo $errorMsg . "\n";
            } else {
                error_log($errorMsg);
            }
        };

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
            pincode VARCHAR(10),
            aadhar_number VARCHAR(20),
            pan_number VARCHAR(20),
            voter_id VARCHAR(20),
            bank_account_number VARCHAR(30),
            bank_name VARCHAR(100),
            ifsc_code VARCHAR(20),
            account_holder_name VARCHAR(100),
            total_land_holding DECIMAL(10,2) DEFAULT 0,
            cultivated_area DECIMAL(10,2) DEFAULT 0,
            irrigated_area DECIMAL(10,2) DEFAULT 0,
            non_irrigated_area DECIMAL(10,2) DEFAULT 0,
            crop_types JSON,
            farming_experience INT DEFAULT 0,
            education_level VARCHAR(50),
            family_members INT DEFAULT 0,
            family_income DECIMAL(15,2),
            credit_score ENUM('excellent','good','fair','poor') DEFAULT 'fair',
            credit_limit DECIMAL(15,2) DEFAULT 50000,
            outstanding_loans DECIMAL(15,2) DEFAULT 0,
            payment_history JSON,
            status ENUM('active','inactive','blacklisted','under_review') DEFAULT 'active',
            associate_id BIGINT(20) UNSIGNED,
            created_by BIGINT(20) UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);

        // Land holdings table
        $sql = "CREATE TABLE IF NOT EXISTS farmer_land_holdings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            farmer_id INT NOT NULL,
            khasra_number VARCHAR(50),
            land_area DECIMAL(10,2) NOT NULL,
            land_area_unit VARCHAR(20) DEFAULT 'sqft',
            land_type ENUM('agricultural','residential','commercial','mixed') DEFAULT 'agricultural',
            soil_type VARCHAR(100),
            irrigation_source VARCHAR(100),
            water_source VARCHAR(100),
            electricity_available BOOLEAN DEFAULT FALSE,
            road_access BOOLEAN DEFAULT FALSE,
            location VARCHAR(255),
            village VARCHAR(100),
            tehsil VARCHAR(100),
            district VARCHAR(100),
            state VARCHAR(100),
            land_value DECIMAL(15,2),
            current_status ENUM('cultivated','fallow','sold','under_acquisition','disputed') DEFAULT 'cultivated',
            ownership_document VARCHAR(255),
            mutation_document VARCHAR(255),
            acquisition_status ENUM('not_acquired','under_negotiation','acquired','rejected') DEFAULT 'not_acquired',
            acquisition_date DATE,
            acquisition_amount DECIMAL(15,2),
            payment_status ENUM('pending','partial','completed') DEFAULT 'pending',
            payment_received DECIMAL(15,2) DEFAULT 0,
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);

        // Farmer transactions table
        $sql = "CREATE TABLE IF NOT EXISTS farmer_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            farmer_id INT NOT NULL,
            transaction_type ENUM('land_acquisition','payment','loan','commission','refund','penalty') NOT NULL,
            transaction_number VARCHAR(50) NOT NULL UNIQUE,
            amount DECIMAL(15,2) NOT NULL,
            transaction_date DATE NOT NULL,
            payment_method ENUM('cash','cheque','bank_transfer','online') DEFAULT 'cash',
            bank_reference VARCHAR(100),
            transaction_id VARCHAR(100),
            description TEXT,
            land_acquisition_id INT,
            commission_id INT,
            status ENUM('pending','completed','failed','cancelled') DEFAULT 'completed',
            created_by BIGINT(20) UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (land_acquisition_id) REFERENCES land_acquisitions(id) ON DELETE SET NULL,
            FOREIGN KEY (commission_id) REFERENCES commission_tracking(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);

        // Farmer loans table
        $sql = "CREATE TABLE IF NOT EXISTS farmer_loans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            farmer_id INT NOT NULL,
            loan_number VARCHAR(50) NOT NULL UNIQUE,
            loan_amount DECIMAL(15,2) NOT NULL,
            interest_rate DECIMAL(5,2) NOT NULL,
            loan_tenure INT NOT NULL, -- in months
            emi_amount DECIMAL(15,2),
            purpose VARCHAR(255),
            sanction_date DATE NOT NULL,
            disbursement_date DATE,
            maturity_date DATE,
            outstanding_amount DECIMAL(15,2),
            status ENUM('applied','sanctioned','disbursed','active','closed','defaulted') DEFAULT 'applied',
            collateral_type ENUM('land','gold','property','none') DEFAULT 'none',
            collateral_value DECIMAL(15,2),
            guarantor_name VARCHAR(100),
            guarantor_phone VARCHAR(15),
            repayment_schedule JSON,
            created_by BIGINT(20) UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);

        // Farmer support requests table
        $sql = "CREATE TABLE IF NOT EXISTS farmer_support_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            farmer_id INT NOT NULL,
            request_number VARCHAR(50) NOT NULL UNIQUE,
            request_type ENUM('technical','financial','legal','infrastructure','other') NOT NULL,
            priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
            subject VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            status ENUM('open','in_progress','resolved','closed','rejected') DEFAULT 'open',
            assigned_to BIGINT(20) UNSIGNED,
            resolution TEXT,
            resolution_date DATE,
            satisfaction_rating INT DEFAULT 0, -- 1-5 scale
            feedback TEXT,
            created_by BIGINT(20) UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);

        // Insert sample farmer data
        $this->insertSampleFarmerData();
    }

    /**
     * Insert sample farmer data
     */
    private function insertSampleFarmerData()
    {
        $checkSql = "SELECT COUNT(*) as count FROM farmer_profiles";
        $row = $this->db->fetch($checkSql);

        if ($row && $row['count'] == 0) {
            $sampleFarmers = [
                [
                    'farmer_number' => 'F001',
                    'full_name' => 'Rajesh Kumar',
                    'father_name' => 'Suresh Kumar',
                    'phone' => '9876543210',
                    'village' => 'Sample Village',
                    'district' => 'Sample District',
                    'state' => 'Haryana',
                    'total_land_holding' => 15.5,
                    'cultivated_area' => 12.0,
                    'irrigated_area' => 8.0,
                    'crop_types' => '["wheat", "rice", "sugarcane"]',
                    'farming_experience' => 25,
                    'education_level' => 'Graduate',
                    'family_members' => 5,
                    'family_income' => 350000,
                    'status' => 'active'
                ],
                [
                    'farmer_number' => 'F002',
                    'full_name' => 'Sunita Devi',
                    'father_name' => 'Ram Kumar',
                    'phone' => '9876543211',
                    'village' => 'Sample Village',
                    'district' => 'Sample District',
                    'state' => 'Haryana',
                    'total_land_holding' => 8.5,
                    'cultivated_area' => 7.0,
                    'irrigated_area' => 5.0,
                    'crop_types' => '["vegetables", "pulses"]',
                    'farming_experience' => 15,
                    'education_level' => 'High School',
                    'family_members' => 4,
                    'family_income' => 200000,
                    'status' => 'active'
                ]
            ];

            foreach ($sampleFarmers as $farmer) {
                $sql = "INSERT INTO farmer_profiles (
                    farmer_number, full_name, father_name, phone, village, district, state,
                    total_land_holding, cultivated_area, irrigated_area, crop_types,
                    farming_experience, education_level, family_members, family_income, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                try {
                    $params = [
                        $farmer['farmer_number'],
                        $farmer['full_name'],
                        $farmer['father_name'],
                        $farmer['phone'],
                        $farmer['village'],
                        $farmer['district'],
                        $farmer['state'],
                        $farmer['total_land_holding'],
                        $farmer['cultivated_area'],
                        $farmer['irrigated_area'],
                        $farmer['crop_types'],
                        $farmer['farming_experience'],
                        $farmer['education_level'],
                        $farmer['family_members'],
                        $farmer['family_income'],
                        $farmer['status']
                    ];
                    $this->db->execute($sql, $params, "sssssssdddssddds");
                } catch (Exception $e) {
                    if ($this->logger) {
                        $this->logger->log("Error inserting sample farmer: " . $e->getMessage(), 'error', 'farmer');
                    }
                }
            }
        }
    }

    public function addFarmer($data)
    {
        $sql = "INSERT INTO farmer_profiles (
            farmer_number, full_name, father_name, phone, alternate_phone,
            email, address, village, district, state, pincode,
            aadhar_number, pan_number, bank_account_number, bank_name,
            ifsc_code, total_land_holding, associate_id, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['farmer_number'],
            $data['full_name'],
            $data['father_name'],
            $data['phone'],
            $data['alternate_phone'] ?? null,
            $data['email'] ?? null,
            $data['address'],
            $data['village'],
            $data['district'],
            $data['state'],
            $data['pincode'],
            $data['aadhar_number'],
            $data['pan_number'] ?? null,
            $data['bank_account_number'],
            $data['bank_name'],
            $data['ifsc_code'],
            $data['total_land_holding'] ?? 0,
            $data['associate_id'] ?? null,
            $data['created_by']
        ];

        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    public function getAllFarmers($status = 'active')
    {
        $sql = "SELECT fp.*, ua.name as associate_name
                FROM farmer_profiles fp
                LEFT JOIN associates a ON fp.associate_id = a.id
                LEFT JOIN users ua ON a.user_id = ua.id
                WHERE fp.status = ?
                ORDER BY fp.created_at DESC";

        return $this->db->fetchAll($sql, [$status]);
    }

    public function updateFarmer($id, $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }

        if (empty($fields)) return false;

        $sql = "UPDATE farmer_profiles SET " . implode(", ", $fields) . " WHERE id = ?";
        $params[] = $id;

        $this->db->execute($sql, $params);
        return true;
    }

    public function getFarmer($id)
    {
        $sql = "SELECT fp.*, ua.name as associate_name, u.name as created_by_name
                FROM farmer_profiles fp
                LEFT JOIN associates a ON fp.associate_id = a.id
                LEFT JOIN users ua ON a.user_id = ua.id
                LEFT JOIN users u ON fp.created_by = u.id
                WHERE fp.id = ?";

        return $this->db->fetch($sql, [$id]);
    }

    public function getFarmerLandHoldings($farmerId)
    {
        $sql = "SELECT * FROM farmer_land_holdings WHERE farmer_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$farmerId]);
    }

    public function getFarmerTransactions($farmerId)
    {
        $sql = "SELECT * FROM farmer_transactions WHERE farmer_id = ? ORDER BY transaction_date DESC";
        return $this->db->fetchAll($sql, [$farmerId]);
    }

    public function getFarmerLoans($farmerId)
    {
        $sql = "SELECT * FROM farmer_loans WHERE farmer_id = ? ORDER BY sanction_date DESC";
        return $this->db->fetchAll($sql, [$farmerId]);
    }

    public function getFarmerSupportRequests($farmerId)
    {
        $sql = "SELECT * FROM farmer_support_requests WHERE farmer_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$farmerId]);
    }

    public function addLandHolding($data)
    {
        $sql = "INSERT INTO farmer_land_holdings (
            farmer_id, khasra_number, land_area, land_area_unit,
            land_type, location, village, district, state,
            land_value, acquisition_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['farmer_id'],
            $data['khasra_number'],
            $data['land_area'],
            $data['land_area_unit'],
            $data['land_type'],
            $data['location'],
            $data['village'],
            $data['district'],
            $data['state'],
            $data['land_value'],
            $data['acquisition_status'] ?? 'not_acquired'
        ];

        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    public function updateAcquisitionStatus($holdingId, $status, $amount = null)
    {
        if ($amount !== null) {
            $sql = "UPDATE farmer_land_holdings SET acquisition_status = ?, acquisition_amount = ? WHERE id = ?";
            $this->db->execute($sql, [$status, $amount, $holdingId]);
        } else {
            $sql = "UPDATE farmer_land_holdings SET acquisition_status = ? WHERE id = ?";
            $this->db->execute($sql, [$status, $holdingId]);
        }
        return true;
    }

    /**
     * Update farmer's total land holding
     */
    private function updateFarmerTotalLand($farmerId)
    {
        $sql = "UPDATE farmer_profiles SET total_land_holding = (
            SELECT SUM(land_area) FROM farmer_land_holdings WHERE farmer_id = ?
        ) WHERE id = ?";
        try {
            $this->db->execute($sql, [$farmerId, $farmerId], "ii");
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error updating farmer total land: " . $e->getMessage(), 'error', 'farmer');
            }
        }
    }

    public function addTransaction($data)
    {
        $sql = "INSERT INTO farmer_transactions (
            farmer_id, transaction_type, transaction_number,
            amount, transaction_date, payment_method,
            description, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['farmer_id'],
            $data['transaction_type'],
            $data['transaction_number'],
            $data['amount'],
            $data['transaction_date'],
            $data['payment_method'],
            $data['description'] ?? null,
            $data['created_by']
        ];

        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Update farmer payment history
     */
    private function updateFarmerPaymentHistory($farmerId, $transaction)
    {
        // Get current payment history
        $sql = "SELECT payment_history FROM farmer_profiles WHERE id = ?";
        try {
            $farmer = $this->db->fetch($sql, [$farmerId]);

            if (!$farmer) return;

            $paymentHistory = json_decode($farmer['payment_history'] ?? '[]', true);
            $paymentHistory[] = [
                'transaction_id' => $transaction['transaction_number'],
                'amount' => $transaction['amount'],
                'type' => $transaction['transaction_type'],
                'date' => $transaction['transaction_date'],
                'status' => $transaction['status']
            ];

            // Keep only last 10 transactions
            $paymentHistory = array_slice($paymentHistory, -10);

            $updateSql = "UPDATE farmer_profiles SET payment_history = ? WHERE id = ?";
            $historyJson = json_encode($paymentHistory);
            $this->db->execute($updateSql, [$historyJson, $farmerId]);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error updating farmer payment history: " . $e->getMessage(), 'error', 'farmer');
            }
        }
    }

    /**
     * Create support request for farmer
     */
    public function createSupportRequest($data)
    {
        $sql = "INSERT INTO farmer_support_requests (
            farmer_id, request_number, request_type, priority, subject, description,
            status, assigned_to, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $params = [
                $data['farmer_id'],
                $data['request_number'],
                $data['request_type'],
                $data['priority'],
                $data['subject'],
                $data['description'],
                $data['status'],
                $data['assigned_to'],
                $data['created_by']
            ];

            $this->db->execute($sql, $params);
            $requestId = $this->db->lastInsertId();

            if ($this->logger) {
                $this->logger->log("Support request created: {$data['request_number']}", 'info', 'farmer');
            }

            return $requestId;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error creating support request: " . $e->getMessage(), 'error', 'farmer');
            }
            return false;
        }
    }

    /**
     * Get farmer dashboard data
     */
    public function getFarmerDashboard($farmerId)
    {
        $dashboard = [];

        try {
            // Farmer basic info
            $farmer = $this->getFarmer($farmerId);
            $dashboard['farmer_info'] = $farmer;

            // Land holding summary
            $sql = "SELECT
                SUM(land_area) as total_area,
                SUM(CASE WHEN current_status = 'cultivated' THEN land_area ELSE 0 END) as cultivated_area,
                SUM(CASE WHEN current_status = 'under_acquisition' THEN land_area ELSE 0 END) as under_acquisition,
                SUM(CASE WHEN acquisition_status = 'acquired' THEN land_area ELSE 0 END) as acquired_area
                FROM farmer_land_holdings WHERE farmer_id = ?";
            $dashboard['land_summary'] = $this->db->fetch($sql, [$farmerId]);

            // Transaction summary
            $sql = "SELECT
                SUM(CASE WHEN transaction_type = 'payment' AND status = 'completed' THEN amount ELSE 0 END) as total_received,
                SUM(CASE WHEN transaction_type = 'loan' AND status = 'active' THEN amount ELSE 0 END) as total_loans,
                SUM(CASE WHEN transaction_type = 'commission' AND status = 'completed' THEN amount ELSE 0 END) as total_commissions
                FROM farmer_transactions WHERE farmer_id = ?";
            $dashboard['transaction_summary'] = $this->db->fetch($sql, [$farmerId]);

            // Recent transactions
            $sql = "SELECT * FROM farmer_transactions WHERE farmer_id = ?
                    ORDER BY created_at DESC LIMIT 5";
            $dashboard['recent_transactions'] = $this->db->fetchAll($sql, [$farmerId]);

            // Active loans
            $sql = "SELECT * FROM farmer_loans WHERE farmer_id = ? AND status IN ('active', 'disbursed')
                    ORDER BY created_at DESC";
            $activeLoans = $this->db->fetchAll($sql, [$farmerId]);
            foreach ($activeLoans as &$row) {
                $row['repayment_schedule'] = json_decode($row['repayment_schedule'] ?? '[]', true);
            }
            $dashboard['active_loans'] = $activeLoans;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error getting farmer dashboard: " . $e->getMessage(), 'error', 'farmer');
            }
        }

        return $dashboard;
    }

    public function getFarmerStats()
    {
        $stats = [
            'total_farmers' => 0,
            'active_farmers' => 0,
            'total_land_area' => 0,
            'acquired_land_area' => 0,
            'total_payments' => 0
        ];

        // Total farmers
        $row = $this->db->fetch("SELECT COUNT(*) as count FROM farmer_profiles");
        $stats['total_farmers'] = $row['count'] ?? 0;

        // Active farmers
        $row = $this->db->fetch("SELECT COUNT(*) as count FROM farmer_profiles WHERE status = 'active'");
        $stats['active_farmers'] = $row['count'] ?? 0;

        // Land area stats
        $row = $this->db->fetch("SELECT SUM(land_area) as total,
                                      SUM(CASE WHEN acquisition_status = 'acquired' THEN land_area ELSE 0 END) as acquired
                                      FROM farmer_land_holdings");
        $stats['total_land_area'] = $row['total'] ?? 0;
        $stats['acquired_land_area'] = $row['acquired'] ?? 0;

        // Payment stats
        $row = $this->db->fetch("SELECT SUM(amount) as total FROM farmer_transactions WHERE transaction_type = 'payment'");
        $stats['total_payments'] = $row['total'] ?? 0;

        return $stats;
    }
}
