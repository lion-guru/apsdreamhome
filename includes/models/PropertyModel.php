<?php
/**
 * Property Model
 * 
 * Handles all property-related database operations
 */
class PropertyModel {
    /** @var \mysqli */
    private $db_conn;
    
    /** @var \mysqli */
    private $db; // Alias for backward compatibility
    
    /**
     * Constructor
     * 
     * @param \mysqli $db_conn Database connection object
     */
    public function __construct($db_conn) {
        $this->db_conn = $db_conn;
        $this->db = $db_conn; // Set alias for backward compatibility
    }
    
    /**
     * Get featured properties
     * 
     * @param int $limit Number of properties to return
     * @return array Array of featured properties
     */
    public function getFeaturedProperties(int $limit = 9): array {
        try {
            $sql = "
                SELECT 
                    p.*, 
                    pt.name AS property_type_name,
                    pt.icon AS property_type_icon,
                    u.first_name, 
                    u.last_name,
                    u.phone AS agent_phone,
                    u.email AS agent_email,
                    (SELECT pi.image_url 
                     FROM property_images pi 
                     WHERE pi.property_id = p.id 
                     ORDER BY pi.is_primary DESC, pi.id ASC 
                     LIMIT 1) AS main_image,
                    (SELECT COUNT(*) 
                     FROM property_images pi2 
                     WHERE pi2.property_id = p.id) AS image_count,
                    (SELECT AVG(rating) 
                     FROM property_reviews pr 
                     WHERE pr.property_id = p.id 
                     AND pr.status = 'approved') AS average_rating,
                    (SELECT COUNT(*) 
                     FROM property_reviews pr2 
                     WHERE pr2.property_id = p.id 
                     AND pr2.status = 'approved') AS review_count
                FROM 
                    properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    LEFT JOIN users u ON p.agent_id = u.id
                WHERE 
                    p.status = 'available'
                    AND p.is_featured = 1
                    AND p.approved = 1
                ORDER BY 
                    p.featured_until DESC, 
                    p.created_at DESC
                LIMIT ?
            ";
            
            $stmt = $this->db_conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception('Prepare failed: ' . $this->db_conn->error);
            }
            
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $properties = [];
            
            while ($row = $result->fetch_assoc()) {
                $properties[] = $this->formatPropertyData($row);
            }
            $stmt->close();
            return $properties;
            
        } catch (Exception $e) {
            log_message('Error in getFeaturedProperties: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get property counts and statistics
     * 
     * @return array Array of property statistics
     */
    public function getPropertyCounts(): array {
        try {
            $stats = [
                'total_properties' => 0,
                'sold_properties' => 0,
                'happy_customers' => 0,
                'agents_count' => 0
            ];
            
            // Get total available properties
            $sql_total = "SELECT COUNT(*) as count FROM properties WHERE status = 'available' AND approved = 1";
            $result_total = $this->db_conn->query($sql_total);
            if ($result_total && $row_total = $result_total->fetch_assoc()) {
                $stats['total_properties'] = (int)$row_total['count'];
            }
            
            // Get sold properties
            $sql_sold = "SELECT COUNT(*) as count FROM properties WHERE status = 'sold'";
            $result_sold = $this->db_conn->query($sql_sold);
            if ($result_sold && $row_sold = $result_sold->fetch_assoc()) {
                $stats['sold_properties'] = (int)$row_sold['count'];
            }
            
            // Get happy customers (customers with successful transactions or positive reviews)
            // This is an example, adjust based on actual schema/logic for 'happy customers'
            $sql_happy = "
                SELECT COUNT(DISTINCT c.id) as count 
                FROM customers c
                JOIN property_visits pv ON c.id = pv.customer_id
                WHERE pv.rating >= 4
            "; // Assuming a rating of 4 or 5 means happy
            $result_happy = $this->db_conn->query($sql_happy);
            if ($result_happy && $row_happy = $result_happy->fetch_assoc()) {
                $stats['happy_customers'] = (int)$row_happy['count'];
            }
            
            // Get active agents
            $sql_agents = "SELECT COUNT(*) as count FROM users WHERE role = 'agent' AND status = 'active'";
            $result_agents = $this->db_conn->query($sql_agents);
            if ($result_agents && $row_agents = $result_agents->fetch_assoc()) {
                $stats['agents_count'] = (int)$row_agents['count'];
            }
            
            return $stats;
            
        } catch (Exception $e) {
            log_message('Error in getPropertyCounts: ' . $e->getMessage(), 'error');
            return [
                'total_properties' => 0,
                'sold_properties' => 0,
                'happy_customers' => 0,
                'agents_count' => 0
            ];
        }
    }
    
    /**
     * Format property data for display
     * 
     * @param array $property Raw property data from database
     * @return array Formatted property data
     */
    private function formatPropertyData(array $property): array {
        // Format price
        $price = (float)($property['price'] ?? 0);
        if ($price >= 10000000) {
            $formattedPrice = '₹' . number_format($price / 10000000, 2) . ' Cr';
        } elseif ($price >= 100000) {
            $formattedPrice = '₹' . number_format($price / 100000, 2) . ' L';
        } else {
            $formattedPrice = '₹' . number_format($price);
        }
        
        // Format area
        $area = (float)($property['area'] ?? 0);
        $areaUnit = $property['area_unit'] ?? 'sq.ft.';
        $formattedArea = number_format($area) . ' ' . e($areaUnit);
        
        // Format address
        $addressParts = array_filter([
            $property['address_line1'] ?? '',
            $property['address_line2'] ?? '',
            $property['landmark'] ?? '',
            $property['city'] ?? '',
            $property['state'] ?? '',
            $property['pincode'] ?? ''
        ]);
        $formattedAddress = implode(', ', array_map('e', $addressParts));
        
        // Format agent name
        $agentName = '';
        if (!empty($property['first_name']) || !empty($property['last_name'])) {
            $agentName = trim(e($property['first_name']) . ' ' . e($property['last_name']));
        }
        
        // Format amenities
        $amenities = [];
        if (!empty($property['amenities'])) {
            $amenities_json = is_string($property['amenities']) ? $property['amenities'] : '[]';
            $decoded_amenities = json_decode($amenities_json, true);
            $amenities = is_array($decoded_amenities) ? array_map('e', $decoded_amenities) : [];
        }
        
        // Format property features
        $features = [];
        if (!empty($property['bedrooms'])) $features[] = e($property['bedrooms']) . ' Beds';
        if (!empty($property['bathrooms'])) $features[] = e($property['bathrooms']) . ' Baths';
        if (!empty($property['balconies'])) $features[] = e($property['balconies']) . ' Balconies';
        if (!empty($property['total_floors'])) $features[] = e($property['total_floors']) . ' Floors'; // Assuming 'total_floors' field
        
        // Format property status
        $status = $property['status'] ?? 'available';
        $statusClass = '';
        switch (strtolower($status)) {
            case 'sold': $statusClass = 'sold'; break;
            case 'pending': $statusClass = 'pending'; break;
            case 'rented': $statusClass = 'rented'; break;
            case 'available':
            default: $statusClass = 'available'; break;
        }
        
        // Format created_at
        $createdAt = !empty($property['created_at']) 
            ? date('M d, Y', strtotime($property['created_at'])) 
            : '';
        
        // Format updated_at
        $updatedAt = !empty($property['updated_at']) 
            ? date('M d, Y', strtotime($property['updated_at'])) 
            : '';
        
        $slug = $this->createSlug($property['title'] ?? 'untitled-property');
        $property_id = (int)($property['id'] ?? 0);

        // Return formatted data
        return [
            'id' => $property_id,
            'title' => e($property['title'] ?? 'Untitled Property'),
            'slug' => $slug,
            'description' => e($property['description'] ?? ''),
            'price' => $price,
            'formatted_price' => $formattedPrice,
            'area' => $area,
            'formatted_area' => $formattedArea,
            'address' => $formattedAddress,
            'address_line1' => e($property['address_line1'] ?? ''),
            'address_line2' => e($property['address_line2'] ?? ''),
            'landmark' => e($property['landmark'] ?? ''),
            'city' => e($property['city'] ?? ''),
            'state' => e($property['state'] ?? ''),
            'pincode' => e($property['pincode'] ?? ''),
            'latitude' => $property['latitude'] ?? null,
            'longitude' => $property['longitude'] ?? null,
            'bedrooms' => (int)($property['bedrooms'] ?? 0),
            'bathrooms' => (int)($property['bathrooms'] ?? 0),
            'balconies' => (int)($property['balconies'] ?? 0),
            'total_floors' => (int)($property['total_floors'] ?? 1),
            'built_year' => $property['built_year'] ?? null,
            'parking_slots' => (int)($property['parking_slots'] ?? 0), // Assuming 'parking_slots'
            'furnishing_status' => e($property['furnishing_status'] ?? 'unfurnished'), // Assuming 'furnishing_status'
            'facing_direction' => e($property['facing_direction'] ?? ''), // Assuming 'facing_direction'
            'property_type_id' => (int)($property['property_type_id'] ?? 0),
            'property_type' => e($property['property_type_name'] ?? 'Unknown'),
            'property_type_icon' => e($property['property_type_icon'] ?? 'home'),
            'agent_id' => (int)($property['agent_id'] ?? 0),
            'agent_name' => $agentName,
            'agent_phone' => e($property['agent_phone'] ?? ''),
            'agent_email' => e($property['agent_email'] ?? ''),
            'main_image' => e($property['main_image'] ?? '/assets/images/property-placeholder.jpg'),
            'image_count' => (int)($property['image_count'] ?? 0),
            'average_rating' => $property['average_rating'] !== null ? (float)$property['average_rating'] : null,
            'review_count' => (int)($property['review_count'] ?? 0),
            'amenities' => $amenities,
            'features' => $features,
            'status' => e($status),
            'status_class' => e($statusClass),
            'is_featured' => !empty($property['is_featured']),
            'featured_until' => $property['featured_until'] ?? null,
            'created_at' => $property['created_at'] ?? null,
            'updated_at' => $property['updated_at'] ?? null,
            'formatted_created_at' => $createdAt,
            'formatted_updated_at' => $updatedAt,
            'url' => SITE_URL . '/property-details.php?id=' . $property_id . '&slug=' . $slug // Updated URL structure
        ];
    }
    
    /**
     * Format price for display
     * 
     * @param float $price The price to format
     * @param string $currency_symbol The currency symbol to use (default: ₹)
     * @return string The formatted price
     */
    private function formatPrice($price, $currency_symbol = '₹') {
        if (!is_numeric($price) || $price == 0) {
            return 'Contact for Price';
        }
        // Format for lakhs and crores if applicable
        if ($price >= 10000000) { // 1 Crore+
            return $currency_symbol . number_format($price / 10000000, 2) . ' Cr';
        } elseif ($price >= 100000) { // 1 Lakh+
            return $currency_symbol . number_format($price / 100000, 2) . ' Lac';
        }
        return $currency_symbol . number_format($price);
    }

    /**
     * Create a URL-friendly slug from a string
     * 
     * @param string $string The string to convert to a slug
     * @return string The generated slug
     */
    private function createSlug(string $string): string {
        // Replace non-letter or non-digit characters with -
        $string = preg_replace('~[^\pL\d]+~u', '-', $string);
        // Transliterate
        $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
        // Remove unwanted characters that are not alphanumeric or dash
        $string = preg_replace('~[^-\w]+~', '', $string);
        // Trim dashes from beginning and end
        $string = trim($string, '-');
        // Convert to lowercase
        $string = strtolower($string);
        // Remove duplicate dashes
        $string = preg_replace('~-+~', '-', $string);
        
        if (empty($string)) {
            return 'n-a'; // Not applicable / Not available
        }
        
        return $string;
    }

    /**
     * Fetches comprehensive details for a single property by its ID.
     * Includes main data, type, agent, gallery, features, and amenities.
     *
     * @param int $property_id The ID of the property to fetch.
     * @return array|null An associative array of property details, or null if not found or error.
     */
    public function getPropertyDetailsById(int $property_id): ?array
    {
        if ($property_id <= 0) {
            error_log("PropertyModel::getPropertyDetailsById - Invalid property ID: " . $property_id);
            return null;
        }

        $property = null;

        // Main property query
        $sql = "SELECT 
                    p.*, 
                    pt.name as property_type_name,
                    pt.slug as property_type_slug,
                    l.name as locality_name,
                    l.slug as locality_slug,
                    ci.name as city_name,
                    ci.slug as city_slug,
                    CONCAT(u.first_name, ' ', u.last_name) as agent_name,
                    u.email as agent_email,
                    u.phone as agent_phone,
                    u.profile_photo as agent_photo,
                    u.slug as agent_slug,
                    ps.name as property_status_name,
                    ps.slug as property_status_slug,
                    ps.css_class as property_status_css_class
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN localities l ON p.locality_id = l.id
                LEFT JOIN cities ci ON l.city_id = ci.id
                LEFT JOIN users u ON p.agent_id = u.id
                LEFT JOIN property_statuses ps ON p.property_status_id = ps.id
                WHERE p.id = ? AND p.is_active = 1 AND p.is_approved = 1";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("PropertyModel::getPropertyDetailsById - Prepare failed (main): " . $this->db->error);
            return null;
        }
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $main_details = $result->fetch_assoc()) {
            $property = $main_details;

            $property['formatted_price'] = $this->formatPrice($property['price']);
            $property['price_per_sqft'] = ($property['area'] > 0 && $property['price'] > 0) ? $this->formatPrice(round($property['price'] / $property['area'])) . '/sq.ft.' : 'N/A';
            $property['formatted_area'] = (!empty($property['area']) ? number_format($property['area']) : 'N/A') . ' sq.ft.';
            $property['listed_date_formatted'] = !empty($property['listed_date']) ? date('M d, Y', strtotime($property['listed_date'])) : 'N/A';
            $property['updated_at_formatted'] = !empty($property['updated_at']) ? date('M d, Y', strtotime($property['updated_at'])) : 'N/A';
            $property['url'] = SITE_URL . '/property/' . ($property['property_type_slug'] ?? 'property') . '/' . ($property['slug'] ?? $property_id);

            // Fetch Gallery Images
            $property['gallery_images'] = [];
            $gallery_sql = "SELECT image_url, caption, is_primary FROM property_images WHERE property_id = ? ORDER BY display_order ASC, is_primary DESC, id ASC";
            $gallery_stmt = $this->db->prepare($gallery_sql);
            if ($gallery_stmt) {
                $gallery_stmt->bind_param("i", $property_id);
                $gallery_stmt->execute();
                $gallery_result = $gallery_stmt->get_result();
                while ($img_row = $gallery_result->fetch_assoc()) {
                    $property['gallery_images'][] = [
                        'url' => SITE_URL . '/' . ltrim($img_row['image_url'], '/'),
                        'caption' => $img_row['caption'],
                        'is_primary' => (bool)$img_row['is_primary']
                    ];
                }
                $gallery_stmt->close();
            } else {
                error_log("PropertyModel::getPropertyDetailsById - Prepare failed (gallery): " . $this->db->error);
            }
            if (empty($property['gallery_images']) && !empty($property['featured_image'])) {
                 $property['gallery_images'][] = ['url' => SITE_URL . '/' . ltrim($property['featured_image'], '/'), 'caption' => $property['title'], 'is_primary' => true];
            } elseif (empty($property['gallery_images'])) {
                 $property['gallery_images'][] = ['url' => SITE_URL . '/assets/images/property-placeholder.jpg', 'caption' => 'Placeholder', 'is_primary' => true];
            }
            $property['main_image'] = $property['featured_image'] ? SITE_URL . '/' . ltrim($property['featured_image'], '/') : $property['gallery_images'][0]['url'];

            // Fetch Features
            $property['features'] = [];
            $features_sql = "SELECT f.name, f.icon_class as icon FROM property_features pf JOIN features f ON pf.feature_id = f.id WHERE pf.property_id = ?";
            $features_stmt = $this->db->prepare($features_sql);
            if ($features_stmt) {
                $features_stmt->bind_param("i", $property_id);
                $features_stmt->execute();
                $features_result = $features_stmt->get_result();
                while ($feat_row = $features_result->fetch_assoc()) {
                    $property['features'][] = $feat_row;
                }
                $features_stmt->close();
            } else {
                error_log("PropertyModel::getPropertyDetailsById - Prepare failed (features): " . $this->db->error);
            }

            // Fetch Amenities
            $property['amenities'] = [];
            $amenities_sql = "SELECT a.name, a.icon_class as icon FROM property_amenities pa JOIN amenities a ON pa.amenity_id = a.id WHERE pa.property_id = ?";
            $amenities_stmt = $this->db->prepare($amenities_sql);
            if ($amenities_stmt) {
                $amenities_stmt->bind_param("i", $property_id);
                $amenities_stmt->execute();
                $amenities_result = $amenities_stmt->get_result();
                while ($amen_row = $amenities_result->fetch_assoc()) {
                    $property['amenities'][] = $amen_row;
                }
                $amenities_stmt->close();
            } else {
                error_log("PropertyModel::getPropertyDetailsById - Prepare failed (amenities): " . $this->db->error);
            }
            
            $property['similar_properties'] = $this->getSimilarProperties($property_id, $property['property_type_id'] ?? null, $property['locality_id'] ?? null, 4);

        }
        $stmt->close();
        return $property;
    }

    /**
     * Fetches similar properties based on type, locality, excluding the current property.
     *
     * @param int $current_property_id The ID of the current property to exclude.
     * @param int|null $type_id The property type ID to match.
     * @param int|null $locality_id The locality ID to match.
     * @param int $limit Max number of similar properties to fetch.
     * @return array
     */
    public function getSimilarProperties(int $current_property_id, ?int $type_id, ?int $locality_id, int $limit = 4): array
    {
        $similar_properties = [];
        $params = [];
        $types = "";

        $sql = "SELECT p.id, p.title, p.slug, p.price, p.bedrooms, p.bathrooms, p.area, p.featured_image, 
                       pt.name as property_type_name, pt.slug as property_type_slug,
                       l.name as locality_name, l.slug as locality_slug,
                       ps.name as property_status_name, ps.slug as property_status_slug, ps.css_class as property_status_css_class
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN localities l ON p.locality_id = l.id
                LEFT JOIN property_statuses ps ON p.property_status_id = ps.id
                WHERE p.id != ? AND p.is_active = 1 AND p.is_approved = 1";
        
        $params[] = $current_property_id;
        $types .= "i";

        if ($type_id !== null) {
            $sql .= " AND p.property_type_id = ?";
            $params[] = $type_id;
            $types .= "i";
        }
        if ($locality_id !== null) {
            $sql .= " AND p.locality_id = ?";
            $params[] = $locality_id;
            $types .= "i";
        }
        
        $sql .= " ORDER BY p.is_featured DESC, RAND() LIMIT ?";
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("PropertyModel::getSimilarProperties - Prepare failed: " . $this->db->error);
            return [];
        }
        
        if (!empty($types)) { // Ensure bind_param is called only if there are params
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $row['url'] = SITE_URL . '/property/' . ($row['property_type_slug'] ?? 'property') . '/' . ($row['slug'] ?? $row['id']);
            $row['main_image'] = $row['featured_image'] ? SITE_URL . '/' . ltrim($row['featured_image'], '/') : SITE_URL . '/assets/images/property-placeholder.jpg';
            $row['formatted_price'] = $this->formatPrice($row['price']);
            $row['formatted_area'] = (!empty($row['area']) ? number_format($row['area']) : 'N/A') . ' sq.ft.';
            $similar_properties[] = $row;
        }
        $stmt->close();
        return $similar_properties;
    }
}
