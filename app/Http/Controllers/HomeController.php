<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        // Load all required data for home page
        $data = [
            'page_title' => 'Welcome to APS Dream Home',
            'page_description' => 'Discover premium properties and find your dream home with APS Dream Home - Your trusted real estate partner in UP',
            'hero_stats' => $this->loadHeroStats(),
            'property_types' => $this->loadPropertyTypes(),
            'featured_properties' => $this->loadFeaturedProperties(),
            'why_choose_us' => $this->loadWhyChooseUs(),
            'testimonials' => $this->loadTestimonials()
        ];

        // Render view with data
        $this->render('pages/home_new', $data);
    }
    
    public function about()
    {
        $data = [
            'page_title' => 'About Us - APS Dream Home',
            'page_description' => 'Learn about APS Dream Home - Leading real estate developer in Gorakhpur and Lucknow with 8+ years of excellence.',
            'stats' => $this->loadHeroStats(),
            'why_choose_us' => $this->loadWhyChooseUs(),
            'testimonials' => $this->loadTestimonials()
        ];

        $this->render('pages/about', $data);
    }
    
    public function contact()
    {
        // Handle POST request (form submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $this->sanitizeInput($_POST['name'] ?? '');
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $phone = $this->sanitizeInput($_POST['phone'] ?? '');
            $subject = $this->sanitizeInput($_POST['subject'] ?? '');
            $message = $this->sanitizeInput($_POST['message'] ?? '');

            // Basic validation
            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                $_SESSION['error'] = 'Please fill in all required fields.';
            } else {
                // Here you would typically save to database or send email
                $_SESSION['success'] = 'Thank you for your message! We will get back to you within 24 hours.';
                error_log("Contact form submission: Name: $name, Email: $email, Subject: $subject");
            }
            
            // Redirect back to contact page
            $this->redirect('contact');
            return;
        }

        // Load contact data
        $data = [
            'title' => 'Contact Us - APS Dream Home',
            'description' => 'Get in touch with APS Dream Home for all your real estate needs. Visit our offices or call us today.',
            'offices' => $this->loadOfficeLocations()
        ];

        // Render view with data
        $this->render('pages/contact', $data);
    }
    
    public function careers()
    {
        $data = [
            'page_title' => 'Careers - APS Dream Home',
            'page_description' => 'Join our team at APS Dream Home. Explore career opportunities in real estate development.'
        ];

        $this->render('pages/careers', $data);
    }
    
    public function careerApply()
    {
        $data = [
            'page_title' => 'Career Application - APS Dream Home',
            'page_description' => 'Apply for career opportunities at APS Dream Home.'
        ];

        $this->render('pages/career_apply', $data);
    }
    
    public function projects()
    {
        $data = [
            'page_title' => 'Our Projects - APS Dream Home',
            'page_description' => 'Explore our ongoing and completed real estate projects in Gorakhpur and Lucknow.',
            'projects' => $this->loadProjects()
        ];

        $this->render('pages/projects', $data);
    }
    
    public function blog()
    {
        // Sample blog data
        $blog_posts = [
            [
                'id' => 1,
                'title' => 'Top 10 Areas to Invest in Gorakhpur 2024',
                'excerpt' => 'Discover the most promising residential and commercial areas in Gorakhpur for real estate investment this year.',
                'content' => 'Full content would go here...',
                'category' => 'investment',
                'featured_image' => 'assets/images/blog/blog-1.jpg',
                'created_at' => '2024-01-15',
                'read_time' => 5
            ],
            [
                'id' => 2,
                'title' => 'Complete Guide to Home Loans in India',
                'excerpt' => 'Everything you need to know about getting a home loan, from eligibility to documentation.',
                'content' => 'Full content would go here...',
                'category' => 'finance',
                'featured_image' => 'assets/images/blog/blog-2.jpg',
                'created_at' => '2024-01-10',
                'read_time' => 8
            ]
        ];

        $data = [
            'page_title' => 'Blog - APS Dream Home',
            'page_description' => 'Stay updated with latest real estate news, market insights, and property tips from APS Dream Home.',
            'blog_posts' => $blog_posts
        ];

        $this->render('pages/blog', $data);
    }
    
    public function faq()
    {
        $data = [
            'page_title' => 'FAQ - APS Dream Home',
            'page_description' => 'Find answers to frequently asked questions about our properties and services.',
            'categories' => [
                ['id' => 1, 'name' => 'General', 'slug' => 'general'],
                ['id' => 2, 'name' => 'Properties', 'slug' => 'properties']
            ],
            'faqs' => [
                [
                    'id' => 1,
                    'question' => 'What types of properties do you offer?',
                    'answer' => 'We offer apartments, villas, and commercial spaces.',
                    'category_id' => 2
                ]
            ]
        ];

        $this->render('pages/faqs', $data);
    }
    
    public function team()
    {
        $data = [
            'page_title' => 'Our Team - APS Dream Home',
            'page_description' => 'Meet the experienced professionals behind APS Dream Home\'s success.'
        ];

        $this->render('pages/team', $data);
    }

    public function testimonials()
    {
        $data = [
            'page_title' => 'Testimonials - APS Dream Home',
            'page_description' => 'Read what our satisfied customers say about their experience with APS Dream Home.',
            'testimonials' => $this->loadTestimonials()
        ];

        $this->render('pages/testimonials', $data);
    }

    public function resell()
    {
        $data = [
            'page_title' => 'Resell Properties - APS Dream Home',
            'page_description' => 'Find resale properties and pre-owned homes in prime locations.'
        ];

        $this->render('pages/resell', $data);
    }
    
    private function loadFeaturedProperties()
    {
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
                'image_path' => '/assets/img/property1.jpg'
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
                'image_path' => '/assets/img/property2.jpg'
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
                'image_path' => '/assets/img/property3.jpg'
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
                'image_path' => '/assets/img/project1.jpg'
            ],
            (object)[
                'id' => 2,
                'name' => 'APS Plaza',
                'location' => 'Hazratganj, Lucknow',
                'type' => 'Commercial',
                'status' => 'Completed',
                'completion' => '100%',
                'description' => 'Premium commercial spaces in heart of Lucknow',
                'image_path' => '/assets/img/project2.jpg'
            ]
        ];
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
    
    private function loadOfficeLocations()
    {
        return [
            (object)[
                'name' => 'Main Office - Gorakhpur',
                'address' => '1st floor singhariya chauraha, Kunraghat, deoria Road, Gorakhpur, UP - 273008',
                'phone' => '+91 7007444842',
                'email' => 'info@apsdreamhome.com',
                'hours' => 'Mon-Sat: 9:30 AM - 7:00 PM, Sun: 10:00 AM - 5:00 PM'
            ],
            (object)[
                'name' => 'Branch Office - Lucknow',
                'address' => 'VIP Road, Gomti Nagar, Lucknow, UP - 226010',
                'phone' => '+91 7007444843',
                'email' => 'lucknow@apsdreamhome.com',
                'hours' => 'Mon-Sat: 10:00 AM - 6:00 PM'
            ]
        ];
    }
}
