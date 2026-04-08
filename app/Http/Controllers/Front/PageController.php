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

        // Get featured projects from database
        $featured_properties = [];
        $all_projects = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE status IN ('active', 'completed') ORDER BY site_name LIMIT 6");
            $stmt->execute();
            $all_projects = $stmt->fetchAll(\PDO::FETCH_OBJ);
            
            // Map to featured format
            foreach ($all_projects as $project) {
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project->site_name));
                $featured_properties[] = [
                    'id' => $project->id,
                    'title' => $project->site_name,
                    'location' => ($project->district ?? '') . ', ' . ($project->state ?? ''),
                    'city' => $project->district ?? '',
                    'price' => 'Starting from ₹5.5 Lakhs',
                    'slug' => $slug,
                    'type' => ucfirst($project->site_type ?? 'Residential'),
                    'status' => ($project->status === 'active') ? 'Available' : 'Completed',
                    'total_area' => $project->total_area,
                    'description' => $project->description
                ];
            }
        } catch (\Exception $e) {
            error_log("Home projects error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'APS Dream Home - Premium Real Estate in UP',
            'page_description' => 'Discover premium residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh',
            'hero_stats' => $hero_stats,
            'featured_properties' => $featured_properties,
            'all_projects' => $all_projects,
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
                    
                    // Also save to inquiries table for CRM
                    try {
                        $inqStmt = $this->db->prepare("INSERT INTO inquiries (name, email, phone, message, type, status, priority, created_at) VALUES (?, ?, ?, ?, ?, 'new', 'medium', NOW())");
                        $inqStmt->execute([$name, $email, $phone, $subject . ': ' . $message, 'contact']);
                    } catch (\Exception $e2) {
                        error_log("Inquiry save error: " . $e2->getMessage());
                    }
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

    // Service Interest Handler
    public function serviceInterest()
    {
        header('Content-Type: application/json');
        
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $serviceType = trim($_POST['service_type'] ?? '');
        $propertyId = (int)($_POST['property_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || empty($serviceType)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            return;
        }

        try {
            // Check if service_interests table exists
            $this->db->query("SELECT 1 FROM service_interests LIMIT 1");
            
            $stmt = $this->db->prepare("
                INSERT INTO service_interests (service_type, property_id, status, notes, created_at) 
                VALUES (?, ?, 'new', ?, NOW())
            ");
            $stmt->execute([$serviceType, $propertyId, $message]);
            $serviceId = $this->db->lastInsertId();

            // Create lead
            $leadStmt = $this->db->prepare("
                INSERT INTO leads (name, email, phone, source, status, created_at) 
                VALUES (?, ?, ?, 'website', 'new', NOW())
            ");
            $leadStmt->execute([$name, $email, $phone]);
            $leadId = $this->db->lastInsertId();

            // Link lead to service
            $this->db->prepare("UPDATE service_interests SET lead_id = ? WHERE id = ?")
                ->execute([$leadId, $serviceId]);

            echo json_encode(['success' => true, 'message' => 'Thank you! We will contact you shortly.']);
        } catch (\Exception $e) {
            // Table might not exist, create it
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $this->createServiceInterestsTable();
                // Retry
                $stmt = $this->db->prepare("
                    INSERT INTO service_interests (service_type, property_id, status, notes, created_at) 
                    VALUES (?, ?, 'new', ?, NOW())
                ");
                $stmt->execute([$serviceType, $propertyId, $message]);
                $serviceId = $this->db->lastInsertId();

                $leadStmt = $this->db->prepare("
                    INSERT INTO leads (name, email, phone, source, status, created_at) 
                    VALUES (?, ?, ?, 'website', 'new', NOW())
                ");
                $leadStmt->execute([$name, $email, $phone]);
                $leadId = $this->db->lastInsertId();

                $this->db->prepare("UPDATE service_interests SET lead_id = ? WHERE id = ?")
                    ->execute([$leadId, $serviceId]);

                echo json_encode(['success' => true, 'message' => 'Thank you! We will contact you shortly.']);
            } else {
                error_log("Service interest error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
            }
        }
    }

    private function createServiceInterestsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS service_interests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lead_id INT DEFAULT NULL,
            property_id INT DEFAULT NULL,
            service_type VARCHAR(50) NOT NULL,
            status ENUM('new', 'contacted', 'in_progress', 'completed', 'cancelled') DEFAULT 'new',
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_lead (lead_id),
            INDEX idx_property (property_id),
            INDEX idx_service_type (service_type),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->exec($sql);
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
        $page = (int)($_GET['page'] ?? 1);
        $type = $_GET['type'] ?? '';
        $listingType = $_GET['listing'] ?? '';
        $location = $_GET['location'] ?? '';
        $minPrice = (int)($_GET['min_price'] ?? 0);
        $maxPrice = (int)($_GET['max_price'] ?? 0);
        $sortBy = $_GET['sort'] ?? 'newest';
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $properties = [];
        $total = 0;

        // Try to fetch from database first
        try {
            $this->db->query("SELECT 1 FROM user_properties LIMIT 1");
            
            $where = "WHERE status = 'approved'";
            $params = [];

            if ($type) {
                $where .= " AND property_type = ?";
                $params[] = $type;
            }
            if ($listingType) {
                $where .= " AND listing_type = ?";
                $params[] = $listingType;
            }
            if ($location) {
                $where .= " AND address LIKE ?";
                $params[] = '%' . $location . '%';
            }
            if ($minPrice > 0) {
                $where .= " AND price >= ?";
                $params[] = $minPrice;
            }
            if ($maxPrice > 0) {
                $where .= " AND price <= ?";
                $params[] = $maxPrice;
            }

            $orderBy = match($sortBy) {
                'price_low' => 'price ASC',
                'price_high' => 'price DESC',
                'oldest' => 'created_at ASC',
                default => 'created_at DESC'
            };

            // Count total
            $countSql = "SELECT COUNT(*) as total FROM user_properties $where";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Get properties
            $sql = "SELECT * FROM user_properties $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            // Table doesn't exist or error, use sample data
        }

        // If no properties in DB, use sample data
        if (empty($properties)) {
            $sampleProperties = $this->getSampleProperties();
            $total = count($sampleProperties);
            
            // Apply filters to sample data
            if ($type) {
                $sampleProperties = array_filter($sampleProperties, fn($p) => strtolower($p['type']) === strtolower($type));
            }
            if ($listingType) {
                $sampleProperties = array_filter($sampleProperties, fn($p) => strtolower($p['listing_type']) === strtolower($listingType));
            }
            if ($location) {
                $sampleProperties = array_filter($sampleProperties, fn($p) => stripos($p['location'], $location) !== false);
            }
            
            // Sort
            usort($sampleProperties, function($a, $b) use ($sortBy) {
                return match($sortBy) {
                    'price_low' => $a['price_num'] <=> $b['price_num'],
                    'price_high' => $b['price_num'] <=> $a['price_num'],
                    default => 0
                };
            });
            
            $total = count($sampleProperties);
            $properties = array_slice($sampleProperties, $offset, $perPage);
        }

        $totalPages = ceil($total / $perPage);

        $data = [
            'properties' => $properties,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'type' => $type,
            'listingType' => $listingType,
            'location' => $location,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'sortBy' => $sortBy,
            'property_types' => ['plot', 'house', 'flat', 'shop', 'farmhouse', 'land'],
            'locations' => ['Gorakhpur', 'Lucknow', 'Kushinagar', 'Varanasi'],
            'price_ranges' => ['Under 5 Lakhs', '5-10 Lakhs', '10-20 Lakhs', '20-50 Lakhs', '50+ Lakhs'],
            'page_title' => 'Properties - APS Dream Home',
            'page_description' => 'Browse properties for sale and rent'
        ];

        $this->render('pages/properties', $data);
    }

    private function getSampleProperties()
    {
        return [
            [
                'id' => 1,
                'name' => 'Suryoday Colony',
                'address' => 'Gorakhpur, Uttar Pradesh',
                'location' => 'Gorakhpur',
                'price' => 750000,
                'price_display' => '₹7.5 Lakhs',
                'price_num' => 750000,
                'image' => 'suyoday.jpg',
                'property_type' => 'plot',
                'listing_type' => 'sell',
                'type' => 'Plot',
                'status' => 'approved',
                'area_sqft' => 1200,
                'area' => '1200 sq ft',
                'bedrooms' => 'N/A',
                'description' => 'Premium residential plots with modern infrastructure and amenities.'
            ],
            [
                'id' => 2,
                'name' => 'Raghunat Nagri',
                'address' => 'Gorakhpur, Uttar Pradesh',
                'location' => 'Gorakhpur',
                'price' => 850000,
                'price_display' => '₹8.5 Lakhs',
                'price_num' => 850000,
                'image' => 'raghunat.jpg',
                'property_type' => 'plot',
                'listing_type' => 'sell',
                'type' => 'Plot',
                'status' => 'approved',
                'area_sqft' => 1500,
                'area' => '1500 sq ft',
                'bedrooms' => 'N/A',
                'description' => 'Premium residential plots in developing area with all facilities.'
            ],
            [
                'id' => 3,
                'name' => 'Braj Radha Nagri',
                'address' => 'Gorakhpur, Uttar Pradesh',
                'location' => 'Gorakhpur',
                'price' => 650000,
                'price_display' => '₹6.5 Lakhs',
                'price_num' => 650000,
                'image' => 'brajradha.jpg',
                'property_type' => 'plot',
                'listing_type' => 'sell',
                'type' => 'Plot',
                'status' => 'approved',
                'area_sqft' => 1000,
                'area' => '1000 sq ft',
                'bedrooms' => 'N/A',
                'description' => 'Affordable residential plots with basic amenities.'
            ],
            [
                'id' => 4,
                'name' => 'Budh Bihar Colony',
                'address' => 'Kushinagar, Uttar Pradesh',
                'location' => 'Kushinagar',
                'price' => 550000,
                'price_display' => '₹5.5 Lakhs',
                'price_num' => 550000,
                'image' => 'budhbihar.jpg',
                'property_type' => 'plot',
                'listing_type' => 'sell',
                'type' => 'Plot',
                'status' => 'approved',
                'area_sqft' => 1100,
                'area' => '1100 sq ft',
                'bedrooms' => 'N/A',
                'description' => 'Integrated township with modern facilities.'
            ],
            [
                'id' => 5,
                'name' => 'Awadhpuri',
                'address' => 'Lucknow, Uttar Pradesh',
                'location' => 'Lucknow',
                'price' => 1200000,
                'price_display' => '₹12 Lakhs',
                'price_num' => 1200000,
                'image' => 'awadhpuri.jpg',
                'property_type' => 'plot',
                'listing_type' => 'sell',
                'type' => 'Plot',
                'status' => 'approved',
                'area_sqft' => 2000,
                'area' => '2000 sq ft',
                'bedrooms' => 'N/A',
                'description' => '20 bigha premium project with luxury amenities.'
            ],
            [
                'id' => 6,
                'name' => 'Commercial Shop',
                'address' => 'Gorakhpur, Uttar Pradesh',
                'location' => 'Gorakhpur',
                'price' => 2500000,
                'price_display' => '₹25 Lakhs',
                'price_num' => 2500000,
                'image' => 'commercial.jpg',
                'property_type' => 'shop',
                'listing_type' => 'sell',
                'type' => 'Commercial',
                'status' => 'approved',
                'area_sqft' => 800,
                'area' => '800 sq ft',
                'bedrooms' => 'N/A',
                'description' => 'Prime commercial space in heart of the city.'
            ]
        ];
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
        try {
            // Get sites by state for grouping
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE status IN ('active', 'under_development') ORDER BY state, city, site_name");
            $stmt->execute();
            $projects = $stmt->fetchAll(\PDO::FETCH_OBJ);
            
            // Group by state
            $grouped = [];
            // Group by state > district
            $grouped = [];
            foreach ($projects as $project) {
                $state = $project->state ?? 'Other';
                $district = $project->district ?? 'Other';
                if (!isset($grouped[$state])) {
                    $grouped[$state] = [];
                }
                if (!isset($grouped[$state][$district])) {
                    $grouped[$state][$district] = [];
                }
                $grouped[$state][$district][] = $project;
            }
        } catch (\Exception $e) {
            $projects = [];
            $grouped = [];
            error_log("Projects error: " . $e->getMessage());
        }
        
        $data = [
            'page_title' => 'Our Projects - APS Dream Home',
            'page_description' => 'Explore our residential and commercial projects',
            'projects' => $projects,
            'grouped_projects' => $grouped
        ];
        $this->render('pages/company_projects', $data);
    }

    // Project Details - Dynamic
    public function projectDetails($slug = null)
    {
        $project = null;
        $plots = [];
        
        if ($slug) {
            try {
                // Convert slug to site name format
                $searchName = str_replace('-', ' ', $slug);
                $searchName = preg_replace('/\s+/', ' ', trim($searchName));
                
                // Try exact match on site_name
                $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name = ? LIMIT 1");
                $stmt->execute([ucwords($searchName)]);
                $project = $stmt->fetch(\PDO::FETCH_OBJ);
                
                // Try case-insensitive match
                if (!$project) {
                    $stmt = $this->db->prepare("SELECT * FROM sites WHERE LOWER(site_name) = LOWER(?) LIMIT 1");
                    $stmt->execute([$searchName]);
                    $project = $stmt->fetch(\PDO::FETCH_OBJ);
                }
                
                // Try LIKE match
                if (!$project) {
                    $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE ? LIMIT 1");
                    $stmt->execute(['%' . $searchName . '%']);
                    $project = $stmt->fetch(\PDO::FETCH_OBJ);
                }
                
                // Get any active project as final fallback
                if (!$project) {
                    $stmt = $this->db->query("SELECT * FROM sites WHERE status = 'active' LIMIT 1");
                    $project = $stmt->fetch(\PDO::FETCH_OBJ);
                }
                
                // Get plots for this site
                if ($project) {
                    try {
                        $plotStmt = $this->db->prepare("SELECT * FROM plots WHERE site_id = ? AND status IN ('available', 'open') LIMIT 20");
                        $plotStmt->execute([$project->id]);
                        $plots = $plotStmt->fetchAll(\PDO::FETCH_OBJ);
                    } catch (\Exception $e) {
                        $plots = [];
                    }
                    
                    // Get related projects (same district, excluding current)
                    try {
                        $relatedStmt = $this->db->prepare("SELECT * FROM sites WHERE district = ? AND id != ? AND status IN ('active', 'completed') ORDER BY site_name LIMIT 4");
                        $relatedStmt->execute([$project->district, $project->id]);
                        $related_projects = $relatedStmt->fetchAll(\PDO::FETCH_OBJ);
                    } catch (\Exception $e) {
                        $related_projects = [];
                    }
                }
            } catch (\Exception $e) {
                error_log("Project details error: " . $e->getMessage());
            }
        }
        
        $data = [
            'page_title' => $project ? ($project->site_name ?? 'Project') . ' - APS Dream Home' : 'Project Not Found',
            'page_description' => $project ? 'View details of ' . ($project->site_name ?? 'our project') : 'Project details',
            'project' => $project,
            'plots' => $plots ?? [],
            'related_projects' => $related_projects ?? []
        ];
        $this->render('pages/project_detail', $data);
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
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE '%Suryoday%' LIMIT 1");
            $stmt->execute();
            $project = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            $project = null;
        }
        
        $data = [
            'page_title' => 'Suyoday Colony - APS Dream Home',
            'page_description' => 'Premium residential plots in Suyoday Colony, Gorakhpur',
            'project' => $project
        ];
        $this->render('pages/project_detail', $data);
    }

    // Raghunat Nagri
    public function raghunatNagri()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE '%Raghunath%' OR site_name LIKE '%Raghunat%' LIMIT 1");
            $stmt->execute();
            $project = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            $project = null;
        }
        
        $data = [
            'page_title' => 'Raghunat Nagri - APS Dream Home',
            'page_description' => 'Premium residential plots in Raghunat Nagri, Gorakhpur',
            'project' => $project
        ];
        $this->render('pages/project_detail', $data);
    }

    // Braj Radha Nagri
    public function brajRadhaNagri()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE '%Braj Radha%' OR site_name LIKE '%Braj%' LIMIT 1");
            $stmt->execute();
            $project = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            $project = null;
        }
        
        $data = [
            'page_title' => 'Braj Radha Nagri - APS Dream Home',
            'page_description' => 'Affordable residential plots in Braj Radha Nagri',
            'project' => $project
        ];
        $this->render('pages/project_detail', $data);
    }

    // Budh Bihar Colony
    public function budhBiharColony()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE '%Budh Bihar%' OR site_name LIKE '%Budh%' LIMIT 1");
            $stmt->execute();
            $project = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            $project = null;
        }
        
        $data = [
            'page_title' => 'Budh Bihar Colony - APS Dream Home',
            'page_description' => 'Integrated township at Budh Bihar Colony, Kushinagar',
            'project' => $project
        ];
        $this->render('pages/project_detail', $data);
    }

    // Awadhpuri
    public function awadhpuri()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE '%Awadhpuri%' LIMIT 1");
            $stmt->execute();
            $project = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            $project = null;
        }
        
        $data = [
            'page_title' => 'Awadhpuri - APS Dream Home',
            'page_description' => 'Premium project at Awadhpuri, Lucknow',
            'project' => $project
        ];
        $this->render('pages/project_detail', $data);
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

    // Builder Registration
    public function builderRegistration()
    {
        $data = [
            'page_title' => 'Builder Registration - APS Dream Home',
            'page_description' => 'Join our developer partner program'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['company_name'] ?? '');
            $contact_person = trim($_POST['contact_person'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $company_type = trim($_POST['company_type'] ?? '');
            $total_projects = intval($_POST['total_projects'] ?? 0);
            $ongoing_projects = intval($_POST['ongoing_projects'] ?? 0);
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirm_password = trim($_POST['confirm_password'] ?? '');
            $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;

            $errors = [];

            if (empty($name) || empty($contact_person) || empty($mobile) || empty($email) || empty($password)) {
                $errors[] = "Please fill all required fields.";
            }
            if ($password !== $confirm_password) {
                $errors[] = "Passwords do not match.";
            }
            if (strlen($mobile) != 10) {
                $errors[] = "Mobile number must be 10 digits.";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Please enter a valid email address.";
            }
            if (!$terms_accepted) {
                $errors[] = "Please accept the terms and conditions.";
            }

            if (empty($errors)) {
                try {
                    $check = $this->db->prepare("SELECT id FROM builders WHERE mobile = ? OR email = ?");
                    $check->execute([$mobile, $email]);
                    if ($check->fetch()) {
                        $data['error'] = "Mobile number or email already registered!";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $address = $city . ', ' . $state;

                        $stmt = $this->db->prepare("INSERT INTO builders (name, email, mobile, address, license_number, specialization, total_projects, ongoing_projects, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
                        $stmt->execute([$name, $email, $mobile, $address, $company_type, 'Residential', $total_projects, $ongoing_projects]);

                        $data['success'] = "Registration successful! Your account is under review. Our team will contact you soon.";
                    }
                } catch (\Exception $e) {
                    $data['error'] = "Registration failed. Please try again.";
                    error_log('Builder registration error: ' . $e->getMessage());
                }
            } else {
                $data['error'] = implode(" ", $errors);
            }
        }

        $this->render('pages/builder_registration', $data);
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

    // Buy Property
    public function buyProperty()
    {
        $this->render('pages/buy');
    }

    // Sell Property
    public function sellProperty()
    {
        $this->render('pages/sell');
    }

    // Rent Property
    public function rentProperty()
    {
        $this->render('pages/rent');
    }

    // Investment Property
    public function investProperty()
    {
        $this->render('pages/invest');
    }

    // Projects by Location
    public function projectsByLocation($location = null)
    {
        $projects = [];
        if ($location) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM sites WHERE LOWER(district) = LOWER(?) AND status IN ('active', 'completed') ORDER BY site_name");
                $stmt->execute([$location]);
                $projects = $stmt->fetchAll(\PDO::FETCH_OBJ);
            } catch (\Exception $e) {
                error_log("Projects by location error: " . $e->getMessage());
            }
        }
        $data = [
            'page_title' => ucfirst($location) . ' Projects - APS Dream Home',
            'page_description' => 'Explore our projects in ' . ucfirst($location),
            'projects' => $projects,
            'location' => $location
        ];
        $this->render('pages/projects_by_location', $data);
    }

    // Handle Quick Inquiry from Homepage
    public function handleQuickInquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $requirement = trim($_POST['requirement'] ?? '');
            $budget = trim($_POST['budget'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $timeline = trim($_POST['timeline'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $formType = trim($_POST['form_type'] ?? 'quick_inquiry');

            if (empty($name) || empty($phone)) {
                $_SESSION['flash_error'] = 'Please fill in name and phone number.';
                $this->redirect('/');
                return;
            }

            try {
                // Save to inquiries table
                $fullMessage = "Requirement: " . ucfirst(str_replace('_', ' ', $requirement)) . "\n";
                $fullMessage .= "Budget: " . ucfirst(str_replace('_', ' ', $budget)) . "\n";
                $fullMessage .= "Location: " . ucfirst($location) . "\n";
                $fullMessage .= "Timeline: " . ucfirst(str_replace('_', ' ', $timeline)) . "\n";
                if ($message) {
                    $fullMessage .= "Message: " . $message;
                }

                $stmt = $this->db->prepare("INSERT INTO inquiries (name, email, phone, message, type, status, priority, created_at) VALUES (?, ?, ?, ?, ?, 'new', 'high', NOW())");
                $stmt->execute([$name, $email, $phone, $fullMessage, $formType]);
                $inquiryId = $this->db->lastInsertId();

                // Also save to contacts table
                try {
                    $contactStmt = $this->db->prepare("INSERT INTO contacts (name, email, phone, subject, message, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $contactStmt->execute([$name, $email, $phone, 'Quick Inquiry - ' . ucfirst(str_replace('_', ' ', $requirement)), $fullMessage, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
                } catch (\Exception $e2) {}

                // Track service interests based on requirement
                $this->trackServiceInterests($name, $phone, $email, $requirement, $inquiryId);

                $_SESSION['flash_success'] = 'Thank you! Your inquiry has been submitted. We will contact you shortly.';
            } catch (\Exception $e) {
                error_log("Quick inquiry error: " . $e->getMessage());
                $_SESSION['flash_error'] = 'Failed to submit. Please call us directly at +91 92771 21112.';
            }
        }
        $this->redirect('/');
    }

    // Track Service Interests
    private function trackServiceInterests($name, $phone, $email, $requirement, $inquiryId)
    {
        // Map requirements to service types
        $serviceMapping = [
            'home_loan' => ['buy_house', 'buy_flat', 'invest'],
            'legal' => ['legal', 'registry'],
            'interior' => ['interior']
        ];

        foreach ($serviceMapping as $serviceType => $requirements) {
            if (in_array($requirement, $requirements)) {
                try {
                    $serviceStmt = $this->db->prepare("INSERT INTO service_interests (inquiry_id, service_type, status, created_at) VALUES (?, ?, 'new', NOW())");
                    $serviceStmt->execute([$inquiryId, $serviceType]);
                } catch (\Exception $e) {
                    error_log("Service interest tracking error: " . $e->getMessage());
                }
            }
        }
    }

    // Property Listing (User Post Property)
    public function listProperty()
    {
        $this->render('pages/list_property');
    }

    // Handle Property Listing Submission
    public function handlePropertyListing()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $propertyType = trim($_POST['property_type'] ?? '');
            $listingType = trim($_POST['listing_type'] ?? 'sell');
            $price = (float)str_replace([',', ' '], '', $_POST['price'] ?? 0);
            $location = trim($_POST['location'] ?? '');
            $area = (int)str_replace([',', ' '], '', $_POST['area'] ?? 0);
            $description = trim($_POST['description'] ?? '');

            if (empty($name) || empty($phone) || empty($propertyType)) {
                $_SESSION['flash_error'] = 'Please fill in all required fields.';
                $this->redirect('/list-property');
                return;
            }

            try {
                // Handle image upload
                $imagePath = null;
                if (!empty($_FILES['property_image']['name'])) {
                    $uploadDir = __DIR__ . '/../../../../assets/images/properties/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['property_image']['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    if (in_array($ext, $allowed) && $_FILES['property_image']['size'] <= 5 * 1024 * 1024) {
                        $newName = 'prop_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $targetPath = $uploadDir . $newName;
                        if (move_uploaded_file($_FILES['property_image']['tmp_name'], $targetPath)) {
                            $imagePath = 'properties/' . $newName;
                        }
                    }
                }

                // Try to save to user_properties table
                $savedToUserProperties = false;
                try {
                    $this->db->query("SELECT 1 FROM user_properties LIMIT 1");
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO user_properties (user_id, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, image, status, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                    ");
                    $stmt->execute([
                        null,
                        $name,
                        $phone,
                        $email,
                        $propertyType,
                        $listingType,
                        $location,
                        $area,
                        $price,
                        $listingType === 'rent' ? 'month' : 'lakh',
                        $description,
                        $imagePath
                    ]);
                    $savedToUserProperties = true;
                } catch (\Exception $e1) {
                    // Table might not exist, create it
                    if (strpos($e1->getMessage(), "doesn't exist") !== false) {
                        $this->createUserPropertiesTable();
                        $stmt = $this->db->prepare("
                            INSERT INTO user_properties (user_id, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, image, status, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                        ");
                        $stmt->execute([
                            null,
                            $name,
                            $phone,
                            $email,
                            $propertyType,
                            $listingType,
                            $location,
                            $area,
                            $price,
                            $listingType === 'rent' ? 'month' : 'lakh',
                            $description,
                            $imagePath
                        ]);
                        $savedToUserProperties = true;
                    }
                }

                // Also save to inquiries for CRM tracking
                $message = "Property Type: " . ucfirst($propertyType) . "\n";
                $message .= "Listing Type: " . ucfirst($listingType) . "\n";
                $message .= "Price: " . $price . "\n";
                $message .= "Area: " . $area . " sq ft\n";
                $message .= "Location: " . $location . "\n";
                $message .= "Description: " . $description;

                try {
                    $inqStmt = $this->db->prepare("INSERT INTO inquiries (name, email, phone, message, type, status, priority, created_at) VALUES (?, ?, ?, ?, 'property_listing', 'new', 'medium', NOW())");
                    $inqStmt->execute([$name, $email, $phone, $message]);
                } catch (\Exception $e2) {
                    error_log("Inquiry save error: " . $e2->getMessage());
                }

                $_SESSION['flash_success'] = 'Thank you! Your property listing request has been submitted. Our team will contact you within 24 hours to verify the details.';
            } catch (\Exception $e) {
                error_log("Property listing error: " . $e->getMessage());
                $_SESSION['flash_error'] = 'Failed to submit. Please try again or call us directly.';
            }
        }
        $this->redirect('/list-property');
    }

    private function createUserPropertiesTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS user_properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            name VARCHAR(200) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(100) DEFAULT NULL,
            property_type ENUM('plot','house','flat','shop','farmhouse','warehouse','land') NOT NULL,
            listing_type ENUM('sell','rent') NOT NULL DEFAULT 'sell',
            state_id INT DEFAULT NULL,
            district_id INT DEFAULT NULL,
            city_id INT DEFAULT NULL,
            address TEXT,
            area_sqft INT DEFAULT NULL,
            price DECIMAL(15,2) DEFAULT NULL,
            price_type ENUM('lakh','crore','month') DEFAULT 'lakh',
            description TEXT,
            images JSON,
            status ENUM('pending','verified','approved','rejected','sold','rented') DEFAULT 'pending',
            is_featured TINYINT DEFAULT 0,
            views INT DEFAULT 0,
            inquiries INT DEFAULT 0,
            verified_by INT DEFAULT NULL,
            verified_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_property_type (property_type),
            INDEX idx_listing_type (listing_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->exec($sql);
    }
}
