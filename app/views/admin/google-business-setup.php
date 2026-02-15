<?php
require_once __DIR__ . '/core/init.php';

// Set page title and metadata
$page_title = 'Google Business Setup - APS Dream Homes';
$page_description = 'Complete guide for setting up Google My Business for APS Dream Homes. Optimize your online presence and attract more customers.';
$page_keywords = 'Google My Business, APS Dream Homes, local SEO, Gorakhpur real estate, business listing';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h($page_description); ?>">
    <meta name="keywords" content="<?php echo h($page_keywords); ?>">

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
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 120px 0 80px;
            color: white;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff20" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,96C1248,75,1344,53,1392,42.7L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        /* Setup Steps */
        .setup-section {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .step-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            border-left: 5px solid var(--primary-color);
        }

        .step-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .step-number {
            position: absolute;
            top: -20px;
            left: 30px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .step-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .step-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .step-description {
            color: #666;
            margin-bottom: 20px;
        }

        .step-action {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .step-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            color: white;
        }

        /* Benefits Section */
        .benefits-section {
            padding: 100px 0;
            background: white;
        }

        .benefit-item {
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .benefit-item:hover {
            background: #f8f9fa;
            transform: translateY(-5px);
        }

        .benefit-icon {
            font-size: 3rem;
            color: var(--success-color);
            margin-bottom: 20px;
        }

        .benefit-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 10px;
        }

        .benefit-description {
            color: #666;
        }

        /* Tools Section */
        .tools-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .tool-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .tool-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .tool-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--info-color), var(--primary-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }

        .tool-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .tool-description {
            color: #666;
            margin-bottom: 20px;
        }

        .tool-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .tool-link:hover {
            color: var(--secondary-color);
        }

        /* Checklist Section */
        .checklist-section {
            padding: 100px 0;
            background: white;
        }

        .checklist-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .checklist-item:hover {
            background: #f8f9fa;
            transform: translateX(10px);
        }

        .checklist-icon {
            color: var(--success-color);
            font-size: 1.5rem;
            margin-right: 15px;
            margin-top: 3px;
        }

        .checklist-content h4 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 5px;
        }

        .checklist-content p {
            color: #666;
            margin: 0;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 100px 0;
            text-align: center;
            color: white;
        }

        .btn-cta {
            background: white;
            color: var(--primary-color);
            padding: 15px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 10px;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            color: var(--primary-color);
        }

        .btn-outline-cta {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-outline-cta:hover {
            background: white;
            color: var(--primary-color);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 60px;
            }

            .step-card {
                padding: 30px 20px;
            }

            .step-number {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include_once __DIR__ . '/includes/components/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center hero-content" data-aos="fade-up">
                    <div class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill">
                        <i class="fab fa-google me-2"></i>Google Business Setup
                    </div>
                    <h1 class="display-3 fw-bold mb-4">Setup Google My Business</h1>
                    <p class="lead mb-4">
                        Complete guide to setup your Google My Business profile for APS Dream Homes.
                        Attract more customers and improve your online presence in Gorakhpur.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="#setup" class="btn btn-light btn-lg">
                            <i class="fas fa-rocket me-2"></i>Start Setup
                        </a>
                        <a href="#benefits" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-chart-line me-2"></i>View Benefits
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Setup Steps Section -->
    <section class="setup-section" id="setup">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Setup Steps</h2>
                <p class="lead text-muted">Follow these steps to create your Google My Business profile</p>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="fab fa-google"></i>
                        </div>
                        <h3 class="step-title">Create Google Account</h3>
                        <p class="step-description">
                            If you don't have a Google Account, create one using your business email.
                            This will be used to manage your Google My Business profile.
                        </p>
                        <a href="https://accounts.google.com/signup" target="_blank" class="step-action">
                            <i class="fas fa-external-link-alt me-2"></i>Create Account
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h3 class="step-title">Create Business Profile</h3>
                        <p class="step-description">
                            Go to Google My Business and create a new profile for "APS Dream Homes".
                            Add your business name, category, and location details.
                        </p>
                        <a href="https://business.google.com/create" target="_blank" class="step-action">
                            <i class="fas fa-external-link-alt me-2"></i>Create Profile
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3 class="step-title">Add Location Details</h3>
                        <p class="step-description">
                            Add your business address in Gorakhpur, service areas, and contact information.
                            Include phone number and website URL.
                        </p>
                        <a href="#" class="step-action" onclick="alert('Add: APS Dream Homes, Gorakhpur, Uttar Pradesh'); return false;">
                            <i class="fas fa-info-circle me-2"></i>View Details
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <div class="step-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <h3 class="step-title">Upload Photos</h3>
                        <p class="step-description">
                            Upload high-quality photos of your properties, office, team, and completed projects.
                            Use photos from your gallery page for best results.
                        </p>
                        <a href="<?php echo h(BASE_URL); ?>gallery" class="step-action">
                            <i class="fas fa-images me-2"></i>View Gallery
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="step-card">
                        <div class="step-number">5</div>
                        <div class="step-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="step-title">Get Reviews</h3>
                        <p class="step-description">
                            Ask satisfied customers to leave reviews on your Google My Business profile.
                            Use testimonials from your website as reference.
                        </p>
                        <a href="<?php echo h(BASE_URL); ?>testimonials" class="step-action">
                            <i class="fas fa-comments me-2"></i>View Testimonials
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="step-card">
                        <div class="step-number">6</div>
                        <div class="step-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3 class="step-title">Publish & Promote</h3>
                        <p class="step-description">
                            Verify your business and publish your profile. Start creating posts about
                            new properties and services to attract customers.
                        </p>
                        <a href="#tools" class="step-action">
                            <i class="fas fa-tools me-2"></i>View Tools
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section" id="benefits">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Why Google My Business?</h2>
                <p class="lead text-muted">Benefits of having a Google My Business profile</p>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="benefit-title">Better Visibility</h3>
                        <p class="benefit-description">
                            Appear in Google Search results and Google Maps for local real estate searches
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="benefit-title">More Customers</h3>
                        <p class="benefit-description">
                            Attract local customers searching for real estate services in Gorakhpur
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="benefit-title">Free Analytics</h3>
                        <p class="benefit-description">
                            Track how customers find your business and interact with your profile
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="benefit-title">Mobile Access</h3>
                        <p class="benefit-description">
                            Customers can easily call, visit website, or get directions from mobile devices
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tools Section -->
    <section class="tools-section" id="tools">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Essential Tools</h2>
                <p class="lead text-muted">Tools to manage and optimize your Google presence</p>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="tool-card">
                        <div class="tool-logo">GA</div>
                        <h3 class="tool-title">Google Analytics</h3>
                        <p class="tool-description">
                            Track website traffic and user behavior to optimize your marketing efforts
                        </p>
                        <a href="https://analytics.google.com" target="_blank" class="tool-link">
                            <i class="fas fa-external-link-alt me-2"></i>Get Started
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="tool-card">
                        <div class="tool-logo">SC</div>
                        <h3 class="tool-title">Search Console</h3>
                        <p class="tool-description">
                            Monitor your website's search performance and fix SEO issues
                        </p>
                        <a href="https://search.google.com/search-console" target="_blank" class="tool-link">
                            <i class="fas fa-external-link-alt me-2"></i>Get Started
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="tool-card">
                        <div class="tool-logo">GT</div>
                        <h3 class="tool-title">Google Trends</h3>
                        <p class="tool-description">
                            Research popular real estate keywords and trends in Gorakhpur
                        </p>
                        <a href="https://trends.google.com" target="_blank" class="tool-link">
                            <i class="fas fa-external-link-alt me-2"></i>Get Started
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="tool-card">
                        <div class="tool-logo">GM</div>
                        <h3 class="tool-title">Google Maps</h3>
                        <p class="tool-description">
                            Ensure your business location is accurately marked on Google Maps
                        </p>
                        <a href="https://maps.google.com" target="_blank" class="tool-link">
                            <i class="fas fa-external-link-alt me-2"></i>Get Started
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Checklist Section -->
    <section class="checklist-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Setup Checklist</h2>
                <p class="lead text-muted">Complete checklist for Google My Business setup</p>
            </div>

            <div class="row">
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="checklist-content">
                            <h4>Business Information</h4>
                            <p>Add accurate business name, address, phone number, and website</p>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="checklist-content">
                            <h4>Business Category</h4>
                            <p>Select "Real Estate Agency" or "Real Estate Service" as primary category</p>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="checklist-content">
                            <h4>Service Areas</h4>
                            <p>Set service areas to include Gorakhpur and nearby cities</p>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="checklist-content">
                            <h4>Business Hours</h4>
                            <p>Set accurate business hours for weekdays and weekends</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="checklist-content">
                            <h4>Photos & Videos</h4>
                            <p>Upload high-quality property photos, office pictures, and team photos</p>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="checklist-content">
                            <h4>Services Description</h4>
                            <p>Detailed description of your real estate services and specialties</p>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="checklist-content">
                            <h4>Q&A Section</h4>
                            <p>Add frequently asked questions and answers about your services</p>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="checklist-content">
                            <h4>Verification</h4>
                            <p>Complete business verification via phone or mail</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                    <h2 class="display-4 fw-bold mb-4">Ready to Grow Your Business?</h2>
                    <p class="lead mb-4">
                        Start your Google My Business setup today and attract more customers to APS Dream Homes.
                        Our complete guide will help you every step of the way.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="https://business.google.com/create" target="_blank" class="btn-cta">
                            <i class="fab fa-google me-2"></i>Start Now
                        </a>
                        <a href="<?php echo BASE_URL; ?>contact" class="btn-cta btn-outline-cta">
                            <i class="fas fa-phone me-2"></i>Get Help
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include_once __DIR__ . '/includes/components/footer.php'; ?>

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

        // Smooth scroll for anchor links
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

        // Track setup progress
        let setupProgress = 0;
        const totalSteps = 6;

        // Update progress when user clicks on setup steps
        document.querySelectorAll('.step-action').forEach(link => {
            link.addEventListener('click', function() {
                setupProgress++;
                const percentage = Math.round((setupProgress / totalSteps) * 100);

                // You can show a progress indicator here
                console.log(`Setup Progress: ${percentage}%`);

                // Save progress to localStorage
                localStorage.setItem('gmbSetupProgress', percentage);
            });
        });

        // Load saved progress
        const savedProgress = localStorage.getItem('gmbSetupProgress');
        if (savedProgress) {
            console.log(`Previous Setup Progress: ${savedProgress}%`);
        }
    </script>
</body>
</html>
