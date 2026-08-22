<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Models;

use PDO;

class SupportTicket extends Model
{
    protected static $table = 'support_tickets';
    protected static $tenantScoped = true;

    protected $fillable = [
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
        [$tSql, $tParams] = static::tenantClause();
        $sql = "SELECT * FROM " . static::$table . " WHERE ticket_number = :ticket_number{$tSql}";
        $params = ['ticket_number' => $ticketNumber] + array_combine(range(1, count($tParams)), $tParams);
        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTicketsByUser($userId)
    {
        [$tSql, $tParams] = static::tenantClause();
        $sql = "SELECT * FROM " . static::$table . " WHERE user_id = :user_id{$tSql} ORDER BY created_at DESC";
        $params = ['user_id' => $userId] + array_combine(range(1, count($tParams)), $tParams);
        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTicketsWithUser()
    {
        [$tSql, $tParams] = static::tenantClause();
        $sql = "SELECT t.*, u.name as user_name, u.email as user_email 
                FROM " . static::$table . " t 
                JOIN users u ON t.user_id = u.id 
                WHERE 1=1{$tSql}
                ORDER BY t.created_at DESC";
        $stmt = static::getDb()->prepare($sql);
        $stmt->execute($tParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
