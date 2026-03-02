<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class AdminControllerSimple extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        // Set admin layout
        $this->layout = 'layouts/admin';
        
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Display admin dashboard
     */
    public function index()
    {
        $this->data = [
            'title' => 'Admin Dashboard - APS Dream Home',
            'description' => 'Admin dashboard for managing properties, users, and system settings',
            'stats' => [
                'total_properties' => 150,
                'total_users' => 2500,
                'total_leads' => 850,
                'total_revenue' => '₹2.5Cr'
            ],
            'recent_properties' => [
                (object)[
                    'id' => 1,
                    'title' => 'Luxury Apartment in Gomti Nagar',
                    'location' => 'Gomti Nagar, Lucknow',
                    'price' => 7500000,
                    'status' => 'Active',
                    'created_at' => '2026-03-01'
                ],
                (object)[
                    'id' => 2,
                    'title' => 'Modern Villa in Hazratganj',
                    'location' => 'Hazratganj, Lucknow',
                    'price' => 12000000,
                    'status' => 'Pending',
                    'created_at' => '2026-03-02'
                ]
            ],
            'recent_users' => [
                (object)[
                    'id' => 1,
                    'name' => 'Rahul Kumar',
                    'email' => 'rahul@example.com',
                    'phone' => '9876543210',
                    'registered_at' => '2026-03-01'
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Priya Singh',
                    'email' => 'priya@example.com',
                    'phone' => '9876543211',
                    'registered_at' => '2026-03-02'
                ]
            ],
            'recent_leads' => [
                (object)[
                    'id' => 1,
                    'name' => 'Amit Sharma',
                    'email' => 'amit@example.com',
                    'phone' => '9876543212',
                    'property_interest' => 'Luxury Apartment',
                    'budget' => '₹50L - ₹1Cr',
                    'status' => 'New',
                    'source' => 'Website',
                    'created_at' => '2026-03-01',
                    'assigned_to' => 'Agent 1'
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Neha Gupta',
                    'email' => 'neha@example.com',
                    'phone' => '9876543213',
                    'property_interest' => 'Modern Villa',
                    'budget' => '₹1Cr - ₹2Cr',
                    'status' => 'Contacted',
                    'source' => 'Referral',
                    'created_at' => '2026-03-02',
                    'assigned_to' => 'Agent 2'
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Rohit Verma',
                    'email' => 'rohit@example.com',
                    'phone' => '9876543214',
                    'property_interest' => 'Commercial Space',
                    'budget' => '₹80L - ₹1.5Cr',
                    'status' => 'Qualified',
                    'source' => 'Social Media',
                    'created_at' => '2026-03-03',
                    'assigned_to' => 'Agent 1'
                ]
            ]
        ];

        $this->render('admin/dashboard', $this->data, 'layouts/admin');
    }

    /**
     * Display properties management
     */
    public function properties()
    {
        $this->data = [
            'title' => 'Properties Management - APS Dream Home',
            'description' => 'Manage all properties in the system',
            'properties' => [
                (object)[
                    'id' => 1,
                    'title' => 'Luxury Apartment in Gomti Nagar',
                    'location' => 'Gomti Nagar, Lucknow',
                    'price' => 7500000,
                    'type' => 'apartment',
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => 1500,
                    'status' => 'Active',
                    'featured' => true,
                    'created_at' => '2026-03-01'
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
                    'status' => 'Pending',
                    'featured' => false,
                    'created_at' => '2026-03-02'
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
                    'status' => 'Active',
                    'featured' => false,
                    'created_at' => '2026-03-03'
                ]
            ]
        ];

        $this->render('admin/properties/index', $this->data, 'layouts/admin');
    }

    /**
     * Display users management
     */
    public function users()
    {
        $this->data = [
            'title' => 'Users Management - APS Dream Home',
            'description' => 'Manage all users in the system',
            'users' => [
                (object)[
                    'id' => 1,
                    'name' => 'Rahul Kumar',
                    'email' => 'rahul@example.com',
                    'phone' => '9876543210',
                    'role' => 'user',
                    'status' => 'Active',
                    'registered_at' => '2026-03-01',
                    'last_login' => '2026-03-02'
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Priya Singh',
                    'email' => 'priya@example.com',
                    'phone' => '9876543211',
                    'role' => 'user',
                    'status' => 'Active',
                    'registered_at' => '2026-03-02',
                    'last_login' => '2026-03-03'
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Amit Sharma',
                    'email' => 'amit@example.com',
                    'phone' => '9876543212',
                    'role' => 'agent',
                    'status' => 'Active',
                    'registered_at' => '2026-03-01',
                    'last_login' => '2026-03-02'
                ]
            ]
        ];

        $this->render('admin/users/index', $this->data, 'layouts/admin');
    }

    /**
     * Display leads management
     */
    public function leads()
    {
        $this->data = [
            'title' => 'Leads Management - APS Dream Home',
            'description' => 'Manage all leads in the system',
            'leads' => [
                (object)[
                    'id' => 1,
                    'name' => 'Amit Sharma',
                    'email' => 'amit@example.com',
                    'phone' => '9876543212',
                    'property_interest' => 'Luxury Apartment',
                    'budget' => '₹50L - ₹1Cr',
                    'status' => 'New',
                    'source' => 'Website',
                    'created_at' => '2026-03-01',
                    'assigned_to' => 'Agent 1'
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Neha Gupta',
                    'email' => 'neha@example.com',
                    'phone' => '9876543213',
                    'property_interest' => 'Modern Villa',
                    'budget' => '₹1Cr - ₹2Cr',
                    'status' => 'Contacted',
                    'source' => 'Referral',
                    'created_at' => '2026-03-02',
                    'assigned_to' => 'Agent 2'
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Rohit Verma',
                    'email' => 'rohit@example.com',
                    'phone' => '9876543214',
                    'property_interest' => 'Commercial Space',
                    'budget' => '₹80L - ₹1.5Cr',
                    'status' => 'Qualified',
                    'source' => 'Social Media',
                    'created_at' => '2026-03-03',
                    'assigned_to' => 'Agent 1'
                ]
            ]
        ];

        $this->render('admin/leads/index', $this->data, 'layouts/admin');
    }

    /**
     * Display settings page
     */
    public function settings()
    {
        $this->data = [
            'title' => 'Settings - APS Dream Home',
            'description' => 'Manage system settings',
            'settings' => [
                'site_name' => 'APS Dream Home',
                'site_email' => 'info@apsdreamhome.com',
                'site_phone' => '+91-551-2345678',
                'site_address' => '123, Civil Lines, Gorakhpur, Uttar Pradesh - 273001',
                'social_media' => [
                    'facebook' => 'https://facebook.com/apsdreamhome',
                    'twitter' => 'https://twitter.com/apsdreamhome',
                    'instagram' => 'https://instagram.com/apsdreamhome',
                    'linkedin' => 'https://linkedin.com/company/apsdreamhome'
                ],
                'seo' => [
                    'meta_title' => 'APS Dream Home - Premium Real Estate in UP',
                    'meta_description' => 'Find your dream home with APS Dream Home. Premium properties in Gorakhpur, Lucknow & UP.',
                    'meta_keywords' => 'real estate, properties, gorakhpur, lucknow, dream home'
                ]
            ]
        ];

        $this->render('admin/settings/index', $this->data, 'layouts/admin');
    }
}
