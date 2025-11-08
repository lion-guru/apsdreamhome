<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Project;
use App\Models\Property;

class HomeController extends Controller {
    private $projectModel;
    private $propertyModel;

    public function __construct() {
        $this->projectModel = new Project();
        $this->propertyModel = new Property();
    }

    public function index() {
        // Get featured projects
        $featuredProjects = $this->projectModel->getFeaturedProjects(6);

        // Get featured properties (legacy support)
        $featuredProperties = $this->propertyModel->getFeaturedProperties(6);

        // Get all active projects for listing
        $allProjects = $this->projectModel->getAllActiveProjects(12);

        // Get project statistics
        $stats = $this->projectModel->getProjectStats();

        // Get unique cities for filter
        $cities = $this->projectModel->getUniqueCities();

        $data = [
            'title' => 'Welcome to APS Dream Home',
            'featured_projects' => $featuredProjects,
            'featured_properties' => $featuredProperties,
            'all_projects' => $allProjects,
            'stats' => $stats,
            'cities' => $cities
        ];

        $this->view('home/index', $data);
    }

    public function about() {
        // Get company information from database or static content
        $data = [
            'title' => 'About Us - APS Dream Homes Pvt Ltd',
            'company_info' => $this->getCompanyInfo()
        ];

        $this->view('pages/about', $data);
    }

    public function contact() {
        $data = [
            'title' => 'Contact Us - APS Dream Homes Pvt Ltd',
            'contact_info' => $this->getContactInfo()
        ];

        $this->view('pages/contact', $data);
    }

    public function project($projectCode = null) {
        if (!$projectCode) {
            $this->redirect('/');
        }

        $project = $this->projectModel->getProjectByCode($projectCode);

        if (!$project) {
            $this->notFound();
        }

        // Decode JSON fields for display
        $project['amenities'] = json_decode($project['amenities'] ?? '[]', true) ?: [];
        $project['highlights'] = json_decode($project['highlights'] ?? '[]', true) ?: [];
        $project['gallery_images'] = json_decode($project['gallery_images'] ?? '[]', true) ?: [];

        $data = [
            'title' => $project['project_name'] . ' - APS Dream Home',
            'project' => $project
        ];

        $this->view('projects/detail', $data);
    }

    public function projects() {
        $city = $_GET['city'] ?? null;
        $search = $_GET['search'] ?? null;

        $projects = [];
        if ($search) {
            $projects = $this->projectModel->searchProjects($search, $_GET);
        } elseif ($city) {
            $projects = $this->projectModel->getProjectsByCity($city);
        } else {
            $projects = $this->projectModel->getAllActiveProjects();
        }

        $cities = $this->projectModel->getUniqueCities();
        $projectTypes = $this->projectModel->getUniqueProjectTypes();

        $data = [
            'title' => 'Our Projects - APS Dream Home',
            'projects' => $projects,
            'cities' => $cities,
            'project_types' => $projectTypes,
            'current_city' => $city,
            'current_search' => $search
        ];

        $this->view('projects/index', $data);
    }

    public function featuredProperties() {
        $featuredProperties = $this->propertyModel->getFeaturedProperties(12);

        $data = [
            'title' => 'Featured Properties - APS Dream Home',
            'properties' => $featuredProperties
        ];

        $this->view('properties/featured', $data);
    }

    public function propertyDetail($id) {
        $property = $this->propertyModel->getPropertyById($id);

        if (!$property) {
            $this->notFound();
        }

        $data = [
            'title' => $property->title . ' - APS Dream Home',
            'property' => $property
        ];

        $this->view('properties/detail', $data);
    }

    private function getCompanyInfo() {
        return [
            'name' => 'APS Dream Homes Pvt Ltd',
            'description' => 'APS Dream Homes Pvt Ltd is a leading real estate developer committed to creating exceptional living spaces that combine modern design with traditional values.',
            'mission' => 'To provide quality housing solutions that exceed customer expectations while maintaining the highest standards of construction and customer service.',
            'vision' => 'To be the most trusted and preferred real estate developer in the region, known for innovation, quality, and customer satisfaction.',
            'values' => [
                'Quality Construction',
                'Customer Satisfaction',
                'Innovation',
                'Integrity',
                'Sustainability'
            ],
            'established' => '2010',
            'projects_completed' => '50+',
            'happy_customers' => '1000+',
            'experience' => '15+ Years'
        ];
    }

    public function services() {
        $data = [
            'title' => 'Our Services - APS Dream Homes Pvt Ltd',
            'services' => $this->getServices()
        ];
        $this->view('pages/services', $data);
    }

    public function team() {
        $data = [
            'title' => 'Our Team - APS Dream Homes Pvt Ltd',
            'team_members' => $this->getTeamMembers()
        ];
        $this->view('pages/team', $data);
    }

    public function careers() {
        $data = [
            'title' => 'Careers - APS Dream Homes Pvt Ltd',
            'openings' => $this->getJobOpenings()
        ];
        $this->view('pages/careers', $data);
    }

    public function testimonials() {
        $data = [
            'title' => 'Testimonials - APS Dream Homes Pvt Ltd',
            'testimonials' => $this->getTestimonials()
        ];
        $this->view('pages/testimonials', $data);
    }

    public function blog() {
        $data = [
            'title' => 'Blog - APS Dream Homes Pvt Ltd',
            'blog_posts' => $this->getBlogPosts()
        ];
        $this->view('pages/blog', $data);
    }

    public function blogShow($slug) {
        $post = $this->getBlogPost($slug);
        if (!$post) {
            $this->notFound();
        }

        $data = [
            'title' => $post['title'] . ' - APS Dream Homes',
            'post' => $post
        ];
        $this->view('pages/blog_post', $data);
    }

    public function faq() {
        $data = [
            'title' => 'FAQ - APS Dream Homes Pvt Ltd',
            'faqs' => $this->getFAQs()
        ];
        $this->view('pages/faq', $data);
    }

    public function sitemap() {
        $data = [
            'title' => 'Sitemap - APS Dream Homes Pvt Ltd'
        ];
        $this->view('pages/sitemap', $data);
    }

    public function privacy() {
        $data = [
            'title' => 'Privacy Policy - APS Dream Homes Pvt Ltd'
        ];
        $this->view('pages/privacy', $data);
    }

    public function terms() {
        $data = [
            'title' => 'Terms & Conditions - APS Dream Homes Pvt Ltd'
        ];
        $this->view('pages/terms', $data);
    }

    private function getServices() {
        return [
            [
                'icon' => 'fas fa-home',
                'title' => 'Residential Projects',
                'description' => 'Premium residential plots and apartments with modern amenities and strategic locations.',
                'features' => ['Prime Locations', 'Modern Amenities', 'Quality Construction', 'Timely Possession']
            ],
            [
                'icon' => 'fas fa-building',
                'title' => 'Commercial Properties',
                'description' => 'Commercial spaces designed for businesses with excellent connectivity and infrastructure.',
                'features' => ['Strategic Locations', 'Modern Infrastructure', 'High ROI', 'Flexible Spaces']
            ],
            [
                'icon' => 'fas fa-map',
                'title' => 'Land Development',
                'description' => 'Comprehensive land development services from acquisition to plot demarcation.',
                'features' => ['Site Analysis', 'Legal Compliance', 'Infrastructure Development', 'Plot Division']
            ],
            [
                'icon' => 'fas fa-handshake',
                'title' => 'Property Consultation',
                'description' => 'Expert consultation services for property investment and development decisions.',
                'features' => ['Market Analysis', 'Investment Guidance', 'Legal Assistance', 'Documentation Support']
            ]
        ];
    }

    private function getTeamMembers() {
        return [
            [
                'name' => 'Rajesh Kumar',
                'position' => 'Managing Director',
                'experience' => '15+ Years',
                'specialization' => 'Real Estate Development',
                'image' => '/assets/images/team/rajesh-kumar.jpg',
                'bio' => 'Visionary leader with extensive experience in real estate development and project management.'
            ],
            [
                'name' => 'Priya Sharma',
                'position' => 'Head of Sales',
                'experience' => '10+ Years',
                'specialization' => 'Sales & Marketing',
                'image' => '/assets/images/team/priya-sharma.jpg',
                'bio' => 'Dynamic sales professional with expertise in customer relationship management and market strategy.'
            ],
            [
                'name' => 'Amit Patel',
                'position' => 'Project Manager',
                'experience' => '12+ Years',
                'specialization' => 'Project Execution',
                'image' => '/assets/images/team/amit-patel.jpg',
                'bio' => 'Experienced project manager ensuring timely delivery and quality construction standards.'
            ]
        ];
    }

    private function getJobOpenings() {
        return [
            [
                'title' => 'Sales Executive',
                'department' => 'Sales',
                'location' => 'Gorakhpur',
                'type' => 'Full-time',
                'experience' => '2-5 years',
                'description' => 'Looking for dynamic sales professionals to join our growing team.',
                'requirements' => ['Graduate degree', 'Real estate experience preferred', 'Excellent communication skills'],
                'salary' => '₹3-5 LPA'
            ],
            [
                'title' => 'Project Engineer',
                'department' => 'Engineering',
                'location' => 'Lucknow',
                'type' => 'Full-time',
                'experience' => '3-7 years',
                'description' => 'Civil engineers required for project execution and quality control.',
                'requirements' => ['B.Tech Civil', 'Site execution experience', 'AutoCAD knowledge'],
                'salary' => '₹4-6 LPA'
            ]
        ];
    }

    private function getTestimonials() {
        return [
            [
                'name' => 'Suresh Gupta',
                'location' => 'Gorakhpur',
                'project' => 'Suryoday Colony',
                'rating' => 5,
                'testimonial' => 'Excellent quality construction and timely possession. The amenities provided are world-class.',
                'image' => '/assets/images/testimonials/suresh-gupta.jpg'
            ],
            [
                'name' => 'Anita Singh',
                'location' => 'Lucknow',
                'project' => 'Shyam City',
                'rating' => 5,
                'testimonial' => 'Great investment opportunity with excellent returns. The location is perfect for my family.',
                'image' => '/assets/images/testimonials/anita-singh.jpg'
            ]
        ];
    }

    private function getBlogPosts() {
        return [
            [
                'title' => 'Real Estate Investment Trends in 2024',
                'slug' => 'real-estate-investment-trends-2024',
                'excerpt' => 'Discover the latest trends shaping the real estate market in India.',
                'author' => 'Rajesh Kumar',
                'date' => '2024-01-15',
                'image' => '/assets/images/blog/investment-trends.jpg',
                'category' => 'Investment'
            ],
            [
                'title' => 'Benefits of Investing in Gorakhpur Real Estate',
                'slug' => 'benefits-gorakhpur-real-estate',
                'excerpt' => 'Why Gorakhpur is becoming a prime destination for property investment.',
                'author' => 'Priya Sharma',
                'date' => '2024-01-10',
                'image' => '/assets/images/blog/gorakhpur-benefits.jpg',
                'category' => 'Market Analysis'
            ]
        ];
    }

    private function getBlogPost($slug) {
        $posts = $this->getBlogPosts();
        foreach ($posts as $post) {
            if ($post['slug'] === $slug) {
                return array_merge($post, [
                    'content' => 'Full blog content would be here...',
                    'tags' => ['real estate', 'investment', 'gorakhpur']
                ]);
            }
        }
        return null;
    }

    private function getFAQs() {
        return [
            [
                'question' => 'How can I book a property?',
                'answer' => 'You can book a property by visiting our office or contacting our sales team. We require a booking amount and necessary documents.'
            ],
            [
                'question' => 'What documents are required for property purchase?',
                'answer' => 'Required documents include ID proof, address proof, PAN card, passport size photographs, and income proof for loan purposes.'
            ],
            [
                'question' => 'Do you provide home loans?',
                'answer' => 'Yes, we have tie-ups with major banks and financial institutions to provide home loan assistance to our customers.'
            ],
            [
                'question' => 'What is the possession timeline?',
                'answer' => 'Possession timeline varies by project. Please check the specific project details or contact our sales team for accurate information.'
            ]
        ];
    }
}
