<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

class PropertyController extends BaseController
{
    private function checkWritePermission()
    {
        $allowedRoles = ['superadmin', 'manager'];
        $currentRole = $this->session->get('admin_role') ?? '';
        if (!in_array($currentRole, $allowedRoles)) {
            $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Unauthorized access.') : 'Unauthorized access.');
            $this->redirect('admin/properties');
            exit;
        }
    }

    /**
     * Show form to create a new property
     */
    public function create()
    {
        $this->checkWritePermission();
        $this->data['page_title'] = ($this->mlSupport ? $this->mlSupport->translate('Add New Property') : 'Add New Property') . ' - ' . APP_NAME;
        $this->data['propertyTypes'] = PropertyType::getForSelect();
        $this->render('admin/properties/create');
    }

    /**
     * Store a newly created property
     */
    public function store()
    {
        $this->checkWritePermission();

        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Invalid request method.') : 'Invalid request method.');
            $this->redirect('admin/properties/create');
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Security validation failed. Please try again.') : 'Security validation failed.');
            $this->redirect('admin/properties/create');
            return;
        }

        try {
            $data = $this->request->all();

            // Basic validation
            if (empty($data['title']) || empty($data['price'])) {
                throw new Exception($this->mlSupport ? $this->mlSupport->translate('Title and Price are required.') : 'Title and Price are required.');
            }

            // Set default values
            $data['status'] = $data['status'] ?? 'active';
            $data['featured'] = isset($data['featured']) ? 1 : 0;
            $data['created_at'] = date('Y-m-d H:i:s');

            $property = Property::create($data);

            if ($property) {
                // Log the action
                $auditLog = new AuditLog();
                $auditLog->log(
                    $this->session->get('user_id') ?? 0,
                    'create_property',
                    'properties',
                    $property->id,
                    'Created property: ' . $property->title
                );

                $this->setFlash('success', $this->mlSupport ? $this->mlSupport->translate('Property added successfully!') : 'Property added successfully!');
                $this->redirect('admin/properties');
            } else {
                throw new Exception($this->mlSupport ? $this->mlSupport->translate('Failed to create property.') : 'Failed to create property.');
            }
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Error adding property: ') : 'Error adding property: ' . $e->getMessage());
            $this->redirect('admin/properties/create');
        }
    }

    /**
     * Show property details
     */
    public function show($id)
    {
        $property = Property::find->with(['fill', 'title'])($id);

        if (!$property) {
            $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Property not found!') : 'Property not found!');
            $this->redirect('admin/properties');
            return;
        }

        $this->data['property'] = $property->toArray();
        $this->data['page_title'] = ($this->mlSupport ? $this->mlSupport->translate('Property Details') : 'Property Details') . ' - ' . APP_NAME;
        $this->render('admin/properties/show');
    }

    /**
     * Show form to edit a property
     */
    public function edit($id)
    {
        $property = Property::find($id);

        if (!$property) {
            $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Property not found!') : 'Property not found!');
            $this->redirect('admin/properties');
            return;
        }

        // Convert to array for view compatibility
        $this->data['property'] = $property->toArray();
        $this->data['page_title'] = ($this->mlSupport ? $this->mlSupport->translate('Edit Property') : 'Edit Property') . ' - ' . APP_NAME;
        $this->data['propertyTypes'] = PropertyType::getForSelect();
        $this->render('admin/properties/edit');
    }

    /**
     * Update an existing property
     */
    public function update($id)
    {
        $this->checkWritePermission();

        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Invalid request method.') : 'Invalid request method.');
            $this->redirect("admin/properties/edit/{$id}");
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Security validation failed. Please try again.') : 'Security validation failed.');
            $this->redirect("admin/properties/edit/{$id}");
            return;
        }

        try {
            $property = Property::find($id);
            if (!$property) {
                throw new Exception($this->mlSupport ? $this->mlSupport->translate('Property not found!') : 'Property not found!');
            }

            $data = $this->request->all();
            $data['featured'] = isset($data['featured']) ? 1 : 0;
            $data['updated_at'] = date('Y-m-d H:i:s');

            $property->fill($data);

            if ($property->save()) {
                // Log the action
                $auditLog = new AuditLog();
                $auditLog->log(
                    $this->session->get('user_id') ?? 0,
                    'update_property',
                    'properties',
                    $property->id,
                    'Updated property: ' . $property->title
                );

                $this->setFlash('success', $this->mlSupport ? $this->mlSupport->translate('Property updated successfully!') : 'Property updated successfully!');
                $this->redirect('admin/properties');
            } else {
                throw new Exception($this->mlSupport ? $this->mlSupport->translate('Failed to update property.') : 'Failed to update property.');
            }
        } catch (Exception $e) {
            $this->setFlash('error', ($this->mlSupport ? $this->mlSupport->translate('Error updating property: ') : 'Error updating property: ') . $e->getMessage());
            $this->redirect("admin/properties/edit/{$id}");
        }
    }

    /**
     * Delete a property
     */
    public function delete($id)
    {
        $this->checkWritePermission();

        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Invalid request method.') : 'Invalid request method.');
            $this->redirect('admin/properties');
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Security validation failed.') : 'Security validation failed.');
            $this->redirect('admin/properties');
            return;
        }

        try {
            $property = Property::find($id);
            if ($property) {
                $title = $property->title;
                if ($property->delete()) {
                    // Log the action
                    $auditLog = new AuditLog();
                    $auditLog->log(
                        $this->session->get('user_id') ?? 0,
                        'delete_property',
                        'properties',
                        $id,
                        'Deleted property: ' . $title
                    );

                    $this->setFlash('success', $this->mlSupport ? $this->mlSupport->translate('Property deleted successfully!') : 'Property deleted successfully!');
                } else {
                    $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Failed to delete property.') : 'Failed to delete property.');
                }
            } else {
                $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Property not found!') : 'Property not found!');
            }
        } catch (Exception $e) {
            $this->setFlash('error', ($this->mlSupport ? $this->mlSupport->translate('Error deleting property: ') : 'Error deleting property: ') . $e->getMessage());
        }

        $this->redirect('admin/properties');
    }

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


