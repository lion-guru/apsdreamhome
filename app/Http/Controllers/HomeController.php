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
    
    public function properties()
    {
        // Load all properties data
        $this->data = [
            'title' => 'Properties - APS Dream Home',
            'description' => 'Browse our extensive collection of premium properties in Gorakhpur, Lucknow, and across Uttar Pradesh',
            'properties' => $this->loadAllProperties()
        ];

        // Render the view with data
        $this->render('properties/index', $this->data, 'layouts/base');
    }
    
    public function projects()
    {
        // Load projects data
        $this->data = [
            'title' => 'Projects - APS Dream Home',
            'description' => 'Explore our ongoing and completed residential and commercial projects across Uttar Pradesh',
            'projects' => $this->loadProjects()
        ];

        // Render the view with data
        $this->render('projects/index', $this->data, 'layouts/base');
    }
    
    public function contact()
    {
        // Load contact data
        $this->data = [
            'title' => 'Contact Us - APS Dream Home',
            'description' => 'Get in touch with APS Dream Home for all your real estate needs. Visit our offices or call us today.',
            'offices' => $this->loadOfficeLocations()
        ];

        // Render the view with data
        $this->render('contact/index', $this->data, 'layouts/base');
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
    
    private function loadAllProperties()
    {
        return [
            (object)[
                'id' => 1,
                'title' => 'Luxury Apartment in Gomti Nagar',
                'location' => 'Gomti Nagar, Lucknow',
                'price' => 7500000,
                'type' => 'apartment',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 1500,
                'featured' => true,
                'image_path' => BASE_URL . '/assets/images/property-1.jpg'
            ],
            (object)[
                'id' => 2,
                'title' => 'Modern Villa in Hazratganj',
                'location' => 'Hazratganj, Lucknow',
                'price' => 12000000,
                'type' => 'villa',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 2000,
                'featured' => true,
                'image_path' => BASE_URL . '/assets/images/property-2.jpg'
            ],
            (object)[
                'id' => 3,
                'title' => 'Commercial Space in Vibhuti Khand',
                'location' => 'Vibhuti Khand, Gomti Nagar',
                'price' => 8500000,
                'type' => 'commercial',
                'bedrooms' => 0,
                'bathrooms' => 2,
                'area' => 1200,
                'featured' => false,
                'image_path' => BASE_URL . '/assets/images/property-3.jpg'
            ],
            (object)[
                'id' => 4,
                'title' => '2BHK Apartment in Alambagh',
                'location' => 'Alambagh, Lucknow',
                'price' => 4500000,
                'type' => 'apartment',
                'bedrooms' => 2,
                'bathrooms' => 1,
                'area' => 950,
                'featured' => false,
                'image_path' => BASE_URL . '/assets/images/property-4.jpg'
            ],
            (object)[
                'id' => 5,
                'title' => '3BHK in Gomti Nagar Extension',
                'location' => 'Gomti Nagar Extension, Lucknow',
                'price' => 6500000,
                'type' => 'apartment',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 1350,
                'featured' => false,
                'image_path' => BASE_URL . '/assets/images/property-5.jpg'
            ]
        ];
    }
    
    private function loadProjects()
    {
        return [
            (object)[
                'id' => 1,
                'name' => 'APS Gardenia',
                'location' => 'Gomti Nagar, Lucknow',
                'type' => 'Residential',
                'status' => 'Ongoing',
                'completion' => '65%',
                'description' => 'Luxury residential apartments with modern amenities',
                'image_path' => BASE_URL . '/assets/images/project-1.jpg'
            ],
            (object)[
                'id' => 2,
                'name' => 'APS Plaza',
                'location' => 'Hazratganj, Lucknow',
                'type' => 'Commercial',
                'status' => 'Completed',
                'completion' => '100%',
                'description' => 'Premium commercial spaces in the heart of Lucknow',
                'image_path' => BASE_URL . '/assets/images/project-2.jpg'
            ]
        ];
    }
    
    private function loadOfficeLocations()
    {
        return [
            (object)[
                'name' => 'Head Office',
                'address' => '123, Civil Lines, Gorakhpur, Uttar Pradesh - 273001',
                'phone' => '+91-551-2345678',
                'email' => 'info@apsdreamhome.com',
                'timing' => '9:00 AM - 7:00 PM'
            ],
            (object)[
                'name' => 'Lucknow Branch',
                'address' => '456, Gomti Nagar, Lucknow, Uttar Pradesh - 226010',
                'phone' => '+91-522-3456789',
                'email' => 'lucknow@apsdreamhome.com',
                'timing' => '10:00 AM - 6:00 PM'
            ]
        ];
    }
}