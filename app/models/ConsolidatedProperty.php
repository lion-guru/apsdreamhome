<?php

namespace App\Models;

use App\Core\UnifiedModel;

/**
 * Consolidated Property Model
 * Unifies functionality from multiple legacy Property implementations:
 * - app/models/Property.php (modern)
 * - includes/PropertyManager.php (legacy)
 * - includes/managers.php PropertyManager (legacy)
 */
class ConsolidatedProperty extends UnifiedModel
{
    protected static $table = 'properties';

    protected $fillable = [
        'title',
        'description',
        'property_type_id',
        'property_category',
        'price',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'bedrooms',
        'bathrooms',
        'area_sqft',
        'land_area',
        'land_area_unit',
        'year_built',
        'parking_spaces',
        'garage_spaces',
        'floors',
        'floor_number',
        'furnishing',
        'condition',
        'orientation',
        'view',
        'amenities',
        'features',
        'images',
        'virtual_tour',
        'video_url',
        'latitude',
        'longitude',
        'agent_id',
        'developer_id',
        'project_id',
        'status',
        'hot_offer',
        'created_by',
        'updated_by',
        'listing_type',
        'listing_date',
        'expiry_date',
        'views',
        'likes',
        'shares',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'slug',
        'created_at',
        'updated_at'
    ];

    /**
     * Legacy manager instances
     */
    protected static $propertyManager = null;

    /**
     * Initialize legacy managers if available
     */
    protected static function initLegacyManagers()
    {
        if (self::$propertyManager === null) {
            try {
                if (class_exists('PropertyManager')) {
                    global $db;
                    self::$propertyManager = new \PropertyManager($db);
                }
            } catch (\Exception $e) {
                self::$propertyManager = false;
            }
        }
        return self::$propertyManager;
    }

    /**
     * Map legacy field names to modern field names
     */
    protected static function mapLegacyFields($data)
    {
        $mapped = [];
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'property_id':
                    $mapped['id'] = $value;
                    break;
                case 'property_name':
                    $mapped['title'] = $value;
                    break;
                case 'prop_type':
                    $mapped['property_type_id'] = $value;
                    break;
                case 'property_type':
                    $mapped['property_type_id'] = $value;
                    break;
                case 'type':
                    $mapped['property_type_id'] = $value;
                    break;
                case 'prop_category':
                    $mapped['property_category'] = $value;
                    break;
                case 'prop_status':
                    $mapped['status'] = $value;
                    break;
                case 'is_featured':
                    $mapped['hot_offer'] = $value;
                    break;
                case 'featured':
                    $mapped['hot_offer'] = $value;
                    break;
                case 'sold_status':
                    $mapped['status'] = $value;
                    break;
                case 'zipcode':
                    $mapped['postal_code'] = $value;
                    break;
                case 'area':
                    $mapped['area_sqft'] = $value;
                    break;
                case 'area_unit':
                    $mapped['land_area_unit'] = $value;
                    break;
                default:
                    $mapped[$key] = $value;
            }
        }
        return $mapped;
    }

    /**
     * Legacy operations
     */
    public static function findLegacy($id)
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'getPropertyById')) {
            $propertyData = $manager->getPropertyById($id);
            if ($propertyData) {
                $propertyData = self::mapLegacyFields($propertyData);
                return new static($propertyData);
            }
        }
        return self::find($id);
    }

    public static function allLegacy()
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'getAllProperties')) {
            $properties = $manager->getAllProperties();
            return array_map(function ($propertyData) {
                $propertyData = self::mapLegacyFields($propertyData);
                return new static($propertyData);
            }, $properties);
        }
        return self::all();
    }

    public function saveLegacy()
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'updateProperty')) {
            $data = $this->toArray();
            $data['property_id'] = $this->id;
            return $manager->updateProperty($data);
        }
        return $this->save();
    }

    public function deleteLegacy()
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'deleteProperty')) {
            return $manager->deleteProperty($this->id);
        }
        return $this->delete();
    }

    /**
     * Consolidated helpers
     */
    public static function getFeaturedProperties($limit = 10)
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'getFeaturedProperties')) {
            $properties = $manager->getFeaturedProperties($limit);
            return array_map(function ($propertyData) {
                $propertyData = self::mapLegacyFields($propertyData);
                return new static($propertyData);
            }, $properties);
        }

        $list = self::query()
            ->where('hot_offer', '=', 1)
            ->where('status', '=', 'available')
            ->get();

        return array_slice($list, 0, $limit);
    }

    public static function searchProperties(array $filters = [], $limit = 20)
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'searchProperties')) {
            $properties = $manager->searchProperties($filters, $limit);
            return array_map(function ($propertyData) {
                $propertyData = self::mapLegacyFields($propertyData);
                return new static($propertyData);
            }, $properties);
        }

        // Build query using the query builder
        $query = self::query();

        if (!empty($filters['location'])) {
            $query->where('city', 'like', '%' . $filters['location'] . '%');
        }

        // Handle property type filtering
        if (!empty($filters['property_type'])) {
            // Get property type ID from name
            $db = new \Database();
            $typeResult = $db->fetchOne("SELECT id FROM property_types WHERE name = :name AND status = 'active'", ['name' => $filters['property_type']]);
            if ($typeResult) {
                $query->where('property_type_id', '=', $typeResult['id']);
            }
        }
        if (!empty($filters['type'])) {
            // Handle numeric property_type_id
            $query->where('property_type_id', '=', $filters['type']);
        }
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }
        if (!empty($filters['bathrooms'])) {
            $query->where('bathrooms', '>=', $filters['bathrooms']);
        }
        if (!empty($filters['min_area'])) {
            $query->where('area_sqft', '>=', $filters['min_area']);
        }
        if (!empty($filters['max_area'])) {
            $query->where('area_sqft', '<=', $filters['max_area']);
        }

        $query->where('status', '=', 'available');
        $results = $query->get();
        return array_slice($results, 0, $limit);
    }

    public static function getPropertiesByAgent($agentId, $limit = 20)
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'getPropertiesByAgent')) {
            $properties = $manager->getPropertiesByAgent($agentId, $limit);
            return array_map(function ($propertyData) {
                $propertyData = self::mapLegacyFields($propertyData);
                return new static($propertyData);
            }, $properties);
        }

        $results = self::query()
            ->where('agent_id', '=', $agentId)
            ->where('status', '=', 'active')
            ->get();
        return array_slice($results, 0, $limit);
    }

    public static function getSimilarProperties($propertyId, $limit = 5)
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'getSimilarProperties')) {
            $properties = $manager->getSimilarProperties($propertyId, $limit);
            return array_map(function ($propertyData) {
                $propertyData = self::mapLegacyFields($propertyData);
                return new static($propertyData);
            }, $properties);
        }

        $property = self::find($propertyId);
        if (!$property) {
            return [];
        }

        $results = self::query()
            ->where('id', '<>', $propertyId)
            ->where('status', '=', 'available')
            ->where('property_type_id', '=', $property->property_type_id)
            ->where('city', '=', $property->city)
            ->get();
        return array_slice($results, 0, $limit);
    }

    public static function getRecentProperties($limit = 10)
    {
        $results = self::query()
            ->where('status', '=', 'active')
            ->get();

        usort($results, function ($a, $b) {
            return strtotime($b->created_at ?? '1970-01-01') <=> strtotime($a->created_at ?? '1970-01-01');
        });
        return array_slice($results, 0, $limit);
    }

    public static function getPropertyStats()
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'getPropertyStats')) {
            return $manager->getPropertyStats();
        }

        $all = self::all();
        $stats = [
            'total' => count($all),
            'active' => 0,
            'featured' => 0,
            'sold' => 0,
            'by_type' => []
        ];

        foreach ($all as $p) {
            if (($p->status ?? '') === 'available') $stats['active']++;
            if (!empty($p->hot_offer)) $stats['featured']++;
            if (($p->status ?? '') === 'sold') $stats['sold']++;

            // Get property type name from property_type_id
            $type = 'unknown';
            if (!empty($p->property_type_id)) {
                $db = new \Database();
                $typeResult = $db->fetchOne("SELECT name FROM property_types WHERE id = :id", ['id' => $p->property_type_id]);
                $type = $typeResult['name'] ?? 'unknown';
            }
            $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
        }

        return $stats;
    }

    public static function getPropertyTypes()
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'getPropertyTypes')) {
            return $manager->getPropertyTypes();
        }

        // Get distinct property types from the database
        $db = new \Database();
        $sql = "SELECT DISTINCT pt.name 
                FROM property_types pt 
                JOIN properties p ON pt.id = p.property_type_id 
                WHERE pt.status = 'active' 
                ORDER BY pt.name";
        $result = $db->fetchAll($sql);
        return array_column($result, 'name');
    }

    public function isActive()
    {
        return ($this->status ?? '') === 'active';
    }

    public function isFeatured()
    {
        return !empty($this->hot_offer);
    }

    public function getFormattedPrice()
    {
        $price = floatval($this->price ?? 0);
        if ($price >= 1000000) {
            return '$' . number_format($price / 1000000, 2) . 'M';
        } elseif ($price >= 1000) {
            return '$' . number_format($price / 1000, 1) . 'K';
        } else {
            return '$' . number_format($price, 2);
        }
    }

    public function getFormattedArea()
    {
        $area = $this->area_sqft ?? null;
        if ($area !== null) {
            return number_format($area) . ' sq ft';
        }
        return null;
    }

    public function getImages()
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'getPropertyImages')) {
            return $manager->getPropertyImages($this->id);
        }
        $images = $this->images ?? [];
        if (is_string($images)) {
            // Attempt simple JSON decode or CSV split
            $decoded = json_decode($images, true);
            if (is_array($decoded)) return $decoded;
            return array_filter(array_map('trim', explode(',', $images)));
        }
        return is_array($images) ? $images : [];
    }

    public function getMainImage()
    {
        $images = $this->getImages();
        return $images[0] ?? null;
    }

    public function getAgent()
    {
        return ConsolidatedUser::find($this->agent_id);
    }

    public function incrementViews()
    {
        $views = intval($this->views ?? 0) + 1;
        $this->views = $views;
        $this->save();
    }

    public function getUrl()
    {
        return '/property/' . (($this->slug ?? null) ?: $this->id);
    }
}
