<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TicketReply extends Model
{
    protected static $table = 'ticket_replies';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachment'
    ];

    public function getRepliesByTicketId($ticketId)
    {
        $sql = "SELECT r.*, u.name as user_name, u.role as user_role 
                FROM " . static::$table . " r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.ticket_id = :ticket_id 
                ORDER BY r.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
