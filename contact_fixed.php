<?php
/**
 * Contact Page - APS Dream Homes Pvt Ltd
 * Complete contact form with professional information
 */

// Include database connection
require_once 'includes/db_connection.php';

try {
    $conn = getDbConnection();

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
    <title>Contact Us - APS Dream Homes Pvt Ltd</title>
    <meta name="description" content="Get in touch with APS Dream Homes Pvt Ltd for all your real estate needs. Call +91-9554000001 or visit our office in Kunraghat, Gorakhpur.">

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

        .contact-icon {
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

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px);
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

        .contact-method {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .contact-method:hover {
            transform: translateY(-2px);
        }

        .phone-contact {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .email-contact {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
        }

        .address-contact {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
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
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
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
                    <h1 class="section-title">Get In Touch</h1>
                    <p class="lead mb-4">
                        Have questions about a property or need expert advice? We're here to help you every step of the way.
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
                    <div class="contact-method phone-contact text-center">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h4>Call Us</h4>
                        <h3><a href="tel:<?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>" class="text-white text-decoration-none">
                            <?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>
                        </a></h3>
                        <p class="mb-0">Our phone lines are open during business hours. For urgent matters, call us anytime.</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="contact-method email-contact text-center">
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
                    <div class="contact-method address-contact text-center">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Visit Us</h4>
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
                            <p class="text-muted mb-4">Fill out the form below and we'll get back to you shortly</p>

                            <form id="contactForm" method="POST" action="process_contact.php">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label fw-bold">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="contact_type" class="form-label fw-bold">Contact Type</label>
                                        <select class="form-select" id="contact_type" name="contact_type">
                                            <option value="General Inquiry">General Inquiry</option>
                                            <option value="Property Inquiry">Property Inquiry</option>
                                            <option value="Technical Support">Technical Support</option>
                                            <option value="Feedback">Feedback</option>
                                            <option value="Partnership">Partnership</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>

                                <div class="mb-4">
                                    <label for="message" class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required placeholder="Tell us about your requirements, questions, or feedback..."></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
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
                                <a href="tel:<?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>" class="text-decoration-none">
                                    <?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>
                                </a>
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6><i class="fas fa-envelope me-2 text-primary"></i>Email Us</h6>
                            <p class="mb-0">
                                <a href="mailto:<?php echo $settings['company_email'] ?? 'info@apsdreamhomes.com'; ?>" class="text-decoration-none">
                                    <?php echo $settings['company_email'] ?? 'info@apsdreamhomes.com'; ?>
                                </a>
                            </p>
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
                <h2>Service Areas</h2>
                <p class="lead text-muted">We serve clients across multiple cities and regions</p>
            </div>

            <div class="service-areas">
                <div class="service-area-card">
                    <h6 class="text-primary mb-2">🏢 Main Office</h6>
                    <h5>Gorakhpur</h5>
                    <p class="mb-0 text-muted">Our headquarters and primary service area</p>
                </div>

                <div class="service-area-card">
                    <h6 class="text-primary mb-2">🏢 Branch Office</h6>
                    <h5>Lucknow</h5>
                    <p class="mb-0 text-muted">Regional office for central UP operations</p>
                </div>

                <div class="service-area-card">
                    <h6 class="text-primary mb-2">🏢 Service Area</h6>
                    <h5>Varanasi</h5>
                    <p class="mb-0 text-muted">Eastern UP service coverage</p>
                </div>

                <div class="service-area-card">
                    <h6 class="text-primary mb-2">🏢 Service Area</h6>
                    <h5>Allahabad</h5>
                    <p class="mb-0 text-muted">Central UP service area</p>
                </div>

                <div class="service-area-card">
                    <h6 class="text-primary mb-2">🏢 Extended Service</h6>
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
                            <p class="mb-0">Our office location and service areas</p>
                            <small class="text-muted">Google Maps integration coming soon</small>
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
                                We typically respond to all inquiries within 24 hours during business days. For urgent matters, please call us directly at <?php echo $settings['company_phone'] ?? '+91-9554000001'; ?>.
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
                                Initial consultations and property viewings are completely free. We only charge fees when you decide to proceed with a purchase or rental agreement.
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
                                Yes, we work with multiple banks and financial institutions to help you secure the best financing options for your property purchase. Our team can guide you through the entire process.
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
                                You'll need ID proof, address proof, income proof, and bank statements. Our legal team will guide you through the complete documentation process and ensure everything is in order.
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

        // Contact form validation and submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();

            // Validation
            if (!name || !email || !subject || !message) {
                showToast('Please fill in all required fields.', 'danger');
                return;
            }

            if (!isValidEmail(email)) {
                showToast('Please enter a valid email address.', 'danger');
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            submitBtn.disabled = true;

            // Simulate form submission (replace with actual processing)
            setTimeout(() => {
                showToast('Thank you for your message! We\'ll get back to you within 24 hours.', 'success');
                this.reset();
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });

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
        document.querySelector('footer form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email) {
                showToast('Thank you for subscribing to our newsletter!', 'success');
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
