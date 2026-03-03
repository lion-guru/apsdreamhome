<?php

namespace App\Models;

use PDO;

class SupportTicket extends Model
{
    protected static $table = 'support_tickets';

    protected array $fillable = [
        'ticket_number',
        'user_id',
        'subject',
        'message',
        'priority',
        'status',
        'attachment'
    ];

    public function getTicketByNumber($ticketNumber)
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE ticket_number = :ticket_number";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ticket_number' => $ticketNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTicketsByUser($userId)
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTicketsWithUser()
    {
        $sql = "SELECT t.*, u.name as user_name, u.email as user_email 
                FROM " . static::$table . " t 
                JOIN users u ON t.user_id = u.id 
                ORDER BY t.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
