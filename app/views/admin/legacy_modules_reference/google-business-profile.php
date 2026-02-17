<?php
// Google Business Profile Setup Guide and Implementation
require_once __DIR__ . '/core/init.php';

// Set page title and metadata
$page_title = 'Google Business Profile - APS Dream Homes';
$page_description = 'Complete Google Business Profile setup and management for APS Dream Homes Gorakhpur. Local SEO optimization and customer engagement.';
$page_keywords = 'Google Business Profile, APS Dream Homes, Gorakhpur real estate, local SEO, Google Maps, business listing';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    
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
        
        /* Status Section */
        .status-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .status-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .status-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .status-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .status-icon.setup {
            color: var(--info-color);
        }
        
        .status-icon.verified {
            color: var(--success-color);
        }
        
        .status-icon.optimized {
            color: var(--warning-color);
        }
        
        .status-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .status-description {
            color: #666;
            line-height: 1.6;
        }
        
        /* Setup Steps Section */
        .setup-section {
            padding: 100px 0;
            background: white;
        }
        
        .step-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            border-left: 5px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .step-card:hover {
            transform: translateX(10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .step-number {
            display: inline-block;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 50px;
            font-weight: 800;
            font-size: 1.2rem;
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
            line-height: 1.6;
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
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .benefit-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .benefit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .benefit-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .benefit-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .benefit-description {
            color: #666;
            line-height: 1.6;
        }
        
        /* Action Section */
        .action-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 100px 0;
            color: white;
        }
        
        .action-card {
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            text-align: center;
        }
        
        .btn-action {
            background: white;
            color: var(--primary-color);
            padding: 15px 35px;
            border-radius: 50px;
            border: none;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }
        
        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            color: var(--primary-color);
        }
        
        .btn-action-outline {
            background: transparent;
            border: 2px solid white;
            color: white;
        }
        
        .btn-action-outline:hover {
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
            
            .action-card {
                padding: 30px 20px;
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
                        <i class="fab fa-google me-2"></i>Google Business Profile
                    </div>
                    <h1 class="display-3 fw-bold mb-4">APS Dream Homes on Google</h1>
                    <p class="lead mb-4">
                        Complete Google Business Profile setup and optimization for maximum local visibility 
                        and customer engagement in Gorakhpur real estate market.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="#setup" class="btn btn-light btn-lg">
                            <i class="fas fa-cog me-2"></i>Setup Guide
                        </a>
                        <a href="#benefits" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-chart-line me-2"></i>View Benefits
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Status Section -->
    <section class="status-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Current Setup Status</h2>
                <p class="lead text-muted">Track your Google Business Profile setup progress</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="status-card">
                        <div class="status-icon setup">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3 class="status-title">Profile Setup</h3>
                        <p class="status-description">
                            Create and verify your Google Business Profile with accurate business information
                        </p>
                        <div class="progress mt-3">
                            <div class="progress-bar bg-info" style="width: 0%">0%</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="status-card">
                        <div class="status-icon verified">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="status-title">Verification</h3>
                        <p class="status-description">
                            Get your business verified to establish trust and unlock all features
                        </p>
                        <div class="progress mt-3">
                            <div class="progress-bar bg-success" style="width: 0%">0%</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="status-card">
                        <div class="status-icon optimized">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="status-title">Optimization</h3>
                        <p class="status-description">
                            Complete profile optimization with photos, reviews, and regular updates
                        </p>
                        <div class="progress mt-3">
                            <div class="progress-bar bg-warning" style="width: 0%">0%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Setup Steps Section -->
    <section class="setup-section" id="setup">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Setup Guide</h2>
                <p class="lead text-muted">Follow these steps to create your Google Business Profile</p>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="step-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="step-number">1</div>
                        <h3 class="step-title">Create Google Account</h3>
                        <p class="step-description">
                            If you don't have one, create a Google Account for your business. Use your business email 
                            address (e.g., contact@apsdreamhomes.com) for professional appearance.
                        </p>
                        <a href="https://accounts.google.com/signup" target="_blank" class="step-action">
                            <i class="fas fa-user-plus me-2"></i>Create Google Account
                        </a>
                    </div>
                    
                    <div class="step-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="step-number">2</div>
                        <h3 class="step-title">Start Business Profile</h3>
                        <p class="step-description">
                            Go to Google Business Profile and click "Manage now". Search for "APS Dream Homes" 
                            to see if it already exists, or create a new listing.
                        </p>
                        <a href="https://business.google.com/create" target="_blank" class="step-action">
                            <i class="fas fa-plus-circle me-2"></i>Create Business Profile
                        </a>
                    </div>
                    
                    <div class="step-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="step-number">3</div>
                        <h3 class="step-title">Enter Business Information</h3>
                        <p class="step-description">
                            Fill in complete business details:
                            <br><br>
                            <strong>Business Name:</strong> APS Dream Homes<br>
                            <strong>Category:</strong> Real Estate Agency<br>
                            <strong>Address:</strong> First floor near ganpati lawn Singha, Kunraghat, Gorakhpur, Uttar Pradesh<br>
                            <strong>Phone:</strong> +91 XXXXX XXXXX<br>
                            <strong>Website:</strong> https://apsdreamhomes.com<br>
                            <strong>Services:</strong> Real Estate Sales, Property Management, Investment Advisory
                        </p>
                    </div>
                    
                    <div class="step-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="step-number">4</div>
                        <h3 class="step-title">Verify Your Business</h3>
                        <p class="step-description">
                            Choose verification method:
                            <br><br>
                            <strong>Postcard:</strong> Google will send a postcard with verification code (5-7 days)<br>
                            <strong>Phone:</strong> Immediate verification via phone call<br>
                            <strong>Email:</strong> If available for your business type<br>
                            <strong>Instant:</strong> If you have Google Search Console verified
                        </p>
                    </div>
                    
                    <div class="step-card" data-aos="fade-up" data-aos-delay="500">
                        <div class="step-number">5</div>
                        <h3 class="step-title">Complete Profile Setup</h3>
                        <p class="step-description">
                            After verification, complete your profile:
                            <br><br>
                            • Add business hours (9:00 AM - 7:00 PM)<br>
                            • Upload high-quality photos of properties and office<br>
                            • Add services and service areas<br>
                            • Write detailed business description<br>
                            • Set up messaging and call tracking
                        </p>
                    </div>
                    
                    <div class="step-card" data-aos="fade-up" data-aos-delay="600">
                        <div class="step-number">6</div>
                        <h3 class="step-title">Optimize for Local SEO</h3>
                        <p class="step-description">
                            Maximize your local search visibility:
                            <br><br>
                            • Use keywords: "real estate Gorakhpur", "property dealer", "homes for sale"<br>
                            • Encourage customer reviews<br>
                            • Post regular updates and offers<br>
                            • Use Google Posts for announcements<br>
                            • Add Q&A section with common queries
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section" id="benefits">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Benefits of Google Business Profile</h2>
                <p class="lead text-muted">Why APS Dream Homes needs Google Business Profile</p>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="benefit-title">Local Search Visibility</h3>
                        <p class="benefit-description">
                            Appear in Google Maps and local search results when customers search for real estate in Gorakhpur
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="benefit-title">Customer Trust</h3>
                        <p class="benefit-description">
                            Build credibility with verified business information and customer reviews
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3 class="benefit-title">Direct Contact</h3>
                        <p class="benefit-description">
                            Customers can call, message, or visit your website directly from search results
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="benefit-title">Free Analytics</h3>
                        <p class="benefit-description">
                            Track how customers find you and interact with your business listing
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <h3 class="benefit-title">Photo Gallery</h3>
                        <p class="benefit-description">
                            Showcase properties, office, and team to build visual trust with potential clients
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="benefit-title">Customer Reviews</h3>
                        <p class="benefit-description">
                            Collect and respond to reviews to build social proof and improve rankings
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="700">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3 class="benefit-title">Google Posts</h3>
                        <p class="benefit-description">
                            Share updates, offers, and announcements directly in search results
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="800">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="benefit-title">Mobile Access</h3>
                        <p class="benefit-description">
                            Reach customers who are searching for properties on their mobile devices
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Action Section -->
    <section class="action-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto" data-aos="fade-up">
                    <div class="action-card">
                        <h2 class="display-4 fw-bold mb-4">Ready to Get Started?</h2>
                        <p class="lead mb-4">
                            Set up your Google Business Profile today and start attracting more customers in Gorakhpur. 
                            This is completely free and can significantly boost your online visibility.
                        </p>
                        
                        <div class="d-flex flex-wrap justify-content-center">
                            <a href="https://business.google.com/create" target="_blank" class="btn-action">
                                <i class="fab fa-google me-2"></i>Create Profile Now
                            </a>
                            <a href="tel:+919876543210" class="btn-action btn-action-outline">
                                <i class="fas fa-phone me-2"></i>Call for Help
                            </a>
                        </div>
                        
                        <div class="mt-4">
                            <h4 class="mb-3">Need Assistance?</h4>
                            <p>
                                Contact APS Dream Homes team for help with Google Business Profile setup:<br>
                                <strong>Phone:</strong> +91 XXXXX XXXXX<br>
                                <strong>Email:</strong> contact@apsdreamhomes.com<br>
                                <strong>Address:</strong> First floor near ganpati lawn Singha, Kunraghat, Gorakhpur
                            </p>
                        </div>
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

        // Update progress bars based on completion
        function updateProgress() {
            // Check localStorage for completion status
            const setupComplete = localStorage.getItem('gbp-setup') === 'true';
            const verificationComplete = localStorage.getItem('gbp-verification') === 'true';
            const optimizationComplete = localStorage.getItem('gbp-optimization') === 'true';
            
            // Update progress bars
            const setupProgress = setupComplete ? 100 : 0;
            const verificationProgress = verificationComplete ? 100 : 0;
            const optimizationProgress = optimizationComplete ? 100 : 0;
            
            document.querySelector('.progress-bar.bg-info').style.width = setupProgress + '%';
            document.querySelector('.progress-bar.bg-success').style.width = verificationProgress + '%';
            document.querySelector('.progress-bar.bg-warning').style.width = optimizationProgress + '%';
        }

        // Mark steps as complete
        document.querySelectorAll('.step-action').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!this.href.includes('http')) {
                    e.preventDefault();
                    
                    // Find the step card
                    const stepCard = this.closest('.step-card');
                    const stepNumber = stepCard.querySelector('.step-number').textContent;
                    
                    // Mark as complete
                    stepCard.style.background = '#e8f5e8';
                    stepCard.style.borderColor = 'var(--success-color)';
                    
                    // Update progress based on step
                    if (stepNumber === '1' || stepNumber === '2') {
                        localStorage.setItem('gbp-setup', 'true');
                    } else if (stepNumber === '4') {
                        localStorage.setItem('gbp-verification', 'true');
                    } else if (stepNumber === '5' || stepNumber === '6') {
                        localStorage.setItem('gbp-optimization', 'true');
                    }
                    
                    updateProgress();
                    
                    // Show success message
                    this.innerHTML = '<i class="fas fa-check me-2"></i>Completed';
                    this.style.background = 'var(--success-color)';
                }
            });
        });

        // Initialize progress on page load
        updateProgress();

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                if (!this.href.includes('http')) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Track setup progress
        function trackProgress() {
            const totalSteps = 6;
            const completedSteps = document.querySelectorAll('.step-action[style*="completed"]').length;
            const progressPercentage = Math.round((completedSteps / totalSteps) * 100);
            
            console.log(`Setup Progress: ${progressPercentage}% (${completedSteps}/${totalSteps} steps completed)`);
            
            // Show completion message
            if (progressPercentage === 100) {
                alert('Congratulations! APS Dream Homes Google Business Profile setup is complete!');
            }
        }

        // Add click tracking for external links
        document.querySelectorAll('a[href*="http"]').forEach(link => {
            link.addEventListener('click', function() {
                console.log('External link clicked:', this.href);
                // In a real application, this would send analytics data
            });
        });
    </script>
</body>
</html>

