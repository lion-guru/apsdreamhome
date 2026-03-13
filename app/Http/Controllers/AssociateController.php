<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Http\Controllers;

require_once __DIR__ . '/BaseController.php';

/**
 * AssociateController - Property Associate management
 */
class AssociateController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require authentication
     */
    private function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            $this->setFlash('error', 'Please login to access this page');
            $this->redirect('/login');
        }
    }

    /**
     * Associate registration page
     */
    public function register()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/associate/dashboard');
            return;
        }

        $this->render('auth/associate_register', [
            'page_title' => 'Associate Registration - APS Dream Home',
            'page_description' => 'Register as a Property Associate'
        ], 'layouts/base');
    }

    /**
     * Store associate registration
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $this->sanitize($_POST['name']) ?? '';
            $email = $this->sanitize($_POST['email']) ?? '';
            $phone = $this->sanitize($_POST['phone']) ?? '';
            $password = $this->sanitize($_POST['password']) ?? '';
            $experience = $this->sanitize($_POST['experience']) ?? '';
            $commission_rate = $this->sanitize($_POST['commission_rate']) ?? '';

            // Basic validation
            if (empty($name) || empty($email) || empty($phone) || empty($password)) {
                $this->setFlash('error', 'All required fields must be filled');
                $this->redirect('/associate/register');
                return;
            }

            // In production, save to database
            $this->setFlash('success', 'Registration successful! Please login.');
            $this->redirect('/login');
        }
    }

    /**
     * Associate dashboard
     */
    public function dashboard()
    {
        $this->requireAuth();

        // Sample data
        $stats = [
            'total_properties' => 15,
            'sold_properties' => 8,
            'pending_deals' => 3,
            'commission_earned' => 125000,
            'active_clients' => 12
        ];

        $recent_properties = [
            [
                'id' => 1,
                'title' => 'Luxury Apartment in Gomti Nagar',
                'price' => 7500000,
                'status' => 'listed',
                'client_name' => 'Ramesh Kumar',
                'commission' => 150000
            ],
            [
                'id' => 2,
                'title' => 'Modern Villa in Hazratganj',
                'price' => 12000000,
                'status' => 'negotiating',
                'client_name' => 'Priya Singh',
                'commission' => 240000
            ]
        ];

        $this->render('associate/dashboard', [
            'page_title' => 'Associate Dashboard - APS Dream Home',
            'page_description' => 'Manage your property listings and client relationships',
            'stats' => $stats,
            'recent_properties' => $recent_properties
        ], 'layouts/base');
    }
}
