<?php

namespace App\Http\Controllers\Api;

use Exception;

class PropertyController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['only' => ['saveSearch', 'getSavedSearches', 'deleteSearch', 'toggleFavorite']]);
        $this->middleware('csrf', ['only' => ['saveSearch', 'deleteSearch', 'toggleFavorite']]);
    }

    /**
     * Search properties
     */
    public function index()
    {
        return $this->search();
    }

    public function search()
    {
        try {
            // Check for API key but make it optional for public search if needed
            // if ($response = $this->validateApiKey(false)) {
            //     return $response;
            // }

            $filters = [
                'keyword' => $this->request()->input('keyword', $this->request()->input('q', '')),
                'location' => $this->request()->input('location', ''),
                'type' => $this->request()->input('type', $this->request()->input('property_type', '')),
                'purpose' => $this->request()->input('purpose', ''),
                'min_price' => $this->request()->input('min_price', 0),
                'max_price' => $this->request()->input('max_price', 1000000000),
                'bedrooms' => $this->request()->input('bedrooms', 0),
                'bathrooms' => $this->request()->input('bathrooms', 0)
            ];

            $page = \max(1, (int)$this->request()->input('page', 1));
            $limit = \min(50, \max(1, (int)$this->request()->input('limit', 10)));
            $offset = ($page - 1) * $limit;

            $propertyModel = $this->model('Property');
            $result = $propertyModel->searchProperties($filters, $limit, $offset);

            return $this->jsonSuccess([
                'properties' => $result['properties'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => \ceil($result['total'] / $limit)
                ]
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get a single property by ID
     */
    public function show($id)
    {
        try {
            if ($response = $this->validateApiKey(true)) {
                return $response;
            }
            $property = $this->model('Property')->getDetails($id);

            if (!$property) {
                return $this->jsonError('Property not found', 404);
            }

            return $this->jsonSuccess($property);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Compare properties
     */
    public function compare()
    {
        try {
            if ($response = $this->validateApiKey(true)) {
                return $response;
            }

            $idsParam = $this->request()->input('ids');
            $ids = $idsParam ? \explode(',', $idsParam) : [];
            $ids = \array_map('intval', \array_filter($ids));

            if (empty($ids)) {
                return $this->jsonError('No properties specified for comparison', 400);
            }

            $properties = $this->model('Property')->getByIds($ids);

            return $this->jsonSuccess(['properties' => $properties]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Save a property search
     */
    public function saveSearch()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();
            $name = $this->request()->input('name', 'My Search ' . \date('Y-m-d H:i'));
            $filters = $this->request()->input('filters', []);

            if (empty($filters)) {
                return $this->jsonError('Search filters are required', 400);
            }

            $this->model('SavedSearch')->create([
                'user_id' => $user->id,
                'name' => $name,
                'filters' => \json_encode($filters),
                'created_at' => \date('Y-m-d H:i:s')
            ]);

            return $this->jsonSuccess(['id' => $this->db->getLastInsertId()], 'Search saved successfully', 201);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get saved searches for current user
     */
    public function getSavedSearches()
    {
        try {
            $user = $this->auth->user();
            $searches = $this->model('SavedSearch')->getByUserId($user->id);

            foreach ($searches as &$search) {
                $search['filters'] = \json_decode($search['filters'], true);
            }

            return $this->jsonSuccess(['saved_searches' => $searches]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get a specific saved search
     */
    public function getSavedSearch()
    {
        try {
            $user = $this->auth->user();
            $id = $this->request()->input('id');

            if (!$id) {
                return $this->jsonError('Search ID is required', 400);
            }

            $search = $this->model('SavedSearch')->find($id);

            if (!$search || $search->user_id !== $user->id) {
                return $this->jsonError('Search not found', 404);
            }

            // Decode filters
            $filters = \json_decode($search->filters, true);

            // Format for frontend
            $data = [
                'id' => $search->id,
                'name' => $search->name,
                'user_id' => $search->user_id,
                'filters' => $filters,
                'search_params' => $filters, // Frontend expects this
                'created_at' => $search->created_at,
                'updated_at' => $search->updated_at
            ];

            return $this->jsonSuccess($data);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Delete a saved search
     */
    public function deleteSearch($id = null)
    {
        $method = $this->request()->getMethod();
        if ($method !== 'POST' && $method !== 'DELETE') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();

            // Get ID from input if not provided (for POST requests)
            if ($id === null) {
                $id = $this->request()->input('id');
            }

            if (!$id) {
                return $this->jsonError('Search ID is required', 400);
            }

            $this->model('SavedSearch')->deleteForUser($id, $user->id);

            return $this->jsonSuccess(null, 'Saved search deleted');
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get properties near coordinates
     */
    public function getNearby()
    {
        try {
            $lat = $this->request()->input('lat');
            $lng = $this->request()->input('lng');

            if ($lat === null || $lng === null) {
                return $this->jsonError('Latitude and longitude are required', 400);
            }

            $lat = (float)$lat;
            $lng = (float)$lng;
            $radius = \min((float)$this->request()->input('radius', 10), 50); // Max 50km
            $limit = \min((int)$this->request()->input('limit', 20), 50);

            $properties = $this->model('Property')->getNearby($lat, $lng, $radius, $limit);

            // Format properties with distance
            $formatted_properties = \array_map(function ($property) {
                return [
                    'id' => $property['id'],
                    'title' => $property['title'],
                    'price' => (float)$property['price'],
                    'city' => $property['city'],
                    'state' => $property['state'],
                    'property_type' => $property['property_type_name'],
                    'bedrooms' => (int)$property['bedrooms'],
                    'bathrooms' => (int)$property['bathrooms'],
                    'area_sqft' => (float)($property['area_sqft'] ?? 0),
                    'featured' => (bool)$property['featured'],
                    'distance' => \round((float)$property['distance'], 2),
                    'latitude' => (float)$property['latitude'],
                    'longitude' => (float)$property['longitude'],
                    'thumbnail' => $property['thumbnail_url'] ?? null
                ];
            }, $properties);

            return $this->jsonSuccess([
                'properties' => $formatted_properties,
                'search_center' => [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'radius_km' => $radius
                ],
                'total_found' => \count($formatted_properties)
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get amenities for an area
     */
    public function getAmenities()
    {
        try {
            $city = $this->request()->input('city', '');
            $type = $this->request()->input('type', 'all');

            if (empty($city)) {
                return $this->jsonError('City is required', 400);
            }

            $amenities = $this->model('AreaAmenity')->getByCity($city, $type);

            return $this->jsonSuccess([
                'city' => $city,
                'type' => $type,
                'amenities' => $amenities
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Bulk delete properties
     */
    public function bulkDelete()
    {
        try {
            if ($response = $this->validateApiKey(true)) {
                return $response;
            }

            $ids = $this->request()->input('ids', []);
            if (empty($ids) || !is_array($ids)) {
                return $this->jsonError('Property IDs array is required', 400);
            }

            // Validate that all IDs are numeric
            foreach ($ids as $id) {
                if (!is_numeric($id)) {
                    return $this->jsonError('Invalid property ID: ' . $id, 400);
                }
            }

            $propertyModel = $this->model('Property');
            $deletedCount = 0;

            // Start transaction for atomic operation
            $this->db()->beginTransaction();

            foreach ($ids as $id) {
                try {
                    $result = $propertyModel->delete($id);
                    if ($result) {
                        $deletedCount++;
                    }
                } catch (Exception $e) {
                    // Continue with other deletions even if one fails
                    error_log("Failed to delete property $id: " . $e->getMessage());
                }
            }

            $this->db()->commit();

            return $this->jsonSuccess([
                'message' => "Successfully deleted $deletedCount out of " . count($ids) . " properties",
                'deleted_count' => $deletedCount,
                'total_requested' => count($ids)
            ]);

        } catch (Exception $e) {
            if ($this->db()->inTransaction()) {
                $this->db()->rollBack();
            }
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Bulk update properties
     */
    public function bulkUpdate()
    {
        try {
            if ($response = $this->validateApiKey(true)) {
                return $response;
            }

            $ids = $this->request()->input('ids', []);
            $updates = $this->request()->input('updates', []);

            if (empty($ids) || !is_array($ids)) {
                return $this->jsonError('Property IDs array is required', 400);
            }

            if (empty($updates) || !is_array($updates)) {
                return $this->jsonError('Updates object is required', 400);
            }

            // Validate allowed update fields
            $allowedFields = ['status', 'price', 'featured', 'is_available'];
            $filteredUpdates = array_intersect_key($updates, array_flip($allowedFields));

            if (empty($filteredUpdates)) {
                return $this->jsonError('No valid fields to update', 400);
            }

            $propertyModel = $this->model('Property');
            $updatedCount = 0;

            // Start transaction
            $this->db()->beginTransaction();

            foreach ($ids as $id) {
                try {
                    $result = $propertyModel->update($id, $filteredUpdates);
                    if ($result) {
                        $updatedCount++;
                    }
                } catch (Exception $e) {
                    error_log("Failed to update property $id: " . $e->getMessage());
                }
            }

            $this->db()->commit();

            return $this->jsonSuccess([
                'message' => "Successfully updated $updatedCount out of " . count($ids) . " properties",
                'updated_count' => $updatedCount,
                'total_requested' => count($ids),
                'updated_fields' => array_keys($filteredUpdates)
            ]);

        } catch (Exception $e) {
            if ($this->db()->inTransaction()) {
                $this->db()->rollBack();
            }
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function getTypes()
    {
        try {
            if ($response = $this->validateApiKey(true)) {
                return $response;
            }
            $types = $this->model('PropertyType')->getAllOrdered();
            return $this->jsonSuccess(['property_types' => $types]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get all unique locations
     */
    public function getLocations()
    {
        try {
            if ($response = $this->validateApiKey(true)) {
                return $response;
            }
            $locations = $this->model('Property')->getUniqueLocations();
            return $this->jsonSuccess(['locations' => $locations]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
