<?php
/**
 * About Page - APS Dream Homes Pvt Ltd - Enhanced Version
 * Incorporating successful strategies from Royal Sight Infra and Anantjit Infra
 */
// Define constant to allow database connection
define('INCLUDED_FROM_MAIN', true);
// Include database connection
require_once 'includes/db_connection.php';

try {
    global $pdo;
    $conn = $pdo;
   // $conn = getDbConnection();

    // Get company settings from database
    $settings_sql = "SELECT setting_name, setting_value FROM site_settings WHERE setting_name IN ('company_name', 'company_description', 'mission_statement', 'vision_statement', 'about_company', 'established_year', 'company_type', 'services_offered', 'company_phone', 'company_email', 'company_address', 'working_hours')";
    $settings_stmt = $conn->prepare($settings_sql);
    $settings_stmt->execute();
    $settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    $settings = [];
    $error_message = "Unable to load company information.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - APS Dream Homes Pvt Ltd - Leading Real Estate Developer in Gorakhpur</title>
    <meta name="description" content="Learn about APS Dream Homes Pvt Ltd, a leading real estate developer in Gorakhpur with 8+ years of experience. We specialize in residential and commercial property development with a focus on quality, transparency, and customer satisfaction.">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: transform 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(45deg, #667eea, #764ba2);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 40px;
            padding-left: 40px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -32px;
            top: 8px;
            width: 16px;
            height: 16px;
            background: #667eea;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 0 0 3px #667eea;
        }

        .service-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .service-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .team-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .team-card:hover {
            transform: translateY(-5px);
        }

        .team-image {
            height: 300px;
            object-fit: cover;
        }

        .vision-mission-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid #667eea;
        }

        .achievement-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }

        .achievement-card:hover {
            transform: translateY(-5px);
        }

        .achievement-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 1.5rem;
        }

        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-left: 5px solid #ffc107;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 15px 35px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-home me-2"></i>APS Dream Homes Pvt Ltd
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="properties.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="tel:<?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>" class="btn btn-outline-success me-2">
                        <i class="fas fa-phone me-1"></i><?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>
                    </a>
                    <a href="customer_login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="customer_registration.php" class="btn btn-success">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="section-title">About APS Dream Homes Pvt Ltd</h1>
                    <p class="lead mb-4">
                        Leading Real Estate Developer in Gorakhpur<br>
                        <small>Established 2022 • Registration No: U70109UP2022PTC163047</small>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                        <h2 class="mb-2">8+</h2>
                        <p class="mb-0">Years Experience</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-home fa-3x mb-3"></i>
                        <h2 class="mb-2">500+</h2>
                        <p class="mb-0">Properties Delivered</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h2 class="mb-2">1000+</h2>
                        <p class="mb-0">Happy Families</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                        <h2 class="mb-2">15+</h2>
                        <p class="mb-0">Prime Locations</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Company Journey -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Journey</h2>
                <p class="lead text-muted">From humble beginnings to becoming a trusted name in real estate</p>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="d-flex align-items-center mb-3">
                                <div class="badge bg-primary me-3">2022</div>
                                <h5 class="mb-0">Foundation & Registration</h5>
                            </div>
                            <p>APS Dream Homes Pvt Ltd was established as a registered real estate company under the Companies Act 2013. We started with a vision to transform the real estate landscape in Gorakhpur with professional expertise and customer-centric approach.</p>
                        </div>

                        <div class="timeline-item">
                            <div class="d-flex align-items-center mb-3">
                                <div class="badge bg-success me-3">2022-2023</div>
                                <h5 class="mb-0">Growth & Expansion</h5>
                            </div>
                            <p>Successfully launched multiple residential and commercial projects, establishing ourselves as a trusted name in Gorakhpur's real estate market. Our focus on quality construction and timely delivery earned us recognition among customers and industry peers.</p>
                        </div>

                        <div class="timeline-item">
                            <div class="d-flex align-items-center mb-3">
                                <div class="badge bg-info me-3">2024</div>
                                <h5 class="mb-0">Market Leadership</h5>
                            </div>
                            <p>Expanded our portfolio with premium developments and enhanced our service offerings. We became one of the most preferred real estate developers in Eastern Uttar Pradesh, known for transparency, quality, and customer satisfaction.</p>
                        </div>

                        <div class="timeline-item">
                            <div class="d-flex align-items-center mb-3">
                                <div class="badge bg-warning me-3">2025</div>
                                <h5 class="mb-0">Innovation & Excellence</h5>
                            </div>
                            <p>Continuing our journey of excellence with innovative construction techniques, sustainable development practices, and cutting-edge customer service. We remain committed to building not just properties, but lasting relationships.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Company Credentials</h5>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fas fa-building text-primary me-2"></i>
                                    <strong>Company Type:</strong> Private Limited
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-certificate text-primary me-2"></i>
                                    <strong>Registration:</strong> U70109UP2022PTC163047
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    <strong>Headquarters:</strong> Gorakhpur, UP
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <strong>Team Size:</strong> 25+ Professionals
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-chart-line text-primary me-2"></i>
                                    <strong>Growth Rate:</strong> 200% YoY
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-award text-primary me-2"></i>
                                    <strong>Projects:</strong> 8+ Completed
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision & Mission -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Vision & Mission</h2>
                <p class="lead text-muted">Guiding principles that drive our success</p>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="vision-mission-card">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-eye me-2"></i>Our Vision
                        </h4>
                        <p class="mb-0">
                            <?php echo $settings['vision_statement'] ?? 'To become a leading real estate developer in Eastern Uttar Pradesh, recognized for our commitment to quality, sustainability, and customer-centric approach. We envision creating modern living spaces that enhance the quality of life for families and contribute to the growth of smart cities.'; ?>
                        </p>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="vision-mission-card">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-bullseye me-2"></i>Our Mission
                        </h4>
                        <p class="mb-0">
                            <?php echo $settings['mission_statement'] ?? 'To develop and deliver exceptional real estate projects that create value for our customers, stakeholders, and the community. We strive to be the most trusted name in property development through innovation, quality construction, and transparent business practices.'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Services -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Comprehensive Services</h2>
                <p class="lead text-muted">Complete real estate solutions under one roof</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-hammer"></i>
                            </div>
                            <h5 class="card-title">Property Development</h5>
                            <p class="card-text">End-to-end property development services including planning, construction, and project management with focus on quality and timely delivery.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h5 class="card-title">Real Estate Consultancy</h5>
                            <p class="card-text">Expert guidance on property investment, market analysis, and real estate portfolio management to maximize your returns.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h5 class="card-title">Investment Advisory</h5>
                            <p class="card-text">Professional investment advice on real estate opportunities, helping you make informed decisions for optimal returns.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h5 class="card-title">Property Management</h5>
                            <p class="card-text">Complete property management services including maintenance, tenant relations, and rental management for property owners.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-gavel"></i>
                            </div>
                            <h5 class="card-title">Legal Documentation</h5>
                            <p class="card-text">Comprehensive legal support for all property transactions, documentation, and regulatory compliance requirements.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="service-icon">
                                <i class="fas fa-analytics"></i>
                            </div>
                            <h5 class="card-title">Market Research</h5>
                            <p class="card-text">In-depth market research and analysis to help you understand current trends, pricing, and investment opportunities.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Why Choose APS Dream Homes Pvt Ltd?</h2>
                <p class="lead text-muted">What sets us apart from other developers</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up">
                    <div class="achievement-card h-100">
                        <div class="achievement-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Registered Company</h5>
                        <p class="text-muted">Licensed and registered under Companies Act 2013 with proper legal compliance and transparent operations</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="achievement-card h-100">
                        <div class="achievement-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5>Timely Delivery</h5>
                        <p class="text-muted">Proven track record of delivering projects on time without compromising quality standards</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="achievement-card h-100">
                        <div class="achievement-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5>Transparent Deals</h5>
                        <p class="text-muted">Complete transparency in all transactions and pricing structures with no hidden costs</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="achievement-card h-100">
                        <div class="achievement-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h5>Sustainable Development</h5>
                        <p class="text-muted">Eco-friendly construction practices with green spaces and energy-efficient designs</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="achievement-card h-100">
                        <div class="achievement-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5>24/7 Support</h5>
                        <p class="text-muted">Round-the-clock customer support and dedicated relationship managers for all clients</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="achievement-card h-100">
                        <div class="achievement-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h5>Quality Assured</h5>
                        <p class="text-muted">Premium construction quality with modern amenities and contemporary architectural design</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Customer Testimonials</h2>
                <p class="lead text-muted">What our customers say about us</p>
            </div>

            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="testimonial-card">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">RS</span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Rajesh Sharma</h6>
                                <small class="text-muted">Property Owner, Civil Lines</small>
                            </div>
                        </div>
                        <p class="mb-0">"APS Dream Homes delivered our dream home exactly as promised. The quality of construction and attention to detail exceeded our expectations. Highly recommended!"</p>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="testimonial-card">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">PG</span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Priya Gupta</h6>
                                <small class="text-muted">First-time Home Buyer</small>
                            </div>
                        </div>
                        <p class="mb-0">"As a first-time buyer, I was nervous about the process. The team at APS Dream Homes guided me through every step with patience and professionalism."</p>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="testimonial-card">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">AK</span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Amit Kumar</h6>
                                <small class="text-muted">Business Owner</small>
                            </div>
                        </div>
                        <p class="mb-0">"Invested in a commercial property with APS Dream Homes. The location and quality are excellent. Great returns on investment and professional service."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Start Your Property Journey?</h2>
            <p class="lead mb-4">
                Join thousands of satisfied customers who have found their perfect property with APS Dream Homes Pvt Ltd
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="properties.php" class="btn btn-light btn-lg">
                    <i class="fas fa-home me-2"></i>View Our Properties
                </a>
                <a href="contact.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-phone me-2"></i>Get In Touch
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-home me-2"></i>APS Dream Homes Pvt Ltd
                    </h5>
                    <p>Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.</p>
                    <div class="social-links mt-3">
                        <a href="https://facebook.com/apsdreamhomes" class="text-white me-3" target="_blank">
                            <i class="fab fa-facebook-f fa-lg"></i>
                        </a>
                        <a href="https://instagram.com/apsdreamhomes" class="text-white me-3" target="_blank">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="https://linkedin.com/company/aps-dream-homes-pvt-ltd" class="text-white me-3" target="_blank">
                            <i class="fab fa-linkedin-in fa-lg"></i>
                        </a>
                        <a href="https://youtube.com/apsdreamhomes" class="text-white" target="_blank">
                            <i class="fab fa-youtube fa-lg"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white-50">Home</a></li>
                        <li class="mb-2"><a href="properties.php" class="text-white-50">Properties</a></li>
                        <li class="mb-2"><a href="about.php" class="text-white-50">About Us</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white-50">Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="mb-3">Contact Info</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            123, Kunraghat Main Road<br>
                            Near Railway Station<br>
                            Gorakhpur, UP - 273008
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone-alt me-2"></i>
                            <a href="tel:+919554000001" class="text-white-50">+91-9554000001</a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:info@apsdreamhomes.com" class="text-white-50">info@apsdreamhomes.com</a>
                        </li>
                        <li>
                            <i class="fas fa-clock me-2"></i>
                            Mon-Sat: 9:30 AM - 7:00 PM<br>
                            Sun: 10:00 AM - 5:00 PM
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="mb-3">Newsletter</h5>
                    <p class="text-white-50">Subscribe for latest property updates and exclusive deals</p>
                    <form class="mb-3" id="newsletterForm">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your Email" required id="newsletterEmail">
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="my-4 bg-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-white-50">
                        &copy; 2025 APS Dream Homes Pvt Ltd. All rights reserved.<br>
                        <small>Registration No: U70109UP2022PTC163047</small>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white-50 me-3">Privacy Policy</a>
                    <a href="#" class="text-white-50">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Newsletter subscription
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('newsletterEmail').value;
            if (email) {
                alert('Thank you for subscribing to our newsletter!');
                this.reset();
            }
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
    </script>
</body>
</html>
