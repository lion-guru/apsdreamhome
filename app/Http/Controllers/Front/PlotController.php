<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Core\Database;
use App\Services\Accounting\AccountingIntegrationService;

class PlotController extends Controller
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    private function requireCustomerLogin()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $userType = $_SESSION['user_type'] ?? '';
        if ($userType !== '' && $userType !== 'customer') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    private function getUser()
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$user) {
            header('Location: ' . BASE_URL . '/user/logout');
            exit;
        }
        return $user;
    }

    /**
     * List all colonies with available plots
     */
    public function index()
    {
        $colonies = $this->db->fetchAll("
            SELECT c.*, d.name as district_name, s.name as state_name,
                (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status = 'available') as available_plots
            FROM colonies c
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE c.is_active = 1
            ORDER BY c.name
        ");

        $this->render('pages/plots', [
            'page_title' => 'Available Plots - APS Dream Home',
            'meta_description' => 'Browse available residential and commercial plots for sale in Gorakhpur, Deoria, Kushinagar, and Varanasi.',
            'colonies' => $colonies,
        ]);
    }

    /**
     * Show available plots in a specific colony with filters
     */
    public function colonyPlots($slug)
    {
        $colony = $this->db->fetchRow("
            SELECT c.*, d.name as district_name, s.name as state_name
            FROM colonies c
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE c.slug = ? AND c.is_active = 1
        ", [$slug]);

        if (!$colony) {
            return $this->redirect('/plots');
        }

        // Build filter query
        $where = "p.colony_id = ? AND p.is_active = 1";
        $params = [$colony['id']];

        // Status filter
        $status = $_GET['status'] ?? 'available';
        if (in_array($status, ['available', 'booked', 'sold', 'hold', 'reserved'])) {
            $where .= " AND p.status = ?";
            $params[] = $status;
        }

        // Dimension filter (20x40, 30x50, etc.)
        $dimension = $_GET['dimension'] ?? '';
        if ($dimension && preg_match('/^\d+x\d+$/', $dimension)) {
            $where .= " AND p.dimension_label = ?";
            $params[] = $dimension;
        }

        // Block filter
        $block = $_GET['block'] ?? '';
        if ($block) {
            $where .= " AND p.block = ?";
            $params[] = $block;
        }

        // Price range filter
        $minPrice = floatval($_GET['min_price'] ?? 0);
        $maxPrice = floatval($_GET['max_price'] ?? 0);
        if ($minPrice > 0) {
            $where .= " AND p.total_price >= ?";
            $params[] = $minPrice;
        }
        if ($maxPrice > 0) {
            $where .= " AND p.total_price <= ?";
            $params[] = $maxPrice;
        }

        // Area range filter
        $minArea = floatval($_GET['min_area'] ?? 0);
        $maxArea = floatval($_GET['max_area'] ?? 0);
        if ($minArea > 0) {
            $where .= " AND p.area_sqft >= ?";
            $params[] = $minArea;
        }
        if ($maxArea > 0) {
            $where .= " AND p.area_sqft <= ?";
            $params[] = $maxArea;
        }

        // Sorting
        $sort = $_GET['sort'] ?? 'plot_number';
        $allowedSorts = ['plot_number' => 'p.plot_number', 'price_asc' => 'p.total_price ASC', 'price_desc' => 'p.total_price DESC', 'area_asc' => 'p.area_sqft ASC', 'area_desc' => 'p.area_sqft DESC'];
        $orderBy = $allowedSorts[$sort] ?? 'p.plot_number';

        $sql = "SELECT p.* FROM plots p WHERE $where ORDER BY p.block, $orderBy";
        $plots = $this->db->fetchAll($sql, $params);

        // Get distinct dimensions, blocks for filters
        $dimensions = $this->db->fetchAll("SELECT DISTINCT dimension_label FROM plots WHERE colony_id = ? AND dimension_label IS NOT NULL AND dimension_label != '' ORDER BY dimension_label", [$colony['id']]);
        $blocks = $this->db->fetchAll("SELECT DISTINCT block FROM plots WHERE colony_id = ? AND block IS NOT NULL AND block != '' ORDER BY block", [$colony['id']]);

        // Stats
        $stats = $this->db->fetchRow("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
                SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                MIN(total_price) as min_price,
                MAX(total_price) as max_price,
                MIN(area_sqft) as min_area,
                MAX(area_sqft) as max_area
            FROM plots WHERE colony_id = ? AND is_active = 1
        ", [$colony['id']]);

        $this->render('pages/colony_plots', [
            'page_title' => $colony['name'] . ' - Available Plots',
            'meta_description' => $colony['meta_description'] ?: "Browse available plots in {$colony['name']}, {$colony['district_name']}",
            'colony' => $colony,
            'plots' => $plots,
            'dimensions' => $dimensions,
            'blocks' => $blocks,
            'stats' => $stats,
            'current_status' => $status,
            'current_dimension' => $dimension,
            'current_block' => $block,
            'current_sort' => $sort,
            'current_min_price' => $minPrice,
            'current_max_price' => $maxPrice,
            'current_min_area' => $minArea,
            'current_max_area' => $maxArea,
        ]);
    }

    /**
     * Show single plot detail (public view)
     */
    public function show($id)
    {
        $plot = $this->db->fetchRow("
            SELECT p.*, c.name as colony_name, c.slug as colony_slug, c.description as colony_description,
                c.amenities, c.nearby_places, c.gallery_images, c.map_link, c.image_path as colony_image,
                d.name as district_name, s.name as state_name
            FROM plots p
            JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE p.id = ? AND p.is_active = 1
        ", [$id]);

        if (!$plot) {
            return $this->redirect('/plots');
        }

        // Price history
        $priceHistory = $this->db->fetchAll("
            SELECT * FROM price_history WHERE plot_id = ? ORDER BY created_at DESC LIMIT 10
        ", [$id]);

        // Nearby plots in same block
        $nearbyPlots = $this->db->fetchAll("
            SELECT id, plot_number, block, area_sqft, total_price, dimension_label, status
            FROM plots WHERE colony_id = ? AND block = ? AND id != ? AND is_active = 1
            LIMIT 6
        ", [$plot['colony_id'], $plot['block'], $id]);

        $this->render('pages/plot_detail', [
            'page_title' => "Plot {$plot['plot_number']} - {$plot['colony_name']}",
            'meta_description' => "{$plot['dimension_label']} plot in {$plot['colony_name']}, {$plot['district_name']}. Area: {$plot['area_sqft']} sqft, Price: ₹{$plot['total_price']}.",
            'plot' => $plot,
            'priceHistory' => $priceHistory,
            'nearbyPlots' => $nearbyPlots,
        ]);
    }

    /**
     * API: Get plots by colony (JSON for AJAX loading)
     */
    public function apiByColony($colonyId)
    {
        $status = $_GET['status'] ?? 'available';
        $block = $_GET['block'] ?? '';
        
        $where = "colony_id = ? AND is_active = 1";
        $params = [$colonyId];
        
        if (in_array($status, ['available', 'booked', 'sold'])) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        if ($block) {
            $where .= " AND block = ?";
            $params[] = $block;
        }
        
        $plots = $this->db->fetchAll("SELECT id, plot_number, block, dimension_label, area_sqft, base_price_per_sqft, total_price, status, corner_plot, park_facing FROM plots WHERE $where ORDER BY block, plot_number", $params);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'plots' => $plots]);
        exit;
    }

    /**
     * Show booking form for a specific plot
     */
    public function bookPlot($plotId)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $plot = $this->db->fetchRow("
            SELECT p.*, c.name as colony_name, c.slug as colony_slug,
                   d.name as district_name, s.name as state_name
            FROM plots p
            JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE p.id = ? AND p.is_active = 1
        ", [$plotId]);

        if (!$plot || $plot['status'] !== 'available') {
            $this->setFlash('error', 'This plot is not available for booking');
            return $this->redirect('/colony/' . ($plot['colony_slug'] ?? '') . '/plots');
        }

        $userBookings = $this->db->fetchAll("SELECT * FROM bookings WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5", [$user['id']]);

        $this->layout = 'layouts/customer';
        $this->render('pages/plot_booking', [
            'page_title' => 'Book Plot - ' . $plot['plot_number'] . ' - ' . $plot['colony_name'],
            'plot' => $plot,
            'user' => $user,
            'userBookings' => $userBookings,
        ]);
    }

    /**
     * Process booking form submission
     */
    public function storeBooking()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/plots');
        }

        $plotId = intval($_POST['plot_id'] ?? 0);
        $plot = $this->db->fetchRow("SELECT * FROM plots WHERE id = ? AND is_active = 1 AND status = 'available'", [$plotId]);
        if (!$plot) {
            $this->setFlash('error', 'Plot not found or no longer available');
            return $this->redirect('/plots');
        }

        // Determine final price (negotiated or total)
        $dealPrice = floatval($_POST['negotiated_price'] ?? $plot['negotiated_price'] ?? $plot['total_price']);
        if ($dealPrice <= 0) $dealPrice = floatval($plot['total_price']);

        $this->db->beginTransaction();
        try {
            // 1. Create booking
            $bookingId = $this->db->insert('bookings', [
                'customer_id' => $user['id'],
                'plot_id' => $plot['id'],
                'colony_id' => $plot['colony_id'],
                'booking_number' => 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'booking_type' => $_POST['booking_type'] ?? 'online_consultation',
                'booking_date' => date('Y-m-d'),
                'status' => 'pending',
                'payment_status' => 'pending',
                'total_amount' => $dealPrice,
                'amount' => 0,
                'negotiated_price' => $dealPrice,
                'notes' => $_POST['notes'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // 2. Update plot status to hold
            $this->db->execute("UPDATE plots SET status = 'hold' WHERE id = ?", [$plot['id']]);

            // 3. Create initial EMI schedule (25% token within 15 days)
            $tokenAmount = $dealPrice * 0.25;
            $this->db->insert('booking_emis', [
                'booking_id' => $bookingId,
                'installment_no' => 1,
                'due_date' => date('Y-m-d', strtotime('+15 days')),
                'amount' => $tokenAmount,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // 4. Set flash and redirect
            $this->setFlash('success', 'Booking request submitted successfully! Booking #' . $bookingId . '. Please complete the 25% token payment within 15 days.');
            $this->db->commit();
            return $this->redirect('/user/dashboard?booking=' . $bookingId);
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->setFlash('error', 'Booking failed: ' . $e->getMessage());
            return $this->redirect('/plot/' . $plotId . '/book');
        }
    }

    /**
     * Show booking confirmation / payment page
     */
    public function bookingConfirmation($bookingId)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $booking = $this->db->fetchRow("
            SELECT b.*, p.plot_number, p.block, p.area_sqft, p.dimension_label, p.total_price as plot_price,
                   p.corner_plot, p.park_facing, c.name as colony_name
            FROM bookings b
            LEFT JOIN plots p ON b.plot_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            WHERE b.id = ? AND b.customer_id = ?
        ", [$bookingId, $user['id']]);

        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/user/dashboard');
        }

        $emis = $this->db->fetchAll("SELECT * FROM booking_emis WHERE booking_id = ? ORDER BY installment_no", [$bookingId]);

        $this->layout = 'layouts/customer';
        $this->render('pages/booking_confirmation', [
            'page_title' => 'Booking Confirmation #' . $bookingId,
            'booking' => $booking,
            'emis' => $emis,
            'user' => $user,
        ]);
    }
}
