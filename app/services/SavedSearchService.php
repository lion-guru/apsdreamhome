<?php

namespace App\Services;

use App\Services\EmailService;

use PDO;

/**
 * Saved Searches Service
 * Save/load/share complex filter combinations for any entity
 */
class SavedSearchService
{
    private PDO $db;

    public function __construct($db = null)
    {
        if ($db === null) {
            $db = new \PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
        } elseif (is_object($db) && method_exists($db, 'getPdo')) {
            $db = $db->getPdo();
        }
        $this->db = $db;
    }

    public function save(int $userId, string $role, string $name, string $entityType, array $filters, ?string $description = null, bool $isFavorite = false, bool $isPublic = false, int $emailAlerts = 0): int
    {
        $stmt = $this->db->prepare("INSERT INTO saved_searches (user_id, user_role, name, description, entity_type, filters, email_alerts, is_favorite, is_public, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $role, $name, $description, $entityType, json_encode($filters, JSON_UNESCAPED_UNICODE), $emailAlerts ? 1 : 0, $isFavorite ? 1 : 0, $isPublic ? 1 : 0, $this->getTenantId()]);
        return (int)$this->db->lastInsertId();
    }

    public function get(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM saved_searches WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['filters'] = json_decode($row['filters'], true) ?: [];
        }
        return $row ?: null;
    }

    public function update(int $id, int $userId, string $role, array $data): bool
    {
        $allowed = ['name', 'description', 'filters', 'is_favorite', 'is_public'];
        $sets = [];
        $vals = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field = ?";
                $vals[] = $field === 'filters' ? json_encode($data[$field], JSON_UNESCAPED_UNICODE) : $data[$field];
            }
        }
        if (empty($sets)) return false;
        $vals[] = $id;
        $vals[] = $userId;
        $vals[] = $role;
        $vals[] = $this->getTenantId();
        $stmt = $this->db->prepare("UPDATE saved_searches SET " . implode(', ', $sets) . " WHERE id = ? AND user_id = ? AND user_role = ? AND tenant_id = ?");
        $stmt->execute($vals);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id, int $userId, string $role): bool
    {
        $stmt = $this->db->prepare("DELETE FROM saved_searches WHERE id = ? AND tenant_id = ? AND (user_id = ? AND user_role = ? OR is_public = 1)");
        $stmt->execute([$id, $this->getTenantId(), $userId, $role]);
        return $stmt->rowCount() > 0;
    }

    public function toggleFavorite(int $id, int $userId, string $role): bool
    {
        $stmt = $this->db->prepare("UPDATE saved_searches SET is_favorite = NOT is_favorite WHERE id = ? AND tenant_id = ? AND (user_id = ? AND user_role = ? OR is_public = 1)");
        $stmt->execute([$id, $this->getTenantId(), $userId, $role]);
        return $stmt->rowCount() > 0;
    }

    public function recordUse(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE saved_searches SET use_count = use_count + 1, last_used_at = NOW() WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$id, $this->getTenantId()]);
        return true;
    }

    public function list(int $userId, string $role, string $entityType = '', bool $favoritesOnly = false): array
    {
        $sql = "SELECT * FROM saved_searches WHERE (user_id = ? AND user_role = ? OR is_public = 1)";
        $params = [$userId, $role];
        if ($entityType) {
            $sql .= " AND entity_type = ?";
            $params[] = $entityType;
        }
        if ($favoritesOnly) {
            $sql .= " AND is_favorite = 1";
        }
        $sql .= " ORDER BY is_favorite DESC, use_count DESC, last_used_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['filters'] = json_decode($row['filters'], true) ?: [];
        }
        return $rows;
    }

    public function recordHistory(int $userId, string $role, string $entityType, array $filters, ?int $resultsCount = null): void
    {
        $stmt = $this->db->prepare("INSERT INTO search_history (user_id, user_role, entity_type, filters, results_count, ip_address, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $role, $entityType, json_encode($filters, JSON_UNESCAPED_UNICODE), $resultsCount, $_SERVER['REMOTE_ADDR'] ?? null, $this->getTenantId()]);
    }

    public function getHistory(int $userId, string $role, string $entityType = '', int $limit = 20): array
    {
        $sql = "SELECT * FROM search_history WHERE user_id = ? AND user_role = ?";
        $params = [$userId, $role];
        if ($entityType) {
            $sql .= " AND entity_type = ?";
            $params[] = $entityType;
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['filters'] = json_decode($row['filters'], true) ?: [];
        }
        return $rows;
    }

    public function getStats(int $userId, string $role): array
    {
        $stmt = $this->db->prepare("SELECT entity_type, COUNT(*) as count, SUM(use_count) as total_uses, MAX(last_used_at) as last_used FROM saved_searches WHERE user_id = ? AND user_role = ? OR is_public = 1 GROUP BY entity_type");
        $stmt->execute([$userId, $role]);
        $byEntity = $stmt->fetchAll();
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM saved_searches WHERE user_id = ? AND user_role = ?");
        $stmt->execute([$userId, $role]);
        $mine = $stmt->fetch();
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM saved_searches WHERE is_public = 1");
        $stmt->execute();
        $public = $stmt->fetch();
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM saved_searches WHERE is_favorite = 1 AND (user_id = ? AND user_role = ? OR is_public = 1)");
        $stmt->execute([$userId, $role]);
        $fav = $stmt->fetch();
        return [
            'my_searches' => (int)($mine['total'] ?? 0),
            'public_searches' => (int)($public['total'] ?? 0),
            'favorites' => (int)($fav['total'] ?? 0),
            'by_entity' => $byEntity
        ];
    }

    /**
     * Front-friendly alias: Save a search using just (userId, name, filters).
     * Returns the new saved search id.
     */
    public function saveSearch(int $userId, string $name, array $filters, ?string $description = null, int $emailAlerts = 0, string $entityType = 'user_properties'): int
    {
        $role = $this->resolveUserRole($userId);
        return $this->save($userId, $role, $name, $entityType, $filters, $description, false, false, $emailAlerts);
    }

    /**
     * Front-friendly alias: list all saved searches for a user.
     */
    public function getUserSearches(int $userId, ?string $entityType = null): array
    {
        $role = $this->resolveUserRole($userId);
        return $this->list($userId, $role, $entityType ?? '', false);
    }

    /**
     * Resolve role string for a user.
     */
    public function resolveUserRole(int $userId): string
    {
        try {
            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            return $row['role'] ?? 'customer';
        } catch (\Throwable $e) {
            return 'customer';
        }
    }

    /**
     * Build & execute a property search from a saved search's filter set.
     * Returns array of matching properties (id, name, address, price, image, etc).
     */
    public function matchProperties(array $filters, int $limit = 100, int $offset = 0, ?int $excludePropertyId = null): array
    {
        $where = ["status = 'approved'"];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "(name LIKE ? OR address LIKE ? OR location LIKE ? OR description LIKE ?)";
            $q = '%' . $filters['q'] . '%';
            array_push($params, $q, $q, $q, $q);
        }
        if (!empty($filters['type'])) {
            $where[] = "property_type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['listing'])) {
            $where[] = "listing_type = ?";
            $params[] = $filters['listing'];
        }
        if (!empty($filters['location'])) {
            $where[] = "(address LIKE ? OR location LIKE ? OR city_name LIKE ?)";
            $loc = '%' . $filters['location'] . '%';
            array_push($params, $loc, $loc, $loc);
        }
        if (isset($filters['min_price']) && (int)$filters['min_price'] > 0) {
            $where[] = "price >= ?";
            $params[] = (int)$filters['min_price'];
        }
        if (isset($filters['max_price']) && (int)$filters['max_price'] > 0) {
            $where[] = "price <= ?";
            $params[] = (int)$filters['max_price'];
        }
        if (isset($filters['bedrooms']) && $filters['bedrooms'] !== '' && $filters['bedrooms'] !== null) {
            $where[] = "bedrooms >= ?";
            $params[] = (int)$filters['bedrooms'];
        }
        if (isset($filters['bathrooms']) && $filters['bathrooms'] !== '' && $filters['bathrooms'] !== null) {
            $where[] = "bathrooms >= ?";
            $params[] = (int)$filters['bathrooms'];
        }
        if (!empty($filters['furnished'])) {
            $where[] = "furnished = ?";
            $params[] = $filters['furnished'];
        }
        if (!empty($filters['year_built'])) {
            $where[] = "year_built >= ?";
            $params[] = (int)$filters['year_built'];
        }
        if (isset($filters['area_min']) && (int)$filters['area_min'] > 0) {
            $where[] = "area_sqft >= ?";
            $params[] = (int)$filters['area_min'];
        }
        if (isset($filters['area_max']) && (int)$filters['area_max'] > 0) {
            $where[] = "area_sqft <= ?";
            $params[] = (int)$filters['area_max'];
        }
        if (!empty($filters['state_id'])) {
            $where[] = "state_id = ?";
            $params[] = (int)$filters['state_id'];
        }
        if (!empty($filters['district_id'])) {
            $where[] = "district_id = ?";
            $params[] = (int)$filters['district_id'];
        }
        if (!empty($filters['city_id'])) {
            $where[] = "city_id = ?";
            $params[] = (int)$filters['city_id'];
        }
        if ($excludePropertyId) {
            $where[] = "id != ?";
            $params[] = $excludePropertyId;
        }

        $sort = $filters['sort'] ?? 'newest';
        $orderBy = match ($sort) {
            'price_low' => 'price ASC',
            'price_high' => 'price DESC',
            'oldest' => 'created_at ASC',
            'area_large' => 'area_sqft DESC',
            'area_small' => 'area_sqft ASC',
            'newest', '' => 'created_at DESC',
            default => 'created_at DESC'
        };

        $sql = "SELECT id, user_id, name, property_type, listing_type, address, location, area_sqft, price, price_type, image, description, bedrooms, bathrooms, furnished, year_built, created_at, views
                FROM user_properties
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $orderBy
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count matches for a filter set.
     */
    public function countMatches(array $filters): int
    {
        $where = ["status = 'approved'"];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "(name LIKE ? OR address LIKE ? OR location LIKE ? OR description LIKE ?)";
            $q = '%' . $filters['q'] . '%';
            array_push($params, $q, $q, $q, $q);
        }
        if (!empty($filters['type'])) { $where[] = "property_type = ?"; $params[] = $filters['type']; }
        if (!empty($filters['listing'])) { $where[] = "listing_type = ?"; $params[] = $filters['listing']; }
        if (!empty($filters['location'])) {
            $where[] = "(address LIKE ? OR location LIKE ? OR city_name LIKE ?)";
            $loc = '%' . $filters['location'] . '%';
            array_push($params, $loc, $loc, $loc);
        }
        if (isset($filters['min_price']) && (int)$filters['min_price'] > 0) { $where[] = "price >= ?"; $params[] = (int)$filters['min_price']; }
        if (isset($filters['max_price']) && (int)$filters['max_price'] > 0) { $where[] = "price <= ?"; $params[] = (int)$filters['max_price']; }
        if (isset($filters['bedrooms']) && (int)$filters['bedrooms'] > 0) { $where[] = "bedrooms >= ?"; $params[] = (int)$filters['bedrooms']; }
        if (isset($filters['bathrooms']) && (int)$filters['bathrooms'] > 0) { $where[] = "bathrooms >= ?"; $params[] = (int)$filters['bathrooms']; }
        if (!empty($filters['furnished'])) { $where[] = "furnished = ?"; $params[] = $filters['furnished']; }
        if (!empty($filters['year_built'])) { $where[] = "year_built >= ?"; $params[] = (int)$filters['year_built']; }
        if (isset($filters['area_min']) && (int)$filters['area_min'] > 0) { $where[] = "area_sqft >= ?"; $params[] = (int)$filters['area_min']; }
        if (isset($filters['area_max']) && (int)$filters['area_max'] > 0) { $where[] = "area_sqft <= ?"; $params[] = (int)$filters['area_max']; }
        if (!empty($filters['state_id'])) { $where[] = "state_id = ?"; $params[] = (int)$filters['state_id']; }
        if (!empty($filters['district_id'])) { $where[] = "district_id = ?"; $params[] = (int)$filters['district_id']; }
        if (!empty($filters['city_id'])) { $where[] = "city_id = ?"; $params[] = (int)$filters['city_id']; }

        $sql = "SELECT COUNT(*) as cnt FROM user_properties WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Find properties newly added since `since` that match the saved search.
     * Used by sendAlerts() cron.
     */
    public function findNewMatches(array $filters, string $since): array
    {
        $filters['exclude_existing'] = false;
        $matches = $this->matchProperties($filters, 50, 0);
        $new = [];
        foreach ($matches as $m) {
            $created = $m['created_at'] ?? '';
            if ($created && $created > $since) {
                $new[] = $m;
            }
        }
        return $new;
    }

    /**
     * Record that an alert email was sent (or attempted) for (search, property).
     * Prevents duplicate sends via UNIQUE KEY (search_id, property_id).
     */
    public function logAlertSent(int $searchId, int $userId, int $propertyId, string $status = 'sent', ?string $error = null): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO search_alert_log (search_id, user_id, property_id, sent_at, email_status, error_message, tenant_id)
                VALUES (?, ?, ?, NOW(), ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    sent_at = NOW(),
                    email_status = VALUES(email_status),
                    error_message = VALUES(error_message)
            ");
            $stmt->execute([$searchId, $userId, $propertyId, $status, $error, $this->getTenantId()]);
            return true;
        } catch (\Throwable $e) {
            error_log("SavedSearchService::logAlertSent: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a saved search's email_alerts toggle and last_run_at timestamp.
     */
    public function toggleAlerts(int $id, int $userId, bool $enabled): bool
    {
        $role = $this->resolveUserRole($userId);
        $stmt = $this->db->prepare("
            UPDATE saved_searches 
            SET email_alerts = ?, last_run_at = NOW() 
            WHERE id = ? AND user_id = ? AND user_role = ? AND tenant_id = ?
        ");
        $stmt->execute([$enabled ? 1 : 0, $id, $userId, $role, $this->getTenantId()]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update result_count and last_run_at after executing a search.
     */
    public function recordRun(int $id, int $resultCount): bool
    {
        $stmt = $this->db->prepare("
            UPDATE saved_searches 
            SET result_count = ?, last_run_at = NOW(), use_count = use_count + 1, last_used_at = NOW()
            WHERE id = ? AND tenant_id = ?
        ");
        $stmt->execute([$resultCount, $id, $this->getTenantId()]);
        return true;
    }

    /**
     * Cron job: send email alerts for all saved searches with email_alerts=1.
     * For each search, find properties added since last_run_at (or last 24h)
     * that match the criteria and that we haven't alerted about before.
     * Returns stats.
     */
    public function sendAlerts(): array
    {
        $stats = [
            'searches_processed' => 0,
            'alerts_sent' => 0,
            'alerts_skipped_duplicate' => 0,
            'alerts_failed' => 0,
            'errors' => []
        ];

        try {
            $stmt = $this->db->query("
                SELECT s.*, u.email as user_email, u.name as user_name
                FROM saved_searches s
                JOIN users u ON s.user_id = u.id
                WHERE s.email_alerts = 1 AND u.email IS NOT NULL AND u.email != ''
            ");
            $searches = $stmt->fetchAll();

            $emailService = new EmailService();

            foreach ($searches as $search) {
                $stats['searches_processed']++;
                $filters = json_decode($search['filters'] ?? '{}', true) ?: [];
                $sinceDate = $search['last_run_at']
                    ? date('Y-m-d H:i:s', strtotime($search['last_run_at']) - 300) // 5 min overlap
                    : date('Y-m-d H:i:s', strtotime('-24 hours'));

                $newMatches = $this->findNewMatches($filters, $sinceDate);

                foreach ($newMatches as $property) {
                    // Check if we've already alerted about this property for this search
                    $check = $this->db->prepare("
                        SELECT id FROM search_alert_log
                        WHERE search_id = ? AND property_id = ? AND email_status = 'sent'
                    ");
                    $check->execute([$search['id'], $property['id']]);
                    if ($check->fetch()) {
                        $stats['alerts_skipped_duplicate']++;
                        continue;
                    }

                    $subject = "New property match: " . ($search['name'] ?? 'Your saved search');
                    $body = $this->buildAlertEmailBody($search, $property);

                    $sent = $emailService->send($search['user_email'], $subject, $body);

                    if ($sent) {
                        $this->logAlertSent($search['id'], $search['user_id'], $property['id'], 'sent');
                        $stats['alerts_sent']++;
                    } else {
                        $this->logAlertSent($search['id'], $search['user_id'], $property['id'], 'failed', 'mail() returned false');
                        $stats['alerts_failed']++;
                    }
                }

                // Update last_run_at even if no new matches (mark as "checked")
                $this->recordRun($search['id'], $this->countMatches($filters));
            }
        } catch (\Throwable $e) {
            $stats['errors'][] = $e->getMessage();
            error_log("SavedSearchService::sendAlerts: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Build HTML email body for a saved-search alert.
     */
    public function buildAlertEmailBody(array $search, array $property): string
    {
        $baseUrl = BASE_URL;
        $link = $baseUrl . '/properties?q=' . urlencode($property['name'] ?? '') . '&type=' . urlencode($property['property_type'] ?? '');
        $propertyUrl = $baseUrl . '/listing/' . (int)($property['id'] ?? 0);
        $image = !empty($property['image']) ? $baseUrl . '/assets/images/properties/' . htmlspecialchars($property['image']) : $baseUrl . '/assets/images/placeholder/property.svg';
        $price = number_format((float)($property['price'] ?? 0));
        $area = (int)($property['area_sqft'] ?? 0);
        $bedrooms = (int)($property['bedrooms'] ?? 0);
        $furnished = htmlspecialchars($property['furnished'] ?? '');

        $name = htmlspecialchars($search['name'] ?? 'Your saved search');
        $userName = htmlspecialchars($search['user_name'] ?? 'Customer');

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                <h2 style='margin: 0;'>🔔 New Property Match!</h2>
            </div>
            <div style='background: #f9f9f9; padding: 20px;'>
                <p>Hi <strong>{$userName}</strong>,</p>
                <p>A new property matching your saved search <strong>\"{$name}\"</strong> has just been listed:</p>
                <div style='background: white; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; margin: 20px 0;'>
                    <img src='{$image}' alt='Property' style='width: 100%; height: 200px; object-fit: cover;'>
                    <div style='padding: 15px;'>
                        <h3 style='margin: 0 0 10px;'>" . htmlspecialchars($property['name'] ?? 'Property') . "</h3>
                        <p style='margin: 5px 0; color: #666;'>📍 " . htmlspecialchars($property['address'] ?? $property['location'] ?? '') . "</p>
                        <p style='margin: 5px 0;'><strong style='color: #28a745; font-size: 1.3em;'>₹{$price}</strong></p>
                        <p style='margin: 5px 0; font-size: 0.9em; color: #888;'>
                            " . ucfirst($property['property_type'] ?? 'Property') . " •
                            {$area} sq ft •
                            " . ($bedrooms > 0 ? "{$bedrooms} BHK • " : '') . "
                            {$furnished}
                        </p>
                        <a href='{$propertyUrl}' style='display: inline-block; margin-top: 10px; background: #0d9488; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>View Property</a>
                    </div>
                </div>
                <p style='text-align: center; margin-top: 20px;'>
                    <a href='{$link}' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>See All Matches</a>
                </p>
                <p style='font-size: 0.85em; color: #888; margin-top: 30px;'>
                    You're receiving this because you saved a search with email alerts enabled.
                    <a href='{$baseUrl}/user/saved-searches' style='color: #0d9488;'>Manage saved searches</a>
                </p>
            </div>
        </div>
        ";
    }

    /**
     * Cron: cleanup old saved searches (no activity in 90+ days).
     * Keeps favorites and public searches.
     */
    public function cleanup(int $daysOld = 90): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM saved_searches
            WHERE tenant_id = ?
              AND is_favorite = 0 
              AND is_public = 0
              AND (last_used_at IS NULL OR last_used_at < DATE_SUB(NOW(), INTERVAL ? DAY))
              AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$this->getTenantId(), $daysOld, $daysOld]);
        return $stmt->rowCount();
    }

    /**
     * Get sent alert log for a user.
     */
    public function getAlertLog(int $userId, int $limit = 50): array
    {
        $limit = max(1, min(500, (int)$limit));
        $stmt = $this->db->prepare("
            SELECT l.*, s.name as search_name, p.name as property_name
            FROM search_alert_log l
            LEFT JOIN saved_searches s ON l.search_id = s.id
            LEFT JOIN user_properties p ON l.property_id = p.id
            WHERE l.user_id = ?
            ORDER BY l.sent_at DESC
            LIMIT {$limit}
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    private function getTenantId(): int
    {
        if (class_exists('\App\Core\Middleware\TenantContext')) {
            try {
                return \App\Core\Middleware\TenantContext::getId();
            } catch (\Throwable $e) {
                return 1;
            }
        }
        return 1;
    }
}
