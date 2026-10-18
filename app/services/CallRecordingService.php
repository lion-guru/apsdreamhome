<?php
namespace App\Services;

use App\Traits\ServiceTenantTrait;

class CallRecordingService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    public function getAll(array $filters = []): array
    {
        $sql = "SELECT s.id, s.lead_id, s.agent_id, s.agent_name, s.phone, s.call_type, s.status, s.duration_seconds, s.recording_url, s.recording_duration, s.customer_response, s.interest_level, s.created_at, l.name AS lead_name, l.phone AS lead_phone FROM ai_call_sessions s LEFT JOIN leads l ON l.id = s.lead_id WHERE s.recording_url IS NOT NULL AND s.recording_url <> ''";
        $params = [];
        if (!empty($filters['lead_id'])) {
            $sql .= " AND s.lead_id = ?";
            $params[] = (int)$filters['lead_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (l.name LIKE ? OR s.phone LIKE ? OR s.agent_name LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        $sql .= " ORDER BY s.created_at DESC, s.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT s.*, l.name AS lead_name, l.phone AS lead_phone, l.email AS lead_email FROM ai_call_sessions s LEFT JOIN leads l ON l.id = s.lead_id WHERE s.id = ? AND s.recording_url IS NOT NULL AND s.recording_url <> ''");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getByLead(int $leadId): array
    {
        $stmt = $this->pdo->prepare("SELECT s.id, s.lead_id, s.agent_name, s.phone, s.call_type, s.status, s.duration_seconds, s.recording_url, s.recording_duration, s.customer_response, s.interest_level, s.created_at FROM ai_call_sessions s WHERE s.lead_id = ? AND s.recording_url IS NOT NULL AND s.recording_url <> '' ORDER BY s.created_at DESC");
        $stmt->execute([$leadId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getStats(): array
    {
        $row = $this->pdo->query("SELECT COUNT(*) AS total, SUM(status='completed') AS completed, COALESCE(SUM(duration_seconds),0) AS total_seconds, COUNT(DISTINCT lead_id) AS leads FROM ai_call_sessions WHERE recording_url IS NOT NULL AND recording_url <> ''")->fetch(\PDO::FETCH_ASSOC);
        return [
            'total' => (int)($row['total'] ?? 0),
            'completed' => (int)($row['completed'] ?? 0),
            'total_seconds' => (int)($row['total_seconds'] ?? 0),
            'leads' => (int)($row['leads'] ?? 0),
        ];
    }
}
