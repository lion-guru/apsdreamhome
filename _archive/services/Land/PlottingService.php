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
    }

    /**
     * Add land acquisition
     */
    public function addLandAcquisition(array $data)
    {
        try {
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
     * Add plot — columns match actual `plots` table schema
     */
    public function addPlot(array $data)
    {
        try {
            $plotNumber = $data['plot_number'] ?? $this->generatePlotNumber($data['colony_id']);

            $sql = "INSERT INTO plots (
                colony_id, plot_number, block, sector, plot_type,
                area_sqft, area_sqm, width_ft, length_ft, dimension_label,
                price_per_sqft, base_price_per_sqft, total_price,
                facing, corner_plot, park_facing, road_width_ft,
                status, is_active, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $data['colony_id'],
                $plotNumber,
                $data['block'] ?? null,
                $data['sector'] ?? null,
                $data['plot_type'] ?? 'residential',
                $data['area_sqft'] ?? 0,
                $data['area_sqm'] ?? 0,
                $data['width_ft'] ?? null,
                $data['length_ft'] ?? null,
                $data['dimension_label'] ?? null,
                $data['price_per_sqft'] ?? 0,
                $data['base_price_per_sqft'] ?? $data['price_per_sqft'] ?? 0,
                $data['total_price'] ?? 0,
                $data['facing'] ?? null,
                $data['corner_plot'] ?? 0,
                $data['park_facing'] ?? 0,
                $data['road_width_ft'] ?? 0,
                $data['status'] ?? 'available',
                $data['is_active'] ?? 1,
                $data['created_by'] ?? null
            ];

            $this->database->query($sql, $params);
            $plotId = $this->database->lastInsertId();

            $this->logger->info('Plot added', [
                'plot_id' => $plotId,
                'plot_number' => $plotNumber,
                'colony_id' => $data['colony_id'],
                'area_sqft' => $data['area_sqft'] ?? 0
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
     * Get plots — JOINed with colonies table for colony_name and location
     */
    public function getPlots($filters = [], $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT p.*, c.name as colony_name, d.name as location
                    FROM plots p
                    JOIN colonies c ON p.colony_id = c.id
                    LEFT JOIN districts d ON c.district_id = d.id
                    WHERE 1=1";
            $params = [];

            if (!empty($filters['status'])) {
                $sql .= " AND p.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['colony_id'])) {
                $sql .= " AND p.colony_id = ?";
                $params[] = $filters['colony_id'];
            }

            if (!empty($filters['plot_type'])) {
                $sql .= " AND p.plot_type = ?";
                $params[] = $filters['plot_type'];
            }

            if (!empty($filters['block'])) {
                $sql .= " AND p.block = ?";
                $params[] = $filters['block'];
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
     * Book plot — columns match actual `plot_bookings` table schema
     */
    public function bookPlot(array $data)
    {
        try {
            $plot = $this->database->selectOne(
                "SELECT * FROM plots WHERE id = ? AND status = 'available'",
                [$data['plot_id']]
            );

            if (!$plot) {
                return [
                    'success' => false,
                    'message' => 'Plot not available for booking'
                ];
            }

            $bookingNumber = $this->generateBookingNumber();

            $sql = "INSERT INTO plot_bookings (
                plot_id, customer_id, booking_number, booking_date,
                total_plot_value, booking_amount, agreement_value,
                status, channel, associate_id,
                commission_pct, commission_amount, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $data['plot_id'],
                $data['customer_id'],
                $bookingNumber,
                $data['booking_date'] ?? date('Y-m-d'),
                $data['total_plot_value'] ?? $plot['total_price'] ?? 0,
                $data['booking_amount'] ?? 0,
                $data['agreement_value'] ?? $data['total_plot_value'] ?? $plot['total_price'] ?? 0,
                $data['status'] ?? 'token_paid',
                $data['channel'] ?? 'direct',
                $data['associate_id'] ?? null,
                $data['commission_pct'] ?? 0,
                $data['commission_amount'] ?? 0,
                $data['notes'] ?? null
            ];

            $this->database->query($sql, $params);
            $bookingId = $this->database->lastInsertId();

            $this->updatePlotStatus($data['plot_id'], 'booked');

            if (!empty($data['associate_id'])) {
                $this->calculateCommissions($bookingId, $data['associate_id'], $data['total_plot_value'] ?? $plot['total_price'] ?? 0);
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
            $sql = "SELECT pb.*, p.plot_number, p.area_sqft, p.plot_type,
                           c.name as colony_name,
                           u.full_name as customer_name, u.phone as customer_phone,
                           a.full_name as associate_name
                    FROM plot_bookings pb
                    JOIN plots p ON pb.plot_id = p.id
                    JOIN colonies c ON p.colony_id = c.id
                    LEFT JOIN users u ON pb.customer_id = u.id
                    LEFT JOIN associates assoc ON pb.associate_id = assoc.id
                    LEFT JOIN users a ON assoc.user_id = a.id
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

            if (!empty($filters['colony_id'])) {
                $sql .= " AND p.colony_id = ?";
                $params[] = $filters['colony_id'];
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
     * Add payment to booking — records into payment_transactions table
     */
    public function addBookingPayment(array $data)
    {
        try {
            $transactionId = 'TRX-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));

            $sql = "INSERT INTO payment_transactions (
                transaction_id, user_id, booking_id, amount,
                payment_method, payment_status, gateway_transaction_id, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $params = [
                $transactionId,
                $data['user_id'] ?? $data['customer_id'] ?? 0,
                $data['booking_id'],
                $data['amount'],
                $data['payment_method'] ?? 'bank_transfer',
                $data['payment_status'] ?? 'completed',
                $data['gateway_transaction_id'] ?? null
            ];

            $this->database->query($sql, $params);
            $paymentId = $this->database->lastInsertId();

            $this->updateBookingPaymentStatus($data['booking_id']);

            $this->logger->info('Payment added to booking', [
                'payment_id' => $paymentId,
                'booking_id' => $data['booking_id'],
                'amount' => $data['amount'],
                'transaction_id' => $transactionId
            ]);

            return [
                'success' => true,
                'message' => 'Payment recorded successfully',
                'payment_id' => $paymentId,
                'transaction_id' => $transactionId
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
     * Get plotting statistics — uses correct column names (area_sqft, total_price, etc.)
     */
    public function getPlottingStats()
    {
        try {
            $stats = [];

            $stats['land_acquired'] = $this->database->selectOne(
                "SELECT COUNT(*) as total_acquisitions, SUM(land_area) as total_area,
                        SUM(acquisition_cost) as total_cost FROM land_acquisitions WHERE status = 'active'"
            );

            $stats['plots'] = $this->database->selectOne(
                "SELECT COUNT(*) as total_plots,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_plots,
                        SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked_plots,
                        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold_plots,
                        SUM(total_price) as total_value FROM plots WHERE is_active = 1"
            );

            $stats['bookings'] = $this->database->selectOne(
                "SELECT COUNT(*) as total_bookings,
                        SUM(CASE WHEN status IN ('token_paid','agreement_signed','emi_active','partially_paid') THEN 1 ELSE 0 END) as active_bookings,
                        SUM(CASE WHEN status = 'fully_paid' THEN 1 ELSE 0 END) as completed_bookings,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                        SUM(total_plot_value) as total_booking_value FROM plot_bookings"
            );

            $stats['monthly_sales'] = $this->database->select(
                "SELECT MONTH(booking_date) as month, YEAR(booking_date) as year,
                        COUNT(*) as bookings, SUM(total_plot_value) as value
                 FROM plot_bookings WHERE status NOT IN ('cancelled')
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
    private function generatePlotNumber($colonyId)
    {
        $prefix = 'PLOT';
        $sequence = $this->getPlotSequenceNumber($colonyId);

        return $prefix . str_pad($colonyId, 4, '0', STR_PAD_LEFT) . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
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

        $this->database->query("
            CREATE TABLE IF NOT EXISTS $table (
                type VARCHAR(50) PRIMARY KEY,
                last_number INT DEFAULT 0,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

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
    private function getPlotSequenceNumber($colonyId)
    {
        $result = $this->database->selectOne(
            "SELECT COUNT(*) as count FROM plots WHERE colony_id = ?",
            [$colonyId]
        );

        return ($result['count'] ?? 0) + 1;
    }

    /**
     * Update plot status
     */
    public function updatePlotStatus($plotId, $status)
    {
        $this->database->query(
            "UPDATE plots SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $plotId]
        );

        return true;
    }

    /**
     * Create a new land project — SQL built directly, no dead try-catch wrapper
     */
    public function createProject($data)
    {
        try {
            $sql = "INSERT INTO land_projects (
                project_name, description, location, total_area, created_by, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

            $this->database->query($sql, [
                $data['project_name'],
                $data['description'] ?? '',
                $data['location'] ?? '',
                $data['total_area'] ?? 0,
                $data['created_by']
            ]);

            $projectId = $this->database->lastInsertId();

            $this->logger->info('Land project created', [
                'project_id' => $projectId,
                'project_name' => $data['project_name']
            ]);

            return [
                'success' => true,
                'project_id' => $projectId,
                'message' => 'Project created successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to create project', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create project: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Subdivide land into plots
     */
    public function subdivideLand($data)
    {
        try {
            $sql = "INSERT INTO plots (
                colony_id, plot_number, area_sqft, plot_type,
                price_per_sqft, total_price, status, is_active,
                created_by, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'available', 1, ?, NOW(), NOW())";

            $this->database->query($sql, [
                $data['colony_id'] ?? $data['land_acquisition_id'],
                $data['plot_number'],
                $data['area_sqft'] ?? $data['plot_area'] ?? 0,
                $data['plot_type'] ?? 'residential',
                $data['price_per_sqft'] ?? $data['price_per_unit'] ?? 0,
                $data['total_price'],
                $data['created_by']
            ]);

            return $this->database->lastInsertId();
        } catch (\Exception $e) {
            $this->logger->error('Failed to subdivide land', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get project details
     */
    public function getProject($projectId)
    {
        try {
            $sql = "SELECT * FROM land_projects WHERE id = ?";
            $result = $this->database->fetchOne($sql, [$projectId]);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get project', [
                'error' => $e->getMessage(),
                'project_id' => $projectId
            ]);

            return null;
        }
    }

    /**
     * Get plot details
     */
    public function getPlot($plotId)
    {
        try {
            $sql = "SELECT p.*, c.name as colony_name, d.name as location
                     FROM plots p
                     JOIN colonies c ON p.colony_id = c.id
                     LEFT JOIN districts d ON c.district_id = d.id
                     WHERE p.id = ?";
            $result = $this->database->fetchOne($sql, [$plotId]);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get plot', [
                'error' => $e->getMessage(),
                'plot_id' => $plotId
            ]);

            return null;
        }
    }

    /**
     * Get available plots
     */
    public function getAvailablePlots($filters = [])
    {
        try {
            $sql = "SELECT p.*, c.name as colony_name, d.name as location
                     FROM plots p
                     JOIN colonies c ON p.colony_id = c.id
                     LEFT JOIN districts d ON c.district_id = d.id
                     WHERE p.status = 'available' AND p.is_active = 1";

            $params = [];

            if (!empty($filters['colony_id'])) {
                $sql .= " AND p.colony_id = ?";
                $params[] = $filters['colony_id'];
            }

            if (!empty($filters['min_area'])) {
                $sql .= " AND p.area_sqft >= ?";
                $params[] = $filters['min_area'];
            }

            if (!empty($filters['max_price'])) {
                $sql .= " AND p.total_price <= ?";
                $params[] = $filters['max_price'];
            }

            if (!empty($filters['block'])) {
                $sql .= " AND p.block = ?";
                $params[] = $filters['block'];
            }

            $sql .= " ORDER BY p.total_price ASC";

            $results = $this->database->fetchAll($sql, $params);

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get available plots', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Calculate commissions
     */
    private function calculateCommissions($bookingId, $associateId, $totalAmount)
    {
        try {
            $booking = $this->database->selectOne("SELECT * FROM plot_bookings WHERE id = ?", [$bookingId]);
            if (!$booking) return;

            $calculator = new \App\Services\DifferentialCommissionCalculator();
            $result = $calculator->calculate($totalAmount, $booking['customer_id'], $booking['plot_id']);

            if ($result['success']) {
                $this->logger->info('MLM Commissions distributed for booking', [
                    'booking_id' => $bookingId,
                    'total_distributed' => $result['total_distributed'],
                    'recipients' => count($result['commissions'])
                ]);

                try {
                    $this->database->query(
                        "INSERT INTO commission_tracking (
                            booking_id, associate_id, commission_type, commission_level,
                            commission_amount, commission_percentage, payment_status, remarks
                        ) VALUES (?, ?, 'mlm_differential', 1, ?, ?, 'pending', 'Distributed via MLM Differential Logic')",
                        [$bookingId, $associateId, $totalAmount * ($result['total_distributed'] / 100), $result['total_distributed']]
                    );
                } catch (\Exception $e) {
                    $this->logger->debug('commission_tracking insert skipped (table dropped)', ['error' => $e->getMessage()]);
                }
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
     * Update booking payment status — queries payment_transactions table
     */
    private function updateBookingPaymentStatus($bookingId)
    {
        try {
            $result = $this->database->selectOne(
                "SELECT SUM(amount) as total_paid FROM payment_transactions
                 WHERE booking_id = ? AND payment_status = 'completed'",
                [$bookingId]
            );

            $totalPaid = $result['total_paid'] ?? 0;

            $booking = $this->database->selectOne(
                "SELECT total_plot_value FROM plot_bookings WHERE id = ?",
                [$bookingId]
            );

            $totalAmount = $booking['total_plot_value'] ?? 0;

            $paymentStatus = 'pending';
            if ($totalPaid >= $totalAmount && $totalAmount > 0) {
                $paymentStatus = 'completed';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'partial';
            }

            $this->database->query(
                "UPDATE plot_bookings SET notes = CONCAT(COALESCE(notes, ''), '\nPayment status updated: $paymentStatus')
                 WHERE id = ?",
                [$bookingId]
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to update booking payment status', [
                'error' => $e->getMessage(),
                'booking_id' => $bookingId
            ]);
        }
    }
}
