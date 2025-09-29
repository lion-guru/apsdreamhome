<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Property;

class HomeController extends Controller {
    private $propertyModel;

    public function __construct() {
        $this->propertyModel = $this->model('Property');
    }

    public function index() {
        // Get featured properties - simplified version
        $featuredProperties = [];
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->query(
                "SELECT * FROM properties
                 WHERE is_featured = 1 AND status = 'active'
                 ORDER BY created_at DESC LIMIT 6"
            );
            $featuredProperties = $stmt->fetchAll();
        } catch (\Exception $e) {
            // Handle error silently
        }

        $properties = [];
        foreach ($featuredProperties as $property) {
            $properties[] = [
                'id' => $property['id'],
                'title' => $property['title'],
                'description' => $property['description'],
                'price' => $property['price'],
                'location' => $property['location'],
                'property_type' => $property['property_type'],
                'bedrooms' => $property['bedrooms'],
                'bathrooms' => $property['bathrooms'],
                'area' => $property['area'],
                'image_url' => $property['image_path'] ?: '/assets/images/no-image.jpg',
                'status' => $property['status']
            ];
        }

        $data = [
            'title' => 'Welcome to APS Dream Home',
            'properties' => $properties
        ];

        $this->view('home/index', $data);
    }

    public function about() {
        $data = [
            'title' => 'About Us - APS Dream Homes Pvt Ltd'
        ];

        $this->view('pages/about', $data);
    }

    public function contact() {
        $data = [
            'title' => 'Contact Us - APS Dream Homes Pvt Ltd'
        ];

        $this->view('pages/contact', $data);
    }
}
