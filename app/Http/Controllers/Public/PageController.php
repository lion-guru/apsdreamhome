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

class PageController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Display homepage
     */
    public function index()
    {
        // Set page data
        $this->data['page_title'] = 'Home - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL]
        ];

        // Get featured properties
        $this->data['featured_properties'] = $this->getFeaturedProperties();

        // Get locations for search dropdown
        $this->data['locations'] = $this->getLocations();

        // Get company statistics
        $this->data['company_stats'] = $this->getCompanyStats();

        // Get counts for home page
        $this->data['counts'] = [
            'total' => $this->data['company_stats']['total_properties'],
            'agents' => 50 // Mock or fetch real count
        ];

        // Fetch published news using the Model
        // Use getPublished() directly as it handles missing columns gracefully
        $this->data['news'] = News::getPublished(3);

        // Render the homepage
        $this->render('pages/home');
    }

    /**
     * Get featured properties for homepage
     */
    private function getFeaturedProperties()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.*, 
                    (SELECT image_path FROM property_images WHERE property_id = p.id LIMIT 1) as primary_image
                FROM properties p 
                WHERE p.is_featured = 1 AND p.status = 'active'
                ORDER BY p.created_at DESC 
                LIMIT 6
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
            $stmt = $this->db->prepare("SELECT DISTINCT city FROM properties WHERE status = 'active' ORDER BY city ASC");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get company statistics
     */
    private function getCompanyStats()
    {
        return [
            'total_properties' => 1200,
            'happy_clients' => 850,
            'years_experience' => 15,
            'awards_won' => 25
        ];
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
     * Display Services page
     */
    public function services()
    {
        // Set page data
        $this->data['page_title'] = 'Our Services - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Services', 'url' => BASE_URL . 'services']
        ];

        // Fetch services using the Model
        $this->data['services'] = Service::query()->where('status', 'active')->get();

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
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Team', 'url' => BASE_URL . 'team']
        ];

        // Fetch team members
        $this->data['team_members'] = TeamMember::query()->where('status', 'active')->get();

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

        try {
            // Fetch gallery categories
            $this->data['categories'] = Gallery::query()
                ->select('category')
                ->distinct()
                ->where('status', 'active')
                ->orderBy('category')
                ->get();

            // Fetch gallery images
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
     * Display Resell page
     */
    public function resell()
    {
        $this->data['page_title'] = 'Resell Properties - ' . APP_NAME;
        $this->render('pages/resell');
    }

    /**
     * Display Careers page
     */
    public function careers()
    {
        $this->data['page_title'] = 'Careers - ' . APP_NAME;

        // Fetch active careers using the Model
        $this->data['careers'] = Career::query()->where('status', 'active')->get();

        $this->render('pages/careers');
    }

    /**
     * Display News page
     */
    public function news()
    {
        $this->data['page_title'] = 'News & Updates - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'News', 'url' => BASE_URL . 'news']
        ];

        // Pagination and Filtering parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $category = isset($_GET['category']) ? $_GET['category'] : 'all';
        $limit = 9;
        $offset = ($page - 1) * $limit;

        // Get data from Model
        $this->data['news_items'] = News::getPublished($limit, $offset, $category);
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
        $this->render('pages/blog');
    }

    /**
     * Display individual Blog post
     */
    public function blogShow($slug = null)
    {
        $this->data['page_title'] = 'Blog Article - ' . APP_NAME;
        $this->data['slug'] = $slug;
        $this->render('pages/blog_detail');
    }

    /**
     * Display FAQ page
     */
    public function faq()
    {
        $this->data['page_title'] = 'FAQs - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'FAQ', 'url' => BASE_URL . 'faq']
        ];

        try {
            // Fetch categories
            $this->data['categories'] = Faq::query()
                ->select('category')
                ->distinct()
                ->where('status', 'active')
                ->orderBy('category')
                ->get();

            // Fetch FAQs
            $query = Faq::query()->where('status', 'active');

            if (isset($_GET['category']) && $_GET['category'] !== 'all') {
                $query->where('category', $_GET['category']);
            }

            $faqs = $query->orderBy('display_order', 'DESC')->get();

            // Group FAQs
            $grouped_faqs = [];
            foreach ($faqs as $faq) {
                // Handle both object and array access
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
     * Display Downloads page
     */
    public function downloads()
    {
        $this->data['page_title'] = 'Downloads - ' . APP_NAME;
        $this->render('pages/downloads');
    }

    /**
     * Display Sitemap page
     */
    public function sitemap()
    {
        $this->data['page_title'] = 'Sitemap - ' . APP_NAME;
        $this->render('pages/sitemap');
    }

    /**
     * Display Privacy Policy
     */
    public function privacy()
    {
        $this->data['page_title'] = 'Privacy Policy - ' . APP_NAME;
        $this->render('pages/privacy_policy');
    }

    /**
     * Display Terms of Service
     */
    public function terms()
    {
        $this->data['page_title'] = 'Terms of Service - ' . APP_NAME;
        $this->render('pages/terms_of_service');
    }
}
