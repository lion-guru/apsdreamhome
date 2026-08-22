<?php
namespace App\Http\Controllers\Front;

use App\Core\Database\Database;
use App\Http\Controllers\BaseController;
use App\Services\Accounting\AccountingIntegrationService;
use App\Services\Booking\BookingComplianceService;
use App\Services\Notification\BookingNotificationService;

class PlotController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    protected $db;

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
        $userType = $_SESSION['role'] ?? '';
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
        $tid = (int)$this->tenantId();
        $tidSql = $tid > 1 ? ' AND p.tenant_id = ?' : '';
        $tidParam = $tid > 1 ? [$tid] : [];
        $colonies = $this->db->fetchAll("
            SELECT c.*, d.name as district_name, s.name as state_name,
                (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status = 'available'" . $tidSql . ") as available_plots
            FROM colonies c
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE c.is_active = 1
            ORDER BY c.name
        ", $tidParam);

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
        $tid = (int)$this->tenantId();
        if ($tid > 1) { $where .= " AND p.tenant_id = ?"; $params[] = $tid; }

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
        $tidPlots = $tid > 1 ? ' AND tenant_id = ?' : '';
        $tidPlotParam = $tid > 1 ? [$tid] : [];
        $dimensions = $this->db->fetchAll("SELECT DISTINCT dimension_label FROM plots WHERE colony_id = ? AND dimension_label IS NOT NULL AND dimension_label != ''" . $tidPlots . " ORDER BY dimension_label", array_merge([$colony['id']], $tidPlotParam));
        $blocks = $this->db->fetchAll("SELECT DISTINCT block FROM plots WHERE colony_id = ? AND block IS NOT NULL AND block != ''" . $tidPlots . " ORDER BY block", array_merge([$colony['id']], $tidPlotParam));

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
            FROM plots WHERE colony_id = ? AND is_active = 1" . $tidPlots . "
        ", array_merge([$colony['id']], $tidPlotParam));

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
        $tid = (int)$this->tenantId();
        $plotParams = [$plotId];
        if ($tid > 1) { $plotParams[] = $tid; }
        $plot = $this->db->fetchRow("SELECT * FROM plots WHERE id = ? AND is_active = 1 AND status = 'available'" . ($tid > 1 ? ' AND tenant_id = ?' : ''), $plotParams);
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
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // 2. Update plot status to hold
            $this->db->execute("UPDATE plots SET status = 'hold' WHERE id = ? AND tenant_id = ?", [$plot['id'], $tid]);

            // 3. Create initial EMI schedule (25% token within 15 days)
            $tokenAmount = $dealPrice * 0.25;
            $this->db->insert('booking_emis', [
                'booking_id' => $bookingId,
                'installment_no' => 1,
                'due_date' => date('Y-m-d', strtotime('+15 days')),
                'amount' => $tokenAmount,
                'status' => 'pending',
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // 4. Set flash and redirect
            $this->db->commit();

            $notifService = new BookingNotificationService();
            $notifService->ensureNotificationsTable();
            $notifService->notifyBookingCreated($bookingId, ['id' => $bookingId], $user);

            $this->setFlash('success', 'Booking request submitted! Pay the 25% token amount (₹' . number_format($tokenAmount, 2) . ') to confirm your booking.');
            return $this->redirect('/booking/' . $bookingId . '/pay');
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
                   p.corner_plot, p.park_facing, c.name as colony_name,
                   d.name as district_name, s.name as state_name
            FROM bookings b
            LEFT JOIN plots p ON b.plot_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE b.id = ? AND b.customer_id = ?
        ", [$bookingId, $user['id']]);

        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/user/dashboard');
        }

        try {
            $emis = $this->db->fetchAll("SELECT * FROM booking_emis WHERE booking_id = ? ORDER BY installment_no", [$bookingId]);
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/booking_confirmation', [
            'page_title' => 'Booking Confirmation #' . $bookingId,
            'booking' => $booking,
            'emis' => $emis,
            'user' => $user,
        ]);
    }

    /**
     * Printable booking receipt
     */
    public function receipt($bookingId)
    {
        try {
            @session_start();
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            $stmt = $this->db->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$user) {
                header('Location: ' . BASE_URL . '/user/logout');
                exit;
            }

            $booking = $this->db->fetchRow("
                SELECT b.*, p.plot_number, p.block, p.area_sqft, p.dimension_label, p.total_price as plot_price,
                       p.corner_plot, p.park_facing, c.name as colony_name,
                       d.name as district_name, s.name as state_name
                FROM bookings b
                LEFT JOIN plots p ON b.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN states s ON d.state_id = s.id
                WHERE b.id = ? AND b.customer_id = ?
            ", [$bookingId, $user['id']]);

            if (!$booking) {
                echo '<h2>Booking not found</h2><a href="' . BASE_URL . '/user/dashboard">Back to Dashboard</a>';
                exit;
            }

            try {
                $emis = $this->db->fetchAll("SELECT * FROM booking_emis WHERE booking_id = ? ORDER BY installment_no", [$bookingId]);
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            $currentStatus = $booking['status'] ?? 'pending';

            $viewFile = __DIR__ . '/../../views/pages/booking_receipt.php';
            if (file_exists($viewFile)) {
                require $viewFile;
            } else {
                echo '<h2>View file not found</h2>';
            }
        } catch (\Throwable $e) {
            echo '<h2>Error: ' . htmlspecialchars($e->getMessage()) . '</h2>';
            echo '<p>File: ' . $e->getFile() . ':' . $e->getLine() . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
        exit;
    }

    /**
     * Show payment form for booking token amount
     */
    public function payBooking($bookingId)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $booking = $this->db->fetchRow("
            SELECT b.*, p.plot_number, p.block, p.area_sqft, p.dimension_label, p.total_price as plot_price,
                   c.name as colony_name
            FROM bookings b
            LEFT JOIN plots p ON b.plot_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            WHERE b.id = ? AND b.customer_id = ?
        ", [$bookingId, $user['id']]);

        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/user/dashboard');
        }

        $requiredToken = (float)$booking['total_amount'] * 0.25;
        $paidSoFar = (float)$booking['amount'];
        $tokenDue = max(0, $requiredToken - $paidSoFar);
        $tokenPercent = $requiredToken > 0 ? min(100, round(($paidSoFar / $requiredToken) * 100)) : 0;

        $this->layout = 'layouts/customer';
        $this->render('pages/booking_pay', [
            'page_title' => 'Pay Token - Booking #' . $bookingId,
            'booking' => $booking,
            'requiredToken' => $requiredToken,
            'tokenDue' => $tokenDue,
            'tokenPercent' => $tokenPercent,
            'user' => $user,
        ]);
    }

    /**
     * Process token payment from customer
     */
    public function processPayment($bookingId)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/booking/' . $bookingId . '/pay');
        }

        $booking = $this->db->fetchRow("SELECT * FROM bookings WHERE id = ? AND customer_id = ?", [$bookingId, $user['id']]);
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/user/dashboard');
        }

        $amount = (float)($_POST['amount'] ?? 0);
        $mode = $_POST['mode'] ?? 'online';
        $reference = $_POST['reference'] ?? '';
        $tid = (int)$this->tenantId();

        if ($amount <= 0) {
            $this->setFlash('error', 'Invalid payment amount');
            return $this->redirect('/booking/' . $bookingId . '/pay');
        }

        $requiredToken = (float)$booking['total_amount'] * 0.25;
        $paidSoFar = (float)$booking['amount'];
        if (($paidSoFar + $amount) > $requiredToken) {
            $this->setFlash('error', 'Amount exceeds required token. Maximum: ₹' . number_format($requiredToken - $paidSoFar, 2));
            return $this->redirect('/booking/' . $bookingId . '/pay');
        }

        $this->db->beginTransaction();
        try {
            $newPaid = $paidSoFar + $amount;
            $paymentStatus = ($newPaid >= $requiredToken) ? 'partial' : 'pending';

            $this->db->execute("UPDATE bookings SET amount = ?, payment_status = ? WHERE id = ? AND tenant_id = ?", [$newPaid, $paymentStatus, $bookingId, $tid]);

            $this->db->insert('booking_emis', [
                'booking_id' => $bookingId,
                'installment_no' => 0,
                'due_date' => date('Y-m-d'),
                'amount' => $amount,
                'paid_amount' => $amount,
                'paid_date' => date('Y-m-d'),
                'payment_mode' => $mode,
                'transaction_ref' => $reference,
                'status' => 'paid',
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->commit();

            $notifService = new BookingNotificationService();
            $notifService->ensureNotificationsTable();
            $notifService->notifyPaymentReceived($bookingId, $booking, $amount, $mode);

            $this->setFlash('success', 'Payment of ₹' . number_format($amount, 2) . ' received successfully!');
            return $this->redirect('/booking/' . $bookingId . '/confirmation');
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->setFlash('error', 'Payment failed: ' . $e->getMessage());
            return $this->redirect('/booking/' . $bookingId . '/pay');
        }
    }
}
