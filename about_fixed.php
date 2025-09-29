<?php
/**
 * About Page - APS Dream Homes Pvt Ltd
 * Professional company information and services
 */

// Include database connection
require_once 'includes/db_connection.php';

try {
    $conn = getDbConnection();

    // Get company settings from database
    $settings_sql = "SELECT setting_name, setting_value FROM site_settings WHERE setting_name IN ('company_name', 'company_description', 'mission_statement', 'vision_statement', 'about_company', 'established_year', 'company_type', 'services_offered')";
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
    <title>About Us - APS Dream Homes Pvt Ltd</title>
    <meta name="description" content="Learn about APS Dream Homes Pvt Ltd, a leading real estate company in Gorakhpur established in 2022. We specialize in property development, real estate consultancy, and investment advisory services.">

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
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .service-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .service-icon {
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

        .team-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
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

        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .company-timeline {
            position: relative;
            padding-left: 30px;
        }

        .company-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(45deg, #667eea, #764ba2);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 30px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 5px;
            width: 12px;
            height: 12px;
            background: #667eea;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #667eea;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
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
                    <a href="customer_login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="customer_registration.php" class="btn btn-primary">Register</a>
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
                    <p class="lead">
                        <?php echo $settings['company_description'] ?? 'Leading real estate company in Gorakhpur, providing comprehensive property solutions with professional expertise and customer-centric approach.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Company Overview -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="mb-4">Our Story</h2>
                    <div class="company-timeline">
                        <div class="timeline-item">
                            <h5>2022 - Foundation</h5>
                            <p>APS Dream Homes Pvt Ltd was established as a registered real estate company under the Companies Act 2013, bringing professional expertise to the property development sector.</p>
                        </div>

                        <div class="timeline-item">
                            <h5>2022-2023 - Growth Phase</h5>
                            <p>Successfully launched multiple residential and commercial projects, establishing ourselves as a trusted name in Gorakhpur's real estate market.</p>
                        </div>

                        <div class="timeline-item">
                            <h5>2024 - Expansion</h5>
                            <p>Expanded our portfolio with premium developments and enhanced our service offerings to provide comprehensive real estate solutions.</p>
                        </div>

                        <div class="timeline-item">
                            <h5>2025 - Innovation</h5>
                            <p>Continuing to innovate with modern construction techniques, sustainable development practices, and customer-centric service approach.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Company Facts</h5>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    <strong>Established:</strong> <?php echo $settings['established_year'] ?? '2022'; ?>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-building text-primary me-2"></i>
                                    <strong>Type:</strong> <?php echo $settings['company_type'] ?? 'Private Limited Company'; ?>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-certificate text-primary me-2"></i>
                                    <strong>Registration:</strong> <?php echo $settings['registration_number'] ?? 'U70109UP2022PTC163047'; ?>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    <strong>Location:</strong> Gorakhpur, UP
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <strong>Team Size:</strong> 25+ Professionals
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-home text-primary me-2"></i>
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

    <!-- Services -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Services</h2>
                <p class="lead text-muted">Comprehensive real estate solutions for all your property needs</p>
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
                <p class="lead text-muted">What sets us apart in the real estate industry</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Registered Company</h5>
                        <p class="text-muted">Licensed and registered under Companies Act 2013 with proper legal compliance</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5>Timely Delivery</h5>
                        <p class="text-muted">Committed to delivering projects on time without compromising quality</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5>Transparent Deals</h5>
                        <p class="text-muted">Complete transparency in all transactions and pricing structures</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5>24/7 Support</h5>
                        <p class="text-muted">Round-the-clock customer support and after-sales service</p>
                    </div>
                </div>
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
                    <p>Established in 2022, APS Dream Homes Pvt Ltd has quickly emerged as a trusted name in the real estate sector of Gorakhpur. As a registered company under the Companies Act 2013, we bring professional expertise and corporate governance to the property development industry.</p>
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
                    <form class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your Email" required>
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
        document.querySelector('footer form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
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
