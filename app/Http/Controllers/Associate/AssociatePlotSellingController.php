<?php
/**
 * Associate Plot Selling Controller
 * Complete system for associates to sell plots through MLM
 */

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;

class AssociatePlotSellingController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Associate Plot Inventory Dashboard
     */
    public function plotInventory() {
        global $pdo;

        $associate_id = $_SESSION['associate_id'] ?? null;
        if (!$associate_id) {
            $this->redirect('/login');
            return;
        }

        // Get associate's allocated plots
        $allocated_plots = [];
        $available_plots = [];
        $sold_plots = [];

        try {
            // Get associate's allocated plots
            $allocated_query = "
                SELECT p.*, c.name as colony_name, c.location as colony_location,
                       c.price_per_sqft, c.total_plots, c.available_plots
                FROM plots p
                JOIN colonies c ON p.colony_id = c.id
                WHERE p.allocated_to = ? AND p.status IN ('available', 'allocated')
                ORDER BY c.name, p.plot_number
            ";
            $stmt = $pdo->prepare($allocated_query);
            $stmt->execute([$associate_id]);
            $allocated_plots = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get general available plots (not allocated yet)
            $available_query = "
                SELECT p.*, c.name as colony_name, c.location as colony_location,
                       c.price_per_sqft, c.total_plots, c.available_plots
                FROM plots p
                JOIN colonies c ON p.colony_id = c.id
                WHERE p.status = 'available' AND p.allocated_to IS NULL
                ORDER BY c.name, p.plot_number
                LIMIT 50
            ";
            $stmt = $pdo->prepare($available_query);
            $stmt->execute();
            $available_plots = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get associate's sold plots
            $sold_query = "
                SELECT p.*, c.name as colony_name, c.location as colony_location,
                       ps.sale_price, ps.commission_earned, ps.sale_date
                FROM plots p
                JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN plot_sales ps ON p.id = ps.plot_id
                WHERE p.allocated_to = ? AND p.status = 'sold'
                ORDER BY ps.sale_date DESC
                LIMIT 20
            ";
            $stmt = $pdo->prepare($sold_query);
            $stmt->execute([$associate_id]);
            $sold_plots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Associate plot inventory error: ' . $e->getMessage());
        }

        // Calculate summary stats
        $summary = [
            'allocated_plots' => count($allocated_plots),
            'available_for_allocation' => count($available_plots),
            'total_sold' => count($sold_plots),
            'total_commission' => array_sum(array_column($sold_plots, 'commission_earned')),
            'this_month_sales' => 0,
            'pending_commissions' => 0
        ];

        // Calculate this month's sales
        $current_month = date('Y-m');
        foreach ($sold_plots as $plot) {
            if (isset($plot['sale_date']) && strpos($plot['sale_date'], $current_month) === 0) {
                $summary['this_month_sales']++;
            }
        }

        $this->render('associate/plot_inventory', [
            'allocated_plots' => $allocated_plots,
            'available_plots' => $available_plots,
            'sold_plots' => $sold_plots,
            'summary' => $summary,
            'page_title' => 'Plot Inventory - Associate Dashboard'
        ]);
    }

    /**
     * Real-time Commission Calculator
     */
    public function commissionCalculator() {
        global $pdo;

        $associate_id = $_SESSION['associate_id'] ?? null;
        if (!$associate_id) {
            $this->redirect('/login');
            return;
        }

        // Get associate's current level and commission structure
        $associate_level = 1;
        $commission_rates = [];

        try {
            // Get associate level
            $level_query = "SELECT current_level FROM associates WHERE id = ?";
            $stmt = $pdo->prepare($level_query);
            $stmt->execute([$associate_id]);
            $associate_level = $stmt->fetchColumn() ?: 1;

            // Get commission structure
            $commission_query = "SELECT * FROM associate_levels WHERE level_number <= ? ORDER BY level_number";
            $stmt = $pdo->prepare($commission_query);
            $stmt->execute([$associate_level]);
            $commission_rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Commission calculator error: ' . $e->getMessage());
        }

        // Handle AJAX calculation requests
        if (isset($_POST['calculate'])) {
            $plot_price = floatval($_POST['plot_price']);
            $plot_area = floatval($_POST['plot_area']);

            $calculations = [];
            foreach ($commission_rates as $level) {
                $base_commission = ($plot_price * $level['commission_percentage']) / 100;
                $bonus_commission = ($plot_price * $level['bonus_percentage']) / 100;
                $override_commission = ($plot_price * $level['override_percentage']) / 100;

                $calculations[] = [
                    'level' => $level['level_number'],
                    'level_name' => $level['level_name'],
                    'base_commission' => $base_commission,
                    'bonus_commission' => $bonus_commission,
                    'override_commission' => $override_commission,
                    'total_commission' => $base_commission + $bonus_commission + $override_commission
                ];
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'calculations' => $calculations,
                'total_potential' => array_sum(array_column($calculations, 'total_commission'))
            ]);
            return;
        }

        $this->render('associate/commission_calculator', [
            'associate_level' => $associate_level,
            'commission_rates' => $commission_rates,
            'page_title' => 'Commission Calculator - Associate Dashboard'
        ]);
    }

    /**
     * Plot Sales Analytics for Associates
     */
    public function salesAnalytics() {
        global $pdo;

        $associate_id = $_SESSION['associate_id'] ?? null;
        if (!$associate_id) {
            $this->redirect('/login');
            return;
        }

        // Get sales analytics data
        $analytics = [
            'total_sales' => 0,
            'total_commission' => 0,
            'monthly_sales' => [],
            'top_colonies' => [],
            'sales_trend' => [],
            'performance_metrics' => []
        ];

        try {
            // Total sales and commission
            $total_query = "
                SELECT COUNT(*) as total_sales, SUM(commission_earned) as total_commission
                FROM plot_sales
                WHERE associate_id = ?
            ";
            $stmt = $pdo->prepare($total_query);
            $stmt->execute([$associate_id]);
            $total_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $analytics['total_sales'] = $total_data['total_sales'] ?: 0;
            $analytics['total_commission'] = $total_data['total_commission'] ?: 0;

            // Monthly sales for last 12 months
            $monthly_query = "
                SELECT DATE_FORMAT(sale_date, '%Y-%m') as month,
                       COUNT(*) as sales_count,
                       SUM(commission_earned) as monthly_commission
                FROM plot_sales
                WHERE associate_id = ? AND sale_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
                ORDER BY month
            ";
            $stmt = $pdo->prepare($monthly_query);
            $stmt->execute([$associate_id]);
            $analytics['monthly_sales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Top performing colonies
            $colonies_query = "
                SELECT c.name as colony_name, COUNT(ps.id) as sales_count,
                       SUM(ps.commission_earned) as total_commission
                FROM plot_sales ps
                JOIN plots p ON ps.plot_id = p.id
                JOIN colonies c ON p.colony_id = c.id
                WHERE ps.associate_id = ?
                GROUP BY c.id, c.name
                ORDER BY sales_count DESC
                LIMIT 5
            ";
            $stmt = $pdo->prepare($colonies_query);
            $stmt->execute([$associate_id]);
            $analytics['top_colonies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Performance metrics
            $analytics['performance_metrics'] = [
                'average_commission_per_sale' => $analytics['total_sales'] > 0 ?
                    $analytics['total_commission'] / $analytics['total_sales'] : 0,
                'conversion_rate' => 0, // Would need leads data
                'monthly_target' => 50000, // Configurable target
                'achievement_percentage' => 0
            ];

            // Calculate achievement percentage
            $current_month_commission = 0;
            foreach ($analytics['monthly_sales'] as $month) {
                if ($month['month'] === date('Y-m')) {
                    $current_month_commission = $month['monthly_commission'];
                    break;
                }
            }
            $analytics['performance_metrics']['achievement_percentage'] =
                ($current_month_commission / $analytics['performance_metrics']['monthly_target']) * 100;

        } catch (Exception $e) {
            error_log('Sales analytics error: ' . $e->getMessage());
        }

        $this->render('associate/sales_analytics', [
            'analytics' => $analytics,
            'page_title' => 'Sales Analytics - Associate Dashboard'
        ]);
    }

    /**
     * Customer Referral System
     */
    public function customerReferrals() {
        global $pdo;

        $associate_id = $_SESSION['associate_id'] ?? null;
        if (!$associate_id) {
            $this->redirect('/login');
            return;
        }

        // Handle referral submission
        if (isset($_POST['submit_referral'])) {
            try {
                $referral_data = [
                    'associate_id' => $associate_id,
                    'customer_name' => $_POST['customer_name'],
                    'customer_phone' => $_POST['customer_phone'],
                    'customer_email' => $_POST['customer_email'] ?? '',
                    'preferred_location' => $_POST['preferred_location'],
                    'budget_range' => $_POST['budget_range'],
                    'plot_type' => $_POST['plot_type'],
                    'notes' => $_POST['notes'] ?? '',
                    'status' => 'new',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $insert_query = "
                    INSERT INTO customer_referrals
                    (associate_id, customer_name, customer_phone, customer_email,
                     preferred_location, budget_range, plot_type, notes, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = $pdo->prepare($insert_query);
                $stmt->execute([
                    $referral_data['associate_id'], $referral_data['customer_name'],
                    $referral_data['customer_phone'], $referral_data['customer_email'],
                    $referral_data['preferred_location'], $referral_data['budget_range'],
                    $referral_data['plot_type'], $referral_data['notes'],
                    $referral_data['status'], $referral_data['created_at']
                ]);

                $_SESSION['success_message'] = 'Referral submitted successfully!';
                $this->redirect('/associate/referrals');

            } catch (Exception $e) {
                $_SESSION['error_message'] = 'Error submitting referral: ' . $e->getMessage();
            }
        }

        // Get associate's referrals
        $referrals = [];
        try {
            $referrals_query = "
                SELECT * FROM customer_referrals
                WHERE associate_id = ?
                ORDER BY created_at DESC
            ";
            $stmt = $pdo->prepare($referrals_query);
            $stmt->execute([$associate_id]);
            $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Referrals fetch error: ' . $e->getMessage());
        }

        $this->render('associate/customer_referrals', [
            'referrals' => $referrals,
            'page_title' => 'Customer Referrals - Associate Dashboard'
        ]);
    }

    /**
     * Plot Booking & Hold System
     */
    public function plotBooking() {
        global $pdo;

        $associate_id = $_SESSION['associate_id'] ?? null;
        if (!$associate_id) {
            $this->redirect('/login');
            return;
        }

        // Handle plot booking
        if (isset($_POST['book_plot'])) {
            try {
                $plot_id = $_POST['plot_id'];
                $booking_type = $_POST['booking_type']; // 'hold' or 'book'
                $customer_name = $_POST['customer_name'];
                $customer_phone = $_POST['customer_phone'];
                $notes = $_POST['notes'] ?? '';

                // Check if plot is available
                $check_query = "SELECT status, allocated_to FROM plots WHERE id = ?";
                $stmt = $pdo->prepare($check_query);
                $stmt->execute([$plot_id]);
                $plot = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$plot || $plot['status'] !== 'available') {
                    throw new Exception('Plot not available for booking');
                }

                // Create booking
                $booking_data = [
                    'associate_id' => $associate_id,
                    'plot_id' => $plot_id,
                    'customer_name' => $customer_name,
                    'customer_phone' => $customer_phone,
                    'booking_type' => $booking_type,
                    'status' => 'active',
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')), // 7 days hold
                    'notes' => $notes,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $insert_query = "
                    INSERT INTO plot_bookings
                    (associate_id, plot_id, customer_name, customer_phone, booking_type,
                     status, expires_at, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = $pdo->prepare($insert_query);
                $stmt->execute([
                    $booking_data['associate_id'], $booking_data['plot_id'],
                    $booking_data['customer_name'], $booking_data['customer_phone'],
                    $booking_data['booking_type'], $booking_data['status'],
                    $booking_data['expires_at'], $booking_data['notes'],
                    $booking_data['created_at']
                ]);

                // Update plot status
                $update_query = "UPDATE plots SET status = ? WHERE id = ?";
                $stmt = $pdo->prepare($update_query);
                $stmt->execute([$booking_type === 'book' ? 'booked' : 'on_hold', $plot_id]);

                $_SESSION['success_message'] = 'Plot ' . $booking_type . 'ed successfully!';
                $this->redirect('/associate/plot-booking');

            } catch (Exception $e) {
                $_SESSION['error_message'] = 'Error booking plot: ' . $e->getMessage();
            }
        }

        // Get associate's bookings
        $bookings = [];
        try {
            $bookings_query = "
                SELECT pb.*, p.plot_number, p.plot_area, c.name as colony_name,
                       c.location as colony_location
                FROM plot_bookings pb
                JOIN plots p ON pb.plot_id = p.id
                JOIN colonies c ON p.colony_id = c.id
                WHERE pb.associate_id = ?
                ORDER BY pb.created_at DESC
            ";
            $stmt = $pdo->prepare($bookings_query);
            $stmt->execute([$associate_id]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Bookings fetch error: ' . $e->getMessage());
        }

        // Get available plots for booking
        $available_plots = [];
        try {
            $plots_query = "
                SELECT p.*, c.name as colony_name, c.location as colony_location,
                       c.price_per_sqft
                FROM plots p
                JOIN colonies c ON p.colony_id = c.id
                WHERE p.status = 'available' AND p.allocated_to = ?
                ORDER BY c.name, p.plot_number
                LIMIT 50
            ";
            $stmt = $pdo->prepare($plots_query);
            $stmt->execute([$associate_id]);
            $available_plots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Available plots fetch error: ' . $e->getMessage());
        }

        $this->render('associate/plot_booking', [
            'bookings' => $bookings,
            'available_plots' => $available_plots,
            'page_title' => 'Plot Booking - Associate Dashboard'
        ]);
    }

    /**
     * Release/Cancel Plot Booking
     */
    public function releaseBooking($booking_id) {
        global $pdo;

        $associate_id = $_SESSION['associate_id'] ?? null;
        if (!$associate_id) {
            $this->redirect('/login');
            return;
        }

        try {
            // Get booking details
            $booking_query = "
                SELECT pb.*, p.id as plot_id
                FROM plot_bookings pb
                JOIN plots p ON pb.plot_id = p.id
                WHERE pb.id = ? AND pb.associate_id = ?
            ";
            $stmt = $pdo->prepare($booking_query);
            $stmt->execute([$booking_id, $associate_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('Booking not found or not authorized');
            }

            // Update booking status
            $update_booking = "UPDATE plot_bookings SET status = 'cancelled' WHERE id = ?";
            $stmt = $pdo->prepare($update_booking);
            $stmt->execute([$booking_id]);

            // Release plot back to available
            $update_plot = "UPDATE plots SET status = 'available' WHERE id = ?";
            $stmt = $pdo->prepare($update_plot);
            $stmt->execute([$booking['plot_id']]);

            $_SESSION['success_message'] = 'Plot booking released successfully!';
            $this->redirect('/associate/plot-booking');

        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error releasing booking: ' . $e->getMessage();
            $this->redirect('/associate/plot-booking');
        }
    }
}
