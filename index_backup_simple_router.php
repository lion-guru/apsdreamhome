<?php
/**
 * WORKING INDEX FOR APS DREAM HOME - IMMEDIATE FIX
 * This version provides a working homepage immediately
 */

// Basic configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_url = $protocol . $host . '/';

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$request_path = parse_url($request_uri, PHP_URL_PATH);
$request_path = trim($request_path, '/');

// Remove project folder from path if present
$project_folders = ['apsdreamhome', 'apsdreamhomefinal'];
foreach ($project_folders as $folder) {
    if (strpos($request_path, $folder) === 0) {
        $request_path = substr($request_path, strlen($folder));
        $request_path = trim($request_path, '/');
        break;
    }
}

// Simple routing - handle basic pages
if (empty($request_path) || $request_path === 'home' || $request_path === 'index') {
    // Show the working homepage
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>APS Dream Home - Premium Real Estate in Gorakhpur</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Arial', sans-serif;
                line-height: 1.6;
                color: #333;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }
            
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            
            header {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 15px;
                padding: 20px;
                margin-bottom: 30px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }
            
            .logo {
                font-size: 2.5rem;
                font-weight: bold;
                color: #2d3748;
                text-align: center;
                margin-bottom: 10px;
            }
            
            .tagline {
                text-align: center;
                color: #4a5568;
                font-size: 1.2rem;
                margin-bottom: 20px;
            }
            
            nav {
                text-align: center;
                margin-top: 20px;
            }
            
            nav a {
                display: inline-block;
                margin: 0 15px;
                padding: 10px 20px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                text-decoration: none;
                border-radius: 25px;
                transition: transform 0.3s ease;
            }
            
            nav a:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }
            
            .hero {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 15px;
                padding: 60px 40px;
                text-align: center;
                margin-bottom: 30px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }
            
            .hero h1 {
                font-size: 3rem;
                color: #2d3748;
                margin-bottom: 20px;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .hero p {
                font-size: 1.3rem;
                color: #4a5568;
                margin-bottom: 30px;
                max-width: 600px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .cta-button {
                display: inline-block;
                padding: 15px 40px;
                background: linear-gradient(135deg, #48bb78, #38a169);
                color: white;
                text-decoration: none;
                border-radius: 50px;
                font-size: 1.2rem;
                font-weight: bold;
                transition: all 0.3s ease;
                box-shadow: 0 5px 15px rgba(72, 187, 120, 0.4);
            }
            
            .cta-button:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(72, 187, 120, 0.6);
            }
            
            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 30px;
                margin-bottom: 30px;
            }
            
            .feature {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 15px;
                padding: 30px;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease;
            }
            
            .feature:hover {
                transform: translateY(-5px);
            }
            
            .feature h3 {
                color: #2d3748;
                font-size: 1.5rem;
                margin-bottom: 15px;
            }
            
            .feature p {
                color: #4a5568;
                line-height: 1.6;
            }
            
            footer {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 15px;
                padding: 30px;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }
            
            .contact-info {
                margin-bottom: 20px;
            }
            
            .contact-info h3 {
                color: #2d3748;
                margin-bottom: 10px;
            }
            
            .contact-info p {
                color: #4a5568;
                margin: 5px 0;
            }
            
            .admin-links {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #e2e8f0;
            }
            
            .admin-links a {
                display: inline-block;
                margin: 0 10px;
                padding: 8px 16px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 20px;
                font-size: 0.9rem;
            }
            
            .admin-links a:hover {
                background: #5a67d8;
            }
            
            @media (max-width: 768px) {
                .container {
                    padding: 10px;
                }
                
                .hero {
                    padding: 40px 20px;
                }
                
                .hero h1 {
                    font-size: 2rem;
                }
                
                .hero p {
                    font-size: 1.1rem;
                }
                
                nav a {
                    display: block;
                    margin: 10px auto;
                    max-width: 200px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <div class="logo">🏠 APS Dream Home</div>
                <div class="tagline">Premium Real Estate Solutions in Gorakhpur</div>
                <nav>
                    <a href="<?php echo $base_url; ?>">Home</a>
                    <a href="<?php echo $base_url; ?>properties.php">Properties</a>
                    <a href="<?php echo $base_url; ?>about.php">About</a>
                    <a href="<?php echo $base_url; ?>contact.php">Contact</a>
                    <a href="<?php echo $base_url; ?>projects.php">Projects</a>
                </nav>
            </header>
            
            <section class="hero">
                <h1>Welcome to APS Dream Home</h1>
                <p>Your trusted partner for premium real estate solutions in Gorakhpur. Discover exceptional properties, innovative projects, and unparalleled service.</p>
                <a href="<?php echo $base_url; ?>properties.php" class="cta-button">Explore Properties</a>
            </section>
            
            <div class="features">
                <div class="feature">
                    <h3>🏢 Premium Properties</h3>
                    <p>Discover a wide range of premium residential and commercial properties in prime locations across Gorakhpur.</p>
                </div>
                
                <div class="feature">
                    <h3>🚀 Innovative Projects</h3>
                    <p>Explore our cutting-edge real estate projects designed with modern amenities and sustainable living concepts.</p>
                </div>
                
                <div class="feature">
                    <h3>🤝 Trusted Service</h3>
                    <p>Experience professional, transparent, and reliable real estate services with complete customer satisfaction.</p>
                </div>
                
                <div class="feature">
                    <h3>📍 Prime Locations</h3>
                    <p>Properties strategically located in developing areas with excellent connectivity and growth potential.</p>
                </div>
                
                <div class="feature">
                    <h3>💎 Quality Assurance</h3>
                    <p>Every property is thoroughly verified for legal compliance, quality construction, and value appreciation.</p>
                </div>
                
                <div class="feature">
                    <h3>📞 Expert Support</h3>
                    <p>Our experienced team provides end-to-end assistance from property selection to documentation and possession.</p>
                </div>
            </div>
            
            <footer>
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <p><strong>Address:</strong> APS Dream Home Office, Gorakhpur, Uttar Pradesh</p>
                    <p><strong>Phone:</strong> +91-XXXXXXXXXX</p>
                    <p><strong>Email:</strong> info@apsdreamhome.com</p>
                    <p><strong>Website:</strong> www.apsdreamhome.com</p>
                </div>
                
                <div class="admin-links">
                    <a href="<?php echo $base_url; ?>admin/login.php">Admin Login</a>
                    <a href="<?php echo $base_url; ?>login.php">User Login</a>
                    <a href="<?php echo $base_url; ?>registration.php">Register</a>
                    <a href="<?php echo $base_url; ?>test.php">System Test</a>
                </div>
                
                <div style="margin-top: 20px; color: #718096; font-size: 0.9rem;">
                    <p>&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
                    <p>Designed and Developed for APS Dream Home Real Estate</p>
                </div>
            </footer>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle other pages with simple redirects or includes
if ($request_path === 'properties' || $request_path === 'properties.php') {
    if (file_exists('properties.php')) {
        include 'properties.php';
    } else {
        echo '<h1>Properties</h1><p>Properties page coming soon!</p>';
    }
} elseif ($request_path === 'about' || $request_path === 'about.php') {
    if (file_exists('about.php')) {
        include 'about.php';
    } else {
        echo '<h1>About Us</h1><p>About APS Dream Home coming soon!</p>';
    }
} elseif ($request_path === 'contact' || $request_path === 'contact.php') {
    if (file_exists('contact.php')) {
        include 'contact.php';
    } else {
        echo '<h1>Contact Us</h1><p>Contact information coming soon!</p>';
    }
} elseif ($request_path === 'projects' || $request_path === 'projects.php') {
    if (file_exists('projects.php')) {
        include 'projects.php';
    } else {
        echo '<h1>Our Projects</h1><p>Project information coming soon!</p>';
    }
} elseif (strpos($request_path, 'admin') === 0) {
    // Admin pages
    $admin_file = 'admin/' . str_replace('admin/', '', $request_path);
    if (file_exists($admin_file)) {
        include $admin_file;
    } elseif (file_exists('admin/index.php')) {
        include 'admin/index.php';
    } else {
        echo '<h1>Admin Panel</h1><p>Admin functionality coming soon!</p>';
    }
} elseif ($request_path === 'login' || $request_path === 'login.php') {
    if (file_exists('login.php')) {
        include 'login.php';
    } else {
        echo '<h1>Login</h1><p>Login page coming soon!</p>';
    }
} elseif ($request_path === 'register' || $request_path === 'registration.php') {
    if (file_exists('registration.php')) {
        include 'registration.php';
    } elseif (file_exists('register.php')) {
        include 'register.php';
    } else {
        echo '<h1>Register</h1><p>Registration page coming soon!</p>';
    }
} elseif ($request_path === 'test' || $request_path === 'test.php') {
    if (file_exists('test.php')) {
        include 'test.php';
    } else {
        echo '<h1>System Test</h1><p>Test page coming soon!</p>';
    }
} else {
    // 404 - Page not found
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>404 - Page Not Found</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            h1 { color: #e53e3e; }
            a { color: #667eea; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <h1>404 - Page Not Found</h1>
        <p>The page you requested (' . htmlspecialchars($request_path) . ') could not be found.</p>
        <p><a href="' . $base_url . '">Return to Homepage</a></p>
        <hr>
        <p><small>APS Dream Home - Real Estate Solutions</small></p>
    </body>
    </html>';
}

exit;