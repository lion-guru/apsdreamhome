<?php
namespace App\Services;

use PDO;
use App\Traits\ServiceTenantTrait;

class ResellPropertyService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function listProperties(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT r.*, u.name as owner_name, d.name as district_name
                FROM resell_properties r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN districts d ON r.district_id = d.id
                WHERE r.status IN ('active', 'approved', 'pending', 'available')" . ($tid > 1 ? " AND r.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if (!empty($filters['property_type'])) { $sql .= " AND r.property_type = :pt"; $params[':pt'] = $filters['property_type']; }
        if (!empty($filters['min_price'])) { $sql .= " AND r.asking_price >= :mp"; $params[':mp'] = $filters['min_price']; }
        if (!empty($filters['max_price'])) { $sql .= " AND r.asking_price <= :xp"; $params[':xp'] = $filters['max_price']; }
        if (!empty($filters['district_id'])) { $sql .= " AND r.district_id = :d"; $params[':d'] = $filters['district_id']; }
        $sql .= " ORDER BY r.created_at DESC LIMIT :lim OFFSET :off";
        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createProperty(array $data): array
    {
        $tid = $this->tenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';
        $required = ['user_id', 'title', 'property_type', 'asking_price', 'area_sqft'];
        foreach ($required as $f) if (empty($data[$f])) return ['error' => "Missing field: $f"];

        $st = $this->pdo->prepare("INSERT INTO resell_properties (user_id, title, description, property_type, asking_price, original_price, area_sqft, bedrooms, bathrooms, location, district_id, age_years, amenities, status, created_at{$extraCol})
                                VALUES (:u, :t, :d, :pt, :p, :op, :a, :br, :ba, :loc, :di, :age, :am, 'active', NOW(){$extraVal})");
        $params = [
            ':u' => $data['user_id'], ':t' => $data['title'], ':d' => $data['description'] ?? '',
            ':pt' => $data['property_type'], ':p' => $data['asking_price'],
            ':op' => $data['original_price'] ?? $data['asking_price'],
            ':a' => $data['area_sqft'], ':br' => $data['bedrooms'] ?? 0,
            ':ba' => $data['bathrooms'] ?? 0, ':loc' => $data['location'] ?? $data['address'] ?? '',
            ':di' => $data['district_id'] ?? null, ':age' => $data['age_years'] ?? 0,
            ':am' => isset($data['amenities']) ? json_encode($data['amenities']) : null
        ];
        if ($tid > 1) $params[':tid'] = $tid;
        $st->execute($params);
        $id = (int)$this->pdo->lastInsertId();
        return ['ok' => true, 'id' => $id];
    }

    public function getProperty(int $id): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT r.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email, d.name as district_name
                               FROM resell_properties r
                               LEFT JOIN users u ON r.user_id = u.id
                               LEFT JOIN districts d ON r.district_id = d.id
                               WHERE r.id = :id" . ($tid > 1 ? " AND r.tenant_id = ?" : "");
        $params = [':id' => $id];
        if ($tid > 1) $params[] = $tid;
        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) { if (is_int($k)) { $st->execute(array_values($params)); break; } else $st->bindValue($k, $v); }
        if ($tid > 1) $st->execute(array_values($params));
        else $st->execute(array_values($params));
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;

        try {
            $sql2 = "SELECT * FROM resell_property_images WHERE property_id = :id" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY sort_order";
            $st2 = $this->pdo->prepare($sql2);
            $st2->bindValue(':id', $id);
            if ($tid > 1) $st2->execute([$id, $tid]);
            else $st2->execute([$id]);
            $r['images'] = $st2->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { $r['images'] = []; }

        try {
            $sql3 = "SELECT * FROM property_ai_tags WHERE property_id = :id" . ($tid > 1 ? " AND tenant_id = ?" : "");
            $st3 = $this->pdo->prepare($sql3);
            $st3->bindValue(':id', $id);
            if ($tid > 1) $st3->execute([$id, $tid]);
            else $st3->execute([$id]);
            $r['ai_tags'] = $st3->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { $r['ai_tags'] = []; }

        return $r;
    }

    public function addImage(int $propertyId, string $imageUrl, int $sortOrder = 0): array
    {
        $tid = $this->tenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';
        $st = $this->pdo->prepare("INSERT INTO resell_property_images (property_id, image_url, sort_order, uploaded_at{$extraCol}) VALUES (:p, :i, :s, NOW(){$extraVal})");
        $params = [':p' => $propertyId, ':i' => $imageUrl, ':s' => $sortOrder];
        if ($tid > 1) $params[':tid'] = $tid;
        $st->execute($params);
        return ['ok' => true, 'id' => (int)$this->pdo->lastInsertId()];
    }

    public function recordValuation(int $propertyId, float $estimatedValue, string $source = 'manual', array $details = []): array
    {
        $tid = $this->tenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';
        $st = $this->pdo->prepare("INSERT INTO property_valuations (property_id, estimated_value, valuation_source, valuation_details, valid_until, created_at{$extraCol})
                                  VALUES (:p, :v, :s, :d, DATE_ADD(NOW(), INTERVAL 90 DAY), NOW(){$extraVal})");
        $params = [':p' => $propertyId, ':v' => $estimatedValue, ':s' => $source, ':d' => json_encode($details, JSON_UNESCAPED_UNICODE)];
        if ($tid > 1) $params[':tid'] = $tid;
        $st->execute($params);
        return ['ok' => true, 'id' => (int)$this->pdo->lastInsertId()];
    }

    public function getLatestValuation(int $propertyId): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM property_valuations WHERE property_id = :p AND (valid_until IS NULL OR valid_until > NOW())" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC LIMIT 1";
        $params = [':p' => $propertyId];
        if ($tid > 1) $params[] = $tid;
        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) { if (is_int($k)) { $st->execute(array_values($params)); break; } else $st->bindValue($k, $v); }
        if ($tid > 1) $st->execute(array_values($params));
        else $st->execute([$propertyId]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function tagProperty(int $propertyId, string $tag, float $confidence = 1.0, string $source = 'ai'): array
    {
        $tid = $this->tenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';
        $st = $this->pdo->prepare("INSERT INTO property_ai_tags (property_id, tag, confidence, source, created_at{$extraCol}) VALUES (:p, :t, :c, :s, NOW(){$extraVal})
                                  ON DUPLICATE KEY UPDATE confidence = VALUES(confidence), source = VALUES(source)");
        $params = [':p' => $propertyId, ':t' => $tag, ':c' => $confidence, ':s' => $source];
        if ($tid > 1) $params[':tid'] = $tid;
        $st->execute($params);
        return ['ok' => true];
    }

    public function recordAnalytics(int $propertyId, string $event, array $data = []): array
    {
        $tid = $this->tenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';
        $st = $this->pdo->prepare("INSERT INTO property_analytics (property_id, event_type, event_data, ip_address, user_agent, created_at{$extraCol}) VALUES (:p, :e, :d, :ip, :ua, NOW(){$extraVal})");
        $params = [':p' => $propertyId, ':e' => $event, ':d' => json_encode($data, JSON_UNESCAPED_UNICODE), ':ip' => $_SERVER['REMOTE_ADDR'] ?? '', ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''];
        if ($tid > 1) $params[':tid'] = $tid;
        $st->execute($params);
        return ['ok' => true];
    }

    public function getAnalytics(int $propertyId, int $days = 30): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT event_type, COUNT(*) as count, DATE(created_at) as day FROM property_analytics WHERE property_id = :p AND created_at > DATE_SUB(NOW(), INTERVAL :days DAY)" . ($tid > 1 ? " AND tenant_id = ?" : "") . " GROUP BY event_type, day ORDER BY day DESC";
        $params = [':p' => $propertyId, ':days' => $days];
        if ($tid > 1) $params[] = $tid;
        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) { if (is_int($k)) { $st->execute(array_values($params)); break; } else $st->bindValue($k, $v); }
        if ($tid > 1) $st->execute(array_values($params));
        else $st->execute(array_values($params));
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMarketData(int $districtId = 0, int $propertyType = 0, int $days = 90): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM property_market_data WHERE 1=1";
        $params = [];
        if ($districtId) { $sql .= " AND district_id = :d"; $params[':d'] = $districtId; }
        if ($propertyType) { $sql .= " AND property_type = :pt"; $params[':pt'] = $propertyType; }
        $sql .= " AND created_at > DATE_SUB(NOW(), INTERVAL :days DAY) ORDER BY created_at DESC LIMIT 50";
        $params[':days'] = $days;
        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMarketData(int $districtId, int $propertyType, float $avgPrice, int $totalListings, int $totalSold, float $priceChangePct = 0): array
    {
        $tid = $this->tenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';
        $st = $this->pdo->prepare("INSERT INTO property_market_data (district_id, property_type, avg_price, total_listings, total_sold, price_change_pct, created_at{$extraCol}) VALUES (:d, :pt, :ap, :tl, :ts, :pc, NOW(){$extraVal})");
        $params = [':d' => $districtId, ':pt' => $propertyType, ':ap' => $avgPrice, ':tl' => $totalListings, ':ts' => $totalSold, ':pc' => $priceChangePct];
        if ($tid > 1) $params[':tid'] = $tid;
        $st->execute($params);
        return ['ok' => true, 'id' => (int)$this->pdo->lastInsertId()];
    }

    public function getCommissionStructure(): array
    {
        try {
            $st = $this->pdo->query("SELECT * FROM resell_commission_structure WHERE active = 1 ORDER BY min_price ASC");
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { error_log('Silent catch: ' . $e->getMessage()); return []; }
    }
}
