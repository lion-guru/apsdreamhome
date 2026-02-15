# Routing Implementation Guide - APS Dream Home

## 🎯 Current Routing Issues

### Problems Identified:
1. **Multiple routing systems** causing conflicts
2. **Broken admin dashboard** access
3. **Inconsistent URL patterns**
4. **Missing SEO-friendly URLs**
5. **No proper route organization**

## 🚀 Simplified Routing Solution

### Phase 1: Basic Routing Setup (Immediate)

#### 1.1 Create Simple Router Class
Create `app/core/SimpleRouter.php`:
```php
<?php
class SimpleRouter {
    private $routes = [];
    private $basePath = '';
    
    public function __construct($basePath = '') {
        $this->basePath = $basePath;
    }
    
    // Add route
    public function add($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $this->basePath . $path,
            'handler' => $handler
        ];
    }
    
    // GET route
    public function get($path, $handler) {
        $this->add('GET', $path, $handler);
    }
    
    // POST route
    public function post($path, $handler) {
        $this->add('POST', $path, $handler);
    }
    
    // Dispatch request
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {
                $pattern = $this->convertToRegex($route['path']);
                
                if (preg_match($pattern, $requestUri, $matches)) {
                    array_shift($matches); // Remove full match
                    return $this->executeHandler($route['handler'], $matches);
                }
            }
        }
        
        // No route found
        $this->handle404();
    }
    
    // Convert path to regex
    private function convertToRegex($path) {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    // Execute handler
    private function executeHandler($handler, $params = []) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        } elseif (is_string($handler)) {
            return $this->executeStringHandler($handler, $params);
        }
        
        throw new Exception("Invalid route handler");
    }
    
    // Execute string handler
    private function executeStringHandler($handler, $params = []) {
        $parts = explode('@', $handler);
        $controller = $parts[0];
        $method = $parts[1] ?? 'index';
        
        $controllerFile = "app/controllers/{$controller}.php";
        
        if (!file_exists($controllerFile)) {
            throw new Exception("Controller file not found: {$controllerFile}");
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controller)) {
            throw new Exception("Controller class not found: {$controller}");
        }
        
        $controllerInstance = new $controller();
        
        if (!method_exists($controllerInstance, $method)) {
            throw new Exception("Controller method not found: {$method}");
        }
        
        return call_user_func_array([$controllerInstance, $method], $params);
    }
    
    // Handle 404
    private function handle404() {
        http_response_code(404);
        require 'app/views/404.php';
        exit;
    }
}
```

#### 1.2 Create Main Entry Point
Create `index.php` (replace current one):
```php
<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Define constants
define('BASE_URL', 'http://localhost/apsdreamhomefinal/');
define('APP_PATH', __DIR__ . '/app/');

// Autoload classes
spl_autoload_register(function ($class) {
    $file = APP_PATH . 'core/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Create router
$router = new SimpleRouter();

// Public Routes
$router->get('/', function() {
    $pageTitle = 'APS Dream Home - Find Your Dream Property';
    require 'app/views/pages/homepage_modern.php';
});

$router->get('/properties', function() {
    $pageTitle = 'Properties - APS Dream Home';
    require 'app/views/properties/index.php';
});

$router->get('/property/{id}', function($id) {
    $pageTitle = 'Property Details - APS Dream Home';
    $_GET['id'] = $id;
    require 'app/views/properties/detail.php';
});

$router->get('/about', function() {
    $pageTitle = 'About Us - APS Dream Home';
    require 'app/views/pages/about.php';
});

$router->get('/contact', function() {
    $pageTitle = 'Contact Us - APS Dream Home';
    require 'app/views/pages/contact.php';
});

$router->post('/contact', function() {
    require 'app/views/pages/contact_handler.php';
});

$router->get('/services', function() {
    $pageTitle = 'Our Services - APS Dream Home';
    require 'app/views/pages/services.php';
});

// Auth Routes
$router->get('/login', function() {
    $pageTitle = 'Login - APS Dream Home';
    require 'app/views/auth/login.php';
});

$router->post('/login', function() {
    require 'app/views/auth/login_handler.php';
});

$router->get('/register', function() {
    $pageTitle = 'Register - APS Dream Home';
    require 'app/views/auth/register.php';
});

$router->post('/register', function() {
    require 'app/views/auth/register_handler.php';
});

$router->get('/logout', function() {
    session_destroy();
    header('Location: /');
    exit;
});

// Customer Routes (Protected)
$router->get('/dashboard', function() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    $pageTitle = 'Dashboard - APS Dream Home';
    require 'app/views/customer/dashboard.php';
});

$router->get('/profile', function() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    $pageTitle = 'Profile - APS Dream Home';
    require 'app/views/customer/profile.php';
});

// Admin Routes (Protected)
$router->get('/admin', function() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /admin/login');
        exit;
    }
    $pageTitle = 'Admin Dashboard - APS Dream Home';
    require 'app/views/admin/dashboard.php';
});

$router->get('/admin/login', function() {
    $pageTitle = 'Admin Login - APS Dream Home';
    require 'app/views/admin/login.php';
});

$router->post('/admin/login', function() {
    require 'app/views/admin/login_handler.php';
});

$router->get('/admin/logout', function() {
    unset($_SESSION['admin_id']);
    header('Location: /admin/login');
    exit;
});

// API Routes
$router->get('/api/properties', function() {
    header('Content-Type: application/json');
    require 'app/api/properties.php';
});

$router->get('/api/property/{id}', function($id) {
    header('Content-Type: application/json');
    $_GET['id'] = $id;
    require 'app/api/property_detail.php';
});

// Dispatch the request
try {
    $router->dispatch();
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "An error occurred. Please try again later.";
}
```

#### 1.3 Create .htaccess for Clean URLs
Create `.htaccess` (replace current one):
```apache
# Enable rewrite engine
RewriteEngine On

# Set base path (adjust if needed)
RewriteBase /apsdreamhomefinal/

# Redirect to HTTPS (optional, for production)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]

# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "\.(env|json|lock|md|yml|yaml)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect .htaccess file
<Files .htaccess>
    Order allow,deny
    Deny from all
</Files>

# Protect directories
RewriteRule ^(app|config|core|logs|temp|vendor)/ - [F,L,NC]

# Main rewrite rule - send all requests to index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Handle trailing slashes
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)/$ /$1 [R=301,L]

# Set environment variable for development
SetEnv DEVELOPMENT_MODE true

# Security headers (for production)
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

### Phase 2: Page Organization Structure

#### 2.1 Recommended Directory Structure
```
app/
├── views/
│   ├── templates/
│   │   ├── base.php              # Main layout template
│   │   ├── auth.php              # Authentication layout
│   │   └── admin.php             # Admin layout
│   ├── components/
│   │   ├── header.php            # Unified header
│   │   ├── footer.php            # Unified footer
│   │   ├── navigation.php        # Main navigation
│   │   ├── breadcrumbs.php       # Breadcrumb navigation
│   │   └── alerts.php            # Alert messages
│   ├── pages/
│   │   ├── home.php              # Homepage
│   │   ├── about.php             # About page
│   │   ├── contact.php           # Contact page
│   │   └── services.php          # Services page
│   ├── auth/
│   │   ├── login.php               # Login form
│   │   ├── register.php            # Registration form
│   │   ├── forgot_password.php     # Password reset
│   │   └── login_handler.php       # Login processing
│   ├── properties/
│   │   ├── index.php               # Property listing
│   │   ├── detail.php              # Property details
│   │   ├── search.php              # Advanced search
│   │   └── results.php             # Search results
│   ├── customer/
│   │   ├── dashboard.php           # Customer dashboard
│   │   ├── profile.php             # Customer profile
│   │   ├── saved_properties.php    # Saved properties
│   │   └── inquiries.php           # Property inquiries
│   ├── admin/
│   │   ├── dashboard.php           # Admin dashboard
│   │   ├── login.php               # Admin login
│   │   ├── properties/
│   │   │   ├── index.php           # Property management
│   │   │   ├── create.php          # Add property
│   │   │   └── edit.php            # Edit property
│   │   ├── users/
│   │   │   ├── index.php           # User management
│   │   │   └── edit.php            # Edit user
│   │   └── settings.php            # Admin settings
│   └── errors/
│       ├── 404.php                 # 404 error page
│       ├── 403.php                 # 403 error page
│       └── 500.php                 # 500 error page
```

#### 2.2 Base Template System
Create `app/views/templates/base.php`:
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'APS Dream Home'); ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription ?? 'Find your dream home with APS Dream Home. Premium real estate services with expert guidance.'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords ?? 'real estate, property, homes, apartments, plots'); ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle ?? 'APS Dream Home'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription ?? 'Find your dream home with APS Dream Home.'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl ?? BASE_URL); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImage ?? BASE_URL . 'assets/images/og-image.jpg'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
    
    <?php echo $additionalCss ?? ''; ?>
</head>
<body class="page-<?php echo $pageClass ?? 'default'; ?>">
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Header -->
    <?php include APP_PATH . 'views/components/header.php'; ?>
    
    <!-- Alert Messages -->
    <?php include APP_PATH . 'views/components/alerts.php'; ?>
    
    <!-- Main Content -->
    <main id="main-content" class="main-content">
        <?php echo $content; ?>
    </main>
    
    <!-- Footer -->
    <?php include APP_PATH . 'views/components/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/main.js"></script>
    
    <?php echo $additionalJs ?? ''; ?>
</body>
</html>
```

#### 2.3 Page Template Usage Example
Create `app/views/pages/home.php`:
```php
<?php
$content = '
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Find Your Dream Home</h1>
                <p class="lead mb-4">Discover premium properties in prime locations with APS Dream Home.</p>
                <div class="d-flex gap-3">
                    <a href="/properties" class="btn btn-light btn-lg px-4">Browse Properties</a>
                    <a href="/contact" class="btn btn-outline-light btn-lg px-4">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        <h5 class="card-title text-dark mb-4">Quick Property Search</h5>
                        <form action="/properties" method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-dark">Property Type</label>
                                <select class="form-select" name="type">
                                    <option value="">Any Type</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="villa">Villa</option>
                                    <option value="plot">Plot</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-dark">Location</label>
                                <input type="text" class="form-control" name="location" placeholder="Enter location">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Search Properties
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Featured Properties</h2>
            <p class="lead text-muted">Handpicked properties just for you</p>
        </div>
        
        <div class="row g-4">
            ' . getFeaturedProperties() . '
        </div>
        
        <div class="text-center mt-5">
            <a href="/properties" class="btn btn-outline-primary btn-lg">View All Properties</a>
        </div>
    </div>
</section>
';

// Set page variables
$pageTitle = 'APS Dream Home - Find Your Dream Property';
$pageClass = 'home';
$metaDescription = 'Find your dream home with APS Dream Home. Premium real estate services with expert guidance and extensive property listings.';
$metaKeywords = 'real estate, property, homes, apartments, plots, APS Dream Home';
$currentUrl = BASE_URL;

// Include base template
require APP_PATH . 'views/templates/base.php';

// Helper function to get featured properties
function getFeaturedProperties() {
    // This would normally come from database
    $properties = [];
    for ($i = 1; $i <= 6; $i++) {
        $properties[] = '
            <div class="col-lg-4 col-md-6">
                <div class="property-card card h-100 shadow-sm hover-shadow-lg transition-all">
                    <div class="position-relative overflow-hidden">
                        <img src="/assets/images/property' . $i . '.jpg" class="card-img-top" alt="Property" style="height: 200px; object-fit: cover;">
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-primary">Featured</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Beautiful Property ' . $i . '</h5>
                        <p class="card-text text-muted flex-grow-1">Stunning property with modern amenities and excellent location.</p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h5 text-primary mb-0">₹45,00,000</span>
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>City Center</small>
                        </div>
                        <a href="/property/' . $i . '" class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
        ';
    }
    return implode('', $properties);
}
?>
```

### Phase 3: Advanced Routing Features

#### 3.1 Route Parameters and Validation
```php
// Property routes with validation
$router->get('/property/{id}', function($id) {
    // Validate ID
    if (!is_numeric($id) || $id < 1) {
        header('Location: /404');
        exit;
    }
    
    $pageTitle = 'Property Details - APS Dream Home';
    $_GET['id'] = $id;
    require 'app/views/properties/detail.php';
});

// Search routes with parameters
$router->get('/properties/search/{type}/{location}/{min_price}/{max_price}', function($type, $location, $min_price, $max_price) {
    $_GET['type'] = $type;
    $_GET['location'] = $location;
    $_GET['min_price'] = $min_price;
    $_GET['max_price'] = $max_price;
    
    $pageTitle = 'Search Results - APS Dream Home';
    require 'app/views/properties/search_results.php';
});
```

#### 3.2 Route Middleware
```php
// Admin middleware
function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /admin/login');
        exit;
    }
}

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Protected routes
$router->get('/admin/dashboard', function() {
    requireAdmin();
    $pageTitle = 'Admin Dashboard - APS Dream Home';
    require 'app/views/admin/dashboard.php';
});

$router->get('/profile', function() {
    requireAuth();
    $pageTitle = 'My Profile - APS Dream Home';
    require 'app/views/customer/profile.php';
});
```

#### 3.3 SEO-Friendly URLs
```php
// Property categories
$router->get('/properties/apartments', function() {
    $_GET['category'] = 'apartment';
    $pageTitle = 'Apartments for Sale - APS Dream Home';
    require 'app/views/properties/category.php';
});

$router->get('/properties/villas', function() {
    $_GET['category'] = 'villa';
    $pageTitle = 'Villas for Sale - APS Dream Home';
    require 'app/views/properties/category.php';
});

$router->get('/properties/plots', function() {
    $_GET['category'] = 'plot';
    $pageTitle = 'Plots for Sale - APS Dream Home';
    require 'app/views/properties/category.php';
});

// Location-based URLs
$router->get('/properties/{city}', function($city) {
    $_GET['city'] = $city;
    $pageTitle = 'Properties in ' . ucfirst($city) . ' - APS Dream Home';
    require 'app/views/properties/city.php';
});
```

## 🎯 Implementation Steps

### Step 1: Backup Current Files
```bash
# Backup current index.php and .htaccess
cp index.php index_backup.php
cp .htaccess .htaccess_backup
```

### Step 2: Create Router Structure
```bash
# Create necessary directories
mkdir -p app/core
mkdir -p app/controllers
mkdir -p app/views/templates
mkdir -p app/views/components
mkdir -p app/views/pages
mkdir -p app/views/properties
mkdir -p app/views/auth
mkdir -p app/views/customer
mkdir -p app/views/admin
mkdir -p app/views/errors
```

### Step 3: Implement Basic Router
1. Create `app/core/SimpleRouter.php` (code provided above)
2. Create new `index.php` with routing logic
3. Create new `.htaccess` for clean URLs

### Step 4: Test Basic Routing
```bash
# Test these URLs:
http://localhost/apsdreamhomefinal/
http://localhost/apsdreamhomefinal/properties
http://localhost/apsdreamhomefinal/property/1
http://localhost/apsdreamhomefinal/about
http://localhost/apsdreamhomefinal/contact
```

### Step 5: Migrate Existing Pages
1. Move existing pages to appropriate directories
2. Update includes and file paths
3. Test all functionality

## 📊 Benefits of This Implementation

✅ **Clean URLs**: SEO-friendly URLs like `/property/123`
✅ **Organized Structure**: Logical file organization
✅ **Easy Maintenance**: Centralized routing logic
✅ **Scalable**: Easy to add new routes
✅ **Secure**: Built-in protection for sensitive files
✅ **Performance**: Efficient routing without complex frameworks

## 🔧 Troubleshooting Common Issues

### Issue 1: 404 Errors
```bash
# Check .htaccess is working
# Enable mod_rewrite in Apache
# Check file permissions
```

### Issue 2: Routes Not Found
```php
// Add debug output
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
```

### Issue 3: Session Issues
```php
// Ensure session_start() is called
// Check session save path
// Verify session cookies
```

## 🚀 Next Steps

1. **Implement the basic router** following the steps above
2. **Test all routes** to ensure they work correctly
3. **Migrate existing pages** to the new structure
4. **Add authentication middleware** for protected routes
5. **Implement SEO-friendly URLs** for better search ranking
6. **Add API routes** for AJAX functionality

This routing implementation will solve your current routing issues and provide a solid foundation for organizing your pages and URLs effectively!