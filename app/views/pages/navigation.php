<?php
// Navigation Menu for APS Dream Homes
// This file provides easy navigation to all implemented pages

require_once __DIR__ . '/init.php';

// Set page metadata
$page_title = 'Navigation - APS Dream Homes';
$page_description = 'Navigate through all pages of APS Dream Homes website';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 80px 0 60px;
            color: white;
            text-align: center;
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        .navigation-section {
            padding: 60px 0;
        }
        
        .nav-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .nav-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .nav-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .nav-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .nav-icon.main {
            color: var(--primary-color);
        }
        
        .nav-icon.feature {
            color: var(--success-color);
        }
        
        .nav-icon.marketing {
            color: var(--info-color);
        }
        
        .nav-icon.admin {
            color: var(--warning-color);
        }
        
        .nav-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .nav-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .nav-link {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            color: white;
        }
        
        .nav-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .nav-status.complete {
            background: #d4edda;
            color: var(--success-color);
        }
        
        .category-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .quick-actions {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 60px;
        }
        
        .action-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            margin: 10px;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            color: white;
        }
        
        .action-btn.secondary {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .action-btn.secondary:hover {
            background: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .nav-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title" data-aos="fade-up">APS Dream Homes</h1>
            <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="100">
                Complete Website Navigation - All Pages Ready to Use
            </p>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="navigation-section">
        <div class="container">
            <div class="quick-actions" data-aos="fade-up">
                <h2 style="text-align: center; margin-bottom: 30px;">🚀 Quick Start</h2>
                <div class="text-center">
                    <a href="index_improved.php" class="action-btn">
                        <i class="fas fa-home me-2"></i>Go to Homepage
                    </a>
                    <a href="admin/" class="action-btn secondary">
                        <i class="fas fa-cog me-2"></i>Admin Panel
                    </a>
                </div>
            </div>
            
            <!-- Main Pages -->
            <h2 class="category-title" data-aos="fade-up">🏠 Main Pages</h2>
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3 class="nav-title">Homepage</h3>
                        <p class="nav-description">
                            Enhanced homepage with company showcase, team presentation, and featured properties
                        </p>
                        <a href="index_improved.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>Visit Homepage
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3 class="nav-title">Properties</h3>
                        <p class="nav-description">
                            Browse all available properties with advanced search and filtering options
                        </p>
                        <a href="property-listings.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>View Properties
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h3 class="nav-title">About Us</h3>
                        <p class="nav-description">
                            Learn about APS Dream Homes, our team, and our commitment to excellence
                        </p>
                        <a href="about.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>About Company
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3 class="nav-title">Contact</h3>
                        <p class="nav-description">
                            Get in touch with us for inquiries, appointments, and property consultations
                        </p>
                        <a href="contact.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>Contact Us
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3 class="nav-title">Careers</h3>
                        <p class="nav-description">
                            Join our team! Explore career opportunities and apply for positions
                        </p>
                        <a href="careers.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>View Careers
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h3 class="nav-title">FAQ</h3>
                        <p class="nav-description">
                            Find answers to frequently asked questions about our services and properties
                        </p>
                        <a href="faq.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>View FAQ
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Feature Pages -->
            <h2 class="category-title" data-aos="fade-up">⭐ Feature Pages</h2>
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="nav-title">Customer Reviews</h3>
                        <p class="nav-description">
                            Read genuine customer reviews and testimonials about our services
                        </p>
                        <a href="customer-reviews.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>Read Reviews
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-blog"></i>
                        </div>
                        <h3 class="nav-title">Blog</h3>
                        <p class="nav-description">
                            Read our latest articles, tips, and insights about real estate
                        </p>
                        <a href="blog.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>Read Blog
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3 class="nav-title">Services</h3>
                        <p class="nav-description">
                            Explore our comprehensive real estate services and solutions
                        </p>
                        <a href="services.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>Our Services
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <h3 class="nav-title">Projects</h3>
                        <p class="nav-description">
                            View our completed and ongoing real estate projects
                        </p>
                        <a href="projects.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>View Projects
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="nav-title">Team</h3>
                        <p class="nav-description">
                            Meet our dedicated team of real estate professionals
                        </p>
                        <a href="team.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>Our Team
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3 class="nav-title">Enhanced Features</h3>
                        <p class="nav-description">
                            Premium features inspired by market leaders with modern amenities
                        </p>
                        <a href="enhanced-features.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>View Features
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="700">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon marketing">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3 class="nav-title">Competitor Analysis</h3>
                        <p class="nav-description">
                            Market comparison and competitive advantages analysis
                        </p>
                        <a href="competitor-analysis.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>View Analysis
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Marketing Pages -->
            <h2 class="category-title" data-aos="fade-up">📊 Marketing & Admin</h2>
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon marketing">
                            <i class="fab fa-google"></i>
                        </div>
                        <h3 class="nav-title">Google Business Profile</h3>
                        <p class="nav-description">
                            Complete setup guide for Google Business Profile optimization
                        </p>
                        <a href="google-business-profile.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>Setup Guide
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon marketing">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="nav-title">Marketing Dashboard</h3>
                        <p class="nav-description">
                            Track your marketing performance and online presence
                        </p>
                        <a href="marketing-dashboard.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>View Dashboard
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon marketing">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3 class="nav-title">Implementation Status</h3>
                        <p class="nav-description">
                            Track complete implementation status and next steps
                        </p>
                        <a href="online-presence-status.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>View Status
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon admin">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h3 class="nav-title">Admin Panel</h3>
                        <p class="nav-description">
                            Access admin dashboard for content management
                        </p>
                        <a href="admin/" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>Admin Login
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon admin">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <h3 class="nav-title">Sitemap</h3>
                        <p class="nav-description">
                            XML sitemap for search engine optimization
                        </p>
                        <a href="sitemap.xml.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>View Sitemap
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i>Complete
                        </div>
                        <div class="nav-icon admin">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3 class="nav-title">Robots.txt</h3>
                        <p class="nav-description">
                            Search engine crawling instructions
                        </p>
                        <a href="robots.txt" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i>View Robots
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- User Section -->
            <div class="text-center mt-5" data-aos="fade-up">
                <h2 style="margin-bottom: 30px;">👤 User Section</h2>
                <div class="d-flex justify-content-center flex-wrap">
                    <a href="login.php" class="action-btn">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <a href="register.php" class="action-btn secondary">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </a>
                    <a href="dashboard.php" class="action-btn secondary">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Track page visits
        document.querySelectorAll('.nav-link, .action-btn').forEach(link => {
            link.addEventListener('click', function(e) {
                const pageName = this.textContent.trim();
                console.log(`Navigating to: ${pageName}`);
                
                // Add visual feedback
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
            });
        });

        // Add hover effects
        document.querySelectorAll('.nav-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Show page load animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.5s ease';
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>
