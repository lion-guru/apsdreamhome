<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use PDO;

class PageController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function loadPageContent(string $slug): array
    {
        $pageTitle = '';
        $pageContent = '';
        try {
            $stmt = $this->db->prepare("SELECT title, content FROM pages WHERE slug = ? AND status = 'published' LIMIT 1");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $pageTitle = $row['title'];
                $pageContent = $row['content'];
            }
        } catch (\Exception $e) {
            error_log('PageController loadPageContent: ' . $e->getMessage());
        }
        return [$pageTitle, $pageContent];
    }

    // Home Page
    public function home()
    {
        // Get hero statistics from DB settings with fallback
        $scStats = \App\Services\SiteContentService::getInstance()->getSection('settings');
        $hero_stats = [
            'years_experience' => (int) preg_replace('/[^0-9]/', '', $scStats['stat_experience_value'] ?? '15') ?: 15,
            'projects_completed' => (int) preg_replace('/[^0-9]/', '', $scStats['stat_projects_value'] ?? '50') ?: 50,
            'happy_customers' => (int) preg_replace('/[^0-9]/', '', $scStats['stat_families_value'] ?? '1000') ?: 1000,
            'awards_won' => (int) preg_replace('/[^0-9]/', '', $scStats['stat_properties_value'] ?? '25') ?: 25,
        ];

        // Get featured projects from database (hot-path cached, 15 min TTL)
        $featured_properties = [];
        $all_projects = [];
        try {
            $cachedHome = \App\Services\Cache\HotPathCacheService::getHomeFeaturedProperties(
                function () {
                    $stmt = $this->db->prepare("SELECT * FROM sites WHERE status IN ('active', 'completed') ORDER BY site_name LIMIT 6");
                    $stmt->execute();
                    return $stmt->fetchAll(\PDO::FETCH_OBJ);
                }
            );
            $all_projects = is_array($cachedHome) ? $cachedHome : [];

            // Map to featured format
            foreach ($all_projects as $project) {
                if (!is_object($project)) {
                    continue;
                }
                $siteName = $project->site_name ?? '';
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $siteName));
                $featured_properties[] = [
                    'id' => $project->id ?? null,
                    'title' => $siteName,
                    'location' => ($project->district ?? '') . ', ' . ($project->state ?? ''),
                    'city' => $project->district ?? '',
                    'price' => 'Starting from ₹5.5 Lakhs',
                    'slug' => $slug,
                    'type' => ucfirst($project->site_type ?? 'Residential'),
                    'status' => ($project->status === 'active') ? 'Available' : 'Completed',
                    'total_area' => $project->total_area ?? null,
                    'description' => $project->description ?? null
                ];
            }
        } catch (\Exception $e) {
            error_log("Home projects error: " . $e->getMessage());
        }

        // Get rank slabs for MLM benefits table
        $rank_slabs = [];
        try {
            $stmt = $this->db->query("SELECT rank_name, min_gbv, commission_rate, reward_name, reward_value FROM mlm_rank_slabs WHERE is_active = 1 ORDER BY min_gbv ASC");
            $rank_slabs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Home rank_slabs error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'APS Dream Home - Premium Real Estate in UP',
            'page_description' => 'Discover premium residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh',
            'hero_stats' => $hero_stats,
            'featured_properties' => $featured_properties,
            'all_projects' => $all_projects,
            'rank_slabs' => $rank_slabs,
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

                    // Auto-wire to CRM lead
                    try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>$subject.': '.$message,'type'=>'contact']); } catch (\Exception $e3) {}

                    // Trigger WhatsApp notification for new inquiry
                    try {
                        $waService = new \App\Services\Communication\WhatsAppService();
                        $waService->sendTemplate($phone, 'inquiry_received', [
                            'customer_name' => $name ?: 'Valued Customer',
                            'property_type' => 'property',
                        ]);
                    } catch (\Exception $e) {
                        // WhatsApp notification is best-effort
                        error_log("PageController.php: " . $e->getMessage());
                    }
                } catch (\Exception $e) {
                    $error = 'Failed to submit. Please try again or call us directly.';
                    error_log("Contact form error: " . $e->getMessage());
                }
            }

            if (
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false)
            ) {
                header('Content-Type: application/json');
                if ($success) {
                    echo json_encode(['success' => true, 'message' => __('contact_success', null, 'Thank you! Your message has been sent successfully.')]);
                } else {
                    echo json_encode(['success' => false, 'message' => $error ?: 'Validation failed. Please check your inputs.']);
                }
                exit;
            }
        }

        [$cmsTitle, $pageContent] = $this->loadPageContent('contact');
        $data = [
            'page_title' => ($cmsTitle ?: 'Contact Us') . ' - APS Dream Home',
            'page_description' => 'Get in touch with APS Dream Home',
            'contact_success' => $success,
            'contact_error' => $error,
            'pageContent' => $pageContent
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
                INSERT INTO service_interests (service_type, customer_name, customer_phone, customer_email, status, notes, created_at) 
                VALUES (?, ?, ?, ?, 'pending', ?, NOW())
            ");
            $stmt->execute([$serviceType, $name, $phone, $email, $message]);
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
                error_log("PageController.php: " . $e->getMessage());
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
        [$cmsTitle, $pageContent] = $this->loadPageContent('about');

        // Load dynamic content from DB (fallback to empty)
        $siteContent = [];
        try {
            $siteContent = \App\Services\SiteContentService::getInstance()->getSection('about');
        } catch (\Exception $e) {
            // fallback: empty, view will use __() lang keys
        }

        $data = [
            'page_title' => ($cmsTitle ?: 'About Us') . ' - APS Dream Home',
            'page_description' => 'Learn more about APS Dream Home',
            'pageContent' => $pageContent,
            'siteContent' => $siteContent,
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
        $bedrooms = (int)($_GET['bedrooms'] ?? 0);
        $bathrooms = (int)($_GET['bathrooms'] ?? 0);
        $furnished = $_GET['furnished'] ?? '';
        $yearBuilt = (int)($_GET['year_built'] ?? 0);
        $areaMin = (int)($_GET['area_min'] ?? 0);
        $areaMax = (int)($_GET['area_max'] ?? 0);
        $keyword = trim($_GET['q'] ?? '');
        $sortBy = $_GET['sort'] ?? 'newest';
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        // ── Hot-path cache: property listings (5 min TTL) ──
        $filterHash = [
            'q' => $keyword,
            'type' => $type,
            'listing' => $listingType,
            'location' => $location,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'furnished' => $furnished,
            'year_built' => $yearBuilt,
            'area_min' => $areaMin,
            'area_max' => $areaMax,
        ];
        $cached = \App\Services\Cache\HotPathCacheService::getPropertyList(
            $filterHash,
            $page,
            $perPage,
            $sortBy,
            function () use ($filterHash, $keyword, $type, $listingType, $location, $minPrice, $maxPrice, $bedrooms, $bathrooms, $furnished, $yearBuilt, $areaMin, $areaMax, $sortBy, $perPage, $offset) {
                return $this->runPropertyListQuery(
                    $keyword,
                    $type,
                    $listingType,
                    $location,
                    $minPrice,
                    $maxPrice,
                    $bedrooms,
                    $bathrooms,
                    $furnished,
                    $yearBuilt,
                    $areaMin,
                    $areaMax,
                    $sortBy,
                    $perPage,
                    $offset
                );
            }
        );
        $properties = $cached['properties'] ?? [];
        $total = (int) ($cached['total'] ?? 0);

        $totalPages = max(1, (int)ceil($total / $perPage));

        // Fetch saved searches for logged-in users
        $savedSearches = [];
        if (!empty($_SESSION['user_id'])) {
            try {
                $svc = new \App\Services\SavedSearchService($this->db);
                $savedSearches = $svc->getUserSearches((int)$_SESSION['user_id'], 'user_properties');
            } catch (\Throwable $e) {
                error_log("PageController::properties - savedSearches: " . $e->getMessage());
            }
        }

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
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'furnished' => $furnished,
            'yearBuilt' => $yearBuilt,
            'areaMin' => $areaMin,
            'areaMax' => $areaMax,
            'keyword' => $keyword,
            'sortBy' => $sortBy,
            'savedSearches' => $savedSearches,
            'property_types' => ['plot', 'house', 'flat', 'shop', 'farmhouse', 'land'],
            'locations' => ['Gorakhpur', 'Lucknow', 'Kushinagar', 'Varanasi'],
            'price_ranges' => ['Under 5 Lakhs', '5-10 Lakhs', '10-20 Lakhs', '20-50 Lakhs', '50+ Lakhs'],
            'page_title' => 'Properties - APS Dream Home',
            'page_description' => 'Browse properties for sale and rent'
        ];

        $this->render('pages/properties', $data);
    }

    /**
     * Run the actual property list query (extracted for caching).
     * Returns ['properties' => array, 'total' => int].
     */
    private function runPropertyListQuery(
        string $keyword,
        string $type,
        string $listingType,
        string $location,
        int $minPrice,
        int $maxPrice,
        int $bedrooms,
        int $bathrooms,
        string $furnished,
        int $yearBuilt,
        int $areaMin,
        int $areaMax,
        string $sortBy,
        int $perPage,
        int $offset
    ): array {
        $properties = [];
        $total = 0;

        // Try to fetch from database first
        try {
            $this->db->query("SELECT 1 FROM properties LIMIT 1");

            $where = "WHERE status = 'active' AND deleted_at IS NULL";
            $params = [];

            if ($keyword !== '') {
                $where .= " AND (title LIKE ? OR location LIKE ? OR description LIKE ?)";
                $like = '%' . $keyword . '%';
                array_push($params, $like, $like, $like);
            }
            if ($type) {
                $where .= " AND type = ?";
                $params[] = $type;
            }
            if ($listingType) {
                // properties table doesn't have listing_type, skip
            }
            if ($location) {
                $where .= " AND (location LIKE ? OR city LIKE ?)";
                $loc = '%' . $location . '%';
                array_push($params, $loc, $loc);
            }
            if ($minPrice > 0) {
                $where .= " AND price >= ?";
                $params[] = $minPrice;
            }
            if ($maxPrice > 0) {
                $where .= " AND price <= ?";
                $params[] = $maxPrice;
            }
            if ($bedrooms > 0) {
                $where .= " AND bedrooms >= ?";
                $params[] = $bedrooms;
            }
            if ($bathrooms > 0) {
                $where .= " AND bathrooms >= ?";
                $params[] = $bathrooms;
            }
            if ($furnished !== '') {
                // properties table doesn't have furnished column
            }
            if ($yearBuilt > 0) {
                // properties table doesn't have year_built column
            }
            if ($areaMin > 0) {
                $where .= " AND area_sqft >= ?";
                $params[] = $areaMin;
            }
            if ($areaMax > 0) {
                $where .= " AND area_sqft <= ?";
                $params[] = $areaMax;
            }

            $orderBy = match ($sortBy) {
                'price_low' => 'price ASC',
                'price_high' => 'price DESC',
                'oldest' => 'created_at ASC',
                'area_large' => 'area_sqft DESC',
                'area_small' => 'area_sqft ASC',
                default => 'created_at DESC'
            };

            // Count total
            $countSql = "SELECT COUNT(*) as total FROM properties $where";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetch()['total'];

            // Get properties
            $sql = "SELECT * FROM properties $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("PageController.php: properties(): " . $e->getMessage());
        }

        // If no properties in DB, use sample data
        if (empty($properties)) {
            $sampleProperties = $this->getSampleProperties();
            $total = count($sampleProperties);

            // Apply filters to sample data
            if ($keyword !== '') {
                $sampleProperties = array_filter($sampleProperties, function ($p) use ($keyword) {
                    return stripos($p['name'] ?? '', $keyword) !== false
                        || stripos($p['location'] ?? '', $keyword) !== false
                        || stripos($p['description'] ?? '', $keyword) !== false;
                });
            }
            if ($type) {
                $sampleProperties = array_filter($sampleProperties, fn($p) => strtolower($p['type'] ?? '') === strtolower($type));
            }
            if ($listingType) {
                $sampleProperties = array_filter($sampleProperties, fn($p) => strtolower($p['listing_type'] ?? '') === strtolower($listingType));
            }
            if ($location) {
                $sampleProperties = array_filter($sampleProperties, fn($p) => stripos($p['location'] ?? '', $location) !== false);
            }
            if ($minPrice > 0) {
                $sampleProperties = array_filter($sampleProperties, fn($p) => ($p['price_num'] ?? 0) >= $minPrice);
            }
            if ($maxPrice > 0) {
                $sampleProperties = array_filter($sampleProperties, fn($p) => ($p['price_num'] ?? 0) <= $maxPrice);
            }

            // Sort
            usort($sampleProperties, function ($a, $b) use ($sortBy) {
                return match ($sortBy) {
                    'price_low' => ($a['price_num'] ?? 0) <=> ($b['price_num'] ?? 0),
                    'price_high' => ($b['price_num'] ?? 0) <=> ($a['price_num'] ?? 0),
                    default => 0
                };
            });

            $total = count($sampleProperties);
            $properties = array_slice($sampleProperties, $offset, $perPage);
        }

        return ['properties' => $properties, 'total' => $total];
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
        [$cmsTitle, $pageContent] = $this->loadPageContent('team');
        $team_members = [];
        $expertise_groups = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM team_members WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $obj = (object)$r;
                $team_members[] = $obj;
                $cat = $r['expertise'] ? explode(',', $r['expertise'])[0] : 'Other';
                $cat = trim($cat);
                if (!isset($expertise_groups[$cat])) $expertise_groups[$cat] = [];
                $expertise_groups[$cat][] = $obj;
            }
        } catch (\Exception $e) {
            error_log("Team error: " . $e->getMessage());
        }

        $data = [
            'page_title' => ($cmsTitle ?: 'Our Team') . ' - APS Dream Home',
            'page_description' => 'Meet the team behind APS Dream Home',
            'team_members' => $team_members,
            'expertise_groups' => $expertise_groups,
            'pageContent' => $pageContent
        ];
        $this->render('pages/team', $data);
    }

    // Careers
    public function careers()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM careers WHERE status = 'open' ORDER BY created_at DESC");
            $stmt->execute();
            $careers = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            $careers = [];
            error_log("Careers error: " . $e->getMessage());
        }

        $benefits = [];
        try {
            $bStmt = $this->db->prepare("SELECT * FROM career_benefits WHERE is_active = 1 ORDER BY sort_order ASC");
            $bStmt->execute();
            $benefits = $bStmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Career benefits error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'Careers - APS Dream Home',
            'page_description' => 'Join our team at APS Dream Home',
            'careers' => $careers,
            'benefits' => $benefits
        ];
        $this->render('pages/careers', $data);
    }

    // Services
    public function services()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('services');
        $services = [
            (object)[
                'icon' => 'fas fa-home',
                'color' => 'primary',
                'title' => 'Property Sales',
                'description' => 'Find your dream home from our extensive collection of residential and commercial properties with expert guidance.',
                'features' => 'Residential Properties,Commercial Properties,Investment Properties,Expert Negotiation'
            ],
            (object)[
                'icon' => 'fas fa-hand-holding-usd',
                'color' => 'success',
                'title' => 'Home Loan Assistance',
                'description' => 'We help you secure the best home loan with competitive interest rates from leading banks and financial institutions.',
                'features' => 'Loan Pre-approval,Best Interest Rates,Documentation Help,Quick Processing'
            ],
            (object)[
                'icon' => 'fas fa-gavel',
                'color' => 'warning',
                'title' => 'Legal Services',
                'description' => 'Complete legal support for property transactions including title verification, agreement drafting, and registration.',
                'features' => 'Title Verification,Agreement Drafting,Property Registration,Legal Consultation'
            ],
            (object)[
                'icon' => 'fas fa-couch',
                'color' => 'info',
                'title' => 'Interior Design',
                'description' => 'Turnkey interior design solutions for your new home - from modular kitchens to complete home interiors.',
                'features' => 'Modular Kitchen,Wardrobe Design,False Ceiling,Complete Interiors'
            ],
            (object)[
                'icon' => 'fas fa-file-contract',
                'color' => 'danger',
                'title' => 'Registry & Mutation',
                'description' => 'Hassle-free property registration and mutation services with experienced professionals handling all paperwork.',
                'features' => 'Sale Deed Registration,Mutation Services,Stamp Paper,Property Tax'
            ],
            (object)[
                'icon' => 'fas fa-building',
                'color' => 'secondary',
                'title' => 'Property Management',
                'description' => 'Comprehensive property management services including tenant management, rent collection, and maintenance.',
                'features' => 'Tenant Management,Rent Collection,Property Maintenance,Regular Inspections'
            ]
        ];
        $data = [
            'page_title' => ($cmsTitle ?: 'Our Services') . ' - APS Dream Home',
            'page_description' => 'Services offered by APS Dream Home',
            'pageContent' => $pageContent,
            'services' => $services
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
        [$cmsTitle, $pageContent] = $this->loadPageContent('privacy');
        $data = [
            'page_title' => ($cmsTitle ?: 'Privacy Policy') . ' - APS Dream Home',
            'page_description' => 'Our privacy policy',
            'pageContent' => $pageContent
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
        try {
            $news_items = $this->db->fetchAll(
                "SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC"
            );
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $news_items = [];
        }

        $categories = ['Project Launch', 'Company News', 'Market Updates'];

        $data = [
            'page_title' => 'News - APS Dream Home',
            'page_description' => 'Latest news and updates from APS Dream Home',
            'news_items' => $news_items,
            'categories' => $categories,
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
        [$cmsTitle, $pageContent] = $this->loadPageContent('faqs');
        $data = [
            'page_title' => ($cmsTitle ?: 'FAQs') . ' - APS Dream Home',
            'page_description' => 'Frequently asked questions about APS Dream Home',
            'pageContent' => $pageContent
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
            'page_description' => 'Read reviews from our satisfied users'
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
        $services = [];
        $portfolio = [];
        $team_members = [];
        $testimonials = [];
        $faqs = [];

        try {
            $stmt = $this->db->query("SELECT * FROM interior_services WHERE status = 'active' ORDER BY sort_order ASC LIMIT 6");
            $services = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[" . __CLASS__ . "] " . __FUNCTION__ . "() exception: " . $e->getMessage());

            $services = [
                ['id' => 1, 'title' => 'Residential Design', 'description' => 'Complete home interior design from concept to completion, including furniture, lighting, and decor selection.', 'icon' => 'fas fa-home', 'features' => json_encode(['Space Planning', 'Furniture Selection', 'Lighting Design', 'Color Consultation'])],
                ['id' => 2, 'title' => 'Commercial Design', 'description' => 'Professional office and commercial space design that enhances productivity and brand identity.', 'icon' => 'fas fa-building', 'features' => json_encode(['Office Layout', 'Brand Integration', 'Ergonomic Design', 'Reception Design'])],
                ['id' => 3, 'title' => 'Kitchen & Bath', 'description' => 'Custom kitchen and bathroom design with modern fixtures and optimal space utilization.', 'icon' => 'fas fa-utensils', 'features' => json_encode(['Cabinet Design', 'Countertop Selection', 'Fixture Installation', 'Storage Solutions'])],
                ['id' => 4, 'title' => 'Modular Furniture', 'description' => 'Custom modular furniture solutions including wardrobes, TV units, and storage systems.', 'icon' => 'fas fa-couch', 'features' => json_encode(['Wardrobe Design', 'TV Units', 'Bookshelves', 'Storage Systems'])],
                ['id' => 5, 'title' => 'False Ceiling', 'description' => 'Designer false ceiling solutions with modern lighting integration for aesthetic ceilings.', 'icon' => 'fas fa-lightbulb', 'features' => json_encode(['POP Design', 'LED Integration', 'Cove Lighting', 'Sound Proofing'])],
                ['id' => 6, 'title' => 'Turnkey Interiors', 'description' => 'Complete end-to-end interior solutions for new homes and offices under one roof.', 'icon' => 'fas fa-key', 'features' => json_encode(['End-to-End Service', 'Budget Planning', 'Project Management', 'Handover Guarantee'])],
            ];
        }

        try {
            $stmt = $this->db->query("SELECT * FROM interior_portfolio WHERE status = 'active' ORDER BY sort_order ASC LIMIT 6");
            $portfolio = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[" . __CLASS__ . "] " . __FUNCTION__ . "() exception: " . $e->getMessage());

            $portfolio = [];
        }

        try {
            $stmt = $this->db->query("SELECT * FROM interior_team WHERE status = 'active' ORDER BY sort_order ASC LIMIT 4");
            $team_members = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[" . __CLASS__ . "] " . __FUNCTION__ . "() exception: " . $e->getMessage());

            $team_members = [];
        }

        try {
            $stmt = $this->db->query("SELECT * FROM testimonials WHERE type = 'interior' AND status = 'active' ORDER BY sort_order ASC LIMIT 3");
            $testimonials = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[" . __CLASS__ . "] " . __FUNCTION__ . "() exception: " . $e->getMessage());

            $testimonials = [];
        }

        $faqs = [
            ['question' => 'What is the typical timeline for an interior design project?', 'answer' => 'Timelines vary based on scope. A single room typically takes 2-3 weeks, while a full home interior can take 6-12 weeks depending on customization and material availability.'],
            ['question' => 'Do you provide free consultation?', 'answer' => 'Yes, we offer a free initial consultation where we discuss your requirements, take measurements, and provide a ballpark estimate. There is no obligation to proceed.'],
            ['question' => 'What areas do you serve?', 'answer' => 'We primarily serve Gorakhpur, Lucknow, Varanasi, Kushinagar, and surrounding areas in Uttar Pradesh. For large projects, we can travel to other locations.'],
            ['question' => 'Can I see a 3D design before work starts?', 'answer' => 'Absolutely. We provide detailed 3D visualizations and walkthroughs for every project before any work begins, so you know exactly what to expect.'],
            ['question' => 'What payment terms do you offer?', 'answer' => 'We work on milestone-based payments. Typically 30% advance, 40% after design approval, 20% during execution, and 10% on completion and handover.'],
        ];

        $this->render('pages/interior_design', [
            'page_title' => 'Interior Design - APS Dream Home',
            'page_description' => 'Professional interior design services in Gorakhpur, Lucknow, Varanasi',
            'services' => $services,
            'portfolio' => $portfolio,
            'team_members' => $team_members,
            'testimonials' => $testimonials,
            'faqs' => $faqs,
        ]);
    }

    // Construction Services
    public function constructionServices()
    {
        $projects = [];
        $testimonials = [];

        try {
            $stmt = $this->db->query("SELECT * FROM construction_projects WHERE status IN ('completed', 'in_progress') ORDER BY created_at DESC LIMIT 6");
            $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $projects = [];
        }

        try {
            $stmt = $this->db->query("SELECT * FROM testimonials WHERE type = 'construction' AND status = 'active' LIMIT 3");
            $testimonials = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $testimonials = [];
        }

        $this->render('pages/construction_services', [
            'page_title' => 'Construction & Contracting - APS Dream Home',
            'page_description' => 'Professional construction and project contracting services',
            'projects' => $projects,
            'testimonials' => $testimonials,
        ]);
    }

    // Construction Inquiry Form
    public function constructionInquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/construction-services');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $project_type = trim($_POST['project_type'] ?? '');
        $budget = floatval($_POST['budget'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($phone)) {
            $_SESSION['flash_error'] = 'Name and phone are required';
            header('Location: ' . BASE_URL . '/construction-services#contact-form');
            exit;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO inquiries (name, email, phone, message, type, status, priority, created_at) VALUES (?, ?, ?, ?, 'project', 'pending', 'medium', NOW())");
            $stmt->execute([$name, $email, $phone, "Construction Inquiry - {$project_type}" . ($budget > 0 ? " | Budget: ₹{$budget}" : '') . ($location ? " | Location: {$location}" : '') . ($message ? " | Details: {$message}" : '')]);

            // Auto-wire to CRM lead
            try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>"Construction: {$project_type}",'type'=>'project']); } catch (\Exception $e3) {}

            // Also save to service_interests if table exists
            try {
                $sStmt = $this->db->prepare("INSERT INTO service_interests (lead_id, service_type, status, notes, created_at) VALUES (?, 'construction', 'pending', ?, NOW())");
                $sStmt->execute([$this->db->lastInsertId(), "Budget: ₹{$budget}, Location: {$location}, Type: {$project_type}"]);
            } catch (\Exception $e) {
                error_log('PageController constructionInquiry service interests: ' . $e->getMessage());
            }

            $_SESSION['flash_success'] = 'Thank you! We will contact you shortly regarding your construction project.';
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $_SESSION['flash_error'] = 'Something went wrong. Please try again.';
        }

        header('Location: ' . BASE_URL . '/construction-services#contact-form');
        exit;
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
        [$cmsTitle, $pageContent] = $this->loadPageContent('terms-conditions');
        $data = [
            'page_title' => ($cmsTitle ?: 'Terms & Conditions') . ' - APS Dream Home',
            'page_description' => 'Detailed terms and conditions of APS Dream Home',
            'pageContent' => $pageContent
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
        $this->requireLogin();
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
        $reviews = [];

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

                    // Fetch approved reviews
                    $revStmt = $this->db->prepare("SELECT r.*, COALESCE(u.name, 'Anonymous') as user_name FROM property_reviews r LEFT JOIN users u ON r.customer_id = u.id WHERE r.property_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC");
                    $revStmt->execute([$id]);
                    $reviews = $revStmt->fetchAll(\PDO::FETCH_ASSOC);
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
            'related_properties' => $related_properties,
            'reviews' => $reviews
        ];
        $this->render('properties/detail', $data);
    }

    // Submit Property Review
    public function reviewSubmit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /properties');
            exit;
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);
        $reviewText = trim($_POST['review_text'] ?? '');

        if (!$propertyId || !$name || !$email || !$rating || !$reviewText) {
            $_SESSION['flash_error'] = 'All fields are required.';
            header('Location: /properties/' . $propertyId);
            exit;
        }

        if ($rating < 1 || $rating > 5) {
            $_SESSION['flash_error'] = 'Rating must be between 1 and 5.';
            header('Location: /properties/' . $propertyId);
            exit;
        }

        try {
            // Find or create a user record for this reviewer
            $customerId = null;
            $userStmt = $this->db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $userStmt->execute([$email]);
            $existingUser = $userStmt->fetch(\PDO::FETCH_ASSOC);
            if ($existingUser) {
                $customerId = $existingUser['id'];
            } else {
                $createStmt = $this->db->prepare("INSERT INTO users (name, email, role, created_at) VALUES (?, ?, 'customer', NOW())");
                $createStmt->execute([$name, $email]);
                $customerId = $this->db->lastInsertId();
            }

            $stmt = $this->db->prepare("INSERT INTO property_reviews (customer_id, property_id, rating, review_text, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$customerId, $propertyId, $rating, $reviewText]);

            $_SESSION['flash_success'] = 'Thank you! Your review has been submitted and is pending approval.';
        } catch (\Exception $e) {
            error_log("Review submit error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Something went wrong. Please try again.';
        }

        header('Location: /properties/' . $propertyId);
        exit;
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
                        error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

                        $plots = [];
                    }

                    // Get related projects (same district, excluding current)
                    try {
                        $relatedStmt = $this->db->prepare("SELECT * FROM sites WHERE district = ? AND id != ? AND status IN ('active', 'completed') ORDER BY site_name LIMIT 4");
                        $relatedStmt->execute([$project->district, $project->id]);
                        $related_projects = $relatedStmt->fetchAll(\PDO::FETCH_OBJ);
                    } catch (\Exception $e) {
                        error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

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
        [$cmsTitle, $pageContent] = $this->loadPageContent('gallery');

        $galleryImages = [];
        $galleryCategories = [];
        try {
            $stmt = $this->db->query("SELECT * FROM gallery WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC");
            $galleryImages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $catStmt = $this->db->query("SELECT DISTINCT category FROM gallery WHERE status = 'active' ORDER BY category");
            foreach ($catStmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $galleryCategories[] = $row['category'];
            }
        } catch (\Exception $e) {
            error_log('Gallery error: ' . $e->getMessage());
        }

        $data = [
            'page_title' => ($cmsTitle ?: 'Gallery') . ' - APS Dream Home',
            'page_description' => 'Photo and video gallery of our projects',
            'pageContent' => $pageContent,
            'galleryImages' => $galleryImages,
            'galleryCategories' => $galleryCategories
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
        $careers = [];
        try {
            $stmt = $this->db->prepare("SELECT id, title FROM careers WHERE status = 'open' ORDER BY title");
            $stmt->execute();
            $careers = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {}

        $data = [
            'page_title' => 'Apply for a Job - APS Dream Home',
            'page_description' => 'Submit your job application',
            'careers' => $careers
        ];
        $this->render('pages/career_apply', $data);
    }

    // Submit Career Application
    public function submitCareerApplication()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->careerApply();
        }

        $careerId = (int)($_POST['career_id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $coverLetter = trim($_POST['cover_letter'] ?? '');
        $experienceYears = (int)($_POST['experience_years'] ?? 0);
        $currentCompany = trim($_POST['current_company'] ?? '');

        if (empty($fullName) || empty($email) || $careerId <= 0) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            return $this->careerApply();
        }

        try {
            $resumePath = null;
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(__DIR__, 3) . '/assets/uploads/resumes/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
                $filename = 'resume_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $uploadDir . $filename)) {
                    $resumePath = 'assets/uploads/resumes/' . $filename;
                }
            }

            $stmt = $this->db->prepare("INSERT INTO career_applications (career_id, full_name, email, phone, resume_path, cover_letter, experience_years, current_company, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$careerId, $fullName, $email, $phone, $resumePath, $coverLetter, $experienceYears, $currentCompany]);

            $_SESSION['success'] = 'Your application has been submitted successfully! We will review it and get back to you soon.';
            header('Location: ' . BASE_URL . '/careers');
            exit;
        } catch (\Exception $e) {
            error_log("Career application error: " . $e->getMessage());
            $_SESSION['error'] = 'Something went wrong. Please try again.';
            return $this->careerApply();
        }
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
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

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
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

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
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

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
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

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
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

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
        $this->requireLogin();
        $userId = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0;

        $investments = [];
        try {
            $stmt = $this->db->prepare("SELECT p.*, s.site_name, s.district as site_location 
                FROM plots p LEFT JOIN sites s ON p.colony_id = s.id 
                WHERE p.customer_id = ? AND p.is_active = 1 ORDER BY p.updated_at DESC LIMIT 20");
            $stmt->execute([$userId]);
            $investments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Investments fetch error: ' . $e->getMessage());
        }

        $data = [
            'page_title' => 'My Investments - APS Dream Home',
            'page_description' => 'Track your property investments',
            'investments' => $investments
        ];
        $this->render('pages/user/investments', $data);
    }

    // User Edit Profile
    public function userEditProfile()
    {
        $this->requireLogin();
        $data = [
            'page_title' => 'Edit Profile - APS Dream Home',
            'page_description' => 'Update your profile information'
        ];
        $this->render('pages/user/edit_profile', $data);
    }

    // Stamp Duty Calculator
    public function stampDutyCalculator()
    {
        $data = [
            'page_title' => 'Stamp Duty & Registration Calculator - APS Dream Home',
            'page_description' => 'Calculate stamp duty, registration fees and total cost for property purchase'
        ];
        $this->render('pages/tools/stamp_duty_calculator', $data);
    }

    // Plot Size Converter
    public function plotSizeConverter()
    {
        $data = [
            'page_title' => 'Plot Size Converter - APS Dream Home',
            'page_description' => 'Convert between square feet, square meters, acres, bigha, gaj and more'
        ];
        $this->render('pages/tools/plot_converter', $data);
    }

    // Plot Converter (new alias)
    public function plotConverter()
    {
        $data = [
            'page_title' => 'Plot Area Converter - APS Dream Home',
            'page_description' => 'Convert between sqft, sqm, acre, bigha, gaj, katha, marla and more'
        ];
        $this->render('pages/tools/plot_converter', $data);
    }

    // Valuation Calculator (new)
    public function valuationCalculator()
    {
        $districts = [];
        try {
            $stmt = $this->db->query("SELECT id, name FROM districts ORDER BY name");
            $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('valuationCalculator districts error: ' . $e->getMessage());
        }
        $data = [
            'page_title' => 'Property Valuation Calculator - APS Dream Home',
            'page_description' => 'Estimate your property value based on location, type and area',
            'districts' => $districts,
        ];
        $this->render('pages/tools/valuation_calculator', $data);
    }

    // Home Loan Eligibility
    public function homeLoanEligibility()
    {
        $data = [
            'page_title' => 'Home Loan Eligibility Calculator - APS Dream Home',
            'page_description' => 'Check your home loan eligibility based on income and existing obligations'
        ];
        $this->render('pages/tools/loan_eligibility', $data);
    }

    // Property Valuation
    public function propertyValuation()
    {
        $districts = [];
        try {
            $stmt = $this->db->query("SELECT id, name FROM districts ORDER BY name");
            $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('propertyValuation districts error: ' . $e->getMessage());
        }
        $data = [
            'page_title' => 'Property Valuation - APS Dream Home',
            'page_description' => 'Get approximate market value of your property',
            'districts' => $districts,
        ];
        $this->render('pages/tools/property_valuation', $data);
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
    // Schedule Meeting
    public function scheduleMeeting()
    {
        $data = [
            'page_title' => 'Schedule a Meeting - APS Dream Home',
            'page_description' => 'Book an appointment with our users'
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

                // Auto-wire to CRM lead
                try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>$fullMessage,'type'=>$formType]); } catch (\Exception $e3) {}

                // Also save to contacts table
                try {
                    $contactStmt = $this->db->prepare("INSERT INTO contacts (name, email, phone, subject, message, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $contactStmt->execute([$name, $email, $phone, 'Quick Inquiry - ' . ucfirst(str_replace('_', ' ', $requirement)), $fullMessage, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
                } catch (\Exception $e2) {
                    error_log('PageController handleQuickInquiry contacts: ' . $e2->getMessage());
                }

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
            $stateId = (int)($_POST['selected_state_id'] ?? 0);
            $districtId = (int)($_POST['selected_district_id'] ?? 0);
            $cityName = trim($_POST['selected_city_name'] ?? $_POST['city'] ?? '');

            if (empty($name) || empty($phone) || empty($propertyType)) {
                $_SESSION['flash_error'] = 'Please fill in all required fields.';
                $this->redirect('/list-property');
                return;
            }

            try {
                // Handle image upload
                $imagePath = null;
                if (!empty($_FILES['property_image']['name']) && $_FILES['property_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../../../assets/images/properties/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $v = \UploadValidator::validate($_FILES['property_image'], ['types' => 'images', 'max_size' => 5]);
                    if ($v['valid']) {
                        $safeName = \UploadValidator::safeFilename($_FILES['property_image']['name']);
                        $newName = 'prop_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($safeName, PATHINFO_EXTENSION);
                        $targetPath = $uploadDir . $newName;
                        if (move_uploaded_file($_FILES['property_image']['tmp_name'], $targetPath)) {
                            \App\Core\ImageOptimizer::optimizeStatic($targetPath);
                            $imagePath = 'properties/' . $newName;
                            // Mirror to StorageManager (S3 or local fallback).
                            try {
                                \App\Services\Storage\StorageManager::getInstance()->put(
                                    'assets/images/' . $imagePath,
                                    file_get_contents($targetPath),
                                    [
                                        'ContentType'   => mime_content_type($targetPath) ?: 'image/jpeg',
                                        'Cache-Control' => 'public, max-age=31536000, immutable',
                                    ]
                                );
                            } catch (\Throwable $e) {
                                error_log('PageController::listPropertySubmit storage mirror: ' . $e->getMessage());
                            }
                        }
                    }
                }

                // Track WHO posted this property (associate/customer/agent)
                $postedBy = null;
                $postedByType = null;
                $userId = null;

                @session_start();

                if (isset($_SESSION['associate_id'])) {
                    $postedBy = $_SESSION['associate_id'];
                    $postedByType = 'associate';
                    $userId = $_SESSION['associate_id'];
                } elseif (isset($_SESSION['user_id'])) {
                    $postedBy = $_SESSION['user_id'];
                    $postedByType = 'customer';
                    $userId = $_SESSION['user_id'];
                } elseif (isset($_SESSION['agent_id'])) {
                    $postedBy = $_SESSION['agent_id'];
                    $postedByType = 'agent';
                    $userId = $_SESSION['agent_id'];
                }

                // Try to save to user_properties table
                $savedToUserProperties = false;
                try {
                    $this->db->query("SELECT 1 FROM user_properties LIMIT 1");

                    $stmt = $this->db->prepare("
                        INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, image, state_id, district_id, city_name, status, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                    ");
                    $stmt->execute([
                        $userId,
                        $postedBy,
                        $postedByType,
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
                        $imagePath,
                        $stateId ?: null,
                        $districtId ?: null,
                        $cityName ?: null
                    ]);
                    $propertyId = $this->db->lastInsertId();
                    $savedToUserProperties = true;
                } catch (\Exception $e1) {
                    // Table might not exist, create it
                    if (strpos($e1->getMessage(), "doesn't exist") !== false) {
                        $this->createUserPropertiesTable();
                        $stmt = $this->db->prepare("
                            INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, image, state_id, district_id, city_name, status, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                        ");
                        $stmt->execute([
                            $userId,
                            $postedBy,
                            $postedByType,
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
                            $imagePath,
                            $stateId ?: null,
                            $districtId ?: null,
                            $cityName ?: null
                        ]);
                        $propertyId = $this->db->lastInsertId();
                        $savedToUserProperties = true;
                        error_log("PageController.php: " . $e1->getMessage());
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
                    $inqStmt = $this->db->prepare("
                        INSERT INTO inquiries (name, email, phone, message, type, status, priority, user_id, created_at)
                        VALUES (?, ?, ?, ?, 'property', 'pending', 'medium', ?, NOW())
                    ");
                    $inqStmt->execute([$name, $email, $phone, $message, $postedBy]);
                } catch (\Exception $e2) {
                    error_log("Inquiry save error: " . $e2->getMessage());
                }

                // Auto-wire to CRM lead
                try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>$message,'type'=>'property','created_by'=>$postedBy]); } catch (\Exception $e3) {}

                // Success message with user-specific redirect
                $_SESSION['flash_success'] = 'Thank you! Your property listing request has been submitted. Our team will contact you within 24 hours to verify the details.';

                // Redirect based on user type
                if ($postedByType === 'associate') {
                    $this->redirect('/associate/properties');
                    return;
                } elseif ($postedByType === 'customer') {
                    $this->redirect('/user/properties');
                    return;
                }
            } catch (\Exception $e) {
                error_log("Property listing error: " . $e->getMessage());
                $_SESSION['flash_error'] = 'Failed to submit. Please try again or call us directly.';
            }
        }
        $this->redirect('/list-property');
    }

    // Bank Details Page
    public function bank()
    {
        $data = [
            'page_title' => 'Bank Details - APS Dream Home',
            'page_description' => 'Official banking information for secure property transactions',
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Bank Details', 'url' => '']
            ]
        ];
        $this->render('pages/bank', $data);
    }

    // Legal Main Page
    public function legal()
    {
        $legal_docs = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM legal_documents ORDER BY category, title");
            $stmt->execute();
            $legal_docs = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $legal_docs = [];
        }

        $data = [
            'page_title' => 'Legal Documents - APS Dream Home',
            'page_description' => 'View our official certifications and legal papers',
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Legal', 'url' => '']
            ],
            'legal_docs' => $legal_docs
        ];
        $this->render('pages/legal/legal', $data);
    }

    // Legal Privacy Policy
    public function legalPrivacy()
    {
        $data = [
            'page_title' => 'Privacy Policy - APS Dream Home',
            'page_description' => 'How we collect, use, and protect your data',
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Privacy Policy', 'url' => '']
            ]
        ];
        $this->render('pages/legal/privacy', $data);
    }

    // Legal Terms of Service
    public function legalTermsPage()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('terms');
        $data = [
            'page_title' => ($cmsTitle ?: 'Terms of Service') . ' - APS Dream Home',
            'page_description' => 'Please read our terms carefully before using our services',
            'pageContent' => $pageContent,
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Terms of Service', 'url' => '']
            ]
        ];
        $this->render('pages/legal/terms', $data);
    }

    // Budha City Project Page
    public function budhaCity()
    {
        $data = [
            'page_title' => 'Budha City - APS Dream Home',
            'page_description' => 'Integrated Township at Premwaliya, Kushinagar Highway',
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Budha City', 'url' => '']
            ]
        ];
        $this->render('pages/budhacity', $data);
    }

    // Suyoday Colony Page
    public function suyodayColonyPage()
    {
        $data = [
            'page_title' => 'Suyoday Colony - APS Dream Home',
            'page_description' => 'Premium residential plots in Gorakhpur with modern infrastructure'
        ];
        $this->render('pages/suyoday_colony', $data);
    }

    public function toolsHub()
    {
        $pageTitle = 'Tools Hub';
        $metaDescription = 'All property calculators in one place - EMI, Stamp Duty, Plot Converter, Loan Eligibility, Valuation and more';
        return $this->render('pages/tools/hub', compact('pageTitle', 'metaDescription'));
    }

    public function rentVsBuy()
    {
        $pageTitle = 'Rent vs Buy Calculator';
        $metaDescription = 'Compare renting vs buying property over 20 years with comprehensive financial analysis';
        return $this->render('pages/tools/rent_vs_buy', compact('pageTitle', 'metaDescription'));
    }

    public function sipVsRealestate()
    {
        $pageTitle = 'SIP vs Real Estate Calculator';
        $metaDescription = 'Compare SIP mutual fund investments vs real estate property investments over 20 years';
        return $this->render('pages/tools/sip_vs_realestate', compact('pageTitle', 'metaDescription'));
    }

    public function capitalGains()
    {
        $pageTitle = 'Capital Gains Calculator';
        $metaDescription = 'Calculate LTCG and STCG tax on property sales with CII indexation for Indian real estate';
        return $this->render('pages/tools/capital_gains', compact('pageTitle', 'metaDescription'));
    }

    public function gstCalculator()
    {
        $pageTitle = 'GST Calculator for Property';
        $metaDescription = 'Calculate GST on under-construction and ready-to-move properties in India';
        return $this->render('pages/tools/gst_calculator', compact('pageTitle', 'metaDescription'));
    }

    public function constructionCostEstimator()
    {
        $pageTitle = 'Construction Cost Estimator';
        $metaDescription = 'Estimate building construction cost based on plot area, floors, and quality';
        return $this->render('pages/tools/construction_cost', compact('pageTitle', 'metaDescription'));
    }

    public function rentalYieldCalculator()
    {
        $pageTitle = 'Rental Yield Calculator';
        $metaDescription = 'Calculate rental income, yield, and payback period for investment property';
        return $this->render('pages/tools/rental_yield', compact('pageTitle', 'metaDescription'));
    }

    public function propertyTaxCalculator()
    {
        $pageTitle = 'Property Tax Calculator';
        $metaDescription = 'Estimate annual property tax with breakdown by type, city, and occupancy';
        return $this->render('pages/tools/property_tax', compact('pageTitle', 'metaDescription'));
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

    /**
     * Dynamic colony/project detail page
     */
    public function colonyDetail($slug = null)
    {
        if (!$slug) {
            $this->redirect('/projects');
            return;
        }
        $colony = $this->db->fetch("SELECT c.*, d.name as district_name, s.name as state_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id WHERE c.slug = ? AND c.is_active = 1", [$slug]);
        if (!$colony) {
            $this->notFound();
            return;
        }
        $availablePlots = $this->db->fetchAll("SELECT * FROM plots WHERE colony_id = ? AND status = 'available' ORDER BY plot_number LIMIT 20", [$colony['id']]);
        $this->render('pages/colony_detail', [
            'page_title' => $colony['meta_title'] ?: $colony['name'] . ' - APS Dream Home',
            'page_description' => $colony['meta_description'] ?: $colony['name'] . ' - Premium residential plots',
            'colony' => $colony,
            'availablePlots' => $availablePlots,
        ]);
    }

    /**
     * Public plot listing for a colony
     */
    public function colonyPlots($slug = null)
    {
        if (!$slug) {
            $this->redirect('/projects');
            return;
        }
        $colony = $this->db->fetch("SELECT * FROM colonies WHERE slug = ? AND is_active = 1", [$slug]);
        if (!$colony) {
            $this->notFound();
            return;
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 24;
        $offset = ($page - 1) * $perPage;
        $totalPlots = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM plots WHERE colony_id = ? AND status = 'available'", [$colony['id']]);
        $plots = $this->db->fetchAll("SELECT * FROM plots WHERE colony_id = ? AND status = 'available' ORDER BY plot_number LIMIT $perPage OFFSET $offset", [$colony['id']]);
        $this->render('pages/colony_plots', [
            'page_title' => 'Available Plots - ' . $colony['name'],
            'colony' => $colony,
            'plots' => $plots,
            'totalPlots' => $totalPlots,
            'current_page' => $page,
            'total_pages' => max(1, ceil($totalPlots / $perPage)),
        ]);
    }

    public function documentGallery()
    {
        $category = $_GET['category'] ?? '';
        $search = trim($_GET['q'] ?? '');

        $where = 'WHERE is_published = 1';
        $params = [];
        if ($category) {
            $where .= ' AND category = ?';
            $params[] = $category;
        }
        if ($search) {
            $where .= ' AND (title LIKE ? OR description LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $documents = $this->db->fetchAll("SELECT * FROM document_gallery $where ORDER BY sort_order ASC, created_at DESC", $params);
        $categories = $this->db->fetchAll("SELECT DISTINCT category FROM document_gallery WHERE is_published = 1 ORDER BY category", []);

        $data = [
            'page_title' => 'Document Gallery - APS Dream Home',
            'page_description' => 'Download property brochures, guides, legal documents and more',
            'documents' => $documents,
            'categories' => $categories,
            'selected_category' => $category,
            'search_query' => $search
        ];
        $this->render('pages/document_gallery', $data);
    }

    public function downloadDocument($id)
    {
        $doc = $this->db->fetch("SELECT * FROM document_gallery WHERE id = ? AND is_published = 1", [$id]);
        if (!$doc) {
            $this->notFound();
            return;
        }
        // Increment download count
        try {
            $this->db->query("UPDATE document_gallery SET downloads_count = downloads_count + 1 WHERE id = ?", [$id]);
        } catch (\Exception $e) {
            error_log("PageController.php: " . $e->getMessage());
        }

        $filePath = __DIR__ . '/../../../../assets/' . $doc['file_path'];
        if (!file_exists($filePath)) {
            $_SESSION['flash_error'] = 'File not found. Please contact support.';
            $this->redirect('/documents');
            return;
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($doc['file_path']) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function reraLookup()
    {
        $reraNumber = $_GET['rera_number'] ?? '';
        $data = ['page_title' => 'RERA Compliance Lookup - APS Dream Home', 'result' => null];
        if ($reraNumber) {
            try {
                $user = $this->db->fetch("SELECT id, name, email, phone, rera_number, is_rera_approved, rera_deduction_wallet FROM users WHERE rera_number = ?", [$reraNumber]);
                if ($user) {
                    $requests = $this->db->fetchAll("SELECT * FROM rera_requests WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
                    $data['result'] = ['found' => true, 'user' => $user, 'requests' => $requests];
                } else {
                    $data['result'] = ['found' => false, 'message' => 'No record found for RERA number: ' . htmlspecialchars($reraNumber)];
                }
            } catch (\Exception $e) {
                error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

                $data['result'] = ['found' => false, 'message' => 'Lookup failed. Please try again.'];
            }
        }
        $this->render('pages/rera_lookup', $data);
    }

    public function userPropertyDetail($id = null)
    {
        if (!$id) {
            $this->redirect('/properties');
            return;
        }

        try {
            $stmt = $this->db->prepare("SELECT up.*, s.name as state_name, d.name as district_name FROM user_properties up LEFT JOIN states s ON up.state_id = s.id LEFT JOIN districts d ON up.district_id = d.id WHERE up.id = ? AND up.status = 'approved' LIMIT 1");
            $stmt->execute([$id]);
            $property = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $property = null;
        }

        if (!$property) {
            $this->notFound();
            return;
        }

        // Increment view count
        try {
            $this->db->query("UPDATE user_properties SET views = views + 1 WHERE id = ?", [$id]);
        } catch (\Exception $e) {
            error_log("PageController.php: " . $e->getMessage());
        }

        // Track property view for lead generation
        try {
            $this->trackPropertyView($id);
        } catch (\Exception $e) {
            error_log("PageController: trackPropertyView error: " . $e->getMessage());
        }

        $data = [
            'page_title' => ($property['name'] ?? 'Property') . ' - APS Dream Home',
            'page_description' => 'View property details',
            'property' => $property
        ];
        $this->render('properties/user_detail', $data);
    }

    /**
     * Track property view and auto-create leads from browsing behavior
     */
    private function trackPropertyView(int $propertyId): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';

        // Insert into property_views
        try {
            $this->db->query(
                "INSERT INTO property_views (property_id, user_id, ip_address, user_agent, referrer, viewed_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$propertyId, $userId, $ip, $ua, $referrer]
            );
        } catch (\Exception $e) {
            // Table might not exist yet — silently skip
            if (strpos($e->getMessage(), 'property_views') !== false) {
                return;
            }
            throw $e;
        }

        // Auto-create lead from browsing behavior (3+ property views in session)
        if (!$userId) {
            if (!isset($_SESSION['property_view_count'])) {
                $_SESSION['property_view_count'] = 0;
                $_SESSION['property_views_list'] = [];
            }
            $_SESSION['property_view_count']++;
            if (!in_array($propertyId, $_SESSION['property_views_list'])) {
                $_SESSION['property_views_list'][] = $propertyId;
            }

            // Auto-create lead after 3+ property views
            if ($_SESSION['property_view_count'] >= 3 && !empty($_SESSION['auto_lead_created'])) {
                // Already created, skip
            } elseif ($_SESSION['property_view_count'] >= 3) {
                $viewedIds = $_SESSION['property_views_list'];
                $propNames = [];
                try {
                    $placeholders = implode(',', array_fill(0, count($viewedIds), '?'));
                    $rows = $this->db->fetchAll("SELECT name FROM user_properties WHERE id IN ($placeholders)", $viewedIds);
                    $propNames = array_column($rows, 'name');
                } catch (\Exception $e) { /* skip */ }

                try {
                    $this->db->query(
                        "INSERT INTO leads (name, phone, email, source, status, priority, property_interest, notes, created_by, lead_score, created_at) VALUES (?, ?, ?, 'browsing_behavior', 'nurture', 'low', ?, ?, 0, 30, NOW())",
                        [
                            'Anonymous Visitor',
                            '',
                            '',
                            implode(', ', $propNames),
                            "Auto-created: Viewed " . count($viewedIds) . " properties — " . implode(', ', $propNames)
                        ]
                    );
                    $_SESSION['auto_lead_created'] = true;
                } catch (\Exception $e) {
                    error_log("Auto lead creation error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * AJAX endpoint: Record property interest (creates lead)
     */
    public function propertyInterest()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        $source = trim($_POST['source'] ?? 'property_interest');

        if (!$propertyId || empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Phone number is required']);
            return;
        }

        // Validate phone (basic Indian format)
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phoneClean) < 10) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number']);
            return;
        }

        // Get property name
        $propName = '';
        try {
            $prop = $this->db->fetch("SELECT name FROM user_properties WHERE id = ?", [$propertyId]);
            $propName = $prop['name'] ?? '';
        } catch (\Exception $e) { /* skip */ }

        // Check if lead already exists for this phone
        $existingLead = null;
        try {
            $existingLead = $this->db->fetch("SELECT id FROM leads WHERE phone = ? AND deleted_at IS NULL ORDER BY id DESC LIMIT 1", [$phoneClean]);
        } catch (\Exception $e) { /* skip */ }

        $userId = $_SESSION['user_id'] ?? 0;

        try {
            if ($existingLead) {
                // Update existing lead
                $this->db->query(
                    "UPDATE leads SET property_interest = CONCAT(COALESCE(property_interest, ''), ?), notes = CONCAT(COALESCE(notes, ''), ?), lead_score = LEAST(100, lead_score + 10), updated_at = NOW() WHERE id = ?",
                    [
                        ($propName ? ", $propName" : ''),
                        "\n" . date('Y-m-d H:i') . " — Expressed interest in: $propName (Budget: $budget)",
                        $existingLead['id']
                    ]
                );
                $leadId = $existingLead['id'];
            } else {
                // Create new lead
                $this->db->query(
                    "INSERT INTO leads (name, phone, email, source, status, priority, property_interest, budget_range, notes, created_by, lead_score, created_at) VALUES (?, ?, '', ?, 'new', 'high', ?, ?, ?, 0, 50, NOW())",
                    [
                        $name ?: 'Unknown',
                        $phoneClean,
                        $source,
                        $propName,
                        $budget,
                        "Expressed interest in: $propName" . ($budget ? " (Budget: $budget)" : '')
                    ]
                );
                $leadId = $this->db->lastInsertId();
            }

            // Also save to inquiries table for backward compatibility
            try {
                $this->db->query(
                    "INSERT INTO inquiries (property_id, name, email, phone, message, type, property_type, status, priority, lead_id, created_at) VALUES (?, ?, '', ?, ?, 'property_inquiry', 'user_property', 'new', 'high', ?, NOW())",
                    [
                        $propertyId,
                        $name ?: 'Unknown',
                        $phoneClean,
                        "Interested in: $propName" . ($budget ? " | Budget: $budget" : ''),
                        $leadId
                    ]
                );
            } catch (\Exception $e) { /* skip */ }

            // Notify property owner
            try {
                $prop = $this->db->fetch("SELECT * FROM user_properties WHERE id = ?", [$propertyId]);
                if ($prop && !empty($prop['email'])) {
                    $ownerMsg = "New interest in your property '{$prop['name']}'!\n\nFrom: " . ($name ?: 'Unknown') . " ($phoneClean)\nBudget: $budget\n\nAPS Dream Home";
                    @mail($prop['email'], "Interest in your property: {$prop['name']}", $ownerMsg, "From: info@apsdreamhome.com\r\nReply-To: $phoneClean@aps.local");
                }
            } catch (\Exception $e) { /* skip */ }

            echo json_encode(['success' => true, 'message' => 'Interest recorded! Our team will contact you shortly.', 'lead_id' => $leadId]);
        } catch (\Exception $e) {
            error_log("Property interest error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
        }
    }

    public function propertyInquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/properties');
            return;
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$propertyId || empty($name) || empty($phone)) {
            $_SESSION['flash_error'] = 'Please fill in all required fields.';
            $this->redirect('/listing/' . $propertyId);
            return;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO inquiries (property_id, name, email, phone, message, type, property_type, status, priority, created_at) VALUES (?, ?, ?, ?, ?, 'property_inquiry', 'user_property', 'new', 'high', NOW())");
            $stmt->execute([$propertyId, $name, $email, $phone, $message]);

            // Auto-wire to CRM lead
            try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>$message,'type'=>'property_inquiry','property_id'=>$propertyId]); } catch (\Exception $e3) {}

            // Also notify property owner
            try {
                $prop = $this->db->fetch("SELECT * FROM user_properties WHERE id = ?", [$propertyId]);
                if ($prop && !empty($prop['email'])) {
                    $ownerMsg = "New inquiry for your property '{$prop['name']}'!\n\nFrom: $name ($phone, $email)\nMessage: $message\n\nAPS Dream Home";
                    @mail($prop['email'], "Inquiry for your property: {$prop['name']}", $ownerMsg, "From: info@apsdreamhome.com\r\nReply-To: $email");
                }
            } catch (\Exception $e) {
                error_log("Property owner notification error: " . $e->getMessage());
            }

            $_SESSION['flash_success'] = 'Your inquiry has been sent. The property owner will contact you soon!';
        } catch (\Exception $e) {
            error_log("Property inquiry error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to send inquiry. Please call us at +91 92771 21112.';
        }

        $this->redirect('/listing/' . $propertyId);
    }

    public function aiChatbotPage()
    {
        $this->render('pages/ai/chatbot', ['page_title' => 'AI Property Assistant']);
    }

    public function colonies()
    {
        try {
            $rows = $this->db->fetchAll("SELECT c.*, d.name as district_name, s.name as state_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id WHERE c.is_active = 1 ORDER BY c.name");
            $totalPlots = $this->db->fetch("SELECT COUNT(*) as total FROM plots WHERE colony_id IN (SELECT id FROM colonies WHERE is_active = 1) AND status = 'available'")['total'] ?? 0;
            $colonies = [];
            foreach ($rows as $row) {
                $amenities = [];
                if (!empty($row['amenities'])) {
                    $amenitiesList = json_decode($row['amenities'], true);
                    if (is_array($amenitiesList)) {
                        foreach ($amenitiesList as $amenity) {
                            $amenities[] = is_string($amenity) ? $amenity : ($amenity['name'] ?? '');
                        }
                    }
                }
                $highlights = [];
                if (!empty($row['highlights'])) {
                    $highlightsList = json_decode($row['highlights'], true);
                    if (is_array($highlightsList)) {
                        foreach ($highlightsList as $highlight) {
                            $highlights[] = is_string($highlight) ? $highlight : ($highlight['text'] ?? '');
                        }
                    }
                }
                $colonies[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'slug' => $row['slug'] ?? '',
                    'district_name' => $row['district_name'],
                    'state_name' => $row['state_name'],
                    'available_plots' => $row['total_plots'] ?? 0,
                    'price_per_sqft' => $row['price_per_sqft'] ?? 0,
                    'description' => $row['description'] ?? '',
                    'amenities' => $amenities,
                    'highlights' => $highlights,
                ];
            }
            $colonyStats = [
                'total_colonies' => count($colonies),
                'total_area' => array_sum(array_map(function ($c) {
                    return intval($c['available_plots']) * 1200;
                }, $colonies)) . ' sqft',
                'total_plots' => $totalPlots,
                'cities_covered' => count(array_unique(array_column($colonies, 'district_name')))
            ];
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $colonies = [];
            $colonyStats = ['total_colonies' => 0, 'total_area' => '0', 'total_plots' => 0, 'cities_covered' => 0];
        }
        $this->render('pages/colonies', ['colonies' => $colonies, 'colony_stats' => $colonyStats]);
    }

    /**
     * Analytics page
     */
    public function analytics()
    {
        $this->render('pages/analytics', ['page_title' => 'Analytics']);
    }

    /**
     * Calculator page
     */
    public function calc()
    {
        $this->render('pages/calc', ['page_title' => 'Calculator']);
    }

    /**
     * Inquiry page
     */
    public function inquiry()
    {
        $this->render('pages/inquiry', ['page_title' => 'Contact Us / Inquiry']);
    }

    /**
     * Become Associate page
     */
    public function becomeAssociate()
    {
        $isLoggedIn = !empty($_SESSION['user_id']);
        $loggedInReferralCode = $isLoggedIn ? ($_SESSION['referral_code'] ?? '') : '';
        $userName = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
        $base = BASE_URL;
        $role = $_SESSION['role'] ?? '';

        // Default company referral code
        $referral_code = $loggedInReferralCode ?: 'APS-COMPANY';
        $referral_link = $base . '/associate/register?ref=' . urlencode($referral_code);

        // If logged in, use portal layout; otherwise standalone page
        if ($isLoggedIn && in_array($role, ['associate', 'customer', 'user', 'agent'])) {
            $layoutMap = [
                'associate' => 'layouts/associate',
                'agent' => 'layouts/associate',
            ];
            $this->layout = $layoutMap[$role] ?? 'layouts/customer';
            $this->render('pages/become_associate_embed', [
                'page_title' => 'Promote & Earn',
                'page_description' => 'Share your referral code and earn rewards',
                'current_page' => 'list-property',
                'isLoggedIn' => true,
                'loggedInReferralCode' => $loggedInReferralCode,
                'referral_code' => $referral_code,
                'referral_link' => $referral_link,
                'userName' => $userName,
                'base' => $base,
            ], $this->layout);
        } else {
            // Standalone public page
            $this->render('pages/become_associate', [
                'page_title' => 'Become an Associate',
                'isLoggedIn' => false,
                'loggedInReferralCode' => '',
                'referral_code' => $referral_code,
                'referral_link' => $referral_link,
                'userName' => '',
                'base' => $base
            ]);
        }
    }

    /**
     * Location page by slug
     */
    public function location($slug = null)
    {
        $allowed = [
            'gorakhpur-bohisawagar',
            'gorakhpur-raghunath-nagri',
            'gorakhpur-suryoday-colony',
            'kushinagar-budha-city',
            'lucknow-ram-nagri',
            'varanasi-ganga-nagri'
        ];
        if (!in_array($slug, $allowed)) {
            header('HTTP/1.0 404 Not Found');
            $this->render('errors/404', ['page_title' => 'Page Not Found']);
            return;
        }
        $this->render('locations/' . $slug, ['page_title' => ucwords(str_replace(['-', '/'], [' ', ' '], $slug))]);
    }

    public function setLanguage(string $lang)
    {
        $allowed = ['en', 'hi'];
        if (in_array($lang, $allowed)) {
            $_SESSION['user_language'] = $lang;
            setcookie('user_language', $lang, time() + 86400 * 30, '/');
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Interactive Plot Layout Map — SVG grid with status colors
     */
    public function plotMap()
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($requestUri, '/admin/') !== false || strpos($requestUri, 'admin') !== false) {
            $this->requireAdmin();
        }

        $colonies = [];
        $allPlots = [];
        $colonyStats = [];
        $totalStats = ['available' => 0, 'booked' => 0, 'sold' => 0, 'blocked' => 0, 'total' => 0];

        try {
            $colStmt = $this->db->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY id");
            $colonies = $colStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('PageController::plotMap colonies: ' . $e->getMessage());
        }

        foreach ($colonies as &$colony) {
            $colony['plots'] = [];
            $colony['stats'] = ['available' => 0, 'booked' => 0, 'sold' => 0, 'blocked' => 0, 'total' => 0];
            try {
                $pStmt = $this->db->prepare(
                    "SELECT p.id, p.colony_id, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                            p.total_price, p.status, p.facing, p.corner_plot, p.park_facing,
                            c.name AS colony_name,
                            (SELECT psh.changed_at FROM plot_status_history psh WHERE psh.plot_id = p.id ORDER BY psh.changed_at DESC LIMIT 1) AS last_status_change
                     FROM plots p
                     JOIN colonies c ON c.id = p.colony_id
                     WHERE p.colony_id = ? AND p.is_active = 1
                     ORDER BY p.block, p.plot_number"
                );
                $pStmt->execute([$colony['id']]);
                $plots = $pStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                foreach ($plots as &$plot) {
                    $status = $plot['status'];
                    if ($status === 'available') {
                        $plot['display_status'] = 'Available';
                        $plot['color'] = '#10b981';
                        $colony['stats']['available']++;
                        $totalStats['available']++;
                    } elseif ($status === 'booked' || $status === 'reserved') {
                        $plot['display_status'] = ucfirst($status);
                        $plot['color'] = '#f59e0b';
                        $colony['stats']['booked']++;
                        $totalStats['booked']++;
                    } elseif ($status === 'sold') {
                        $plot['display_status'] = 'Sold';
                        $plot['color'] = '#ef4444';
                        $colony['stats']['sold']++;
                        $totalStats['sold']++;
                    } else {
                        $plot['display_status'] = ucfirst($status);
                        $plot['color'] = '#6b7280';
                        $colony['stats']['blocked']++;
                        $totalStats['blocked']++;
                    }
                    $plot['width_ft'] = $plot['width_ft'] ?? 30;
                    $plot['length_ft'] = $plot['length_ft'] ?? 40;
                    $colony['stats']['total']++;
                    $totalStats['total']++;
                }
                $colony['plots'] = $plots;
            } catch (\Exception $e) {
                error_log('PageController::plotMap plots colony ' . $colony['id'] . ': ' . $e->getMessage());
            }
            $colonyStats[$colony['id']] = $colony['stats'];
        }
        unset($plot, $colony);

        $this->render('pages/plot_layout', [
            'page_title' => 'Plot Layout Map',
            'colonies' => $colonies,
            'colony_stats' => $colonyStats,
            'total_stats' => $totalStats,
        ]);
    }

    // Associate & Agent Opportunity Page
    public function opportunity()
    {
        $this->render('pages/opportunity', [
            'page_title' => 'Earning Opportunity - APS Dream Home',
            'page_description' => 'Join the APS Dream Home Associate & Agent program to earn unlimited commissions.',
        ]);
    }

    /**
     * Public MLM plan info page — no auth required.
     * Shows commission structure, rank ladder, and how to join.
     */
    public function howItWorks()
    {
        $levels = [];
        $benefits = [];
        $stats = [
            'total_associates' => 0,
            'total_commission_paid' => 0,
            'active_colonies' => 0,
        ];

        try {
            $levels = $this->db->fetchAll("SELECT * FROM mlm_levels ORDER BY level_number ASC");
        } catch (\Exception $e) { /* graceful */ }

        try {
            $benefits = $this->db->fetchAll("SELECT * FROM mlm_rank_benefits ORDER BY rank_order ASC");
        } catch (\Exception $e) { /* graceful */ }

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM mlm_profiles");
            $stats['total_associates'] = (int)($row['cnt'] ?? 0);
        } catch (\Exception $e) { /* graceful */ }

        try {
            $row = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE status IN ('approved','paid')");
            $stats['total_commission_paid'] = (float)($row['total'] ?? 0);
        } catch (\Exception $e) { /* graceful */ }

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM colonies WHERE status = 'active'");
            $stats['active_colonies'] = (int)($row['cnt'] ?? 0);
        } catch (\Exception $e) { /* graceful */ }

        $this->render('associate/mlm_plan', [
            'page_title'  => 'MLM Plan & Commission Structure - APS Dream Home',
            'page_description' => 'Learn about APS Dream Home MLM plan, commission structure, and earning potential.',
            'levels'      => $levels,
            'benefits'    => $benefits,
            'current_plan' => null,
            'current_rank' => null,
            'next_rank'    => null,
            'user_profile' => null,
            'current_page' => 'how-it-works',
            'is_public'   => true,
            'stats'       => $stats,
        ]);
    }
}




