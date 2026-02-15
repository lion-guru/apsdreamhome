<?php

namespace App\Http\Controllers\Api;

use \Exception;

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
    public function search()
    {
        try {
            if ($response = $this->validateApiKey(true)) {
                return $response;
            }

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

        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Delete a saved search
     */
    public function deleteSearch($id)
    {
        $method = $this->request()->getMethod();
        if ($method !== 'POST' && $method !== 'DELETE') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();
            $this->model('SavedSearch')->deleteForUser($id, $user->id);

            return $this->jsonSuccess(null, 'Saved search deleted');
        } catch (\Exception $e) {
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
            $formatted_properties = \array_map(function($property) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
