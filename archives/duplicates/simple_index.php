<?php
// Simple Homepage Example

require_once __DIR__ . '/includes/simple_template.php';

$content = "
<!-- Hero Section -->
<section class='hero-section'>
    <div class='container'>
        <div class='row'>
            <div class='col-lg-8 mx-auto text-center'>
                <h1 class='display-4 fw-bold mb-4'>Find Your Dream Home</h1>
                <p class='lead mb-4'>Discover the perfect property with APS Dream Home - your trusted real estate partner</p>
                <div class='d-flex justify-content-center gap-3'>
                    " . simple_button('Browse Properties', 'properties.php', 'btn-light btn-lg', 'search') . "
                    " . simple_button('Contact Us', 'contact.php', 'btn-outline-light btn-lg', 'phone') . "
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class='py-5'>
    <div class='container'>
        <h2 class='text-center mb-5'>Why Choose APS Dream Home?</h2>

        <div class='row g-4'>
            <div class='col-md-4'>
                " . simple_card('
                    <i class="fas fa-home fa-3x text-primary mb-3"></i>
                    <h5>Wide Selection</h5>
                    <p>Explore thousands of properties across different categories and price ranges.</p>
                ', '', 'text-center h-100') . "
            </div>

            <div class='col-md-4'>
                " . simple_card('
                    <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                    <h5>Verified Listings</h5>
                    <p>All properties are verified by our team to ensure authenticity and quality.</p>
                ', '', 'text-center h-100') . "
            </div>

            <div class='col-md-4'>
                " . simple_card('
                    <i class="fas fa-headset fa-3x text-info mb-3"></i>
                    <h5>24/7 Support</h5>
                    <p>Get expert assistance whenever you need it from our dedicated support team.</p>
                ', '', 'text-center h-100') . "
            </div>
        </div>
    </div>
</section>

<!-- Quick Stats -->
<section class='py-5 bg-light'>
    <div class='container'>
        <div class='row text-center'>
            <div class='col-md-3'>
                <h3 class='text-primary'>1,200+</h3>
                <p>Properties Listed</p>
            </div>
            <div class='col-md-3'>
                <h3 class='text-success'>850+</h3>
                <p>Happy Customers</p>
            </div>
            <div class='col-md-3'>
                <h3 class='text-info'>15+</h3>
                <p>Years Experience</p>
            </div>
            <div class='col-md-3'>
                <h3 class='text-warning'>50+</h3>
                <p>Expert Agents</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class='py-5'>
    <div class='container'>
        <div class='row justify-content-center'>
            <div class='col-md-8 text-center'>
                " . simple_card('
                    <h3>Ready to Find Your Dream Home?</h3>
                    <p class="mb-4">Join thousands of satisfied customers who found their perfect property with us.</p>
                    <div class="d-flex justify-content-center gap-3">
                        ' . simple_button('Get Started', 'customer_registration.php', 'btn-primary btn-lg', 'rocket') . '
                        ' . simple_button('Learn More', 'about.php', 'btn-outline-primary btn-lg', 'info-circle') . '
                    </div>
                ', '', 'border-0 shadow') . "
            </div>
        </div>
    </div>
</section>";

simple_page($content, 'APS Dream Home - Find Your Perfect Property');
?>
