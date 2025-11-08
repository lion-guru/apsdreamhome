<?php
/**
 * Property Favorite Model
 * Handles property favorites/favourites operations
 */

namespace App\Models;

class PropertyFavorite extends Model {
    protected static string $table = 'property_favorites';

    /**
     * Add property to favorites
     */
    public function addFavorite($user_id, $property_id) {
        try {
            // Check if already exists
            if ($this->isFavorited($user_id, $property_id)) {
                return false; // Already favorited
            }

            $sql = "INSERT INTO {$this->table} (user_id, property_id, created_at) VALUES (?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$user_id, $property_id]);

        } catch (\Exception $e) {
            error_log('Add favorite error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove property from favorites
     */
    public function removeFavorite($user_id, $property_id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE user_id = ? AND property_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$user_id, $property_id]);

        } catch (\Exception $e) {
            error_log('Remove favorite error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if property is favorited by user
     */
    public function isFavorited($user_id, $property_id) {
        try {
            $sql = "SELECT id FROM {$this->table} WHERE user_id = ? AND property_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id, $property_id]);

            return $stmt->rowCount() > 0;

        } catch (\Exception $e) {
            error_log('Check favorite error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's favorite properties
     */
    public function getUserFavorites($user_id, $limit = 20) {
        try {
            $sql = "SELECT f.*, p.title, p.price, p.city, p.state, p.area_sqft,
                           p.bedrooms, p.bathrooms, p.featured, p.status,
                           (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
                    FROM {$this->table} f
                    INNER JOIN properties p ON f.property_id = p.id
                    WHERE f.user_id = ? AND p.status = 'available'
                    ORDER BY f.created_at DESC LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id, $limit]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('User favorites fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get favorite count for property
     */
    public function getFavoriteCount($property_id) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE property_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$property_id]);

            return (int)$stmt->fetch()['count'];

        } catch (\Exception $e) {
            error_log('Favorite count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite($user_id, $property_id) {
        if ($this->isFavorited($user_id, $property_id)) {
            return $this->removeFavorite($user_id, $property_id);
        } else {
            return $this->addFavorite($user_id, $property_id);
        }
    }

    /**
     * Get popular properties (most favorited)
     */
    public function getPopularProperties($limit = 10) {
        try {
            $sql = "SELECT p.*, COUNT(f.id) as favorite_count,
                           (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
                    FROM properties p
                    LEFT JOIN {$this->table} f ON p.id = f.property_id
                    WHERE p.status = 'available'
                    GROUP BY p.id
                    ORDER BY favorite_count DESC, p.created_at DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Popular properties fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get favorites statistics
     */
    public function getStats() {
        try {
            $stats = [];

            // Total favorites
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
            $stats['total_favorites'] = (int)$stmt->fetch()['total'];

            // New favorites (last 30 days)
            $stmt = $this->db->query("SELECT COUNT(*) as new FROM {$this->table}
                                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats['new_favorites'] = (int)$stmt->fetch()['new'];

            // Users with favorites
            $stmt = $this->db->query("SELECT COUNT(DISTINCT user_id) as users FROM {$this->table}");
            $stats['users_with_favorites'] = (int)$stmt->fetch()['users'];

            // Most favorited properties
            $stmt = $this->db->query("SELECT property_id, COUNT(*) as count
                                     FROM {$this->table}
                                     GROUP BY property_id
                                     ORDER BY count DESC
                                     LIMIT 5");
            $stats['top_properties'] = $stmt->fetchAll();

            return $stats;

        } catch (\Exception $e) {
            error_log('Favorites stats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get favorites trends
     */
    public function getTrends($days = 30) {
        try {
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count
                    FROM {$this->table}
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$days]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Favorites trends error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Remove all favorites for a property (when property is deleted)
     */
    public function removeByProperty($property_id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE property_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$property_id]);

        } catch (\Exception $e) {
            error_log('Remove property favorites error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove all favorites for a user (when user is deleted)
     */
    public function removeByUser($user_id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$user_id]);

        } catch (\Exception $e) {
            error_log('Remove user favorites error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get favorites for API response
     */
    public function getForAPI($user_id) {
        try {
            $sql = "SELECT p.id, p.title, p.price, p.city, p.state, p.area_sqft,
                           p.bedrooms, p.bathrooms, p.featured,
                           (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as image
                    FROM {$this->table} f
                    INNER JOIN properties p ON f.property_id = p.id
                    WHERE f.user_id = ? AND p.status = 'available'
                    ORDER BY f.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('API favorites fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Bulk operations
     */
    public function bulkAddFavorites($user_id, $property_ids) {
        try {
            if (empty($property_ids)) {
                return false;
            }

            $placeholders = str_repeat('?,', count($property_ids) - 1) . '?';
            $sql = "INSERT IGNORE INTO {$this->table} (user_id, property_id, created_at)
                    VALUES (?, ?, NOW())";

            $success_count = 0;
            foreach ($property_ids as $property_id) {
                $stmt = $this->db->prepare($sql);
                if ($stmt->execute([$user_id, $property_id])) {
                    $success_count++;
                }
            }

            return $success_count;

        } catch (\Exception $e) {
            error_log('Bulk add favorites error: ' . $e->getMessage());
            return 0;
        }
    }

    public function bulkRemoveFavorites($user_id, $property_ids) {
        try {
            if (empty($property_ids)) {
                return false;
            }

            $placeholders = str_repeat('?,', count($property_ids) - 1) . '?';
            $sql = "DELETE FROM {$this->table}
                    WHERE user_id = ? AND property_id IN ({$placeholders})";

            $params = array_merge([$user_id], $property_ids);
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (\Exception $e) {
            error_log('Bulk remove favorites error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user favorite statistics
     */
    public function getUserStats($user_id) {
        try {
            $sql = "SELECT
                        COUNT(*) as total_favorites,
                        COUNT(CASE WHEN p.status = 'available' THEN 1 END) as active_favorites,
                        COUNT(CASE WHEN p.status != 'available' THEN 1 END) as inactive_favorites,
                        MIN(f.created_at) as first_favorite_date,
                        MAX(f.created_at) as last_favorite_date
                    FROM {$this->table} f
                    LEFT JOIN properties p ON f.property_id = p.id
                    WHERE f.user_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id]);

            return $stmt->fetch();

        } catch (\Exception $e) {
            error_log('User favorites stats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property favorite statistics
     */
    public function getPropertyStats($property_id) {
        try {
            $sql = "SELECT
                        COUNT(*) as total_favorites,
                        COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_user_favorites,
                        MIN(f.created_at) as first_favorite_date,
                        MAX(f.created_at) as last_favorite_date
                    FROM {$this->table} f
                    LEFT JOIN users u ON f.user_id = u.id
                    WHERE f.property_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$property_id]);

            return $stmt->fetch();

        } catch (\Exception $e) {
            error_log('Property favorites stats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Export favorites
     */
    public function export($format = 'csv', $user_id = null) {
        try {
            $sql = "SELECT f.*, p.title as property_title, p.city, p.state, p.price,
                           u.name as user_name, u.email as user_email
                    FROM {$this->table} f
                    LEFT JOIN properties p ON f.property_id = p.id
                    LEFT JOIN users u ON f.user_id = u.id";

            if ($user_id) {
                $sql .= " WHERE f.user_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$user_id]);
            } else {
                $stmt = $this->db->query($sql);
            }

            $data = $stmt->fetchAll();

            if ($format === 'csv') {
                return generateCSVReport($data);
            } elseif ($format === 'json') {
                return json_encode($data, JSON_PRETTY_PRINT);
            }

            return $data;

        } catch (\Exception $e) {
            error_log('Favorites export error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent favorites for admin dashboard
     */
    public function getRecent($limit = 20) {
        try {
            $sql = "SELECT f.*, p.title as property_title, p.city, p.state,
                           u.name as user_name, u.email as user_email
                    FROM {$this->table} f
                    LEFT JOIN properties p ON f.property_id = p.id
                    LEFT JOIN users u ON f.user_id = u.id
                    ORDER BY f.created_at DESC LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Recent favorites fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search favorites
     */
    public function search($search_term, $limit = 50) {
        try {
            $sql = "SELECT f.*, p.title as property_title, p.city, p.state,
                           u.name as user_name, u.email as user_email
                    FROM {$this->table} f
                    LEFT JOIN properties p ON f.property_id = p.id
                    LEFT JOIN users u ON f.user_id = u.id
                    WHERE p.title LIKE ?
                       OR p.city LIKE ?
                       OR p.state LIKE ?
                       OR u.name LIKE ?
                       OR u.email LIKE ?
                    ORDER BY f.created_at DESC LIMIT ?";

            $search_pattern = "%{$search_term}%";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$search_pattern, $search_pattern, $search_pattern, $search_pattern, $search_pattern, $limit]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Favorites search error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get favorites by date range
     */
    public function getByDateRange($start_date, $end_date, $user_id = null) {
        try {
            $sql = "SELECT f.*, p.title as property_title, p.city, p.state,
                           u.name as user_name, u.email as user_email
                    FROM {$this->table} f
                    LEFT JOIN properties p ON f.property_id = p.id
                    LEFT JOIN users u ON f.user_id = u.id
                    WHERE f.created_at BETWEEN ? AND ?";

            $params = [$start_date, $end_date];

            if ($user_id) {
                $sql .= " AND f.user_id = ?";
                $params[] = $user_id;
            }

            $sql .= " ORDER BY f.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Favorites date range fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get favorite activity for analytics
     */
    public function getActivityData($days = 30) {
        try {
            $sql = "SELECT
                        DATE(f.created_at) as date,
                        COUNT(*) as favorites_added,
                        COUNT(DISTINCT f.user_id) as unique_users,
                        COUNT(DISTINCT f.property_id) as unique_properties
                    FROM {$this->table} f
                    WHERE f.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(f.created_at)
                    ORDER BY date";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$days]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Favorites activity data error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top favorited properties by location
     */
    public function getTopByLocation($city = null, $limit = 10) {
        try {
            $sql = "SELECT p.city, p.state, COUNT(f.id) as favorite_count,
                           AVG(p.price) as avg_price
                    FROM {$this->table} f
                    INNER JOIN properties p ON f.property_id = p.id
                    WHERE p.status = 'available'";

            $params = [];

            if ($city) {
                $sql .= " AND p.city = ?";
                $params[] = $city;
            }

            $sql .= " GROUP BY p.city, p.state
                      ORDER BY favorite_count DESC
                      LIMIT ?";

            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Top favorites by location error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get favorite conversion rate (inquiries from favorites)
     */
    public function getConversionRate($days = 30) {
        try {
            $sql = "SELECT
                        COUNT(DISTINCT f.id) as total_favorites,
                        COUNT(DISTINCT i.id) as inquiries_from_favorites,
                        ROUND((COUNT(DISTINCT i.id) / COUNT(DISTINCT f.id)) * 100, 2) as conversion_rate
                    FROM {$this->table} f
                    LEFT JOIN property_inquiries i ON f.property_id = i.property_id
                        AND i.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                        AND i.created_at >= f.created_at
                    WHERE f.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$days, $days]);

            return $stmt->fetch();

        } catch (\Exception $e) {
            error_log('Favorite conversion rate error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean up orphaned favorites (properties that no longer exist)
     */
    public function cleanupOrphaned() {
        try {
            $sql = "DELETE f FROM {$this->table} f
                    LEFT JOIN properties p ON f.property_id = p.id
                    WHERE p.id IS NULL";

            $stmt = $this->db->prepare($sql);
            $deleted = $stmt->execute();

            return $stmt->rowCount();

        } catch (\Exception $e) {
            error_log('Orphaned favorites cleanup error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get favorites for mobile API
     */
    public function getForMobileAPI($user_id) {
        try {
            $sql = "SELECT p.id, p.title, p.price, p.city, p.state, p.area_sqft,
                           p.bedrooms, p.bathrooms, p.featured,
                           (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as image,
                           f.created_at as favorited_at
                    FROM {$this->table} f
                    INNER JOIN properties p ON f.property_id = p.id
                    WHERE f.user_id = ? AND p.status = 'available'
                    ORDER BY f.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Mobile API favorites fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get favorite recommendations based on user preferences
     */
    public function getRecommendations($user_id, $limit = 10) {
        try {
            // Get user's favorite property types and locations
            $sql = "SELECT p.property_type, p.city, p.state, COUNT(*) as count
                    FROM {$this->table} f
                    INNER JOIN properties p ON f.property_id = p.id
                    WHERE f.user_id = ?
                    GROUP BY p.property_type, p.city, p.state
                    ORDER BY count DESC
                    LIMIT 5";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id]);
            $preferences = $stmt->fetchAll();

            if (empty($preferences)) {
                // Return popular properties if no preferences
                return $this->getPopularProperties($limit);
            }

            // Build recommendation query based on preferences
            $conditions = [];
            $params = [];

            foreach ($preferences as $pref) {
                $conditions[] = "(p.property_type = ? AND p.city = ? AND p.state = ?)";
                $params[] = $pref['property_type'];
                $params[] = $pref['city'];
                $params[] = $pref['state'];
            }

            $where_clause = implode(' OR ', $conditions);

            $sql = "SELECT p.*, pf.count as preference_score,
                           (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
                    FROM properties p
                    LEFT JOIN (
                        SELECT property_id, COUNT(*) as count
                        FROM {$this->table}
                        WHERE user_id = ?
                        GROUP BY property_id
                    ) pf ON p.id = pf.property_id
                    WHERE p.status = 'available'
                      AND p.id NOT IN (SELECT property_id FROM {$this->table} WHERE user_id = ?)
                      AND ({$where_clause})
                    ORDER BY pf.count DESC, p.created_at DESC
                    LIMIT ?";

            $params[] = $user_id;
            $params[] = $user_id;
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Favorite recommendations error: ' . $e->getMessage());
            return [];
        }
    }
}
