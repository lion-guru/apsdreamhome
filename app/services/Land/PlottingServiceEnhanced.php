<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use App\Services\LoggingService;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Plotting and Land Subdivision Management Service - APS Dream Home
 * Complete system for colonizer companies to manage land acquisition,
 * plot subdivision, sales, and farmer relationships
 * Custom MVC implementation without Laravel dependencies
 */
class PlottingServiceEnhanced
{
    private $database;
    private $logger;

    public function __construct($database = null, $logger = null)
    {
        $this->database = $database ?: Database::getInstance();
        $this->logger = $logger ?: new LoggingService();
        $this->createPlottingTables();
    }

    /**
     * Create all plotting related tables
     */
    private function createPlottingTables()
    {
        try {
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
                created_by BIGINT(20) UNSIGNED,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->database->query($sql);

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
            $this->database->query($sql);

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
            $this->database->query($sql);

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
            $this->database->query($sql);

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
            $this->database->query($sql);

        } catch (Exception $e) {
            $this->logger->log("Error creating plotting tables: " . $e->getMessage(), 'error', 'plotting');
            throw new RuntimeException("Failed to create plotting tables: " . $e->getMessage());
        }
    }

    /**
     * Add land acquisition
     */
    public function addLandAcquisition($data)
    {
        if (empty($data['acquisition_number']) || empty($data['land_area']) || empty($data['location'])) {
            throw new InvalidArgumentException("Missing required fields for land acquisition");
        }

        $sql = "INSERT INTO land_acquisitions (
            acquisition_number, farmer_id, land_area, land_area_unit, location, village, tehsil, district, state,
            acquisition_date, acquisition_cost, payment_status, land_type, soil_type, water_source,
            electricity_available, road_access, documents, remarks, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['acquisition_number'],
            $data['farmer_id'] ?? null,
            $data['land_area'],
            $data['land_area_unit'] ?? 'sqft',
            $data['location'],
            $data['village'] ?? null,
            $data['tehsil'] ?? null,
            $data['district'] ?? null,
            $data['state'] ?? null,
            $data['acquisition_date'],
            $data['acquisition_cost'] ?? null,
            $data['payment_status'] ?? 'pending',
            $data['land_type'] ?? 'agricultural',
            $data['soil_type'] ?? null,
            $data['water_source'] ?? null,
            $data['electricity_available'] ?? false,
            $data['road_access'] ?? false,
            json_encode($data['documents'] ?? []),
            $data['remarks'] ?? null,
            $data['status'] ?? 'active',
            $data['created_by'] ?? null
        ];

        try {
            $this->database->execute($sql, $params);
            $acquisitionId = $this->database->lastInsertId();
            $this->logger->log("Land acquisition added: {$data['acquisition_number']}", 'info', 'plotting');
            return $acquisitionId;
        } catch (Exception $e) {
            $this->logger->log("Error adding land acquisition: " . $e->getMessage(), 'error', 'plotting');
            throw new RuntimeException("Failed to add land acquisition: " . $e->getMessage());
        }
    }

    /**
     * Create plots from land acquisition
     */
    public function createPlots($landAcquisitionId, $plotsData)
    {
        if (empty($landAcquisitionId) || empty($plotsData)) {
            throw new InvalidArgumentException("Land acquisition ID and plots data are required");
        }

        $createdPlots = [];

        foreach ($plotsData as $plotData) {
            if (empty($plotData['plot_number']) || empty($plotData['plot_area'])) {
                continue;
            }

            $sql = "INSERT INTO plots (
                plot_number, land_acquisition_id, plot_area, plot_area_unit, plot_type,
                dimensions_length, dimensions_width, corner_plot, park_facing, road_facing,
                plot_status, base_price, current_price, plc_amount, other_charges, total_price, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $plotData['plot_number'],
                $landAcquisitionId,
                $plotData['plot_area'],
                $plotData['plot_area_unit'] ?? 'sqft',
                $plotData['plot_type'] ?? 'residential',
                $plotData['dimensions_length'] ?? null,
                $plotData['dimensions_width'] ?? null,
                $plotData['corner_plot'] ?? false,
                $plotData['park_facing'] ?? false,
                $plotData['road_facing'] ?? false,
                $plotData['plot_status'] ?? 'available',
                $plotData['base_price'] ?? 0,
                $plotData['current_price'] ?? $plotData['base_price'] ?? 0,
                $plotData['plc_amount'] ?? 0,
                $plotData['other_charges'] ?? 0,
                $plotData['total_price'] ?? ($plotData['current_price'] ?? 0),
                $plotData['remarks'] ?? null
            ];

            try {
                $this->database->execute($sql, $params);
                $createdPlots[] = $this->database->lastInsertId();
            } catch (Exception $e) {
                $this->logger->log("Error creating plot {$plotData['plot_number']}: " . $e->getMessage(), 'error', 'plotting');
            }
        }

        if (!empty($createdPlots)) {
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

        if (!empty($filters['plot_type'])) {
            $sql .= " AND p.plot_type = ?";
            $params[] = $filters['plot_type'];
        }

        $sql .= " ORDER BY p.plot_number";

        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        try {
            return $this->database->fetchAll($sql, $params);
        } catch (Exception $e) {
            $this->logger->log("Error fetching plots: " . $e->getMessage(), 'error', 'plotting');
            return [];
        }
    }

    /**
     * Book a plot
     */
    public function bookPlot($data)
    {
        if (empty($data['plot_id']) || empty($data['booking_number']) || empty($data['booking_amount'])) {
            throw new InvalidArgumentException("Missing required fields for plot booking");
        }

        $sql = "INSERT INTO plot_bookings (
            plot_id, customer_id, associate_id, booking_number, booking_type,
            booking_amount, total_amount, payment_plan, installment_period, installment_amount,
            payment_status, payment_method, transaction_id, booking_date, commission_percentage,
            associate_commission, agent_commission, remarks, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['plot_id'],
            $data['customer_id'] ?? null,
            $data['associate_id'] ?? null,
            $data['booking_number'],
            $data['booking_type'] ?? 'direct',
            $data['booking_amount'],
            $data['total_amount'] ?? $data['booking_amount'],
            $data['payment_plan'] ?? 'lump_sum',
            $data['installment_period'] ?? null,
            $data['installment_amount'] ?? null,
            $data['payment_status'] ?? 'pending',
            $data['payment_method'] ?? null,
            $data['transaction_id'] ?? null,
            $data['booking_date'],
            $data['commission_percentage'] ?? null,
            $data['associate_commission'] ?? 0,
            $data['agent_commission'] ?? 0,
            $data['remarks'] ?? null,
            $data['status'] ?? 'pending',
            $data['created_by'] ?? null
        ];

        try {
            $this->database->execute($sql, $params);
            $bookingId = $this->database->lastInsertId();

            // Update plot status
            $this->updatePlotStatus($data['plot_id'], 'booked');

            // Calculate commissions if associate is involved
            if (!empty($data['associate_id'])) {
                $this->calculateCommissions($bookingId, $data['associate_id'], $data['total_amount'] ?? $data['booking_amount']);
            }

            $this->logger->log("Plot booked: {$data['booking_number']} (ID: $bookingId)", 'info', 'plotting');
            return $bookingId;
        } catch (Exception $e) {
            $this->logger->log("Error booking plot: " . $e->getMessage(), 'error', 'plotting');
            throw new RuntimeException("Failed to book plot: " . $e->getMessage());
        }
    }

    /**
     * Update plot status
     */
    private function updatePlotStatus($plotId, $status)
    {
        $sql = "UPDATE plots SET plot_status = ?, updated_at = NOW() WHERE id = ?";
        try {
            $this->database->execute($sql, [$status, $plotId]);
        } catch (Exception $e) {
            $this->logger->log("Error updating plot status: " . $e->getMessage(), 'error', 'plotting');
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
            $stats['land_acquired'] = $this->database->fetchOne($sql);

            // Total plots created
            $sql = "SELECT COUNT(*) as total_plots,
                           SUM(CASE WHEN plot_status = 'available' THEN 1 ELSE 0 END) as available_plots,
                           SUM(CASE WHEN plot_status = 'sold' THEN 1 ELSE 0 END) as sold_plots,
                           SUM(current_price) as total_value FROM plots";
            $stats['plots'] = $this->database->fetchOne($sql);

            // Total bookings
            $sql = "SELECT COUNT(*) as total_bookings,
                           SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                           SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                           SUM(total_amount) as total_booking_value FROM plot_bookings";
            $stats['bookings'] = $this->database->fetchOne($sql);

        } catch (Exception $e) {
            $this->logger->log("Error fetching plotting statistics: " . $e->getMessage(), 'error', 'plotting');
        }

        return $stats;
    }

    /**
     * Calculate commissions
     */
    private function calculateCommissions($bookingId, $associateId, $totalAmount)
    {
        try {
            // Use the new DifferentialCommissionCalculator for automated MLM distribution
            $calculator = new \App\Services\DifferentialCommissionCalculator();
            $result = $calculator->calculate($totalAmount, $associateId, $bookingId);

            if ($result['success']) {
                $this->logger->log('MLM Commissions distributed for booking', 'info', 'plotting', [
                    'booking_id' => $bookingId,
                    'total_distributed' => $result['total_distributed'],
                    'recipients' => count($result['commissions'])
                ]);
                
                // Also record a summary in commission_tracking for plotting-specific reports
                $this->database->query(
                    "INSERT INTO commission_tracking (
                        booking_id, associate_id, commission_type, commission_level,
                        commission_amount, commission_percentage, payment_status, remarks
                    ) VALUES (?, ?, 'mlm_differential', 1, ?, ?, 'pending', 'Distributed via MLM Differential Logic')",
                    [$bookingId, $associateId, $totalAmount * ($result['total_distributed'] / 100), $result['total_distributed']]
                );
            } else {
                $this->logger->log('MLM Commission calculation skipped or failed: ' . ($result['message'] ?? 'Unknown error'), 'warning', 'plotting');
            }
            
        } catch (Exception $e) {
            $this->logger->log('Failed to calculate commissions: ' . $e->getMessage(), 'error', 'plotting');
        }
    }
}
