<?php
/**
 * Customer Dashboard Controller
 * Shows plots, bookings, EMI, payment history
 */

namespace App\Http\Controllers;

require_once __DIR__ . '/BaseController.php';

use App\Core\Database\Database;

class CustomerDashboardController extends BaseController
{
    public function dashboard()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Auth check
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $customer_name = $_SESSION['user_name'] ?? 'Customer';

        try {
            $db = Database::getInstance();

            // Get customer's bookings/properties
            $bookings = $db->fetchAll("SELECT b.*, p.title as property_name, p.location, p.price FROM bookings b LEFT JOIN properties p ON b.property_id = p.id WHERE b.customer_id = ? ORDER BY b.created_at DESC", [$user_id]) ?? [];

            // Get EMI schedule
            $emi_schedule = $db->fetchAll("SELECT * FROM emi_schedule WHERE customer_id = ? ORDER BY due_date ASC", [$user_id]) ?? [];

            // Get payment history
            $payment_history = $db->fetchAll("SELECT * FROM payments WHERE customer_id = ? ORDER BY created_at DESC LIMIT 10", [$user_id]) ?? [];

            // Stats
            $stats = [
                'properties' => count($bookings),
                'bookings' => count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'active')),
                'pending_emi' => count(array_filter($emi_schedule, fn($e) => ($e['status'] ?? '') === 'pending')),
                'total_investment' => array_sum(array_column($bookings, 'amount'))
            ];

        } catch (\Exception $e) {
            error_log("Customer dashboard error: " . $e->getMessage());
            $bookings = [];
            $emi_schedule = [];
            $payment_history = [];
            $stats = ['properties' => 0, 'bookings' => 0, 'pending_emi' => 0, 'total_investment' => 0];
        }

        // Render
        $this->layout = false;
        $data = compact('customer_name', 'bookings', 'emi_schedule', 'payment_history', 'stats');
        $data['page_title'] = 'My Dashboard';

        ob_start();
        extract($data);
        $viewPath = __DIR__ . '/../../../views/pages/customer_dashboard_standalone.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "<h1>Customer Dashboard</h1><p>Welcome, $customer_name!</p>";
        }
        echo ob_get_clean();
    }
}
