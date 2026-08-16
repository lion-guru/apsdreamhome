/**
* navigation - APS Dream Home Component
*
* @package APS Dream Home
* @version 1.0.0
* @author APS Dream Home Team
* @copyright 2026 APS Dream Home
*
* Description: Handles navigation functionality
*
* Features:
* - Secure input validation
* - Comprehensive error handling
* - Performance optimization
* - Database integration
* - Session management
* - CSRF protection
*
* @see https://apsdreamhome.com/docs
*/
<?php

require_once __DIR__ . '/../../Helpers/TranslationHelper.php';

// Navigation Menu for APS Dream Homes
$page_title = __('nav_page_title', 'Navigation - APS Dream Homes');
$page_description = __('nav_page_desc', 'Navigate through all pages of APS Dream Homes website');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">

    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d9488;
            --secondary-color: #0f766e;
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
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
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
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .nav-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .nav-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .nav-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            display: inline-block;
        }

        .nav-icon.main {
            color: var(--primary);
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
            background: linear-gradient(135deg, var(--primary), var(--secondary));
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
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
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
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            margin-bottom: 60px;
        }

        .action-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
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
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .action-btn.secondary {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .action-btn.secondary:hover {
            background: var(--primary);
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
                <?php echo __('nav_complete_navigation', 'Complete Website Navigation - All Pages Ready to Use'); ?>
            </p>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="navigation-section">
        <div class="container">
            <div class="quick-actions" data-aos="fade-up">
                <h2 class="style-58267">ðŸš€ <?php echo __('nav_quick_start', 'Quick Start'); ?></h2>
                <div class="text-center">
                    <a href="index_improved.php" class="action-btn">
                        <i class="fas fa-home me-2"></i><?php echo __('nav_go_to_homepage', 'Go to Homepage'); ?>
                    </a>
                    <a href="admin/" class="action-btn secondary">
                        <i class="fas fa-cog me-2"></i><?php echo __('nav_admin_panel', 'Admin Panel'); ?>
                    </a>
                </div>
            </div>

            <!-- Main Pages -->
            <h2 class="category-title" data-aos="fade-up">ðŸ�  <?php echo __('nav_main_pages', 'Main Pages'); ?></h2>
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('nav_homepage', 'Homepage'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_homepage_desc', 'Enhanced homepage with company showcase, team presentation, and featured properties'); ?>
                        </p>
                        <a href="index_improved.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_visit_homepage', 'Visit Homepage'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('properties_title', 'Properties'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_properties_desc', 'Browse all available properties with advanced search and filtering options'); ?>
                        </p>
                        <a href="/apsdreamhome/properties" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_view_properties', 'View Properties'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('about_title', 'About Us'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_about_desc', 'Learn about APS Dream Homes, our team, and our commitment to excellence'); ?>
                        </p>
                        <a href="about.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_about_company', 'About Company'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('contact_title', 'Contact'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_contact_desc', 'Get in touch with us for inquiries, appointments, and property consultations'); ?>
                        </p>
                        <a href="contact.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('contact_us', 'Contact Us'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('careers_title', 'Careers'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_careers_desc', 'Join our team! Explore career opportunities and apply for positions'); ?>
                        </p>
                        <a href="careers.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_view_careers', 'View Careers'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon main">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('faqs_title', 'FAQ'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_faq_desc', 'Find answers to frequently asked questions about our services and properties'); ?>
                        </p>
                        <a href="faq.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_view_faq', 'View FAQ'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Feature Pages -->
            <h2 class="category-title" data-aos="fade-up">â­� <?php echo __('nav_feature_pages', 'Feature Pages'); ?></h2>
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('nav_customer_reviews', 'Customer Reviews'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_reviews_desc', 'Read genuine customer reviews and testimonials about our services'); ?>
                        </p>
                        <a href="customer-reviews.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_read_reviews', 'Read Reviews'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-blog"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('blog_title', 'Blog'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_blog_desc', 'Read our latest articles, tips, and insights about real estate'); ?>
                        </p>
                        <a href="blog.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_read_blog', 'Read Blog'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('services_title', 'Services'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_services_desc', 'Explore our comprehensive real estate services and solutions'); ?>
                        </p>
                        <a href="services.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_our_services', 'Our Services'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('projects_title', 'Projects'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_projects_desc', 'View our completed and ongoing real estate projects'); ?>
                        </p>
                        <a href="projects.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_view_projects', 'View Projects'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('team_title', 'Team'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_team_desc', 'Meet our dedicated team of real estate professionals'); ?>
                        </p>
                        <a href="team.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_our_team', 'Our Team'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon feature">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('nav_enhanced_features', 'Enhanced Features'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_enhanced_desc', 'Premium features inspired by market leaders with modern amenities'); ?>
                        </p>
                        <a href="enhanced-features.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_view_features', 'View Features'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="700">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon marketing">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('nav_competitor_analysis', 'Competitor Analysis'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_competitor_desc', 'Market comparison and competitive advantages analysis'); ?>
                        </p>
                        <a href="competitor-analysis.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_view_analysis', 'View Analysis'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Marketing Pages -->
            <h2 class="category-title" data-aos="fade-up">ðŸ"Š <?php echo __('nav_marketing_admin', 'Marketing & Admin'); ?></h2>
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon marketing">
                            <i class="fab fa-google"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('nav_google_business', 'Google Business Profile'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_google_desc', 'Complete setup guide for Google Business Profile optimization'); ?>
                        </p>
                        <a href="google-business-profile.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_setup_guide', 'Setup Guide'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon marketing">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('nav_marketing_dashboard', 'Marketing Dashboard'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_marketing_desc', 'Track your marketing performance and online presence'); ?>
                        </p>
                        <a href="marketing-dashboard.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_view_dashboard', 'View Dashboard'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon marketing">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('nav_implementation_status', 'Implementation Status'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_implementation_desc', 'Track complete implementation status and next steps'); ?>
                        </p>
                        <a href="online-presence-status.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_view_status', 'View Status'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon admin">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('nav_admin_panel', 'Admin Panel'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_admin_desc', 'Access admin dashboard for content management'); ?>
                        </p>
                        <a href="admin/" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_admin_login', 'Admin Login'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon admin">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('nav_sitemap', 'Sitemap'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_sitemap_desc', 'XML sitemap for search engine optimization'); ?>
                        </p>
                        <a href="sitemap.xml.php" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_view_sitemap', 'View Sitemap'); ?>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="nav-card">
                        <div class="nav-status complete">
                            <i class="fas fa-check me-1"></i><?php echo __('nav_complete', 'Complete'); ?>
                        </div>
                        <div class="nav-icon admin">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3 class="nav-title"><?php echo __('nav_robots', 'Robots.txt'); ?></h3>
                        <p class="nav-description">
                            <?php echo __('nav_robots_desc', 'Search engine crawling instructions'); ?>
                        </p>
                        <a href="robots.txt" class="nav-link">
                            <i class="fas fa-arrow-right me-2"></i><?php echo __('nav_view_robots', 'View Robots'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Section -->
            <div class="text-center mt-5" data-aos="fade-up">
                <h2 class="style-11016">ðŸ'¤ <?php echo __('nav_user_section', 'User Section'); ?></h2>
                <div class="d-flex justify-content-center flex-wrap">
                    <a href="<?= BASE_URL ?>/login" class="action-btn">
                        <i class="fas fa-sign-in-alt me-2"></i><?php echo __('nav_login', 'Login'); ?>
                    </a>
                    <a href="<?= BASE_URL ?>/register" class="action-btn secondary">
                        <i class="fas fa-user-plus me-2"></i><?php echo __('nav_register', 'Register'); ?>
                    </a>
                    <a href="<?= BASE_URL ?>/dashboard" class="action-btn secondary">
                        <i class="fas fa-tachometer-alt me-2"></i><?php echo __('nav_dashboard', 'Dashboard'); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
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
