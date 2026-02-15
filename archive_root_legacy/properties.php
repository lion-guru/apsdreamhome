<?php
/**
 * PERFECT PROPERTIES - APS Dream Home
 * Consolidated Properties Page with All Best Features
 * Combines: Advanced search, filtering, pagination, modern UI, and comprehensive property display
 */

// Enhanced session and security
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Enhanced error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enhanced database connection with error handling
class PerfectDatabase {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            $this->handleConnectionError($e);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function handleConnectionError($error) {
        error_log("Database connection failed: " . $error->getMessage());
        throw new Exception("Database connection unavailable. Please try again later.");
    }
}

// Enhanced property service
class PerfectPropertyService {
    private $db;
    private $itemsPerPage = 9;
    
    public function __construct() {
        $this->db = PerfectDatabase::getInstance()->getConnection();
    }
    
    public function getProperties($filters = [], $page = 1) {
        try {
            $whereConditions = [];
            $params = [];
            
            // Build dynamic WHERE conditions
            if (!empty($filters['type']) && $filters['type'] !== 'all') {
                $whereConditions[] = "p.type = :type";
                $params[':type'] = $filters['type'];
            }
            
            if (!empty($filters['location']) && $filters['location'] !== 'all') {
                $whereConditions[] = "p.location = :location";
                $params[':location'] = $filters['location'];
            }
            
            if (!empty($filters['min_price'])) {
                $whereConditions[] = "p.price >= :min_price";
                $params[':min_price'] = $filters['min_price'];
            }
            
            if (!empty($filters['max_price'])) {
                $whereConditions[] = "p.price <= :max_price";
                $params[':max_price'] = $filters['max_price'];
            }
            
            if (!empty($filters['bedrooms'])) {
                $whereConditions[] = "p.bedrooms >= :bedrooms";
                $params[':bedrooms'] = $filters['bedrooms'];
            }
            
            if (!empty($filters['search'])) {
                $whereConditions[] = "(p.title LIKE :search OR p.description LIKE :search OR p.location LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM properties p $whereClause";
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->execute($params);
            $totalCount = $countStmt->fetch()['total'];
            
            // Calculate pagination
            $totalPages = ceil($totalCount / $this->itemsPerPage);
            $offset = ($page - 1) * $this->itemsPerPage;
            
            // Get properties with enhanced query
            $query = "
                SELECT 
                    p.*,
                    pi.image_path,
                    pi.alt_text,
                    pt.type_name,
                    COUNT(DISTinct pi2.id) as image_count,
                    AVG(r.rating) as avg_rating,
                    COUNT(DISTINCT r.id) as review_count
                FROM properties p
                LEFT JOIN property_types pt ON p.type = pt.id
                LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
                LEFT JOIN property_images pi2 ON p.id = pi2.property_id
                LEFT JOIN reviews r ON p.id = r.property_id
                $whereClause
                GROUP BY p.id
                ORDER BY 
                    CASE 
                        WHEN :sort = 'price_asc' THEN p.price 
                        WHEN :sort = 'price_desc' THEN -p.price 
                        WHEN :sort = 'newest' THEN -p.id 
                        ELSE -p.id 
                    END
                LIMIT :limit OFFSET :offset
            ";
            
            $params[':sort'] = $filters['sort'] ?? 'newest';
            $params[':limit'] = $this->itemsPerPage;
            $params[':offset'] = $offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $properties = $stmt->fetchAll();
            
            return [
                'properties' => $properties,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'total_count' => $totalCount
            ];
            
        } catch (Exception $e) {
            error_log("Error fetching properties: " . $e->getMessage());
            return [
                'properties' => [],
                'total_pages' => 0,
                'current_page' => 1,
                'total_count' => 0
            ];
        }
    }
    
    public function getPropertyTypes() {
        try {
            $stmt = $this->db->query("SELECT * FROM property_types ORDER BY type_name");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error fetching property types: " . $e->getMessage());
            return [];
        }
    }
    
    public function getLocations() {
        try {
            $stmt = $this->db->query("SELECT DISTINCT location FROM properties WHERE location IS NOT NULL ORDER BY location");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error fetching locations: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPropertyStats() {
        try {
            $stats = [];
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM properties WHERE status = 'available'");
            $stats['available'] = $stmt->fetch()['total'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM properties WHERE status = 'sold'");
            $stats['sold'] = $stmt->fetch()['total'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM properties");
            $stats['total'] = $stmt->fetch()['total'];
            
            $stmt = $this->db->query("SELECT AVG(price) as avg_price FROM properties WHERE status = 'available'");
            $stats['avg_price'] = $stmt->fetch()['avg_price'];
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error fetching property stats: " . $e->getMessage());
            return ['available' => 0, 'sold' => 0, 'total' => 0, 'avg_price' => 0];
        }
    }
}

// Enhanced page data handler
class PerfectPageData {
    private $propertyService;
    
    public function __construct() {
        $this->propertyService = new PerfectPropertyService();
    }
    
    public function getPageData() {
        // Get filters from request
        $filters = [
            'type' => $_GET['type'] ?? 'all',
            'location' => $_GET['location'] ?? 'all',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
            'bedrooms' => $_GET['bedrooms'] ?? '',
            'search' => $_GET['search'] ?? '',
            'sort' => $_GET['sort'] ?? 'newest'
        ];
        
        // Clean and validate filters
        $filters = $this->cleanFilters($filters);
        
        // Get current page
        $currentPage = max(1, intval($_GET['page'] ?? 1));
        
        // Get data
        $propertiesData = $this->propertyService->getProperties($filters, $currentPage);
        $propertyTypes = $this->propertyService->getPropertyTypes();
        $locations = $this->propertyService->getLocations();
        $stats = $this->propertyService->getPropertyStats();
        
        return [
            'filters' => $filters,
            'properties' => $propertiesData['properties'],
            'pagination' => [
                'current_page' => $propertiesData['current_page'],
                'total_pages' => $propertiesData['total_pages'],
                'total_count' => $propertiesData['total_count']
            ],
            'property_types' => $propertyTypes,
            'locations' => $locations,
            'stats' => $stats
        ];
    }
    
    private function cleanFilters($filters) {
        foreach ($filters as $key => $value) {
            if (is_string($value)) {
                $filters[$key] = htmlspecialchars(trim($value));
            }
        }
        
        // Validate numeric fields
        if (!empty($filters['min_price']) && !is_numeric($filters['min_price'])) {
            $filters['min_price'] = '';
        }
        if (!empty($filters['max_price']) && !is_numeric($filters['max_price'])) {
            $filters['max_price'] = '';
        }
        if (!empty($filters['bedrooms']) && !is_numeric($filters['bedrooms'])) {
            $filters['bedrooms'] = '';
        }
        
        return $filters;
    }
}

// Initialize page data
$pageData = new PerfectPageData();
$data = $pageData->getPageData();

// Enhanced page rendering
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfect Properties - APS Dream Home</title>
    
    <!-- Enhanced CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --text-dark: #2d3748;
            --text-light: #718096;
            --bg-light: #f7fafc;
            --border-color: #e2e8f0;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #f56565;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            background-color: var(--bg-light);
        }
        
        /* Enhanced Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.05"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.05"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        /* Enhanced Search Form */
        .search-form {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }
        
        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        /* Enhanced Property Cards */
        .property-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .property-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }
        
        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .property-card:hover .property-image img {
            transform: scale(1.1);
        }
        
        .property-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--success-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .property-badge.sold {
            background: var(--danger-color);
        }
        
        .property-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .property-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-dark);
        }
        
        .property-location {
            color: var(--text-light);
            margin-bottom: 15px;
        }
        
        .property-features {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        /* Enhanced Stats Section */
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        /* Enhanced Pagination */
        .pagination {
            justify-content: center;
            margin-top: 50px;
        }
        
        .page-link {
            border: 2px solid var(--border-color);
            color: var(--text-dark);
            margin: 0 5px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .page-link:hover, .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Enhanced Advanced Search */
        .advanced-search {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        
        .view-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .view-btn {
            padding: 8px 15px;
            border: 2px solid var(--border-color);
            background: white;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .view-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            padding: 50px;
        }
        
        .spinner {
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            .search-form {
                padding: 20px;
                margin-top: -30px;
            }
            
            .property-features {
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .stats-number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8" data-aos="fade-right">
                    <h1 class="display-4 fw-bold mb-4">Find Your Perfect Property</h1>
                    <p class="lead mb-0">Discover the finest properties in prime locations with our comprehensive search system</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Search Form -->
    <section class="py-5">
        <div class="container">
            <div class="search-form" data-aos="fade-up" data-aos-delay="200">
                <form method="GET" action="" id="searchForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Property Type</label>
                            <select class="form-select" name="type">
                                <option value="all">All Types</option>
                                <?php foreach ($data['property_types'] as $type): ?>
                                    <option value="<?php echo $type['id']; ?>" 
                                        <?php echo $data['filters']['type'] == $type['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type['type_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Location</label>
                            <select class="form-select" name="location">
                                <option value="all">All Locations</option>
                                <?php foreach ($data['locations'] as $location): ?>
                                    <option value="<?php echo htmlspecialchars($location['location']); ?>" 
                                        <?php echo $data['filters']['location'] == $location['location'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($location['location']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Min Price</label>
                            <input type="number" class="form-control" name="min_price" 
                                   value="<?php echo htmlspecialchars($data['filters']['min_price']); ?>" 
                                   placeholder="Min Price">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Max Price</label>
                            <input type="number" class="form-control" name="max_price" 
                                   value="<?php echo htmlspecialchars($data['filters']['max_price']); ?>" 
                                   placeholder="Max Price">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Bedrooms</label>
                            <select class="form-select" name="bedrooms">
                                <option value="">Any</option>
                                <option value="1" <?php echo $data['filters']['bedrooms'] == '1' ? 'selected' : ''; ?>>1+</option>
                                <option value="2" <?php echo $data['filters']['bedrooms'] == '2' ? 'selected' : ''; ?>>2+</option>
                                <option value="3" <?php echo $data['filters']['bedrooms'] == '3' ? 'selected' : ''; ?>>3+</option>
                                <option value="4" <?php echo $data['filters']['bedrooms'] == '4' ? 'selected' : ''; ?>>4+</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Search</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($data['filters']['search']); ?>" 
                                       placeholder="Search properties...">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Sort By</label>
                            <select class="form-select" name="sort">
                                <option value="newest" <?php echo $data['filters']['sort'] == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_asc" <?php echo $data['filters']['sort'] == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_desc" <?php echo $data['filters']['sort'] == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Search Properties
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Enhanced Statistics -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo number_format($data['stats']['total']); ?></div>
                        <h5>Total Properties</h5>
                        <p class="text-muted mb-0">Available in our database</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="stats-card">
                        <div class="stats-number text-success"><?php echo number_format($data['stats']['available']); ?></div>
                        <h5>Available Now</h5>
                        <p class="text-muted mb-0">Ready for immediate viewing</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="stats-card">
                        <div class="stats-number text-info">₹<?php echo number_format($data['stats']['avg_price']); ?></div>
                        <h5>Average Price</h5>
                        <p class="text-muted mb-0">For available properties</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="stats-card">
                        <div class="stats-number text-warning"><?php echo number_format($data['stats']['sold']); ?></div>
                        <h5>Sold Properties</h5>
                        <p class="text-muted mb-0">Successfully closed deals</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Properties Grid -->
    <section class="py-5">
        <div class="container">
            <!-- View Toggle and Results Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h3 class="mb-0">
                        Found <?php echo $data['pagination']['total_count']; ?> Properties
                        <?php if ($data['pagination']['total_count'] > 0): ?>
                            <small class="text-muted">(Page <?php echo $data['pagination']['current_page']; ?> of <?php echo $data['pagination']['total_pages']; ?>)</small>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="col-md-6 text-end">
                    <div class="view-toggle">
                        <button class="view-btn active" id="gridView">
                            <i class="fas fa-th"></i> Grid
                        </button>
                        <button class="view-btn" id="listView">
                            <i class="fas fa-list"></i> List
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading Animation -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Loading properties...</p>
            </div>

            <!-- Properties Grid -->
            <div class="row g-4" id="propertiesGrid">
                <?php if (empty($data['properties'])): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No properties found</h4>
                            <p class="text-muted">Try adjusting your search criteria</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($data['properties'] as $property): ?>
                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo ($loop->iteration ?? 1) * 100; ?>">
                            <div class="property-card">
                                <div class="property-image">
                                    <img src="<?php echo htmlspecialchars($property['image_path'] ?? 'assets/images/property-placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($property['alt_text'] ?? $property['title']); ?>">
                                    <div class="property-badge <?php echo $property['status'] === 'sold' ? 'sold' : ''; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="property-price">₹<?php echo number_format($property['price']); ?></div>
                                    <h5 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    <p class="property-location">
                                        <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($property['location']); ?>
                                    </p>
                                    <div class="property-features">
                                        <?php if ($property['bedrooms']): ?>
                                            <div class="feature-item">
                                                <i class="fas fa-bed"></i>
                                                <span><?php echo $property['bedrooms']; ?> Beds</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($property['bathrooms']): ?>
                                            <div class="feature-item">
                                                <i class="fas fa-bath"></i>
                                                <span><?php echo $property['bathrooms']; ?> Baths</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($property['area_sqft']): ?>
                                            <div class="feature-item">
                                                <i class="fas fa-ruler-combined"></i>
                                                <span><?php echo number_format($property['area_sqft']); ?> sqft</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="rating">
                                            <?php if ($property['avg_rating']): ?>
                                                <i class="fas fa-star text-warning"></i>
                                                <span><?php echo number_format($property['avg_rating'], 1); ?></span>
                                                <small class="text-muted">(<?php echo $property['review_count']; ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                        <a href="property_detail.php?id=<?php echo $property['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Enhanced Pagination -->
            <?php if ($data['pagination']['total_pages'] > 1): ?>
                <nav aria-label="Property pagination">
                    <ul class="pagination">
                        <!-- Previous Page -->
                        <?php if ($data['pagination']['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $data['pagination']['current_page'] - 1])); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $startPage = max(1, $data['pagination']['current_page'] - 2);
                        $endPage = min($data['pagination']['total_pages'], $data['pagination']['current_page'] + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?php echo $i === $data['pagination']['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Page -->
                        <?php if ($data['pagination']['current_page'] < $data['pagination']['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $data['pagination']['current_page'] + 1])); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </section>

    <!-- Enhanced Advanced Search Modal -->
    <div class="modal fade" id="advancedSearchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Advanced Search Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Advanced search form content -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
        
        // Enhanced search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('searchForm');
            const clearSearch = document.getElementById('clearSearch');
            const gridView = document.getElementById('gridView');
            const listView = document.getElementById('listView');
            const propertiesGrid = document.getElementById('propertiesGrid');
            const loading = document.getElementById('loading');
            
            // Clear search functionality
            clearSearch.addEventListener('click', function() {
                searchForm.reset();
            });
            
            // View toggle functionality
            gridView.addEventListener('click', function() {
                gridView.classList.add('active');
                listView.classList.remove('active');
                propertiesGrid.classList.remove('list-view');
            });
            
            listView.addEventListener('click', function() {
                listView.classList.add('active');
                gridView.classList.remove('active');
                propertiesGrid.classList.add('list-view');
            });
            
            // AJAX search functionality (enhanced)
            let searchTimeout;
            searchForm.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    performAjaxSearch();
                }, 500);
            });
            
            function performAjaxSearch() {
                loading.style.display = 'block';
                propertiesGrid.style.display = 'none';
                
                const formData = new FormData(searchForm);
                const params = new URLSearchParams(formData);
                
                fetch('properties_ajax.php?' + params.toString())
                    .then(response => response.json())
                    .then(data => {
                        updatePropertiesDisplay(data);
                        loading.style.display = 'none';
                        propertiesGrid.style.display = 'flex';
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        loading.style.display = 'none';
                        propertiesGrid.style.display = 'flex';
                    });
            }
            
            function updatePropertiesDisplay(data) {
                // Update properties display with new data
                // This is a placeholder for the actual implementation
                console.log('Properties updated:', data);
            }
        });
        
        // Enhanced property card interactions
        document.querySelectorAll('.property-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>