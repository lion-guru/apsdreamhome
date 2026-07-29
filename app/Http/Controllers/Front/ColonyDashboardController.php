<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;

class ColonyDashboardController extends BaseController
{
    use TenantAwareTrait;

    private $firebaseConfig;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/base';
        $this->db = Database::getInstance()->getConnection();
        $configPath = __DIR__ . '/../../../Config/firebase.php';
        $this->firebaseConfig = file_exists($configPath) ? require $configPath : ['client' => []];
    }

    /**
     * Skip CSRF for API endpoints (sync-booking, bookings list)
     * These are called from Firebase webhooks and the iframe.
     */
    protected function skipCsrfProtection(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api/colony/') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Raghunath Nagri Block C - Interactive Booking Dashboard
     * Real-time Firebase sync for field team + admin
     */
    public function raghunathBlockC()
    {
        // Get colony data from MySQL for reference
        $colony = $this->db->prepare("
            SELECT * FROM colonies WHERE slug = 'raghunath-nagri' AND is_active = 1 LIMIT 1
        ");
        $colony->execute();
        $colony = $colony->fetch(\PDO::FETCH_ASSOC);

        if (!$colony) {
            $this->render('pages/404', ['page_title' => 'Colony Not Found']);
            return;
        }

        // Get existing plots from MySQL for Block C
        $plots = $this->db->prepare("
            SELECT p.*, 
                   CASE WHEN pb.id IS NOT NULL THEN 'booked' ELSE p.status END as current_status,
                   u.name as customer_name, u.phone as customer_phone, pb.booking_date as booked_at
            FROM plots p
            LEFT JOIN plot_bookings pb ON p.id = pb.plot_id AND pb.status IN ('token_paid', 'agreement_signed', 'emi_active', 'partially_paid')
            LEFT JOIN users u ON pb.customer_id = u.id
            WHERE p.colony_id = ? AND p.block = 'C' AND p.is_active = 1
            ORDER BY p.plot_number
        ");
        $plots->execute([$colony['id']]);
        $plots = $plots->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate stats
        $stats = [
            'total' => count($plots),
            'available' => count(array_filter($plots, fn($p) => $p['current_status'] === 'available')),
            'booked' => count(array_filter($plots, fn($p) => $p['current_status'] === 'booked')),
            'corners' => count(array_filter($plots, fn($p) => stripos($p['plot_number'], 'CNR') !== false)),
            'row_a' => count(array_filter($plots, fn($p) => stripos($p['plot_number'], 'C-A') === 0)),
            'row_b' => count(array_filter($plots, fn($p) => stripos($p['plot_number'], 'C-B') === 0)),
            'row_w' => count(array_filter($plots, fn($p) => stripos($p['plot_number'], 'C-W') === 0)),
        ];

        // Firebase config for frontend
        $firebaseConfig = $this->firebaseConfig['client'];

        $data = [
            'page_title' => 'Raghunath Nagri Block C - Live Booking Dashboard',
            'page_description' => 'Real-time interactive booking suite for Block C plots. Field team & admin live sync.',
            'colony' => $colony,
            'plots' => $plots,
            'stats' => $stats,
            'firebase' => $firebaseConfig,
            'app_id' => $this->firebaseConfig['client']['appId'] ?? 'aps-dream-homes',
        ];

        $this->render('pages/colony/raghunath_block_c_dashboard', $data);
    }

    /**
     * API endpoint: Sync Firebase booking to MySQL (called via webhook or cron)
     */
    public function syncBookingFromFirebase()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $plotId = $input['plotId'] ?? '';
        $name = $input['name'] ?? '';
        $phone = $input['phone'] ?? '';
        $timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');

        if (!$plotId || !$name || !$phone) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        try {
            // Find plot in MySQL by plot_number (e.g., "RN-C-001", "C-1")
            $stmt = $this->db->prepare("
                SELECT p.id, p.colony_id, p.total_price, p.plot_number
                FROM plots p
                WHERE p.plot_number = ? AND p.block = 'C' AND p.is_active = 1
            ");
            $stmt->execute([$plotId]);
            $plot = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$plot) {
                http_response_code(404);
                echo json_encode(['error' => 'Plot not found in MySQL']);
                return;
            }

            // Check if booking already exists
            $existing = $this->db->prepare("
                SELECT id FROM plot_bookings WHERE plot_id = ? AND status NOT IN ('cancelled')
            ");
            $existing->execute([$plot['id']]);
            if ($existing->fetch()) {
                echo json_encode(['success' => true, 'message' => 'Booking already exists']);
                return;
            }

            // Find or create customer user
            $user = $this->db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
            $user->execute([$phone]);
            $user = $user->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $cols = "name, phone, role, tenant_id, created_at";
                $vals = "?, ?, 'customer', ?, NOW()";
                $params = [$name, $phone, $this->tenantId()];
                $stmt = $this->db->prepare("INSERT INTO users ($cols) VALUES ($vals)");
                $stmt->execute($params);
                $customerId = $this->db->lastInsertId();
            } else {
                $customerId = $user['id'];
            }

            // Create booking in MySQL
            $bookingNumber = 'BK' . date('Ymd') . strtoupper(substr(md5($plotId . $phone), 0, 6));
            $stmt = $this->db->prepare("
                INSERT INTO plot_bookings (plot_id, customer_id, booking_number, booking_date, total_plot_value, booking_amount, status, approval_status, channel, notes, tenant_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'token_paid', 'pending', 'direct', ?, ?, NOW())
            ");
            $stmt->execute([
                $plot['id'], $customerId, $bookingNumber,
                date('Y-m-d', strtotime($timestamp)),
                $plot['total_price'] ?? 0,
                $plot['total_price'] ?? 0,
                json_encode(['source' => 'firebase_dashboard', 'firebase_timestamp' => $timestamp]),
                $this->tenantId()
            ]);

            // Update plot status
            $stmt = $this->db->prepare("UPDATE plots SET status = 'booked', customer_id = ?, booking_date = ? WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$customerId, date('Y-m-d', strtotime($timestamp)), $plot['id'], $this->tenantId()]);

            // Log for audit
            error_log("Firebase booking synced: Plot {$plotId}, Customer {$name}, Phone {$phone}, Booking {$bookingNumber}");

            echo json_encode([
                'success' => true, 
                'booking_id' => $bookingNumber,
                'message' => 'Booking synced to MySQL successfully'
            ]);

        } catch (\Exception $e) {
            error_log("Firebase sync error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Get all Block C bookings (for admin panel)
     */
    public function getBlockCBookings()
    {
        header('Content-Type: application/json');
        
        $colony = $this->db->prepare("SELECT id FROM colonies WHERE slug = 'raghunath-nagri' LIMIT 1");
        $colony->execute();
        $colony = $colony->fetch(\PDO::FETCH_ASSOC);

        if (!$colony) {
            echo json_encode([]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT pb.*, p.plot_number, p.block, p.area_sqft, p.total_price,
                   u.name as customer_name, u.phone as customer_phone
            FROM plot_bookings pb
            JOIN plots p ON pb.plot_id = p.id
            LEFT JOIN users u ON pb.customer_id = u.id
            WHERE p.colony_id = ? AND p.block = 'C'
            ORDER BY pb.created_at DESC
        ");
        $stmt->execute([$colony['id']]);
        $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode($bookings);
    }
}