<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Property;
use App\Models\PropertyType;
use Exception;

class PropertyController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List all properties
     */
    public function index()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->data['page_title'] = 'Property Management - ' . APP_NAME;

        $stmt = $this->db->query("SELECT * FROM properties ORDER BY created_at DESC");
        $this->data['properties'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/properties/index');
    }

    /**
     * Show form to create a new property
     */
    public function create()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->data['page_title'] = 'Add New Property - ' . APP_NAME;
        $this->data['propertyTypes'] = PropertyType::getForSelect();
        $this->render('admin/properties/create');
    }

    /**
     * Store a newly created property
     */
    public function store()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        try {
            $propertyData = $_POST;

            $sql = "INSERT INTO properties (title, description, price, location, property_type, bedrooms, bathrooms, area, status, created_at) 
                    VALUES (:title, :description, :price, :location, :property_type, :bedrooms, :bathrooms, :area, :status, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title' => $propertyData['title'] ?? '',
                ':description' => $propertyData['description'] ?? '',
                ':price' => $propertyData['price'] ?? 0,
                ':location' => $propertyData['location'] ?? '',
                ':property_type' => $propertyData['property_type'] ?? 'Residential',
                ':bedrooms' => $propertyData['bedrooms'] ?? 0,
                ':bathrooms' => $propertyData['bathrooms'] ?? 0,
                ':area' => $propertyData['area'] ?? 0,
                ':status' => $propertyData['status'] ?? 'active'
            ]);

            $this->setFlash('success', "Property added successfully!");
            $this->redirect('admin/properties');
        } catch (Exception $e) {
            $this->setFlash('error', "Error adding property: " . $e->getMessage());
            $this->redirect('admin/properties/create');
        }
    }

    /**
     * Show form to edit a property
     */
    public function edit($id)
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM properties WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $property = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$property) {
            $this->setFlash('error', "Property not found!");
            $this->redirect('admin/properties');
            return;
        }

        $this->data['property'] = $property;
        $this->data['page_title'] = 'Edit Property - ' . APP_NAME;
        $this->data['propertyTypes'] = PropertyType::getForSelect();
        $this->render('admin/properties/edit');
    }

    /**
     * Update an existing property
     */
    public function update($id)
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        try {
            $propertyData = $_POST;
            $sql = "UPDATE properties SET title = :title, description = :description, price = :price, location = :location, property_type = :property_type, bedrooms = :bedrooms, bathrooms = :bathrooms, area = :area, status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title' => $propertyData['title'],
                ':description' => $propertyData['description'],
                ':price' => $propertyData['price'],
                ':location' => $propertyData['location'],
                ':property_type' => $propertyData['property_type'],
                ':bedrooms' => $propertyData['bedrooms'],
                ':bathrooms' => $propertyData['bathrooms'],
                ':area' => $propertyData['area'],
                ':status' => $propertyData['status'],
                ':id' => $id
            ]);

            $this->setFlash('success', "Property updated successfully!");
            $this->redirect('admin/properties');
        } catch (Exception $e) {
            $this->setFlash('error', "Error updating property: " . $e->getMessage());
            $this->redirect("admin/properties/edit/{$id}");
        }
    }

    /**
     * Delete a property
     */
    public function delete($id)
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM properties WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $this->setFlash('success', "Property deleted successfully!");
        } catch (Exception $e) {
            $this->setFlash('error', "Error deleting property: " . $e->getMessage());
        }

        $this->redirect('admin/properties');
    }
}
