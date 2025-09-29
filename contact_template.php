<?php
/**
 * Contact Page - APS Dream Homes Pvt Ltd - Enhanced Version
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - APS Dream Homes Pvt Ltd - Real Estate Services in Gorakhpur</title>
    <meta name="description" content="Contact APS Dream Homes Pvt Ltd for all your real estate needs in Gorakhpur. Call +91-9554000001, visit our office, or fill out our contact form. Professional real estate services with 8+ years of experience.">

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

        .contact-method {
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            color: white;
        }

        .contact-method:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .phone-contact {
            background: linear-gradient(135deg, #28a745, #20c997);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .phone-contact:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .email-contact {
            background: linear-gradient(135deg, #007bff, #6610f2);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .email-contact:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .address-contact {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .address-contact:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        /* Ripple effect */
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }
        
        /* Social Media Icons */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        /* Form Validation */
        .form-control.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .form-control.is-valid {
            border-color: #198754;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        /* Loading Spinner */
        .spinner-border {
            display: none;
            width: 1.5rem;
            height: 1.5rem;
            margin-right: 0.5rem;
            vertical-align: text-bottom;
            border: 0.25em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
        }
        
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }

        .contact-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 2rem;
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        /* Social Media Buttons */
        .btn-instagram {
            background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D);
            color: white;
            border: none;
        }
        
        .btn-linkedin {
            background-color: #0077B5;
            color: white;
            border: none;
        }
        
        .btn-instagram:hover, .btn-linkedin:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 15px 35px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid #667eea;
        }

        .service-areas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .service-area-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .service-area-card:hover {
            transform: translateY(-5px);
        }

        .faq-item {
            background: white;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .accordion-button {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
        }

        .accordion-button:not(.collapsed) {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        .map-placeholder {
            background: #e9ecef;
            border-radius: 15px;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 1.1rem;
        }

        .contact-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }

        .contact-card:hover {
            transform: translateY(-5px);
        }

        .stats-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 60px 0;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: transform 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-left: 5px solid #ffc107;
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
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
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
                    <h1 class="section-title">Get In Touch</h1>
                    <p class="lead mb-4">
                        Have questions about a property or need expert advice?<br>
                        We're here to help you every step of the way.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Contact Methods -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up">
                    <div class="contact-method phone-contact">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h4>Call Us Directly</h4>
                        <h3><a href="tel:<?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>" class="text-white text-decoration-none">
                            <?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>
                        </a></h3>
                        <p class="mb-0">Our phone lines are open during business hours. For urgent matters, call us anytime.</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="contact-method email-contact">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email Us</h4>
                        <h5><a href="mailto:<?php echo $settings['company_email'] ?? 'info@apsdreamhomes.com'; ?>" class="text-white text-decoration-none">
                            <?php echo $settings['company_email'] ?? 'info@apsdreamhomes.com'; ?>
                        </a></h5>
                        <p class="mb-0">Send us an email and we'll get back to you within 24 hours.</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="contact-method address-contact">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Visit Our Office</h4>
                        <p class="mb-0">
                            <?php echo $settings['company_address'] ?? '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form & Information -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card contact-card">
                        <div class="card-body p-5">
                            <h3 class="mb-4">Send Us a Message</h3>
                            <p class="text-muted mb-4">Fill out the form below and we'll get back to you within 24 hours</p>

                            <!-- Google Sign-In Button -->
                            <div class="mb-4 text-center">
                                <p class="mb-2">Sign in with Google to track your request status</p>
                                <div id="g_id_onload"
                                    data-client_id="YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com"
                                    data-login_uri="process_google_auth.php"
                                    data-auto_prompt="false">
                                </div>
                                <div class="g_id_signin"
                                    data-type="standard"
                                    data-size="large"
                                    data-theme="outline"
                                    data-text="sign_in_with"
                                    data-shape="rectangular"
                                    data-logo_alignment="left">
                                </div>
                                <div id="user-info" class="mt-3 d-none">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <img id="user-pic" class="rounded-circle me-2" width="32" height="32" src="" alt="Profile">
                                        <span id="user-name"></span>
                                    </div>
                                    <input type="hidden" id="google_id" name="google_id">
                                    <input type="hidden" id="user_email" name="user_email">
                                </div>
                            </div>

                            <form id="contactForm" method="POST" action="process_contact.php" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required placeholder="Enter your full name">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required placeholder="your.email@example.com">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label fw-bold">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Your contact number">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="contact_type" class="form-label fw-bold">Contact Type</label>
                                        <select class="form-select" id="contact_type" name="contact_type">
                                            <option value="General Inquiry">General Inquiry</option>
                                            <option value="Property Inquiry">Property Inquiry</option>
                                            <option value="Investment Consultation">Investment Consultation</option>
                                            <option value="Site Visit Request">Site Visit Request</option>
                                            <option value="Technical Support">Technical Support</option>
                                            <option value="Feedback">Feedback</option>
                                            <option value="Partnership">Partnership</option>
                                            <option value="Legal Documentation">Legal Documentation</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="budget" class="form-label fw-bold">Budget Range</label>
                                        <select class="form-select" id="budget" name="budget">
                                            <option value="">Select Budget Range</option>
                                            <option value="Under 25L">Under ₹25 Lakh</option>
                                            <option value="25L-50L">₹25L - ₹50L</option>
                                            <option value="50L-1Cr">₹50L - ₹1Cr</option>
                                            <option value="1Cr-2Cr">₹1Cr - ₹2Cr</option>
                                            <option value="Above 2Cr">Above ₹2Cr</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="property_type" class="form-label fw-bold">Property Type Interest</label>
                                        <select class="form-select" id="property_type" name="property_type">
                                            <option value="">Select Property Type</option>
                                            <option value="Apartment">Apartment</option>
                                            <option value="Villa">Villa</option>
                                            <option value="Plot">Plot</option>
                                            <option value="Commercial">Commercial</option>
                                            <option value="House">Independent House</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="subject" name="subject" required placeholder="Brief description of your inquiry">
                                </div>

                                <div class="mb-4">
                                    <label for="message" class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required placeholder="Please provide detailed information about your requirements, questions, or feedback. Our team will get back to you within 24 hours."></textarea>
                                </div>

                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        Subscribe to our newsletter for latest property updates and exclusive deals
                                    </label>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
                                        <i class="fas fa-paper-plane me-2"></i>
                                        <span id="submitText">Send Message</span>
                                    </button>
                                </div>
                                <div id="formSuccess" class="alert alert-success mt-3 d-none">
                                    <i class="fas fa-check-circle me-2"></i> Your message has been sent successfully!
                                </div>
                                <div id="formError" class="alert alert-danger mt-3 d-none">
                                    <i class="fas fa-exclamation-circle me-2"></i> There was an error sending your message. Please try again.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="info-card">
                        <h4 class="mb-4">Contact Information</h4>
                        <p class="mb-4">Multiple ways to reach us</p>

                        <div class="mb-4">
                            <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i>Visit Our Office</h6>
                            <p class="mb-0">
                                <?php echo $settings['company_address'] ?? '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008'; ?>
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6><i class="fas fa-clock me-2 text-primary"></i>Business Hours</h6>
                            <p class="mb-0">
                                <?php echo $settings['working_hours'] ?? 'Mon-Sat: 9:30 AM - 7:00 PM, Sun: 10:00 AM - 5:00 PM'; ?>
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6><i class="fas fa-phone me-2 text-primary"></i>Call Us</h6>
                            <p class="mb-0">
                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $settings['company_phone'] ?? '+91-9554000001'); ?>" class="text-decoration-none fw-bold d-inline-flex align-items-center">
                                    <i class="fas fa-phone-alt me-2"></i>
                                    <?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>
                                </a>
                            </p>
                            <div class="mt-2">
                                <a href="https://wa.me/919554000001" class="btn btn-sm btn-outline-success me-2" target="_blank">
                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                </a>
                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $settings['company_phone'] ?? '+91-9554000001'); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-phone me-1"></i> Call Now
                                </a>
                            </div>
                            <small class="text-muted d-block mt-2">Available during business hours</small>
                        </div>

                        <div class="mb-4">
                            <h6><i class="fas fa-envelope me-2 text-primary"></i>Email Us</h6>
                            <p class="mb-0">
                                <a href="mailto:<?php echo htmlspecialchars($settings['company_email'] ?? 'info@apsdreamhomes.com'); ?>" class="text-decoration-none d-inline-flex align-items-center">
                                    <i class="fas fa-envelope me-2"></i>
                                    <?php echo htmlspecialchars($settings['company_email'] ?? 'info@apsdreamhomes.com'); ?>
                                </a>
                            </p>
                            <div class="mt-2">
                                <a href="mailto:<?php echo htmlspecialchars($settings['company_email'] ?? 'info@apsdreamhomes.com'); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Email
                                </a>
                            </div>
                            <small class="text-muted d-block mt-2">Response within 24 hours</small>
                        </div>

                        <div class="mb-4">
                            <h6><i class="fab fa-whatsapp me-2 text-success"></i>Connect With Us</h6>
                            <div class="social-links d-flex gap-2">
                                <a href="https://wa.me/919554000001" class="btn btn-sm btn-success" target="_blank" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <a href="https://www.facebook.com/apsdreamhomes" class="btn btn-sm btn-primary" target="_blank" title="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://www.instagram.com/apsdreamhomes" class="btn btn-sm btn-instagram" target="_blank" title="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="https://www.linkedin.com/company/aps-dream-homes" class="btn btn-sm btn-linkedin" target="_blank" title="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <a href="https://www.youtube.com/apsdreamhomes" class="btn btn-sm btn-danger" target="_blank" title="YouTube">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            </div>
                            <small class="text-muted d-block mt-2">Follow us for latest updates</small>
                        </div>
                    </div>

                    <div class="card contact-card">
                        <div class="card-body p-4">
                            <h5 class="mb-3">We'd Love to Hear From You</h5>
                            <p class="text-muted mb-0">Send us a message and we'll respond as soon as possible.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Areas -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Service Areas</h2>
                <p class="lead text-muted">We serve clients across multiple cities and regions</p>
            </div>

            <div class="service-areas">
                <div class="service-area-card">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-building"></i>
                    </div>
                    <h6 class="text-primary mb-2">Main Office</h6>
                    <h5>Gorakhpur</h5>
                    <p class="mb-0 text-muted">Our headquarters and primary service area</p>
                </div>

                <div class="service-area-card">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-building"></i>
                    </div>
                    <h6 class="text-primary mb-2">Branch Office</h6>
                    <h5>Lucknow</h5>
                    <p class="mb-0 text-muted">Regional office for central UP operations</p>
                </div>

                <div class="service-area-card">
                    <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h6 class="text-primary mb-2">Service Area</h6>
                    <h5>Varanasi</h5>
                    <p class="mb-0 text-muted">Eastern UP service coverage</p>
                </div>

                <div class="service-area-card">
                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h6 class="text-primary mb-2">Service Area</h6>
                    <h5>Allahabad</h5>
                    <p class="mb-0 text-muted">Central UP service area</p>
                </div>

                <div class="service-area-card">
                    <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </div>
                    <h6 class="text-primary mb-2">Extended Service</h6>
                    <h5>Other Cities</h5>
                    <p class="mb-0 text-muted">Available on request</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Map Placeholder -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Find Us</h2>
                <p class="lead text-muted">Visit our office or explore our service areas</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="map-placeholder">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt fa-3x mb-3 text-primary"></i>
                            <h4>Interactive Map</h4>
                            <p class="mb-2">Our office location and service areas</p>
                            <small class="text-muted">Google Maps integration coming soon</small>
                            <div class="mt-3">
                                <a href="https://maps.google.com/?q=123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fas fa-directions me-1"></i>View on Google Maps
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Frequently Asked Questions</h2>
                <p class="lead text-muted">Quick answers to common questions</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item">
                        <h5 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How quickly can I expect a response?
                            </button>
                        </h5>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We typically respond to all inquiries within 24 hours during business days. For urgent matters, please call us directly at <?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>. Emergency support is available 24/7.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <h5 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do you charge any fees for consultations?
                            </button>
                        </h5>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Initial consultations and property viewings are completely free. We only charge fees when you decide to proceed with a purchase or rental agreement. Our consultation services include market analysis, property evaluation, and investment guidance.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <h5 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can you help with property financing?
                            </button>
                        </h5>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we work with multiple banks and financial institutions to help you secure the best financing options for your property purchase. Our team can guide you through the entire loan process, documentation requirements, and help you get pre-approved for loans.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <h5 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What documents do I need to buy a property?
                            </button>
                        </h5>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You'll need ID proof (Aadhaar/PAN), address proof, income proof (salary slips/IT returns), bank statements (last 6 months), and passport-sized photographs. Our legal team will guide you through the complete documentation process and ensure everything is in order.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <h5 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Do you provide after-sales support?
                            </button>
                        </h5>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we provide comprehensive after-sales support including property management services, maintenance coordination, legal assistance for registration, and ongoing customer support. Our relationship with customers continues long after the sale is completed.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <h5 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                Can I schedule a site visit?
                            </button>
                        </h5>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolutely! You can schedule a site visit by calling us, sending an email, or filling out the contact form above. We'll arrange a convenient time for you to visit our properties and have our experts show you around and answer all your questions.
                            </div>
                        </div>
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
                    <p>Professional real estate services with registered company status and comprehensive property solutions.</p>
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
        // Google Sign-In Callback
        function handleCredentialResponse(response) {
            // Decode the JWT token
            const responsePayload = JSON.parse(atob(response.credential.split('.')[1]));
            
            // Update UI with user info
            document.getElementById('user-info').classList.remove('d-none');
            document.getElementById('user-pic').src = responsePayload.picture;
            document.getElementById('user-name').textContent = responsePayload.name;
            document.getElementById('google_id').value = responsePayload.sub;
            document.getElementById('email').value = responsePayload.email;
            document.getElementById('email').readOnly = true;
            
            // Show check status button
            document.getElementById('check-status-btn').style.display = 'block';
            
            // Check if user has any previous inquiries
            checkInquiryStatus(responsePayload.email);
        }
        
        // Check inquiry status for the user
        function checkInquiryStatus(email) {
            fetch(`check_inquiry_status.php?email=${encodeURIComponent(email)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.hasInquiries) {
                        const statusBtn = document.getElementById('check-status-btn');
                        statusBtn.classList.add('btn-info');
                        statusBtn.innerHTML = `<i class="fas fa-search me-1"></i> View Status (${data.pendingCount} Pending)`;
                        statusBtn.onclick = () => {
                            // Show inquiry status in a modal or redirect to status page
                            showInquiryStatus(data.inquiries);
                        };
                    }
                });
        }
        
        // Show inquiry status in a modal
        function showInquiryStatus(inquiries) {
            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="inquiryStatusModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Your Inquiry Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Last Update</th>
                                            </tr>
                                        </thead>
                                        <tbody id="inquiry-status-body">
                                            ${inquiries.map(inquiry => `
                                                <tr>
                                                    <td>${new Date(inquiry.created_at).toLocaleDateString()}</td>
                                                    <td>${inquiry.subject}</td>
                                                    <td><span class="badge bg-${getStatusBadgeClass(inquiry.status)}">${inquiry.status}</span></td>
                                                    <td>${new Date(inquiry.updated_at).toLocaleString()}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to the page
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('inquiryStatusModal'));
            modal.show();
            
            // Clean up when modal is closed
            document.getElementById('inquiryStatusModal').addEventListener('hidden.bs.modal', function () {
                this.remove();
            });
        }
        
        // Helper function to get badge class based on status
        function getStatusBadgeClass(status) {
            const statusMap = {
                'Pending': 'warning',
                'In Progress': 'info',
                'Resolved': 'success',
                'Closed': 'secondary'
            };
            return statusMap[status] || 'secondary';
        }

        // Google Maps Integration
        function initMap() {
            const mapElement = document.getElementById('map');
            if (!mapElement) return;
            
            // Default to Gorakhpur coordinates
            const gorakhpur = { lat: 26.7606, lng: 83.3732 };
            
            // Create map
            const map = new google.maps.Map(mapElement, {
                zoom: 15,
                center: gorakhpur,
                styles: [
                    {
                        featureType: 'poi',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    },
                    {
                        featureType: 'transit',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    }
                ]
            });
            
            // Add marker
            new google.maps.Marker({
                position: gorakhpur,
                map: map,
                title: 'APS Dream Homes',
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                    scaledSize: new google.maps.Size(40, 40)
                }
            });
        }
        
        // Load Google Maps API
        function loadGoogleMaps() {
            // Only load if not already loaded
            if (!document.querySelector('script[src*="maps.googleapis.com"]')) {
                const script = document.createElement('script');
                script.src = `https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap`;
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
            }
        }

        // Initialize everything when the DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS animations
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true
            });
            
            // Load Google Maps
            loadGoogleMaps();
            
            // Get form elements
            const form = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitSpinner = document.getElementById('submitSpinner');
            const submitText = document.getElementById('submitText');
            const formSuccess = document.getElementById('formSuccess');
            const formError = document.getElementById('formError');
            
            // Add ripple effect and click handlers to contact cards
            document.querySelectorAll('.contact-method').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple');
                    
                    // Get click position
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    // Position ripple
                    ripple.style.width = ripple.style.height = `${size}px`;
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    // Add to card
                    this.appendChild(ripple);
                    
                    // Remove ripple after animation
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                    
                    // Handle click action based on card type
                    if (this.classList.contains('phone-contact')) {
                        window.location.href = 'tel:<?php echo preg_replace('/[^0-9+]/', '', $settings['company_phone'] ?? '+91-9554000001'); ?>';
                    } else if (this.classList.contains('email-contact')) {
                        window.location.href = 'mailto:<?php echo htmlspecialchars($settings['company_email'] ?? 'info@apsdreamhomes.com'); ?>';
                    } else if (this.classList.contains('address-contact')) {
                        window.open('https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($settings['company_address'] ?? '123 Dream Street, Gorakhpur, Uttar Pradesh 273001'); ?>', '_blank');
                    }
                });
            });
            
            // Form submission handling
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Basic validation
                    if (!form.checkValidity()) {
                        e.stopPropagation();
                        form.classList.add('was-validated');
                        return;
                    }
                    
                    // Show loading state
                    submitBtn.disabled = true;
                    submitSpinner.classList.remove('d-none');
                    submitText.textContent = 'Sending...';
                    
                    // Prepare form data
                    const formData = new FormData(form);
                    
                    // Send AJAX request
                    fetch('process_contact.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            formSuccess.textContent = data.message || 'Thank you for your message! We will get back to you soon.';
                            formSuccess.classList.remove('d-none');
                            formError.classList.add('d-none');
                            
                            // Reset form
                            form.reset();
                            form.classList.remove('was-validated');
                            
                            // Hide success message after 5 seconds
                            setTimeout(() => {
                                formSuccess.classList.add('d-none');
                            }, 5000);
                        } else {
                            // Show error message
                            formError.textContent = data.message || 'An error occurred while sending your message. Please try again.';
                            formError.classList.remove('d-none');
                            
                            // If there are field-specific errors, show them
                            if (data.errors) {
                                Object.entries(data.errors).forEach(([field, message]) => {
                                    const input = form.querySelector(`[name="${field}"]`);
                                    if (input) {
                                        const feedback = input.nextElementSibling;
                                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                                            feedback.textContent = message;
                                            input.classList.add('is-invalid');
                                        }
                                    }
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        formError.textContent = 'An error occurred while sending your message. Please try again later.';
                        formError.classList.remove('d-none');
                    })
                    .finally(() => {
                        // Reset button state
                        submitBtn.disabled = false;
                        submitSpinner.classList.add('d-none');
                        submitText.textContent = 'Send Message';
                        
                        // Scroll to top of form to show messages
                        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                });
            }

            // Email validation function
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Toast notification function
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 5000);
            }

            // Newsletter subscription
            const newsletterForm = document.getElementById('newsletterForm');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = document.getElementById('newsletterEmail')?.value;
                    if (email) {
                        showToast('Thank you for subscribing to our newsletter!', 'success');
                        this.reset();
                    }
                });
            }

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

            // Phone number click tracking
            document.querySelectorAll('a[href^="tel:"]').forEach(link => {
                link.addEventListener('click', function() {
                    // Track phone call analytics here
                    console.log('Phone call initiated:', this.href);
                });
            });

            // Email link tracking
            document.querySelectorAll('a[href^="mailto:"]').forEach(link => {
                link.addEventListener('click', function() {
                    // Track email click analytics here
                    console.log('Email initiated:', this.href);
                });
            });
        });
    </script>
</body>
</html>
