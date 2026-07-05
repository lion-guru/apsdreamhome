<?php
namespace App\Services;

use App\Core\Database\Database;
use PDO;

class DirectoryService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Categories ──

    public function getActiveCategories(): array
    {
        $stmt = $this->db->query("SELECT * FROM directory_categories WHERE is_active = 1 ORDER BY sort_order ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM directory_categories WHERE slug = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getCategory(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM directory_categories WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAllCategories(): array
    {
        $stmt = $this->db->query("SELECT dc.*, (SELECT COUNT(*) FROM directory_listings WHERE category_id = dc.id AND status = 'approved') as listing_count FROM directory_categories dc ORDER BY dc.sort_order ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function upsertCategory(array $data): bool
    {
        try {
            if (!empty($data['id'])) {
                $stmt = $this->db->prepare("UPDATE directory_categories SET name=?, slug=?, description=?, icon=?, parent_id=?, sort_order=?, is_active=? WHERE id=?");
                return $stmt->execute([$data['name'], $data['slug'], $data['description'] ?? '', $data['icon'] ?? 'fas fa-building', $data['parent_id'] ?: null, (int)($data['sort_order'] ?? 0), (int)($data['is_active'] ?? 1), $data['id']]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO directory_categories (name, slug, description, icon, parent_id, sort_order, is_active) VALUES (?,?,?,?,?,?,?)");
                return $stmt->execute([$data['name'], $data['slug'], $data['description'] ?? '', $data['icon'] ?? 'fas fa-building', $data['parent_id'] ?: null, (int)($data['sort_order'] ?? 0), (int)($data['is_active'] ?? 1)]);
            }
        } catch (\Exception $e) { error_log('DirectoryService::upsertCategory: ' . $e->getMessage()); return false; }
    }

    public function deleteCategory(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE directory_listings SET category_id = NULL WHERE category_id = ?");
            $stmt->execute([$id]);
            $stmt2 = $this->db->prepare("DELETE FROM directory_categories WHERE id = ?");
            return $stmt2->execute([$id]);
        } catch (\Exception $e) { return false; }
    }

    // ── Listings ──

    public function getListings(int $categoryId = null, string $search = '', string $city = '', string $sort = 'latest', int $page = 1, int $perPage = 20): array
    {
        $where = ["l.status = 'approved'"];
        $params = [];

        if ($categoryId) {
            $where[] = 'l.category_id = ?';
            $params[] = $categoryId;
        }
        if ($search) {
            $where[] = '(l.business_name LIKE ? OR l.description LIKE ? OR l.owner_name LIKE ?)';
            $s = '%' . $search . '%';
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        if ($city) {
            $where[] = 'l.city LIKE ?';
            $params[] = '%' . $city . '%';
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $order = match($sort) {
            'rating' => 'l.rating DESC, l.review_count DESC',
            'views' => 'l.views DESC',
            'name' => 'l.business_name ASC',
            default => 'l.is_featured DESC, l.created_at DESC',
        };

        $countSql = "SELECT COUNT(*) FROM directory_listings l WHERE $whereClause";
        $cStmt = $this->db->prepare($countSql);
        $cStmt->execute($params);
        $total = (int)$cStmt->fetchColumn();

        $sql = "SELECT l.*, dc.name as category_name, dc.slug as category_slug, dc.icon as category_icon
                FROM directory_listings l
                LEFT JOIN directory_categories dc ON l.category_id = dc.id
                WHERE $whereClause
                ORDER BY $order
                LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['items' => $items, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => ceil($total / $perPage)];
    }

    public function getFeaturedListings(int $limit = 6): array
    {
        $stmt = $this->db->prepare("SELECT l.*, dc.name as category_name, dc.slug as category_slug, dc.icon as category_icon
            FROM directory_listings l LEFT JOIN directory_categories dc ON l.category_id = dc.id
            WHERE l.status = 'approved' AND l.is_featured = 1 ORDER BY l.rating DESC, l.views DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListing(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT l.*, dc.name as category_name, dc.slug as category_slug
            FROM directory_listings l LEFT JOIN directory_categories dc ON l.category_id = dc.id WHERE l.id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->incrementListingViews($id);
        }
        return $row ?: null;
    }

    public function getListingReviews(int $listingId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM directory_reviews WHERE listing_id = ? AND status = 'approved' ORDER BY created_at DESC");
        $stmt->execute([$listingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllListings(string $status = '', int $categoryId = 0): array
    {
        $where = [];
        $params = [];
        if ($status) {
            $where[] = 'l.status = ?'; $params[] = $status;
        }
        if ($categoryId) {
            $where[] = 'l.category_id = ?'; $params[] = $categoryId;
        }
        $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->db->prepare("SELECT l.*, dc.name as category_name FROM directory_listings l LEFT JOIN directory_categories dc ON l.category_id = dc.id $w ORDER BY l.created_at DESC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function upsertListing(array $data): int
    {
        try {
            if (!empty($data['id'])) {
                $stmt = $this->db->prepare("UPDATE directory_listings SET category_id=?, business_name=?, owner_name=?, description=?, phone=?, whatsapp=?, email=?, website=?, address=?, city=?, state=?, pincode=?, latitude=?, longitude=?, experience_years=?, price_range=?, photo=?, is_verified=?, is_featured=?, status=? WHERE id=?");
                $stmt->execute([$data['category_id'], $data['business_name'], $data['owner_name'] ?? '', $data['description'] ?? '', $data['phone'] ?? '', $data['whatsapp'] ?? '', $data['email'] ?? '', $data['website'] ?? '', $data['address'] ?? '', $data['city'] ?? '', $data['state'] ?? '', $data['pincode'] ?? '', $data['latitude'] ?: null, $data['longitude'] ?: null, (int)($data['experience_years'] ?? 0), $data['price_range'] ?? '', $data['photo'] ?? '', (int)($data['is_verified'] ?? 0), (int)($data['is_featured'] ?? 0), $data['status'] ?? 'pending', $data['id']]);
                return $data['id'];
            } else {
                $stmt = $this->db->prepare("INSERT INTO directory_listings (category_id, user_id, business_name, owner_name, description, phone, whatsapp, email, website, address, city, state, pincode, latitude, longitude, experience_years, price_range, photo, is_verified, is_featured, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$data['category_id'], $data['user_id'] ?? null, $data['business_name'], $data['owner_name'] ?? '', $data['description'] ?? '', $data['phone'] ?? '', $data['whatsapp'] ?? '', $data['email'] ?? '', $data['website'] ?? '', $data['address'] ?? '', $data['city'] ?? '', $data['state'] ?? '', $data['pincode'] ?? '', $data['latitude'] ?: null, $data['longitude'] ?: null, (int)($data['experience_years'] ?? 0), $data['price_range'] ?? '', $data['photo'] ?? '', (int)($data['is_verified'] ?? 0), (int)($data['is_featured'] ?? 0), $data['status'] ?? 'pending']);
                return (int)$this->db->lastInsertId();
            }
        } catch (\Exception $e) { error_log('DirectoryService::upsertListing: ' . $e->getMessage()); return 0; }
    }

    public function deleteListing(int $id): bool
    {
        try {
            $this->db->prepare("DELETE FROM directory_reviews WHERE listing_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM directory_materials WHERE listing_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM directory_listings WHERE id = ?")->execute([$id]);
            return true;
        } catch (\Exception $e) { return false; }
    }

    private function incrementListingViews(int $id): void
    {
        try {
            $this->db->prepare("UPDATE directory_listings SET views = views + 1 WHERE id = ?")->execute([$id]);
        } catch (\Exception $e) {}
    }

    public function addReview(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO directory_reviews (listing_id, user_id, reviewer_name, rating, review, status) VALUES (?,?,?,?,?,?)");
            $r = $stmt->execute([$data['listing_id'], $data['user_id'] ?? null, $data['reviewer_name'] ?? 'Anonymous', (int)$data['rating'], $data['review'] ?? '', $data['status'] ?? 'approved']);
            $this->recalculateRating($data['listing_id']);
            return $r;
        } catch (\Exception $e) { return false; }
    }

    public function recalculateRating(int $listingId): void
    {
        $stmt = $this->db->prepare("SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM directory_reviews WHERE listing_id = ? AND status = 'approved'");
        $stmt->execute([$listingId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->db->prepare("UPDATE directory_listings SET rating = ?, review_count = ? WHERE id = ?")->execute([round($row['avg_r'] ?? 0, 1), (int)($row['cnt'] ?? 0), $listingId]);
    }

    public function getReviewsForAdmin(int $listingId = 0, string $status = ''): array
    {
        $where = []; $params = [];
        if ($listingId) { $where[] = 'r.listing_id = ?'; $params[] = $listingId; }
        if ($status) { $where[] = 'r.status = ?'; $params[] = $status; }
        $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->db->prepare("SELECT r.*, l.business_name FROM directory_reviews r LEFT JOIN directory_listings l ON r.listing_id = l.id $w ORDER BY r.created_at DESC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateReviewStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE directory_reviews SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $rStmt = $this->db->prepare("SELECT listing_id FROM directory_reviews WHERE id = ?");
            $rStmt->execute([$id]);
            $row = $rStmt->fetch(PDO::FETCH_ASSOC);
            if ($row) $this->recalculateRating($row['listing_id']);
            return true;
        } catch (\Exception $e) { return false; }
    }

    // ── Jobs ──

    public function getJobs(string $type = '', string $category = '', int $isSeeking = -1, int $page = 1, int $perPage = 20): array
    {
        $where = ["j.status = 'active'"]; $params = [];
        if ($type) { $where[] = 'j.job_type = ?'; $params[] = $type; }
        if ($category) { $where[] = 'j.category = ?'; $params[] = $category; }
        if ($isSeeking >= 0) { $where[] = 'j.is_seeking = ?'; $params[] = $isSeeking; }
        $w = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $cStmt = $this->db->prepare("SELECT COUNT(*) FROM directory_jobs j WHERE $w");
        $cStmt->execute($params);
        $total = (int)$cStmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT j.*, l.business_name, l.phone as listing_phone FROM directory_jobs j LEFT JOIN directory_listings l ON j.listing_id = l.id WHERE $w ORDER BY j.created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => ceil($total / $perPage)];
    }

    public function upsertJob(array $data): bool
    {
        try {
            if (!empty($data['id'])) {
                $stmt = $this->db->prepare("UPDATE directory_jobs SET listing_id=?, title=?, job_type=?, category=?, description=?, location=?, salary_range=?, contact_phone=?, contact_person=?, is_seeking=?, status=? WHERE id=?");
                return $stmt->execute([$data['listing_id'] ?: null, $data['title'], $data['job_type'] ?? 'gig', $data['category'] ?? '', $data['description'] ?? '', $data['location'] ?? '', $data['salary_range'] ?? '', $data['contact_phone'] ?? '', $data['contact_person'] ?? '', (int)($data['is_seeking'] ?? 1), $data['status'] ?? 'active', $data['id']]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO directory_jobs (listing_id, user_id, title, job_type, category, description, location, salary_range, contact_phone, contact_person, is_seeking, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
                return $stmt->execute([$data['listing_id'] ?: null, $data['user_id'] ?? null, $data['title'], $data['job_type'] ?? 'gig', $data['category'] ?? '', $data['description'] ?? '', $data['location'] ?? '', $data['salary_range'] ?? '', $data['contact_phone'] ?? '', $data['contact_person'] ?? '', (int)($data['is_seeking'] ?? 1), $data['status'] ?? 'active']);
            }
        } catch (\Exception $e) { error_log('DirectoryService::upsertJob: ' . $e->getMessage()); return false; }
    }

    public function deleteJob(int $id): bool
    {
        try { return $this->db->prepare("DELETE FROM directory_jobs WHERE id = ?")->execute([$id]); }
        catch (\Exception $e) { return false; }
    }

    public function getAllJobsAdmin(): array
    {
        $stmt = $this->db->query("SELECT j.*, l.business_name FROM directory_jobs j LEFT JOIN directory_listings l ON j.listing_id = l.id ORDER BY j.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Materials ──

    public function getMaterials(string $category = '', string $search = ''): array
    {
        $where = ["m.status = 'active'"]; $params = [];
        if ($category) { $where[] = 'm.category = ?'; $params[] = $category; }
        if ($search) { $where[] = '(m.material_name LIKE ? OR m.brand LIKE ?)'; $s = '%' . $search . '%'; $params[] = $s; $params[] = $s; }
        $w = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT m.*, l.business_name as supplier_name, l.phone as supplier_phone, l.city FROM directory_materials m LEFT JOIN directory_listings l ON m.listing_id = l.id WHERE $w ORDER BY m.category, m.material_name, m.price ASC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function upsertMaterial(array $data): bool
    {
        try {
            if (!empty($data['id'])) {
                $stmt = $this->db->prepare("UPDATE directory_materials SET listing_id=?, material_name=?, category=?, brand=?, unit=?, price=?, price_date=?, notes=?, status=? WHERE id=?");
                return $stmt->execute([$data['listing_id'] ?: null, $data['material_name'], $data['category'] ?? '', $data['brand'] ?? '', $data['unit'] ?? '', $data['price'], $data['price_date'] ?? date('Y-m-d'), $data['notes'] ?? '', $data['status'] ?? 'active', $data['id']]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO directory_materials (listing_id, material_name, category, brand, unit, price, price_date, notes, status) VALUES (?,?,?,?,?,?,?,?,?)");
                return $stmt->execute([$data['listing_id'] ?: null, $data['material_name'], $data['category'] ?? '', $data['brand'] ?? '', $data['unit'] ?? '', $data['price'], $data['price_date'] ?? date('Y-m-d'), $data['notes'] ?? '', $data['status'] ?? 'active']);
            }
        } catch (\Exception $e) { error_log('DirectoryService::upsertMaterial: ' . $e->getMessage()); return false; }
    }

    public function deleteMaterial(int $id): bool
    {
        try { return $this->db->prepare("DELETE FROM directory_materials WHERE id = ?")->execute([$id]); }
        catch (\Exception $e) { return false; }
    }

    public function getAllMaterialsAdmin(): array
    {
        $stmt = $this->db->query("SELECT m.*, l.business_name as supplier_name FROM directory_materials m LEFT JOIN directory_listings l ON m.listing_id = l.id ORDER BY m.category, m.material_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats(): array
    {
        $stats = [];
        $stats['total_categories'] = $this->db->query("SELECT COUNT(*) FROM directory_categories")->fetchColumn();
        $stats['total_listings'] = $this->db->query("SELECT COUNT(*) FROM directory_listings")->fetchColumn();
        $stats['approved_listings'] = $this->db->query("SELECT COUNT(*) FROM directory_listings WHERE status = 'approved'")->fetchColumn();
        $stats['pending_listings'] = $this->db->query("SELECT COUNT(*) FROM directory_listings WHERE status = 'pending'")->fetchColumn();
        $stats['total_jobs'] = $this->db->query("SELECT COUNT(*) FROM directory_jobs")->fetchColumn();
        $stats['active_jobs'] = $this->db->query("SELECT COUNT(*) FROM directory_jobs WHERE status = 'active'")->fetchColumn();
        $stats['total_reviews'] = $this->db->query("SELECT COUNT(*) FROM directory_reviews")->fetchColumn();
        $stats['total_materials'] = $this->db->query("SELECT COUNT(*) FROM directory_materials")->fetchColumn();
        return $stats;
    }
}
