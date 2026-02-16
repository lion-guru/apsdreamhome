<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\TicketReply;

class SupportTicketService
{
    protected $ticketModel;
    protected $replyModel;

    public function __construct()
    {
        $this->ticketModel = new SupportTicket();
        $this->replyModel = new TicketReply();
    }

    public function getAllTickets()
    {
        return $this->ticketModel->getAllTicketsWithUser();
    }

    public function getTicketsByUser($userId)
    {
        return $this->ticketModel->getTicketsByUser($userId);
    }

    public function getTicketById($id)
    {
        $ticket = $this->ticketModel->find($id);
        if ($ticket) {
            $ticket['replies'] = $this->replyModel->getRepliesByTicketId($id);
        }
        return $ticket;
    }

    public function createTicket($data, $userId)
    {
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
        return $this->ticketModel->update($id, ['status' => $status]);
    }

    public function addReply($ticketId, $userId, $message, $attachment = null)
    {
        $replyData = [
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'message' => $message,
            'attachment' => $attachment
        ];

        $replyId = $this->replyModel->create($replyData);
        
        // Update ticket updated_at timestamp
        // The model update method should handle updated_at automatically if configured, 
        // but explicit update ensures it bumps up
        $this->ticketModel->update($ticketId, []); 

        return $replyId;
    }
}
