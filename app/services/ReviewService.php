<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;

/**
 * Review & Testimonial Service
 * Customer reviews with moderation workflow
 */
class ReviewService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;

    public function __construct($db = null)
    {
        if ($db === null) {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
        } elseif (is_object($db) && method_exists($db, 'getPdo')) {
            $db = $db->getPdo();
        }
        $this->db = $db;
        $this->pdo = $db;
    }

    public function createReview(array $data): int
    {
        $tid = $this->tenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';
        $stmt = $this->pdo->prepare("INSERT INTO property_reviews
            (customer_id, property_id, rating, review_text, anonymous, status{$extraCol})
            VALUES (?, ?, ?, ?, ?, 'pending'{$extraVal})");
        $params = [
            $data['customer_id'] ?? null,
            $data['property_id'],
            $data['rating'],
            $data['review_text'] ?? null,
            !empty($data['anonymous']) ? 1 : 0
        ];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function getReviewsByProperty(int $propertyId, int $limit = 20, string $status = 'approved'): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT r.*, u.name as customer_name, u.email as customer_email
                FROM property_reviews r
                LEFT JOIN users u ON u.id = r.customer_id
                WHERE r.property_id = ?" . ($tid > 1 ? " AND r.tenant_id = ?" : "");
        $params = [$propertyId];
        if ($tid > 1) $params[] = $tid;
        if ($status) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY r.created_at DESC LIMIT ?";
        $params[] = $limit;
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getAllReviews(string $status = '', int $limit = 50): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT r.*, u.name as customer_name, u.email as customer_email,
                COALESCE(p.name, CONCAT('Property #', r.property_id)) as property_title
                FROM property_reviews r
                LEFT JOIN users u ON u.id = r.customer_id
                LEFT JOIN user_properties p ON p.id = r.property_id";
        $params = [];
        $conditions = [];
        if ($status) {
            $conditions[] = "r.status = ?";
            $params[] = $status;
        }
        if ($tid > 1) {
            $conditions[] = "r.tenant_id = ?";
            $params[] = $tid;
        }
        if ($conditions) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        $sql .= " ORDER BY r.created_at DESC LIMIT ?";
        $params[] = $limit;
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getReviewById(int $id): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT r.*, u.name as customer_name FROM property_reviews r LEFT JOIN users u ON u.id = r.customer_id WHERE r.id = ?" . ($tid > 1 ? " AND r.tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function approve(int $id): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE property_reviews SET status = 'approved' WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function reject(int $id, string $reason = null): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE property_reviews SET status = 'rejected' WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function addAdminResponse(int $id, string $response): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE property_reviews SET admin_response = ?, admin_response_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$response, $id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $tid = $this->tenantId();
        $sql = "DELETE FROM property_reviews WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function markHelpful(int $id, ?int $userId, string $ip): bool
    {
        $tid = $this->tenantId();
        try {
            $check = $this->pdo->prepare("SELECT id FROM review_helpful_votes WHERE review_id = ? AND (user_id = ? OR ip_address = ?)" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            $checkParams = [$id, $userId, $ip];
            if ($tid > 1) $checkParams[] = $tid;
            $check->execute($checkParams);
            if ($check->fetch()) return false;
            $insertParams = [$id, $userId, $ip];
            if ($tid > 1) $insertParams[] = $tid;
            $this->pdo->prepare("INSERT INTO review_helpful_votes (review_id, user_id, ip_address" . ($tid > 1 ? ", tenant_id" : "") . ") VALUES (?, ?, ?" . ($tid > 1 ? ", ?" : "") . ")")
                ->execute($insertParams);
            $this->pdo->prepare("UPDATE property_reviews SET helpful_count = helpful_count + 1 WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))
                ->execute($tid > 1 ? [$id, $tid] : [$id]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function report(int $id, ?int $userId, string $reason, ?string $description): int
    {
        $tid = $this->tenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';
        $stmt = $this->pdo->prepare("INSERT INTO review_reports (review_id, user_id, reason, description{$extraCol}) VALUES (?, ?, ?, ?{$extraVal})");
        $params = [$id, $userId, $reason, $description];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function getPropertyRating(int $propertyId): array
    {
        $tid = $this->tenantId();
        try {
            $sql = "SELECT
                COUNT(*) as total,
                AVG(rating) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM property_reviews WHERE property_id = ? AND status = 'approved'" . ($tid > 1 ? " AND tenant_id = ?" : "");
            $params = [$propertyId];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();
            return [
                'total' => (int)($row['total'] ?? 0),
                'avg' => round((float)($row['avg_rating'] ?? 0), 1),
                '5' => (int)($row['five_star'] ?? 0),
                '4' => (int)($row['four_star'] ?? 0),
                '3' => (int)($row['three_star'] ?? 0),
                '2' => (int)($row['two_star'] ?? 0),
                '1' => (int)($row['one_star'] ?? 0)
            ];
        } catch (\Throwable $e) {
            return ['total' => 0, 'avg' => 0, '5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0];
        }
    }

    public function getTestimonials(int $limit = 20, bool $featuredOnly = false): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM testimonials WHERE status = 'approved'" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($featuredOnly) {
            $sql .= " AND is_featured = 1";
        }
        $sql .= " ORDER BY is_featured DESC, created_at DESC LIMIT ?";
        $params[] = $limit;
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getAllTestimonials(string $status = '', int $limit = 50): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM testimonials";
        $params = [];
        $conditions = [];
        if ($status) {
            $conditions[] = "status = ?";
            $params[] = $status;
        }
        if ($tid > 1) {
            $conditions[] = "tenant_id = ?";
            $params[] = $tid;
        }
        if ($conditions) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function toggleFeaturedTestimonial(int $id): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE testimonials SET is_featured = NOT is_featured WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function approveTestimonial(int $id): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE testimonials SET status = 'approved', approved_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function rejectTestimonial(int $id): bool
    {
        $tid = $this->tenantId();
        $sql = "UPDATE testimonials SET status = 'rejected' WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function deleteTestimonial(int $id): bool
    {
        $tid = $this->tenantId();
        $sql = "DELETE FROM testimonials WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function getStats(): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " WHERE tenant_id = ?" : "";
        $stats = [
            'total_reviews' => 0, 'pending_reviews' => 0, 'approved_reviews' => 0, 'rejected_reviews' => 0,
            'avg_rating' => 0, '5_star' => 0, 'total_testimonials' => 0, 'featured_testimonials' => 0,
            'pending_testimonials' => 0
        ];
        try {
            $stats['total_reviews'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_reviews{$tenantWhere}", $tid > 1 ? [$tid] : [])->fetchColumn();
            foreach (['pending', 'approved', 'rejected'] as $s) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM property_reviews WHERE status = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
                $stmt->execute($tid > 1 ? [$s, $tid] : [$s]);
                $stats[$s . '_reviews'] = (int)$stmt->fetchColumn();
            }
            $stats['avg_rating'] = round((float)$this->pdo->query("SELECT AVG(rating) FROM property_reviews WHERE status = 'approved'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : [])->fetchColumn(), 2);
            $stats['5_star'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_reviews WHERE rating = 5 AND status = 'approved'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : [])->fetchColumn();
            $stats['total_testimonials'] = (int)$this->pdo->query("SELECT COUNT(*) FROM testimonials{$tenantWhere}", $tid > 1 ? [$tid] : [])->fetchColumn();
            $stats['featured_testimonials'] = (int)$this->pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_featured = 1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : [])->fetchColumn();
            $stats['pending_testimonials'] = (int)$this->pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : [])->fetchColumn();
        } catch (\Throwable $e) {
        // ignore
        error_log($e->getMessage());
        }
        return $stats;
    }
}
