<?php

namespace App\Services\Map;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

/**
 * Map Service
 * Google Maps integration for property locations
 */
class MapService
{
    use ServiceTenantTrait;
    private $database;
    private $apiKey;
    private $baseUrl = 'https://maps.googleapis.com/maps/api';
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure map tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Property coordinates
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Nearby places of interest
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Map cache
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Geocode address to coordinates
     */
    public function geocode(string $address): array
    {
        $cacheKey = 'geocode_' . md5($address);
        
        // Check cache
        $cached = $this->getFromCache($cacheKey);
        if ($cached) {
            return $cached;
        }
        
        // Build API URL
        $url = $this->baseUrl . '/geocode/json';
        $params = [
            'address' => $address,
            'key' => $this->apiKey,
            'region' => 'in'
        ];
        
        $url .= '?' . http_build_query($params);
        
        try {
            // Make API call (in production)
            // $response = file_get_contents($url);
            // $data = json_decode($response, true);
            
            // For now, return mock data
            $result = [
                'success' => true,
                'formatted_address' => $address,
                'latitude' => 26.76000000 + (rand(-100, 100) / 10000),
                'longitude' => 83.37000000 + (rand(-100, 100) / 10000),
                'accuracy' => 'ROOFTOP',
                'place_id' => 'ChIJ' . uniqid()
            ];
            
            // Save to cache
            $this->saveToCache($cacheKey, $result, 86400); // 24 hours
            
            return $result;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Reverse geocode coordinates to address
     */
    public function reverseGeocode(float $lat, float $lng): array
    {
        $cacheKey = 'reverse_' . md5("{$lat},{$lng}");
        
        $cached = $this->getFromCache($cacheKey);
        if ($cached) {
            return $cached;
        }
        
        $url = $this->baseUrl . '/geocode/json';
        $params = [
            'latlng' => "{$lat},{$lng}",
            'key' => $this->apiKey
        ];
        
        $url .= '?' . http_build_query($params);
        
        try {
            // Mock response
            $result = [
                'success' => true,
                'formatted_address' => '123, Sample Street, Gorakhpur, Uttar Pradesh 273001, India',
                'address_components' => [
                    ['type' => 'street', 'name' => 'Sample Street'],
                    ['type' => 'city', 'name' => 'Gorakhpur'],
                    ['type' => 'state', 'name' => 'Uttar Pradesh'],
                    ['type' => 'postal_code', 'name' => '273001']
                ]
            ];
            
            $this->saveToCache($cacheKey, $result, 86400);
            return $result;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Save property coordinates
     */
    public function savePropertyCoordinates(int $propertyId, float $lat, float $lng, string $address = null): array
    {
        try {
            // Geocode if address provided but no coordinates
            if ($address && (!$lat || !$lng)) {
                $geocodeResult = $this->geocode($address);
                if ($geocodeResult['success']) {
                    $lat = $geocodeResult['latitude'];
                    $lng = $geocodeResult['longitude'];
                }
            }
            
            if (!$lat || !$lng) {
                return ['success' => false, 'error' => 'Invalid coordinates'];
            }
            
            $sql = "INSERT INTO property_coordinates 
                (property_id, latitude, longitude, address_formatted, geocoded_at, tenant_id)
                VALUES (?, ?, ?, ?, NOW(), ?)
                ON DUPLICATE KEY UPDATE
                latitude = VALUES(latitude),
                longitude = VALUES(longitude),
                address_formatted = VALUES(address_formatted),
                geocoded_at = NOW()";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $propertyId,
                $lat,
                $lng,
                $address,
                $this->tenantId()
            ]);
            
            // Fetch nearby places
            $this->fetchNearbyPlaces($propertyId, $lat, $lng);
            
            return [
                'success' => true,
                'property_id' => $propertyId,
                'latitude' => $lat,
                'longitude' => $lng
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get property coordinates
     */
    public function getPropertyCoordinates(int $propertyId): ?array
    {
        $sql = "SELECT pc.*, p.title, p.address
            FROM property_coordinates pc
            JOIN properties p ON pc.property_id = p.id
            WHERE pc.property_id = ?" . $this->tenantSql();
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$propertyId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Get properties in bounds
     */
    public function getPropertiesInBounds(float $swLat, float $swLng, float $neLat, float $neLng, array $filters = []): array
    {
        $sql = "SELECT pc.property_id, pc.latitude, pc.longitude, pc.address_formatted,
            p.title, p.price, p.type, p.area, p.bedrooms, p.primary_image
            FROM property_coordinates pc
            JOIN properties p ON pc.property_id = p.id
            WHERE pc.latitude BETWEEN ? AND ?
            AND pc.longitude BETWEEN ? AND ?
            AND p.status = 'available'" . $this->tenantSql();
        
        $params = [$swLat, $neLat, $swLng, $neLng];
        
        // Add filters
        if (!empty($filters['type'])) {
            $sql .= " AND p.type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        $sql .= " LIMIT 100";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Calculate distance between two points
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        $earthRadius = 6371000; // meters
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDelta / 2) * sin($lngDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        $distanceMeters = $earthRadius * $c;
        $distanceKm = $distanceMeters / 1000;
        
        // Estimate times
        $walkTimeMinutes = ceil($distanceMeters / 80); // 80 meters per minute walking
        $driveTimeMinutes = ceil($distanceKm / 0.5); // 30 km/h average in city
        
        return [
            'meters' => round($distanceMeters),
            'kilometers' => round($distanceKm, 2),
            'walk_time_minutes' => $walkTimeMinutes,
            'drive_time_minutes' => $driveTimeMinutes
        ];
    }
    
    /**
     * Get nearby places for property
     */
    public function getNearbyPlaces(int $propertyId, ?string $type = null, int $radius = 2000): array
    {
        $sql = "SELECT * FROM nearby_places 
            WHERE property_id = ?";
        
        $params = [$propertyId];
        
        if ($type) {
            $sql .= " AND place_type = ?";
            $params[] = $type;
        }
        
        $sql .= " AND distance_meters <= ? ORDER BY distance_meters ASC";
        $params[] = $radius;
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Fetch nearby places from Google Places API
     */
    private function fetchNearbyPlaces(int $propertyId, float $lat, float $lng): void
    {
        $placeTypes = [
            'school' => 'school',
            'hospital' => 'hospital',
            'bank' => 'bank',
            'shopping_mall' => 'mall',
            'supermarket' => 'market',
            'restaurant' => 'restaurant',
            'bus_station' => 'transit',
            'train_station' => 'transit',
            'park' => 'park',
            'place_of_worship' => 'temple'
        ];
        
        foreach ($placeTypes as $googleType => $ourType) {
            // In production, make API call
            // For now, insert mock data
            $this->saveNearbyPlace($propertyId, [
                'place_type' => $ourType,
                'place_name' => ucfirst($googleType) . ' ' . rand(1, 5),
                'distance_meters' => rand(100, 2000),
                'rating' => rand(30, 50) / 10,
                'walk_time_minutes' => rand(2, 20),
                'drive_time_minutes' => rand(1, 10)
            ]);
        }
    }
    
    /**
     * Save nearby place
     */
    private function saveNearbyPlace(int $propertyId, array $place): void
    {
$tenantId = $this->tenantId();
         $sql = "INSERT INTO nearby_places 
             (property_id, place_type, place_name, distance_meters, rating, walk_time_minutes, drive_time_minutes, tenant_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             distance_meters = VALUES(distance_meters)";
         
         $stmt = $this->database->prepare($sql);
         $stmt->execute([
             $propertyId,
             $place['place_type'],
             $place['place_name'],
             $place['distance_meters'],
             $place['rating'] ?? null,
             $place['walk_time_minutes'] ?? null,
             $place['drive_time_minutes'] ?? null,
             $tenantId,
         ]);
    }
    
    /**
     * Get directions
     */
    public function getDirections(float $originLat, float $originLng, float $destLat, float $destLng, string $mode = 'driving'): array
    {
        $cacheKey = 'directions_' . md5("{$originLat},{$originLng},{$destLat},{$destLng},{$mode}");
        
        $cached = $this->getFromCache($cacheKey);
        if ($cached) {
            return $cached;
        }
        
        $url = $this->baseUrl . '/directions/json';
        $params = [
            'origin' => "{$originLat},{$originLng}",
            'destination' => "{$destLat},{$destLng}",
            'mode' => $mode,
            'key' => $this->apiKey
        ];
        
        $url .= '?' . http_build_query($params);
        
        // Mock response
        $result = [
            'success' => true,
            'routes' => [
                [
                    'distance' => ['text' => '5.2 km', 'value' => 5200],
                    'duration' => ['text' => '15 mins', 'value' => 900],
                    'steps' => [
                        ['instruction' => 'Head north', 'distance' => '100 m'],
                        ['instruction' => 'Turn left', 'distance' => '200 m'],
                        ['instruction' => 'Destination on right', 'distance' => '50 m']
                    ]
                ]
            ]
        ];
        
        $this->saveToCache($cacheKey, $result, 3600); // 1 hour
        
        return $result;
    }
    
    /**
     * Generate static map URL
     */
    public function getStaticMapUrl(float $lat, float $lng, int $zoom = 15, string $size = '600x400'): string
    {
        $url = 'https://maps.googleapis.com/maps/api/staticmap';
        $params = [
            'center' => "{$lat},{$lng}",
            'zoom' => $zoom,
            'size' => $size,
            'markers' => "color:red|{$lat},{$lng}",
            'key' => $this->apiKey
        ];
        
        return $url . '?' . http_build_query($params);
    }
    
    /**
     * Get map cache
     */
    private function getFromCache(string $key): ?array
    {
        $sql = "SELECT response_data FROM map_cache 
            WHERE cache_key = ? AND expires_at > NOW()
            LIMIT 1";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$key]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ? json_decode($result['response_data'], true) : null;
    }
    
    /**
     * Save to map cache
     */
    private function saveToCache(string $key, array $data, int $ttl): void
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
        
$tenantId = $this->tenantId();
         $sql = "INSERT INTO map_cache (cache_key, response_data, expires_at, tenant_id)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             response_data = VALUES(response_data),
             expires_at = VALUES(expires_at)";
         
         $stmt = $this->database->prepare($sql);
         $stmt->execute([$key, json_encode($data), $expiresAt, $tenantId]);
    }
    
    /**
     * Cleanup old cache
     */
    public function cleanupCache(): int
    {
        $sql = "DELETE FROM map_cache WHERE expires_at < NOW() AND tenant_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$this->tenantId()]);
        return $stmt->rowCount();
    }
    
    /**
     * Get area insights
     */
    public function getAreaInsights(float $lat, float $lng): array
    {
        // Mock data - in production, aggregate from various sources
        return [
            'safety_score' => rand(70, 95),
            'connectivity_score' => rand(75, 95),
            'livability_score' => rand(70, 90),
            'avg_price_per_sqft' => rand(2500, 4500),
            'price_trend' => rand(-5, 15),
            'popular_amenities' => ['Schools', 'Hospitals', 'Malls', 'Parks'],
            'transport_connectivity' => [
                'nearest_metro' => rand(1, 5) . ' km',
                'nearest_railway' => rand(3, 10) . ' km',
                'nearest_airport' => rand(15, 40) . ' km'
            ]
        ];
    }
}
