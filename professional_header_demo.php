<?php
// Professional Header Demo Page for APS Dream Home
$page_title = 'Professional Header Demo - APS Dream Home';
$page_description = 'Showcase of the new professional real estate header design';
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/apsdreamhomefinal';
$current_url = $base_url . $_SERVER['REQUEST_URI'];

// Include the professional header
require_once 'includes/templates/professional_header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 text-white py-5" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #1e40af 100%); min-height: 50vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-3 fw-bold mb-4" data-aos="fade-up">
                    <i class="fas fa-home me-3"></i>APS Dream Home
                </h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="200">
                    Professional Real Estate Platform with Advanced Features
                </p>
                <div class="row g-3 justify-content-center">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-lg" data-aos="fade-up" data-aos-delay="400">
                            <div class="card-body text-center">
                                <i class="fas fa-building fa-3x text-primary mb-3"></i>
                                <h5>500+ Projects</h5>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-4 fw-bold text-dark">🎯 Premium Professional Header</h2>
                <p class="lead text-muted">Ultimate real estate navigation with mega menus, premium styling, and advanced functionality</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-palette fa-3x text-primary"></i>
                        </div>
                        <h5>Premium Brand Design</h5>
                        <p>• Logo with gradient text company name</p>
                        <p>• Premium hover effects and animations</p>
                        <p>• Professional typography with subtitle</p>
                        <p>• Enhanced visual hierarchy</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
{{ ... }}
                        <p>Fully responsive design that works perfectly on all devices and screen sizes</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-search fa-3x text-info"></i>
                        </div>
                        <h5>Advanced Search</h5>
                        <p>Integrated search functionality for properties with elegant styling</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="600">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-sitemap fa-3x text-warning"></i>
                        </div>
                        <h5>Premium Mega Menus</h5>
                        <p>• Properties mega menu with stats sidebar</p>
                        <p>• Projects mega menu with featured projects</p>
                        <p>• Premium styling with gradients and shadows</p>
                        <p>• Smooth animations and hover effects</p>
                        <p>• Professional layout with organized sections</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-5">
                <h2 class="display-5 fw-bold text-center">Ready to Use Professional Header?</h2>
                <p class="lead mb-4">This header is perfectly designed for your comprehensive real estate platform with all modern features and professional styling.</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="/" class="btn btn-light btn-lg px-5">
                        <i class="fas fa-home me-2"></i>View Homepage
                    </a>
                    <a href="properties" class="btn btn-outline-light btn-lg px-5">
                        <i class="fas fa-building me-2"></i>Browse Properties
                    </a>
                    <a href="contact" class="btn btn-warning btn-lg px-5">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.feature-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
}
</style>

<?php
// Include Bootstrap JS for animations
echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
echo '<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>';
echo '<script>AOS.init();</script>';
?>

</body>
</html>
