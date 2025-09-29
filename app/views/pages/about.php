<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .about-section {
            padding: 80px 0;
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 25px;
            font-size: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .stats-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 60px 0;
        }
        .team-member {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .team-member:hover {
            transform: translateY(-5px);
        }
        .member-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            border: 4px solid #667eea;
        }
        .cta-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include '../app/views/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">About APS Dream Homes Pvt Ltd</h1>
                    <p class="lead mb-4">
                        Leading the way in premium real estate development in Gorakhpur with 8+ years of excellence,
                        innovation, and customer satisfaction.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="/contact" class="btn btn-light btn-lg">
                            <i class="fas fa-phone me-2"></i>Get In Touch
                        </a>
                        <a href="/properties" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-search me-2"></i>View Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h2 class="mb-4">Our Story</h2>
                    <p class="lead mb-4">
                        Founded in 2016, APS Dream Homes Pvt Ltd has been at the forefront of real estate development
                        in Eastern Uttar Pradesh. What started as a vision to provide quality housing has now become
                        a trusted name in the industry.
                    </p>
                    <p class="mb-4">
                        We specialize in developing premium residential and commercial properties that combine
                        modern design, quality construction, and strategic locations. Our commitment to excellence
                        has helped us deliver over 500 properties and serve more than 1000 happy families.
                    </p>
                    <div class="row">
                        <div class="col-md-6">
                            <h4><i class="fas fa-award text-primary me-2"></i>Our Mission</h4>
                            <p>To provide exceptional real estate solutions that exceed customer expectations and
                               contribute to the growth of sustainable communities.</p>
                        </div>
                        <div class="col-md-6">
                            <h4><i class="fas fa-eye text-primary me-2"></i>Our Vision</h4>
                            <p>To be the most trusted and innovative real estate developer in Eastern UP,
                               setting new standards in quality and customer service.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                         alt="Company Office" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="about-section bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Why Choose APS Dream Homes?</h2>
                <p class="lead text-muted">Experience the difference with our professional approach and commitment to excellence</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Registered Company</h5>
                        <p class="text-muted">Licensed under Companies Act 2013 with proper legal compliance and transparent operations. Registration No: U70109UP2022PTC163047</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5>Prime Locations</h5>
                        <p class="text-muted">Strategically located projects in high-growth areas with excellent connectivity, infrastructure, and amenities.</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5>Customer First</h5>
                        <p class="text-muted">Every decision we make prioritizes customer satisfaction and long-term relationships built on trust and transparency.</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5>Timely Delivery</h5>
                        <p class="text-muted">Proven track record of delivering projects on time without compromising quality standards or customer commitments.</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h5>Sustainable Development</h5>
                        <p class="text-muted">Eco-friendly construction practices with green spaces, energy-efficient designs, and environmentally conscious development.</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5>24/7 Support</h5>
                        <p class="text-muted">Round-the-clock customer support and dedicated relationship managers ensuring complete peace of mind for all clients.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">8+</h2>
                    <h5>Years of Excellence</h5>
                    <p class="text-white-50">Serving customers since 2016</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">500+</h2>
                    <h5>Properties Delivered</h5>
                    <p class="text-white-50">Successful project completions</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">1000+</h2>
                    <h5>Happy Families</h5>
                    <p class="text-white-50">Satisfied customers and counting</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">15+</h2>
                    <h5>Prime Locations</h5>
                    <p class="text-white-50">Strategic development sites</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="about-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Meet Our Leadership Team</h2>
                <p class="lead text-muted">Experienced professionals driving our vision forward</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="team-member">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                             alt="CEO" class="member-image">
                        <h5>Abhay Pratap Singh</h5>
                        <p class="text-primary mb-2">Founder & CEO</p>
                        <p class="text-muted">Visionary leader with 15+ years of experience in real estate development and business management.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-primary me-3"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-primary me-3"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-primary"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="team-member">
                        <img src="https://images.unsplash.com/photo-1494790108755-2616b332c9e9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                             alt="CTO" class="member-image">
                        <h5>Priya Sharma</h5>
                        <p class="text-primary mb-2">Chief Operating Officer</p>
                        <p class="text-muted">Operations expert ensuring seamless project execution and quality control across all developments.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-primary me-3"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-primary me-3"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-primary"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="team-member">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                             alt="CMO" class="member-image">
                        <h5>Rahul Kumar</h5>
                        <p class="text-primary mb-2">Head of Sales & Marketing</p>
                        <p class="text-muted">Marketing strategist driving customer acquisition and brand building initiatives across multiple channels.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-primary me-3"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-primary me-3"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-primary"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="mb-4">Ready to Start Your Property Journey?</h2>
                    <p class="lead mb-4">
                        Join thousands of satisfied customers who have found their perfect home with APS Dream Homes Pvt Ltd
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="/contact" class="btn btn-light btn-lg">
                            <i class="fas fa-phone me-2"></i>Contact Us Today
                        </a>
                        <a href="/properties" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-search me-2"></i>Browse Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../app/views/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
