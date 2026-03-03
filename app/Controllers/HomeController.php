<?php
/**
 * Home Controller
 * 
 * Handles home page functionality
 */

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    
    /**
     * Home page index
     */
    public function index() {
        // Get featured properties
        $featuredProperties = $this->getFeaturedProperties();
        
        // Get recent properties
        $recentProperties = $this->getRecentProperties();
        
        // Render home view
        return $this->view('home.index', [
            'featuredProperties' => $featuredProperties,
            'recentProperties' => $recentProperties
        ]);
    }
    
    /**
     * Get featured properties
     */
    private function getFeaturedProperties() {
        // Mock data for now - in real implementation, use Property model
        return [
            [
                'id' => 1,
                'title' => 'Luxury Villa in Goa',
                'location' => 'Goa, India',
                'price' => 2500000,
                'image' => 'assets/images/property1.jpg',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 3500
            ],
            [
                'id' => 2,
                'title' => 'Modern Apartment in Mumbai',
                'location' => 'Mumbai, India',
                'price' => 1800000,
                'image' => 'assets/images/property2.jpg',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 1800
            ],
            [
                'id' => 3,
                'title' => 'Beach House in Kerala',
                'location' => 'Kerala, India',
                'price' => 3200000,
                'image' => 'assets/images/property3.jpg',
                'bedrooms' => 5,
                'bathrooms' => 4,
                'area' => 4500
            ]
        ];
    }
    
    /**
     * Get recent properties
     */
    private function getRecentProperties() {
        // Mock data for now - in real implementation, use Property model
        return [
            [
                'id' => 4,
                'title' => 'Penthouse in Delhi',
                'location' => 'Delhi, India',
                'price' => 4500000,
                'image' => 'assets/images/property4.jpg',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 3200
            ],
            [
                'id' => 5,
                'title' => 'Cottage in Manali',
                'location' => 'Manali, India',
                'price' => 1200000,
                'image' => 'assets/images/property5.jpg',
                'bedrooms' => 2,
                'bathrooms' => 1,
                'area' => 1200
            ],
            [
                'id' => 6,
                'title' => 'Farmhouse in Pune',
                'location' => 'Pune, India',
                'price' => 2800000,
                'image' => 'assets/images/property6.jpg',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 4000
            ]
        ];
    }
}
