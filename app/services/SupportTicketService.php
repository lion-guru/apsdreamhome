<?php

namespace App\Services;

use PDO;

class SupportTicketService
{
    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
    }

    public function createTicket(int $userId, string $subject, string $message, string $category = 'general', string $priority = 'medium', ?int $bookingId = null): array
    {
        $ticketNumber = 'APS-TKT-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $stmt = $this->db->prepare("
            INSERT INTO support_tickets (ticket_number, user_id, subject, message, category, priority, booking_id, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'open', NOW())
        ");
        $stmt->execute([$ticketNumber, $userId, $subject, $message, $category, $priority, $bookingId]);
        $ticketId = (int) $this->db->lastInsertId();

        $stmt2 = $this->db->prepare("
            INSERT INTO support_ticket_replies (ticket_id, user_id, message, is_admin, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        $stmt2->execute([$ticketId, $userId, $message]);

        $this->db->prepare("UPDATE support_tickets SET reply_count = 1, last_reply_by = ?, last_reply_at = NOW() WHERE id = ?")
            ->execute([$userId, $ticketId]);

        return $this->getTicket($ticketId);
    }

    public function getTickets(?int $userId = null, ?string $status = null, ?string $category = null, ?string $priority = null, ?string $search = null, int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];

        if ($userId !== null) {
            $where[] = 't.user_id = ?';
            $params[] = $userId;
        }
        if ($status !== null && $status !== '') {
            $where[] = 't.status = ?';
            $params[] = $status;
        }
        if ($category !== null && $category !== '') {
            $where[] = 't.category = ?';
            $params[] = $category;
        }
        if ($priority !== null && $priority !== '') {
            $where[] = 't.priority = ?';
            $params[] = $priority;
        }
        if ($search !== null && $search !== '') {
            $where[] = '(t.subject LIKE ? OR t.ticket_number LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM support_tickets t WHERE $whereSql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT t.*, u.name as customer_name, u.email as customer_email,
                       a.name as assigned_name
                FROM support_tickets t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN users a ON t.assigned_to = a.id
                WHERE $whereSql
                ORDER BY t.created_at DESC
                LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll();

        return [
            'tickets' => $tickets,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function getTicket(int $ticketId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                   a.name as assigned_name, a.email as assigned_email
            FROM support_tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN users a ON t.assigned_to = a.id
            WHERE t.id = ?
        ");
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch();
        if (!$ticket) {
            return null;
        }

        $replyStmt = $this->db->prepare("
            SELECT r.*, u.name as user_name, u.role as user_role
            FROM support_ticket_replies r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.ticket_id = ?
            ORDER BY r.created_at ASC
        ");
        $replyStmt->execute([$ticketId]);
        $ticket['replies'] = $replyStmt->fetchAll();

        return $ticket;
    }

    public function addReply(int $ticketId, int $userId, string $message, bool $isStaff = false): array
    {
        $isStaffFlag = $isStaff ? 1 : 0;

        $stmt = $this->db->prepare("
            INSERT INTO support_ticket_replies (ticket_id, user_id, message, is_admin, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$ticketId, $userId, $message, $isStaffFlag]);
        $replyId = (int) $this->db->lastInsertId();

        $newStatus = $isStaff ? 'waiting_customer' : 'in_progress';
        $this->db->prepare("
            UPDATE support_tickets
            SET reply_count = reply_count + 1, last_reply_by = ?, last_reply_at = NOW(),
                status = IF(status IN ('resolved','closed'), status, ?)
            WHERE id = ?
        ")->execute([$userId, $newStatus, $ticketId]);

        $stmt2 = $this->db->prepare("SELECT * FROM support_ticket_replies WHERE id = ?");
        $stmt2->execute([$replyId]);
        return $stmt2->fetch();
    }

    public function updateStatus(int $ticketId, string $status): bool
    {
        $extra = '';
        if ($status === 'resolved') {
            $extra = ', resolved_at = NOW()';
        }
        $stmt = $this->db->prepare("UPDATE support_tickets SET status = ?$extra WHERE id = ?");
        return $stmt->execute([$status, $ticketId]);
    }

    public function assignTicket(int $ticketId, int $staffId): bool
    {
        $stmt = $this->db->prepare("UPDATE support_tickets SET assigned_to = ? WHERE id = ?");
        return $stmt->execute([$staffId, $ticketId]);
    }

    public function updatePriority(int $ticketId, string $priority): bool
    {
        $stmt = $this->db->prepare("UPDATE support_tickets SET priority = ? WHERE id = ?");
        return $stmt->execute([$priority, $ticketId]);
    }

    public function getStats(): array
    {
        $stats = [];

        $row = $this->db->query("SELECT COUNT(*) as total, SUM(status='open') as open_count, SUM(status='in_progress') as in_progress, SUM(status='waiting_customer') as waiting, SUM(status='resolved') as resolved, SUM(status='closed') as closed FROM support_tickets")->fetch();
        $stats['total'] = (int)($row['total'] ?? 0);
        $stats['open'] = (int)($row['open_count'] ?? 0);
        $stats['in_progress'] = (int)($row['in_progress'] ?? 0);
        $stats['waiting_customer'] = (int)($row['waiting'] ?? 0);
        $stats['resolved'] = (int)($row['resolved'] ?? 0);
        $stats['closed'] = (int)($row['closed'] ?? 0);

        $catRows = $this->db->query("SELECT category, COUNT(*) as cnt FROM support_tickets GROUP BY category")->fetchAll();
        $stats['by_category'] = [];
        foreach ($catRows as $r) {
            $stats['by_category'][$r['category']] = (int) $r['cnt'];
        }

        $priRows = $this->db->query("SELECT priority, COUNT(*) as cnt FROM support_tickets GROUP BY priority")->fetchAll();
        $stats['by_priority'] = [];
        foreach ($priRows as $r) {
            $stats['by_priority'][$r['priority']] = (int) $r['cnt'];
        }

        $avgRow = $this->db->query("
            SELECT AVG(TIMESTAMPDIFF(HOUR, t.created_at, t.last_reply_at)) as avg_hours
            FROM support_tickets t
            WHERE t.last_reply_at IS NOT NULL AND t.reply_count > 1
        ")->fetch();
        $stats['avg_response_hours'] = round((float)($avgRow['avg_hours'] ?? 0), 1);

        $stats['resolution_rate'] = $stats['total'] > 0
            ? round((($stats['resolved'] + $stats['closed']) / $stats['total']) * 100, 1)
            : 0;

        return $stats;
    }

    public function getTicketCount(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function getUserTicketStats(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(status='open') as open_count,
                SUM(status='in_progress') as in_progress,
                SUM(status IN ('resolved','closed')) as resolved
            FROM support_tickets WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return [
            'total' => (int)($row['total'] ?? 0),
            'open' => (int)($row['open_count'] ?? 0),
            'in_progress' => (int)($row['in_progress'] ?? 0),
            'resolved' => (int)($row['resolved'] ?? 0),
        ];
    }
}
