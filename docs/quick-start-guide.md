# 🚀 Quick Start Guide - APS Dream Home UI/UX

## 🎯 Start Here - Essential First Steps

### Step 1: Create Development Mode (5 minutes)
Create `development_mode.php` in your project root:
```php
<?php
// Enable development mode
define('DEVELOPMENT_MODE', true);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Base URL (adjust as needed)
define('BASE_URL', 'http://localhost/apsdreamhomefinal/');
define('APP_PATH', __DIR__ . '/app/');

// Database configuration (development)
define('DB_HOST', 'localhost');
define('DB_NAME', 'aps_dream_home');
define('DB_USER', 'root');
define('DB_PASS', '');
?>
```

### Step 2: Create Basic Router (10 minutes)
Create `app/core/SimpleRouter.php`:
```php
<?php
class SimpleRouter {
    private $routes = [];
    
    public function get($path, $handler) {
        $this->add('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->add('POST', $path, $handler);
    }
    
    private function add($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {
                $pattern = $this->convertToRegex($route['path']);
                
                if (preg_match($pattern, $requestUri, $matches)) {
                    array_shift($matches);
                    return $this->executeHandler($route['handler'], $matches);
                }
            }
        }
        
        $this->handle404();
    }
    
    private function convertToRegex($path) {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    private function executeHandler($handler, $params = []) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        } elseif (is_string($handler)) {
            return $this->executeStringHandler($handler, $params);
        }
    }
    
    private function executeStringHandler($handler, $params) {
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
    
    private function handle404() {
        http_response_code(404);
        require 'app/views/errors/404.php';
        exit;
    }
}
?>
```

### Step 3: Create Base Template (15 minutes)
Create `app/views/templates/base.php`:
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'APS Dream Home'); ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription ?? 'Find your dream home with APS Dream Home.'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords ?? 'real estate, property, homes'); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="page-<?php echo $pageClass ?? 'default'; ?>">
    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <i class="fas fa-home me-2"></i>APS Dream Home
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/properties">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light ms-2 px-3" href="/login">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php echo $content; ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <h5>APS Dream Home</h5>
                    <p>Your trusted partner in finding the perfect home.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="/properties" class="text-white-50">Properties</a></li>
                        <li><a href="/services" class="text-white-50">Services</a></li>
                        <li><a href="/about" class="text-white-50">About Us</a></li>
                        <li><a href="/contact" class="text-white-50">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i>123 Real Estate Ave, City</p>
                    <p><i class="fas fa-phone me-2"></i>+91 12345 67890</p>
                    <p><i class="fas fa-envelope me-2"></i>info@apsdreamhome.com</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; 2024 APS Dream Home. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
```

### Step 4: Create Main CSS (10 minutes)
Create `assets/css/main.css`:
```css
/* Custom CSS for APS Dream Home */

/* Root Variables */
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --accent-color: #f59e0b;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --info-color: #3b82f6;
    --light-color: #f8fafc;
    --dark-color: #1e293b;
    --border-radius: 0.5rem;
    --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

/* Global Styles */
* {
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: var(--dark-color);
}

/* Utility Classes */
.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary-color), #1d4ed8);
}

.text-primary {
    color: var(--primary-color) !important;
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    transition: var(--transition);
}

.btn-primary:hover {
    background-color: #1d4ed8;
    border-color: #1d4ed8;
    transform: translateY(-1px);
}

/* Card Styles */
.card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

/* Hero Section */
.hero-section {
    min-height: 70vh;
    display: flex;
    align-items: center;
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
    background: rgba(0, 0, 0, 0.3);
    z-index: 1;
}

.hero-section .container {
    position: relative;
    z-index: 2;
}

/* Property Cards */
.property-card {
    margin-bottom: 2rem;
}

.property-card .card-img-top {
    height: 200px;
    object-fit: cover;
    transition: var(--transition);
}

.property-card:hover .card-img-top {
    transform: scale(1.05);
}

.property-card .price {
    font-size: 1.25rem;
    font-weight: bold;
    color: var(--primary-color);
}

.property-card .location {
    color: var(--secondary-color);
    font-size: 0.9rem;
}

/* Navigation */
.navbar-brand {
    font-size: 1.5rem;
    font-weight: bold;
}

.nav-link {
    font-weight: 500;
    transition: var(--transition);
}

.nav-link:hover {
    color: var(--accent-color) !important;
}

/* Footer */
footer {
    background: linear-gradient(135deg, var(--dark-color), #0f172a);
}

footer h5 {
    color: var(--accent-color);
    margin-bottom: 1rem;
}

footer a {
    text-decoration: none;
    transition: var(--transition);
}

footer a:hover {
    color: var(--accent-color) !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section {
        min-height: 50vh;
        text-align: center;
    }
    
    .hero-section h1 {
        font-size: 2rem;
    }
    
    .property-card .card-img-top {
        height: 150px;
    }
    
    .navbar-brand {
        font-size: 1.2rem;
    }
}

/* Animation Classes */
.fade-in {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-in-left {
    transform: translateX(-100%);
    animation: slideInLeft 0.6s ease forwards;
}

@keyframes slideInLeft {
    to {
        transform: translateX(0);
    }
}

/* Loading Animation */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #1d4ed8;
}
```

### Step 5: Create Main JS (10 minutes)
Create `assets/js/main.js`:
```javascript
// Main JavaScript for APS Dream Home

document.addEventListener('DOMContentLoaded', function() {
    console.log('APS Dream Home loaded successfully!');
    
    // Initialize components
    initNavigation();
    initAnimations();
    initForms();
    initLazyLoading();
});

// Navigation functionality
function initNavigation() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Mobile menu toggle
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (navbarCollapse && navbarCollapse.classList.contains('show')) {
            if (!e.target.closest('.navbar')) {
                navbarCollapse.classList.remove('show');
            }
        }
    });
}

// Animation functionality
function initAnimations() {
    // Fade in elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements with animation classes
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
}

// Form functionality
function initForms() {
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Real-time validation
    const inputs = document.querySelectorAll('input[required], textarea[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
}

// Form validation functions
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        isValid = false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    }
    
    // Phone validation
    if (field.type === 'tel' && value) {
        const phoneRegex = /^[\d\s\-\(\)\+]+$/;
        if (!phoneRegex.test(value)) {
            showFieldError(field, 'Please enter a valid phone number');
            isValid = false;
        }
    }
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorElement = document.createElement('div');
    errorElement.className = 'invalid-feedback d-block';
    errorElement.textContent = message;
    
    field.classList.add('is-invalid');
    field.parentNode.appendChild(errorElement);
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const errorElement = field.parentNode.querySelector('.invalid-feedback');
    if (errorElement) {
        errorElement.remove();
    }
}

// Lazy loading functionality
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Utility functions
function showAlert(message, type = 'info') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const alertContainer = document.getElementById('alert-container') || createAlertContainer();
    alertContainer.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert:last-child');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alert-container';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Loading state functions
function showLoading(element) {
    element.classList.add('loading');
    element.disabled = true;
}

function hideLoading(element) {
    element.classList.remove('loading');
    element.disabled = false;
}

// Export functions for global use
window.APS = {
    showAlert,
    showLoading,
    hideLoading,
    validateForm,
    validateField
};
```

### Step 6: Create New Index.php (15 minutes)
Create new `index.php` (backup your old one first!):
```php
<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include development mode
require_once 'development_mode.php';

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
    ob_start();
    ?>
    
    <!-- Hero Section -->
    <section class="hero-section bg-gradient-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center min-vh-50">
                <div class="col-lg-6 animate-on-scroll">
                    <h1 class="display-4 fw-bold mb-4">Find Your Dream Home</h1>
                    <p class="lead mb-4">Discover premium properties in prime locations with APS Dream Home.</p>
                    <div class="d-flex gap-3">
                        <a href="/properties" class="btn btn-light btn-lg px-4">Browse Properties</a>
                        <a href="/contact" class="btn btn-outline-light btn-lg px-4">Contact Us</a>
                    </div>
                </div>
                <div class="col-lg-6 animate-on-scroll">
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
            <div class="text-center mb-5 animate-on-scroll">
                <h2 class="display-5 fw-bold">Featured Properties</h2>
                <p class="lead text-muted">Handpicked properties just for you</p>
            </div>
            
            <div class="row g-4">
                <?php for($i = 1; $i <= 6; $i++): ?>
                <div class="col-lg-4 col-md-6 animate-on-scroll">
                    <div class="property-card card h-100 shadow-sm hover-shadow-lg transition-all">
                        <div class="position-relative overflow-hidden">
                            <img src="https://via.placeholder.com/400x250/007bff/ffffff?text=Property+<?php echo $i; ?>" class="card-img-top" alt="Property <?php echo $i; ?>" style="height: 200px; object-fit: cover;">
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-primary">Featured</span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Beautiful Property <?php echo $i; ?></h5>
                            <p class="card-text text-muted flex-grow-1">Stunning property with modern amenities and excellent location.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h5 text-primary mb-0">₹45,00,000</span>
                                <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>City Center</small>
                            </div>
                            <a href="/property/<?php echo $i; ?>" class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            
            <div class="text-center mt-5 animate-on-scroll">
                <a href="/properties" class="btn btn-outline-primary btn-lg">View All Properties</a>
            </div>
        </div>
    </section>
    
    <!-- Services Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5 animate-on-scroll">
                <h2 class="display-5 fw-bold">Our Services</h2>
                <p class="lead text-muted">Comprehensive real estate solutions</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 animate-on-scroll">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-search fa-3x text-primary mb-3"></i>
                            <h4>Property Search</h4>
                            <p>Find the perfect property with our advanced search tools and expert guidance.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 animate-on-scroll">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                            <h4>Property Sales</h4>
                            <p>Sell your property quickly and at the best price with our marketing expertise.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 animate-on-scroll">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-calculator fa-3x text-primary mb-3"></i>
                            <h4>Financial Advice</h4>
                            <p>Get expert financial advice and assistance with mortgages and loans.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php
    $content = ob_get_clean();
    
    // Set page variables
    $pageTitle = 'APS Dream Home - Find Your Dream Property';
    $pageClass = 'home';
    $metaDescription = 'Find your dream home with APS Dream Home. Premium real estate services with expert guidance.';
    $metaKeywords = 'real estate, property, homes, apartments, plots';
    
    // Include base template
    require APP_PATH . 'views/templates/base.php';
});

// Property Routes
$router->get('/properties', function() {
    $pageTitle = 'Properties - APS Dream Home';
    ob_start();
    ?>
    
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">All Properties</h1>
                <p class="lead text-muted">Discover your perfect property from our extensive collection</p>
            </div>
            
            <!-- Search Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Property Type</label>
                            <select class="form-select" name="type">
                                <option value="">All Types</option>
                                <option value="apartment">Apartment</option>
                                <option value="villa">Villa</option>
                                <option value="plot">Plot</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" placeholder="Enter location">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Min Price</label>
                            <input type="number" class="form-control" name="min_price" placeholder="Min Price">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Price</label>
                            <input type="number" class="form-control" name="max_price" placeholder="Max Price">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Search Properties
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Properties Grid -->
            <div class="row g-4">
                <?php for($i = 1; $i <= 12; $i++): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="property-card card h-100 shadow-sm">
                        <img src="https://via.placeholder.com/400x250/28a745/ffffff?text=Property+<?php echo $i; ?>" class="card-img-top" alt="Property <?php echo $i; ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Property <?php echo $i; ?></h5>
                            <p class="card-text text-muted flex-grow-1">Beautiful property with excellent features and prime location.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h5 text-primary mb-0">₹<?php echo number_format(rand(2500000, 8000000)); ?></span>
                                <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Location <?php echo $i; ?></small>
                            </div>
                            <a href="/property/<?php echo $i; ?>" class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>
    
    <?php
    $content = ob_get_clean();
    require APP_PATH . 'views/templates/base.php';
});

$router->get('/property/{id}', function($id) {
    $pageTitle = 'Property Details - APS Dream Home';
    ob_start();
    ?>
    
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <img src="https://via.placeholder.com/800x500/6c757d/ffffff?text=Property+<?php echo $id; ?>" class="card-img-top" alt="Property <?php echo $id; ?>">
                        <div class="card-body">
                            <h1 class="card-title">Property <?php echo $id; ?></h1>
                            <p class="card-text">This is a beautiful property with excellent features and prime location. Perfect for families looking for a comfortable and modern living space.</p>
                            
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <strong>Price:</strong><br>
                                    <span class="h4 text-primary">₹<?php echo number_format(rand(2500000, 8000000)); ?></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Location:</strong><br>
                                    <span>Prime Location <?php echo $id; ?></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Property Type:</strong><br>
                                    <span><?php echo ['Apartment', 'Villa', 'Plot'][rand(0,2)]; ?></span>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Bedrooms:</strong><br>
                                    <span><?php echo rand(1, 5); ?></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Bathrooms:</strong><br>
                                    <span><?php echo rand(1, 4); ?></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Area:</strong><br>
                                    <span><?php echo rand(800, 3000); ?> sq ft</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Year Built:</strong><br>
                                    <span><?php echo rand(2015, 2023); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Contact Agent</h5>
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php
    $content = ob_get_clean();
    require APP_PATH . 'views/templates/base.php';
});

// Static Pages
$router->get('/about', function() {
    $pageTitle = 'About Us - APS Dream Home';
    ob_start();
    ?>
    
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">About APS Dream Home</h1>
                    <p class="lead mb-4">Your trusted partner in finding the perfect home. We are dedicated to helping you discover premium properties that match your lifestyle and budget.</p>
                    
                    <p>With years of experience in the real estate industry, we have established ourselves as a leading property service provider. Our team of expert professionals is committed to delivering exceptional service and ensuring your property journey is smooth and successful.</p>
                    
                    <h3 class="mt-5 mb-4">Our Mission</h3>
                    <p>To provide comprehensive real estate solutions that exceed our clients' expectations, making the process of buying, selling, or renting properties as seamless and rewarding as possible.</p>
                    
                    <h3 class="mt-5 mb-4">Our Vision</h3>
                    <p>To be the most trusted and innovative real estate partner, transforming the way people find and experience their dream homes.</p>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Why Choose Us?</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Expert Guidance</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Extensive Property List</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Transparent Process</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Best Prices</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Customer Support</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php
    $content = ob_get_clean();
    require APP_PATH . 'views/templates/base.php';
});

$router->get('/contact', function() {
    $pageTitle = 'Contact Us - APS Dream Home';
    ob_start();
    ?>
    
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">Contact Us</h1>
                <p class="lead text-muted">Get in touch with our real estate experts</p>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body p-4">
                            <h3 class="mb-4">Send us a Message</h3>
                            <form id="contactForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Your Name *</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Subject</label>
                                        <input type="text" class="form-control" name="subject">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Your Message *</label>
                                    <textarea class="form-control" name="message" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Contact Information</h5>
                            <div class="mb-3">
                                <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i>Address</strong><br>
                                <span>123 Real Estate Avenue<br>City Center, City 123456</span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-phone me-2 text-primary"></i>Phone</strong><br>
                                <span>+91 12345 67890</span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-envelope me-2 text-primary"></i>Email</strong><br>
                                <span>info@apsdreamhome.com</span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-clock me-2 text-primary"></i>Business Hours</strong><br>
                                <span>Mon - Sat: 9:00 AM - 6:00 PM</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php
    $content = ob_get_clean();
    require APP_PATH . 'views/templates/base.php';
});

// Error Pages
$router->get('/404', function() {
    http_response_code(404);
    $pageTitle = 'Page Not Found - APS Dream Home';
    ob_start();
    ?>
    
    <section class="py-5 text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
                    <h1 class="display-1 fw-bold text-primary">404</h1>
                    <h2 class="mb-4">Page Not Found</h2>
                    <p class="lead mb-4">Sorry, the page you're looking for doesn't exist. It might have been moved or deleted.</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="/" class="btn btn-primary">Go Home</a>
                        <a href="/properties" class="btn btn-outline-primary">Browse Properties</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php
    $content = ob_get_clean();
    require APP_PATH . 'views/templates/base.php';
});

// Dispatch the request
try {
    $router->dispatch();
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "An error occurred. Please try again later.";
}
?>
```

## 🚀 Test Your Setup

After creating all these files, test these URLs:

1. **Homepage**: http://localhost/apsdreamhomefinal/
2. **Properties**: http://localhost/apsdreamhomefinal/properties  
3. **Property Detail**: http://localhost/apsdreamhomefinal/property/1
4. **About**: http://localhost/apsdreamhomefinal/about
5. **Contact**: http://localhost/apsdreamhomefinal/contact
6. **404 Page**: http://localhost/apsdreamhomefinal/404

## 🎯 Next Steps

Once you have the basic setup working:

1. **Create more pages** using the same template structure
2. **Add database connectivity** for real property data
3. **Implement user authentication** 
4. **Add admin dashboard** functionality
5. **Enhance mobile responsiveness**

This quick start gives you a solid foundation with modern UI/UX that you can build upon!