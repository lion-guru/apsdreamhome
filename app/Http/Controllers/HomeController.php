<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $data;

    public function index()
    {
        // Load sample properties data
        $this->data = [
            'title' => 'Welcome to APS Dream Home',
            'description' => 'Discover premium properties and find your dream home with APS Dream Home - Your trusted real estate partner in UP',
            'properties' => $this->loadFeaturedProperties()
        ];

        // Render the view with data
        $this->render('home/index', $this->data, 'layouts/base');
    }
    
    private function loadFeaturedProperties()
    {
        // Sample featured properties data
        return [
            (object)[
                'id' => 1,
                'title' => 'Luxury Apartment in Gomti Nagar',
                'location' => 'Gomti Nagar, Lucknow',
                'address' => 'Gomti Nagar, Lucknow',
                'price' => 7500000,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 1500,
                'image_path' => BASE_URL . '/assets/images/property-1.jpg'
            ],
            (object)[
                'id' => 2,
                'title' => 'Modern Villa in Hazratganj',
                'location' => 'Hazratganj, Lucknow',
                'address' => 'Hazratganj, Lucknow',
                'price' => 12000000,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 2000,
                'image_path' => BASE_URL . '/assets/images/property-2.jpg'
            ],
            (object)[
                'id' => 3,
                'title' => 'Commercial Space in Vibhuti Khand',
                'location' => 'Vibhuti Khand, Gomti Nagar',
                'address' => 'Vibhuti Khand, Gomti Nagar',
                'price' => 8500000,
                'bedrooms' => 0,
                'bathrooms' => 2,
                'area' => 1200,
                'image_path' => BASE_URL . '/assets/images/property-3.jpg'
            ]
        ];
    }
}