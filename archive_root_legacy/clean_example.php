<?php
// Clean Template System Example

require_once __DIR__ . '/includes/simple_template.php';

// Simple homepage
$content = "
<!-- Hero Section -->
<section class='hero-section'>
    <div class='container'>
        <div class='row'>
            <div class='col-lg-8 mx-auto text-center'>
                <h1 class='display-4 fw-bold mb-4'>Welcome to APS Dream Home</h1>
                <p class='lead mb-4'>Find your perfect property with our simple and clean system</p>
                <div class='d-flex justify-content-center gap-3'>
                    " . simple_button('Browse Properties', 'properties.php', 'btn-light btn-lg', 'search') . "
                    " . simple_button('Login', 'customer_login.php', 'btn-outline-light btn-lg', 'sign-in-alt') . "
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class='py-5'>
    <div class='container'>
        <h2 class='text-center mb-5'>Why Choose Us?</h2>
        <div class='row g-4'>
            <div class='col-md-4'>
                " . simple_card('
                    <i class="fas fa-home fa-3x text-primary mb-3"></i>
                    <h5>Easy to Use</h5>
                    <p>Simple template system that just works.</p>
                ', '', 'text-center h-100') . "
            </div>
            <div class='col-md-4'>
                " . simple_card('
                    <i class="fas fa-cog fa-3x text-success mb-3"></i>
                    <h5>No Setup Hassle</h5>
                    <p>One file, one include, done!</p>
                ', '', 'text-center h-100') . "
            </div>
            <div class='col-md-4'>
                " . simple_card('
                    <i class="fas fa-rocket fa-3x text-info mb-3"></i>
                    <h5>Fast Development</h5>
                    <p>Focus on your code, not templates.</p>
                ', '', 'text-center h-100') . "
            </div>
        </div>
    </div>
</section>";

simple_page($content, 'APS Dream Home - Clean & Simple');
?>
