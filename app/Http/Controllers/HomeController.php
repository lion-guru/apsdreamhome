<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $data;

    public function index()
    {
        // Load all required data for home page
        $this->data = [
            'page_title' => 'Welcome to APS Dream Home',
            'page_description' => 'Discover premium properties and find your dream home with APS Dream Home - Your trusted real estate partner in UP',
            'hero_stats' => $this->loadHeroStats(),
            'property_types' => $this->loadPropertyTypes(),
            'featured_properties' => $this->loadFeaturedProperties(),
            'why_choose_us' => $this->loadWhyChooseUs(),
            'testimonials' => $this->loadTestimonials()
        ];

        // Render view with data
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
    
    public function propertyDetail($id)
    {
        // Get specific property data
        $allProperties = $this->loadAllProperties();
        $property = null;
        
        foreach ($allProperties as $prop) {
            if ($prop->id == $id) {
                $property = $prop;
                break;
            }
        }
        
        if (!$property) {
            // Property not found, redirect to properties page
            header('Location: ' . BASE_URL . '/properties');
            exit;
        }
        
        $this->data = [
            'title' => $property->title . ' - APS Dream Home',
            'description' => 'View details for ' . $property->title . ' in ' . $property->location,
            'property' => $property,
            'related_properties' => array_slice($allProperties, 0, 3)
        ];
        
        $this->render('properties/detail', $this->data, 'layouts/base');
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
                'price' => '₹75 Lakhs',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 1500,
                'type' => 'apartment',
                'featured' => true,
                'image_path' => BASE_URL . '/assets/images/property-1.jpg'
            ],
            (object)[
                'id' => 2,
                'title' => 'Modern Villa in Hazratganj',
                'location' => 'Hazratganj, Lucknow',
                'address' => 'Hazratganj, Lucknow',
                'price' => '₹1.2 Crore',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 2000,
                'type' => 'villa',
                'featured' => true,
                'image_path' => BASE_URL . '/assets/images/property-2.jpg'
            ],
            (object)[
                'id' => 3,
                'title' => 'Commercial Space in Vibhuti Khand',
                'location' => 'Vibhuti Khand, Gomti Nagar',
                'address' => 'Vibhuti Khand, Gomti Nagar',
                'price' => '₹85 Lakhs',
                'bedrooms' => 0,
                'bathrooms' => 2,
                'area' => 1200,
                'type' => 'commercial',
                'featured' => false,
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
    
    public function services()
    {
        // Redirect to PageController services method
        $pageController = new \App\Http\Controllers\Public\PageController();
        return $pageController->services();
    }
    
    private function loadHeroStats()
    {
        return [
            'properties_sold' => '500+',
            'happy_clients' => '1000+',
            'years_experience' => '8+',
            'projects_completed' => '50+'
        ];
    }
    
    private function loadPropertyTypes()
    {
        return [
            (object)[
                'name' => 'Apartments',
                'count' => 25,
                'icon' => 'fa-building'
            ],
            (object)[
                'name' => 'Villas',
                'count' => 15,
                'icon' => 'fa-home'
            ],
            (object)[
                'name' => 'Commercial',
                'count' => 10,
                'icon' => 'fa-store'
            ],
            (object)[
                'name' => 'Land',
                'count' => 8,
                'icon' => 'fa-mountain'
            ]
        ];
    }
    
    private function loadWhyChooseUs()
    {
        return [
            (object)[
                'title' => '8+ Years Experience',
                'description' => 'Trusted real estate developer with proven track record in Uttar Pradesh',
                'icon' => 'fa-award'
            ],
            (object)[
                'title' => 'Quality Construction',
                'description' => 'Premium materials and modern construction techniques for lasting value',
                'icon' => 'fa-hard-hat'
            ],
            (object)[
                'title' => 'Customer Satisfaction',
                'description' => '1000+ happy families who found their dream home with us',
                'icon' => 'fa-smile'
            ],
            (object)[
                'title' => 'Transparent Pricing',
                'description' => 'No hidden charges, clear documentation, and fair pricing',
                'icon' => 'fa-handshake'
            ]
        ];
    }
    
    private function loadTestimonials()
    {
        return [
            (object)[
                'name' => 'Ramesh Kumar',
                'property' => '3BHK Apartment, Gomti Nagar',
                'content' => 'Excellent service and transparent dealing. Got my dream home within budget. Highly recommended!',
                'rating' => 5
            ],
            (object)[
                'name' => 'Priya Singh',
                'property' => '2BHK Villa, Hazratganj',
                'content' => 'Professional team and quality construction. The entire process was smooth and hassle-free.',
                'rating' => 5
            ],
            (object)[
                'name' => 'Amit Verma',
                'property' => 'Commercial Space, Vibhuti Khand',
                'content' => 'Great investment opportunity. APS Dream Home delivered exactly what they promised.',
                'rating' => 4
            ]
        ];
    }
}