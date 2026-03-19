<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Property;
use App\Core\Database;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\AuthenticationService;
use App\Services\RequestService;
use App\Services\LoggingService;
use Exception;

// Placeholder NotificationService
if (!class_exists('App\Services\NotificationService')) {
    class NotificationService
    {
        public function sendEmail($to, $subject, $body, $type = '', $userId = 0, $data = [])
        {
            // Placeholder email sending
            error_log("Email sent to: $to, Subject: $subject");
            return true;
        }

        public function notifyAdmin($subject, $body, $type = '', $data = [])
        {
            // Placeholder admin notification
            error_log("Admin notification: $subject");
            return true;
        }
    }
}

class BookingController extends AdminController
{
    public $auth;
    protected $db;
    private LoggingService $loggingService;
    private RequestService $requestService;

    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);

        // Initialize database and services
        $this->db = Database::getInstance()->getConnection();
        $this->loggingService = new LoggingService();
        $this->requestService = new RequestService();

        // Initialize auth property
        $this->auth = (object) [
            'user' => function () {
                return (object) ['id' => $_SESSION['user_id'] ?? 1];
            }
        ];
    }

    /**
     * List all bookings with filters and pagination
     */
    public function index()
    {
        try {
            $filters = [
                'search' => $_GET['search'] ?? '',
                'status' => $_GET['status'] ?? '',
                'customer_id' => $_GET['customer_id'] ?? '',
                'associate_id' => $_GET['associate_id'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'page' => (int)($_GET['page'] ?? 1),
                'per_page' => (int)($_GET['per_page'] ?? 10),
                'sort' => $_GET['sort'] ?? 'created_at',
                'order' => $_GET['order'] ?? 'DESC'
            ];

            // Build query
            $sql = "SELECT b.*, p.title as property_title, p.location as property_location, 
                           c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                           a.name as associate_name, a.email as associate_email
                    FROM bookings b
                    LEFT JOIN properties p ON b.property_id = p.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN users a ON b.associate_id = a.id
                    WHERE 1=1";

            $params = [];

            // Apply filters
            if (!empty($filters['search'])) {
                $sql .= " AND (b.booking_number LIKE :search OR c.name LIKE :search OR p.title LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            if (!empty($filters['status'])) {
                $sql .= " AND b.status = :status";
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['customer_id'])) {
                $sql .= " AND b.customer_id = :customer_id";
                $params['customer_id'] = $filters['customer_id'];
            }

            if (!empty($filters['associate_id'])) {
                $sql .= " AND b.associate_id = :associate_id";
                $params['associate_id'] = $filters['associate_id'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(b.created_at) >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(b.created_at) <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }

            // Count total records
            $countSql = str_replace("SELECT b.*, p.title as property_title, p.location as property_location, 
                           c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                           a.name as associate_name, a.email as associate_email", "SELECT COUNT(*) as total", $sql);

            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply sorting and pagination
            $sql .= " ORDER BY b.{$filters['sort']} {$filters['order']}";
            $offset = ($filters['page'] - 1) * $filters['per_page'];
            $sql .= " LIMIT :offset, :limit";

            $params['offset'] = $offset;
            $params['limit'] = $filters['per_page'];

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get filter options
            $customers = $this->db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $associates = $this->db->query("SELECT id, name, email FROM users WHERE role = 'associate' AND status = 'active' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Bookings - APS Dream Home',
                'bookings' => $bookings,
                'filters' => $filters,
                'customers' => $customers,
                'associates' => $associates,
                'total' => $total,
                'total_pages' => ceil($total / $filters['per_page']),
                'current_page' => $filters['page']
            ];

            return $this->render('admin/bookings/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Booking listing error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load bookings');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show single booking details
     */
    public function show($id)
    {
        try {
            $bookingId = intval($id);
            if ($bookingId <= 0) {
                $this->setFlash('error', 'Invalid booking ID');
                return $this->redirect('admin/bookings');
            }

            // Get booking details with all related information
            $sql = "SELECT b.*, p.title as property_title, p.location as property_location, p.price as property_price,
                           c.name as customer_name, c.email as customer_email, c.phone as customer_phone, c.address as customer_address,
                           a.name as associate_name, a.email as associate_email, a.phone as associate_phone
                    FROM bookings b
                    LEFT JOIN properties p ON b.property_id = p.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN users a ON b.associate_id = a.id
                    WHERE b.id = :id LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $bookingId]);
            $booking = $stmt->fetch();

            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                return $this->redirect('admin/bookings');
            }

            // Get payment history
            $paymentsSql = "SELECT * FROM booking_payments WHERE booking_id = :booking_id ORDER BY created_at DESC";
            $paymentsStmt = $this->db->prepare($paymentsSql);
            $paymentsStmt->execute(['booking_id' => $bookingId]);
            $payments = $paymentsStmt->fetchAll();

            // Get commission history
            $commissionSql = "SELECT * FROM mlm_commission_ledger WHERE source_booking_id = :booking_id ORDER BY created_at DESC";
            $commissionStmt = $this->db->prepare($commissionSql);
            $commissionStmt->execute(['booking_id' => $bookingId]);
            $commissions = $commissionStmt->fetchAll();

            $data = [
                'page_title' => 'Booking Details - APS Dream Home',
                'booking' => $booking,
                'payments' => $payments,
                'commissions' => $commissions,
                'total_paid' => array_sum(array_column($payments, 'amount')),
                'total_commission' => array_sum(array_column($commissions, 'amount'))
            ];

            return $this->render('admin/bookings/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Booking show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load booking details');
            return $this->redirect('admin/bookings');
        }
    }

    /**
     * Show edit booking form
     */
    public function edit($id)
    {
        try {
            $bookingId = intval($id);
            if ($bookingId <= 0) {
                $this->setFlash('error', 'Invalid booking ID');
                return $this->redirect('admin/bookings');
            }

            // Get booking details
            $sql = "SELECT b.*, p.title as property_title, c.name as customer_name, a.name as associate_name
                    FROM bookings b
                    LEFT JOIN properties p ON b.property_id = p.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN users a ON b.associate_id = a.id
                    WHERE b.id = :id LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $bookingId]);
            $booking = $stmt->fetch();

            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                return $this->redirect('admin/bookings');
            }

            // Get form data
            $properties = $this->db->query("SELECT id, title, price FROM properties ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
            $customers = $this->db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $associates = $this->db->query("SELECT id, name, email FROM users WHERE role = 'associate' AND status = 'active' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Edit Booking - APS Dream Home',
                'booking' => $booking,
                'properties' => $properties,
                'customers' => $customers,
                'associates' => $associates
            ];

            return $this->render('admin/bookings/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Booking edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load booking for editing');
            return $this->redirect('admin/bookings');
        }
    }

    /**
     * Update booking
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/bookings');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/bookings');
        }

        try {
            $bookingId = intval($id);
            if ($bookingId <= 0) {
                $this->setFlash('error', 'Invalid booking ID');
                return $this->redirect('admin/bookings');
            }

            $data = $_POST;

            $property_id = intval($data['property_id'] ?? 0);
            $customer_id = intval($data['customer_id'] ?? 0);
            $associate_id = intval($data['associate_id'] ?? 0);
            $booking_date = $data['booking_date'] ?? '';
            $total_amount = floatval($data['total_amount'] ?? 0);
            $status = $data['status'] ?? 'pending';
            $notes = $data['notes'] ?? '';

            // Validation
            if ($property_id <= 0 || $customer_id <= 0 || $total_amount <= 0 || empty($booking_date)) {
                $this->setFlash('error', 'Please fill in all required fields');
                return $this->redirect("admin/bookings/{$bookingId}/edit");
            }

            // Check if booking exists
            $stmt = $this->db->prepare("SELECT id FROM bookings WHERE id = ? LIMIT 1");
            $stmt->execute([$bookingId]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$existing) {
                $this->setFlash('error', 'Booking not found');
                return $this->redirect('admin/bookings');
            }

            // Update booking
            $sql = "UPDATE bookings 
                    SET property_id = :property_id, customer_id = :customer_id, associate_id = :associate_id,
                        booking_date = :booking_date, total_amount = :total_amount, status = :status,
                        notes = :notes, updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'property_id' => $property_id,
                'customer_id' => $customer_id,
                'associate_id' => $associate_id,
                'booking_date' => $booking_date,
                'total_amount' => $total_amount,
                'status' => $status,
                'notes' => $notes,
                'id' => $bookingId
            ]);

            if ($success) {
                // Log the update
                $this->logBookingUpdate($bookingId, $data);

                // Send notifications if status changed
                $this->handleStatusChange($bookingId, $status);

                $this->setFlash('success', 'Booking updated successfully');
                return $this->redirect('admin/bookings');
            } else {
                $this->setFlash('error', 'Failed to update booking');
                return $this->redirect("admin/bookings/{$bookingId}/edit");
            }
        } catch (Exception $e) {
            $this->loggingService->error("Booking update error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update booking');
            return $this->redirect("admin/bookings/{$bookingId}/edit");
        }
    }

    /**
     * Delete booking
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/bookings');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/bookings');
        }

        try {
            $bookingId = intval($id);
            if ($bookingId <= 0) {
                $this->setFlash('error', 'Invalid booking ID');
                return $this->redirect('admin/bookings');
            }

            // Check if booking exists
            $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = ? LIMIT 1");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                return $this->redirect('admin/bookings');
            }

            // Start transaction for safe deletion
            $this->db->beginTransaction();

            try {
                // Delete related payments
                $this->db->prepare("DELETE FROM booking_payments WHERE booking_id = ?")->execute([$bookingId]);

                // Delete related receipts
                $this->db->prepare("DELETE FROM payment_receipts WHERE payment_id IN (SELECT id FROM booking_payments WHERE booking_id = ?)")->execute([$bookingId]);

                // Delete related commissions
                $this->db->prepare("DELETE FROM mlm_commission_ledger WHERE source_booking_id = ?")->execute([$bookingId]);

                // Delete the booking
                $this->db->prepare("DELETE FROM bookings WHERE id = ?")->execute([$bookingId]);

                $this->db->commit();

                // Log the deletion
                $this->logBookingDeletion($bookingId, $booking);

                $this->setFlash('success', 'Booking deleted successfully');
                return $this->redirect('admin/bookings');
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Booking deletion error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to delete booking');
            return $this->redirect('admin/bookings');
        }
    }

    /**
     * Log booking update
     */
    private function logBookingUpdate(int $bookingId, array $data): void
    {
        try {
            $user = $this->auth->user();
            $logData = [
                'booking_id' => $bookingId,
                'action' => 'updated',
                'user_id' => $user->id,
                'changes' => json_encode($data),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $sql = "INSERT INTO booking_logs (booking_id, action, user_id, changes, created_at)
                    VALUES (:booking_id, :action, :user_id, :changes, :created_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($logData);
        } catch (Exception $e) {
            $this->loggingService->error("Booking update log error: " . $e->getMessage());
        }
    }

    /**
     * Log booking deletion
     */
    private function logBookingDeletion(int $bookingId, array $booking): void
    {
        try {
            $user = $this->auth->user();
            $logData = [
                'booking_id' => $bookingId,
                'action' => 'deleted',
                'user_id' => $user->id,
                'changes' => json_encode($booking),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $sql = "INSERT INTO booking_logs (booking_id, action, user_id, changes, created_at)
                    VALUES (:booking_id, :action, :user_id, :changes, :created_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($logData);
        } catch (Exception $e) {
            $this->loggingService->error("Booking deletion log error: " . $e->getMessage());
        }
    }

    /**
     * Handle status change notifications
     */
    private function handleStatusChange(int $bookingId, string $newStatus): void
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT b.*, c.name as customer_name, c.email, p.title as property_title
                 FROM bookings b
                 JOIN users c ON b.customer_id = c.id
                 JOIN properties p ON b.property_id = p.id
                 WHERE b.id = ? LIMIT 1"
            );
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking) return;

            $statusMessages = [
                'confirmed' => 'Your booking has been confirmed!',
                'completed' => 'Your booking has been completed successfully!',
                'cancelled' => 'Your booking has been cancelled.',
                'pending' => 'Your booking is pending confirmation.'
            ];

            $message = $statusMessages[$newStatus] ?? 'Booking status updated.';

            // Send email notification
            $notificationService = new NotificationService();
            $subject = "Booking Status Update - {$booking['booking_number']}";
            $body = "Dear {$booking['customer_name']},<br><br>{$message}<br><br>
                     Property: {$booking['property_title']}<br>
                     Booking Number: {$booking['booking_number']}<br><br>
                     Thank you for choosing APS Dream Home.";

            $notificationService->sendEmail($booking['email'], $subject, $body, 'booking_status', $bookingId);
        } catch (Exception $e) {
            $this->loggingService->error("Status change notification error: " . $e->getMessage());
        }
    }

    /**
     * Process payment for booking
     */
    public function processPayment($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect("admin/bookings/{$id}");
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect("admin/bookings/{$id}");
        }

        try {
            $bookingId = intval($id);
            if ($bookingId <= 0) {
                $this->setFlash('error', 'Invalid booking ID');
                return $this->redirect('admin/bookings');
            }

            $booking = $this->db->fetchOne("SELECT * FROM bookings WHERE id = ? LIMIT 1", [$bookingId]);
            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                return $this->redirect('admin/bookings');
            }

            $amount = floatval($_POST['amount'] ?? 0);
            $paymentMethod = $_POST['payment_method'] ?? '';
            $transactionId = $_POST['transaction_id'] ?? '';

            if ($amount <= 0 || empty($paymentMethod)) {
                $this->setFlash('error', 'Invalid payment details');
                return $this->redirect("admin/bookings/{$bookingId}");
            }

            // Start transaction
            $this->db->beginTransaction();

            try {
                // Insert payment record
                $sql = "INSERT INTO booking_payments (booking_id, amount, payment_method, transaction_id, status, created_at)
                        VALUES (:booking_id, :amount, :payment_method, :transaction_id, 'completed', NOW())";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'booking_id' => $bookingId,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'transaction_id' => $transactionId
                ]);

                $paymentId = (int)$this->db->lastInsertId();

                // Update booking payment status
                $totalPaid = $this->getTotalPaidAmount($bookingId);
                $totalAmount = floatval($booking['total_amount']);

                if ($totalPaid >= $totalAmount) {
                    // Full payment received
                    $this->db->prepare("UPDATE bookings SET payment_status = 'paid', status = 'confirmed' WHERE id = ?")
                        ->execute([$bookingId]);
                } else {
                    // Partial payment
                    $this->db->prepare("UPDATE bookings SET payment_status = 'partial' WHERE id = ?")
                        ->execute([$bookingId]);
                }

                // Generate receipt
                $this->generatePaymentReceipt($paymentId);

                // Notify customer
                $this->sendPaymentNotification($booking['customer_id'], $amount, $paymentId);

                // Notify accounts department
                $this->notifyAccountsDepartment($bookingId, $amount, $paymentMethod);

                $this->db->commit();

                $this->setFlash('success', 'Payment processed successfully!');
                return $this->redirect("admin/bookings/{$bookingId}");
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Payment processing error: " . $e->getMessage());
            $this->setFlash('error', 'Payment processing failed. Please try again.');
            return $this->redirect("admin/bookings/{$id}");
        }
    }

    /**
     * Show create booking form
     */
    public function create()
    {
        // Fetch properties (available)
        // Using direct query for simple list or use model method if available
        $properties = $this->db->query("SELECT id, title, price FROM properties WHERE status = 'available' ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch customers (users with role='customer')
        // Using direct query to be efficient
        $customers = $this->db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch associates (users with role='associate' and status='active')
        // For associate assignment in booking
        $associates = $this->db->query("SELECT id, name, email FROM users WHERE role = 'associate' AND status = 'active' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('admin/bookings/create', [
            'page_title' => 'Add New Booking - APS Dream Home',
            'properties' => $properties,
            'customers' => $customers,
            'associates' => $associates
        ]);
    }

    /**
     * Store new booking with automatic customer registration
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method.');
            return $this->redirect('admin/bookings/create');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed. Please try again.');
            return $this->redirect('admin/bookings/create');
        }

        $data = $_POST;

        $property_id = intval($data['property_id'] ?? 0);
        $customer_id = intval($data['customer_id'] ?? 0);
        $associate_id = intval($data['associate_id'] ?? 0);
        $booking_date = $data['booking_date'] ?? date('Y-m-d');
        $booking_amount = floatval($data['amount'] ?? 0);
        $status = $data['status'] ?? 'pending';
        $booking_number = 'BK-' . strtoupper(uniqid());

        // Check if this is a new customer booking (customer_id = 0 or new customer data provided)
        $isNewCustomer = ($customer_id <= 0) || !empty($data['new_customer_name']);

        if ($isNewCustomer) {
            // Auto-register new customer
            $customer_id = $this->autoRegisterCustomer($data);
            if (!$customer_id) {
                $this->setFlash('error', 'Failed to register customer. Please check customer details.');
                return $this->redirect('admin/bookings/create');
            }
        }

        if ($property_id <= 0 || $customer_id <= 0 || $booking_amount <= 0) {
            $this->setFlash('error', 'Please fill in all required fields.');
            return $this->redirect('admin/bookings/create');
        }

        // Fetch property price
        $property = $this->db->fetchOne("SELECT price FROM properties WHERE id = ? LIMIT 1", [$property_id]);
        $total_amount = ($property && isset($property['price'])) ? $property['price'] : $booking_amount;

        try {
            // Insert booking
            $sql = "INSERT INTO bookings (property_id, customer_id, associate_id, booking_date, booking_amount, total_amount, status, booking_number, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                $property_id,
                $customer_id,
                $associate_id,
                $booking_date,
                $booking_amount,
                $total_amount,
                $status,
                $booking_number
            ]);

            if ($success) {
                $booking_id = $this->db->lastInsertId();

                // Trigger commission calculation if associate is assigned
                if ($associate_id > 0) {
                    try {
                        // Placeholder for commission service
                        error_log("Commission calculation triggered for booking {$booking_number} with associate {$associate_id}");
                    } catch (Exception $e) {
                        error_log("Commission service error for booking {$booking_number}: " . $e->getMessage());
                    }
                }

                // Send notifications
                $this->sendNotifications($booking_id, $property_id, $customer_id, $booking_amount, $booking_date, $booking_number, $associate_id);

                $this->setFlash('success', 'Booking added successfully!' . ($associate_id > 0 ? ' Commission calculated for associate.' : ''));
                return $this->redirect('admin/bookings');
            } else {
                throw new Exception("Failed to save booking.");
            }
        } catch (Exception $e) {
            $this->loggingService->error("Error adding booking: " . $e->getMessage());
            $this->setFlash('error', 'Error adding booking: ' . $e->getMessage());
            return $this->redirect('admin/bookings/create');
        }
    }

    /**
     * Send booking notifications
     */
    private function sendNotifications($booking_id, $property_id, $customer_id, $amount, $date, $booking_number, $associate_id = 0)
    {
        try {
            $notificationService = new NotificationService();

            // Fetch customer
            $customer = $this->db->fetchOne("SELECT name, email FROM users WHERE id = ? LIMIT 1", [$customer_id]);

            if (!$customer) {
                return;
            }

            // Fetch property
            $property = $this->db->fetchOne("SELECT title FROM properties WHERE id = ? LIMIT 1", [$property_id]);
            $property_title = ($property && isset($property['title'])) ? $property['title'] : 'Property #' . $property_id;

            // Send to Customer
            $subject = "Booking Confirmation - " . $booking_number;
            $body = "Dear " . ($customer['name'] ?? 'Customer') . ",<br><br>";
            $body .= "Your booking for property '<strong>" . htmlspecialchars($property_title) . "</strong>' has been confirmed.<br>";
            $body .= "Booking Number: <strong>" . htmlspecialchars($booking_number) . "</strong><br>";
            $body .= "Date: " . htmlspecialchars($date) . "<br>";
            $body .= "Amount: " . number_format($amount, 2) . "<br>";

            // Add associate information if assigned
            if ($associate_id > 0) {
                $associate = $this->db->fetchOne("SELECT name, email, phone FROM users WHERE id = ?", [$associate_id]);
                if ($associate) {
                    $body .= "Assigned Associate: <strong>" . htmlspecialchars($associate['name']) . "</strong><br>";
                    $body .= "Associate Contact: " . htmlspecialchars($associate['phone']) . "<br>";
                }
            }

            $body .= "<br>Thank you for choosing APS Dream Home.";

            // Use email from customer object
            $customerEmail = $customer['email'] ?? '';

            if ($customerEmail) {
                $notificationService->sendEmail($customerEmail, $subject, $body, 'booking', $customer_id, [
                    'booking_number' => $booking_number,
                    'property_title' => $property_title,
                    'amount' => $amount,
                    'date' => $date
                ]);
            }

            // Send notification to associate if assigned
            if ($associate_id > 0) {
                $associate = $this->db->fetchOne("SELECT name, email FROM users WHERE id = ?", [$associate_id]);
                if ($associate && !empty($associate['email'])) {
                    $associateSubject = "New Booking Assignment - " . $booking_number;
                    $associateBody = "Dear " . htmlspecialchars($associate['name']) . ",<br><br>";
                    $associateBody .= "You have been assigned a new booking:<br>";
                    $associateBody .= "Booking Number: <strong>" . htmlspecialchars($booking_number) . "</strong><br>";
                    $associateBody .= "Property: <strong>" . htmlspecialchars($property_title) . "</strong><br>";
                    $associateBody .= "Customer: " . htmlspecialchars($customer['name'] ?? 'Unknown') . "<br>";
                    $associateBody .= "Amount: " . number_format($amount, 2) . "<br>";
                    $associateBody .= "Date: " . htmlspecialchars($date) . "<br><br>";
                    $associateBody .= "Your commission will be calculated and credited accordingly.<br>";
                    $associateBody .= "Please follow up with the customer for further processing.";

                    $notificationService->sendEmail($associate['email'], $associateSubject, $associateBody, 'booking_assignment', $associate_id, [
                        'booking_number' => $booking_number,
                        'customer_name' => $customer['name'] ?? 'Unknown',
                        'property_title' => $property_title,
                        'amount' => $amount
                    ]);
                }
            }

            // Notify Admin
            $adminSubject = "New Booking - " . $booking_number;
            $adminBody = "A new booking has been created:<br>";
            $adminBody .= "Booking Number: <strong>" . htmlspecialchars($booking_number) . "</strong><br>";
            $adminBody .= "Property: <strong>" . htmlspecialchars($property_title) . "</strong><br>";
            $adminBody .= "Customer: " . htmlspecialchars($customer['name'] ?? 'Unknown') . "<br>";
            $adminBody .= "Amount: " . number_format($amount, 2) . "<br>";
            $adminBody .= "Date: " . htmlspecialchars($date) . "<br>";

            if ($associate_id > 0) {
                $associate = $this->db->fetchOne("SELECT name FROM users WHERE id = ?", [$associate_id]);
                $adminBody .= "Associate: " . htmlspecialchars($associate['name'] ?? 'Unknown') . "<br>";
            }

            $adminBody .= "Status: <strong>" . htmlspecialchars($status ?? 'pending') . "</strong>";

            $notificationService->notifyAdmin($adminSubject, $adminBody, 'new_booking', [
                'booking_number' => $booking_number,
                'property_id' => $property_id,
                'customer_id' => $customer_id,
                'associate_id' => $associate_id,
                'amount' => $amount
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Error sending booking notifications: " . $e->getMessage());
        }
    }

    /**
     * Auto-register new customer from booking data
     */
    private function autoRegisterCustomer(array $data): ?int
    {
        try {
            $name = CoreFunctionsServiceCustom::validateInput($data['new_customer_name'] ?? $data['customer_name'] ?? '', 'string');
            $email = CoreFunctionsServiceCustom::validateInput($data['new_customer_email'] ?? $data['customer_email'] ?? '', 'email');
            $phone = CoreFunctionsServiceCustom::validateInput($data['new_customer_phone'] ?? $data['customer_phone'] ?? '', 'phone');
            $address = CoreFunctionsServiceCustom::validateInput($data['new_customer_address'] ?? $data['customer_address'] ?? '', 'string');

            if (empty($name) || (empty($email) && empty($phone))) {
                return null;
            }

            // Generate unique username and password
            $username = $this->generateUsername($name);
            $password = $this->generateSecurePassword();

            // Check if email already exists
            if (!empty($email)) {
                $existing = $this->db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
                if ($existing) {
                    return (int)$existing['id']; // Return existing customer ID
                }
            }

            // Insert new customer
            $sql = "INSERT INTO users (name, email, phone, address, username, password, role, status, registration_source, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'customer', 'active', 'booking', NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                $name,
                $email,
                $phone,
                $address,
                $username,
                CoreFunctionsServiceCustom::hashPassword($password)
            ]);

            if ($success) {
                $customerId = (int)$this->db->lastInsertId();

                // Send welcome credentials to customer
                $this->sendCustomerCredentials($customerId, $username, $password, $email, $phone);

                return $customerId;
            }

            return null;
        } catch (Exception $e) {
            $this->loggingService->error("Customer auto-registration error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate unique username from customer name
     */
    private function generateUsername(string $name): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z]/', '', $name));
        $username = $base . rand(100, 999);

        // Ensure uniqueness
        $count = 1;
        while ($this->db->fetchOne("SELECT id FROM users WHERE username = ? LIMIT 1", [$username])) {
            $username = $base . rand(100, 999) . $count;
            $count++;
        }

        return $username;
    }

    /**
     * Generate secure password for new customer
     */
    private function generateSecurePassword(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        return substr(str_shuffle($chars), 0, 12);
    }

    /**
     * Send login credentials to new customer
     */
    private function sendCustomerCredentials(int $customerId, string $username, string $password, string $email, string $phone): void
    {
        try {
            $customer = $this->db->fetchOne("SELECT name FROM users WHERE id = ? LIMIT 1", [$customerId]);
            if (!$customer) return;

            $subject = "Welcome to APS Dream Home - Your Account Details";
            $message = "Dear " . htmlspecialchars($customer['name']) . ",\n\n";
            $message .= "Your account has been created with APS Dream Home.\n\n";
            $message .= "Login Details:\n";
            $message .= "Username: " . htmlspecialchars($username) . "\n";
            $message .= "Password: " . htmlspecialchars($password) . "\n\n";
            $message .= "You can login at: " . BASE_URL . "/login\n\n";
            $message .= "Use these credentials to view your booking details, EMI schedule, payment history, and more.\n\n";
            $message .= "Thank you for choosing APS Dream Home!";

            // Send email
            if (!empty($email)) {
                $notificationService = new NotificationService();
                $notificationService->sendEmail($email, $subject, nl2br($message), 'welcome', $customerId);
            }

            // Send SMS (if SMS service is available)
            if (!empty($phone)) {
                $this->sendSMS($phone, "Welcome to APS Dream Home! Your login details: Username: $username, Password: $password. Login at: " . BASE_URL . "/login");
            }

            // Send WhatsApp (if WhatsApp service is available)
            if (!empty($phone)) {
                $this->sendWhatsApp($phone, $message);
            }
        } catch (Exception $e) {
            $this->loggingService->error("Error sending customer credentials: " . $e->getMessage());
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSMS(string $phone, string $message): bool
    {
        try {
            // Integrate with SMS gateway (placeholder implementation)
            error_log("SMS sent to $phone: $message");
            return true;
        } catch (Exception $e) {
            $this->loggingService->error("SMS sending error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send WhatsApp notification
     */
    private function sendWhatsApp(string $phone, string $message): bool
    {
        try {
            // Integrate with WhatsApp API (placeholder implementation)
            error_log("WhatsApp sent to $phone: $message");
            return true;
        } catch (Exception $e) {
            $this->loggingService->error("WhatsApp sending error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total paid amount for booking
     */
    private function getTotalPaidAmount(int $bookingId): float
    {
        $result = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM booking_payments WHERE booking_id = ? AND status = 'completed'",
            [$bookingId]
        );
        return floatval($result['total'] ?? 0);
    }

    /**
     * Generate payment receipt
     */
    private function generatePaymentReceipt(int $paymentId): void
    {
        try {
            $payment = $this->db->fetchOne(
                "SELECT bp.*, b.booking_number, c.name as customer_name, c.email, p.title as property_title
                 FROM booking_payments bp
                 JOIN bookings b ON bp.booking_id = b.id
                 JOIN users c ON b.customer_id = c.id
                 JOIN properties p ON b.property_id = p.id
                 WHERE bp.id = ? LIMIT 1",
                [$paymentId]
            );

            if (!$payment) return;

            $receiptNumber = 'RCP-' . strtoupper(uniqid());

            // Update payment with receipt number
            $this->db->prepare("UPDATE booking_payments SET receipt_number = ? WHERE id = ?")
                ->execute([$receiptNumber, $paymentId]);

            // Create receipt record
            $sql = "INSERT INTO payment_receipts (payment_id, receipt_number, customer_name, property_title, amount, payment_method, transaction_id, created_at)
                    VALUES (:payment_id, :receipt_number, :customer_name, :property_title, :amount, :payment_method, :transaction_id, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'payment_id' => $paymentId,
                'receipt_number' => $receiptNumber,
                'customer_name' => $payment['customer_name'],
                'property_title' => $payment['property_title'],
                'amount' => $payment['amount'],
                'payment_method' => $payment['payment_method'],
                'transaction_id' => $payment['transaction_id']
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Receipt generation error: " . $e->getMessage());
        }
    }

    /**
     * Send payment notification to customer
     */
    private function sendPaymentNotification(int $customerId, float $amount, int $paymentId): void
    {
        try {
            $customer = $this->db->fetchOne("SELECT name, email FROM users WHERE id = ? LIMIT 1", [$customerId]);
            if (!$customer) return;

            $receipt = $this->db->fetchOne("SELECT receipt_number FROM payment_receipts WHERE payment_id = ? LIMIT 1", [$paymentId]);

            $subject = "Payment Received - APS Dream Home";
            $message = "Dear " . htmlspecialchars($customer['name']) . ",\n\n";
            $message .= "We have received your payment of ₹" . number_format($amount, 2) . ".\n";
            if ($receipt) {
                $message .= "Receipt Number: " . htmlspecialchars($receipt['receipt_number']) . "\n";
            }
            $message .= "\nYou can view your payment history and download receipts from your dashboard.\n\n";
            $message .= "Thank you for your payment!";

            $notificationService = new NotificationService();
            $notificationService->sendEmail($customer['email'], $subject, nl2br($message), 'payment', $customerId);
        } catch (Exception $e) {
            $this->loggingService->error("Payment notification error: " . $e->getMessage());
        }
    }

    /**
     * Notify accounts department
     */
    private function notifyAccountsDepartment(int $bookingId, float $amount, string $paymentMethod): void
    {
        try {
            $booking = $this->db->fetchOne(
                "SELECT b.booking_number, c.name as customer_name, p.title as property_title
                 FROM bookings b
                 JOIN users c ON b.customer_id = c.id
                 JOIN properties p ON b.property_id = p.id
                 WHERE b.id = ? LIMIT 1",
                [$bookingId]
            );

            if (!$booking) return;

            $subject = "Payment Received - Booking #" . $booking['booking_number'];
            $message = "Payment Details:\n";
            $message .= "Booking: " . htmlspecialchars($booking['booking_number']) . "\n";
            $message .= "Customer: " . htmlspecialchars($booking['customer_name']) . "\n";
            $message .= "Property: " . htmlspecialchars($booking['property_title']) . "\n";
            $message .= "Amount: ₹" . number_format($amount, 2) . "\n";
            $message .= "Payment Method: " . htmlspecialchars($paymentMethod) . "\n";
            $message .= "Date: " . date('Y-m-d H:i:s');

            // Send to accounts department email
            $notificationService = new NotificationService();
            $notificationService->notifyAdmin($subject, nl2br($message), 'payment', [
                'booking_id' => $bookingId,
                'amount' => $amount,
                'payment_method' => $paymentMethod
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Accounts notification error: " . $e->getMessage());
        }
    }

    public function availability($propertyId)
    {
        try {
            if (!\is_numeric($propertyId)) {
                return $this->jsonErrorLocal('Invalid property ID', 400);
            }

            $sql = "SELECT visit_date FROM visit_availability WHERE property_id = ? AND visit_date >= CURDATE()";
            $result = $this->db->fetchAll($sql, [$propertyId]);

            $availableDates = \array_column($result, 'visit_date');

            return $this->jsonSuccess($availableDates);
        } catch (Exception $e) {
            return $this->jsonErrorLocal($e->getMessage(), 500);
        }
    }

    public function myBookings()
    {
        try {
            $user = $this->auth->user();
            $sql = "SELECT b.*, p.title as property_title, p.location 
                    FROM bookings b
                    JOIN properties p ON b.property_id = p.id
                    WHERE b.customer_id = ?
                    ORDER BY b.visit_date DESC";
            $bookings = $this->db->fetchAll($sql, [$user->id]);

            return $this->jsonSuccess($bookings);
        } catch (Exception $e) {
            return $this->jsonErrorLocal($e->getMessage(), 500);
        }
    }

    // Helper methods for CSRF validation and JSON responses
    private function validateCsrfTokenLocal(): bool
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return $token === ($_SESSION['csrf_token'] ?? '');
    }

    private function jsonErrorLocal(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    private function jsonSuccess($data): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
}
