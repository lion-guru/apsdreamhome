<?php
// Enhanced About.php using Universal Template System
// All functionality from old about.php preserved and enhanced

require_once __DIR__ . '/includes/enhanced_universal_template.php';

// Get database connection and data (preserved from old about.php)
require_once __DIR__ . '/init.php';

// Get team members (same query as old about.php)
$team_members = [];
try {
    $db = \App\Core\App::database();
    $team_members = $db->fetchAll("SELECT * FROM user WHERE utype = :utype AND status = :status ORDER BY uname LIMIT 6", [
        'utype' => 'agent',
        'status' => 'active'
    ]);
} catch (Exception $e) {
    error_log('Error fetching team members: ' . $e->getMessage());
}

// Get testimonials (same query as old about.php)
$testimonials = [];
try {
    $db = \App\Core\App::database();
    $testimonials = $db->fetchAll("SELECT * FROM testimonials WHERE status = :status ORDER BY rating DESC LIMIT 4", [
        'status' => 'approved'
    ]);
} catch (Exception $e) {
    error_log('Error fetching testimonials: ' . $e->getMessage());
}

// Company info (same as old about.php)
$company_info = [
    'phone' => '+91-9000000001',
    'email' => 'info@apsdreamhome.com',
    'address' => 'Gorakhpur, Uttar Pradesh, India'
];

// Build the complete content with all old about.php features
$content = "
<!-- About Hero Section (Identical to old about.php) -->
<section class='py-5 bg-primary text-white' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);'>
    <div class='container'>
        <div class='row align-items-center'>
            <div class='col-lg-6'>
                <h1 class='display-4 fw-bold mb-4'>About APS Dream Home</h1>
                <p class='lead mb-4'>Your trusted partner in real estate solutions, providing comprehensive property services with modern technology and personalized approach.</p>
                <div class='d-flex gap-3'>
                    <a href='#contact' class='btn btn-light btn-lg'>
                        <i class='fas fa-phone me-2'></i>Get In Touch
                    </a>
                    <a href='properties.php' class='btn btn-outline-light btn-lg'>
                        <i class='fas fa-search me-2'></i>Browse Properties
                    </a>
                </div>
            </div>
            <div class='col-lg-6'>
                <div class='text-center'>
                    <i class='fas fa-home fa-5x text-warning mb-4'></i>
                    <h3 class='h2 mb-3'>Building Dreams, One Home at a Time</h3>
                    <p class='mb-0'>With years of experience in the real estate industry, we have helped thousands of families find their perfect homes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Story Section (Identical to old about.php) -->
<section class='py-5'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h2 class='display-5 fw-bold mb-3'>Our Story</h2>
                <p class='lead text-muted'>How we started and where we're headed</p>
            </div>
        </div>

        <div class='row align-items-center'>
            <div class='col-lg-6 mb-4 mb-lg-0'>
                <div class='pe-lg-4'>
                    <h3 class='h2 fw-bold mb-4'>Founded with a Vision</h3>
                    <p class='mb-4'>APS Dream Home was founded with the vision of revolutionizing the real estate industry in India. We recognized that finding the perfect home should be an exciting journey, not a stressful ordeal.</p>

                    <div class='row g-4'>
                        <div class='col-md-6'>
                            <div class='text-center p-3 bg-light rounded'>
                                <i class='fas fa-bullseye fa-3x text-primary mb-3'></i>
                                <h5>Our Mission</h5>
                                <p class='small mb-0'>To make home buying and selling a seamless, transparent, and enjoyable experience for everyone.</p>
                            </div>
                        </div>
                        <div class='col-md-6'>
                            <div class='text-center p-3 bg-light rounded'>
                                <i class='fas fa-eye fa-3x text-success mb-3'></i>
                                <h5>Our Vision</h5>
                                <p class='small mb-0'>To be the most trusted and innovative real estate platform in India.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-lg-6'>
                <img src='assets/images/about/company-story.jpg'
                     alt='Company Story'
                     class='img-fluid rounded shadow'
                     onerror=\"this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmOWZhIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzZjNzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkNvbXBhbnkgU3Rvcnk8L3RleHQ+PC9zdmc+'\">
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section (Identical to old about.php) -->
<section class='py-5 bg-light'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h2 class='display-5 fw-bold mb-3'>Why Choose APS Dream Home?</h2>
                <p class='lead text-muted'>What sets us apart from the competition</p>
            </div>
        </div>

        <div class='row g-4'>
            <div class='col-lg-4 col-md-6' data-aos='fade-up'>
                <div class='card h-100 border-0 shadow-sm text-center'>
                    <div class='card-body p-4'>
                        <div class='mb-3'>
                            <i class='fas fa-shield-alt fa-3x text-primary'></i>
                        </div>
                        <h5 class='card-title fw-bold mb-3'>Trusted & Secure</h5>
                        <p class='card-text'>We prioritize your security with bank-grade encryption and verified listings. Your trust is our most valuable asset.</p>
                    </div>
                </div>
            </div>

            <div class='col-lg-4 col-md-6' data-aos='fade-up' data-aos-delay='100'>
                <div class='card h-100 border-0 shadow-sm text-center'>
                    <div class='card-body p-4'>
                        <div class='mb-3'>
                            <i class='fas fa-robot fa-3x text-success'></i>
                        </div>
                        <h5 class='card-title fw-bold mb-3'>AI-Powered</h5>
                        <p class='card-text'>Our advanced AI algorithms help you find the perfect property based on your preferences and requirements.</p>
                    </div>
                </div>
            </div>

            <div class='col-lg-4 col-md-6' data-aos='fade-up' data-aos-delay='200'>
                <div class='card h-100 border-0 shadow-sm text-center'>
                    <div class='card-body p-4'>
                        <div class='mb-3'>
                            <i class='fas fa-users fa-3x text-info'></i>
                        </div>
                        <h5 class='card-title fw-bold mb-3'>Expert Team</h5>
                        <p class='card-text'>Our experienced real estate professionals are here to guide you through every step of your property journey.</p>
                    </div>
                </div>
            </div>

            <div class='col-lg-4 col-md-6' data-aos='fade-up' data-aos-delay='300'>
                <div class='card h-100 border-0 shadow-sm text-center'>
                    <div class='card-body p-4'>
                        <div class='mb-3'>
                            <i class='fas fa-chart-line fa-3x text-warning'></i>
                        </div>
                        <h5 class='card-title fw-bold mb-3'>Market Insights</h5>
                        <p class='card-text'>Get access to real-time market data and insights to make informed decisions about your property investments.</p>
                    </div>
                </div>
            </div>

            <div class='col-lg-4 col-md-6' data-aos='fade-up' data-aos-delay='400'>
                <div class='card h-100 border-0 shadow-sm text-center'>
                    <div class='card-body p-4'>
                        <div class='mb-3'>
                            <i class='fas fa-mobile-alt fa-3x text-danger'></i>
                        </div>
                        <h5 class='card-title fw-bold mb-3'>Mobile First</h5>
                        <p class='card-text'>Access our platform anywhere, anytime with our responsive design and mobile applications.</p>
                    </div>
                </div>
            </div>

            <div class='col-lg-4 col-md-6' data-aos='fade-up' data-aos-delay='500'>
                <div class='card h-100 border-0 shadow-sm text-center'>
                    <div class='card-body p-4'>
                        <div class='mb-3'>
                            <i class='fas fa-award fa-3x text-secondary'></i>
                        </div>
                        <h5 class='card-title fw-bold mb-3'>Award Winning</h5>
                        <p class='card-text'>Recognized for excellence in customer service and innovative real estate solutions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section (Identical to old about.php) -->";
if (!empty($team_members)) {
    $content .= "
<section class='py-5'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h2 class='display-5 fw-bold mb-3'>Meet Our Team</h2>
                <p class='lead text-muted'>Expert professionals dedicated to your success</p>
            </div>
        </div>

        <div class='row g-4'>";
    foreach ($team_members as $member) {
        $content .= "
            <div class='col-lg-4 col-md-6' data-aos='fade-up'>
                <div class='card h-100 border-0 shadow-sm text-center'>
                    <div class='card-body p-4'>
                        <img src='assets/images/user-placeholder.jpg'
                             alt='" . h(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) . "'
                             class='rounded-circle mb-3' width='100' height='100'>
                        <h5 class='card-title fw-bold mb-2'>
                            " . h(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) . "
                        </h5>
                        <p class='text-muted mb-3'>Real Estate Agent</p>
                        <div class='d-flex justify-content-center gap-2'>
                            <a href='tel:" . h($member['phone'] ?? '') . "' class='btn btn-outline-primary btn-sm'>
                                <i class='fas fa-phone'></i>
                            </a>
                            <a href='mailto:" . h($member['email'] ?? '') . "' class='btn btn-outline-primary btn-sm'>
                                <i class='fas fa-envelope'></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>";
    }
    $content .= "
        </div>
    </div>
</section>";
}
$content .= "

<!-- Testimonials Section (Identical to old about.php) -->";
if (!empty($testimonials)) {
    $content .= "
<section class='py-5 bg-light'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h2 class='display-5 fw-bold mb-3'>What Our Customers Say</h2>
                <p class='lead text-muted'>Real experiences from real customers</p>
            </div>
        </div>

        <div class='row g-4'>";
    foreach ($testimonials as $testimonial) {
        $content .= "
            <div class='col-lg-6' data-aos='fade-up'>
                <div class='card border-0 shadow-sm h-100'>
                    <div class='card-body p-4'>
                        <div class='mb-3'>";
        for ($i = 0; $i < ($testimonial['rating'] ?? 5); $i++) {
            $content .= "<i class='fas fa-star text-warning'></i>";
        }
        $content .= "
                        </div>
                        <p class='card-text fst-italic mb-4'>\"" . h($testimonial['message'] ?? '') . "\"</p>
                        <div class='d-flex align-items-center'>
                            <img src='assets/images/user-placeholder.jpg'
                                 alt='" . h($testimonial['name'] ?? 'Customer') . "'
                                 class='rounded-circle me-3' width='50' height='50'>
                            <div>
                                <h6 class='mb-0'>" . h($testimonial['name'] ?? 'Anonymous') . "</h6>
                                <small class='text-muted'>" . h($testimonial['location'] ?? '') . "</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
    }
    $content .= "
        </div>
    </div>
</section>";
}
$content .= "

<!-- Company Values Section (Identical to old about.php) -->
<section class='py-5'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h2 class='display-5 fw-bold mb-3'>Our Values</h2>
                <p class='lead text-muted'>The principles that guide everything we do</p>
            </div>
        </div>

        <div class='row g-4'>
            <div class='col-lg-3 col-md-6' data-aos='fade-up'>
                <div class='text-center p-4'>
                    <div class='mb-3'>
                        <i class='fas fa-heart fa-3x text-danger'></i>
                    </div>
                    <h5 class='fw-bold mb-3'>Integrity</h5>
                    <p class='text-muted small'>We conduct business with the highest ethical standards and transparency.</p>
                </div>
            </div>
            <div class='col-lg-3 col-md-6' data-aos='fade-up' data-aos-delay='100'>
                <div class='text-center p-4'>
                    <div class='mb-3'>
                        <i class='fas fa-users fa-3x text-primary'></i>
                    </div>
                    <h5 class='fw-bold mb-3'>Customer First</h5>
                    <p class='text-muted small'>Every decision we make prioritizes our customers' needs and satisfaction.</p>
                </div>
            </div>
            <div class='col-lg-3 col-md-6' data-aos='fade-up' data-aos-delay='200'>
                <div class='text-center p-4'>
                    <div class='mb-3'>
                        <i class='fas fa-lightbulb fa-3x text-warning'></i>
                    </div>
                    <h5 class='fw-bold mb-3'>Innovation</h5>
                    <p class='text-muted small'>We embrace technology and innovative solutions to better serve our clients.</p>
                </div>
            </div>
            <div class='col-lg-3 col-md-6' data-aos='fade-up' data-aos-delay='300'>
                <div class='text-center p-4'>
                    <div class='mb-3'>
                        <i class='fas fa-handshake fa-3x text-success'></i>
                    </div>
                    <h5 class='fw-bold mb-3'>Excellence</h5>
                    <p class='text-muted small'>We strive for excellence in every interaction and transaction.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA Section (Identical to old about.php) -->
<section class='py-5 bg-primary text-white' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center'>
                <h2 class='display-5 fw-bold mb-4'>Ready to Find Your Dream Home?</h2>
                <p class='lead mb-4'>Get in touch with our expert team today</p>
                <div class='d-flex gap-3 justify-content-center flex-wrap'>
                    <a href='contact.php' class='btn btn-light btn-lg'>
                        <i class='fas fa-phone me-2'></i>Contact Us
                    </a>
                    <a href='properties.php' class='btn btn-outline-light btn-lg'>
                        <i class='fas fa-search me-2'></i>Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>";

// Add all JavaScript functionality from old about.php (preserved)
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

    // Team member hover effects (from old about.php)
    const teamCards = document.querySelectorAll('.card');
    teamCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
        });
    });
});
");

// Add custom CSS for hover effects (from old about.php)
$template->addCSS("
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
");

// Render the complete page with base template
require_once __DIR__ . '/../../includes/base_template.php';
render_base_template('About Us - APS Dream Home', $content);
