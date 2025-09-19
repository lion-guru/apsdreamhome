<?php
// includes/templates/sections/services.php

$section_title = $page_data['services_title'] ?? 'Our Services';
$section_subtitle = $page_data['services_subtitle'] ?? 'Comprehensive real estate solutions to meet your every need. We are committed to providing exceptional service and expert advice.';

// Default services data - this could also come from $page_data or a database if dynamic
$services = $page_data['services_list'] ?? [
    [
        'icon' => 'fas fa-home', // Font Awesome icon class
        'title' => 'Property Sales',
        'description' => 'Expert assistance in buying and selling residential and commercial properties. Maximize your investment with our market knowledge.',
        'link' => SITE_URL . '/services.php#sales'
    ],
    [
        'icon' => 'fas fa-building', 
        'title' => 'Property Rentals',
        'description' => 'Find the perfect rental property or let us manage your rental portfolio. We streamline the process for landlords and tenants.',
        'link' => SITE_URL . '/services.php#rentals'
    ],
    [
        'icon' => 'fas fa-chart-line',
        'title' => 'Property Management',
        'description' => 'Professional property management services, ensuring your investment is well-maintained and profitable. Hassle-free ownership.',
        'link' => SITE_URL . '/services.php#management'
    ],
    [
        'icon' => 'fas fa-balance-scale',
        'title' => 'Legal Consultation',
        'description' => 'Navigate the complexities of real estate law with our expert legal advisors. Secure your transactions and protect your interests.',
        'link' => SITE_URL . '/services.php#legal'
    ],
    [
        'icon' => 'fas fa-search-dollar',
        'title' => 'Investment Advice',
        'description' => 'Strategic investment advice to help you grow your real estate portfolio. Identify opportunities and make informed decisions.',
        'link' => SITE_URL . '/services.php#investment'
    ],
    [
        'icon' => 'fas fa-tools',
        'title' => 'Home Renovation',
        'description' => 'Connect with trusted contractors for home renovation and improvement projects. Enhance your property’s value and appeal.',
        'link' => SITE_URL . '/services.php#renovation'
    ]
];

?>
<section id="services" class="services-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-md-8 mx-auto text-center">
                <h2 class="section-title fw-bold"><?php echo e($section_title); ?></h2>
                <p class="section-subtitle lead text-muted"><?php echo e($section_subtitle); ?></p>
            </div>
        </div>

        <?php if (!empty($services) && is_array($services)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($services as $index => $service): ?>
                    <?php 
                        $icon = e($service['icon'] ?? 'fas fa-concierge-bell');
                        $title = e($service['title'] ?? 'Service Title');
                        $description = e($service['description'] ?? 'Service description goes here.');
                        $link = e($service['link'] ?? '#');
                        // Add a delay for staggered animation
                        $animation_delay = ($index % 3) * 0.1;
                    ?>
                    <div class="col animate__animated animate__fadeInUp" style="animation-delay: <?php echo $animation_delay; ?>s;">
                        <div class="service-card card h-100 text-center shadow-sm">
                            <div class="card-body">
                                <div class="service-icon text-primary mb-3">
                                    <i class="<?php echo $icon; ?> fa-3x"></i>
                                </div>
                                <h5 class="card-title fw-bold"><?php echo $title; ?></h5>
                                <p class="card-text text-muted small"><?php echo $description; ?></p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 pb-3">
                                <a href="<?php echo $link; ?>" class="btn btn-outline-primary btn-sm">Learn More <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">No services information available at the moment.</div>
        <?php endif; ?>
    </div>
</section>

<style>
.services-section .section-title::after {
    content: '';
    position: absolute;
    display: block;
    width: 60px;
    height: 3px;
    background: var(--bs-primary, #0d6efd);
    bottom: -5px; /* Adjusted for better visual */
    left: 50%;
    transform: translateX(-50%);
}

.service-card {
    border: none; /* Remove default card border */
    border-radius: 0.5rem; /* Softer corners */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.service-icon i {
    transition: transform 0.3s ease;
}

.service-card:hover .service-icon i {
    transform: scale(1.1);
}

.card-title {
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
}

/* Ensure animations are defined or use a library like Animate.css */
.animate__animated.animate__fadeInUp {
    animation-name: fadeInUp; /* Ensure fadeInUp is defined if not using full Animate.css */
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translate3d(0, 30px, 0);
  }
  to {
    opacity: 1;
    transform: translate3d(0, 0, 0);
  }
}
</style>
