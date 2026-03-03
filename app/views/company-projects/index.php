<?php
$page_title = 'Company Projects - APS Dream Home';
$page_description = 'Explore our completed and ongoing projects across Gorakhpur, Lucknow, and Uttar Pradesh.';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-5">
    <div class="container">
        <!-- Hero Section -->
        <div class="projects-hero text-center mb-5">
            <h1 class="display-4 fw-bold text-primary mb-3">
                <i class="fas fa-building me-3"></i>Our Projects
            </h1>
            <p class="lead text-muted">
                Explore our completed and ongoing projects across Gorakhpur, Lucknow, and Uttar Pradesh
            </p>
        </div>

        <!-- Project Stats -->
        <div class="project-stats mb-5">
            <div class="row g-4">
                <div class="col-md-2 col-6 text-center">
                    <div class="stat-card">
                        <h3 class="text-primary fw-bold">105+</h3>
                        <p class="text-muted mb-0">Total Projects</p>
                    </div>
                </div>
                <div class="col-md-2 col-6 text-center">
                    <div class="stat-card">
                        <h3 class="text-success fw-bold">78</h3>
                        <p class="text-muted mb-0">Completed</p>
                    </div>
                </div>
                <div class="col-md-2 col-6 text-center">
                    <div class="stat-card">
                        <h3 class="text-warning fw-bold">15</h3>
                        <p class="text-muted mb-0">Ongoing</p>
                    </div>
                </div>
                <div class="col-md-2 col-6 text-center">
                    <div class="stat-card">
                        <h3 class="text-info fw-bold">2000+</h3>
                        <p class="text-muted mb-0">Happy Families</p>
                    </div>
                </div>
                <div class="col-md-2 col-6 text-center">
                    <div class="stat-card">
                        <h3 class="text-danger fw-bold">500+</h3>
                        <p class="text-muted mb-0">Acres</p>
                    </div>
                </div>
                <div class="col-md-2 col-6 text-center">
                    <div class="stat-card">
                        <h3 class="text-secondary fw-bold">10+</h3>
                        <p class="text-muted mb-0">Years</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Categories -->
        <div class="project-categories mb-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="category-card text-center p-4">
                        <div class="category-icon mb-3">
                            <i class="fas fa-home fa-3x text-primary"></i>
                        </div>
                        <h4>Residential Projects</h4>
                        <p class="text-muted">Luxury apartments, villas, and residential complexes</p>
                        <h5 class="text-primary">45+ Projects</h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="category-card text-center p-4">
                        <div class="category-icon mb-3">
                            <i class="fas fa-building fa-3x text-success"></i>
                        </div>
                        <h4>Commercial Projects</h4>
                        <p class="text-muted">Office spaces, retail complexes, and commercial buildings</p>
                        <h5 class="text-success">28+ Projects</h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="category-card text-center p-4">
                        <div class="category-icon mb-3">
                            <i class="fas fa-map fa-3x text-warning"></i>
                        </div>
                        <h4>Plots & Land</h4>
                        <p class="text-muted">Residential and commercial plots with clear titles</p>
                        <h5 class="text-warning">32+ Projects</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Projects -->
        <div class="featured-projects">
            <h2 class="text-center mb-5">Featured Projects</h2>
            <div class="row g-4">
                <!-- Project 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="project-card">
                        <div class="project-image">
                            <img src="https://via.placeholder.com/400x250/667eea/ffffff?text=APS+Heights" class="img-fluid" alt="APS Heights">
                            <div class="project-status">
                                <span class="badge bg-success">Completed</span>
                            </div>
                        </div>
                        <div class="project-content p-4">
                            <h4>APS Heights</h4>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>Gorakhpur - Kunraghat
                            </p>
                            <p class="mb-3">Premium residential apartments with modern amenities</p>
                            <div class="project-details mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted">Units:</small>
                                        <strong>120 Units</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Price:</small>
                                        <strong>₹45L - ₹85L</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="project-highlights mb-3">
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-light text-dark">24/7 Security</span>
                                    <span class="badge bg-light text-dark">Power Backup</span>
                                    <span class="badge bg-light text-dark">Children Play Area</span>
                                    <span class="badge bg-light text-dark">Gym</span>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100">
                                <i class="fas fa-info-circle me-2"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Project 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="project-card">
                        <div class="project-image">
                            <img src="https://via.placeholder.com/400x250/28a745/ffffff?text=Dream+City+Plaza" class="img-fluid" alt="Dream City Plaza">
                            <div class="project-status">
                                <span class="badge bg-warning">Ongoing</span>
                            </div>
                        </div>
                        <div class="project-content p-4">
                            <h4>Dream City Plaza</h4>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>Gorakhpur - City Center
                            </p>
                            <p class="mb-3">Modern commercial complex with retail spaces and offices</p>
                            <div class="project-details mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted">Units:</small>
                                        <strong>85 Units</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Price:</small>
                                        <strong>₹25L - ₹2Cr</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="project-highlights mb-3">
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-light text-dark">Prime Location</span>
                                    <span class="badge bg-light text-dark">Modern Architecture</span>
                                    <span class="badge bg-light text-dark">Parking Facility</span>
                                    <span class="badge bg-light text-dark">Food Court</span>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100">
                                <i class="fas fa-info-circle me-2"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Project 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="project-card">
                        <div class="project-image">
                            <img src="https://via.placeholder.com/400x250/ffc107/000000?text=Green+Valley+Enclave" class="img-fluid" alt="Green Valley Enclave">
                            <div class="project-status">
                                <span class="badge bg-info">Available</span>
                            </div>
                        </div>
                        <div class="project-content p-4">
                            <h4>Green Valley Enclave</h4>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>Gorakhpur - NH-28
                            </p>
                            <p class="mb-3">Premium residential plots with all infrastructure</p>
                            <div class="project-details mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted">Plots:</small>
                                        <strong>200 Plots</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Size:</small>
                                        <strong>1000-5000 sqft</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="project-highlights mb-3">
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-light text-dark">Clear Titles</span>
                                    <span class="badge bg-light text-dark">Gated Community</span>
                                    <span class="badge bg-light text-dark">Wide Roads</span>
                                    <span class="badge bg-light text-dark">Underground Utilities</span>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100">
                                <i class="fas fa-info-circle me-2"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Achievement Highlights -->
        <div class="achievement-highlights mt-5">
            <h2 class="text-center mb-5">Our Achievements</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="achievement-card text-center p-4">
                        <div class="achievement-icon mb-3">
                            <i class="fas fa-trophy fa-3x text-warning"></i>
                        </div>
                        <h5>Best Real Estate Developer 2025</h5>
                        <p class="text-muted small">Awarded by Uttar Pradesh Real Estate Council</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="achievement-card text-center p-4">
                        <div class="achievement-icon mb-3">
                            <i class="fas fa-certificate fa-3x text-success"></i>
                        </div>
                        <h5>RERA Certified Projects</h5>
                        <p class="text-muted small">All projects are RERA registered and compliant</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="achievement-card text-center p-4">
                        <div class="achievement-icon mb-3">
                            <i class="fas fa-leaf fa-3x text-info"></i>
                        </div>
                        <h5>Green Building Initiative</h5>
                        <p class="text-muted small">Sustainable and eco-friendly construction practices</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="achievement-card text-center p-4">
                        <div class="achievement-icon mb-3">
                            <i class="fas fa-shield-alt fa-3x text-danger"></i>
                        </div>
                        <h5>100% Legal Clearance</h5>
                        <p class="text-muted small">All projects have complete legal documentation</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section mt-5">
            <div class="text-center p-5 bg-primary text-white rounded">
                <h2 class="mb-3">Interested in Our Projects?</h2>
                <p class="mb-4">Get in touch with our team to explore our current and upcoming projects</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= BASE_URL; ?>contact" class="btn btn-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="<?= BASE_URL; ?>properties" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.project-stats .stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    transition: transform 0.3s ease;
}

.project-stats .stat-card:hover {
    transform: translateY(-5px);
}

.category-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.project-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.project-image {
    position: relative;
    overflow: hidden;
}

.project-image img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.project-card:hover .project-image img {
    transform: scale(1.05);
}

.project-status {
    position: absolute;
    top: 15px;
    right: 15px;
}

.achievement-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.achievement-card:hover {
    transform: translateY(-5px);
}

.cta-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.badge {
    font-size: 0.75rem;
}
</style>
