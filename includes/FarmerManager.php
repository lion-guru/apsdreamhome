<?php
/**
 * Farmer/Kisan Management System
 * Complete management system for farmers and agricultural land relationships
 */

class FarmerManager {
    private $conn;
    private $logger;

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->createFarmerTables();
    }

    /**
     * Create farmer management tables
     */
    private function createFarmerTables() {
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
            associate_id INT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

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
        )";

        $this->conn->query($sql);

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
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (land_acquisition_id) REFERENCES land_acquisitions(id) ON DELETE SET NULL,
            FOREIGN KEY (commission_id) REFERENCES commission_tracking(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

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
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

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
            assigned_to INT,
            resolution TEXT,
            resolution_date DATE,
            satisfaction_rating INT DEFAULT 0, -- 1-5 scale
            feedback TEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Insert sample farmer data
        $this->insertSampleFarmerData();
    }

    /**
     * Insert sample farmer data
     */
    private function insertSampleFarmerData() {
        $checkSql = "SELECT COUNT(*) as count FROM farmer_profiles";
        $result = $this->conn->query($checkSql);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
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
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssssdddssddds",
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
                );
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    /**
     * Add new farmer
     */
    public function addFarmer($data) {
        $sql = "INSERT INTO farmer_profiles (
            farmer_number, full_name, father_name, spouse_name, date_of_birth, gender, phone, alternate_phone, email,
            address, village, post_office, tehsil, district, state, pincode, aadhar_number, pan_number, voter_id,
            bank_account_number, bank_name, ifsc_code, account_holder_name, total_land_holding, cultivated_area,
            irrigated_area, non_irrigated_area, crop_types, farming_experience, education_level, family_members,
            family_income, credit_score, credit_limit, outstanding_loans, status, associate_id, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssssdddsdssddsddddsi",
            $data['farmer_number'],
            $data['full_name'],
            $data['father_name'],
            $data['spouse_name'],
            $data['date_of_birth'],
            $data['gender'],
            $data['phone'],
            $data['alternate_phone'],
            $data['email'],
            $data['address'],
            $data['village'],
            $data['post_office'],
            $data['tehsil'],
            $data['district'],
            $data['state'],
            $data['pincode'],
            $data['aadhar_number'],
            $data['pan_number'],
            $data['voter_id'],
            $data['bank_account_number'],
            $data['bank_name'],
            $data['ifsc_code'],
            $data['account_holder_name'],
            $data['total_land_holding'],
            $data['cultivated_area'],
            $data['irrigated_area'],
            $data['non_irrigated_area'],
            json_encode($data['crop_types'] ?? []),
            $data['farming_experience'],
            $data['education_level'],
            $data['family_members'],
            $data['family_income'],
            $data['credit_score'],
            $data['credit_limit'],
            $data['outstanding_loans'],
            $data['status'],
            $data['associate_id'],
            $data['created_by']
        );

        $result = $stmt->execute();
        $farmerId = $stmt->insert_id;
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Farmer added: {$data['full_name']} ({$data['farmer_number']})", 'info', 'farmer');
        }

        return $result ? $farmerId : false;
    }

    /**
     * Get all farmers with filtering
     */
    public function getFarmers($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT fp.*, a.name as associate_name
                FROM farmer_profiles fp
                LEFT JOIN associates a ON fp.associate_id = a.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['status'])) {
            $sql .= " AND fp.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        if (!empty($filters['district'])) {
            $sql .= " AND fp.district LIKE ?";
            $params[] = "%" . $filters['district'] . "%";
            $types .= "s";
        }

        if (!empty($filters['village'])) {
            $sql .= " AND fp.village LIKE ?";
            $params[] = "%" . $filters['village'] . "%";
            $types .= "s";
        }

        if (!empty($filters['search'])) {
            $search = "%" . $filters['search'] . "%";
            $sql .= " AND (fp.full_name LIKE ? OR fp.farmer_number LIKE ? OR fp.phone LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }

        $sql .= " ORDER BY fp.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
        }

        if ($offset > 0) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $farmers = [];
        while ($row = $result->fetch_assoc()) {
            $row['crop_types'] = json_decode($row['crop_types'] ?? '[]', true);
            $farmers[] = $row;
        }
        $stmt->close();

        return $farmers;
    }

    /**
     * Get farmer by ID
     */
    public function getFarmer($id) {
        $sql = "SELECT fp.*, a.name as associate_name, u.full_name as created_by_name
                FROM farmer_profiles fp
                LEFT JOIN associates a ON fp.associate_id = a.id
                LEFT JOIN users u ON fp.created_by = u.id
                WHERE fp.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $farmer = $result->fetch_assoc();
        $stmt->close();

        if ($farmer) {
            $farmer['crop_types'] = json_decode($farmer['crop_types'] ?? '[]', true);
            $farmer['land_holdings'] = $this->getFarmerLandHoldings($id);
            $farmer['transactions'] = $this->getFarmerTransactions($id);
            $farmer['loans'] = $this->getFarmerLoans($id);
        }

        return $farmer;
    }

    /**
     * Get farmer land holdings
     */
    private function getFarmerLandHoldings($farmerId) {
        $sql = "SELECT * FROM farmer_land_holdings WHERE farmer_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $farmerId);
        $stmt->execute();
        $result = $stmt->get_result();

        $holdings = [];
        while ($row = $result->fetch_assoc()) {
            $holdings[] = $row;
        }
        $stmt->close();

        return $holdings;
    }

    /**
     * Get farmer transactions
     */
    private function getFarmerTransactions($farmerId) {
        $sql = "SELECT ft.*, la.acquisition_number
                FROM farmer_transactions ft
                LEFT JOIN land_acquisitions la ON ft.land_acquisition_id = la.id
                WHERE ft.farmer_id = ? ORDER BY ft.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $farmerId);
        $stmt->execute();
        $result = $stmt->get_result();

        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        $stmt->close();

        return $transactions;
    }

    /**
     * Get farmer loans
     */
    private function getFarmerLoans($farmerId) {
        $sql = "SELECT * FROM farmer_loans WHERE farmer_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $farmerId);
        $stmt->execute();
        $result = $stmt->get_result();

        $loans = [];
        while ($row = $result->fetch_assoc()) {
            $row['repayment_schedule'] = json_decode($row['repayment_schedule'] ?? '[]', true);
            $loans[] = $row;
        }
        $stmt->close();

        return $loans;
    }

    /**
     * Add land holding to farmer
     */
    public function addLandHolding($farmerId, $data) {
        $sql = "INSERT INTO farmer_land_holdings (
            farmer_id, khasra_number, land_area, land_area_unit, land_type, soil_type,
            irrigation_source, water_source, electricity_available, road_access, location,
            village, tehsil, district, state, land_value, current_status, ownership_document,
            mutation_document, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isdssssssissssdsdss",
            $farmerId,
            $data['khasra_number'],
            $data['land_area'],
            $data['land_area_unit'],
            $data['land_type'],
            $data['soil_type'],
            $data['irrigation_source'],
            $data['water_source'],
            $data['electricity_available'],
            $data['road_access'],
            $data['location'],
            $data['village'],
            $data['tehsil'],
            $data['district'],
            $data['state'],
            $data['land_value'],
            $data['current_status'],
            $data['ownership_document'],
            $data['mutation_document'],
            $data['remarks']
        );

        $result = $stmt->execute();
        $holdingId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Update farmer's total land holding
            $this->updateFarmerTotalLand($farmerId);

            if ($this->logger) {
                $this->logger->log("Land holding added for farmer ID: $farmerId", 'info', 'farmer');
            }
        }

        return $result ? $holdingId : false;
    }

    /**
     * Update farmer's total land holding
     */
    private function updateFarmerTotalLand($farmerId) {
        $sql = "UPDATE farmer_profiles SET total_land_holding = (
            SELECT SUM(land_area) FROM farmer_land_holdings WHERE farmer_id = ?
        ) WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $farmerId, $farmerId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Record farmer transaction
     */
    public function recordTransaction($data) {
        $sql = "INSERT INTO farmer_transactions (
            farmer_id, transaction_type, transaction_number, amount, transaction_date,
            payment_method, bank_reference, transaction_id, description, land_acquisition_id,
            commission_id, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssdsssssiss",
            $data['farmer_id'],
            $data['transaction_type'],
            $data['transaction_number'],
            $data['amount'],
            $data['transaction_date'],
            $data['payment_method'],
            $data['bank_reference'],
            $data['transaction_id'],
            $data['description'],
            $data['land_acquisition_id'],
            $data['commission_id'],
            $data['status'],
            $data['created_by']
        );

        $result = $stmt->execute();
        $transactionId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Update farmer's payment records
            $this->updateFarmerPaymentHistory($data['farmer_id'], $data);

            if ($this->logger) {
                $this->logger->log("Transaction recorded: {$data['transaction_number']}", 'info', 'farmer');
            }
        }

        return $result ? $transactionId : false;
    }

    /**
     * Update farmer payment history
     */
    private function updateFarmerPaymentHistory($farmerId, $transaction) {
        // Get current payment history
        $sql = "SELECT payment_history FROM farmer_profiles WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $farmerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $farmer = $result->fetch_assoc();
        $stmt->close();

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
        $stmt = $this->conn->prepare($updateSql);
        $historyJson = json_encode($paymentHistory);
        $stmt->bind_param("si", $historyJson, $farmerId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Create support request for farmer
     */
    public function createSupportRequest($data) {
        $sql = "INSERT INTO farmer_support_requests (
            farmer_id, request_number, request_type, priority, subject, description,
            status, assigned_to, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssisii",
            $data['farmer_id'],
            $data['request_number'],
            $data['request_type'],
            $data['priority'],
            $data['subject'],
            $data['description'],
            $data['status'],
            $data['assigned_to'],
            $data['created_by']
        );

        $result = $stmt->execute();
        $requestId = $stmt->insert_id;
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Support request created: {$data['request_number']}", 'info', 'farmer');
        }

        return $result ? $requestId : false;
    }

    /**
     * Get farmer dashboard data
     */
    public function getFarmerDashboard($farmerId) {
        $dashboard = [];

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
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $farmerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['land_summary'] = $result->fetch_assoc();
        $stmt->close();

        // Transaction summary
        $sql = "SELECT
            SUM(CASE WHEN transaction_type = 'payment' AND status = 'completed' THEN amount ELSE 0 END) as total_received,
            SUM(CASE WHEN transaction_type = 'loan' AND status = 'active' THEN amount ELSE 0 END) as total_loans,
            SUM(CASE WHEN transaction_type = 'commission' AND status = 'completed' THEN amount ELSE 0 END) as total_commissions
            FROM farmer_transactions WHERE farmer_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $farmerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['transaction_summary'] = $result->fetch_assoc();
        $stmt->close();

        // Recent transactions
        $sql = "SELECT * FROM farmer_transactions WHERE farmer_id = ?
                ORDER BY created_at DESC LIMIT 5";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $farmerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['recent_transactions'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['recent_transactions'][] = $row;
        }
        $stmt->close();

        // Active loans
        $sql = "SELECT * FROM farmer_loans WHERE farmer_id = ? AND status IN ('active', 'disbursed')
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $farmerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['active_loans'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['repayment_schedule'] = json_decode($row['repayment_schedule'] ?? '[]', true);
            $dashboard['active_loans'][] = $row;
        }
        $stmt->close();

        return $dashboard;
    }

    /**
     * Get farmer statistics
     */
    public function getFarmerStats() {
        $stats = [];

        // Total farmers
        $sql = "SELECT COUNT(*) as total_farmers FROM farmer_profiles WHERE status = 'active'";
        $result = $this->conn->query($sql);
        $stats['total_farmers'] = $result->fetch_assoc()['total_farmers'];

        // Total land holdings
        $sql = "SELECT SUM(total_land_holding) as total_land FROM farmer_profiles WHERE status = 'active'";
        $result = $this->conn->query($sql);
        $stats['total_land'] = $result->fetch_assoc()['total_land'];

        // Land under acquisition
        $sql = "SELECT SUM(land_area) as acquisition_land FROM farmer_land_holdings WHERE acquisition_status = 'under_negotiation'";
        $result = $this->conn->query($sql);
        $stats['acquisition_land'] = $result->fetch_assoc()['acquisition_land'];

        // Total transactions this month
        $sql = "SELECT COUNT(*) as monthly_transactions, SUM(amount) as monthly_amount
                FROM farmer_transactions
                WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        $result = $this->conn->query($sql);
        $stats['monthly_transactions'] = $result->fetch_assoc();

        // Farmers by district
        $sql = "SELECT district, COUNT(*) as count FROM farmer_profiles
                WHERE status = 'active' GROUP BY district ORDER BY count DESC LIMIT 5";
        $result = $this->conn->query($sql);
        $stats['farmers_by_district'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['farmers_by_district'][] = $row;
        }

        return $stats;
    }
}
?>
