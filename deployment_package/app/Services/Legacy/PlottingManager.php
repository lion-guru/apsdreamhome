<?php

namespace App\Services\Legacy;

/**
 * Plotting and Land Subdivision Management System
 * Complete system for colonizer companies to manage land acquisition,
 * plot subdivision, sales, and farmer relationships
 */

use App\Core\Database;

class PlottingManager
{
    private $db;
    private $logger;

    public function __construct($db = null, $logger = null)
    {
        $this->db = $db ?: \App\Core\App::database();
        $this->logger = $logger;
        $this->createPlottingTables();
    }

    /**
     * Create all plotting related tables
     */
    private function createPlottingTables()
    {
        try {
            // Farmers/Kisans table - Unified with farmer_profiles
            // Note: We don't create 'farmers' table anymore, we use 'farmer_profiles'

            // Land acquisitions table - Updated to reference farmer_profiles
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
                created_by BIGINT(20) UNSIGNED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->db->query($sql);

            // Check if plots table exists and handle legacy
            $sql = "SHOW TABLES LIKE 'plots'";
            $result = $this->db->query($sql);
            if ($this->db->fetch($sql)) {
                $sql_col = "SHOW COLUMNS FROM plots LIKE 'land_acquisition_id'";
                if (!$this->db->fetch($sql_col)) {
                    $this->db->query("ALTER TABLE plots ADD COLUMN land_acquisition_id INT AFTER id");
                    $this->db->query("ALTER TABLE plots ADD FOREIGN KEY (land_acquisition_id) REFERENCES land_acquisitions(id) ON DELETE SET NULL");
                }
            }

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
                current_price DECIMAL(15,2),
                base_price DECIMAL(15,2),
                plc_amount DECIMAL(15,2) DEFAULT 0,
                other_charges DECIMAL(15,2) DEFAULT 0,
                total_price DECIMAL(15,2),
                remarks TEXT,
                created_by BIGINT(20) UNSIGNED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (land_acquisition_id) REFERENCES land_acquisitions(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->db->query($sql);

            // Plot bookings table
            $sql = "CREATE TABLE IF NOT EXISTS plot_bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                plot_id INT NOT NULL,
                customer_id BIGINT(20) UNSIGNED,
                associate_id BIGINT(20) UNSIGNED,
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
                created_by BIGINT(20) UNSIGNED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
                FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->db->query($sql);

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
            $this->db->query($sql);

            // Commission tracking table
            $sql = "CREATE TABLE IF NOT EXISTS commission_tracking (
                id INT AUTO_INCREMENT PRIMARY KEY,
                booking_id INT NOT NULL,
                associate_id BIGINT(20) UNSIGNED,
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
            $this->db->query($sql);

            // Employee salary table
            $sql = "CREATE TABLE IF NOT EXISTS employee_salaries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                employee_id BIGINT(20) UNSIGNED NOT NULL,
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
                created_by BIGINT(20) UNSIGNED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->db->query($sql);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error creating plotting tables: " . $e->getMessage(), 'error', 'plotting');
            }
        }
    }

    /**
     * Add land acquisition
     */
    public function addLandAcquisition($data)
    {
        $sql = "INSERT INTO land_acquisitions (
            acquisition_number, farmer_id, land_area, land_area_unit, location, village, tehsil, district, state,
            acquisition_date, acquisition_cost, payment_status, land_type, soil_type, water_source,
            electricity_available, road_access, documents, remarks, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
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
        ];

        try {
            $this->db->execute($sql, $params);
            $acquisitionId = $this->db->lastInsertId();

            if ($this->logger) {
                $this->logger->log("Land acquisition added: {$data['acquisition_number']}", 'info', 'plotting');
            }
            return $acquisitionId;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error adding land acquisition: " . $e->getMessage(), 'error', 'plotting');
            }
            return false;
        }
    }

    /**
     * Add farmer/kisan
     */
    public function addFarmer($data)
    {
        $sql = "INSERT INTO farmer_profiles (
            farmer_number, full_name, father_name, phone, email, address, village, tehsil, district, state,
            aadhar_number, pan_number, bank_account_number, bank_name, ifsc_code, status, associate_id, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
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
        ];

        try {
            $this->db->execute($sql, $params);
            $farmerId = $this->db->lastInsertId();

            if ($this->logger) {
                $this->logger->log("Farmer added: {$data['full_name']} ({$data['farmer_number']})", 'info', 'farmer');
            }
            return $farmerId;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error adding farmer: " . $e->getMessage(), 'error', 'farmer');
            }
            return false;
        }
    }

    /**
     * Create plots from land acquisition
     */
    public function createPlots($landAcquisitionId, $plotsData)
    {
        $createdPlots = [];

        foreach ($plotsData as $plotData) {
            $sql = "INSERT INTO plots (
                plot_number, land_acquisition_id, plot_area, plot_area_unit, plot_type,
                dimensions_length, dimensions_width, corner_plot, park_facing, road_facing,
                plot_status, base_price, current_price, development_cost, maintenance_cost,
                plot_features, plot_restrictions, sector_block, colony_name, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
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
            ];

            try {
                $this->db->execute($sql, $params);
                $createdPlots[] = $this->db->lastInsertId();
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->log("Error creating plot {$plotData['plot_number']}: " . $e->getMessage(), 'error', 'plotting');
                }
            }
        }

        if (!empty($createdPlots) && $this->logger) {
            $this->logger->log("Created " . count($createdPlots) . " plots for acquisition ID: $landAcquisitionId", 'info', 'plotting');
        }

        return $createdPlots;
    }

    /**
     * Get all plots with filtering
     */
    public function getPlots($filters = [], $limit = 50, $offset = 0)
    {
        $sql = "SELECT p.*, la.acquisition_number, la.location as land_location,
                       f.full_name as farmer_name
                FROM plots p
                LEFT JOIN land_acquisitions la ON p.land_acquisition_id = la.id
                LEFT JOIN farmer_profiles f ON la.farmer_id = f.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['plot_status'])) {
            $sql .= " AND p.plot_status = ?";
            $params[] = $filters['plot_status'];
        }

        if (!empty($filters['colony_name'])) {
            $sql .= " AND p.colony_name LIKE ?";
            $params[] = "%" . $filters['colony_name'] . "%";
        }

        if (!empty($filters['sector_block'])) {
            $sql .= " AND p.sector_block = ?";
            $params[] = $filters['sector_block'];
        }

        if (!empty($filters['plot_type'])) {
            $sql .= " AND p.plot_type = ?";
            $params[] = $filters['plot_type'];
        }

        $sql .= " ORDER BY p.colony_name, p.sector_block, p.plot_number";

        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        try {
            return $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error fetching plots: " . $e->getMessage(), 'error', 'plotting');
            }
            return [];
        }
    }

    /**
     * Book a plot
     */
    public function bookPlot($data)
    {
        $sql = "INSERT INTO plot_bookings (
            plot_id, customer_id, associate_id, booking_number, booking_type,
            booking_amount, total_amount, payment_plan, installment_period, installment_amount,
            payment_status, payment_method, transaction_id, booking_date, commission_percentage,
            associate_commission, agent_commission, remarks, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
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
        ];

        try {
            $this->db->execute($sql, $params);
            $bookingId = $this->db->lastInsertId();

            // Update plot status
            $this->updatePlotStatus($data['plot_id'], 'booked');

            // Calculate and create commission records
            $this->calculateCommissions($bookingId, $data);

            if ($this->logger) {
                $this->logger->log("Plot booked: {$data['booking_number']} (ID: $bookingId)", 'info', 'plotting');
            }

            return $bookingId;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error booking plot: " . $e->getMessage(), 'error', 'plotting');
            }
            return false;
        }
    }

    /**
     * Calculate and create commission records
     */
    private function calculateCommissions($bookingId, $bookingData)
    {
        // Get booking details
        $bookingSql = "SELECT pb.*, p.current_price, p.plot_number, la.farmer_id
                      FROM plot_bookings pb
                      JOIN plots p ON pb.plot_id = p.id
                      JOIN land_acquisitions la ON p.land_acquisition_id = la.id
                      WHERE pb.id = ?";

        try {
            $booking = $this->db->fetch($bookingSql, [$bookingId]);

            if (!$booking) return;

            $totalAmount = $booking['total_amount'] ?? $booking['booking_amount'];

            // Direct commission to associate (if any)
            if ($booking['associate_id'] && $booking['associate_commission'] > 0) {
                $commissionSql = "INSERT INTO commission_tracking (
                    booking_id, associate_id, commission_type, commission_level,
                    commission_amount, commission_percentage, payment_status, remarks
                ) VALUES (?, ?, 'direct', 1, ?, ?, 'pending', 'Direct commission for plot booking')";

                $this->db->execute($commissionSql, [
                    $bookingId,
                    $booking['associate_id'],
                    $booking['associate_commission'],
                    $booking['commission_percentage']
                ]);
            }

            // MLM level commissions - Unified with Associate model
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

                $this->db->execute($farmerCommissionSql, [
                    $bookingId,
                    $booking['farmer_id'],
                    $farmerCommission,
                    2.0
                ]);
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error calculating commissions for booking $bookingId: " . $e->getMessage(), 'error', 'plotting');
            }
        }
    }

    /**
     * Calculate MLM commissions for upline associates
     */
    private function calculateMLMCommissions($bookingId, $associateId, $totalAmount)
    {
        // Get user_id for this associate
        $sql = "SELECT user_id FROM associates WHERE id = ?";
        try {
            $associate = $this->db->fetch($sql, [$associateId]);

            if (!$associate) return;

            // Use Associate model to process 7-level commissions
            $db = \App\Core\App::database();
            $associateModel = new \Associate($db);

            // This will process all 7 levels of commissions
            $associateModel->processSaleCommissions($associate['user_id'], $totalAmount, $bookingId);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error calculating MLM commissions for booking $bookingId: " . $e->getMessage(), 'error', 'plotting');
            }
        }
    }

    /**
     * Update plot status
     */
    private function updatePlotStatus($plotId, $status)
    {
        $sql = "UPDATE plots SET plot_status = ?, updated_at = NOW() WHERE id = ?";
        try {
            $this->db->execute($sql, [$status, $plotId]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error updating plot status: " . $e->getMessage(), 'error', 'plotting');
            }
        }
    }

    /**
     * Get plot booking details
     */
    public function getPlotBooking($bookingId)
    {
        $sql = "SELECT pb.*, p.plot_number, p.colony_name, p.sector_block,
                       p.plot_area, p.current_price, u.full_name as customer_name,
                       u.phone as customer_phone, u.email as customer_email,
                       a.name as associate_name
                FROM plot_bookings pb
                JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN user u ON pb.customer_id = u.uid
                LEFT JOIN associates a ON pb.associate_id = a.id
                WHERE pb.id = ?";

        try {
            $booking = $this->db->fetch($sql, [$bookingId]);

            if ($booking) {
                // Get commission details
                $booking['commissions'] = $this->getBookingCommissions($bookingId);
                // Get payment details
                $booking['payments'] = $this->getBookingPayments($bookingId);
            }

            return $booking;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error fetching plot booking $bookingId: " . $e->getMessage(), 'error', 'plotting');
            }
            return null;
        }
    }

    /**
     * Get booking commissions
     */
    private function getBookingCommissions($bookingId)
    {
        $sql = "SELECT ct.*, u.name as associate_name
                FROM commission_tracking ct
                LEFT JOIN associates a ON ct.associate_id = a.id
                LEFT JOIN users u ON a.user_id = u.id
                WHERE ct.booking_id = ?
                ORDER BY ct.commission_level, ct.commission_amount DESC";

        try {
            return $this->db->fetchAll($sql, [$bookingId]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error fetching commissions for booking $bookingId: " . $e->getMessage(), 'error', 'plotting');
            }
            return [];
        }
    }

    /**
     * Get booking payments
     */
    private function getBookingPayments($bookingId)
    {
        $sql = "SELECT * FROM plot_payments WHERE booking_id = ? ORDER BY payment_date ASC";
        try {
            return $this->db->fetchAll($sql, [$bookingId]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error fetching payments for booking $bookingId: " . $e->getMessage(), 'error', 'plotting');
            }
            return [];
        }
    }

    /**
     * Add payment to booking
     */
    public function addBookingPayment($bookingId, $paymentData)
    {
        $sql = "INSERT INTO plot_payments (
            booking_id, amount, payment_date, payment_method, transaction_id,
            installment_number, payment_status, receipt_number, bank_reference, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
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
        ];

        try {
            $this->db->execute($sql, $params);
            $paymentId = $this->db->lastInsertId();

            // Update booking payment status
            $this->updateBookingPaymentStatus($bookingId);

            if ($this->logger) {
                $this->logger->log("Payment added to booking ID: $bookingId, Amount: {$paymentData['amount']}", 'info', 'payment');
            }

            return $paymentId;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error adding payment to booking $bookingId: " . $e->getMessage(), 'error', 'payment');
            }
            return false;
        }
    }

    /**
     * Update booking payment status
     */
    private function updateBookingPaymentStatus($bookingId)
    {
        $sql = "SELECT SUM(amount) as total_paid FROM plot_payments WHERE booking_id = ? AND payment_status = 'completed'";
        try {
            $row = $this->db->fetch($sql, [$bookingId]);
            $totalPaid = $row['total_paid'] ?? 0;

            $bookingSql = "SELECT total_amount FROM plot_bookings WHERE id = ?";
            $row = $this->db->fetch($bookingSql, [$bookingId]);
            $totalAmount = $row['total_amount'] ?? 0;

            $paymentStatus = 'pending';
            if ($totalPaid >= $totalAmount) {
                $paymentStatus = 'completed';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'partial';
            }

            $updateSql = "UPDATE plot_bookings SET payment_status = ? WHERE id = ?";
            $this->db->execute($updateSql, [$paymentStatus, $bookingId]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error updating payment status for booking $bookingId: " . $e->getMessage(), 'error', 'plotting');
            }
        }
    }

    /**
     * Get dashboard statistics for plotting
     */
    public function getPlottingStats()
    {
        $stats = [];

        try {
            // Total land acquired
            $sql = "SELECT COUNT(*) as total_acquisitions, SUM(land_area) as total_area,
                           SUM(acquisition_cost) as total_cost FROM land_acquisitions WHERE status = 'active'";
            $stats['land_acquired'] = $this->db->fetch($sql);

            // Total plots created
            $sql = "SELECT COUNT(*) as total_plots,
                           SUM(CASE WHEN plot_status = 'available' THEN 1 ELSE 0 END) as available_plots,
                           SUM(CASE WHEN plot_status = 'sold' THEN 1 ELSE 0 END) as sold_plots,
                           SUM(current_price) as total_value FROM plots";
            $stats['plots'] = $this->db->fetch($sql);

            // Total bookings
            $sql = "SELECT COUNT(*) as total_bookings,
                           SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                           SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                           SUM(total_amount) as total_booking_value FROM plot_bookings";
            $stats['bookings'] = $this->db->fetch($sql);

            // Total farmers
            $sql = "SELECT COUNT(*) as total_farmers FROM farmer_profiles WHERE status = 'active'";
            $stats['farmers'] = $this->db->fetch($sql);

            // Monthly sales
            $sql = "SELECT MONTH(booking_date) as month, YEAR(booking_date) as year,
                           COUNT(*) as bookings, SUM(total_amount) as value
                    FROM plot_bookings WHERE status IN ('confirmed', 'completed')
                    AND booking_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY YEAR(booking_date), MONTH(booking_date)
                    ORDER BY year DESC, month DESC";
            $stats['monthly_sales'] = $this->db->fetchAll($sql);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error fetching plotting statistics: " . $e->getMessage(), 'error', 'plotting');
            }
        }

        return $stats;
    }
}
