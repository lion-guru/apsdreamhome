<?php
// includes/templates/sections/cta.php

$cta_title = $page_data['cta_title'] ?? 'Ready to Find Your Dream Property?';
$cta_text = $page_data['cta_text'] ?? 'Our expert team is here to assist you every step of the way. Whether you\'re looking to buy, sell, or rent, we have the resources and expertise to make your real estate journey seamless and successful.';
$cta_button_primary_text = $page_data['cta_button_primary_text'] ?? 'Browse Properties';
$cta_button_primary_link = $page_data['cta_button_primary_link'] ?? SITE_URL . '/properties.php';
$cta_button_secondary_text = $page_data['cta_button_secondary_text'] ?? 'Contact Us';
$cta_button_secondary_link = $page_data['cta_button_secondary_link'] ?? SITE_URL . '/contact.php';
$cta_background_image = $page_data['cta_background_image'] ?? SITE_URL . '/assets/images/cta-bg.jpg'; // Default CTA background

?>
<section id="cta" class="cta-section py-5 text-white text-center" style="background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('<?php echo e($cta_background_image); ?>') no-repeat center center; background-size: cover; background-attachment: fixed;">
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-9 col-md-10 mx-auto">
                <h2 class="display-5 fw-bold mb-3 animate__animated animate__pulse"><?php echo e($cta_title); ?></h2>
                <p class="lead mb-4 animate__animated animate__fadeInUp"><?php echo e($cta_text); ?></p>
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center animate__animated animate__fadeInUp animate__delay-1s">
                    <?php if ($cta_button_primary_text && $cta_button_primary_link): ?>
                    <a href="<?php echo e($cta_button_primary_link); ?>" class="btn btn-primary btn-lg px-4 gap-3">
                        <i class="fas fa-th-list me-2"></i><?php echo e($cta_button_primary_text); ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($cta_button_secondary_text && $cta_button_secondary_link): ?>
                    <a href="<?php echo e($cta_button_secondary_link); ?>" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-phone-alt me-2"></i><?php echo e($cta_button_secondary_text); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Ensure animations are defined or use a library like Animate.css */
.animate__animated.animate__pulse {
    animation-name: pulse; 
    animation-iteration-count: 1; /* Or infinite if you want continuous pulsing */
}
.animate__animated.animate__fadeInUp {
    animation-name: fadeInUp;
}

@keyframes pulse {
  from {
    transform: scale3d(1, 1, 1);
  }
  50% {
    transform: scale3d(1.05, 1.05, 1.05);
  }
  to {
    transform: scale3d(1, 1, 1);
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translate3d(0, 20px, 0);
  }
  to {
    opacity: 1;
    transform: translate3d(0, 0, 0);
  }
}
.animate__delay-1s { animation-delay: 0.5s; }
</style>
