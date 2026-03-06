<?php
/**
 * About Page - APS Dream Home
 * Professional company information and team details
 */

// Set page title and description for layout
$page_title = $title ?? 'About Us - APS Dream Home';
$page_description = $description ?? 'Learn about APS Dream Home - Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.';
?>

<!-- Include Header -->
<?php include __DIR__ . '/../layouts/header_new.php'; ?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">
                        About <span class="text-warning">APS Dream Home</span>
                    </h1>
                    <p class="lead mb-4">
                        Leading real estate developer in Gorakhpur with <?php echo $company_info['experience'] ?? '8+ Years'; ?> of excellence in property development and customer satisfaction. Building dreams into reality with trust and innovation.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo BASE_URL; ?>contact" class="btn btn-warning btn-lg">
                            <i class="fas fa-phone me-2"></i>Contact Us
                        </a>
                        <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-home me-2"></i>View Properties
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/hero-about.jpg" 
                         alt="APS Dream Home" 
                         class="img-fluid rounded shadow-lg"
                         onerror="this.src='https://via.placeholder.com/600x400/667eea/ffffff?text=APS+Dream+Home'">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Info Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Our Company</h2>
                <p class="lead text-muted">Building trust through excellence and innovation</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title text-primary mb-3">
                            <i class="fas fa-bullseye me-2"></i>Our Mission
                        </h4>
                        <p class="card-text">
                            <?php echo $mission ?? 'To provide transparent and hassle-free real estate services with a focus on customer satisfaction and quality construction.'; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title text-primary mb-3">
                            <i class="fas fa-eye me-2"></i>Our Vision
                        </h4>
                        <p class="card-text">
                            <?php echo $vision ?? 'To become the most trusted real estate developer in Uttar Pradesh by delivering excellence in every project.'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="display-4 fw-bold text-primary mb-2">
                        <?php echo $company_info['experience'] ?? '8+ Years'; ?>
                    </div>
                    <div class="text-muted">Experience</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="display-4 fw-bold text-primary mb-2">
                        <?php echo $company_info['projects'] ?? '50+'; ?>
                    </div>
                    <div class="text-muted">Projects</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="display-4 fw-bold text-primary mb-2">
                        <?php echo $company_info['properties'] ?? '500+'; ?>
                    </div>
                    <div class="text-muted">Properties</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="display-4 fw-bold text-primary mb-2">
                        <?php echo $company_info['happy_families'] ?? '2000+'; ?>
                    </div>
                    <div class="text-muted">Happy Families</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Our Core Values</h2>
                <p class="lead text-muted">The principles that guide our business</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($values)): ?>
                <?php foreach ($values as $value): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border-0 shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <i class="fas fa-star text-warning fa-2x"></i>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($value); ?></h5>
                                <p class="card-text text-muted small">
                                    We believe in <?php echo strtolower($value); ?> in everything we do.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <i class="fas fa-shield-alt text-primary fa-2x"></i>
                            </div>
                            <h5 class="card-title">Transparency</h5>
                            <p class="card-text text-muted small">Honest dealings and clear communication</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <i class="fas fa-gem text-primary fa-2x"></i>
                            </div>
                            <h5 class="card-title">Quality</h5>
                            <p class="card-text text-muted small">Excellence in every project</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <i class="fas fa-users text-primary fa-2x"></i>
                            </div>
                            <h5 class="card-title">Customer Satisfaction</h5>
                            <p class="card-text text-muted small">Your success is our priority</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Our Leadership Team</h2>
                <p class="lead text-muted">Experienced professionals driving our success</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($team)): ?>
                <?php foreach ($team as $member): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4 text-center">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                         style="width: 80px; height: 80px; font-size: 2rem;">
                                        <?php echo substr($member->name, 0, 2); ?>
                                    </div>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($member->name); ?></h5>
                                <div class="text-primary small mb-2"><?php echo htmlspecialchars($member->position); ?></div>
                                <div class="text-muted small mb-2"><?php echo htmlspecialchars($member->experience); ?></div>
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars($member->description); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    AK
                                </div>
                            </div>
                            <h5 class="card-title">Amit Kumar Singh</h5>
                            <div class="text-primary small mb-2">Managing Director</div>
                            <div class="text-muted small mb-2">15+ Years</div>
                            <p class="card-text text-muted small">
                                Leading company with vision and expertise in real estate development.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    PS
                                </div>
                            </div>
                            <h5 class="card-title">Priya Singh</h5>
                            <div class="text-primary small mb-2">Operations Head</div>
                            <div class="text-muted small mb-2">10+ Years</div>
                            <p class="card-text text-muted small">
                                Managing day-to-day operations with focus on efficiency and quality.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    RV
                                </div>
                            </div>
                            <h5 class="card-title">Rahul Verma</h5>
                            <div class="text-primary small mb-2">Technical Director</div>
                            <div class="text-muted small mb-2">12+ Years</div>
                            <p class="card-text text-muted small">
                                Ensuring technical excellence and innovation in construction.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h2 class="display-6 fw-bold mb-3">Ready to Work With Us?</h2>
                <p class="lead mb-4">
                    Join thousands of satisfied customers who found their perfect property with APS Dream Home.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-warning btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-home me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Include Footer -->
<?php include __DIR__ . '/../layouts/footer_new.php'; ?>
