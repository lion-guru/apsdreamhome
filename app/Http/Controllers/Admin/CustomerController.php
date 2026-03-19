<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Customer Controller - Custom MVC Implementation
 * Handles customer management operations in the Admin panel
 */
class CustomerController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
        
        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'updateProfile', 'createAlert', 'toggleFavorite', 'sendInvitation', 'acceptInvitation']]);
    }

    /**
     * Search customers for Select2
     */
    public function search()
    {
        $search = $_GET['search'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        try {
            $result = $this->searchCustomers($search, $limit, $offset);

            return $this->jsonResponse([
                'items' => $result['items'],
                'more' => ($page * $limit) < $result['total']
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Display a listing of the customers.
     */
    public function index()
    {
        try {
            $searchTerm = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT c.*, COUNT(b.id) as booking_count
                    FROM users c
                    LEFT JOIN bookings b ON c.id = b.customer_id
                    WHERE c.role = 'customer'";
            $params = [];

            // Apply filters
            if (!empty($searchTerm)) {
                $sql .= " AND (c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
                $searchParam = '%' . $searchTerm . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND c.status = ?";
                $params[] = $status;
            }

            $sql .= " GROUP BY c.id ORDER BY c.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT c.*, COUNT(b.id) as booking_count", "SELECT COUNT(DISTINCT c.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $customers = $stmt->fetchAll();

            $data = [
                'page_title' => 'Customers - APS Dream Home',
                'active_page' => 'customers',
                'customers' => $customers,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $searchTerm,
                    'status' => $status
                ]
            ];

            return $this->render('admin/customers/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Customer Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load customers');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        try {
            $data = [
                'page_title' => 'Create Customer - APS Dream Home',
                'active_page' => 'customers'
            ];

            return $this->render('admin/customers/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Customer Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load customer form');
            return $this->redirect('admin/customers');
        }
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['name', 'email', 'phone'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst($field) . ' is required', 400);
                }
            }

            // Validate email
            $email = CoreFunctionsServiceCustom::validateInput($data['email'], 'email');
            if (!$email) {
                return $this->jsonError('Invalid email address', 400);
            }

            // Check if email already exists
            $sql = "SELECT id FROM users WHERE email = ? AND role = 'customer'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return $this->jsonError('Email already registered', 400);
            }

            // Validate phone
            $phone = CoreFunctionsServiceCustom::validateInput($data['phone'], 'phone');
            if ($phone === false) {
                return $this->jsonError('Invalid phone number', 400);
            }

            // Generate username and password
            $username = $this->generateUsername($data['name']);
            $password = $this->generateSecurePassword();

            // Insert customer
            $sql = "INSERT INTO users (name, email, phone, address, username, password, role, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'customer', 'active', NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($data['name'], 'string'),
                $email,
                $phone,
                CoreFunctionsServiceCustom::validateInput($data['address'] ?? '', 'string'),
                $username,
                CoreFunctionsServiceCustom::hashPassword($password)
            ]);

            if ($result) {
                $customerId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'customer_created', [
                    'customer_id' => $customerId,
                    'email' => $email
                ]);

                // Send welcome credentials
                $this->sendCustomerCredentials($customerId, $username, $password, $email, $phone);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Customer created successfully',
                    'customer_id' => $customerId
                ]);
            }

            return $this->jsonError('Failed to create customer', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Customer Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create customer', 500);
        }
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        try {
            $customerId = intval($id);
            if ($customerId <= 0) {
                $this->setFlash('error', 'Invalid customer ID');
                return $this->redirect('admin/customers');
            }

            // Get customer details
            $sql = "SELECT c.*, 
                           COUNT(b.id) as total_bookings,
                           COALESCE(SUM(b.total_amount), 0) as total_spent
                    FROM users c
                    LEFT JOIN bookings b ON c.id = b.customer_id
                    WHERE c.id = ? AND c.role = 'customer'
                    GROUP BY c.id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch();

            if (!$customer) {
                $this->setFlash('error', 'Customer not found');
                return $this->redirect('admin/customers');
            }

            // Get customer bookings
            $sql = "SELECT b.*, p.title as property_title, p.location as property_location
                    FROM bookings b
                    LEFT JOIN properties p ON b.property_id = p.id
                    WHERE b.customer_id = ?
                    ORDER BY b.created_at DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$customerId]);
            $bookings = $stmt->fetchAll();

            $data = [
                'page_title' => 'Customer Details - APS Dream Home',
                'active_page' => 'customers',
                'customer' => $customer,
                'bookings' => $bookings
            ];

            return $this->render('admin/customers/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Customer Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load customer details');
            return $this->redirect('admin/customers');
        }
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        try {
            $customerId = intval($id);
            if ($customerId <= 0) {
                $this->setFlash('error', 'Invalid customer ID');
                return $this->redirect('admin/customers');
            }

            // Get customer details
            $sql = "SELECT * FROM users WHERE id = ? AND role = 'customer'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch();

            if (!$customer) {
                $this->setFlash('error', 'Customer not found');
                return $this->redirect('admin/customers');
            }

            $data = [
                'page_title' => 'Edit Customer - APS Dream Home',
                'active_page' => 'customers',
                'customer' => $customer
            ];

            return $this->render('admin/customers/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Customer Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load customer form');
            return $this->redirect('admin/customers');
        }
    }

    /**
     * Update the specified customer in storage.
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $customerId = intval($id);
            if ($customerId <= 0) {
                return $this->jsonError('Invalid customer ID', 400);
            }

            $data = $_POST;

            // Check if customer exists
            $sql = "SELECT id FROM users WHERE id = ? AND role = 'customer'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$customerId]);
            if (!$stmt->fetch()) {
                return $this->jsonError('Customer not found', 404);
            }

            // Validate email if provided
            if (!empty($data['email'])) {
                $email = CoreFunctionsServiceCustom::validateInput($data['email'], 'email');
                if (!$email) {
                    return $this->jsonError('Invalid email address', 400);
                }

                // Check if email already exists for another customer
                $sql = "SELECT id FROM users WHERE email = ? AND id != ? AND role = 'customer'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$email, $customerId]);
                if ($stmt->fetch()) {
                    return $this->jsonError('Email already exists', 400);
                }
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (!empty($data['name'])) {
                $updateFields[] = "name = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['name'], 'string');
            }

            if (!empty($data['email'])) {
                $updateFields[] = "email = ?";
                $updateValues[] = $email;
            }

            if (!empty($data['phone'])) {
                $phone = CoreFunctionsServiceCustom::validateInput($data['phone'], 'phone');
                if ($phone === false) {
                    return $this->jsonError('Invalid phone number', 400);
                }
                $updateFields[] = "phone = ?";
                $updateValues[] = $phone;
            }

            if (isset($data['address'])) {
                $updateFields[] = "address = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['address'], 'string');
            }

            if (isset($data['status'])) {
                $validStatuses = ['active', 'inactive', 'suspended'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $customerId;

            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'customer_updated', [
                    'customer_id' => $customerId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Customer updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update customer', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Customer Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update customer', 500);
        }
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $customerId = intval($id);
            if ($customerId <= 0) {
                return $this->jsonError('Invalid customer ID', 400);
            }

            // Check if customer exists
            $sql = "SELECT * FROM users WHERE id = ? AND role = 'customer'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch();

            if (!$customer) {
                return $this->jsonError('Customer not found', 404);
            }

            // Check if customer has bookings
            $sql = "SELECT COUNT(*) as booking_count FROM bookings WHERE customer_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$customerId]);
            $bookingCount = $stmt->fetch()['booking_count'];

            if ($bookingCount > 0) {
                return $this->jsonError('Cannot delete customer with existing bookings', 400);
            }

            // Delete customer
            $sql = "DELETE FROM users WHERE id = ? AND role = 'customer'";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$customerId]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'customer_deleted', [
                    'customer_id' => $customerId,
                    'customer_email' => $customer['email']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Customer deleted successfully'
                ]);
            }

            return $this->jsonError('Failed to delete customer', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Customer Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete customer', 500);
        }
    }

    /**
     * Search customers helper method
     */
    private function searchCustomers(string $search, int $limit, int $offset): array
    {
        try {
            $sql = "SELECT id, name, email, phone as text
                    FROM users
                    WHERE role = 'customer' AND status = 'active'
                    AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)
                    ORDER BY name ASC
                    LIMIT ? OFFSET ?";
            
            $searchParam = '%' . $search . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$searchParam, $searchParam, $searchParam, $limit, $offset]);
            $items = $stmt->fetchAll();

            // Count total
            $sql = "SELECT COUNT(*) as total
                    FROM users
                    WHERE role = 'customer' AND status = 'active'
                    AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$searchParam, $searchParam, $searchParam]);
            $total = $stmt->fetch()['total'];

            return [
                'items' => $items,
                'total' => (int)$total
            ];
        } catch (Exception $e) {
            $this->loggingService->error("Search Customers error: " . $e->getMessage());
            return ['items' => [], 'total' => 0];
        }
    }

    /**
     * Generate username from customer name
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
     * Generate secure password
     */
    private function generateSecurePassword(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        return substr(str_shuffle($chars), 0, 12);
    }

    /**
     * Send customer credentials
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
            $message .= "Thank you for choosing APS Dream Home!";

            // Send email
            if (!empty($email)) {
                // Placeholder for email sending
                error_log("Email sent to $email: $subject");
            }

            // Send SMS
            if (!empty($phone)) {
                $smsMessage = "Welcome to APS Dream Home! Username: $username, Password: $password. Login at: " . BASE_URL . "/login";
                error_log("SMS sent to $phone: $smsMessage");
            }
        } catch (Exception $e) {
            $this->loggingService->error("Send Customer Credentials error: " . $e->getMessage());
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error helper
     */
    private function jsonError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}