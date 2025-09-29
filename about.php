<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the template system
require_once __DIR__ . '/includes/enhanced_universal_template.php';

// Create template instance
$template = new EnhancedUniversalTemplate();

// Set page title and description
$page_title = 'About Us - APS Dream Home';
$page_description = 'Learn about APS Dream Home - Your trusted partner in real estate. We provide comprehensive property services with modern technology and personalized approach.';

// Define constant to allow database connection
define('INCLUDED_FROM_MAIN', true);

// Include database connection
require_once 'includes/db_connection.php';

// Get database connection
try {
    global $pdo;
    $conn = $pdo;
    
    if (!$conn) {
        throw new PDOException('Database connection failed');
    }

    // Set default timezone
    date_default_timezone_set('Asia/Kolkata');

    // Define base URL
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . basename(dirname(__FILE__));
    define('BASE_URL', rtrim($base_url, '/'));

    // Get company information
    $company_info = [];
    $query = "SELECT * FROM site_settings WHERE setting_name = 'company_info'";
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['value'])) {
            $company_info = json_decode($row['value'], true) ?: [];
        }
    }

    // Get team members
    $team_members = [];
    $query = "SELECT * FROM users WHERE role = 'agent' AND status = 'active' ORDER BY first_name LIMIT 6";
    $result = $conn->query($query);
    if ($result) {
        $team_members = $result->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get testimonials
    $testimonials = [];
    $query = "SELECT * FROM testimonials WHERE status = 'approved' ORDER BY rating DESC LIMIT 4";
    $result = $conn->query($query);
    if ($result) {
        $testimonials = $result->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "Sorry, we're experiencing technical difficulties. Please try again later.";
}

// Start output buffering
ob_start();
?>
<!-- About Hero Section -->
<section class="py-5 bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">About APS Dream Home</h1>
                <p class="lead mb-4">Your trusted partner in real estate solutions, providing comprehensive property services with modern technology and personalized approach.</p>
                <div class="d-flex gap-3">
                    <a href="#contact" class="btn btn-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Get In Touch
                    </a>
                    <a href="properties.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <i class="fas fa-home fa-5x text-warning mb-4"></i>
                    <h3 class="h2 mb-3">Building Dreams, One Home at a Time</h3>
                    <p class="mb-0">With years of experience in the real estate industry, we have helped thousands of families find their perfect homes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Story Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Our Story</h2>
                <p class="lead text-muted">How we started and where we're headed</p>
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="pe-lg-4">
                    <h3 class="h2 fw-bold mb-4">Founded with a Vision</h3>
                    <p class="mb-4">APS Dream Home was founded with the vision of revolutionizing the real estate industry in India. We recognized that finding the perfect home should be an exciting journey, not a stressful ordeal.</p>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-bullseye fa-3x text-primary mb-3"></i>
                                <h5>Our Mission</h5>
                                <p class="small mb-0">To make home buying and selling a seamless, transparent, and enjoyable experience for everyone.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-eye fa-3x text-success mb-3"></i>
                                <h5>Our Vision</h5>
                                <p class="small mb-0">To be the most trusted and innovative real estate platform in India.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/about/company-story.jpg"
                     alt="Company Story"
                     class="img-fluid rounded shadow"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmOWZhIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzZjNzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkNvbXBhbnkgU3Rvcnk8L3RleHQ+PC9zdmc+'">
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Why Choose APS Dream Home?</h2>
                <p class="lead text-muted">What sets us apart from the competition</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-shield-alt fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Trusted & Secure</h5>
                        <p class="card-text">We prioritize your security with bank-grade encryption and verified listings. Your trust is our most valuable asset.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-robot fa-3x text-success"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">AI-Powered</h5>
                        <p class="card-text">Our advanced AI algorithms help you find the perfect property based on your preferences and requirements.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-users fa-3x text-info"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Expert Team</h5>
                        <p class="card-text">Our experienced real estate professionals are here to guide you through every step of your property journey.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-chart-line fa-3x text-warning"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Market Insights</h5>
                        <p class="card-text">Get access to real-time market data and insights to make informed decisions about your property investments.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-mobile-alt fa-3x text-danger"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Mobile First</h5>
                        <p class="card-text">Access our platform anywhere, anytime with our responsive design and mobile applications.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-award fa-3x text-secondary"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Award Winning</h5>
                        <p class="card-text">Recognized for excellence in customer service and innovative real estate solutions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<?php if (!empty($team_members)): ?>
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Meet Our Team</h2>
                <p class="lead text-muted">Expert professionals dedicated to your success</p>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($team_members as $member): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-4">
                            <img src="assets/images/user-placeholder.jpg"
                                 alt="<?php echo htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')); ?>"
                                 class="rounded-circle mb-3" width="100" height="100">
                            <h5 class="card-title fw-bold mb-2">
                                <?php echo htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')); ?>
                            </h5>
                            <p class="text-muted mb-3">Real Estate Agent</p>
                            <div class="d-flex justify-content-center gap-2">
                                <?php if (!empty($member['phone'])): ?>
                                <a href="tel:<?php echo htmlspecialchars($member['phone']); ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-phone"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($member['email'])): ?>
                                <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="btn btn-outline-primary btn-sm">
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

<!-- Testimonials Section -->
<?php if (!empty($testimonials)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">What Our Customers Say</h2>
                <p class="lead text-muted">Real experiences from real customers</p>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-lg-6" data-aos="fade-up">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <?php for ($i = 0; $i < ($testimonial['rating'] ?? 5); $i++): ?>
                                    <i class="fas fa-star text-warning"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="card-text fst-italic mb-4">"<?php echo htmlspecialchars($testimonial['message'] ?? ''); ?>"</p>
                            <div class="d-flex align-items-center">
                                <img src="assets/images/user-placeholder.jpg"
                                     alt="<?php echo htmlspecialchars($testimonial['name'] ?? 'Customer'); ?>"
                                     class="rounded-circle me-3" width="50" height="50">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($testimonial['name'] ?? 'Anonymous'); ?></h6>
                                    <?php if (!empty($testimonial['location'])): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($testimonial['location']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Company Values Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Our Values</h2>
                <p class="lead text-muted">The principles that guide everything we do</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-heart fa-3x text-danger"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Integrity</h5>
                    <p class="text-muted small">We conduct business with the highest ethical standards and transparency.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Customer First</h5>
                    <p class="text-muted small">Every decision we make prioritizes our customers' needs and satisfaction.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-lightbulb fa-3x text-warning"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Innovation</h5>
                    <p class="text-muted small">We embrace technology and innovative solutions to better serve our clients.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-handshake fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Excellence</h5>
                    <p class="text-muted small">We strive for excellence in every interaction and transaction.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA Section -->
<section class="py-5 bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-4">Ready to Find Your Dream Home?</h2>
                <p class="lead mb-4">Get in touch with our expert team today</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="contact.php" class="btn btn-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="properties.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// End output buffering and get content
$content = ob_get_clean();

// Add JavaScript
$template->addJS("
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS if available
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }

    // Team member hover effects
    const teamCards = document.querySelectorAll('.card');
    teamCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
});
");

// Add CSS
$template->addCSS("
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
");

// Output the page using the template system
page($content, $page_title);
?>
