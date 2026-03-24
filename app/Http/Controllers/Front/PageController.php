<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

class PageController extends BaseController
{
    // Home Page
    public function home()
    {
        try {
            // Get hero statistics
            $hero_stats = [
                'years_experience' => 15,
                'projects_completed' => 50,
                'happy_customers' => 1000,
                'awards_won' => 25,
            ];

            // Get featured properties
            $featured_properties = [
                [
                    'id' => 1,
                    'title' => 'Suyoday Colony',
                    'location' => 'Gorakhpur',
                    'price' => '₹7.5 Lakhs',
                    'image' => 'suyoday.jpg',
                    'type' => 'Residential',
                    'status' => 'Available'
                ],
                [
                    'id' => 2,
                    'title' => 'Raghunat Nagri',
                    'location' => 'Gorakhpur',
                    'price' => '₹8.5 Lakhs',
                    'image' => 'raghunat.jpg',
                    'type' => 'Residential',
                    'status' => 'Available'
                ],
            ];

            // Load home page view with data
            include __DIR__ . '/../../../views/pages/home.php';
        } catch (Exception $e) {
            error_log("Home page error: " . $e->getMessage());
            echo "Error loading home page. Please try again later.";
        }
    }

    // Testimonials
    public function testimonials()
    {
        try {
            // Sample testimonials data
            $testimonials = [
                (object)[
                    'name' => 'Ramesh Kumar',
                    'rating' => 5,
                    'message' => 'Excellent service! Found my dream home through APS Dream Home. The team was very professional and helpful throughout the entire process.',
                    'created_at' => '2024-01-15',
                    'designation' => 'Client'
                ],
                (object)[
                    'name' => 'Sunita Devi',
                    'rating' => 5,
                    'message' => 'Amazing experience with APS Dream Home! They provided excellent guidance and helped us find the perfect property that meets all our requirements.',
                    'created_at' => '2024-02-20',
                    'designation' => 'Client'
                ],
                (object)[
                    'name' => 'Amit Singh',
                    'rating' => 4,
                    'message' => 'Very professional service from APS Dream Home. The team is knowledgeable and provided great support.',
                    'created_at' => '2024-01-10',
                    'designation' => 'Client'
                ],
                (object)[
                    'name' => 'Pooja Sharma',
                    'rating' => 5,
                    'message' => 'Outstanding service! APS Dream Home made our home buying experience smooth and hassle-free.',
                    'created_at' => '2024-03-05',
                    'designation' => 'Client'
                ],
                (object)[
                    'name' => 'Anita Gupta',
                    'rating' => 5,
                    'message' => 'Highly recommend APS Dream Home! Excellent properties and exceptional customer service.',
                    'created_at' => '2024-02-15',
                    'designation' => 'Client'
                ]
            ];

            // Breadcrumb data
            $breadcrumbs = [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Testimonials', 'url' => BASE_URL . '/testimonials']
            ];

            $data = [
                'page_title' => 'Testimonials - APS Dream Home',
                'page_description' => 'What our clients say about APS Dream Home',
                'testimonials' => $testimonials,
                'breadcrumbs' => $breadcrumbs
            ];
            
            $this->render('pages/testimonials', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading testimonials page', $e->getMessage());
        }
    }

    // Resell
    public function resell()
    {
        $data = [
            'page_title' => 'Resell Property - APS Dream Home',
            'page_description' => 'Sell your property through APS Dream Home'
        ];
        $this->render('pages/resell', $data);
    }

    // Plots Availability
    public function plotsAvailability()
    {
        $data = [
            'page_title' => 'Plots Availability - APS Dream Home',
            'page_description' => 'Check available plots across our projects'
        ];
        $this->render('pages/plots-availability', $data);
    }

    // Plot
    public function plot()
    {
        $data = [
            'page_title' => 'Plot Details - APS Dream Home',
            'page_description' => 'View detailed plot information'
        ];
        $this->render('pages/plot', $data);
    }

    // News
    public function news()
    {
        $data = [
            'page_title' => 'News - APS Dream Home',
            'page_description' => 'Latest news and updates from APS Dream Home'
        ];
        $this->render('pages/news', $data);
    }

    // Navigation
    public function navigation()
    {
        $data = [
            'page_title' => 'Navigation - APS Dream Home',
            'page_description' => 'Navigate APS Dream Home website'
        ];
        $this->render('pages/navigation', $data);
    }

    // MLM Dashboard
    public function mlmDashboard()
    {
        $data = [
            'page_title' => 'MLM Dashboard - APS Dream Home',
            'page_description' => 'Manage your MLM network and earnings'
        ];
        $this->render('pages/mlm-dashboard', $data);
    }

    // Financial Services
    public function financialServices()
    {
        $data = [
            'page_title' => 'Financial Services - APS Dream Home',
            'page_description' => 'Banking and financial services for property buyers'
        ];
        $this->render('pages/financial_services', $data);
    }

    // Featured Properties
    public function featuredProperties()
    {
        $data = [
            'page_title' => 'Featured Properties - APS Dream Home',
            'page_description' => 'Handpicked premium properties by APS Dream Home'
        ];
        $this->render('pages/featured_properties', $data);
    }

    // FAQs
    public function faqs()
    {
        $data = [
            'page_title' => 'FAQs - APS Dream Home',
            'page_description' => 'Frequently asked questions about APS Dream Home'
        ];
        $this->render('pages/faqs', $data);
    }

    // Downloads
    public function downloads()
    {
        $data = [
            'page_title' => 'Downloads - APS Dream Home',
            'page_description' => 'Download brochures and documents from APS Dream Home'
        ];
        $this->render('pages/downloads', $data);
    }

    // Customer Reviews
    public function customerReviews()
    {
        $data = [
            'page_title' => 'Customer Reviews - APS Dream Home',
            'page_description' => 'Read reviews from our satisfied customers'
        ];
        $this->render('pages/customer_reviews', $data);
    }

    // Create Mobile App
    public function createMobileApp()
    {
        $data = [
            'page_title' => 'Mobile App - APS Dream Home',
            'page_description' => 'Download APS Dream Home mobile application'
        ];
        $this->render('pages/create_mobile_app', $data);
    }

    // Interior Design
    public function interiorDesign()
    {
        $data = [
            'page_title' => 'Interior Design - APS Dream Home',
            'page_description' => 'Professional interior design services'
        ];
        $this->render('pages/interior_design', $data);
    }

    // Email System
    public function emailSystem()
    {
        $data = [
            'page_title' => 'Email System - APS Dream Home',
            'page_description' => 'Send emails to APS Dream Home team'
        ];
        $this->render('pages/email_system', $data);
    }

    // Legal Terms Conditions
    public function legalTermsConditions()
    {
        $data = [
            'page_title' => 'Terms & Conditions - APS Dream Home',
            'page_description' => 'Detailed terms and conditions of APS Dream Home'
        ];
        $this->render('pages/legal/terms_conditions', $data);
    }

    // Legal Services
    public function legalServices()
    {
        $data = [
            'page_title' => 'Legal Services - APS Dream Home',
            'page_description' => 'Legal services provided by APS Dream Home'
        ];
        $this->render('pages/legal/services', $data);
    }

    // Legal Documents
    public function legalDocuments()
    {
        $data = [
            'page_title' => 'Legal Documents - APS Dream Home',
            'page_description' => 'Access legal documents and agreements'
        ];
        $this->render('pages/legal/documents', $data);
    }

    // System Log Security Event
    public function systemLogSecurityEvent()
    {
        $data = [
            'page_title' => 'Security Log - APS Dream Home',
            'page_description' => 'System security event logging'
        ];
        $this->render('pages/system/log_security_event', $data);
    }

    // System Launch System
    public function systemLaunchSystem()
    {
        $data = [
            'page_title' => 'Launch System - APS Dream Home',
            'page_description' => 'System launch and deployment interface'
        ];
        $this->render('pages/system/launch_system', $data);
    }

    // System KYC Upload
    public function systemKycUpload()
    {
        $data = [
            'page_title' => 'KYC Upload - APS Dream Home',
            'page_description' => 'Know Your Customer verification system'
        ];
        $this->render('pages/system/kyc-upload', $data);
    }

    // WhatsApp Templates
    public function whatsappTemplates()
    {
        $data = [
            'page_title' => 'WhatsApp Templates - APS Dream Home',
            'page_description' => 'WhatsApp message templates for marketing'
        ];
        $this->render('pages/whatsapp-templates', $data);
    }
}
