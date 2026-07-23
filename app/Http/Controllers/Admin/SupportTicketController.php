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

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

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
                    LEFT JOIN users u ON st.user_id = u.id
                    LEFT JOIN users a ON st.assigned_to = a.id
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
            $countStmt = $this->db->query("SELECT COUNT(*) as total FROM support_tickets");
            $total = (int)($countStmt->fetch()['total'] ?? 0);

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
        } catch (\Exception $e) {
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
            // Get users and users for dropdowns using models
            $users = \App\Models\User::getCustomers('all', ['id', 'name', 'email']);
            $users = \App\Models\User::getAgents('active', ['admin', 'support', 'associate'], ['id', 'name', 'email']);

            $data = [
                'page_title' => 'Create Support Ticket - APS Dream Home',
                'active_page' => 'support_tickets',
                'users' => $users,
                'users' => $users
            ];

            return $this->render('admin/support_tickets/create', $data);
        } catch (\Exception $e) {
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

                // Send creation email to customer
                try {
                    $emailSvc = new \App\Services\EmailTemplateService();
                    $emailSvc->sendSupportTicketCreated((int)$data['customer_id'], [
                        'ticket_number' => $ticketNumber,
                        'subject' => $data['subject'],
                        'description' => $data['description'],
                        'priority' => $data['priority'],
                    ]);
                } catch (\Throwable $e) {
                    error_log("[SupportTicketController] store email failed: " . $e->getMessage());
                }

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Support ticket created successfully',
                    'ticket_id' => $ticketId,
                    'ticket_number' => $ticketNumber
                ]);
            }

            return $this->jsonError('Failed to create support ticket', 500);
        } catch (\Exception $e) {
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

            $service = new \App\Services\SupportTicketService();
            $ticket = $service->getTicket($ticketId);

            if (!$ticket) {
                $this->setFlash('error', 'Ticket not found');
                return $this->redirect('admin/support_tickets');
            }

            // Get staff members for assignment dropdown
            $staffStmt = $this->db->query("SELECT id, name, email FROM users WHERE role IN ('admin','employee') ORDER BY name");
            $staffMembers = $staffStmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Ticket ' . ($ticket['ticket_number'] ?? '#' . $ticketId) . ' - APS Dream Home',
                'active_page' => 'support_tickets',
                'ticket' => $ticket,
                'replies' => $ticket['replies'] ?? [],
                'staffMembers' => $staffMembers,
            ];

            return $this->render('admin/support_tickets/show', $data);
        } catch (\Exception $e) {
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

            // Get dropdown options using models
            $users = \App\Models\User::getCustomers('all', ['id', 'name', 'email']);
            $users = \App\Models\User::getAgents('active', ['admin', 'support', 'associate'], ['id', 'name', 'email']);

            $data = [
                'page_title' => 'Edit Support Ticket - APS Dream Home',
                'active_page' => 'support_tickets',
                'ticket' => $ticket,
                'users' => $users,
                'users' => $users
            ];

            return $this->render('admin/support_tickets/edit', $data);
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            $this->loggingService->error("Get Support Ticket Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * Staff reply to a ticket
     */
    public function reply($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $ticketId = intval($id);
            $message = trim($_POST['message'] ?? '');

            if ($ticketId <= 0) {
                return $this->jsonError('Invalid ticket ID', 400);
            }
            if (empty($message)) {
                return $this->jsonError('Reply message is required', 400);
            }

            $sql = "SELECT id FROM support_tickets WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ticketId]);
            if (!$stmt->fetch()) {
                return $this->jsonError('Ticket not found', 404);
            }

            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

            $service = new \App\Services\SupportTicketService();
            $service->addReply($ticketId, $userId, $message, true);

            $this->loggingService->logUserActivity($userId, 'support_ticket_reply', [
                'ticket_id' => $ticketId,
            ]);

            // Send reply email to customer
            try {
                $ticket = $this->db->fetchOne("SELECT st.user_id, st.ticket_number, st.subject FROM support_tickets st WHERE st.id = ?", [$ticketId]);
                if (!empty($ticket['user_id'])) {
                    $emailSvc = new \App\Services\EmailTemplateService();
                    $emailSvc->sendSupportTicketReply($ticket['user_id'], [
                        'ticket_number' => $ticket['ticket_number'],
                        'subject' => $ticket['subject'],
                        'reply_message' => $message,
                        'replied_by' => 'Support Team',
                    ]);
                }
            } catch (\Throwable $e) {
                error_log("[SupportTicketController] reply email failed: " . $e->getMessage());
            }

            $_SESSION['flash_success'] = 'Reply sent successfully!';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/support-tickets/' . $ticketId);
            exit;
        } catch (\Exception $e) {
            $this->loggingService->error("Support Ticket Reply error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to send reply.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/support-tickets/' . $id);
            exit;
        }
    }

    /**
     * Assign ticket to staff member
     */
    public function assign($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $ticketId = intval($id);
            $staffId = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

            if ($ticketId <= 0) {
                return $this->jsonError('Invalid ticket ID', 400);
            }

            $service = new \App\Services\SupportTicketService();
            $service->assignTicket($ticketId, $staffId);

            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'support_ticket_assigned', [
                'ticket_id' => $ticketId,
                'assigned_to' => $staffId,
            ]);

            $_SESSION['flash_success'] = 'Ticket assigned successfully!';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/support-tickets/' . $ticketId);
            exit;
        } catch (\Exception $e) {
            $this->loggingService->error("Support Ticket Assign error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to assign ticket.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/support-tickets/' . $id);
            exit;
        }
    }

    /**
     * Update ticket status
     */
    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $ticketId = intval($id);
            $status = trim($_POST['status'] ?? '');

            if ($ticketId <= 0) {
                return $this->jsonError('Invalid ticket ID', 400);
            }

            $validStatuses = ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'];
            if (!in_array($status, $validStatuses)) {
                return $this->jsonError('Invalid status', 400);
            }

            $service = new \App\Services\SupportTicketService();
            $service->updateStatus($ticketId, $status);

            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'support_ticket_status_changed', [
                'ticket_id' => $ticketId,
                'new_status' => $status,
            ]);

            $_SESSION['flash_success'] = 'Ticket status updated to ' . str_replace('_', ' ', $status) . '!';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/support-tickets/' . $ticketId);
            exit;
        } catch (\Exception $e) {
            $this->loggingService->error("Support Ticket UpdateStatus error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to update status.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/support-tickets/' . $id);
            exit;
        }
    }
}
