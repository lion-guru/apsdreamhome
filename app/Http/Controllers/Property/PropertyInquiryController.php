<?php
/**
 * Property Inquiry Controller
 * Handles property inquiries functionality
 */

namespace App\Controllers;

class PropertyInquiryController extends BaseController {
    public function __construct() {
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Submit property inquiry (AJAX endpoint)
     */
    public function submit() {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF token validation failed.']);
            return;
        }

        // Get POST data
        $property_id = (int)($_POST['property_id'] ?? 0);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $inquiry_type = $_POST['inquiry_type'] ?? 'general';

        // Guest information (if not logged in)
        $guest_name = trim($_POST['guest_name'] ?? '');
        $guest_email = trim($_POST['guest_email'] ?? '');
        $guest_phone = trim($_POST['guest_phone'] ?? '');

        // Validate required fields
        if (!$property_id || empty($subject) || empty($message)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            return;
        }

        // Check if property exists
        if (!$this->propertyExists($property_id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Property not found']);
            return;
        }

        // If user is logged in, use their info
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // If not logged in, validate guest information
        if (!$user_id) {
            if (empty($guest_name) || empty($guest_email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Please provide your name and email']);
                return;
            }

            // Basic email validation
            if (!filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Please provide a valid email address']);
                return;
            }
        }

        try {
            // Create inquiry
            $inquiry_id = $this->createInquiry([
                'property_id' => $property_id,
                'user_id' => $user_id,
                'guest_name' => $guest_name,
                'guest_email' => $guest_email,
                'guest_phone' => $guest_phone,
                'subject' => $subject,
                'message' => $message,
                'inquiry_type' => $inquiry_type
            ]);

            if ($inquiry_id) {
                // Send notification email (if configured)
                $this->sendInquiryNotification($inquiry_id);

                echo json_encode([
                    'success' => true,
                    'message' => 'Your inquiry has been submitted successfully! We will get back to you soon.'
                ]);
            } else {
                throw new Exception('Failed to create inquiry');
            }

        } catch (Exception $e) {
            error_log('Submit inquiry error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred while submitting your inquiry. Please try again.']);
        }
    }

    /**
     * Display admin inquiries management page
     */
    public function adminIndex() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // Set page data
        $this->data['page_title'] = 'Property Inquiries - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => BASE_URL . 'admin'],
            ['title' => 'Inquiries', 'url' => BASE_URL . 'admin/inquiries']
        ];

        // Get filter parameters
        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'type' => $_GET['type'] ?? 'all',
            'priority' => $_GET['priority'] ?? 'all',
            'search' => $_GET['search'] ?? '',
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20),
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Get inquiries data
        $this->data['inquiries'] = $this->getAdminInquiries($filters);
        $this->data['total_inquiries'] = $this->getAdminTotalInquiries($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_inquiries'] / $filters['per_page']);
        $this->data['start_index'] = ($filters['page'] - 1) * $filters['per_page'] + 1;
        $this->data['end_index'] = min($filters['page'] * $filters['per_page'], $this->data['total_inquiries']);

        // Render the inquiries page
        $this->render('admin/inquiries');
    }

    /**
     * View inquiry details (admin)
     */
    public function view() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        $inquiry_id = (int)($_GET['id'] ?? 0);

        if (!$inquiry_id) {
            header('Location: ' . BASE_URL . 'admin/inquiries');
            exit();
        }

        // Get inquiry details
        $this->data['inquiry'] = $this->getInquiryById($inquiry_id);

        if (!$this->data['inquiry']) {
            header('Location: ' . BASE_URL . 'admin/inquiries');
            exit();
        }

        // Set page data
        $this->data['page_title'] = 'Inquiry Details - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => BASE_URL . 'admin'],
            ['title' => 'Inquiries', 'url' => BASE_URL . 'admin/inquiries'],
            ['title' => 'Details', 'url' => BASE_URL . 'admin/inquiries/view?id=' . $inquiry_id]
        ];

        // Render the inquiry details page
        $this->render('admin/inquiry_details');
    }

    /**
     * Update inquiry status (AJAX endpoint)
     */
    public function updateStatus() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $inquiry_id = (int)($_POST['inquiry_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $response_message = trim($_POST['response_message'] ?? '');

        if (!$inquiry_id || empty($status)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }

        try {
            $this->updateInquiryStatus($inquiry_id, $status, $response_message, $_SESSION['user_id']);

            echo json_encode([
                'success' => true,
                'message' => 'Inquiry status updated successfully'
            ]);

        } catch (Exception $e) {
            error_log('Update inquiry status error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Check if property exists
     */
    private function propertyExists($property_id) {
        try {
            global $pdo;
            if (!$pdo) {
                return false;
            }

            $stmt = $pdo->prepare("SELECT id FROM properties WHERE id = ?");
            $stmt->execute([$property_id]);
            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            error_log('Property exists check error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create new inquiry
     */
    private function createInquiry($data) {
        try {
            global $pdo;
            if (!$pdo) {
                throw new Exception('Database connection not available');
            }

            $sql = "
                INSERT INTO property_inquiries
                (property_id, user_id, guest_name, guest_email, guest_phone, subject, message, inquiry_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['property_id'],
                $data['user_id'],
                $data['guest_name'],
                $data['guest_email'],
                $data['guest_phone'],
                $data['subject'],
                $data['message'],
                $data['inquiry_type']
            ]);

            return $pdo->lastInsertId();

        } catch (Exception $e) {
            error_log('Create inquiry error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send inquiry notification email
     */
    private function sendInquiryNotification($inquiry_id) {
        try {
            $emailNotification = new \App\Core\EmailNotification();
            return $emailNotification->sendInquiryNotification($inquiry_id);
        } catch (Exception $e) {
            error_log('Send inquiry notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get inquiries for admin with filters
     */
    private function getAdminInquiries($filters) {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $where_conditions = [];
            $params = [];

            // Status filter
            if ($filters['status'] !== 'all') {
                $where_conditions[] = "pi.status = ?";
                $params[] = $filters['status'];
            }

            // Type filter
            if ($filters['type'] !== 'all') {
                $where_conditions[] = "pi.inquiry_type = ?";
                $params[] = $filters['type'];
            }

            // Priority filter
            if ($filters['priority'] !== 'all') {
                $where_conditions[] = "pi.priority = ?";
                $params[] = $filters['priority'];
            }

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(pi.subject LIKE ? OR pi.message LIKE ? OR pi.guest_name LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
                $search_term = '%' . $filters['search'] . '%';
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            // Build ORDER BY clause
            $allowed_sorts = ['id', 'created_at', 'status', 'priority'];
            $sort = in_array($filters['sort'], $allowed_sorts) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $order_clause = "ORDER BY pi.{$sort} {$order}";

            $sql = "
                SELECT
                    pi.*,
                    p.title as property_title,
                    p.city,
                    p.state,
                    u.name as user_name,
                    u.email as user_email,
                    u.phone as user_phone
                FROM property_inquiries pi
                JOIN properties p ON pi.property_id = p.id
                LEFT JOIN users u ON pi.user_id = u.id
                {$where_clause}
                {$order_clause}
                LIMIT {$filters['per_page']} OFFSET " . (($filters['page'] - 1) * $filters['per_page']);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Admin inquiries query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total inquiries count for pagination
     */
    private function getAdminTotalInquiries($filters) {
        try {
            global $pdo;
            if (!$pdo) {
                return 0;
            }

            $where_conditions = [];
            $params = [];

            // Status filter
            if ($filters['status'] !== 'all') {
                $where_conditions[] = "pi.status = ?";
                $params[] = $filters['status'];
            }

            // Type filter
            if ($filters['type'] !== 'all') {
                $where_conditions[] = "pi.inquiry_type = ?";
                $params[] = $filters['type'];
            }

            // Priority filter
            if ($filters['priority'] !== 'all') {
                $where_conditions[] = "pi.priority = ?";
                $params[] = $filters['priority'];
            }

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(pi.subject LIKE ? OR pi.message LIKE ? OR pi.guest_name LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
                $search_term = '%' . $filters['search'] . '%';
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            $sql = "SELECT COUNT(*) as total FROM property_inquiries pi JOIN properties p ON pi.property_id = p.id LEFT JOIN users u ON pi.user_id = u.id {$where_clause}";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['total'] ?? 0);

        } catch (Exception $e) {
            error_log('Admin total inquiries query error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get inquiry by ID
     */
    private function getInquiryById($inquiry_id) {
        try {
            global $pdo;
            if (!$pdo) {
                return null;
            }

            $sql = "
                SELECT
                    pi.*,
                    p.title as property_title,
                    p.city,
                    p.state,
                    u.name as user_name,
                    u.email as user_email,
                    u.phone as user_phone
                FROM property_inquiries pi
                JOIN properties p ON pi.property_id = p.id
                LEFT JOIN users u ON pi.user_id = u.id
                WHERE pi.id = ?
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$inquiry_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Get inquiry by ID error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update inquiry status
     */
    private function updateInquiryStatus($inquiry_id, $status, $response_message, $user_id) {
        try {
            global $pdo;
            if (!$pdo) {
                throw new Exception('Database connection not available');
            }

            $update_data = [
                'status' => $status,
                'response_message' => $response_message,
                'responded_at' => date('Y-m-d H:i:s'),
                'responded_by' => $user_id
            ];

            $sql = "
                UPDATE property_inquiries
                SET status = :status, response_message = :response_message,
                    responded_at = :responded_at, responded_by = :responded_by,
                    updated_at = NOW()
                WHERE id = :inquiry_id
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':status' => $status,
                ':response_message' => $response_message,
                ':responded_at' => $update_data['responded_at'],
                ':responded_by' => $user_id,
                ':inquiry_id' => $inquiry_id
            ]);

        } catch (Exception $e) {
            error_log('Update inquiry status error: ' . $e->getMessage());
            throw $e;
        }
    }
}

?>
