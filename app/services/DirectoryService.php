<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;

class DirectoryService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Categories ──

    public function getActiveCategories(): array
    {
        $sql = "SELECT * FROM directory_categories WHERE is_active = 1" . $this->tenantSql() . " ORDER BY sort_order ASC";
        $params = [];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getCategoryBySlug(string $slug): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM directory_categories WHERE slug = ? AND is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1";
        $params = [$slug];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getCategory(int $id): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM directory_categories WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1";
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAllCategories(): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT dc.*, (SELECT COUNT(*) FROM directory_listings WHERE category_id = dc.id AND status = 'approved'" . ($tid > 1 ? " AND tenant_id = ?" : "") . ") as listing_count FROM directory_categories dc" . ($tid > 1 ? " WHERE dc.tenant_id = ?" : "") . " ORDER BY dc.sort_order ASC";
        $params = [];
        if ($tid > 1) $params = [$tid, $tid];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function upsertCategory(array $data): bool
    {
        try {
            if (!empty($data['id'])) {
                $stmt = $this->db->prepare("UPDATE directory_categories SET name=?, slug=?, description=?, icon=?, parent_id=?, sort_order=?, is_active=? WHERE id=? AND tenant_id=?");
                return $stmt->execute([$data['name'], $data['slug'], $data['description'] ?? '', $data['icon'] ?? 'fas fa-building', $data['parent_id'] ?: null, (int)($data['sort_order'] ?? 0), (int)($data['is_active'] ?? 1), $data['id'], $this->tenantId()]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO directory_categories (name, slug, description, icon, parent_id, sort_order, is_active, tenant_id) VALUES (?,?,?,?,?,?,?,?)");
                return $stmt->execute([$data['name'], $data['slug'], $data['description'] ?? '', $data['icon'] ?? 'fas fa-building', $data['parent_id'] ?: null, (int)($data['sort_order'] ?? 0), (int)($data['is_active'] ?? 1), $this->tenantId()]);
            }
        } catch (\Exception $e) { error_log('DirectoryService::upsertCategory: ' . $e->getMessage()); return false; }
    }

    public function deleteCategory(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE directory_listings SET category_id = NULL WHERE category_id = ? AND tenant_id = ?");
            $stmt->execute([$id, $this->tenantId()]);
            $stmt2 = $this->db->prepare("DELETE FROM directory_categories WHERE id = ? AND tenant_id = ?");
            return $stmt2->execute([$id, $this->tenantId()]);
        } catch (\Exception $e) { return false; }
    }

    // ── Listings ──

    public function getListings(int $categoryId = null, string $search = '', string $city = '', string $sort = 'latest', int $page = 1, int $perPage = 20): array
    {
        $where = ["l.status = 'approved'" . $this->tenantSql()];
        $params = [];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();

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
        $sql = "SELECT l.*, dc.name as category_name, dc.slug as category_slug, dc.icon as category_icon
            FROM directory_listings l LEFT JOIN directory_categories dc ON l.category_id = dc.id
            WHERE l.status = 'approved' AND l.is_featured = 1" . $this->tenantSql() . " ORDER BY l.rating DESC, l.views DESC LIMIT ?";
        $params = [];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $params[] = $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1 + count($params) - 1, end($params), PDO::PARAM_INT);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getListing(int $id): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT l.*, dc.name as category_name, dc.slug as category_slug
            FROM directory_listings l LEFT JOIN directory_categories dc ON l.category_id = dc.id WHERE l.id = ?" . ($tid > 1 ? " AND l.tenant_id = ?" : "") . " LIMIT 1";
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->incrementListingViews($id);
        }
        return $row ?: null;
    }

    public function getListingReviews(int $listingId): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM directory_reviews WHERE listing_id = ? AND status = 'approved'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC";
        $params = [$listingId];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAllListings(string $status = '', int $categoryId = 0): array
    {
        $where = [];
        $params = [];
        $tid = $this->tenantId();
        $where[] = "l.tenant_id = ?";
        $params[] = $tid;
        if ($status) { $where[] = 'l.status = ?'; $params[] = $status; }
        if ($categoryId) { $where[] = 'l.category_id = ?'; $params[] = $categoryId; }
        $w = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT l.*, dc.name as category_name FROM directory_listings l LEFT JOIN directory_categories dc ON l.category_id = dc.id $w ORDER BY l.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function upsertListing(array $data): int
    {
        try {
            if (!empty($data['id'])) {
                $stmt = $this->db->prepare("UPDATE directory_listings SET category_id=?, business_name=?, owner_name=?, description=?, phone=?, whatsapp=?, email=?, website=?, address=?, city=?, state=?, pincode=?, latitude=?, longitude=?, experience_years=?, price_range=?, photo=?, is_verified=?, is_featured=?, status=? WHERE id=? AND tenant_id=?");
                $stmt->execute([$data['category_id'], $data['business_name'], $data['owner_name'] ?? '', $data['description'] ?? '', $data['phone'] ?? '', $data['whatsapp'] ?? '', $data['email'] ?? '', $data['website'] ?? '', $data['address'] ?? '', $data['city'] ?? '', $data['state'] ?? '', $data['pincode'] ?? '', $data['latitude'] ?: null, $data['longitude'] ?: null, (int)($data['experience_years'] ?? 0), $data['price_range'] ?? '', $data['photo'] ?? '', (int)($data['is_verified'] ?? 0), (int)($data['is_featured'] ?? 0), $data['status'] ?? 'pending', $data['id'], $this->tenantId()]);
                return $data['id'];
            } else {
                $stmt = $this->db->prepare("INSERT INTO directory_listings (category_id, user_id, business_name, owner_name, description, phone, whatsapp, email, website, address, city, state, pincode, latitude, longitude, experience_years, price_range, photo, is_verified, is_featured, status, tenant_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$data['category_id'], $data['user_id'] ?? null, $data['business_name'], $data['owner_name'] ?? '', $data['description'] ?? '', $data['phone'] ?? '', $data['whatsapp'] ?? '', $data['email'] ?? '', $data['website'] ?? '', $data['address'] ?? '', $data['city'] ?? '', $data['state'] ?? '', $data['pincode'] ?? '', $data['latitude'] ?: null, $data['longitude'] ?: null, (int)($data['experience_years'] ?? 0), $data['price_range'] ?? '', $data['photo'] ?? '', (int)($data['is_verified'] ?? 0), (int)($data['is_featured'] ?? 0), $data['status'] ?? 'pending', $this->tenantId()]);
                return (int)$this->db->lastInsertId();
            }
        } catch (\Exception $e) { error_log('DirectoryService::upsertListing: ' . $e->getMessage()); return 0; }
    }

    public function deleteListing(int $id): bool
    {
        try {
            $this->db->prepare("DELETE FROM directory_reviews WHERE listing_id = ? AND tenant_id = ?")->execute([$id, $this->tenantId()]);
            $this->db->prepare("DELETE FROM directory_materials WHERE listing_id = ? AND tenant_id = ?")->execute([$id, $this->tenantId()]);
            $this->db->prepare("DELETE FROM directory_listings WHERE id = ? AND tenant_id = ?")->execute([$id, $this->tenantId()]);
            return true;
        } catch (\Exception $e) { return false; }
    }

    private function incrementListingViews(int $id): void
    {
        try {
            $this->db->prepare("UPDATE directory_listings SET views = views + 1 WHERE id = ? AND tenant_id = ?")->execute([$id, $this->tenantId()]);
        } catch (\Exception $e) { error_log($e->getMessage()); }
    }

    public function addReview(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO directory_reviews (listing_id, user_id, reviewer_name, rating, review, status, tenant_id) VALUES (?,?,?,?,?,?,?)");
            $r = $stmt->execute([$data['listing_id'], $data['user_id'] ?? null, $data['reviewer_name'] ?? 'Anonymous', (int)$data['rating'], $data['review'] ?? '', $data['status'] ?? 'approved', $this->tenantId()]);
            $this->recalculateRating($data['listing_id']);
            return $r;
        } catch (\Exception $e) { return false; }
    }

    public function recalculateRating(int $listingId): void
    {
        $stmt = $this->db->prepare("SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM directory_reviews WHERE listing_id = ? AND status = 'approved'");
        $stmt->execute([$listingId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->db->prepare("UPDATE directory_listings SET rating = ?, review_count = ? WHERE id = ? AND tenant_id = ?")->execute([round($row['avg_r'] ?? 0, 1), (int)($row['cnt'] ?? 0), $listingId, $this->tenantId()]);
    }

    public function getReviewsForAdmin(int $listingId = 0, string $status = ''): array
    {
        $where = []; $params = [];
        $tid = $this->tenantId();
        $where[] = "r.tenant_id = ?";
        $params[] = $tid;
        if ($listingId) { $where[] = 'r.listing_id = ?'; $params[] = $listingId; }
        if ($status) { $where[] = 'r.status = ?'; $params[] = $status; }
        $w = 'WHERE ' . implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT r.*, l.business_name FROM directory_reviews r LEFT JOIN directory_listings l ON r.listing_id = l.id $w ORDER BY r.created_at DESC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function updateReviewStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE directory_reviews SET status = ? WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$status, $id, $this->tenantId()]);
            $rStmt = $this->db->prepare("SELECT listing_id FROM directory_reviews WHERE id = ? AND tenant_id = ?");
            $rStmt->execute([$id, $this->tenantId()]);
            $row = $rStmt->fetch(PDO::FETCH_ASSOC);
            if ($row) $this->recalculateRating($row['listing_id']);
            return true;
        } catch (\Exception $e) { return false; }
    }

    // ── Jobs ──

    public function getJobs(string $type = '', string $category = '', int $isSeeking = -1, int $page = 1, int $perPage = 20): array
    {
        $where = ["j.status = 'active'", "j.tenant_id = ?"]; $params = [$this->tenantId()];
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
                $stmt = $this->db->prepare("UPDATE directory_jobs SET listing_id=?, title=?, job_type=?, category=?, description=?, location=?, salary_range=?, contact_phone=?, contact_person=?, is_seeking=?, status=? WHERE id=? AND tenant_id=?");
                return $stmt->execute([$data['listing_id'] ?: null, $data['title'], $data['job_type'] ?? 'gig', $data['category'] ?? '', $data['description'] ?? '', $data['location'] ?? '', $data['salary_range'] ?? '', $data['contact_phone'] ?? '', $data['contact_person'] ?? '', (int)($data['is_seeking'] ?? 1), $data['status'] ?? 'active', $data['id'], $this->tenantId()]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO directory_jobs (listing_id, user_id, title, job_type, category, description, location, salary_range, contact_phone, contact_person, is_seeking, status, tenant_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                return $stmt->execute([$data['listing_id'] ?: null, $data['user_id'] ?? null, $data['title'], $data['job_type'] ?? 'gig', $data['category'] ?? '', $data['description'] ?? '', $data['location'] ?? '', $data['salary_range'] ?? '', $data['contact_phone'] ?? '', $data['contact_person'] ?? '', (int)($data['is_seeking'] ?? 1), $data['status'] ?? 'active', $this->tenantId()]);
            }
        } catch (\Exception $e) { error_log('DirectoryService::upsertJob: ' . $e->getMessage()); return false; }
    }

    public function deleteJob(int $id): bool
    {
        try { return $this->db->prepare("DELETE FROM directory_jobs WHERE id = ? AND tenant_id = ?")->execute([$id, $this->tenantId()]); }
        catch (\Exception $e) { return false; }
    }

    public function getAllJobsAdmin(): array
    {
        $stmt = $this->db->prepare("SELECT j.*, l.business_name FROM directory_jobs j LEFT JOIN directory_listings l ON j.listing_id = l.id WHERE j.tenant_id = ? ORDER BY j.created_at DESC");
        $stmt->execute([$this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // ── Materials ──

    public function getMaterials(string $category = '', string $search = ''): array
    {
        $where = ["m.status = 'active'", "m.tenant_id = ?"]; $params = [$this->tenantId()];
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
                $stmt = $this->db->prepare("UPDATE directory_materials SET listing_id=?, material_name=?, category=?, brand=?, unit=?, price=?, price_date=?, notes=?, status=? WHERE id=? AND tenant_id=?");
                return $stmt->execute([$data['listing_id'] ?: null, $data['material_name'], $data['category'] ?? '', $data['brand'] ?? '', $data['unit'] ?? '', $data['price'], $data['price_date'] ?? date('Y-m-d'), $data['notes'] ?? '', $data['status'] ?? 'active', $data['id'], $this->tenantId()]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO directory_materials (listing_id, material_name, category, brand, unit, price, price_date, notes, status, tenant_id) VALUES (?,?,?,?,?,?,?,?,?,?)");
                return $stmt->execute([$data['listing_id'] ?: null, $data['material_name'], $data['category'] ?? '', $data['brand'] ?? '', $data['unit'] ?? '', $data['price'], $data['price_date'] ?? date('Y-m-d'), $data['notes'] ?? '', $data['status'] ?? 'active', $this->tenantId()]);
            }
        } catch (\Exception $e) { error_log('DirectoryService::upsertMaterial: ' . $e->getMessage()); return false; }
    }

    public function deleteMaterial(int $id): bool
    {
        try { return $this->db->prepare("DELETE FROM directory_materials WHERE id = ? AND tenant_id = ?")->execute([$id, $this->tenantId()]); }
        catch (\Exception $e) { return false; }
    }

    public function getAllMaterialsAdmin(): array
    {
        $stmt = $this->db->prepare("SELECT m.*, l.business_name as supplier_name FROM directory_materials m LEFT JOIN directory_listings l ON m.listing_id = l.id WHERE m.tenant_id = ? ORDER BY m.category, m.material_name");
        $stmt->execute([$this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStats(): array
    {
        $stats = [];
        $tid = $this->tenantId();
        $params = $tid > 1 ? [$tid] : [];
        $sqlTenant = $this->tenantSql();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM directory_categories" . $sqlTenant);
        $stmt->execute($params);
        $stats['total_categories'] = (int)$stmt->fetchColumn();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM directory_listings" . $sqlTenant);
        $stmt->execute($params);
        $stats['total_listings'] = (int)$stmt->fetchColumn();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM directory_listings WHERE status = 'approved'" . $sqlTenant);
        $stmt->execute($params);
        $stats['approved_listings'] = (int)$stmt->fetchColumn();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM directory_listings WHERE status = 'pending'" . $sqlTenant);
        $stmt->execute($params);
        $stats['pending_listings'] = (int)$stmt->fetchColumn();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM directory_jobs" . $sqlTenant);
        $stmt->execute($params);
        $stats['total_jobs'] = (int)$stmt->fetchColumn();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM directory_jobs WHERE status = 'active'" . $sqlTenant);
        $stmt->execute($params);
        $stats['active_jobs'] = (int)$stmt->fetchColumn();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM directory_reviews" . $sqlTenant);
        $stmt->execute($params);
        $stats['total_reviews'] = (int)$stmt->fetchColumn();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM directory_materials" . $sqlTenant);
        $stmt->execute($params);
        $stats['total_materials'] = (int)$stmt->fetchColumn();
        return $stats;
    }
}
