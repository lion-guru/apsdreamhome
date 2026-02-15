<?php
/**
 * Legal Services Page - APS Dream Homes
 * Professional legal services for real estate
 */

require_once 'core/functions.php';
require_once 'includes/db_connection.php';

try {
    $pdo = getMysqliConnection();

    // Get legal services
    $servicesQuery = "SELECT * FROM legal_services WHERE status = 'active' ORDER BY display_order ASC";
    $servicesStmt = $pdo->query($servicesQuery);
    $services = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get legal team members
    $lawyersQuery = "SELECT * FROM team_members WHERE department = 'legal' AND status = 'active' ORDER BY display_order ASC LIMIT 4";
    $lawyersStmt = $pdo->query($lawyersQuery);
    $lawyers = $lawyersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get legal services FAQs
    $faqsQuery = "SELECT * FROM faqs WHERE category = 'legal_services' AND status = 'active' ORDER BY display_order ASC LIMIT 6";
    $faqsStmt = $pdo->query($faqsQuery);
    $faqs = $faqsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Legal services page database error: ' . $e->getMessage());
    $services = [];
    $lawyers = [];
    $faqs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Include site settings
    require_once 'includes/site_settings.php';
    ?>
    <title><?php echo getSiteSetting('site_title', 'APS Dream Homes'); ?> - Legal Services</title>
    <meta name="description" content="Expert legal services for real estate transactions, property law, contracts, and more. Get professional legal assistance for your real estate needs.">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .legal-hero {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            text-align: center;
            border-left: 5px solid #2c3e50;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 2rem;
        }

        .service-features {
            list-style: none;
            padding: 0;
            margin: 20px 0 0;
        }

        .service-features li {
            margin-bottom: 10px;
            color: #666;
        }

        .service-features li i {
            color: #28a745;
            margin-right: 10px;
        }

        .team-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .team-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .team-image {
            height: 250px;
            overflow: hidden;
        }

        .team-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .team-card:hover .team-image img {
            transform: scale(1.05);
        }

        .team-info {
            padding: 25px;
            text-align: center;
        }

        .designation {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .specialization {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-links a:nth-child(1) {
            background: #0077b5;
        }

        .social-links a:nth-child(2) {
            background: #dc3545;
        }

        .social-links a:hover {
            transform: scale(1.1);
        }

        .process-timeline {
            position: relative;
            padding: 40px 0;
        }

        .process-timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #2c3e50;
            transform: translateX(-50%);
        }

        .process-step {
            position: relative;
            margin-bottom: 60px;
            text-align: center;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        .process-step .step-number {
            width: 60px;
            height: 60px;
            background: #2c3e50;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 20px;
            position: relative;
            z-index: 2;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(45deg, #2c3e50, #34495e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .accordion-button {
            font-weight: 600;
        }

        .accordion-button:not(.collapsed) {
            background-color: #2c3e50;
            color: white;
        }

        .accordion-button:focus {
            border-color: #2c3e50;
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }

        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }

        .cta-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(45deg, #2c3e50, #34495e);
            border: none;
            padding: 15px 35px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #34495e, #2c3e50);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .btn-outline-primary {
            border-color: #2c3e50;
            color: #2c3e50;
        }

        .btn-outline-primary:hover {
            background-color: #2c3e50;
            border-color: #2c3e50;
        }

        .breadcrumb {
            background: #f8f9fa;
            border-radius: 0;
        }

        .empty-state {
            padding: 60px 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="legal-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Expert Legal Services</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Professional legal assistance for all your real estate needs with experienced attorneys specializing in property law.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap" data-aos="fade-up" data-aos-delay="200">
                        <a href="#contact-form" class="btn btn-light btn-lg">
                            <i class="fas fa-gavel me-2"></i>Get Legal Help
                        </a>
                        <a href="#services" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-list me-2"></i>Our Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <nav class="bg-light border-bottom py-2" aria-label="breadcrumb">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="services.php">Services</a></li>
                <li class="breadcrumb-item active" aria-current="page">Legal Services</li>
            </ol>
        </div>
    </nav>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Our Legal Services</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Comprehensive legal solutions for real estate matters with expert guidance
                    </p>
                </div>
            </div>

            <?php if (!empty($services)): ?>
            <div class="row g-4">
                <?php foreach ($services as $service): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="<?php echo htmlspecialchars($service['icon'] ?? 'fas fa-gavel'); ?>"></i>
                        </div>
                        <h3 class="h4 fw-bold mb-3"><?php echo htmlspecialchars($service['title'] ?? 'Legal Service'); ?></h3>
                        <p class="text-muted mb-4"><?php echo htmlspecialchars($service['description'] ?? 'Professional legal assistance for your real estate needs.'); ?></p>

                        <?php if (!empty($service['features'])): ?>
                        <?php $features = json_decode($service['features'], true); ?>
                        <?php if (is_array($features) && !empty($features)): ?>
                        <ul class="service-features">
                            <?php foreach ($features as $feature): ?>
                            <li>
                                <i class="fas fa-check text-success"></i>
                                <?php echo htmlspecialchars($feature); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-gavel fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">Legal Services Coming Soon</h3>
                    <p class="text-muted mb-4">
                        We're currently expanding our legal services. Please contact us for immediate legal assistance.
                    </p>
                    <a href="contact.php" class="btn btn-primary">Contact Us</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Why Choose Our Legal Services</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Expert legal assistance you can trust for all your real estate matters
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Expert Legal Team</h5>
                        <p class="text-muted">Experienced attorneys specializing in real estate law with proven track records</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Protected Interests</h5>
                        <p class="text-muted">We safeguard your rights and interests throughout every legal process</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Timely Solutions</h5>
                        <p class="text-muted">Efficient legal services with quick turnaround times and responsive communication</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Transparent Process</h5>
                        <p class="text-muted">Clear communication and transparent pricing with no hidden fees</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Comprehensive Documentation</h5>
                        <p class="text-muted">Thorough legal documentation and contract preparation services</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Personalized Service</h5>
                        <p class="text-muted">Tailored legal solutions based on your specific needs and circumstances</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Legal Team Section -->
    <?php if (!empty($lawyers)): ?>
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Meet Our Legal Team</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Expert attorneys dedicated to protecting your real estate interests
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($lawyers as $lawyer): ?>
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="<?php echo htmlspecialchars($lawyer['image_path'] ?? 'assets/images/team-placeholder.jpg'); ?>"
                                 alt="<?php echo htmlspecialchars($lawyer['name']); ?>">
                        </div>
                        <div class="team-info">
                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($lawyer['name']); ?></h5>
                            <p class="designation mb-1"><?php echo htmlspecialchars($lawyer['designation']); ?></p>
                            <?php if (!empty($lawyer['specialization'])): ?>
                            <p class="specialization mb-3"><?php echo htmlspecialchars($lawyer['specialization']); ?></p>
                            <?php endif; ?>
                            <div class="social-links">
                                <?php if (!empty($lawyer['linkedin'])): ?>
                                <a href="<?php echo htmlspecialchars($lawyer['linkedin']); ?>" target="_blank" title="LinkedIn">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($lawyer['email'])): ?>
                                <a href="mailto:<?php echo htmlspecialchars($lawyer['email']); ?>" title="Email">
                                    <i class="fas fa-envelope"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Legal Process Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Our Legal Process</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Simple and transparent legal assistance from consultation to resolution
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="process-timeline">
                        <div class="process-step" data-aos="fade-right">
                            <div class="step-number">1</div>
                            <h4 class="fw-bold mb-3">Initial Consultation</h4>
                            <p class="text-muted">Discuss your legal needs and requirements in detail</p>
                        </div>

                        <div class="process-step" data-aos="fade-left" data-aos-delay="100">
                            <div class="step-number">2</div>
                            <h4 class="fw-bold mb-3">Case Evaluation</h4>
                            <p class="text-muted">Thorough analysis of your legal situation and documentation</p>
                        </div>

                        <div class="process-step" data-aos="fade-right" data-aos-delay="200">
                            <div class="step-number">3</div>
                            <h4 class="fw-bold mb-3">Legal Strategy</h4>
                            <p class="text-muted">Develop a customized legal approach for your specific case</p>
                        </div>

                        <div class="process-step" data-aos="fade-left" data-aos-delay="300">
                            <div class="step-number">4</div>
                            <h4 class="fw-bold mb-3">Implementation</h4>
                            <p class="text-muted">Execute legal solutions effectively and efficiently</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <?php if (!empty($faqs)): ?>
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Frequently Asked Questions</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Find answers to common legal questions about real estate
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <?php foreach ($faqs as $index => $faq): ?>
                        <div class="accordion-item" data-aos="fade-up">
                            <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse<?php echo $index; ?>">
                                    <?php echo htmlspecialchars($faq['question']); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $index; ?>"
                                 class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?php echo htmlspecialchars($faq['answer']); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Contact Form Section -->
    <section id="contact-form" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Get Legal Assistance</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Contact us for professional legal help with your real estate matters
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <form id="legalServicesForm" class="contact-form" data-aos="fade-up">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name *</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Your Email *</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Your Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="serviceType" class="form-label">Service Type *</label>
                                <select class="form-control" id="serviceType" name="serviceType" required>
                                    <option value="">Select Service Type</option>
                                    <option value="contract_review">Contract Review</option>
                                    <option value="legal_consultation">Legal Consultation</option>
                                    <option value="property_rights">Property Rights</option>
                                    <option value="dispute_resolution">Dispute Resolution</option>
                                    <option value="documentation">Legal Documentation</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Please describe your legal needs in detail..." required></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="display-5 fw-bold mb-4" data-aos="fade-up">Need Legal Assistance?</h2>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Get expert legal help for your real estate matters today
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap" data-aos="fade-up" data-aos-delay="200">
                        <a href="tel:+919554000001" class="btn btn-light btn-lg">
                            <i class="fas fa-phone me-2"></i>Call Now: +91-9554000001
                        </a>
                        <a href="contact.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-calendar me-2"></i>Schedule Consultation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/templates/footer.php'; ?>

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

        // Form submission
        document.getElementById('legalServicesForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(this);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }

            // In a real application, this would submit to a server
            alert('Thank you for your inquiry! We will contact you soon with legal assistance.');

            // Reset form
            this.reset();
        });

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0, 3) + '-' + value.slice(3);
                } else {
                    value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                }
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
?>

<!-- Hero Section -->
<section class="hero-section" style="background-image: url('<?php echo htmlspecialchars($pageImage); ?>');">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 data-aos="fade-up">Expert Legal Services</h1>
                <p data-aos="fade-up" data-aos-delay="100">Professional legal assistance for all your real estate needs</p>
                <div class="hero-buttons" data-aos="fade-up" data-aos-delay="200">
                    <a href="#contact-form" class="btn btn-primary">Get Legal Help</a>
                    <a href="#services" class="btn btn-outline-light">Our Services</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumbs -->
<div class="breadcrumbs bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li class="list-inline-item"><span>/</span></li>
                    <li class="list-inline-item">Legal Services</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Services Section -->
<section id="services" class="services-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 data-aos="fade-up">Our Legal Services</h2>
                <p data-aos="fade-up" data-aos-delay="100">Comprehensive legal solutions for real estate matters</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($services as $service): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card" data-aos="fade-up">
                    <div class="service-icon">
                        <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                    <ul class="service-features">
                        <?php
                        $features = json_decode($service['features'], true);
                        foreach ($features as $feature):
                        ?>
                        <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-choose-section bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 data-aos="fade-up">Why Choose Our Legal Services</h2>
                <p data-aos="fade-up" data-aos-delay="100">Expert legal assistance you can trust</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h3>Expert Legal Team</h3>
                    <p>Experienced attorneys specializing in real estate law</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Protected Interests</h3>
                    <p>We safeguard your rights and interests throughout the process</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Timely Solutions</h3>
                    <p>Efficient legal services with quick turnaround times</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Legal Team Section -->
<section class="team-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 data-aos="fade-up">Meet Our Legal Team</h2>
                <p data-aos="fade-up" data-aos-delay="100">Expert attorneys at your service</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($lawyers as $lawyer): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-card" data-aos="fade-up">
                    <div class="team-image">
                        <img src="<?php echo htmlspecialchars($lawyer['photo']); ?>" alt="<?php echo htmlspecialchars($lawyer['name']); ?>" class="img-fluid">
                    </div>
                    <div class="team-info">
                        <h3><?php echo htmlspecialchars($lawyer['name']); ?></h3>
                        <p class="designation"><?php echo htmlspecialchars($lawyer['designation']); ?></p>
                        <p class="specialization"><?php echo htmlspecialchars($lawyer['specialization']); ?></p>
                        <div class="social-links">
                            <?php if ($lawyer['linkedin']): ?>
                            <a href="<?php echo htmlspecialchars($lawyer['linkedin']); ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                            <?php endif; ?>
                            <?php if ($lawyer['email']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($lawyer['email']); ?>"><i class="fas fa-envelope"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="process-section bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 data-aos="fade-up">Our Legal Process</h2>
                <p data-aos="fade-up" data-aos-delay="100">Simple and transparent legal assistance</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="process-timeline">
                    <div class="process-step" data-aos="fade-right">
                        <div class="step-number">1</div>
                        <h3>Initial Consultation</h3>
                        <p>Discuss your legal needs and requirements</p>
                    </div>
                    
                    <div class="process-step" data-aos="fade-right" data-aos-delay="100">
                        <div class="step-number">2</div>
                        <h3>Case Evaluation</h3>
                        <p>Thorough analysis of your legal situation</p>
                    </div>
                    
                    <div class="process-step" data-aos="fade-right" data-aos-delay="200">
                        <div class="step-number">3</div>
                        <h3>Legal Strategy</h3>
                        <p>Develop a customized legal approach</p>
                    </div>
                    
                    <div class="process-step" data-aos="fade-right" data-aos-delay="300">
                        <div class="step-number">4</div>
                        <h3>Implementation</h3>
                        <p>Execute legal solutions effectively</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 data-aos="fade-up">Frequently Asked Questions</h2>
                <p data-aos="fade-up" data-aos-delay="100">Find answers to common legal questions</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="faqAccordion">
                    <?php foreach ($faqs as $index => $faq): ?>
                    <div class="accordion-item" data-aos="fade-up">
                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                            <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>">
                                <?php echo htmlspecialchars($faq['question']); ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo htmlspecialchars($faq['answer']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section id="contact-form" class="contact-section bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 data-aos="fade-up">Get Legal Assistance</h2>
                <p data-aos="fade-up" data-aos-delay="100">Contact us for professional legal help</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <form id="legalServicesForm" class="contact-form" data-aos="fade-up">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Your Email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Your Phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <select class="form-control" id="serviceType" name="serviceType" required>
                                <option value="">Select Service Type</option>
                                <option value="contract_review">Contract Review</option>
                                <option value="legal_consultation">Legal Consultation</option>
                                <option value="property_rights">Property Rights</option>
                                <option value="dispute_resolution">Dispute Resolution</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <textarea class="form-control" id="message" name="message" rows="5" placeholder="Your Message" required></textarea>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 data-aos="fade-up">Need Legal Assistance?</h2>
                <p data-aos="fade-up" data-aos-delay="100">Get expert legal help for your real estate matters</p>
                <div class="cta-buttons" data-aos="fade-up" data-aos-delay="200">
                    <a href="tel:<?php echo CONTACT_PHONE; ?>" class="btn btn-primary">Call Now</a>
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-primary">Schedule Consultation</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>