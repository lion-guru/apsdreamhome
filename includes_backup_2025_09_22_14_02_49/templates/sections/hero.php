<?php
// includes/templates/sections/hero.php

// Default values for hero section data
$hero_title = $page_data['hero_title'] ?? 'Find Your Dream Home Today';
$hero_subtitle = $page_data['hero_subtitle'] ?? 'Discover a wide range of properties tailored to your needs. Let us help you find the perfect place to call home.';
$hero_search_placeholder = $page_data['hero_search_placeholder'] ?? 'Enter keyword, location, or property ID...';
$hero_background_image = $page_data['hero_background_image'] ?? SITE_URL . '/assets/images/hero-bg.jpg'; // Default hero background

?>
<section class="hero-section text-white py-5" style="background: url('<?php echo e($hero_background_image); ?>') no-repeat center center; background-size: cover;">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 col-md-10 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-3 animate__animated animate__fadeInDown"><?php echo e($hero_title); ?></h1>
                <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s"><?php echo e($hero_subtitle); ?></p>
                
                <form action="<?php echo SITE_URL; ?>/properties.php" method="get" class="hero-search-form animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="input-group input-group-lg mb-3 shadow">
                        <input type="text" name="search_query" class="form-control" placeholder="<?php echo e($hero_search_placeholder); ?>" aria-label="Search properties">
                        <button class="btn btn-primary px-4" type="submit">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2 advanced-search-options">
                        <a href="<?php echo SITE_URL; ?>/properties.php?type=sale" class="btn btn-outline-light btn-sm">For Sale</a>
                        <a href="<?php echo SITE_URL; ?>/properties.php?type=rent" class="btn btn-outline-light btn-sm">For Rent</a>
                        <a href="<?php echo SITE_URL; ?>/map-search.php" class="btn btn-outline-light btn-sm"><i class="fas fa-map-marked-alt me-1"></i>Map Search</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    position: relative;
    min-height: 70vh; /* Adjust as needed */
    display: flex;
    align-items: center;
    justify-content: center;
}
.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5); /* Dark overlay for text readability */
    z-index: 1;
}
.hero-section .container {
    position: relative;
    z-index: 2;
}
.hero-search-form .form-control {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
.hero-search-form .btn {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}
.advanced-search-options a.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}
/* Basic Animate.css classes (include full library or specific animations if needed) */
.animate__animated {
  animation-duration: 1s;
  animation-fill-mode: both;
}
.animate__delay-1s { animation-delay: 0.5s; }
.animate__delay-2s { animation-delay: 1s; }

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translate3d(0, -20px, 0);
  }
  to {
    opacity: 1;
    transform: translate3d(0, 0, 0);
  }
}
.animate__fadeInDown {
  animation-name: fadeInDown;
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
.animate__fadeInUp {
  animation-name: fadeInUp;
}
</style>
