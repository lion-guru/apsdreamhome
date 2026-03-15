<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use App\Services\SystemLogger as Logger;
use App\Services\ConfigurationManager as Config;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Plotting Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class PlottingService
{
    private $database;
    private $logger;
    private $config;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new Logger();
        $this->config = Config::getInstance();

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
        } catch (\Exception $e) {
            $this->logger->error('Error creating plotting tables', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Add land acquisition
     */
    public function addLandAcquisition(array $data)
    {
        try {
            // Generate acquisition number
            $acquisitionNumber = $this->generateAcquisitionNumber();

            $sql = "INSERT INTO land_acquisitions (
                acquisition_number, farmer_id, land_area, land_area_unit, location, village, tehsil, district, state,
                acquisition_date, acquisition_cost, payment_status, land_type, soil_type, water_source,
                electricity_available, road_access, documents, remarks, status, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $acquisitionNumber,
                $data['farmer_id'] ?? null,
                $data['land_area'],
                $data['land_area_unit'] ?? 'sqft',
                $data['location'],
                $data['village'] ?? '',
                $data['tehsil'] ?? '',
                $data['district'] ?? '',
                $data['state'] ?? '',
                $data['acquisition_date'],
                $data['acquisition_cost'] ?? 0,
                $data['payment_status'] ?? 'pending',
                $data['land_type'] ?? 'agricultural',
                $data['soil_type'] ?? '',
                $data['water_source'] ?? '',
                $data['electricity_available'] ?? false,
                $data['road_access'] ?? false,
                json_encode($data['documents'] ?? []),
                $data['remarks'] ?? '',
                $data['status'] ?? 'active',
                $data['created_by']
            ];

            $this->database->query($sql, $params);
            $acquisitionId = $this->database->lastInsertId();

            $this->logger->info('Land acquisition added', [
                'acquisition_id' => $acquisitionId,
                'acquisition_number' => $acquisitionNumber,
                'land_area' => $data['land_area']
            ]);

            return [
                'success' => true,
                'message' => 'Land acquisition added successfully',
                'acquisition_id' => $acquisitionId,
                'acquisition_number' => $acquisitionNumber
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to add land acquisition', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Failed to add land acquisition: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get land acquisitions
     */
    public function getLandAcquisitions($filters = [], $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT la.*, fp.name as farmer_name, fp.phone as farmer_phone
                    FROM land_acquisitions la
                    LEFT JOIN farmer_profiles fp ON la.farmer_id = fp.id
                    WHERE 1=1";
            $params = [];

            if (!empty($filters['status'])) {
                $sql .= " AND la.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['farmer_id'])) {
                $sql .= " AND la.farmer_id = ?";
                $params[] = $filters['farmer_id'];
            }

            if (!empty($filters['location'])) {
                $sql .= " AND la.location LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }

            $sql .= " ORDER BY la.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $acquisitions = $this->database->select($sql, $params);

            return [
                'success' => true,
                'data' => $acquisitions
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get land acquisitions', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve land acquisitions'
            ];
        }
    }

    /**
     * Add plot
     */
    public function addPlot(array $data)
    {
        try {
            // Generate plot number
            $plotNumber = $this->generatePlotNumber($data['land_acquisition_id']);

            $sql = "INSERT INTO plots (
                plot_number, land_acquisition_id, plot_area, plot_area_unit, plot_type,
                dimensions_length, dimensions_width, corner_plot, park_facing, road_facing,
                current_price, base_price, plc_amount, other_charges, total_price,
                remarks, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $plotNumber,
                $data['land_acquisition_id'],
                $data['plot_area'],
                $data['plot_area_unit'] ?? 'sqft',
                $data['plot_type'] ?? 'residential',
                $data['dimensions_length'] ?? null,
                $data['dimensions_width'] ?? null,
                $data['corner_plot'] ?? false,
                $data['park_facing'] ?? false,
                $data['road_facing'] ?? false,
                $data['current_price'],
                $data['base_price'] ?? $data['current_price'],
                $data['plc_amount'] ?? 0,
                $data['other_charges'] ?? 0,
                $data['total_price'] ?? $data['current_price'],
                $data['remarks'] ?? '',
                $data['created_by']
            ];

            $this->database->query($sql, $params);
            $plotId = $this->database->lastInsertId();

            $this->logger->info('Plot added', [
                'plot_id' => $plotId,
                'plot_number' => $plotNumber,
                'plot_area' => $data['plot_area']
            ]);

            return [
                'success' => true,
                'message' => 'Plot added successfully',
                'plot_id' => $plotId,
                'plot_number' => $plotNumber
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to add plot', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Failed to add plot: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get plots
     */
    public function getPlots($filters = [], $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT p.*, la.location as acquisition_location, la.village, la.acquisition_number
                    FROM plots p
                    JOIN land_acquisitions la ON p.land_acquisition_id = la.id
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

            if (!empty($filters['land_acquisition_id'])) {
                $sql .= " AND p.land_acquisition_id = ?";
                $params[] = $filters['land_acquisition_id'];
            }

            if (!empty($filters['corner_plot'])) {
                $sql .= " AND p.corner_plot = ?";
                $params[] = $filters['corner_plot'];
            }

            $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $plots = $this->database->select($sql, $params);

            return [
                'success' => true,
                'data' => $plots
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get plots', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve plots'
            ];
        }
    }

    /**
     * Book plot
     */
    public function bookPlot(array $data)
    {
        try {
            // Check if plot is available
            $plot = $this->database->selectOne(
                "SELECT * FROM plots WHERE id = ? AND plot_status = 'available'",
                [$data['plot_id']]
            );

            if (!$plot) {
                return [
                    'success' => false,
                    'message' => 'Plot not available for booking'
                ];
            }

            // Generate booking number
            $bookingNumber = $this->generateBookingNumber();

            $sql = "INSERT INTO plot_bookings (
                plot_id, customer_id, associate_id, booking_number, booking_type,
                booking_amount, total_amount, payment_plan, installment_period, installment_amount,
                payment_method, transaction_id, booking_date, status, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $data['plot_id'],
                $data['customer_id'],
                $data['associate_id'] ?? null,
                $bookingNumber,
                $data['booking_type'] ?? 'direct',
                $data['booking_amount'],
                $data['total_amount'],
                $data['payment_plan'] ?? 'lump_sum',
                $data['installment_period'] ?? null,
                $data['installment_amount'] ?? null,
                $data['payment_method'] ?? '',
                $data['transaction_id'] ?? '',
                $data['booking_date'],
                $data['status'] ?? 'pending',
                $data['created_by']
            ];

            $this->database->query($sql, $params);
            $bookingId = $this->database->lastInsertId();

            // Update plot status
            $this->updatePlotStatus($data['plot_id'], 'booked');

            // Calculate commissions if associate is involved
            if (!empty($data['associate_id'])) {
                $this->calculateCommissions($bookingId, $data['associate_id'], $data['total_amount']);
            }

            $this->logger->info('Plot booked', [
                'booking_id' => $bookingId,
                'booking_number' => $bookingNumber,
                'plot_id' => $data['plot_id']
            ]);

            return [
                'success' => true,
                'message' => 'Plot booked successfully',
                'booking_id' => $bookingId,
                'booking_number' => $bookingNumber
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to book plot', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Failed to book plot: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get plot bookings
     */
    public function getPlotBookings($filters = [], $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT pb.*, p.plot_number, p.plot_area, p.plot_type,
                           u.full_name as customer_name, u.phone as customer_phone,
                           a.name as associate_name, la.location as plot_location
                    FROM plot_bookings pb
                    JOIN plots p ON pb.plot_id = p.id
                    JOIN land_acquisitions la ON p.land_acquisition_id = la.id
                    LEFT JOIN users u ON pb.customer_id = u.id
                    LEFT JOIN associates a ON pb.associate_id = a.id
                    WHERE 1=1";
            $params = [];

            if (!empty($filters['status'])) {
                $sql .= " AND pb.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['customer_id'])) {
                $sql .= " AND pb.customer_id = ?";
                $params[] = $filters['customer_id'];
            }

            if (!empty($filters['associate_id'])) {
                $sql .= " AND pb.associate_id = ?";
                $params[] = $filters['associate_id'];
            }

            $sql .= " ORDER BY pb.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $bookings = $this->database->select($sql, $params);

            return [
                'success' => true,
                'data' => $bookings
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get plot bookings', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve plot bookings'
            ];
        }
    }

    /**
     * Add payment to booking
     */
    public function addBookingPayment(array $data)
    {
        try {
            $sql = "INSERT INTO plot_payments (
                booking_id, amount, payment_date, payment_method, transaction_id,
                installment_number, payment_status, receipt_number, bank_reference, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $data['booking_id'],
                $data['amount'],
                $data['payment_date'],
                $data['payment_method'],
                $data['transaction_id'] ?? '',
                $data['installment_number'] ?? null,
                $data['payment_status'] ?? 'completed',
                $data['receipt_number'] ?? '',
                $data['bank_reference'] ?? '',
                $data['remarks'] ?? ''
            ];

            $this->database->query($sql, $params);
            $paymentId = $this->database->lastInsertId();

            // Update booking payment status
            $this->updateBookingPaymentStatus($data['booking_id']);

            $this->logger->info('Payment added to booking', [
                'payment_id' => $paymentId,
                'booking_id' => $data['booking_id'],
                'amount' => $data['amount']
            ]);

            return [
                'success' => true,
                'message' => 'Payment added successfully',
                'payment_id' => $paymentId
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to add booking payment', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Failed to add payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get plotting statistics
     */
    public function getPlottingStats()
    {
        try {
            $stats = [];

            // Total land acquired
            $stats['land_acquired'] = $this->database->selectOne(
                "SELECT COUNT(*) as total_acquisitions, SUM(land_area) as total_area,
                        SUM(acquisition_cost) as total_cost FROM land_acquisitions WHERE status = 'active'"
            );

            // Total plots
            $stats['plots'] = $this->database->selectOne(
                "SELECT COUNT(*) as total_plots,
                        SUM(CASE WHEN plot_status = 'available' THEN 1 ELSE 0 END) as available_plots,
                        SUM(CASE WHEN plot_status = 'sold' THEN 1 ELSE 0 END) as sold_plots,
                        SUM(current_price) as total_value FROM plots"
            );

            // Total bookings
            $stats['bookings'] = $this->database->selectOne(
                "SELECT COUNT(*) as total_bookings,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                        SUM(total_amount) as total_booking_value FROM plot_bookings"
            );

            // Monthly sales
            $stats['monthly_sales'] = $this->database->select(
                "SELECT MONTH(booking_date) as month, YEAR(booking_date) as year,
                        COUNT(*) as bookings, SUM(total_amount) as value
                 FROM plot_bookings WHERE status IN ('confirmed', 'completed')
                 AND booking_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY YEAR(booking_date), MONTH(booking_date)
                 ORDER BY year DESC, month DESC"
            );

            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get plotting statistics', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve statistics'
            ];
        }
    }

    /**
     * Generate acquisition number
     */
    private function generateAcquisitionNumber()
    {
        $prefix = 'LAQ';
        $year = date('Y');
        $sequence = $this->getSequenceNumber('land_acquisition');

        return $prefix . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate plot number
     */
    private function generatePlotNumber($landAcquisitionId)
    {
        $prefix = 'PLOT';
        $sequence = $this->getPlotSequenceNumber($landAcquisitionId);

        return $prefix . str_pad($landAcquisitionId, 4, '0', STR_PAD_LEFT) . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate booking number
     */
    private function generateBookingNumber()
    {
        $prefix = 'BK';
        $year = date('Y');
        $sequence = $this->getSequenceNumber('plot_booking');

        return $prefix . $year . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get sequence number
     */
    private function getSequenceNumber($type)
    {
        $table = 'sequences';

        // Create sequences table if not exists
        $this->database->query("
            CREATE TABLE IF NOT EXISTS $table (
                type VARCHAR(50) PRIMARY KEY,
                last_number INT DEFAULT 0,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Get and update sequence
        $this->database->query("
            INSERT INTO $table (type, last_number) VALUES (?, 1)
            ON DUPLICATE KEY UPDATE last_number = last_number + 1
        ", [$type]);

        $result = $this->database->selectOne("SELECT last_number FROM $table WHERE type = ?", [$type]);

        return $result['last_number'] ?? 1;
    }

    /**
     * Get plot sequence number
     */
    private function getPlotSequenceNumber($landAcquisitionId)
    {
        $result = $this->database->selectOne(
            "SELECT COUNT(*) as count FROM plots WHERE land_acquisition_id = ?",
            [$landAcquisitionId]
        );

        return ($result['count'] ?? 0) + 1;
    }

    /**
     * Update plot status
     */
    public function updatePlotStatus($plotId, $status)
    {
        $this->database->query(
            "UPDATE plots SET plot_status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $plotId]
        );

        return $this->database->lastInsertId();
    }

    /**
     * Create a new land project
     */
    public function createProject($data)
    {
        $sql = "INSERT INTO land_projects (
            project_name, description, location, total_area, 
            created_by, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $this->database->query($sql, [
            $data['project_name'],
            $data['description'],
            $data['location'],
            $data['total_area'],
            $data['created_by']
        ]);

        return $this->database->lastInsertId();
    }

    /**
     * Subdivide land into plots
     */
    public function subdivideLand($data)
    {
        $sql = "INSERT INTO plots (
            land_acquisition_id, plot_number, plot_area, plot_area_unit,
            location, price_per_unit, total_price, plot_status,
            created_by, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $this->database->query($sql, [
            $data['land_acquisition_id'],
            $data['plot_number'],
            $data['plot_area'],
            $data['plot_area_unit'],
            $data['price_per_unit'],
            $data['total_price'],
            'available',
            $data['created_by']
        ]);

        return $this->database->lastInsertId();
    }

    /**
     * Reserve a plot for customer
     */
    public function reservePlot($plotId, $customerId, $reservationData)
    {
        $sql = "INSERT INTO plot_reservations (
            plot_id, customer_id, reservation_date, expiry_date,
            status, created_by, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $this->database->query($sql, [
            $plotId,
            $customerId,
            $reservationData['reservation_date'],
            $reservationData['expiry_date'],
            'reserved',
            $reservationData['created_by']
        ]);

        // Update plot status to reserved
        $this->updatePlotStatus($plotId, 'reserved');

        return $this->database->lastInsertId();
    }

    /**
     * Sell a plot to customer
     */
    public function sellPlot($plotId, $customerId, $saleData)
    {
        $sql = "INSERT INTO plot_sales (
            plot_id, customer_id, sale_date, sale_price,
            payment_status, created_by, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $this->database->query($sql, [
            $plotId,
            $customerId,
            $saleData['sale_date'],
            $saleData['sale_price'],
            $saleData['payment_status'],
            $saleData['created_by']
        ]);

        // Update plot status to sold
        $this->updatePlotStatus($plotId, 'sold');

        return $this->database->lastInsertId();
    }

    /**
     * Get project details
     */
    public function getProject($projectId)
    {
        $sql = "SELECT * FROM land_projects WHERE id = ?";
        $result = $this->database->fetchOne($sql, [$projectId]);

        return $result;
    }

    /**
     * Get plot details
     */
    public function getPlot($plotId)
    {
        $sql = "SELECT p.*, la.project_name, la.location 
                 FROM plots p 
                 LEFT JOIN land_acquisitions la ON p.land_acquisition_id = la.id 
                 WHERE p.id = ?";
        $result = $this->database->fetchOne($sql, [$plotId]);

        return $result;
    }

    /**
     * Get available plots
     */
    public function getAvailablePlots($filters = [])
    {
        $sql = "SELECT p.*, la.project_name, la.location 
                 FROM plots p 
                 LEFT JOIN land_acquisitions la ON p.land_acquisition_id = la.id 
                 WHERE p.plot_status = 'available'";

        $params = [];

        if (!empty($filters['project_id'])) {
            $sql .= " AND la.id = ?";
            $params[] = $filters['project_id'];
        }

        if (!empty($filters['min_area'])) {
            $sql .= " AND p.plot_area >= ?";
            $params[] = $filters['min_area'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price_per_unit <= ?";
            $params[] = $filters['max_price'];
        }

        $results = $this->database->fetchAll($sql, $params);

        return $results;
    }

    /**
     * Calculate commissions
     */
    private function calculateCommissions($bookingId, $associateId, $totalAmount)
    {
        try {
            // Fetch booking details to get the buyer (customer)
            $booking = $this->database->selectOne("SELECT * FROM plot_bookings WHERE id = ?", [$bookingId]);
            if (!$booking) return;

            // Use the new DifferentialCommissionCalculator for automated MLM distribution
            $calculator = new \App\Services\DifferentialCommissionCalculator();
            $result = $calculator->calculate($totalAmount, $booking['customer_id'], $booking['plot_id']);

            if ($result['success']) {
                $this->logger->info('MLM Commissions distributed for booking', [
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
                $this->logger->warning('MLM Commission calculation skipped or failed', [
                    'booking_id' => $bookingId,
                    'message' => $result['message'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to calculate commissions', [
                'error' => $e->getMessage(),
                'booking_id' => $bookingId,
                'associate_id' => $associateId
            ]);
        }
    }

    /**
     * Update booking payment status
     */
    private function updateBookingPaymentStatus($bookingId)
    {
        try {
            $result = $this->database->selectOne(
                "SELECT SUM(amount) as total_paid FROM plot_payments WHERE booking_id = ? AND payment_status = 'completed'",
                [$bookingId]
            );

            $totalPaid = $result['total_paid'] ?? 0;

            $booking = $this->database->selectOne(
                "SELECT total_amount FROM plot_bookings WHERE id = ?",
                [$bookingId]
            );

            $totalAmount = $booking['total_amount'] ?? 0;

            $paymentStatus = 'pending';
            if ($totalPaid >= $totalAmount) {
                $paymentStatus = 'completed';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'partial';
            }

            $this->database->query(
                "UPDATE plot_bookings SET payment_status = ? WHERE id = ?",
                [$paymentStatus, $bookingId]
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to update booking payment status', [
                'error' => $e->getMessage(),
                'booking_id' => $bookingId
            ]);
        }
    }
}
