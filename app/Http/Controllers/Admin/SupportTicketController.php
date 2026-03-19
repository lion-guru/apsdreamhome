<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Support Ticket Controller - Custom MVC Implementation
 * Handles support ticket management operations in Admin panel
 */
class SupportTicketController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
        
        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display support tickets list
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $priority = $_GET['priority'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT st.*, 
                           u.name as customer_name,
                           u.email as customer_email,
                           a.name as assigned_agent_name,
                           a.email as assigned_agent_email
                    FROM support_tickets st
                    LEFT JOIN users u ON st.customer_id = u.id
                    LEFT JOIN users a ON st.assigned_agent_id = a.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (st.subject LIKE ? OR st.description LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND st.status = ?";
                $params[] = $status;
            }

            if (!empty($priority)) {
                $sql .= " AND st.priority = ?";
                $params[] = $priority;
            }

            $sql .= " ORDER BY st.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT st.*, u.name as customer_name, u.email as customer_email, a.name as assigned_agent_name, a.email as assigned_agent_email", "SELECT COUNT(DISTINCT st.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Support Tickets - APS Dream Home',
                'active_page' => 'support_tickets',
                'tickets' => $tickets,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'priority' => $priority
                ]
            ];

            return $this->render('admin/support_tickets/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Support Tickets Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load support tickets');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new support ticket
     */
    public function create()
    {
        try {
            // Get customers and agents for dropdowns
            $customers = $this->db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $agents = $this->db->query("SELECT id, name, email FROM users WHERE role IN ('admin', 'support', 'associate') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Create Support Ticket - APS Dream Home',
                'active_page' => 'support_tickets',
                'customers' => $customers,
                'agents' => $agents
            ];

            return $this->render('admin/support_tickets/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Support Ticket Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load ticket form');
            return $this->redirect('admin/support_tickets');
        }
    }

    /**
     * Store a newly created support ticket
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['customer_id', 'subject', 'description', 'priority'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate priority
            $validPriorities = ['low', 'medium', 'high', 'urgent'];
            if (!in_array($data['priority'], $validPriorities)) {
                return $this->jsonError('Invalid priority level', 400);
            }

            // Generate ticket number
            $ticketNumber = 'TKT' . date('YmdHis') . rand(1000, 9999);

            // Insert ticket
            $sql = "INSERT INTO support_tickets 
                    (ticket_number, customer_id, subject, description, priority, 
                     category, status, assigned_agent_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $ticketNumber,
                (int)$data['customer_id'],
                CoreFunctionsServiceCustom::validateInput($data['subject'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['description'], 'string'),
                $data['priority'],
                CoreFunctionsServiceCustom::validateInput($data['category'] ?? 'general', 'string'),
                'open',
                !empty($data['assigned_agent_id']) ? (int)$data['assigned_agent_id'] : null
            ]);

            if ($result) {
                $ticketId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'support_ticket_created', [
                    'ticket_id' => $ticketId,
                    'ticket_number' => $ticketNumber,
                    'customer_id' => $data['customer_id']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Support ticket created successfully',
                    'ticket_id' => $ticketId,
                    'ticket_number' => $ticketNumber
                ]);
            }

            return $this->jsonError('Failed to create support ticket', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Support Ticket Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create support ticket', 500);
        }
    }

    /**
     * Display the specified support ticket
     */
    public function show($id)
    {
        try {
            $ticketId = intval($id);
            if ($ticketId <= 0) {
                $this->setFlash('error', 'Invalid ticket ID');
                return $this->redirect('admin/support_tickets');
            }

            // Get ticket details
            $sql = "SELECT st.*, 
                           u.name as customer_name,
                           u.email as customer_email,
                           u.phone as customer_phone,
                           a.name as assigned_agent_name,
                           a.email as assigned_agent_email
                    FROM support_tickets st
                    LEFT JOIN users u ON st.customer_id = u.id
                    LEFT JOIN users a ON st.assigned_agent_id = a.id
                    WHERE st.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$ticket) {
                $this->setFlash('error', 'Ticket not found');
                return $this->redirect('admin/support_tickets');
            }

            // Get ticket responses
            $sql = "SELECT * FROM support_ticket_responses 
                    WHERE ticket_id = ? ORDER BY created_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ticketId]);
            $responses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Ticket Details - APS Dream Home',
                'active_page' => 'support_tickets',
                'ticket' => $ticket,
                'responses' => $responses
            ];

            return $this->render('admin/support_tickets/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Support Ticket Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load ticket details');
            return $this->redirect('admin/support_tickets');
        }
    }

    /**
     * Show the form for editing the specified support ticket
     */
    public function edit($id)
    {
        try {
            $ticketId = intval($id);
            if ($ticketId <= 0) {
                $this->setFlash('error', 'Invalid ticket ID');
                return $this->redirect('admin/support_tickets');
            }

            // Get ticket details
            $sql = "SELECT * FROM support_tickets WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$ticket) {
                $this->setFlash('error', 'Ticket not found');
                return $this->redirect('admin/support_tickets');
            }

            // Get dropdown options
            $customers = $this->db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $agents = $this->db->query("SELECT id, name, email FROM users WHERE role IN ('admin', 'support', 'associate') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Edit Support Ticket - APS Dream Home',
                'active_page' => 'support_tickets',
                'ticket' => $ticket,
                'customers' => $customers,
                'agents' => $agents
            ];

            return $this->render('admin/support_tickets/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Support Ticket Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load ticket form');
            return $this->redirect('admin/support_tickets');
        }
    }

    /**
     * Update the specified support ticket
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $ticketId = intval($id);
            if ($ticketId <= 0) {
                return $this->jsonError('Invalid ticket ID', 400);
            }

            $data = $_POST;

            // Check if ticket exists
            $sql = "SELECT * FROM support_tickets WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$ticket) {
                return $this->jsonError('Ticket not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (isset($data['customer_id'])) {
                $updateFields[] = "customer_id = ?";
                $updateValues[] = (int)$data['customer_id'];
            }

            if (isset($data['subject'])) {
                $updateFields[] = "subject = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['subject'], 'string');
            }

            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['description'], 'string');
            }

            if (isset($data['priority'])) {
                $validPriorities = ['low', 'medium', 'high', 'urgent'];
                if (in_array($data['priority'], $validPriorities)) {
                    $updateFields[] = "priority = ?";
                    $updateValues[] = $data['priority'];
                }
            }

            if (isset($data['category'])) {
                $updateFields[] = "category = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['category'], 'string');
            }

            if (isset($data['status'])) {
                $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            if (isset($data['assigned_agent_id'])) {
                $updateFields[] = "assigned_agent_id = ?";
                $updateValues[] = !empty($data['assigned_agent_id']) ? (int)$data['assigned_agent_id'] : null;
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $ticketId;

            $sql = "UPDATE support_tickets SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'support_ticket_updated', [
                    'ticket_id' => $ticketId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Support ticket updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update support ticket', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Support Ticket Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update support ticket', 500);
        }
    }

    /**
     * Remove the specified support ticket
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $ticketId = intval($id);
            if ($ticketId <= 0) {
                return $this->jsonError('Invalid ticket ID', 400);
            }

            // Check if ticket exists
            $sql = "SELECT * FROM support_tickets WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$ticket) {
                return $this->jsonError('Ticket not found', 404);
            }

            // Delete ticket and responses
            $this->db->beginTransaction();

            try {
                // Delete responses first
                $sql = "DELETE FROM support_ticket_responses WHERE ticket_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$ticketId]);

                // Delete ticket
                $sql = "DELETE FROM support_tickets WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$ticketId]);

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'support_ticket_deleted', [
                    'ticket_id' => $ticketId,
                    'ticket_number' => $ticket['ticket_number']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Support ticket deleted successfully'
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Support Ticket Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete support ticket', 500);
        }
    }

    /**
     * Get support ticket statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total tickets
            $sql = "SELECT COUNT(*) as total FROM support_tickets";
            $result = $this->db->fetchOne($sql);
            $stats['total_tickets'] = (int)($result['total'] ?? 0);

            // Tickets by status
            $sql = "SELECT status, COUNT(*) as count FROM support_tickets GROUP BY status";
            $result = $this->db->fetchAll($sql);
            $stats['by_status'] = $result ?: [];

            // Tickets by priority
            $sql = "SELECT priority, COUNT(*) as count FROM support_tickets GROUP BY priority";
            $result = $this->db->fetchAll($sql);
            $stats['by_priority'] = $result ?: [];

            // Recent tickets
            $sql = "SELECT * FROM support_tickets ORDER BY created_at DESC LIMIT 10";
            $result = $this->db->fetchAll($sql);
            $stats['recent_tickets'] = $result ?: [];

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Support Ticket Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
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