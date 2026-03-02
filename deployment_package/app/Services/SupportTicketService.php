<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Core\Auth;
use Exception;

class SupportTicketService
{
    protected $ticketModel;
    protected $replyModel;
    protected $auth;

    public function __construct()
    {
        $this->ticketModel = new SupportTicket();
        $this->replyModel = new TicketReply();
        $this->auth = new Auth();
    }

    public function getAllTickets()
    {
        // RBAC: Only admin can see all tickets
        if (!$this->auth->isAdmin()) {
            return []; // Or throw exception
        }
        return $this->ticketModel->getAllTicketsWithUser();
    }

    public function getTicketsByUser($userId)
    {
        // RBAC: User can only see their own tickets unless admin
        if (!$this->auth->isAdmin() && $this->auth->id() != $userId) {
            return []; // Unauthorized
        }
        return $this->ticketModel->getTicketsByUser($userId);
    }

    public function getTicketById($id)
    {
        $ticket = $this->ticketModel->find($id);
        
        if (!$ticket) {
            return null;
        }

        // RBAC Check
        // Assuming $ticket is an object or array. Model returns object usually, but find() in basic model might return object.
        // Let's check App\Core\Model implementation. 
        // App\Core\Model::find returns object if using fetchObject or array if using fetchAll?
        // App\Core\Model uses PDO::FETCH_OBJ by default in constructor options.
        // But `SupportTicket::getTicketByNumber` uses `fetch(PDO::FETCH_ASSOC)`.
        // `Model::find` is not shown in my read, but usually it returns an object.
        // Let's assume object access first, or check if it's array.
        // Wait, `SupportTicketService` line 33 used `$ticket['replies']`, implying array access.
        // But `App\Core\Model` sets default fetch mode to OBJ.
        // This suggests `SupportTicket` model might override it or `find` returns array.
        // Let's use array access as per previous code usage.
        
        // However, if `find` returns an object (standard in Laravel-like models), array access might fail unless it implements ArrayAccess.
        // In `SupportTicketService.php` before edit:
        // $ticket = $this->ticketModel->find($id);
        // $ticket['replies'] = ...
        // This strongly suggests it returns an array or ArrayAccess object.
        // I'll stick to array access but add a check.

        $ticketUserId = is_array($ticket) ? $ticket['user_id'] : $ticket->user_id;

        if (!$this->auth->isAdmin() && $this->auth->id() != $ticketUserId) {
            return null; // Unauthorized
        }

        if ($ticket) {
            $replies = $this->replyModel->getRepliesByTicketId($id);
            if (is_array($ticket)) {
                $ticket['replies'] = $replies;
            } else {
                $ticket->replies = $replies;
            }
        }
        return $ticket;
    }

    public function createTicket($data, $userId)
    {
        // RBAC: Ensure creating for self unless admin
        if (!$this->auth->isAdmin() && $this->auth->id() != $userId) {
            return false;
        }

        $ticketData = [
            'ticket_number' => 'TKT-' . time() . '-' . rand(1000, 9999),
            'user_id' => $userId,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'open',
            'attachment' => $data['attachment'] ?? null
        ];

        return $this->ticketModel->create($ticketData);
    }

    public function updateTicketStatus($id, $status)
    {
        // RBAC: Only admin can update status (usually)
        // Or user can close their own ticket?
        if (!$this->auth->isAdmin()) {
            // Check if user is closing their own ticket
            $ticket = $this->getTicketById($id);
            if (!$ticket) return false;
            
            // Allow user to close/resolve, but maybe not other statuses?
            // For simplicity, let's restrict status updates to Admin only for now, 
            // except maybe 'closed' by user.
            if ($status !== 'closed' && $status !== 'resolved') {
                 return false;
            }
        }

        return $this->ticketModel->update($id, ['status' => $status]);
    }

    public function addReply($ticketId, $userId, $message, $attachment = null)
    {
        // RBAC check
        $ticket = $this->getTicketById($ticketId); // This already checks read permission
        if (!$ticket) {
            return false;
        }

        // Ensure replier is authorized
        if (!$this->auth->isAdmin() && $this->auth->id() != $userId) {
            return false;
        }

        $replyData = [
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'message' => $message,
            'attachment' => $attachment
        ];

        $replyId = $this->replyModel->create($replyData);
        
        // Update ticket updated_at timestamp
        $this->ticketModel->update($ticketId, []); 

        return $replyId;
    }
}
