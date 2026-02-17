<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\SupportTicketService;

class SupportTicketController extends AdminController
{
    protected $ticketService;

    public function __construct()
    {
        parent::__construct();
        $this->ticketService = new SupportTicketService();
    }

    public function index()
    {
        // Check if admin or regular user
        $user = $this->auth->user();
        if (!$user) {
            $this->redirect('login');
            return;
        }

        if ($user->role === 'admin' || $user->role === 'super_admin' || $user->role === 'support') {
            $tickets = $this->ticketService->getAllTickets();
        } else {
            $tickets = $this->ticketService->getTicketsByUser($user->id);
        }

        $this->data['tickets'] = $tickets;
        $this->data['page_title'] = $this->mlSupport->translate('Support Tickets');
        $this->render('admin/tickets/index');
    }

    public function create()
    {
        $this->data['page_title'] = $this->mlSupport->translate('Create Ticket');
        $this->render('admin/tickets/create');
    }

    public function store()
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->data['error'] = $this->mlSupport->translate('Invalid CSRF token.');
                $this->render('admin/tickets/create');
                return;
            }

            $data = [
                'subject' => $_POST['subject'] ?? '',
                'message' => $_POST['message'] ?? '',
                'priority' => $_POST['priority'] ?? 'medium',
            ];

            // Basic validation
            if (empty($data['subject']) || empty($data['message'])) {
                // Flash error
                $this->data['error'] = $this->mlSupport->translate('Subject and message are required.');
                $this->render('admin/tickets/create');
                return;
            }

            // Handle file upload
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $data['attachment'] = $this->handleFileUpload($_FILES['attachment']);
            }

            $this->ticketService->createTicket($data, $user->id);
            $this->redirect('admin/tickets');
        }
    }

    public function show($id)
    {
        $ticket = $this->ticketService->getTicketById($id);

        if (!$ticket) {
            $this->redirect('admin/tickets'); // Or 404
            return;
        }

        // Access control: only owner or admin/support can view
        $user = $this->auth->user();
        if ($user->role !== 'admin' && $user->role !== 'super_admin' && $user->role !== 'support' && $ticket['user_id'] != $user->id) {
            $this->redirect('admin/tickets'); // Unauthorized
            return;
        }

        $this->data['ticket'] = $ticket;
        $this->data['page_title'] = $this->mlSupport->translate('Ticket #') . $ticket['ticket_number'];
        $this->render('admin/tickets/show');
    }

    public function reply($id)
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                // Should probably show error, but for reply just redirect back
                $this->redirect('admin/tickets/' . $id);
                return;
            }

            $message = $_POST['message'] ?? '';
            $attachment = null;

            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $attachment = $this->handleFileUpload($_FILES['attachment']);
            }

            if (!empty($message) || $attachment) {
                $this->ticketService->addReply($id, $user->id, $message, $attachment);
            }

            $this->redirect('admin/tickets/' . $id);
        }
    }

    public function updateStatus($id)
    {
        $user = $this->auth->user();
        // Only admin can update status usually, or user can close their own ticket
        if (!$user) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->redirect('admin/tickets/' . $id);
                return;
            }

            $status = $_POST['status'] ?? '';
            if (in_array($status, ['open', 'in_progress', 'resolved', 'closed'])) {
                $this->ticketService->updateTicketStatus($id, $status);
            }
            $this->redirect('admin/tickets/' . $id);
        }
    }

    protected function handleFileUpload($file, $allowedTypes = [], $maxSize = 5242880)
    {
        $uploadDir = 'storage/uploads/tickets/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . basename($file['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $targetFile;
        }

        return null;
    }
}
