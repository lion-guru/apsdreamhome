<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Property;
use App\Models\PropertyType;
use Exception;

use App\Models\AuditLog;

class PropertyController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // AdminController handles authentication check
    }

    /**
     * List all properties
     */
    public function index()
    {
        $page = $this->request->get('page', 1);
        $perPage = 10;
        $search = $this->request->get('search', '');
        $status = $this->request->get('status', '');
        $type = $this->request->get('type', '');

        $filters = [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'status' => $status,
            'type' => $type
        ];

        $this->data['page_title'] = ($this->mlSupport ? $this->mlSupport->translate('Property Management') : 'Property Management') . ' - ' . APP_NAME;

        $propertyModel = new Property();
        $this->data['properties'] = $propertyModel->getAdminProperties($filters);
        $this->data['total_properties'] = $propertyModel->getAdminTotalProperties($filters);
        $this->data['current_page'] = $page;
        $this->data['total_pages'] = ceil($this->data['total_properties'] / $perPage);
        $this->data['filters'] = $filters;

        $this->render('admin/properties/index');
    }

    private function checkWritePermission()
    {
        $allowedRoles = ['superadmin', 'manager'];
        $currentRole = $_SESSION['admin_role'] ?? '';
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
                    $_SESSION['user_id'] ?? 0,
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
                    $_SESSION['user_id'] ?? 0,
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
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport ? $this->mlSupport->translate('Security validation failed.') : 'Security validation failed.');
                $this->redirect('admin/properties');
                return;
            }
        }

        try {
            $property = Property::find($id);
            if ($property) {
                $title = $property->title;
                if ($property->delete()) {
                    // Log the action
                    $auditLog = new AuditLog();
                    $auditLog->log(
                        $_SESSION['user_id'] ?? 0,
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
}
}
