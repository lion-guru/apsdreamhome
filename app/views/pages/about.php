<?php

/**
 * Enhanced About Page - APS Dream Home
 * Modern UI/UX for company information and team details
 */

// Set page title and description for layout
$page_title = $title ?? 'About Us - APS Dream Home';
$page_description = 'Learn about APS Dream Home - your trusted real estate partner since 2009. Discover our story, values, and commitment to excellence.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="about APS Dream Home, real estate company Gorakhpur, property developers, real estate services">
    
    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            --dark-gradient: linear-gradient(135deg, #343a40 0%, #495057 100%);
            --light-gradient: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 120px 0;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 3rem;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: none;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .team-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: none;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .team-image {
            height: 250px;
            background: var(--light-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #6c757d;
        }

        .stats-section {
            background: var(--dark-gradient);
            color: white;
            padding: 80px 0;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: var(--warning-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .timeline-item {
            position: relative;
            padding-left: 3rem;
            margin-bottom: 2rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--primary-gradient);
        }

        .timeline-dot {
            position: absolute;
            left: -8px;
            top: 0;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--primary-gradient);
            border: 3px solid white;
        }

        .btn-gradient {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .testimonial-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            border: none;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 1rem;
            left: 1.5rem;
            font-size: 4rem;
            color: var(--primary-gradient);
            opacity: 0.2;
            font-family: Georgia, serif;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title" data-aos="fade-right">
                        About
                        <span class="text-warning">APS Dream Home</span>
                    </h1>
                    <p class="hero-subtitle" data-aos="fade-right" data-aos-delay="100">
                        Your trusted partner in real estate solutions since 2009. We bring dreams to reality
                        with our commitment to excellence, integrity, and customer satisfaction.
                    </p>
                    <div class="d-flex gap-3" data-aos="fade-right" data-aos-delay="200">
                        <a href="#contact" class="btn btn-warning btn-lg">
                            <i class="fas fa-phone me-2"></i>Contact Us
                        </a>
                        <a href="#projects" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-building me-2"></i>View Projects
                        </a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="text-center">
                        <i class="fas fa-home fa-8x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title" data-aos="fade-up">Our Story</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                    Building dreams and creating lasting relationships
                </p>
            </div>
            
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h3 class="mb-3">Leading Real Estate Excellence</h3>
                    <p class="text-muted mb-4">
                        Founded in 2009, APS Dream Home has been at the forefront of real estate development in Gorakhpur and surrounding regions. Our journey began with a simple mission: to provide quality housing solutions that transform lives and build communities.
                    </p>
                    <p class="text-muted mb-4">
                        Over the years, we have successfully delivered numerous residential and commercial projects, earning the trust of thousands of satisfied customers. Our commitment to quality, transparency, and customer satisfaction has made us a preferred choice for real estate investments.
                    </p>
                    <div class="d-flex gap-3">
                        <div class="text-center">
                            <i class="fas fa-award fa-3x text-primary mb-2"></i>
                            <p class="fw-bold">15+ Years</p>
                            <p class="text-muted small">Experience</p>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-users fa-3x text-success mb-2"></i>
                            <p class="fw-bold">5000+</p>
                            <p class="text-muted small">Happy Customers</p>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-building fa-3x text-warning mb-2"></i>
                            <p class="fw-bold">50+</p>
                            <p class="text-muted small">Projects</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="bg-light rounded-4 p-4">
                        <h4 class="mb-3">Our Mission</h4>
                        <p class="text-muted">
                            To create exceptional living spaces that exceed expectations and build lasting relationships with our customers through transparency, quality, and innovation.
                        </p>
                        <h4 class="mb-3">Our Vision</h4>
                        <p class="text-muted">
                            To be the most trusted real estate developer in the region, known for delivering quality projects and creating value for all stakeholders.
                        </p>
                        <h4 class="mb-3">Our Values</h4>
                        <ul class="text-muted">
                            <li>Integrity in all dealings</li>
                            <li>Quality construction</li>
                            <li>Customer satisfaction</li>
                            <li>Innovation in design</li>
                            <li>Sustainable development</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title" data-aos="fade-up">Why Choose APS Dream Home</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                    We deliver excellence in every aspect of real estate
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Trusted & Reliable</h4>
                        <p class="text-muted">15+ years of trusted service with transparent dealings and ethical business practices.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                        <h4>Quality Construction</h4>
                        <p class="text-muted">Premium materials, modern techniques, and attention to detail in every project.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Customer First</h4>
                        <p class="text-muted">Your satisfaction is our priority with personalized service and support.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <h4>Prime Locations</h4>
                        <p class="text-muted">Strategic locations with excellent connectivity and future growth potential.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <h4>Best Prices</h4>
                        <p class="text-muted">Competitive pricing with flexible payment options and financing support.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h4>RERA Approved</h4>
                        <p class="text-muted">All projects are RERA registered ensuring complete legal compliance.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3" data-aos="fade-up">
                    <div class="stat-item">
                        <div class="stat-number" data-count="50">0</div>
                        <p class="h5">Projects Completed</p>
                        <p class="text-white-50">Successfully delivered</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item">
                        <div class="stat-number" data-count="5000">0</div>
                        <p class="h5">Happy Customers</p>
                        <p class="text-white-50">Satisfied families</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item">
                        <div class="stat-number" data-count="15">0</div>
                        <p class="h5">Years Experience</p>
                        <p class="text-white-50">In real estate</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item">
                        <div class="stat-number" data-count="100">0</div>
                        <p class="h5">Acres Developed</p>
                        <p class="text-white-50">Land transformed</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title" data-aos="fade-up">Our Journey</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                    Milestones that define our success
                </p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="timeline-item" data-aos="fade-up" data-aos-delay="200">
                        <div class="timeline-dot"></div>
                        <h5>2009 - Foundation</h5>
                        <p class="text-muted">APS Dream Home was established with a vision to provide quality housing solutions.</p>
                    </div>
                    <div class="timeline-item" data-aos="fade-up" data-aos-delay="300">
                        <div class="timeline-dot"></div>
                        <h5>2012 - First Project</h5>
                        <p class="text-muted">Successfully completed our first residential project with 50+ happy families.</p>
                    </div>
                    <div class="timeline-item" data-aos="fade-up" data-aos-delay="400">
                        <div class="timeline-dot"></div>
                        <h5>2015 - Expansion</h5>
                        <p class="text-muted">Expanded operations to commercial real estate and multiple cities.</p>
                    </div>
                    <div class="timeline-item" data-aos="fade-up" data-aos-delay="500">
                        <div class="timeline-dot"></div>
                        <h5>2018 - Innovation</h5>
                        <p class="text-muted">Introduced smart home features and sustainable building practices.</p>
                    </div>
                    <div class="timeline-item" data-aos="fade-up" data-aos-delay="600">
                        <div class="timeline-dot"></div>
                        <h5>2024 - Excellence</h5>
                        <p class="text-muted">Recognized as leading real estate developer with 50+ completed projects.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title" data-aos="fade-up">Our Leadership Team</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                    Meet the people behind our success
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="team-card">
                        <div class="team-image">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Managing Director</h5>
                            <p class="text-muted">Visionary leader with 20+ years of experience in real estate development.</p>
                            <div class="d-flex gap-2">
                                <a href="#" class="text-primary"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="text-primary"><i class="fab fa-twitter"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="team-card">
                        <div class="team-image">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Technical Director</h5>
                            <p class="text-muted">Expert in construction management and quality assurance with 15+ years experience.</p>
                            <div class="d-flex gap-2">
                                <a href="#" class="text-primary"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="text-primary"><i class="fab fa-twitter"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="team-card">
                        <div class="team-image">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Sales Director</h5>
                            <p class="text-muted">Customer relationship expert with deep understanding of real estate market.</p>
                            <div class="d-flex gap-2">
                                <a href="#" class="text-primary"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="text-primary"><i class="fab fa-twitter"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title" data-aos="fade-up">What Our Customers Say</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                    Real experiences from real customers
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <p class="text-muted mb-4">
                            "Excellent service and quality construction. APS Dream Home delivered exactly what they promised. Our family is very happy with our new home."
                        </p>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle fa-3x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Ramesh Kumar</h6>
                                <p class="text-muted small mb-0">Homeowner</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card">
                        <p class="text-muted mb-4">
                            "Professional team, transparent dealings, and timely delivery. APS Dream Home made our dream of owning a property come true."
                        </p>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle fa-3x text-success me-3"></i>
                            <div>
                                <h6 class="mb-0">Priya Singh</h6>
                                <p class="text-muted small mb-0">Property Investor</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="testimonial-card">
                        <p class="text-muted mb-4">
                            "Great experience from start to finish. The team was very helpful and the quality of construction is outstanding."
                        </p>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle fa-3x text-warning me-3"></i>
                            <div>
                                <h6 class="mb-0">Amit Sharma</h6>
                                <p class="text-muted small mb-0">Business Owner</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4" data-aos="fade-up">Ready to Build Your Dream Home?</h2>
            <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                Join thousands of happy customers who have trusted APS Dream Home
            </p>
            <div data-aos="fade-up" data-aos-delay="200">
                <a href="/contact" class="btn btn-warning btn-lg me-3">
                    <i class="fas fa-phone me-2"></i>Contact Us
                </a>
                <a href="/properties" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-building me-2"></i>View Properties
                </a>
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

        // Animated Counter
        function animateCounter() {
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const increment = target / 100;
                let current = 0;
                
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.textContent = Math.ceil(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target + '+';
                    }
                };
                
                updateCounter();
            });
        }

        // Trigger counter animation when stats section is visible
        const statsSection = document.querySelector('.stats-section');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter();
                    observer.unobserve(entry.target);
                }
            });
        });

        observer.observe(statsSection);
    </script>
</body>
</html>
