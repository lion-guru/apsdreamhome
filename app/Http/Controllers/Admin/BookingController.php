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
            $bookings = $stmt->fetchAll();

            // Get filter options
            $customers = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name");
            $associates = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role = 'associate' AND status = 'active' ORDER BY name");

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

            $sql = "SELECT b.*, p.title as property_title, p.location as property_location,
                           c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
                           a.name as associate_name, a.email as associate_email
                    FROM bookings b
                    LEFT JOIN properties p ON b.property_id = p.id
                    LEFT JOIN users c ON b.customer_id = c.id
                    LEFT JOIN users a ON b.associate_id = a.id
                    WHERE b.id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch();

            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                return $this->redirect('admin/bookings');
            }

            // Get booking history
            $historySql = "SELECT * FROM booking_history WHERE booking_id = ? ORDER BY created_at DESC";
            $historyStmt = $this->db->prepare($historySql);
            $historyStmt->execute([$bookingId]);
            $history = $historyStmt->fetchAll();

            $data = [
                'page_title' => 'Booking Details - APS Dream Home',
                'booking' => $booking,
                'history' => $history
            ];

            return $this->render('admin/bookings/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Booking show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load booking details');
            return $this->redirect('admin/bookings');
        }
    }

    /**
     * Create new booking form
     */
    public function create()
    {
        try {
            // Get available properties
            $properties = $this->db->fetchAll("SELECT id, title, location, price FROM properties WHERE status = 'available' ORDER BY title");
            
            // Get customers
            $customers = $this->db->fetchAll("SELECT id, name, email, phone FROM users WHERE role = 'customer' ORDER BY name");
            
            // Get associates
            $associates = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role = 'associate' AND status = 'active' ORDER BY name");

            $data = [
                'page_title' => 'Create Booking - APS Dream Home',
                'properties' => $properties,
                'customers' => $customers,
                'associates' => $associates
            ];

            return $this->render('admin/bookings/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Booking create form error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load booking form');
            return $this->redirect('admin/bookings');
        }
    }

    /**
     * Store new booking
     */
    public function store()
    {
        try {
            // Validate CSRF token
            if (!$this->requestService->validateCsrfToken()) {
                $this->requestService->errorResponse('Invalid CSRF token', 403);
            }

            // Validate input
            $rules = [
                'property_id' => 'required|numeric',
                'customer_id' => 'required|numeric',
                'associate_id' => 'numeric',
                'booking_type' => 'required',
                'payment_method' => 'required',
                'total_amount' => 'required|numeric',
                'down_payment' => 'numeric'
            ];

            $errors = $this->requestService->validate($rules);
            if (!empty($errors)) {
                $this->requestService->errorResponse('Validation failed', 400, $errors);
            }

            // Get input data
            $data = $this->requestService->input();
            
            // Generate booking number
            $bookingNumber = 'BK' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Check property availability
            $propertySql = "SELECT id, status, price FROM properties WHERE id = ?";
            $propertyStmt = $this->db->prepare($propertySql);
            $propertyStmt->execute([$data['property_id']]);
            $property = $propertyStmt->fetch();

            if (!$property || $property['status'] !== 'available') {
                $this->requestService->errorResponse('Property not available', 400);
            }

            // Calculate payment schedule
            $totalAmount = floatval($data['total_amount']);
            $downPayment = floatval($data['down_payment'] ?? 0);
            $remainingAmount = $totalAmount - $downPayment;

            // Start transaction
            $this->db->beginTransaction();

            try {
                // Create booking
                $bookingSql = "INSERT INTO bookings (booking_number, property_id, customer_id, associate_id, 
                                booking_type, payment_method, total_amount, down_payment, remaining_amount, 
                                status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

                $bookingStmt = $this->db->prepare($bookingSql);
                $bookingStmt->execute([
                    $bookingNumber,
                    $data['property_id'],
                    $data['customer_id'],
                    $data['associate_id'] ?? null,
                    $data['booking_type'],
                    $data['payment_method'],
                    $totalAmount,
                    $downPayment,
                    $remainingAmount
                ]);

                $bookingId = $this->db->lastInsertId();

                // Update property status
                $updatePropertySql = "UPDATE properties SET status = 'booked' WHERE id = ?";
                $updatePropertyStmt = $this->db->prepare($updatePropertySql);
                $updatePropertyStmt->execute([$data['property_id']]);

                // Create booking history
                $historySql = "INSERT INTO booking_history (booking_id, status, notes, created_by, created_at) 
                              VALUES (?, 'pending', 'Booking created', ?, NOW())";
                $historyStmt = $this->db->prepare($historySql);
                $historyStmt->execute([$bookingId, $_SESSION['user_id']]);

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'], 'booking_created', [
                    'booking_id' => $bookingId,
                    'booking_number' => $bookingNumber,
                    'property_id' => $data['property_id'],
                    'customer_id' => $data['customer_id']
                ]);

                // Send notifications
                $notificationService = new NotificationService();
                $notificationService->notifyAdmin('New Booking Created', "Booking #{$bookingNumber} has been created");

                // Commit transaction
                $this->db->commit();

                $this->requestService->successResponse('Booking created successfully', [
                    'booking_id' => $bookingId,
                    'booking_number' => $bookingNumber
                ]);

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            $this->loggingService->error("Booking store error: " . $e->getMessage());
            $this->requestService->errorResponse('Failed to create booking', 500);
        }
    }

    /**
     * Edit booking form
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
            $sql = "SELECT b.*, p.title as property_title, p.location as property_location
                    FROM bookings b
                    LEFT JOIN properties p ON b.property_id = p.id
                    WHERE b.id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch();

            if (!$booking) {
                $this->setFlash('error', 'Booking not found');
                return $this->redirect('admin/bookings');
            }

            // Get options for dropdowns
            $properties = $this->db->fetchAll("SELECT id, title, location FROM properties ORDER BY title");
            $customers = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name");
            $associates = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role = 'associate' AND status = 'active' ORDER BY name");

            $data = [
                'page_title' => 'Edit Booking - APS Dream Home',
                'booking' => $booking,
                'properties' => $properties,
                'customers' => $customers,
                'associates' => $associates
            ];

            return $this->render('admin/bookings/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Booking edit form error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load booking form');
            return $this->redirect('admin/bookings');
        }
    }

    /**
     * Update booking
     */
    public function update($id)
    {
        try {
            $bookingId = intval($id);
            if ($bookingId <= 0) {
                $this->requestService->errorResponse('Invalid booking ID', 400);
            }

            // Validate CSRF token
            if (!$this->requestService->validateCsrfToken()) {
                $this->requestService->errorResponse('Invalid CSRF token', 403);
            }

            // Get input data
            $data = $this->requestService->input();

            // Get current booking
            $currentSql = "SELECT * FROM bookings WHERE id = ?";
            $currentStmt = $this->db->prepare($currentSql);
            $currentStmt->execute([$bookingId]);
            $currentBooking = $currentStmt->fetch();

            if (!$currentBooking) {
                $this->requestService->errorResponse('Booking not found', 404);
            }

            // Start transaction
            $this->db->beginTransaction();

            try {
                // Update booking
                $updateSql = "UPDATE bookings SET associate_id = ?, booking_type = ?, payment_method = ?, 
                              total_amount = ?, down_payment = ?, remaining_amount = ?, updated_at = NOW()
                              WHERE id = ?";

                $remainingAmount = floatval($data['total_amount']) - floatval($data['down_payment'] ?? 0);
                
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([
                    $data['associate_id'] ?? null,
                    $data['booking_type'],
                    $data['payment_method'],
                    $data['total_amount'],
                    $data['down_payment'] ?? 0,
                    $remainingAmount,
                    $bookingId
                ]);

                // Create booking history
                $historySql = "INSERT INTO booking_history (booking_id, status, notes, created_by, created_at) 
                              VALUES (?, ?, 'Booking updated', ?, NOW())";
                $historyStmt = $this->db->prepare($historySql);
                $historyStmt->execute([$bookingId, $_SESSION['user_id']]);

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'], 'booking_updated', [
                    'booking_id' => $bookingId,
                    'changes' => $data
                ]);

                // Commit transaction
                $this->db->commit();

                $this->requestService->successResponse('Booking updated successfully');

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            $this->loggingService->error("Booking update error: " . $e->getMessage());
            $this->requestService->errorResponse('Failed to update booking', 500);
        }
    }

    /**
     * Delete booking
     */
    public function destroy($id)
    {
        try {
            $bookingId = intval($id);
            if ($bookingId <= 0) {
                $this->requestService->errorResponse('Invalid booking ID', 400);
            }

            // Validate CSRF token
            if (!$this->requestService->validateCsrfToken()) {
                $this->requestService->errorResponse('Invalid CSRF token', 403);
            }

            // Get booking details
            $sql = "SELECT * FROM bookings WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch();

            if (!$booking) {
                $this->requestService->errorResponse('Booking not found', 404);
            }

            // Start transaction
            $this->db->beginTransaction();

            try {
                // Update property status back to available
                if ($booking['property_id']) {
                    $updatePropertySql = "UPDATE properties SET status = 'available' WHERE id = ?";
                    $updatePropertyStmt = $this->db->prepare($updatePropertySql);
                    $updatePropertyStmt->execute([$booking['property_id']]);
                }

                // Delete booking
                $deleteSql = "DELETE FROM bookings WHERE id = ?";
                $deleteStmt = $this->db->prepare($deleteSql);
                $deleteStmt->execute([$bookingId]);

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'], 'booking_deleted', [
                    'booking_id' => $bookingId,
                    'booking_number' => $booking['booking_number']
                ]);

                // Commit transaction
                $this->db->commit();

                $this->requestService->successResponse('Booking deleted successfully');

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            $this->loggingService->error("Booking delete error: " . $e->getMessage());
            $this->requestService->errorResponse('Failed to delete booking', 500);
        }
    }

    /**
     * Update booking status
     */
    public function updateStatus($id)
    {
        try {
            $bookingId = intval($id);
            $status = $_POST['status'] ?? '';

            if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'])) {
                $this->requestService->errorResponse('Invalid status', 400);
            }

            // Update booking status
            $sql = "UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$status, $bookingId]);

            // Create booking history
            $historySql = "INSERT INTO booking_history (booking_id, status, notes, created_by, created_at) 
                          VALUES (?, ?, 'Status updated to $status', ?, NOW())";
            $historyStmt = $this->db->prepare($historySql);
            $historyStmt->execute([$bookingId, $_SESSION['user_id']]);

            // Log activity
            $this->loggingService->logUserActivity($_SESSION['user_id'], 'booking_status_updated', [
                'booking_id' => $bookingId,
                'new_status' => $status
            ]);

            $this->requestService->successResponse('Booking status updated successfully');

        } catch (Exception $e) {
            $this->loggingService->error("Booking status update error: " . $e->getMessage());
            $this->requestService->errorResponse('Failed to update booking status', 500);
        }
    }
}