<?php
// includes/templates/sections/testimonials.php

$section_title = $page_data['testimonials_title'] ?? 'What Our Clients Say';
$section_subtitle = $page_data['testimonials_subtitle'] ?? 'Real stories from satisfied clients who found their dream homes with us.';

// Default testimonials data - this could also come from $page_data or a database
$testimonials = $page_data['testimonials_list'] ?? [
    [
        'quote' => 'APS Dream Home made finding our new house a breeze! Their team was professional, attentive, and incredibly knowledgeable. We couldn\'t be happier!',
        'name' => 'Riya Sharma',
        'designation' => 'Home Buyer',
        'image' => SITE_URL . '/assets/images/testimonials/client-1.jpg' // Placeholder image
    ],
    [
        'quote' => 'Selling our property through APS Dream Home was a fantastic experience. They handled everything efficiently and got us a great price. Highly recommended!',
        'name' => 'Amit Patel',
        'designation' => 'Property Seller',
        'image' => SITE_URL . '/assets/images/testimonials/client-2.jpg' // Placeholder image
    ],
    [
        'quote' => 'As a first-time renter, I was nervous, but APS Dream Home guided me through every step. Their agents are friendly and found me the perfect apartment.',
        'name' => 'Priya Singh',
        'designation' => 'Tenant',
        'image' => SITE_URL . '/assets/images/testimonials/client-3.jpg' // Placeholder image
    ]
];

?>
<section id="testimonials" class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-md-8 mx-auto text-center">
                <h2 class="section-title fw-bold"><?php echo e($section_title); ?></h2>
                <p class="section-subtitle lead text-muted"><?php echo e($section_subtitle); ?></p>
            </div>
        </div>

        <?php if (!empty($testimonials) && is_array($testimonials)): ?>
            <div class="swiper testimonials-slider">
                <div class="swiper-wrapper">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <?php 
                            $quote = e($testimonial['quote'] ?? 'No quote provided.');
                            $name = e($testimonial['name'] ?? 'Anonymous');
                            $designation = e($testimonial['designation'] ?? 'Valued Client');
                            $image = e($testimonial['image'] ?? SITE_URL . '/assets/images/testimonials/default-avatar.png');
                        ?>
                        <div class="swiper-slide">
                            <div class="testimonial-card card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>" class="testimonial-image rounded-circle mb-3">
                                    <p class="testimonial-quote fst-italic text-muted">"<?php echo $quote; ?>"</p>
                                    <h5 class="testimonial-name fw-bold mt-4 mb-0"><?php echo $name; ?></h5>
                                    <p class="testimonial-designation text-primary small"><?php echo $designation; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination position-relative mt-4"></div>
                 <!-- Add Navigation -->
                <div class="swiper-button-prev text-primary"></div>
                <div class="swiper-button-next text-primary"></div>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">No testimonials available at the moment.</div>
        <?php endif; ?>
    </div>
</section>

<style>
.testimonials-section .section-title::after {
    content: '';
    position: absolute;
    display: block;
    width: 60px;
    height: 3px;
    background: var(--bs-primary, #0d6efd);
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
}

.testimonial-card {
    border: none;
    border-radius: 0.75rem;
    padding: 20px;
    background-color: #fff;
}

.testimonial-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border: 3px solid var(--bs-primary-light, #cfe2ff); /* Light primary color for border */
}

.testimonial-quote {
    font-size: 1rem;
    line-height: 1.6;
    min-height: 100px; /* Adjust based on average quote length */
}

.testimonial-name {
    font-size: 1.1rem;
}

.testimonial-designation {
    font-size: 0.9rem;
}

/* Swiper Styles */
.testimonials-slider {
    padding-bottom: 50px; /* Space for pagination */
}

.swiper-slide {
    display: flex;
    justify-content: center;
    align-items: stretch; /* Make cards in a row same height */
    padding: 10px; /* Add some padding around slides if needed */
}

.swiper-pagination-bullet {
    background-color: var(--bs-primary, #0d6efd);
    opacity: 0.7;
}

.swiper-pagination-bullet-active {
    background-color: var(--bs-primary, #0d6efd);
    opacity: 1;
}

.swiper-button-next, .swiper-button-prev {
    color: var(--bs-primary, #0d6efd) !important; /* Important to override default swiper color */
    width: calc(var(--swiper-navigation-size) / 44 * 30);
    height: calc(var(--swiper-navigation-size) / 44 * 30);
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.8);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.swiper-button-next:after, .swiper-button-prev:after {
    font-size: 1rem !important; /* Adjust icon size */
    font-weight: bold;
}

@media (max-width: 767px) {
    .swiper-button-next, .swiper-button-prev {
        display: none; /* Hide arrows on smaller screens, rely on pagination/swipe */
    }
}
</style>

<script>
// Initialize Swiper for testimonials - this should ideally be in main.js or a page-specific JS file
// Ensure Swiper JS is loaded before this script runs
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swiper !== 'undefined') {
        const testimonialsSwiper = new Swiper('.testimonials-slider', {
            loop: true,
            slidesPerView: 1,
            spaceBetween: 30,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                // when window width is >= 768px
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30
                },
                // when window width is >= 992px
                992: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            }
        });
    } else {
        console.warn('Swiper library not found. Testimonials slider will not be initialized.');
    }
});
</script>
