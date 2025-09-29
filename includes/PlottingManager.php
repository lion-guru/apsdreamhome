<?php
/**
 * Plotting and Land Subdivision Management System
 * Complete system for colonizer companies to manage land acquisition,
 * plot subdivision, sales, and farmer relationships
 */

class PlottingManager {
    private $conn;
    private $logger;

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->createPlottingTables();
    }

    /**
     * Create all plotting related tables
     */
    private function createPlottingTables() {
        // Land acquisitions table
        $sql = "CREATE TABLE IF NOT EXISTS land_acquisitions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            acquisition_number VARCHAR(50) NOT NULL UNIQUE,
            farmer_id INT,
            land_area DECIMAL(10,2) NOT NULL,
            land_area_unit VARCHAR(20) DEFAULT 'sqft',
            location VARCHAR(255) NOT NULL,
            village VARCHAR(100),
            tehsil VARCHAR(100),
            district VARCHAR(100),
            state VARCHAR(100),
            acquisition_date DATE NOT NULL,
            acquisition_cost DECIMAL(15,2),
            payment_status ENUM('pending','partial','completed') DEFAULT 'pending',
            land_type ENUM('agricultural','residential','commercial','industrial') DEFAULT 'agricultural',
            soil_type VARCHAR(100),
            water_source VARCHAR(100),
            electricity_available BOOLEAN DEFAULT FALSE,
            road_access BOOLEAN DEFAULT FALSE,
            documents JSON,
            remarks TEXT,
            status ENUM('active','sold','under_development','inactive') DEFAULT 'active',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Farmers/Kisans table
        $sql = "CREATE TABLE IF NOT EXISTS farmers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            farmer_number VARCHAR(50) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL,
            father_name VARCHAR(100),
            phone VARCHAR(15),
            email VARCHAR(100),
            address TEXT,
            village VARCHAR(100),
            tehsil VARCHAR(100),
            district VARCHAR(100),
            state VARCHAR(100),
            aadhar_number VARCHAR(20),
            pan_number VARCHAR(20),
            bank_account_number VARCHAR(30),
            bank_name VARCHAR(100),
            ifsc_code VARCHAR(20),
            total_land_area DECIMAL(10,2) DEFAULT 0,
            total_acquisition_value DECIMAL(15,2) DEFAULT 0,
            payment_received DECIMAL(15,2) DEFAULT 0,
            pending_payment DECIMAL(15,2) DEFAULT 0,
            status ENUM('active','inactive','blacklisted') DEFAULT 'active',
            associate_id INT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Plots table
        $sql = "CREATE TABLE IF NOT EXISTS plots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            plot_number VARCHAR(50) NOT NULL,
            land_acquisition_id INT NOT NULL,
            plot_area DECIMAL(10,2) NOT NULL,
            plot_area_unit VARCHAR(20) DEFAULT 'sqft',
            plot_type ENUM('residential','commercial','industrial','mixed') DEFAULT 'residential',
            dimensions_length DECIMAL(8,2),
            dimensions_width DECIMAL(8,2),
            corner_plot BOOLEAN DEFAULT FALSE,
            park_facing BOOLEAN DEFAULT FALSE,
            road_facing BOOLEAN DEFAULT FALSE,
            plot_status ENUM('available','booked','sold','blocked','cancelled') DEFAULT 'available',
            base_price DECIMAL(15,2),
            current_price DECIMAL(15,2),
            development_cost DECIMAL(15,2) DEFAULT 0,
            maintenance_cost DECIMAL(15,2) DEFAULT 0,
            plot_features JSON,
            plot_restrictions JSON,
            coordinates JSON,
            sector_block VARCHAR(50),
            colony_name VARCHAR(100),
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (land_acquisition_id) REFERENCES land_acquisitions(id) ON DELETE CASCADE
        )";

        $this->conn->query($sql);

        // Plot bookings table
        $sql = "CREATE TABLE IF NOT EXISTS plot_bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            plot_id INT NOT NULL,
            customer_id INT,
            associate_id INT,
            booking_number VARCHAR(50) NOT NULL UNIQUE,
            booking_type ENUM('direct','associate','agent') DEFAULT 'direct',
            booking_amount DECIMAL(15,2) NOT NULL,
            total_amount DECIMAL(15,2),
            payment_plan ENUM('lump_sum','installment','custom') DEFAULT 'lump_sum',
            installment_period INT,
            installment_amount DECIMAL(15,2),
            payment_status ENUM('pending','partial','completed','cancelled') DEFAULT 'pending',
            payment_method VARCHAR(50),
            transaction_id VARCHAR(100),
            booking_date DATE NOT NULL,
            agreement_date DATE,
            possession_date DATE,
            cancellation_date DATE,
            cancellation_reason TEXT,
            commission_paid DECIMAL(15,2) DEFAULT 0,
            commission_percentage DECIMAL(5,2),
            associate_commission DECIMAL(15,2) DEFAULT 0,
            agent_commission DECIMAL(15,2) DEFAULT 0,
            remarks TEXT,
            status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
            FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Plot payments table
        $sql = "CREATE TABLE IF NOT EXISTS plot_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id INT NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            payment_date DATE NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            transaction_id VARCHAR(100),
            installment_number INT,
            payment_status ENUM('pending','completed','failed','refunded') DEFAULT 'completed',
            receipt_number VARCHAR(50),
            bank_reference VARCHAR(100),
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES plot_bookings(id) ON DELETE CASCADE
        )";

        $this->conn->query($sql);

        // Commission tracking table
        $sql = "CREATE TABLE IF NOT EXISTS commission_tracking (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id INT NOT NULL,
            associate_id INT,
            commission_type ENUM('direct','level','bonus','override') DEFAULT 'direct',
            commission_level INT DEFAULT 1,
            commission_amount DECIMAL(15,2) NOT NULL,
            commission_percentage DECIMAL(5,2),
            payment_status ENUM('pending','paid','cancelled') DEFAULT 'pending',
            payment_date DATE,
            transaction_id VARCHAR(100),
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES plot_bookings(id) ON DELETE CASCADE,
            FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Employee salary table
        $sql = "CREATE TABLE IF NOT EXISTS employee_salaries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            salary_amount DECIMAL(15,2) NOT NULL,
            salary_type ENUM('monthly','weekly','daily') DEFAULT 'monthly',
            basic_salary DECIMAL(15,2),
            allowances DECIMAL(15,2) DEFAULT 0,
            deductions DECIMAL(15,2) DEFAULT 0,
            effective_from DATE NOT NULL,
            effective_to DATE,
            payment_status ENUM('pending','paid','cancelled') DEFAULT 'pending',
            payment_date DATE,
            transaction_id VARCHAR(100),
            remarks TEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Insert sample data
        $this->insertSampleData();
    }

    /**
     * Insert sample data for testing
     */
    private function insertSampleData() {
        // Check if sample data already exists
        $checkSql = "SELECT COUNT(*) as count FROM land_acquisitions";
        $result = $this->conn->query($checkSql);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            // Insert sample farmer
            $farmerSql = "INSERT INTO farmers (farmer_number, full_name, phone, village, district, state, total_land_area, status)
                         VALUES ('F001', 'Rajesh Kumar', '9876543210', 'Sample Village', 'Sample District', 'Haryana', 10.5, 'active')";
            $this->conn->query($farmerSql);
            $farmerId = $this->conn->insert_id;

            // Insert sample land acquisition
            $landSql = "INSERT INTO land_acquisitions (acquisition_number, farmer_id, land_area, location, village, district, state, acquisition_date, acquisition_cost, payment_status, land_type, status)
                       VALUES ('LA001', $farmerId, 10.5, 'Sample Location', 'Sample Village', 'Sample District', 'Haryana', '2024-01-15', 5000000, 'completed', 'agricultural', 'active')";
            $this->conn->query($landSql);
            $landId = $this->conn->insert_id;

            // Insert sample plots
            for ($i = 1; $i <= 20; $i++) {
                $plotNumber = sprintf('A-%03d', $i);
                $plotArea = rand(1000, 2000) / 10; // 100-200 sq yards
                $price = $plotArea * 50000; // 50k per sq yard

                $plotSql = "INSERT INTO plots (plot_number, land_acquisition_id, plot_area, plot_type, base_price, current_price, colony_name)
                           VALUES ('$plotNumber', $landId, $plotArea, 'residential', $price, $price, 'APS Dream City')";
                $this->conn->query($plotSql);
            }
        }
    }

    /**
     * Add new land acquisition
     */
    public function addLandAcquisition($data) {
        $sql = "INSERT INTO land_acquisitions (
            acquisition_number, farmer_id, land_area, land_area_unit, location, village, tehsil, district, state,
            acquisition_date, acquisition_cost, payment_status, land_type, soil_type, water_source,
            electricity_available, road_access, documents, remarks, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sddsddddddsdsssddsss",
            $data['acquisition_number'],
            $data['farmer_id'],
            $data['land_area'],
            $data['land_area_unit'],
            $data['location'],
            $data['village'],
            $data['tehsil'],
            $data['district'],
            $data['state'],
            $data['acquisition_date'],
            $data['acquisition_cost'],
            $data['payment_status'],
            $data['land_type'],
            $data['soil_type'],
            $data['water_source'],
            $data['electricity_available'],
            $data['road_access'],
            json_encode($data['documents'] ?? []),
            $data['remarks'],
            $data['status'],
            $data['created_by']
        );

        $result = $stmt->execute();
        $acquisitionId = $stmt->insert_id;
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Land acquisition added: {$data['acquisition_number']}", 'info', 'plotting');
        }

        return $result ? $acquisitionId : false;
    }

    /**
     * Add farmer/kisan
     */
    public function addFarmer($data) {
        $sql = "INSERT INTO farmers (
            farmer_number, full_name, father_name, phone, email, address, village, tehsil, district, state,
            aadhar_number, pan_number, bank_account_number, bank_name, ifsc_code, status, associate_id, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssis",
            $data['farmer_number'],
            $data['full_name'],
            $data['father_name'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['village'],
            $data['tehsil'],
            $data['district'],
            $data['state'],
            $data['aadhar_number'],
            $data['pan_number'],
            $data['bank_account_number'],
            $data['bank_name'],
            $data['ifsc_code'],
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
     * Create plots from land acquisition
     */
    public function createPlots($landAcquisitionId, $plotsData) {
        $createdPlots = [];

        foreach ($plotsData as $plotData) {
            $sql = "INSERT INTO plots (
                plot_number, land_acquisition_id, plot_area, plot_area_unit, plot_type,
                dimensions_length, dimensions_width, corner_plot, park_facing, road_facing,
                plot_status, base_price, current_price, development_cost, maintenance_cost,
                plot_features, plot_restrictions, sector_block, colony_name, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sddssdssdddddsssss",
                $plotData['plot_number'],
                $landAcquisitionId,
                $plotData['plot_area'],
                $plotData['plot_area_unit'],
                $plotData['plot_type'],
                $plotData['dimensions_length'],
                $plotData['dimensions_width'],
                $plotData['corner_plot'],
                $plotData['park_facing'],
                $plotData['road_facing'],
                $plotData['plot_status'],
                $plotData['base_price'],
                $plotData['current_price'],
                $plotData['development_cost'],
                $plotData['maintenance_cost'],
                json_encode($plotData['plot_features'] ?? []),
                json_encode($plotData['plot_restrictions'] ?? []),
                $plotData['sector_block'],
                $plotData['colony_name'],
                $plotData['remarks']
            );

            $result = $stmt->execute();
            if ($result) {
                $createdPlots[] = $this->conn->insert_id;
            }
            $stmt->close();
        }

        if (!empty($createdPlots) && $this->logger) {
            $this->logger->log("Created " . count($createdPlots) . " plots for acquisition ID: $landAcquisitionId", 'info', 'plotting');
        }

        return $createdPlots;
    }

    /**
     * Get all plots with filtering
     */
    public function getPlots($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT p.*, la.acquisition_number, la.location as land_location,
                       f.full_name as farmer_name
                FROM plots p
                LEFT JOIN land_acquisitions la ON p.land_acquisition_id = la.id
                LEFT JOIN farmers f ON la.farmer_id = f.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['plot_status'])) {
            $sql .= " AND p.plot_status = ?";
            $params[] = $filters['plot_status'];
            $types .= "s";
        }

        if (!empty($filters['colony_name'])) {
            $sql .= " AND p.colony_name LIKE ?";
            $params[] = "%" . $filters['colony_name'] . "%";
            $types .= "s";
        }

        if (!empty($filters['sector_block'])) {
            $sql .= " AND p.sector_block = ?";
            $params[] = $filters['sector_block'];
            $types .= "s";
        }

        if (!empty($filters['plot_type'])) {
            $sql .= " AND p.plot_type = ?";
            $params[] = $filters['plot_type'];
            $types .= "s";
        }

        $sql .= " ORDER BY p.colony_name, p.sector_block, p.plot_number";

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

        $plots = [];
        while ($row = $result->fetch_assoc()) {
            $plots[] = $row;
        }
        $stmt->close();

        return $plots;
    }

    /**
     * Book a plot
     */
    public function bookPlot($data) {
        $sql = "INSERT INTO plot_bookings (
            plot_id, customer_id, associate_id, booking_number, booking_type,
            booking_amount, total_amount, payment_plan, installment_period, installment_amount,
            payment_status, payment_method, transaction_id, booking_date, commission_percentage,
            associate_commission, agent_commission, remarks, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiissdsdssdssssddsss",
            $data['plot_id'],
            $data['customer_id'],
            $data['associate_id'],
            $data['booking_number'],
            $data['booking_type'],
            $data['booking_amount'],
            $data['total_amount'],
            $data['payment_plan'],
            $data['installment_period'],
            $data['installment_amount'],
            $data['payment_status'],
            $data['payment_method'],
            $data['transaction_id'],
            $data['booking_date'],
            $data['commission_percentage'],
            $data['associate_commission'],
            $data['agent_commission'],
            $data['remarks'],
            $data['status'],
            $data['created_by']
        );

        $result = $stmt->execute();
        $bookingId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Update plot status
            $this->updatePlotStatus($data['plot_id'], 'booked');

            // Calculate and create commission records
            $this->calculateCommissions($bookingId, $data);

            if ($this->logger) {
                $this->logger->log("Plot booked: {$data['booking_number']}", 'info', 'booking');
            }
        }

        return $result ? $bookingId : false;
    }

    /**
     * Calculate and create commission records
     */
    private function calculateCommissions($bookingId, $bookingData) {
        // Get booking details
        $bookingSql = "SELECT pb.*, p.current_price, p.plot_number, la.farmer_id
                      FROM plot_bookings pb
                      JOIN plots p ON pb.plot_id = p.id
                      JOIN land_acquisitions la ON p.land_acquisition_id = la.id
                      WHERE pb.id = ?";
        $stmt = $this->conn->prepare($bookingSql);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$booking) return;

        $totalAmount = $booking['total_amount'] ?? $booking['booking_amount'];

        // Direct commission to associate (if any)
        if ($booking['associate_id'] && $booking['associate_commission'] > 0) {
            $commissionSql = "INSERT INTO commission_tracking (
                booking_id, associate_id, commission_type, commission_level,
                commission_amount, commission_percentage, payment_status, remarks
            ) VALUES (?, ?, 'direct', 1, ?, ?, 'pending', 'Direct commission for plot booking')";

            $stmt = $this->conn->prepare($commissionSql);
            $stmt->bind_param("iddd",
                $bookingId,
                $booking['associate_id'],
                $booking['associate_commission'],
                $booking['commission_percentage']
            );
            $stmt->execute();
            $stmt->close();
        }

        // MLM level commissions
        if ($booking['associate_id']) {
            $this->calculateMLMCommissions($bookingId, $booking['associate_id'], $totalAmount);
        }

        // Farmer commission (percentage of total amount)
        if ($booking['farmer_id']) {
            $farmerCommission = $totalAmount * 0.02; // 2% to farmer
            $farmerCommissionSql = "INSERT INTO commission_tracking (
                booking_id, associate_id, commission_type, commission_level,
                commission_amount, commission_percentage, payment_status, remarks
            ) VALUES (?, ?, 'farmer', 1, ?, ?, 'pending', 'Farmer commission for land sale')";

            $stmt = $this->conn->prepare($farmerCommissionSql);
            $farmerPercentage = 2.0;
            $stmt->bind_param("iddd",
                $bookingId,
                $booking['farmer_id'],
                $farmerCommission,
                $farmerPercentage
            );
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * Calculate MLM commissions for upline associates
     */
    private function calculateMLMCommissions($bookingId, $associateId, $totalAmount) {
        // Get associate hierarchy
        $hierarchy = $this->getAssociateHierarchy($associateId);
        $level = 1;

        foreach ($hierarchy as $uplineId => $uplineInfo) {
            if ($level > 5) break; // Max 5 levels

            $commissionPercentage = $this->getCommissionPercentage($level);
            $commissionAmount = $totalAmount * ($commissionPercentage / 100);

            if ($commissionAmount > 0) {
                $commissionSql = "INSERT INTO commission_tracking (
                    booking_id, associate_id, commission_type, commission_level,
                    commission_amount, commission_percentage, payment_status, remarks
                ) VALUES (?, ?, 'level', ?, ?, ?, 'pending', 'MLM level commission')";

                $stmt = $this->conn->prepare($commissionSql);
                $stmt->bind_param("iiidd",
                    $bookingId,
                    $uplineId,
                    $level,
                    $commissionAmount,
                    $commissionPercentage
                );
                $stmt->execute();
                $stmt->close();
            }

            $level++;
        }
    }

    /**
     * Get commission percentage for level
     */
    private function getCommissionPercentage($level) {
        $percentages = [
            1 => 10, // Direct - 10%
            2 => 5,  // Level 2 - 5%
            3 => 3,  // Level 3 - 3%
            4 => 2,  // Level 4 - 2%
            5 => 1   // Level 5 - 1%
        ];

        return $percentages[$level] ?? 0;
    }

    /**
     * Get associate hierarchy
     */
    private function getAssociateHierarchy($associateId, $maxLevels = 5) {
        $hierarchy = [];
        $currentId = $associateId;
        $level = 0;

        while ($currentId && $level < $maxLevels) {
            $sql = "SELECT sponsor_id, full_name FROM associates WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $currentId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) break;

            $associate = $result->fetch_assoc();
            $stmt->close();

            if ($associate['sponsor_id']) {
                $hierarchy[$associate['sponsor_id']] = [
                    'name' => $associate['full_name'],
                    'level' => $level + 1
                ];
                $currentId = $associate['sponsor_id'];
                $level++;
            } else {
                break;
            }
        }

        return $hierarchy;
    }

    /**
     * Update plot status
     */
    private function updatePlotStatus($plotId, $status) {
        $sql = "UPDATE plots SET plot_status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $plotId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Get plot booking details
     */
    public function getPlotBooking($bookingId) {
        $sql = "SELECT pb.*, p.plot_number, p.colony_name, p.sector_block,
                       p.plot_area, p.current_price, u.full_name as customer_name,
                       u.phone as customer_phone, u.email as customer_email,
                       a.name as associate_name
                FROM plot_bookings pb
                JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN users u ON pb.customer_id = u.id
                LEFT JOIN associates a ON pb.associate_id = a.id
                WHERE pb.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        $stmt->close();

        if ($booking) {
            // Get commission details
            $booking['commissions'] = $this->getBookingCommissions($bookingId);
            // Get payment details
            $booking['payments'] = $this->getBookingPayments($bookingId);
        }

        return $booking;
    }

    /**
     * Get booking commissions
     */
    private function getBookingCommissions($bookingId) {
        $sql = "SELECT ct.*, u.full_name as associate_name
                FROM commission_tracking ct
                LEFT JOIN associates a ON ct.associate_id = a.id
                LEFT JOIN users u ON a.user_id = u.id
                WHERE ct.booking_id = ?
                ORDER BY ct.commission_level, ct.commission_amount DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();

        $commissions = [];
        while ($row = $result->fetch_assoc()) {
            $commissions[] = $row;
        }
        $stmt->close();

        return $commissions;
    }

    /**
     * Get booking payments
     */
    private function getBookingPayments($bookingId) {
        $sql = "SELECT * FROM plot_payments WHERE booking_id = ? ORDER BY payment_date ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();

        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        $stmt->close();

        return $payments;
    }

    /**
     * Add payment to booking
     */
    public function addBookingPayment($bookingId, $paymentData) {
        $sql = "INSERT INTO plot_payments (
            booking_id, amount, payment_date, payment_method, transaction_id,
            installment_number, payment_status, receipt_number, bank_reference, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("idsssissss",
            $bookingId,
            $paymentData['amount'],
            $paymentData['payment_date'],
            $paymentData['payment_method'],
            $paymentData['transaction_id'],
            $paymentData['installment_number'],
            $paymentData['payment_status'],
            $paymentData['receipt_number'],
            $paymentData['bank_reference'],
            $paymentData['remarks']
        );

        $result = $stmt->execute();
        $paymentId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Update booking payment status
            $this->updateBookingPaymentStatus($bookingId);

            if ($this->logger) {
                $this->logger->log("Payment added to booking ID: $bookingId, Amount: {$paymentData['amount']}", 'info', 'payment');
            }
        }

        return $result ? $paymentId : false;
    }

    /**
     * Update booking payment status
     */
    private function updateBookingPaymentStatus($bookingId) {
        $sql = "SELECT SUM(amount) as total_paid FROM plot_payments WHERE booking_id = ? AND payment_status = 'completed'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalPaid = $result->fetch_assoc()['total_paid'] ?? 0;
        $stmt->close();

        $bookingSql = "SELECT total_amount FROM plot_bookings WHERE id = ?";
        $stmt = $this->conn->prepare($bookingSql);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalAmount = $result->fetch_assoc()['total_amount'] ?? 0;
        $stmt->close();

        $paymentStatus = 'pending';
        if ($totalPaid >= $totalAmount) {
            $paymentStatus = 'completed';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }

        $updateSql = "UPDATE plot_bookings SET payment_status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($updateSql);
        $stmt->bind_param("si", $paymentStatus, $bookingId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Get dashboard statistics for plotting
     */
    public function getPlottingStats() {
        $stats = [];

        // Total land acquired
        $sql = "SELECT COUNT(*) as total_acquisitions, SUM(land_area) as total_area,
                       SUM(acquisition_cost) as total_cost FROM land_acquisitions WHERE status = 'active'";
        $result = $this->conn->query($sql);
        $stats['land_acquired'] = $result->fetch_assoc();

        // Total plots created
        $sql = "SELECT COUNT(*) as total_plots,
                       SUM(CASE WHEN plot_status = 'available' THEN 1 ELSE 0 END) as available_plots,
                       SUM(CASE WHEN plot_status = 'sold' THEN 1 ELSE 0 END) as sold_plots,
                       SUM(current_price) as total_value FROM plots";
        $result = $this->conn->query($sql);
        $stats['plots'] = $result->fetch_assoc();

        // Total bookings
        $sql = "SELECT COUNT(*) as total_bookings,
                       SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                       SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                       SUM(total_amount) as total_booking_value FROM plot_bookings";
        $result = $this->conn->query($sql);
        $stats['bookings'] = $result->fetch_assoc();

        // Total farmers
        $sql = "SELECT COUNT(*) as total_farmers FROM farmers WHERE status = 'active'";
        $result = $this->conn->query($sql);
        $stats['farmers'] = $result->fetch_assoc();

        // Monthly sales
        $sql = "SELECT MONTH(booking_date) as month, YEAR(booking_date) as year,
                       COUNT(*) as bookings, SUM(total_amount) as value
                FROM plot_bookings WHERE status IN ('confirmed', 'completed')
                AND booking_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY YEAR(booking_date), MONTH(booking_date)
                ORDER BY year DESC, month DESC";
        $result = $this->conn->query($sql);
        $stats['monthly_sales'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['monthly_sales'][] = $row;
        }

        return $stats;
    }
}
?>
