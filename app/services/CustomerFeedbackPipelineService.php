<?php
namespace App\Services;

use App\Traits\ServiceTenantTrait;

class CustomerFeedbackPipelineService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    public function getAll(array $filters = []): array
    {
        $sql = "SELECT af.* FROM app_feedback af WHERE af.source_type IS NOT NULL";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND af.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['source_type'])) {
            $sql .= " AND af.source_type = ?";
            $params[] = $filters['source_type'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (af.user_name LIKE ? OR af.user_email LIKE ? OR af.title LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        $sql .= " ORDER BY af.created_at DESC, af.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM app_feedback WHERE id = ? AND source_type IS NOT NULL");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getByToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM app_feedback WHERE survey_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function dispatchSurvey(array $data): int
    {
        $token = bin2hex(random_bytes(16));
        $stmt = $this->pdo->prepare("INSERT INTO app_feedback (tenant_id, user_id, user_name, user_email, feedback_type, platform, rating, title, description, status, admin_response, source_type, source_id, survey_token, channel, sent_at, created_at, updated_at) VALUES (?, ?, ?, ?, 'survey', ?, NULL, ?, ?, 'sent', NULL, ?, ?, ?, ?, NOW(), NOW(), NOW())");
        $stmt->execute([
            $data['tenant_id'] ?? 1,
            $data['user_id'] ?: null,
            $data['user_name'] ?? null,
            $data['user_email'] ?? null,
            $data['platform'] ?? 'web',
            $data['title'] ?? 'Customer Feedback Survey',
            $data['description'] ?? null,
            $data['source_type'] ?? 'manual',
            $data['source_id'] ?? null,
            $token,
            $data['channel'] ?? 'email',
        ]);
        $id = (int)$this->pdo->lastInsertId();
        $this->sendSurvey($data, $token);
        return $id;
    }

    public function recordResponse(string $token, int $rating, string $comments): bool
    {
        $item = $this->getByToken($token);
        if (!$item) {
            return false;
        }
        $stmt = $this->pdo->prepare("UPDATE app_feedback SET rating = ?, description = ?, status = 'responded', responded_at = NOW(), updated_at = NOW() WHERE survey_token = ?");
        return $stmt->execute([$rating, $comments, $token]);
    }

    public function updateStatus(int $id, string $status, string $response = ''): bool
    {
        $stmt = $this->pdo->prepare("UPDATE app_feedback SET status = ?, admin_response = ?, responded_by = ?, responded_at = NOW(), updated_at = NOW() WHERE id = ? AND source_type IS NOT NULL");
        return $stmt->execute([$status, $response, (int)($_SESSION['admin_id'] ?? 0), $id]);
    }

    public function getStats(): array
    {
        $row = $this->pdo->query("SELECT COUNT(*) AS total, SUM(status='sent') AS sent, SUM(status='responded') AS responded, SUM(status='closed') AS closed, AVG(rating) AS avg_rating FROM app_feedback WHERE source_type IS NOT NULL")->fetch(\PDO::FETCH_ASSOC);
        return [
            'total' => (int)($row['total'] ?? 0),
            'sent' => (int)($row['sent'] ?? 0),
            'responded' => (int)($row['responded'] ?? 0),
            'closed' => (int)($row['closed'] ?? 0),
            'avg_rating' => round((float)($row['avg_rating'] ?? 0), 1),
        ];
    }

    private function sendSurvey(array $data, string $token): void
    {
        $gateway = new \App\Services\Gateway\CommunicationGateway();
        $responseUrl = (defined('BASE_URL') ? BASE_URL : '') . '/feedback/respond/' . $token;
        $body = "Please share your feedback: " . $responseUrl;
        $user = [
            'email' => $data['user_email'] ?? null,
            'phone' => $data['user_phone'] ?? null,
        ];
        try {
            $gateway->sendMulti($user, [$data['channel'] ?? 'email'], 'Customer Feedback Survey', $body, []);
        } catch (\Throwable $e) {
        }
    }
}
