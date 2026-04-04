<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

class PageController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    // Home Page
    public function home()
    {
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
            [
                'id' => 3,
                'title' => 'Braj Radha Nagri',
                'location' => 'Gorakhpur',
                'price' => '₹6.5 Lakhs',
                'image' => 'brajradha.jpg',
                'type' => 'Residential',
                'status' => 'Available'
            ],
            [
                'id' => 4,
                'title' => 'Budh Bihar Colony',
                'location' => 'Kushinagar',
                'price' => '₹5.5 Lakhs',
                'image' => 'budhbihar.jpg',
                'type' => 'Residential',
                'status' => 'Available'
            ],
        ];

        $data = [
            'page_title' => 'APS Dream Home - Premium Real Estate in UP',
            'page_description' => 'Discover premium residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh',
            'hero_stats' => $hero_stats,
            'featured_properties' => $featured_properties,
        ];

        $this->render('pages/home', $data);
    }

    // Contact Us Page
    public function contact()
    {
        $success = false;
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? 'Contact Form Submission');
            $message = trim($_POST['message'] ?? '');

            if (empty($name) || empty($email) || empty($message)) {
                $error = 'Please fill in all required fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                try {
                    $stmt = $this->db->prepare("INSERT INTO contacts (name, email, phone, subject, message, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $stmt->execute([$name, $email, $phone, $subject, $message, $ip]);
                    $success = true;
                    $_POST = [];
                } catch (\Exception $e) {
                    $error = 'Failed to submit. Please try again or call us directly.';
                    error_log("Contact form error: " . $e->getMessage());
                }
            }
        }

        $data = [
            'page_title' => 'Contact Us - APS Dream Home',
            'page_description' => 'Get in touch with APS Dream Home',
            'contact_success' => $success,
            'contact_error' => $error
        ];
        $this->render('pages/contact', $data);
    }

    // About Us Page
    public function about()
    {
        $data = [
            'page_title' => 'About Us - APS Dream Home',
            'page_description' => 'Learn more about APS Dream Home'
        ];
        $this->render('pages/about', $data);
    }

    // Properties Page
    public function properties()
    {
        try {
            // Get all properties with sample data
            $properties = [
                [
                    'id' => 1,
                    'title' => 'Suyoday Colony',
                    'location' => 'Gorakhpur',
                    'price' => '₹7.5 Lakhs',
                    'image' => 'suyoday.jpg',
                    'type' => 'Residential',
                    'status' => 'Available',
                    'area' => '1200 sq ft',
                    'bedrooms' => '2 BHK',
                    'description' => 'Premium residential plots with modern infrastructure and amenities.'
                ],
                [
                    'id' => 2,
                    'title' => 'Raghunat Nagri',
                    'location' => 'Gorakhpur',
                    'price' => '₹8.5 Lakhs',
                    'image' => 'raghunat.jpg',
                    'type' => 'Residential',
                    'status' => 'Available',
                    'area' => '1500 sq ft',
                    'bedrooms' => '3 BHK',
                    'description' => 'Premium residential plots in developing area with all facilities.'
                ],
                [
                    'id' => 3,
                    'title' => 'Braj Radha Nagri',
                    'location' => 'Gorakhpur',
                    'price' => '₹6.5 Lakhs',
                    'image' => 'brajradha.jpg',
                    'type' => 'Residential',
                    'status' => 'Planned',
                    'area' => '1000 sq ft',
                    'bedrooms' => '2 BHK',
                    'description' => 'Affordable residential plots with basic amenities.'
                ],
                [
                    'id' => 4,
                    'title' => 'Budh Bihar Colony',
                    'location' => 'Kushinagar',
                    'price' => '₹5.5 Lakhs',
                    'image' => 'budhbihar.jpg',
                    'type' => 'Residential',
                    'status' => 'Ongoing',
                    'area' => '1100 sq ft',
                    'bedrooms' => '2 BHK',
                    'description' => 'Integrated township at Premwaliya with modern facilities.'
                ],
                [
                    'id' => 5,
                    'title' => 'Awadhpuri',
                    'location' => 'Lucknow',
                    'price' => '₹12 Lakhs',
                    'image' => 'awadhpuri.jpg',
                    'type' => 'Residential',
                    'status' => 'Coming Soon',
                    'area' => '2000 sq ft',
                    'bedrooms' => '4 BHK',
                    'description' => '20 bigha premium project at Safadarganj with luxury amenities.'
                ],
                [
                    'id' => 6,
                    'title' => 'Commercial Complex',
                    'location' => 'Gorakhpur',
                    'price' => '₹25 Lakhs',
                    'image' => 'commercial.jpg',
                    'type' => 'Commercial',
                    'status' => 'Available',
                    'area' => '800 sq ft',
                    'bedrooms' => 'N/A',
                    'description' => 'Prime commercial space in heart of the city.'
                ]
            ];

            // Get filter options
            $property_types = ['All Types', 'Residential', 'Commercial', 'Land', 'Villa', 'Apartment'];
            $locations = ['All Locations', 'Gorakhpur', 'Lucknow', 'Kanpur', 'Varanasi', 'Allahabad'];
            $price_ranges = ['Any Price', 'Under ₹10L', '₹10L - ₹50L', '₹50L - ₹1Cr', '₹1Cr - ₹5Cr', 'Above ₹5Cr'];
            $bedrooms = ['Any', '1 BHK', '2 BHK', '3 BHK', '4 BHK', '5+ BHK'];

            // Breadcrumb data
            $breadcrumbs = [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Properties', 'url' => BASE_URL . '/properties']
            ];

            $data = [
                'page_title' => 'Properties - APS Dream Home',
                'page_description' => 'Browse our premium residential and commercial properties',
                'properties' => $properties,
                'property_types' => $property_types,
                'locations' => $locations,
                'price_ranges' => $price_ranges,
                'bedrooms' => $bedrooms,
                'breadcrumbs' => $breadcrumbs
            ];

            $this->render('pages/properties', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading properties page', $e->getMessage());
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

    // Team
    public function team()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM team_members WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
            $stmt->execute();
            $team_members = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            $team_members = [];
            error_log("Team error: " . $e->getMessage());
        }
        
        $data = [
            'page_title' => 'Our Team - APS Dream Home',
            'page_description' => 'Meet the team behind APS Dream Home',
            'team_members' => $team_members
        ];
        $this->render('pages/team', $data);
    }

    // Careers
    public function careers()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM careers WHERE status = 'active' ORDER BY created_at DESC");
            $stmt->execute();
            $careers = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            $careers = [];
            error_log("Careers error: " . $e->getMessage());
        }
        
        $data = [
            'page_title' => 'Careers - APS Dream Home',
            'page_description' => 'Join our team at APS Dream Home',
            'careers' => $careers
        ];
        $this->render('pages/careers', $data);
    }

    // Services
    public function services()
    {
        $data = [
            'page_title' => 'Our Services - APS Dream Home',
            'page_description' => 'Services offered by APS Dream Home'
        ];
        $this->render('pages/services', $data);
    }

    // Blog
    public function blog()
    {
        $blog_posts = [
            [
                'id' => 1,
                'title' => 'The Future of Real Estate: Trends to Watch in 2025',
                'excerpt' => 'As we look ahead, the real estate market is poised for significant transformation. From sustainable housing to the integration of AI, here are the key trends that will shape the industry in 2025 and beyond.',
                'featured_image' => 'assets/images/blog/blog-1.jpg',
                'category' => 'Market Trends',
                'read_time' => 5,
                'created_at' => '2024-03-15'
            ],
            [
                'id' => 2,
                'title' => 'A Step-by-Step Guide to Buying Your First Home',
                'excerpt' => 'Buying your first home is a major milestone. This comprehensive guide will walk you through every step of the process, from getting pre-approved for a mortgage to closing the deal.',
                'featured_image' => 'assets/images/blog/blog-2.jpg',
                'category' => 'Buying Guide',
                'read_time' => 8,
                'created_at' => '2024-03-10'
            ],
            [
                'id' => 3,
                'title' => 'Top 5 Interior Design Tips to Increase Your Home\'s Value',
                'excerpt' => 'A well-designed home not only looks great but can also significantly increase its market value. Discover our top 5 interior design tips to make your home more appealing to potential buyers.',
                'featured_image' => 'assets/images/blog/blog-3.jpg',
                'category' => 'Interior Design',
                'read_time' => 4,
                'created_at' => '2024-03-05'
            ],
        ];

        $categories = [
            ['category' => 'Market Trends'],
            ['category' => 'Buying Guide'],
            ['category' => 'Interior Design'],
            ['category' => 'Investment'],
        ];

        $data = [
            'page_title' => 'Blog - APS Dream Home',
            'page_description' => 'Latest news and articles from our blog',
            'blog_posts' => $blog_posts,
            'categories' => $categories
        ];
        $this->render('pages/blog', $data);
    }

    // Privacy Policy
    public function privacy()
    {
        $data = [
            'page_title' => 'Privacy Policy - APS Dream Home',
            'page_description' => 'Our privacy policy'
        ];
        $this->render('pages/privacy', $data);
    }

    // Resell
    public function resell()
    {
        try {
            $cities = $this->db->fetchAll("SELECT DISTINCT city FROM properties WHERE city IS NOT NULL AND city != '' ORDER BY city");
            $property_types = $this->db->fetchAll("SELECT DISTINCT type FROM properties WHERE type IS NOT NULL AND type != '' ORDER BY type");
        } catch (\Exception $e) {
            $cities = [];
            $property_types = [];
            error_log("Resell error: " . $e->getMessage());
        }
        
        $filters = [
            'search' => $_GET['search'] ?? '',
            'city' => $_GET['city'] ?? '',
            'type' => $_GET['type'] ?? '',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? ''
        ];
        
        $data = [
            'page_title' => 'Resell Property - APS Dream Home',
            'page_description' => 'Sell your property through APS Dream Home',
            'cities' => $cities,
            'property_types' => $property_types,
            'filters' => $filters
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
        $news_items = [
            (object)[
                'id' => 1,
                'title' => 'Grand Opening of Suyoday Colony',
                'summary' => 'APS Dream Home is proud to announce the grand opening of Suyoday Colony, our latest residential project in Gorakhpur. The opening ceremony will be held on April 15, 2024.',
                'image' => 'assets/images/news/news-1.jpg',
                'category' => 'Project Launch',
                'created_at' => '2024-03-20'
            ],
            (object)[
                'id' => 2,
                'title' => 'APS Dream Home Wins "Best Real Estate Developer" Award',
                'summary' => 'We are honored to receive the "Best Real Estate Developer" award for the year 2023. This award is a testament to our commitment to quality and customer satisfaction.',
                'image' => 'assets/images/news/news-2.jpg',
                'category' => 'Company News',
                'created_at' => '2024-03-18'
            ],
            (object)[
                'id' => 3,
                'title' => 'New Commercial Project Announced in Lucknow',
                'summary' => 'APS Dream Home is excited to announce a new commercial project in the heart of Lucknow. The project will feature state-of-the-art office spaces and retail outlets.',
                'image' => 'assets/images/news/news-3.jpg',
                'category' => 'Project Launch',
                'created_at' => '2024-03-15'
            ],
        ];

        $categories = ['Project Launch', 'Company News', 'Market Updates'];

        $pagination = [
            'current_page' => 1,
            'total_pages' => 1,
            'current_category' => 'all'
        ];

        $data = [
            'page_title' => 'News - APS Dream Home',
            'page_description' => 'Latest news and updates from APS Dream Home',
            'news_items' => $news_items,
            'categories' => $categories,
            'pagination' => $pagination,
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => '/'],
                ['title' => 'News', 'url' => '']
            ]
        ];
        $this->render('pages/news', $data);
    }

    // News View
    public function newsView($id = null)
    {
        $data = [
            'page_title' => 'News - APS Dream Home',
            'page_description' => 'View news article',
            'news_id' => $id
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
        try {
            $downloads = $this->db->fetchAll("SELECT * FROM downloads WHERE status = 'active' ORDER BY category, sort_order ASC");
        } catch (\Exception $e) {
            $downloads = [];
            error_log("Downloads error: " . $e->getMessage());
        }
        
        $data = [
            'page_title' => 'Downloads - APS Dream Home',
            'page_description' => 'Download brochures and documents from APS Dream Home',
            'downloads' => $downloads
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

    // Sitemap
    public function sitemap()
    {
        $data = [
            'page_title' => 'Sitemap - APS Dream Home',
            'page_description' => 'Complete sitemap of APS Dream Home website'
        ];
        $this->render('pages/sitemap', $data);
    }

    // FAQ (singular)
    public function faq()
    {
        return $this->faqs();
    }

    // Property Details
    public function propertyDetails($id = null)
    {
        $property = null;
        $property_images = [];
        $related_properties = [];

        if ($id) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM properties WHERE id = ? AND status = 'available' LIMIT 1");
                $stmt->execute([$id]);
                $property = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($property) {
                    $imgStmt = $this->db->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_featured DESC LIMIT 5");
                    $imgStmt->execute([$id]);
                    $property_images = $imgStmt->fetchAll(\PDO::FETCH_ASSOC);

                    $relStmt = $this->db->prepare("SELECT * FROM properties WHERE id != ? AND status = 'available' ORDER BY RAND() LIMIT 3");
                    $relStmt->execute([$id]);
                    $related_properties = $relStmt->fetchAll(\PDO::FETCH_ASSOC);
                }
            } catch (\Exception $e) {
                error_log("Property fetch error: " . $e->getMessage());
            }
        }

        $data = [
            'page_title' => $property ? ($property['title'] ?? 'Property') . ' - APS Dream Home' : 'Property Not Found',
            'page_description' => 'View property details',
            'property' => $property,
            'property_images' => $property_images,
            'related_properties' => $related_properties
        ];
        $this->render('properties/detail', $data);
    }

    // Projects List
    public function projects()
    {
        $data = [
            'page_title' => 'Our Projects - APS Dream Home',
            'page_description' => 'Explore our residential and commercial projects'
        ];
        $this->render('pages/company_projects', $data);
    }

    // Project Details
    public function projectDetails($slug = null)
    {
        $data = [
            'page_title' => 'Project Details - APS Dream Home',
            'page_description' => 'View project details',
            'project_slug' => $slug
        ];
        $this->render('pages/company_projects', $data);
    }

    // Gallery
    public function gallery()
    {
        $data = [
            'page_title' => 'Gallery - APS Dream Home',
            'page_description' => 'Photo and video gallery of our projects'
        ];
        $this->render('pages/gallery', $data);
    }

    // Gallery Project
    public function galleryProject($projectId = null)
    {
        $data = [
            'page_title' => 'Project Gallery - APS Dream Home',
            'page_description' => 'Project photo gallery',
            'project_id' => $projectId
        ];
        $this->render('gallery/project', $data);
    }

    // Blog Post
    public function blogPost($slug = null)
    {
        $data = [
            'page_title' => 'Blog Post - APS Dream Home',
            'page_description' => 'Read our latest blog post',
            'post_slug' => $slug
        ];
        $this->render('pages/blog-post', $data);
    }

    // Career Apply
    public function careerApply()
    {
        $data = [
            'page_title' => 'Apply for a Job - APS Dream Home',
            'page_description' => 'Submit your job application'
        ];
        $this->render('pages/career_apply', $data);
    }

    // Submit Career Application
    public function submitCareerApplication()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['success'] = 'Your application has been submitted successfully!';
            header('Location: ' . BASE_URL . '/careers');
            exit;
        }
        return $this->careerApply();
    }

    // Career Jobs
    public function careerJobs()
    {
        $data = [
            'page_title' => 'Job Openings - APS Dream Home',
            'page_description' => 'Current job openings at APS Dream Home'
        ];
        $this->render('pages/careers', $data);
    }

    // Career Job Details
    public function careerJobDetails($id = null)
    {
        $data = [
            'page_title' => 'Job Details - APS Dream Home',
            'page_description' => 'View job details',
            'job_id' => $id
        ];
        $this->render('pages/career_apply', $data);
    }

    // Suyoday Colony
    public function suyodayColony()
    {
        $data = [
            'page_title' => 'Suyoday Colony - APS Dream Home',
            'page_description' => 'Premium residential plots in Suyoday Colony, Gorakhpur'
        ];
        $this->render('pages/suyoday_colony', $data);
    }

    // Raghunat Nagri
    public function raghunatNagri()
    {
        $data = [
            'page_title' => 'Raghunat Nagri - APS Dream Home',
            'page_description' => 'Premium residential plots in Raghunat Nagri, Gorakhpur'
        ];
        $this->render('pages/rahunath_nagri', $data);
    }

    // Braj Radha Nagri
    public function brajRadhaNagri()
    {
        $data = [
            'page_title' => 'Braj Radha Nagri - APS Dream Home',
            'page_description' => 'Affordable residential plots in Braj Radha Nagri'
        ];
        $this->render('pages/budhacity', $data);
    }

    // Budh Bihar Colony
    public function budhBiharColony()
    {
        $data = [
            'page_title' => 'Budh Bihar Colony - APS Dream Home',
            'page_description' => 'Integrated township at Budh Bihar Colony, Kushinagar'
        ];
        $this->render('pages/budhacity', $data);
    }

    // Awadhpuri
    public function awadhpuri()
    {
        $data = [
            'page_title' => 'Awadhpuri - APS Dream Home',
            'page_description' => 'Premium project at Awadhpuri, Lucknow'
        ];
        $this->render('pages/budhacity', $data);
    }

    // WhatsApp Chat
    public function whatsappChat()
    {
        $data = [
            'page_title' => 'WhatsApp Chat - APS Dream Home',
            'page_description' => 'Connect with us on WhatsApp'
        ];
        $this->render('pages/whatsapp_chat', $data);
    }

    // Virtual Tour
    public function virtualTour()
    {
        $data = [
            'page_title' => 'Virtual Tour - APS Dream Home',
            'page_description' => 'Take a virtual tour of our properties'
        ];
        $this->render('pages/virtual_tour', $data);
    }

    // User AI Suggestions
    public function userAiSuggestions()
    {
        $data = [
            'page_title' => 'AI Suggestions - APS Dream Home',
            'page_description' => 'Personalized property suggestions powered by AI'
        ];
        $this->render('pages/user_ai_suggestions', $data);
    }

    // Support
    public function support()
    {
        $data = [
            'page_title' => 'Support - APS Dream Home',
            'page_description' => 'Get support from APS Dream Home team'
        ];
        $this->render('pages/support', $data);
    }

    // AI Valuation
    public function aiValuation()
    {
        $data = [
            'page_title' => 'AI Property Valuation - APS Dream Home',
            'page_description' => 'Get AI-powered property valuation'
        ];
        $this->render('pages/ai-valuation', $data);
    }

    // User Saved Searches
    public function userSavedSearches()
    {
        $data = [
            'page_title' => 'Saved Searches - APS Dream Home',
            'page_description' => 'Your saved property searches'
        ];
        $this->render('pages/user/saved_searches', $data);
    }

    // User Notifications
    public function userNotifications()
    {
        $data = [
            'page_title' => 'Notifications - APS Dream Home',
            'page_description' => 'Your notifications'
        ];
        $this->render('pages/user/notifications', $data);
    }

    // User Investments
    public function userInvestments()
    {
        $data = [
            'page_title' => 'My Investments - APS Dream Home',
            'page_description' => 'Track your property investments'
        ];
        $this->render('pages/user/investments', $data);
    }

    // User Edit Profile
    public function userEditProfile()
    {
        $data = [
            'page_title' => 'Edit Profile - APS Dream Home',
            'page_description' => 'Update your profile information'
        ];
        $this->render('pages/user/edit_profile', $data);
    }

    // Under Construction
    public function underConstruction()
    {
        $data = [
            'page_title' => 'Under Construction - APS Dream Home',
            'page_description' => 'This page is under construction'
        ];
        $this->render('pages/under_construction', $data);
    }

    // Thank You
    public function thankYou()
    {
        $data = [
            'page_title' => 'Thank You - APS Dream Home',
            'page_description' => 'Thank you for contacting us'
        ];
        $this->render('pages/thank_you', $data);
    }

    // Coming Soon
    public function comingSoon()
    {
        $data = [
            'page_title' => 'Coming Soon - APS Dream Home',
            'page_description' => 'This page is coming soon'
        ];
        $this->render('pages/coming_soon', $data);
    }

    // Property Submit
    public function propertySubmit()
    {
        $data = [
            'page_title' => 'Submit Property - APS Dream Home',
            'page_description' => 'Submit your property for listing'
        ];
        $this->render('pages/properties/submit', $data);
    }

    // Property List
    public function propertyList()
    {
        $data = [
            'page_title' => 'Property List - APS Dream Home',
            'page_description' => 'Browse all available properties'
        ];
        $this->render('pages/properties/list', $data);
    }

    // Property Edit
    public function propertyEdit()
    {
        $data = [
            'page_title' => 'Edit Property - APS Dream Home',
            'page_description' => 'Edit your property listing'
        ];
        $this->render('pages/properties/edit', $data);
    }

    // Book Plot
    public function bookPlot()
    {
        $data = [
            'page_title' => 'Book a Plot - APS Dream Home',
            'page_description' => 'Book your dream plot'
        ];
        $this->render('pages/properties/book_plot', $data);
    }

    // Book Property
    public function bookProperty()
    {
        $data = [
            'page_title' => 'Book Property - APS Dream Home',
            'page_description' => 'Book your dream property'
        ];
        $this->render('pages/properties/book', $data);
    }

    // Schedule Meeting
    public function scheduleMeeting()
    {
        $data = [
            'page_title' => 'Schedule a Meeting - APS Dream Home',
            'page_description' => 'Book an appointment with our agents'
        ];
        $this->render('pages/schedule_meeting', $data);
    }

    // Handle Schedule Meeting Form
    public function handleScheduleMeeting()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form data here
            $this->setFlash('success', 'Meeting scheduled successfully! We will contact you soon.');
            $this->redirect('/');
        }
    }

    // Get Featured Properties (API)
    public function getFeaturedProperties()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => [
                ['id' => 1, 'title' => 'Suyoday Colony', 'location' => 'Gorakhpur', 'price' => '750000'],
                ['id' => 2, 'title' => 'Raghunat Nagri', 'location' => 'Gorakhpur', 'price' => '850000'],
            ]
        ]);
        exit;
    }
}
