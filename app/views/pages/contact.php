<?php

/**
 * Enhanced Contact Page - APS Dream Home
 * Professional contact page with comprehensive information and multiple contact methods
 */

// Define constant to allow database connection
define('INCLUDED_FROM_MAIN', true);
// Include database connection
require_once 'includes/db_connection.php';

try {
    global $pdo;
    $conn = $pdo;

    // Get company settings from database
    $settings_sql = "SELECT setting_name, setting_value FROM site_settings WHERE setting_name IN ('company_name', 'company_phone', 'company_email', 'company_address', 'working_hours')";
    $settings_stmt = $conn->prepare($settings_sql);
    $settings_stmt->execute();
    $settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    $settings = [];
    $error_message = "Unable to load company information.";
}

// Default contact info
$contact = [
    'phone' => $settings['company_phone'] ?? '+91-1234567890',
    'email' => $settings['company_email'] ?? 'info@apsdreamhome.com',
    'address' => $settings['company_address'] ?? '123 Main Street, Gorakhpur, Uttar Pradesh - 273001',
    'hours' => $settings['working_hours'] ?? 'Mon - Sat: 9:00 AM - 8:00 PM, Sun: 10:00 AM - 6:00 PM'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - APS Dream Home</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Contact APS Dream Home for all your real estate needs. Get in touch with our expert team for property consultations, site visits, and investment opportunities.">
    <meta name="keywords" content="contact APS Dream Home, real estate contact Gorakhpur, property consultation, real estate advisors">
    
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
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 100px 0;
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

        .contact-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            margin: -50px auto 50px;
            position: relative;
            z-index: 10;
            transition: transform 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
        }

        .contact-info-item {
            padding: 20px;
            border-radius: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .contact-info-item:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateX(10px);
        }

        .contact-form {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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

        .map-section {
            padding: 80px 0;
            background: white;
        }

        .map-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            height: 400px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .social-links {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .social-link {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 50px;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            .contact-card {
                margin: -30px 20px 30px;
                padding: 30px 20px;
            }
            
            .contact-form {
                padding: 30px 20px;
            }
            
            .section-title {
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
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-right">Get in Touch</h1>
                    <p class="lead mb-4" data-aos="fade-right" data-aos-delay="100">
                        We're here to help you find your dream property. Contact our expert team for personalized consultations, site visits, and investment opportunities.
                    </p>
                    <div class="d-flex gap-3" data-aos="fade-right" data-aos-delay="200">
                        <div class="text-center">
                            <i class="fas fa-phone fa-2x mb-2"></i>
                            <p class="mb-0">Call Us</p>
                            <p class="fw-bold"><?php echo htmlspecialchars($contact['phone']); ?></p>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-envelope fa-2x mb-2"></i>
                            <p class="mb-0">Email Us</p>
                            <p class="fw-bold"><?php echo htmlspecialchars($contact['email']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="contact-card">
                        <h3 class="mb-4">Quick Contact</h3>
                        <form id="quickContactForm">
                            <div class="mb-3">
                                <input type="text" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" class="form-control" placeholder="Phone Number" required>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" rows="3" placeholder="Quick Message"></textarea>
                            </div>
                            <button type="submit" class="btn btn-gradient w-100">Send Quick Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title" data-aos="fade-up">Contact Information</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                    Multiple ways to reach us for your convenience
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="contact-info-item text-center">
                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                        <h4>Office Address</h4>
                        <p><?php echo htmlspecialchars($contact['address']); ?></p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="contact-info-item text-center">
                        <i class="fas fa-phone-alt fa-3x mb-3"></i>
                        <h4>Phone Number</h4>
                        <p><?php echo htmlspecialchars($contact['phone']); ?></p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="contact-info-item text-center">
                        <i class="fas fa-envelope fa-3x mb-3"></i>
                        <h4>Email Address</h4>
                        <p><?php echo htmlspecialchars($contact['email']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title" data-aos="fade-up">Send Us a Message</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                    Fill out the form below and we'll get back to you soon
                </p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="contact-form" data-aos="fade-up" data-aos-delay="200">
                        <form id="mainContactForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subject</label>
                                    <select class="form-select">
                                        <option>Property Inquiry</option>
                                        <option>Site Visit Request</option>
                                        <option>Investment Consultation</option>
                                        <option>General Information</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message *</label>
                                    <textarea class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-gradient btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title" data-aos="fade-up">Find Us on Map</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                    Visit our office for a personal consultation
                </p>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="map-container" data-aos="fade-up" data-aos-delay="200">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt fa-4x mb-3"></i>
                            <h4>Interactive Map</h4>
                            <p>Map integration coming soon</p>
                            <p class="mb-0"><?php echo htmlspecialchars($contact['address']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Links Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center">
                <h3 class="mb-4" data-aos="fade-up">Follow Us on Social Media</h3>
                <div class="social-links" data-aos="fade-up" data-aos-delay="100">
                    <a href="#" class="social-link">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-whatsapp"></i>
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

        // Form submission handlers
        document.getElementById('quickContactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });

        document.getElementById('mainContactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your detailed message! We will contact you within 24 hours.');
            this.reset();
        });
    </script>
</body>
</html>
