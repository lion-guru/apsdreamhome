<?php

/**
 * Page Controller
 * Handles static pages like About, Contact, etc.
 */

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;
use App\Models\News;
use App\Models\Career;
use App\Models\TeamMember;
use App\Models\Service;
use App\Models\Gallery;
use App\Models\Faq;
use App\Models\Feedback;
use App\Models\ResellProperty;
use App\Models\Download;

class PageController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Display Company Projects page
     */
    public function companyProjects()
    {
        $this->data['page_title'] = 'Company Projects & Portfolio - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Projects', 'url' => BASE_URL . 'company-projects']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        // Add extra JS
        $this->data['extra_js'] = '
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const searchInput = document.getElementById("searchInput");
                    if(searchInput) {
                        searchInput.addEventListener("keyup", function() {
                            let searchText = this.value.toLowerCase();
                            let accordionItems = document.querySelectorAll(".accordion-item");
                            
                            accordionItems.forEach(item => {
                                let question = item.querySelector(".accordion-button").textContent.toLowerCase();
                                let answer = item.querySelector(".accordion-body").textContent.toLowerCase();
                                
                                if (question.includes(searchText) || answer.includes(searchText)) {
                                    item.style.display = "block";
                                } else {
                                    item.style.display = "none";
                                }
                            });
                        });
                    }
                });
            </script>
        ';

        // Get company projects from database
        $company_projects = [];
        try {
            $projects_query = "
                SELECT
                    cp.*,
                    p.title as property_title,
                    p.price,
                    p.image_url,
                    p.location,
                    p.type,
                    COUNT(cp.id) as project_count
                FROM company_projects cp
                LEFT JOIN properties p ON cp.property_id = p.id
                GROUP BY cp.id
                ORDER BY cp.created_at DESC
            ";
            $stmt = $this->db->prepare($projects_query);
            $stmt->execute();
            $company_projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Company projects fetch error: ' . $e->getMessage());
        }

        // Get project statistics
        $project_stats = [
            'total' => count($company_projects),
            'completed' => 0,
            'ongoing' => 0,
            'upcoming' => 0,
            'total_value' => 0
        ];

        foreach ($company_projects as $project) {
            if ($project['status'] == 'completed') $project_stats['completed']++;
            if ($project['status'] == 'ongoing') $project_stats['ongoing']++;
            if ($project['status'] == 'upcoming') $project_stats['upcoming']++;
            $project_stats['total_value'] += $project['budget'] ?? 0;
        }

        $this->data['company_projects'] = $company_projects;
        $this->data['project_stats'] = $project_stats;

        $this->render('pages/company_projects');
    }

    /**
     * Display About page
     */
    public function about()
    {
        // Set page data
        $this->data['page_title'] = 'About Us - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'About', 'url' => BASE_URL . 'about']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        // Add company stats
        $this->data['company_stats'] = $this->getCompanyStats();

        // Render the about page
        $this->render('pages/about');
    }

    /**
     * Display Contact page
     */
    public function contact()
    {
        // Set page data
        $this->data['page_title'] = 'Contact Us - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Contact', 'url' => BASE_URL . 'contact']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        // Contact information
        $this->data['contact_info'] = [
            'phone' => '+91-1234567890',
            'email' => 'info@apsdreamhome.com',
            'address' => '123 Main Street, Gorakhpur, Uttar Pradesh - 273001',
            'hours' => 'Mon - Sat: 9:00 AM - 8:00 PM, Sun: 10:00 AM - 6:00 PM'
        ];

        // Render the contact page
        $this->render('pages/contact');
    }

    /**
     * Process Contact form
     */
    public function processContact()
    {
        // Basic validation
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $message = $_POST['message'] ?? '';

        if (empty($name) || empty($email) || empty($message)) {
            $this->setFlash('error', 'Please fill in all required fields.');
            $this->redirect('/contact');
            return;
        }

        // Logic to save message or send email would go here
        // For now, just simulate success

        $this->setFlash('success', 'Thank you for contacting us. We will get back to you soon.');
        $this->redirect('/contact');
    }

    /**
     * Display Services page
     */
    public function services()
    {
        // Set page data
        $this->data['page_title'] = 'Our Services - ' . APP_NAME;
        $this->data['page_description'] = 'Discover our comprehensive range of real estate services designed to help you find your perfect property';
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Services', 'url' => BASE_URL . 'services']
        ];

        // Fetch services using the Model
        $this->data['services'] = Service::query()->where('status', 'active')->get();

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        // Render the services page
        $this->render('pages/services');
    }

    /**
     * Display Team page
     */
    public function team()
    {
        // Set page data
        $this->data['page_title'] = 'Our Team - ' . APP_NAME;
        $this->data['page_description'] = 'Meet the experienced team behind APS Dream Home. Our real estate professionals are dedicated to helping you find your perfect property.';
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Team', 'url' => BASE_URL . 'team']
        ];

        // Fetch team members
        $this->data['team_members'] = TeamMember::query()->where('status', 'active')->get();

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        // Render the team page
        $this->render('pages/team');
    }

    /**
     * Display Gallery page
     */
    public function gallery()
    {
        $this->data['page_title'] = 'Gallery - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Gallery', 'url' => BASE_URL . 'gallery']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '
            <link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
        ';

        // Add extra JS
        $this->data['extra_js'] = '<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>';

        try {
            // Get gallery categories
            $this->data['categories'] = Gallery::query()
                ->select('category')
                ->distinct()
                ->where('status', 'active')
                ->orderBy('category', 'ASC')
                ->get();

            // Get gallery images
            $query = Gallery::query()->where('status', 'active');

            if (isset($_GET['category']) && $_GET['category'] !== 'all') {
                $query->where('category', $_GET['category']);
            }

            $this->data['images'] = $query->orderBy('created_at', 'DESC')->get();
            $this->data['current_category'] = $_GET['category'] ?? 'all';
        } catch (\Exception $e) {
            $this->data['categories'] = [];
            $this->data['images'] = [];
            $this->data['current_category'] = 'all';
        }

        $this->render('pages/gallery');
    }

    /**
     * Display Careers page
     */
    public function careers()
    {
        $this->data['page_title'] = 'Careers - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Careers', 'url' => BASE_URL . 'careers']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        try {
            $this->data['jobs'] = Career::query()->where('status', 'active')->orderBy('created_at', 'DESC')->get();
        } catch (\Exception $e) {
            $this->data['jobs'] = [];
        }

        $this->render('pages/careers');
    }

    /**
     * Display News page
     */
    public function news()
    {
        $this->data['page_title'] = 'News - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'News', 'url' => BASE_URL . 'news']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $limit = 9;
        $offset = ($page - 1) * $limit;

        // Get data from Model
        $this->data['news'] = News::getPublished($limit, $offset, $category);
        $this->data['categories'] = News::getCategories();
        $total_items = News::countPublished($category);

        // Pagination data
        $this->data['pagination'] = [
            'current_page' => $page,
            'total_pages' => ceil($total_items / $limit),
            'current_category' => $category,
            'total_items' => $total_items
        ];

        $this->render('pages/news');
    }

    /**
     * Display Testimonials page
     */
    public function testimonials()
    {
        $this->data['page_title'] = 'Testimonials - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Testimonials', 'url' => BASE_URL . 'testimonials']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        try {
            $this->data['testimonials'] = Feedback::query()
                ->where('status', 'approved')
                ->orderBy('created_at', 'DESC')
                ->get();
        } catch (\Exception $e) {
            $this->data['testimonials'] = [];
        }

        $this->render('pages/testimonials');
    }

    /**
     * Display Blog landing page
     */
    public function blog()
    {
        $this->data['page_title'] = 'Blog - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Blog', 'url' => BASE_URL . 'blog']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        try {
            // Get blog posts
            $this->data['blog_posts'] = News::getPublished();

            // Get categories for filter
            $this->data['categories'] = News::getCategories();
        } catch (\Exception $e) {
            error_log('Blog page database error: ' . $e->getMessage());
            $this->data['blog_posts'] = [];
            $this->data['categories'] = [];
        }

        $this->render('pages/blog');
    }

    /**
     * Display individual Blog post
     */
    public function blogShow($slug)
    {
        $this->data['page_title'] = 'Blog Post - ' . APP_NAME;
        // Logic to fetch post by slug would go here

        $this->render('pages/blog_detail');
    }

    /**
     * Display FAQ page
     */
    public function faq()
    {
        $this->data['page_title'] = 'FAQ - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'FAQ', 'url' => BASE_URL . 'faq']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        try {
            // Get FAQ categories
            $this->data['categories'] = Faq::query()
                ->select('category')
                ->distinct()
                ->where('status', 'active')
                ->orderBy('category', 'ASC')
                ->get();

            // Get FAQs
            $query = Faq::query()->where('status', 'active');

            if (isset($_GET['category']) && $_GET['category'] !== 'all') {
                $query->where('category', $_GET['category']);
            }

            $faqs = $query->orderBy('category', 'ASC')->orderBy('priority', 'ASC')->get();

            // Group FAQs
            $grouped_faqs = [];
            foreach ($faqs as $faq) {
                // Handle both object and array
                $category = is_object($faq) ? $faq->category : $faq['category'];
                $grouped_faqs[$category][] = $faq;
            }

            $this->data['grouped_faqs'] = $grouped_faqs;
            $this->data['current_category'] = $_GET['category'] ?? 'all';
        } catch (\Exception $e) {
            $this->data['categories'] = [];
            $this->data['grouped_faqs'] = [];
            $this->data['current_category'] = 'all';
        }

        $this->render('pages/faq');
    }

    /**
     * Display Resell Properties page
     */
    public function resell()
    {
        $this->data['page_title'] = 'Resell Properties - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Resell Properties', 'url' => BASE_URL . 'resell']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        // Get filters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'city' => $_GET['city'] ?? '',
            'type' => $_GET['type'] ?? '',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? ''
        ];

        try {
            // Fetch properties
            $this->data['properties'] = ResellProperty::getActiveWithUser($filters);

            // Fetch filter options
            $this->data['cities'] = ResellProperty::getDistinct('city', ['status' => 'approved']);
            $this->data['property_types'] = ResellProperty::getDistinct('property_type', ['status' => 'approved']);
            $this->data['price_range'] = ResellProperty::getPriceRange(['status' => 'approved']);
        } catch (\Exception $e) {
            error_log("Error loading resell properties: " . $e->getMessage());
            $this->data['properties'] = [];
            $this->data['cities'] = [];
            $this->data['property_types'] = [];
        }

        // Pass filters back to view
        $this->data['filters'] = $filters;

        $this->render('pages/resell');
    }

    /**
     * Display legal services page
     */
    public function legalServices()
    {
        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        $this->data['page_title'] = 'Legal Services - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Legal Services', 'url' => BASE_URL . 'legal-services']
        ];

        try {
            // Get legal services
            $this->data['services'] = Service::query()->where('type', 'legal')->where('status', 'active')->get();

            // Get legal team members
            $this->data['lawyers'] = TeamMember::query()->where('department', 'legal')->where('status', 'active')->get();

            // Get legal services FAQs
            $this->data['faqs'] = Faq::query()->where('category', 'legal')->where('status', 'active')->get();
        } catch (\Exception $e) {
            $this->data['services'] = [];
            $this->data['lawyers'] = [];
            $this->data['faqs'] = [];
        }

        $this->render('pages/legal_services');
    }


    /**
     * Display Downloads page
     */
    public function downloads()
    {
        $this->data['page_title'] = 'Downloads - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Downloads', 'url' => BASE_URL . 'downloads']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        // Initialize data
        $this->data['categories'] = [];
        $this->data['downloads'] = [];
        $this->data['pagination'] = [
            'current_page' => 1,
            'total_pages' => 1
        ];

        try {
            // Fetch categories
            $categories_raw = Download::getCategories();
            foreach ($categories_raw as $cat) {
                // Handle both object and array
                $category_name = is_object($cat) ? $cat->category : $cat['category'];
                if ($category_name) {
                    $this->data['categories'][] = $category_name;
                }
            }

            // Pagination logic
            $items_per_page = 12;
            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            if ($current_page < 1) $current_page = 1;

            $current_category = isset($_GET['category']) ? $_GET['category'] : 'all';

            // Count total items
            $total_items = Download::countActive($current_category);
            $total_pages = ceil($total_items / $items_per_page);
            if ($total_pages < 1) $total_pages = 1;

            $this->data['pagination']['current_page'] = $current_page;
            $this->data['pagination']['total_pages'] = $total_pages;

            // Fetch downloads
            $offset = ($current_page - 1) * $items_per_page;
            $this->data['downloads'] = Download::getActive($current_category, $items_per_page, $offset);
        } catch (\Exception $e) {
            error_log("Error fetching downloads: " . $e->getMessage());
        }

        $this->render('pages/downloads');
    }

    /**
     * Display Sitemap page
     */
    public function sitemap()
    {
        $this->data['page_title'] = 'Sitemap - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Sitemap', 'url' => BASE_URL . 'sitemap']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        $this->render('pages/sitemap');
    }

    /**
     * Display Privacy Policy
     */
    public function privacy()
    {
        $this->data['page_title'] = 'Privacy Policy - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Privacy Policy', 'url' => BASE_URL . 'privacy-policy']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        $this->render('pages/privacy_policy');
    }

    /**
     * Display Terms of Service
     */
    public function terms()
    {
        $this->data['page_title'] = 'Terms of Service - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Terms of Service', 'url' => BASE_URL . 'terms-of-service']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '<link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">';

        $this->render('pages/terms-of-service');
    }

    /**
     * Display Colonies page
     */
    public function colonies()
    {
        $this->data['page_title'] = 'Our Colonies - ' . APP_NAME;
        $this->data['page_description'] = 'Explore APS Dream Homes premium colonies and real estate projects in Gorakhpur, Lucknow and across Uttar Pradesh';

        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Colonies', 'url' => BASE_URL . 'colonies']
        ];

        // Add extra CSS
        $this->data['extra_css'] = '
            <link rel="stylesheet" href="' . BASE_URL . 'public/css/pages.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
        ';

        // Add extra JS
        $this->data['extra_js'] = '
            <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
            <script src="' . get_asset_url('js/colonies.js') . '"></script>
        ';

        // Sample colony data (replace with database queries later)
        $colonies = [
            [
                'id' => 1,
                'name' => 'APS Dream City Gorakhpur',
                'location' => 'Gorakhpur, Uttar Pradesh',
                'total_area' => '50 Acres',
                'developed_area' => '35 Acres',
                'total_plots' => 450,
                'available_plots' => 120,
                'starting_price' => '₹15,00,000',
                'completion_status' => 'Phase 2 Ongoing',
                'amenities' => ['Club House', 'Swimming Pool', 'Gym', 'Children Play Area', '24/7 Security', 'Power Backup'],
                'image' => get_asset_url('images/hero-1.jpg'), // Updated path
                'description' => 'Premium residential colony with modern amenities and excellent connectivity.',
                'highlights' => ['Prime Location', 'Modern Infrastructure', 'Investment Opportunity']
            ],
            [
                'id' => 2,
                'name' => 'APS Royal Residency',
                'location' => 'Lucknow, Uttar Pradesh',
                'total_area' => '25 Acres',
                'developed_area' => '20 Acres',
                'total_plots' => 200,
                'available_plots' => 45,
                'starting_price' => '₹25,00,000',
                'completion_status' => 'Phase 1 Complete',
                'amenities' => ['Community Hall', 'Jogging Track', 'Landscaped Gardens', 'Security', 'Water Supply'],
                'image' => get_asset_url('images/hero-2.jpg'), // Updated path
                'description' => 'Luxury residential project in the heart of Lucknow with world-class facilities.',
                'highlights' => ['Premium Location', 'High Appreciation', 'Modern Design']
            ],
            [
                'id' => 3,
                'name' => 'APS Green Valley',
                'location' => 'Kunraghat, Gorakhpur',
                'total_area' => '30 Acres',
                'developed_area' => '15 Acres',
                'total_plots' => 300,
                'available_plots' => 80,
                'starting_price' => '₹12,00,000',
                'completion_status' => 'Development Started',
                'amenities' => ['Green Spaces', 'Community Center', 'Playground', 'Security', 'Basic Infrastructure'],
                'image' => get_asset_url('images/hero-3.jpg'), // Updated path
                'description' => 'Eco-friendly residential colony with abundant green spaces and natural surroundings.',
                'highlights' => ['Eco-Friendly', 'Affordable Luxury', 'Natural Environment']
            ]
        ];

        // Colony statistics for display
        $colony_stats = [
            'total_colonies' => count($colonies),
            'total_area' => '105 Acres',
            'total_plots' => array_sum(array_column($colonies, 'total_plots')),
            'plots_sold' => array_sum(array_column($colonies, 'total_plots')) - array_sum(array_column($colonies, 'available_plots')),
            'cities_covered' => 3
        ];

        $this->data['colonies'] = $colonies;
        $this->data['colony_stats'] = $colony_stats;

        $this->render('pages/colonies');
    }

    /**
     * Get featured properties for homepage
     */
    private function getFeaturedProperties()
    {
        try {
            // Check if db connection exists
            if (!isset($this->db)) {
                global $conn;
                if (isset($conn)) {
                    $stmt = $conn->prepare("
                        SELECT 
                            p.*, 
                            (SELECT image_path FROM property_images WHERE property_id = p.id LIMIT 1) as primary_image
                        FROM properties p 
                        WHERE p.featured = 1 AND p.status = 'available'
                        ORDER BY p.created_at DESC 
                        LIMIT 6
                    ");
                    $stmt->execute();
                    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                }
                return [];
            }

            // If using PDO or other DB abstraction in future
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get locations for search dropdown
     */
    private function getLocations()
    {
        try {
            if (!isset($this->db)) {
                global $conn;
                if (isset($conn)) {
                    $stmt = $conn->prepare("SELECT DISTINCT location FROM properties WHERE status = 'available' ORDER BY location");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $locations = [];
                    while ($row = $result->fetch_assoc()) {
                        $locations[] = $row['location'];
                    }
                    return $locations;
                }
                return [];
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get company statistics
     */
    private function getStats()
    {
        return [
            'total_properties' => 150,
            'happy_clients' => 850,
            'years_experience' => 12,
            'awards_won' => 15
        ];
    }
}
