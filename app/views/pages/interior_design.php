<?php
/**
 * Interior Design Services Page - APS Dream Homes
 * Professional interior design services for real estate
 */

require_once 'core/functions.php';
require_once 'includes/db_connection.php';

try {
    $pdo = getDbConnection();

    // Get interior design services
    $servicesQuery = "SELECT * FROM services WHERE category = 'interior_design' AND status = 'active' ORDER BY display_order ASC";
    $servicesStmt = $pdo->query($servicesQuery);
    $services = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get portfolio projects
    $portfolioQuery = "SELECT * FROM portfolio WHERE category = 'interior_design' AND status = 'active' ORDER BY created_at DESC LIMIT 6";
    $portfolioStmt = $pdo->query($portfolioQuery);
    $portfolio = $portfolioStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get interior design team members
    $teamQuery = "SELECT * FROM team_members WHERE department = 'interior_design' AND status = 'active' ORDER BY display_order ASC";
    $teamStmt = $pdo->query($teamQuery);
    $team_members = $teamStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get interior design testimonials
    $testimonialsQuery = "SELECT * FROM testimonials WHERE category = 'interior_design' AND status = 'active' ORDER BY created_at DESC LIMIT 3";
    $testimonialsStmt = $pdo->query($testimonialsQuery);
    $testimonials = $testimonialsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get interior design FAQs
    $faqsQuery = "SELECT * FROM faqs WHERE category = 'interior_design' AND status = 'active' ORDER BY display_order ASC LIMIT 5";
    $faqsStmt = $pdo->query($faqsQuery);
    $faqs = $faqsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Interior design page database error: ' . $e->getMessage());
    $services = [];
    $portfolio = [];
    $team_members = [];
    $testimonials = [];
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
    <title><?php echo getSiteSetting('site_title', 'APS Dream Homes'); ?> - Interior Design Services</title>
    <meta name="description" content="Transform your space with our professional interior design services. From concept to completion, we create beautiful and functional environments tailored to your style and needs.">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- GLightbox for portfolio -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">

    <style>
        .interior-hero {
            background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
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
            border-left: 5px solid #8e44ad;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 2rem;
        }

        .portfolio-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
        }

        .portfolio-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .portfolio-image {
            height: 250px;
            overflow: hidden;
        }

        .portfolio-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .portfolio-item:hover .portfolio-image img {
            transform: scale(1.05);
        }

        .portfolio-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 20px;
        }

        .portfolio-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .portfolio-category {
            font-size: 0.9rem;
            opacity: 0.9;
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
            color: #8e44ad;
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
            background: #8e44ad;
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
            background: #8e44ad;
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

        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #8e44ad;
        }

        .testimonial-content {
            font-style: italic;
            margin-bottom: 20px;
            color: #666;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .author-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #8e44ad;
        }

        .author-info h5 {
            color: #8e44ad;
            margin-bottom: 3px;
        }

        .accordion-button {
            font-weight: 600;
        }

        .accordion-button:not(.collapsed) {
            background-color: #8e44ad;
            color: white;
        }

        .accordion-button:focus {
            border-color: #8e44ad;
            box-shadow: 0 0 0 0.2rem rgba(142, 68, 173, 0.25);
        }

        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }

        .cta-section {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(45deg, #8e44ad, #9b59b6);
            border: none;
            padding: 15px 35px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #9b59b6, #8e44ad);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .btn-outline-primary {
            border-color: #8e44ad;
            color: #8e44ad;
        }

        .btn-outline-primary:hover {
            background-color: #8e44ad;
            border-color: #8e44ad;
        }

        .breadcrumb {
            background: #f8f9fa;
            border-radius: 0;
        }

        .empty-state {
            padding: 60px 20px;
        }

        .rating {
            margin-bottom: 15px;
        }

        .rating .fas {
            color: #ffc107;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="interior-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Transform Your Space</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Professional interior design services tailored to your style and needs, creating beautiful and functional environments.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap" data-aos="fade-up" data-aos-delay="200">
                        <a href="#contact-form" class="btn btn-light btn-lg">
                            <i class="fas fa-palette me-2"></i>Get Started
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
                <li class="breadcrumb-item active" aria-current="page">Interior Design</li>
            </ol>
        </div>
    </nav>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Our Interior Design Services</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Comprehensive interior design solutions for every space and style
                    </p>
                </div>
            </div>

            <?php if (!empty($services)): ?>
            <div class="row g-4">
                <?php foreach ($services as $service): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="<?php echo htmlspecialchars($service['icon'] ?? 'fas fa-palette'); ?>"></i>
                        </div>
                        <h3 class="h4 fw-bold mb-3"><?php echo htmlspecialchars($service['title'] ?? 'Design Service'); ?></h3>
                        <p class="text-muted mb-4"><?php echo htmlspecialchars($service['description'] ?? 'Professional interior design service tailored to your needs.'); ?></p>

                        <?php if (!empty($service['features'])): ?>
                        <?php $features = json_decode($service['features'], true); ?>
                        <?php if (is_array($features) && !empty($features)): ?>
                        <ul class="list-unstyled">
                            <?php foreach ($features as $feature): ?>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
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
                    <i class="fas fa-palette fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">Interior Design Services Coming Soon</h3>
                    <p class="text-muted mb-4">
                        We're currently expanding our interior design services. Please contact us for immediate design consultation.
                    </p>
                    <a href="contact.php" class="btn btn-primary">Contact Us</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Portfolio Section -->
    <?php if (!empty($portfolio)): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Our Portfolio</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Browse through some of our recent interior design projects
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($portfolio as $project): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="portfolio-item">
                        <div class="portfolio-image">
                            <img src="<?php echo htmlspecialchars($project['image_url'] ?? 'assets/images/property-placeholder.jpg'); ?>"
                                 alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <div class="portfolio-overlay">
                                <h5 class="portfolio-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                <p class="portfolio-category"><?php echo htmlspecialchars($project['category']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Interior Design Team Section -->
    <?php if (!empty($team_members)): ?>
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Meet Our Design Team</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Creative professionals dedicated to bringing your vision to life
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($team_members as $member): ?>
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="<?php echo htmlspecialchars($member['image_path'] ?? 'assets/images/team-placeholder.jpg'); ?>"
                                 alt="<?php echo htmlspecialchars($member['name']); ?>">
                        </div>
                        <div class="team-info">
                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($member['name']); ?></h5>
                            <p class="designation mb-1"><?php echo htmlspecialchars($member['designation']); ?></p>
                            <?php if (!empty($member['specialization'])): ?>
                            <p class="specialization mb-3"><?php echo htmlspecialchars($member['specialization']); ?></p>
                            <?php endif; ?>
                            <div class="social-links">
                                <?php if (!empty($member['linkedin'])): ?>
                                <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" target="_blank" title="LinkedIn">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($member['email'])): ?>
                                <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" title="Email">
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

    <!-- Design Process Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Our Design Process</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        From concept to completion - a seamless design journey
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="process-timeline">
                        <div class="process-step" data-aos="fade-right">
                            <div class="step-number">1</div>
                            <h4 class="fw-bold mb-3">Initial Consultation</h4>
                            <p class="text-muted">Discuss your vision, preferences, and requirements in detail</p>
                        </div>

                        <div class="process-step" data-aos="fade-left" data-aos-delay="100">
                            <div class="step-number">2</div>
                            <h4 class="fw-bold mb-3">Concept Development</h4>
                            <p class="text-muted">Create initial design concepts and mood boards for your approval</p>
                        </div>

                        <div class="process-step" data-aos="fade-right" data-aos-delay="200">
                            <div class="step-number">3</div>
                            <h4 class="fw-bold mb-3">Detailed Planning</h4>
                            <p class="text-muted">Develop detailed plans, 3D visualizations, and material selections</p>
                        </div>

                        <div class="process-step" data-aos="fade-left" data-aos-delay="300">
                            <div class="step-number">4</div>
                            <h4 class="fw-bold mb-3">Implementation</h4>
                            <p class="text-muted">Execute the design with professional craftsmanship and quality</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <?php if (!empty($testimonials)): ?>
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">What Our Clients Say</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Real experiences from satisfied clients who transformed their spaces
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="testimonial-card">
                        <div class="rating mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= ($testimonial['rating'] ?? 5) ? 'text-warning' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-content"><?php echo htmlspecialchars($testimonial['content'] ?? $testimonial['message'] ?? ''); ?></p>
                        <div class="testimonial-author">
                            <img src="<?php echo htmlspecialchars($testimonial['author_image'] ?? 'assets/images/user-placeholder.jpg'); ?>"
                                 alt="<?php echo htmlspecialchars($testimonial['author_name'] ?? 'Client'); ?>"
                                 class="author-image">
                            <div class="author-info">
                                <h5><?php echo htmlspecialchars($testimonial['author_name'] ?? $testimonial['name'] ?? 'Anonymous'); ?></h5>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($testimonial['location'] ?? ''); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- FAQ Section -->
    <?php if (!empty($faqs)): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Frequently Asked Questions</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Find answers to common questions about our interior design services
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
    <section id="contact-form" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Start Your Design Journey</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                        Contact us to discuss your interior design project and bring your vision to life
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <form id="interiorDesignForm" class="contact-form" data-aos="fade-up">
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
                                <label for="projectType" class="form-label">Project Type *</label>
                                <select class="form-control" id="projectType" name="projectType" required>
                                    <option value="">Select Project Type</option>
                                    <option value="residential">Residential Design</option>
                                    <option value="commercial">Commercial Design</option>
                                    <option value="renovation">Renovation</option>
                                    <option value="consultation">Design Consultation</option>
                                    <option value="staging">Home Staging</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="budget" class="form-label">Budget Range</label>
                                <select class="form-control" id="budget" name="budget">
                                    <option value="">Select Budget Range</option>
                                    <option value="under_5l">Under ₹5 Lakhs</option>
                                    <option value="5l_15l">₹5L - ₹15L</option>
                                    <option value="15l_30l">₹15L - ₹30L</option>
                                    <option value="30l_50l">₹30L - ₹50L</option>
                                    <option value="over_50l">Over ₹50L</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="timeline" class="form-label">Timeline</label>
                                <select class="form-control" id="timeline" name="timeline">
                                    <option value="">Select Timeline</option>
                                    <option value="asap">ASAP</option>
                                    <option value="1_month">Within 1 Month</option>
                                    <option value="3_months">Within 3 Months</option>
                                    <option value="6_months">Within 6 Months</option>
                                    <option value="flexible">Flexible</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Project Details *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Please describe your project, style preferences, and any specific requirements..." required></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Project Inquiry
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
                    <h2 class="display-5 fw-bold mb-4" data-aos="fade-up">Ready to Transform Your Space?</h2>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Let's discuss your interior design project and create something beautiful together
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
    <!-- GLightbox for portfolio -->
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Initialize GLightbox
        const lightbox = GLightbox({
            selector: '.portfolio-item a'
        });

        // Form submission
        document.getElementById('interiorDesignForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(this);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }

            // In a real application, this would submit to a server
            alert('Thank you for your inquiry! Our design team will contact you soon to discuss your project.');

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
<section class="hero-section bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1>Transform Your Space</h1>
                <p class="lead">Professional interior design services tailored to your style and needs</p>
                <a href="#contact" class="btn btn-outline-light btn-lg">Get Started</a>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <img src="/assets/img/interior-design/hero-image.jpg" 
                     alt="Interior Design Services" 
                     class="img-fluid rounded shadow" 
                     loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="services-section">

<!-- Include Process Section -->
<?php include __DIR__ . '/includes/interior-design/process.php'; ?>

<!-- Include Portfolio Section -->
<?php include __DIR__ . '/includes/interior-design/portfolio.php'; ?>

<!-- Include Team Section -->
<?php include __DIR__ . '/includes/interior-design/team.php'; ?>

<!-- Include Testimonials Section -->
<?php include __DIR__ . '/includes/interior-design/testimonials.php'; ?>

<!-- Include FAQ Section -->
<?php include __DIR__ . '/includes/interior-design/faq.php'; ?>

<!-- Include Contact Section -->
<?php include __DIR__ . '/includes/interior-design/contact.php'; ?>

<!-- Include Call to Action Section -->
<?php include __DIR__ . '/includes/interior-design/cta.php'; ?>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2>Our Services</h2>
            <p>Comprehensive interior design solutions for every space</p>
        </div>

        <div class="row">
            <?php foreach ($services as $service): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>