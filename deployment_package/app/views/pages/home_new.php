<?php
/**
 * New Home Page Template for APS Dream Home
 * Compatible with local assets and project structure
 */

// Set page metadata
$page_title = "APS Dream Home - Premium Real Estate in Gorakhpur";
$page_description = "Find your dream home with APS Dream Home. Premium properties in Gorakhpur, Lucknow & UP. Expert real estate services with modern technology.";
$page_keywords = "real estate Gorakhpur, property for sale, buy house, apartments Lucknow, real estate UP, dream home";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    <meta name="author" content="APS Dream Home">

    <!-- Local Assets -->
    <link href="<?php echo BASE_URL; ?>public/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/assets/plugins/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/css/style.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/css/header.css" rel="stylesheet">
    
    <style>
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.9), rgba(30, 58, 138, 0.9)), 
                         url('<?php echo BASE_URL; ?>public/assets/images/banner/ban1.jpg') center/cover;
            color: white;
            padding: 150px 0 100px;
            text-align: center;
            position: relative;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Property Cards */
        .property-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .property-img {
            height: 250px;
            object-fit: cover;
        }
        
        /* Features Section */
        .feature-box {
            text-align: center;
            padding: 2rem;
            border-radius: 10px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .feature-box:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: #1e40af;
            margin-bottom: 1rem;
        }
        
        /* Testimonials */
        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 1rem;
        }
        
        .testimonial-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>

<!-- Include Header -->
<?php include __DIR__ . '/../layouts/header_new.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Find Your Dream Home</h1>
            <p>Discover premium properties in Gorakhpur and across Uttar Pradesh with the most trusted real estate platform</p>
            
            <div class="hero-buttons">
                <a href="<?php echo BASE_URL; ?>public/properties" class="btn btn-warning btn-lg me-3">
                    <i class="fas fa-search me-2"></i>Browse Properties
                </a>
                <a href="<?php echo BASE_URL; ?>public/contact" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-phone me-2"></i>Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-4 fw-bold text-primary">Featured Properties</h2>
            <p class="lead text-muted">Discover our handpicked selection of premium properties</p>
        </div>
        
        <div class="row">
            <!-- Property 1 -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="property-card">
                    <img src="<?php echo BASE_URL; ?>public/assets/images/property1.jpg" alt="Luxury Villa" class="property-img">
                    <div class="card-body">
                        <h5 class="card-title">Luxury Villa in Suryoday City</h5>
                        <p class="card-text text-muted">Gorakhpur • 4 BHK • 2500 sq.ft.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h4 text-primary mb-0">₹75 Lac</span>
                            <a href="#" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Property 2 -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="property-card">
                    <img src="<?php echo BASE_URL; ?>public/assets/images/property2.jpg" alt="Modern Apartment" class="property-img">
                    <div class="card-body">
                        <h5 class="card-title">Modern 3 BHK Apartment</h5>
                        <p class="card-text text-muted">Lucknow • 3 BHK • 1800 sq.ft.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h4 text-primary mb-0">₹52 Lac</span>
                            <a href="#" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Property 3 -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="property-card">
                    <img src="<?php echo BASE_URL; ?>public/assets/images/property3.jpg" alt="Commercial Space" class="property-img">
                    <div class="card-body">
                        <h5 class="card-title">Commercial Space</h5>
                        <p class="card-text text-muted">Gorakhpur • Shop • 1200 sq.ft.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h4 text-primary mb-0">₹35 Lac</span>
                            <a href="#" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>public/properties" class="btn btn-outline-primary btn-lg">
                View All Properties <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-4 fw-bold text-primary">Why Choose APS Dream Home?</h2>
            <p class="lead text-muted">We provide exceptional real estate services with trust and transparency</p>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Trusted & Verified</h4>
                    <p>All properties are thoroughly verified with legal documentation</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h4>Premium Properties</h4>
                    <p>Handpicked selection of the best properties in Uttar Pradesh</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Expert Support</h4>
                    <p>Dedicated team of real estate experts to guide you</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <h4>Best Prices</h4>
                    <p>Competitive pricing and transparent transactions</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-4 fw-bold text-primary">What Our Customers Say</h2>
            <p class="lead text-muted">Real stories from happy homeowners</p>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card">
                    <img src="<?php echo BASE_URL; ?>public/assets/images/profile/img_avatar3.png" alt="Customer" class="testimonial-img">
                    <h5>Rajesh Kumar</h5>
                    <p class="text-muted">Gorakhpur</p>
                    <p class="testimonial-text">"APS Dream Home helped me find my perfect 3 BHK apartment. Their service was exceptional and transparent throughout the process."</p>
                    <div class="text-warning">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card">
                    <img src="<?php echo BASE_URL; ?>public/assets/images/profile/img_avatar3.png" alt="Customer" class="testimonial-img">
                    <h5>Priya Singh</h5>
                    <p class="text-muted">Lucknow</p>
                    <p class="testimonial-text">"The team at APS made our home buying experience smooth and hassle-free. Highly recommended for their professionalism!"</p>
                    <div class="text-warning">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card">
                    <img src="<?php echo BASE_URL; ?>public/assets/images/profile/img_avatar3.png" alt="Customer" class="testimonial-img">
                    <h5>Amit Verma</h5>
                    <p class="text-muted">Varanasi</p>
                    <p class="testimonial-text">"Found my dream commercial space through APS. Their market knowledge and negotiation skills saved me lakhs!"</p>
                    <div class="text-warning">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="display-4 fw-bold mb-4">Ready to Find Your Dream Home?</h2>
        <p class="lead mb-4">Join thousands of satisfied customers who found their perfect home with APS Dream Home</p>
        
        <div class="cta-buttons">
            <a href="<?php echo BASE_URL; ?>public/contact" class="btn btn-warning btn-lg me-3">
                <i class="fas fa-phone me-2"></i>Get Free Consultation
            </a>
            <a href="<?php echo BASE_URL; ?>public/properties" class="btn btn-outline-light btn-lg">
                <i class="fas fa-search me-2"></i>Explore Properties
            </a>
        </div>
    </div>
</section>

<!-- Include Footer -->
<?php include __DIR__ . '/../layouts/footer_new.php'; ?>

<!-- Local Scripts -->
<script src="<?php echo BASE_URL; ?>public/assets/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/jquery.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/js/utils.js"></script>

<script>
    // Initialize components
    document.addEventListener('DOMContentLoaded', function() {
        // Smooth scrolling for navigation
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
        
        // Property card hover effects
        const propertyCards = document.querySelectorAll('.property-card');
        propertyCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            });
        });
    });
</script>

</body>
</html>