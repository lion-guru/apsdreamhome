<?php

namespace App\Services;

/**
 * Review & Testimonial Service
 * Customer reviews with moderation workflow
 */
class ReviewService
{
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
        $stmt = $this->pdo->prepare("INSERT INTO property_reviews
            (customer_id, property_id, rating, review_text, anonymous, status)
            VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $data['customer_id'] ?? null,
            $data['property_id'],
            $data['rating'],
            $data['review_text'] ?? null,
            !empty($data['anonymous']) ? 1 : 0
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getReviewsByProperty(int $propertyId, int $limit = 20, string $status = 'approved'): array
    {
        $sql = "SELECT r.*, u.name as customer_name, u.email as customer_email
                FROM property_reviews r
                LEFT JOIN users u ON u.id = r.customer_id
                WHERE r.property_id = ?";
        $params = [$propertyId];
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
        $sql = "SELECT r.*, u.name as customer_name, u.email as customer_email,
                COALESCE(p.title, CONCAT('Property #', r.property_id)) as property_title
                FROM property_reviews r
                LEFT JOIN users u ON u.id = r.customer_id
                LEFT JOIN user_properties p ON p.id = r.property_id";
        $params = [];
        if ($status) {
            $sql .= " WHERE r.status = ?";
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

    public function getReviewById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT r.*, u.name as customer_name FROM property_reviews r LEFT JOIN users u ON u.id = r.customer_id WHERE r.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function approve(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE property_reviews SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function reject(int $id, string $reason = null): bool
    {
        $stmt = $this->pdo->prepare("UPDATE property_reviews SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function addAdminResponse(int $id, string $response): bool
    {
        $stmt = $this->pdo->prepare("UPDATE property_reviews SET admin_response = ?, admin_response_at = NOW() WHERE id = ?");
        $stmt->execute([$response, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM property_reviews WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function markHelpful(int $id, ?int $userId, string $ip): bool
    {
        try {
            $check = $this->pdo->prepare("SELECT id FROM review_helpful_votes WHERE review_id = ? AND (user_id = ? OR ip_address = ?)");
            $check->execute([$id, $userId, $ip]);
            if ($check->fetch()) return false;
            $this->pdo->prepare("INSERT INTO review_helpful_votes (review_id, user_id, ip_address) VALUES (?, ?, ?)")
                ->execute([$id, $userId, $ip]);
            $this->pdo->prepare("UPDATE property_reviews SET helpful_count = helpful_count + 1 WHERE id = ?")
                ->execute([$id]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function report(int $id, ?int $userId, string $reason, ?string $description): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO review_reports (review_id, user_id, reason, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $userId, $reason, $description]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getPropertyRating(int $propertyId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT
                COUNT(*) as total,
                AVG(rating) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM property_reviews WHERE property_id = ? AND status = 'approved'");
            $stmt->execute([$propertyId]);
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
        $sql = "SELECT * FROM testimonials WHERE status = 'approved'";
        $params = [];
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
        $sql = "SELECT * FROM testimonials";
        $params = [];
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
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
        $stmt = $this->pdo->prepare("UPDATE testimonials SET is_featured = NOT is_featured WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function approveTestimonial(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE testimonials SET status = 'approved', approved_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function rejectTestimonial(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE testimonials SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function deleteTestimonial(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function getStats(): array
    {
        $stats = [
            'total_reviews' => 0, 'pending_reviews' => 0, 'approved_reviews' => 0, 'rejected_reviews' => 0,
            'avg_rating' => 0, '5_star' => 0, 'total_testimonials' => 0, 'featured_testimonials' => 0,
            'pending_testimonials' => 0
        ];
        try {
            $stats['total_reviews'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_reviews")->fetchColumn();
            foreach (['pending', 'approved', 'rejected'] as $s) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM property_reviews WHERE status = ?");
                $stmt->execute([$s]);
                $stats[$s . '_reviews'] = (int)$stmt->fetchColumn();
            }
            $stats['avg_rating'] = round((float)$this->pdo->query("SELECT AVG(rating) FROM property_reviews WHERE status = 'approved'")->fetchColumn(), 2);
            $stats['5_star'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_reviews WHERE rating = 5 AND status = 'approved'")->fetchColumn();
            $stats['total_testimonials'] = (int)$this->pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
            $stats['featured_testimonials'] = (int)$this->pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_featured = 1")->fetchColumn();
            $stats['pending_testimonials'] = (int)$this->pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'")->fetchColumn();
        } catch (\Throwable $e) {
        // ignore
        error_log($e->getMessage());
        }
        return $stats;
    }
}
