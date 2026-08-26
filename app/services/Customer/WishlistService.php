<?php

namespace App\Services\Customer;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

/**
 * Wishlist & Favorites Service
 * Customer property wishlist and saved searches management
 */
class WishlistService
{
    use ServiceTenantTrait;

    private $database;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Wishlist table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Wishlist collections/folders
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Collection items
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Price alerts
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Recently viewed properties
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Add property to wishlist
     */
    public function addToWishlist(int $userId, int $propertyId, array $data = []): array
    {
        try {
            $tenantData = $this->tenantInsertData();
            $columns = array_merge(['user_id', 'property_id', 'notes', 'priority'], array_keys($tenantData));
            $values = array_merge([$userId, $propertyId, $data['notes'] ?? null, $data['priority'] ?? 'medium'], array_values($tenantData));
            $placeholders = implode(',', array_fill(0, count($values), '?'));
            $sql = "INSERT INTO wishlists ({$columns}) 
                    VALUES ({$placeholders})
                    ON DUPLICATE KEY UPDATE 
                    notes = VALUES(notes), priority = VALUES(priority), updated_at = NOW()";
            
            return [
                'success' => true,
                'message' => 'Property added to wishlist',
                'wishlist_id' => $this->database->lastInsertId()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Remove from wishlist
     */
    public function removeFromWishlist(int $userId, int $propertyId): array
    {
        $sql = "DELETE FROM wishlists WHERE user_id = ? AND property_id = ?" . $this->tenantSql();
        $stmt = $this->database->prepare($sql);
        $params = [$userId, $propertyId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        
        return [
            'success' => $stmt->rowCount() > 0,
            'message' => $stmt->rowCount() > 0 ? 'Removed from wishlist' : 'Item not found'
        ];
    }
    
    /**
     * Get user's wishlist
     */
    public function getWishlist(int $userId, array $options = []): array
    {
$sql = "SELECT w.*, p.*, pi.image_path as primary_image,
             (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as total_images,
             (SELECT AVG(rating) FROM property_reviews WHERE property_id = p.id) as avg_rating
             FROM wishlists w
             JOIN properties p ON w.property_id = p.id
             LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
             WHERE w.user_id = ? AND w.tenant_id = ? AND p.status = 'available'
             ORDER BY w.updated_at DESC";
         
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $this->tenantId()]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if property is in wishlist
     */
    public function isInWishlist(int $userId, int $propertyId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM wishlists WHERE user_id = ? AND property_id = ? AND tenant_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $propertyId, $this->tenantId()]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'] > 0;
    }
    
    /**
     * Get wishlist statistics
     */
    public function getWishlistStats(int $userId): array
    {
$sql = "SELECT 
            COUNT(*) as total_items,
            SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority,
            SUM(CASE WHEN priority = 'medium' THEN 1 ELSE 0 END) as medium_priority,
            SUM(CASE WHEN priority = 'low' THEN 1 ELSE 0 END) as low_priority,
            AVG(p.price) as avg_price,
            MAX(p.price) as max_price,
            MIN(p.price) as min_price
            FROM wishlists w
            JOIN properties p ON w.property_id = p.id
            WHERE w.user_id = ? AND w.tenant_id = ? AND p.status = 'available'";
         
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $this->tenantId()]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Track recently viewed
     */
    public function trackView(int $userId, int $propertyId): void
    {
$tenantData = $this->tenantInsertData();
        $columns = array_merge(['user_id', 'property_id', 'view_count'], array_keys($tenantData));
        $values = array_merge([$userId, $propertyId, 1], array_values($tenantData));
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $sql = "INSERT INTO recently_viewed ({$columns}) 
                VALUES ({$placeholders})
                ON DUPLICATE KEY UPDATE 
                view_count = view_count + 1, last_viewed = NOW()";
         
        $stmt = $this->database->prepare($sql);
        $stmt->execute($values);
    }
    
    /**
     * Get recently viewed
     */
    public function getRecentlyViewed(int $userId, int $limit = 10): array
    {
$sql = "SELECT rv.*, p.*, pi.image_path as primary_image
            FROM recently_viewed rv
            JOIN properties p ON rv.property_id = p.id
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            WHERE rv.user_id = ? AND rv.tenant_id = ? AND p.status = 'available'
            ORDER BY rv.last_viewed DESC
            LIMIT ?";
         
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $this->tenantId(), $limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create price alert
     */
    public function createPriceAlert(int $userId, int $propertyId, float $targetPrice, string $type = 'below'): array
    {
        try {
            $tenantData = $this->tenantInsertData();
        $columns = array_merge(['user_id', 'property_id', 'target_price', 'alert_type'], array_keys($tenantData));
        $values = array_merge([$userId, $propertyId, $targetPrice, $type], array_values($tenantData));
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $sql = "INSERT INTO price_alerts ({$columns}) VALUES ({$placeholders})";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([$userId, $propertyId, $targetPrice, $type]);
            
            return [
                'success' => true,
                'alert_id' => $this->database->lastInsertId(),
                'message' => "You'll be notified when price drops to â‚¹" . number_format($targetPrice)
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get price alerts
     */
    public function getPriceAlerts(int $userId): array
    {
$sql = "SELECT pa.*, p.title, p.price as current_price, pi.image_path as primary_image
            FROM price_alerts pa
            JOIN properties p ON pa.property_id = p.id
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            WHERE pa.user_id = ? AND pa.tenant_id = ? AND pa.is_active = 1
            ORDER BY pa.created_at DESC";
         
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $this->tenantId()]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Check and trigger price alerts
     */
    public function checkPriceAlerts(): array
    {
        $triggered = [];
        
        // Find triggered alerts
$sql = "SELECT pa.*, p.price as current_price
            FROM price_alerts pa
            JOIN properties p ON pa.property_id = p.id
            WHERE pa.is_active = 1 AND pa.tenant_id = ? AND pa.triggered_at IS NULL
            AND (
                (pa.alert_type = 'below' AND p.price <= pa.target_price)
                OR (pa.alert_type = 'above' AND p.price >= pa.target_price)
            )";
         
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$this->tenantId()]);
        $alerts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($alerts as $alert) {
            // Mark as triggered
            $updateSql = "UPDATE price_alerts SET triggered_at = NOW() WHERE id = ? AND tenant_id = ?";
            $updateStmt = $this->database->prepare($updateSql);
            $updateStmt->execute([$alert['id'], $this->tenantId()]);
            
            $triggered[] = [
                'alert_id' => $alert['id'],
                'user_id' => $alert['user_id'],
                'property_id' => $alert['property_id'],
                'target_price' => $alert['target_price'],
                'current_price' => $alert['current_price']
            ];
        }
        
        return $triggered;
    }
    
    /**
     * Compare multiple properties
     */
    public function compareProperties(array $propertyIds): array
    {
        if (empty($propertyIds) || count($propertyIds) < 2) {
            return ['error' => 'Select at least 2 properties to compare'];
        }
        
        if (count($propertyIds) > 4) {
            $propertyIds = array_slice($propertyIds, 0, 4);
        }
        
        $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
        
        $tenantSql = $this->tenantSql();
        $tenantParams = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        
        $sql = "SELECT p.*, pi.image_path as primary_image,
            (SELECT AVG(rating) FROM property_reviews WHERE property_id = p.id) as avg_rating,
            (SELECT COUNT(*) FROM property_reviews WHERE property_id = p.id) as review_count
            FROM properties p
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            WHERE p.id IN ({$placeholders}) AND p.tenant_id = ? {$tenantSql}";
        
        $params = array_merge($propertyIds, [$this->tenantId()], $tenantParams);
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Build comparison matrix
        $comparison = [
            'properties' => $properties,
            'comparison_fields' => [
                'price' => array_column($properties, 'price'),
                'area' => array_column($properties, 'area'),
                'bedrooms' => array_column($properties, 'bedrooms'),
                'bathrooms' => array_column($properties, 'bathrooms'),
                'furnishing_status' => array_column($properties, 'furnishing_status'),
                'amenities' => array_map(fn($p) => json_decode($p['amenities'] ?? '[]', true), $properties),
                'rating' => array_column($properties, 'avg_rating')
            ],
            'winner' => $this->determineWinner($properties)
        ];
        
        return $comparison;
    }
    
    /**
     * Determine best property based on criteria
     */
    private function determineWinner(array $properties): ?int
    {
        if (empty($properties)) return null;
        
        $scores = [];
        
        foreach ($properties as $index => $property) {
            $score = 0;
            
            // Price score (lower is better)
            $minPrice = min(array_column($properties, 'price'));
            if ($property['price'] == $minPrice) $score += 2;
            
            // Area score (higher is better)
            $maxArea = max(array_column($properties, 'area'));
            if ($property['area'] == $maxArea) $score += 2;
            
            // Rating score
            $maxRating = max(array_column($properties, 'avg_rating'));
            if ($property['avg_rating'] == $maxRating) $score += 1;
            
            $scores[$index] = $score;
        }
        
        arsort($scores);
        return $properties[array_key_first($scores)]['id'] ?? null;
    }
    
    /**
     * Get wishlist recommendations
     */
    public function getRecommendations(int $userId, int $limit = 6): array
    {
        // Get user's wishlist property types and locations
$sql = "SELECT p.type, p.city, p.location AS locality
            FROM wishlists w
            JOIN properties p ON w.property_id = p.id
            WHERE w.user_id = ? AND w.tenant_id = ?
            GROUP BY p.type, p.city, p.location";
         
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $this->tenantId()]);
        $preferences = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($preferences)) {
            // Return trending properties
            return $this->getTrendingProperties($limit);
        }
        
        // Build recommendation query
        $where = ['p.status = ?', 'p.tenant_id = ?', 'p.id NOT IN (SELECT property_id FROM wishlists WHERE user_id = ?)'];
        $params = ['active', $this->tenantId(), $userId];
        
        $typeConditions = [];
        foreach ($preferences as $pref) {
            $typeConditions[] = "(p.type = ? AND (p.city = ? OR p.location = ?))";
            $params[] = $pref['type'];
            $params[] = $pref['city'];
            $params[] = $pref['locality'];
        }
        
        if (!empty($typeConditions)) {
            $where[] = '(' . implode(' OR ', $typeConditions) . ')';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT p.*, pi.image_path as primary_image,
            (SELECT AVG(rating) FROM property_reviews WHERE property_id = p.id) as avg_rating
            FROM properties p
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            WHERE {$whereClause} AND p.tenant_id = ?
            ORDER BY p.created_at DESC
            LIMIT ?";
        
        $params[] = $this->tenantId();
        $params[] = $limit;
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get trending properties
     */
    private function getTrendingProperties(int $limit): array
    {
$sql = "SELECT p.*, pi.image_path as primary_image,
            (SELECT AVG(rating) FROM property_reviews WHERE property_id = p.id) as avg_rating
            FROM properties p
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            WHERE p.status = 'active' AND p.tenant_id = ?
            ORDER BY p.created_at DESC
            LIMIT ?";
         
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$this->tenantId(), $limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
