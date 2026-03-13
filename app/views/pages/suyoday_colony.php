<?php

/**
 * Suyoday Colony Project Page - APS Dream Home
 */

$layout = 'layouts/base';
$page_title = $page_title ?? 'Suyoday Colony - APS Dream Home';
$page_description = $page_description ?? 'Premium residential plots in Gorakhpur with modern infrastructure';
?>

<!-- Project Hero -->
<section class="project-hero-section section-padding bg-gradient-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">Suyoday Colony</h1>
        <p class="lead mb-0">Premium Residential Plots at Gorakhpur</p>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/company/projects">Projects</a></li>
            <li class="breadcrumb-item active">Suyoday Colony</li>
        </ol>
    </div>
</nav>

<div class="full-row bg-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0" data-aos="fade-right">
                <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="img-fluid rounded-4 shadow-lg" alt="Suyoday Colony Overview">
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <h2 class="text-primary double-down-line mb-4">OVERVIEW - Suyoday Colony</h2>
                <p class="lead"><b>SURYODAY COLONY - Premium Residential Project in Gorakhpur</b></p>
                <p>APS Dream Homes presents Suyoday Colony, a premium residential plot project located in the prime area of Gorakhpur. This project offers meticulously planned residential plots with modern infrastructure, excellent connectivity, and all essential amenities for comfortable living.</p>
                <p class="fw-bold text-dark">Our project is strategically located with easy access to educational institutions, healthcare facilities, shopping centers, and transportation hubs, making it an ideal choice for your dream home.</p>
            </div>
        </div>
    </div>
</div>

<!-- Project Details -->
<div class="full-row bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h2 class="text-primary double-down-line">Project Details</h2>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-map-marked-alt fa-3x text-primary"></i>
                    </div>
                    <h5>Location</h5>
                    <p class="text-muted">Gorakhpur, Uttar Pradesh</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-home fa-3x text-success"></i>
                    </div>
                    <h5>Project Type</h5>
                    <p class="text-muted">Residential Plots</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-ruler-combined fa-3x text-info"></i>
                    </div>
                    <h5>Total Area</h5>
                    <p class="text-muted">15 Acres</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-th fa-3x text-warning"></i>
                    </div>
                    <h5>Total Plots</h5>
                    <p class="text-muted">200+ Plots</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-rupee-sign fa-3x text-danger"></i>
                    </div>
                    <h5>Starting Price</h5>
                    <p class="text-muted">₹7.5 Lakhs</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-hammer fa-3x text-dark"></i>
                    </div>
                    <h5>Status</h5>
                    <p class="text-muted">Ongoing</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-calendar-alt fa-3x text-primary"></i>
                    </div>
                    <h5>Possession</h5>
                    <p class="text-muted">Dec 2025</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-certificate fa-3x text-success"></i>
                    </div>
                    <h5>RERA Approved</h5>
                    <p class="text-muted">Yes</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Amenities Section -->
<div class="full-row bg-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h2 class="text-primary double-down-line">Amenities & Features</h2>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="amenity-item">
                    <div class="amenity-icon">
                        <i class="fas fa-road"></i>
                    </div>
                    <h5>Wide Roads</h5>
                    <p>30 feet and 40 feet wide internal roads with proper drainage</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="amenity-item">
                    <div class="amenity-icon">
                        <i class="fas fa-tint"></i>
                    </div>
                    <h5>Water Supply</h5>
                    <p>24x7 water supply with underground water tanks</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="amenity-item">
                    <div class="amenity-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h5>Electricity</h5>
                    <p>Underground electrical wiring with street lights</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="amenity-item">
                    <div class="amenity-icon">
                        <i class="fas fa-tree"></i>
                    </div>
                    <h5>Green Belt</h5>
                    <p>Lush green parks and tree plantation for fresh environment</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="amenity-item">
                    <div class="amenity-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5>Security</h5>
                    <p>24x7 security with boundary walls and gated community</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="amenity-item">
                    <div class="amenity-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h5>CCTV Surveillance</h5>
                    <p>CCTV cameras at important locations for enhanced security</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Section -->
<div class="full-row bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h2 class="text-primary double-down-line">Project Gallery</h2>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="gallery-item">
                    <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="img-fluid rounded" alt="Suyoday Colony View 1">
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="gallery-item">
                    <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday1.jpeg" class="img-fluid rounded" alt="Suyoday Colony View 2">
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="gallery-item">
                    <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday2.jpeg" class="img-fluid rounded" alt="Suyoday Colony View 3">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="full-row bg-primary py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center text-white">
                <h2 class="mb-4">Interested in Suyoday Colony?</h2>
                <p class="lead mb-4">Book your dream plot today and secure your future!</p>
                <div class="cta-buttons">
                    <a href="<?= BASE_URL ?>/contact" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="<?= BASE_URL ?>/register" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Register Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.project-hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.double-down-line {
    position: relative;
    padding-bottom: 15px;
}

.double-down-line::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: #007bff;
}

.feature-box {
    padding: 30px 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.feature-box:hover {
    transform: translateY(-5px);
}

.amenity-item {
    display: flex;
    align-items: flex-start;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.amenity-icon {
    min-width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: #007bff;
}

.gallery-item img {
    transition: transform 0.3s ease;
}

.gallery-item:hover img {
    transform: scale(1.05);
}
</style>
