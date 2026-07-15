<?php

namespace App\Services;

use App\Core\Database;

class InteractionService
{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getInteractions($filters = []) {
        try {
            $where = ["i.deleted_at IS NULL"];
            $params = [];

            if (!empty($filters['lead_id'])) {
                $where[] = "i.lead_id = ?";
                $params[] = $filters['lead_id'];
            }
            if (!empty($filters['type'])) {
                $where[] = "i.type = ?";
                $params[] = $filters['type'];
            }
            if (!empty($filters['direction'])) {
                $where[] = "i.direction = ?";
                $params[] = $filters['direction'];
            }
            if (!empty($filters['user_id'])) {
                $where[] = "i.user_id = ?";
                $params[] = $filters['user_id'];
            }
            if (!empty($filters['date_from'])) {
                $where[] = "i.created_at >= ?";
                $params[] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = "i.created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $page = max(1, $filters['page'] ?? 1);
            $perPage = min(100, max(10, $filters['per_page'] ?? 25));
            $offset = ($page - 1) * $perPage;

            $countStmt = $this->db->query("SELECT COUNT(*) as total FROM crm_interactions i $whereClause", $params);
            $total = (int)($countStmt->fetch()['total'] ?? 0);

            $sql = "SELECT i.*, u.name as user_name, l.name as lead_name
                    FROM crm_interactions i
                    LEFT JOIN users u ON u.id = i.user_id
                    LEFT JOIN leads l ON l.id = i.lead_id
                    $whereClause
                    ORDER BY i.created_at DESC
                    LIMIT $offset, $perPage";

            $stmt = $this->db->query($sql, $params);
            $interactions = $stmt->fetchAll() ?: [];

            return [
                'interactions' => $interactions,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int)ceil($total / $perPage),
            ];
        } catch (\Exception $e) {
            error_log('InteractionService::getInteractions error: ' . $e->getMessage());
            return ['interactions' => [], 'total' => 0, 'page' => 1, 'per_page' => 25, 'total_pages' => 0];
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->query(
                "SELECT i.*, u.name as user_name, l.name as lead_name
                 FROM crm_interactions i
                 LEFT JOIN users u ON u.id = i.user_id
                 LEFT JOIN leads l ON l.id = i.lead_id
                 WHERE i.id = ?", [$id]
            );
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function create($data) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO crm_interactions (lead_id, type, direction, subject, content, outcome, user_id, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $data['lead_id'],
                $data['type'] ?? 'note',
                $data['direction'] ?? 'outbound',
                $data['subject'] ?? '',
                $data['content'] ?? '',
                $data['outcome'] ?? '',
                $data['user_id'] ?? null,
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log('InteractionService::create error: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $allowed = ['type', 'direction', 'subject', 'content', 'outcome', 'user_id'];
            $fields = [];
            $params = [];
            foreach ($allowed as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            if (empty($fields)) return false;
            $params[] = $id;
            $stmt = $this->db->prepare("UPDATE crm_interactions SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?");
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("UPDATE crm_interactions SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getLeadInteractions($leadId, $limit = 50) {
        try {
            $stmt = $this->db->query(
                "SELECT i.*, u.name as user_name
                 FROM crm_interactions i
                 LEFT JOIN users u ON u.id = i.user_id
                 WHERE i.lead_id = ? AND i.deleted_at IS NULL
                 ORDER BY i.created_at DESC LIMIT ?", [$leadId, $limit]
            );
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getInteractionTypes() {
        return [
            'call' => 'Phone Call',
            'email' => 'Email',
            'meeting' => 'Meeting',
            'whatsapp' => 'WhatsApp',
            'sms' => 'SMS',
            'note' => 'Internal Note',
            'site_visit' => 'Site Visit',
            'demo' => 'Demo/Presentation',
            'other' => 'Other',
        ];
    }

    public function getDirections() {
        return [
            'inbound' => 'Inbound',
            'outbound' => 'Outbound',
        ];
    }

    public function getStats($leadId = null) {
        try {
            $where = $leadId ? "WHERE lead_id = ?" : "";
            $params = $leadId ? [$leadId] : [];

            $stmt = $this->db->query(
                "SELECT type, COUNT(*) as count FROM crm_interactions $where GROUP BY type", $params
            );
            $byType = [];
            foreach ($stmt->fetchAll() as $row) {
                $byType[$row['type']] = (int)$row['count'];
            }

            $stmt = $this->db->query(
                "SELECT direction, COUNT(*) as count FROM crm_interactions $where GROUP BY direction", $params
            );
            $byDirection = [];
            foreach ($stmt->fetchAll() as $row) {
                $byDirection[$row['direction']] = (int)$row['count'];
            }

            $stmt = $this->db->query(
                "SELECT DATE(created_at) as date, COUNT(*) as count FROM crm_interactions $where GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30", $params
            );
            $byDate = $stmt->fetchAll() ?: [];

            return [
                'by_type' => $byType,
                'by_direction' => $byDirection,
                'by_date' => $byDate,
            ];
        } catch (\Exception $e) {
            return ['by_type' => [], 'by_direction' => [], 'by_date' => []];
        }
    }
}