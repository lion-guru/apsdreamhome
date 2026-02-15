<?php

namespace App\Controllers;

use App\Services\PropertyService;

class PropertyController extends Controller {
    private $propertyService;

    public function __construct() {
        parent::__construct();
        $this->propertyService = new PropertyService();
    }

    /**
     * Display a listing of properties
     */
    public function index() {
        $filters = [
            'type' => $_GET['type'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'location' => $_GET['location'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 10),
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        $properties = $this->propertyService->getProperties($filters);
        
        $this->view('properties/index', [
            'title' => 'Properties',
            'properties' => $properties,
            'filters' => $filters
        ]);
    }

    /**
     * Display the specified property
     */
    public function show($id) {
        $property = $this->propertyService->getPropertyById($id);
        
        if (!$property) {
            $this->notFound();
            return;
        }
        
        $this->view('properties/show', [
            'title' => $property['title'],
            'property' => $property
        ]);
    }

    /**
     * Show the form for creating a new property
     */
    public function create() {
        $this->requireLogin();
        
        $this->view('properties/create', [
            'title' => 'Add New Property'
        ]);
    }

    /**
     * Store a newly created property
     */
    public function store() {
        $this->requireLogin();
        
        try {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => (float)($_POST['price'] ?? 0),
                'location' => $_POST['location'] ?? '',
                'type' => $_POST['type'] ?? 'residential',
                'bedrooms' => !empty($_POST['bedrooms']) ? (int)$_POST['bedrooms'] : null,
                'bathrooms' => !empty($_POST['bathrooms']) ? (int)$_POST['bathrooms'] : null,
                'area' => !empty($_POST['area']) ? (float)$_POST['area'] : null,
                'user_id' => $_SESSION['user_id']
            ];
            
            $propertyId = $this->propertyService->createProperty($data);
            
            if ($propertyId) {
                $this->setFlash('success', 'Property added successfully!');
                $this->redirect('/properties/' . $propertyId);
                return;
            }
            
            throw new \Exception('Failed to create property');
            
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/properties/create');
        }
    }

    /**
     * Show the form for editing a property
     */
    public function edit($id) {
        $this->requireLogin();
        
        $property = $this->propertyService->getPropertyById($id);
        
        if (!$property) {
            $this->notFound();
            return;
        }
        
        // Check if user is authorized to edit this property
        if ($property['user_id'] != $_SESSION['user_id'] && !$this->isAdmin()) {
            $this->forbidden();
            return;
        }
        
        $this->view('properties/edit', [
            'title' => 'Edit Property: ' . $property['title'],
            'property' => $property
        ]);
    }

    /**
     * Update the specified property
     */
    public function update($id) {
        $this->requireLogin();
        
        try {
            $property = $this->propertyService->getPropertyById($id);
            
            if (!$property) {
                $this->notFound();
                return;
            }
            
            // Check if user is authorized to update this property
            if ($property['user_id'] != $_SESSION['user_id'] && !$this->isAdmin()) {
                $this->forbidden();
                return;
            }
            
            $data = [
                'title' => $_POST['title'] ?? $property['title'],
                'description' => $_POST['description'] ?? $property['description'],
                'price' => isset($_POST['price']) ? (float)$_POST['price'] : $property['price'],
                'location' => $_POST['location'] ?? $property['location'],
                'type' => $_POST['type'] ?? $property['type'],
                'bedrooms' => isset($_POST['bedrooms']) ? (int)$_POST['bedrooms'] : $property['bedrooms'],
                'bathrooms' => isset($_POST['bathrooms']) ? (int)$_POST['bathrooms'] : $property['bathrooms'],
                'area' => isset($_POST['area']) ? (float)$_POST['area'] : $property['area'],
                'status' => $this->isAdmin() ? ($_POST['status'] ?? $property['status']) : $property['status']
            ];
            
            $result = $this->propertyService->updateProperty($id, $data);
            
            if ($result) {
                $this->setFlash('success', 'Property updated successfully!');
                $this->redirect('/properties/' . $id);
                return;
            }
            
            throw new \Exception('Failed to update property');
            
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            $this->redirect("/properties/$id/edit");
        }
    }

    /**
     * Remove the specified property
     */
    public function destroy($id) {
        $this->requireLogin();
        
        $property = $this->propertyService->getPropertyById($id);
        
        if (!$property) {
            $this->notFound();
            return;
        }
        
        // Check if user is authorized to delete this property
        if ($property['user_id'] != $_SESSION['user_id'] && !$this->isAdmin()) {
            $this->forbidden();
            return;
        }
        
        $result = $this->propertyService->deleteProperty($id);
        
        if ($result) {
            $this->setFlash('success', 'Property deleted successfully!');
            $this->redirect('/properties');
        } else {
            $this->setFlash('error', 'Failed to delete property');
            $this->redirect("/properties/$id");
        }
    }

    /**
     * Display featured properties
     */
    public function featured() {
        $properties = $this->propertyService->getFeaturedProperties(6);
        
        $this->view('properties/featured', [
            'title' => 'Featured Properties',
            'properties' => $properties
        ]);
    }
}
