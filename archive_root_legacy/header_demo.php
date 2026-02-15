<?php
/**
 * Header Demo - Showcasing the new modern header design
 */

require_once 'includes/universal_template.php';

// Create a demo page to showcase the new header
$content = '
<div class="container-fluid py-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 fw-bold text-primary mb-3">🎨 New Modern Header Design</h1>
            <p class="lead text-muted">Professional, responsive, and feature-rich navigation system</p>
        </div>
    </div>

    <!-- Features Showcase -->
    <div class="row g-4">
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-mobile-alt text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title text-primary">Responsive Design</h5>
                    <p class="card-text text-muted">Optimized for all devices with mobile-first approach and touch-friendly interface.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-search text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title text-primary">Advanced Search</h5>
                    <p class="card-text text-muted">Built-in property search modal with filters for type, location, price, and more.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-user-circle text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title text-primary">User Experience</h5>
                    <p class="card-text text-muted">Enhanced user dropdowns, smooth animations, and intuitive navigation flow.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-home text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title text-primary">Professional Branding</h5>
                    <p class="card-text text-muted">Modern logo design with gradient effects and professional color scheme.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-arrow-up text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title text-primary">Back to Top</h5>
                    <p class="card-text text-muted">Smooth scroll back-to-top button with elegant animations and positioning.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-share-alt text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title text-primary">Social Integration</h5>
                    <p class="card-text text-muted">Top bar with social media links and contact information for better engagement.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Features Detail -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Header Features</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="text-primary"><i class="fas fa-check-circle me-2"></i>Top Bar</h6>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-phone text-success me-2"></i>Contact information display</li>
                                <li><i class="fas fa-envelope text-success me-2"></i>Email and social links</li>
                                <li><i class="fas fa-clock text-success me-2"></i>Business hours</li>
                                <li><i class="fas fa-mobile-alt text-success me-2"></i>Mobile responsive</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary"><i class="fas fa-check-circle me-2"></i>Main Navigation</h6>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-home text-success me-2"></i>Professional logo design</li>
                                <li><i class="fas fa-search text-success me-2"></i>Property search modal</li>
                                <li><i class="fas fa-user text-success me-2"></i>User account dropdown</li>
                                <li><i class="fas fa-arrow-up text-success me-2"></i>Back to top button</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row mt-5">
        <div class="col-12 text-center">
            <div class="p-4 bg-light rounded-3">
                <h4 class="text-primary mb-3">🚀 Ready for Production!</h4>
                <p class="text-muted mb-4">The new header design is now live and ready for deployment. All features tested and optimized.</p>
                <a href="index.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-home me-2"></i>View Homepage
                </a>
                <a href="properties.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-building me-2"></i>Browse Properties
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.hover-primary:hover {
    color: var(--primary-color) !important;
    transition: color 0.3s ease;
}

.back-to-top {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    box-shadow: 0 4px 15px rgba(78, 115, 223, 0.4);
    transition: all 0.3s ease;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.back-to-top.show {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(78, 115, 223, 0.6);
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }

    .btn-lg {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>
';

// Use the template to display the demo
template('default')->setTitle('Header Demo - APS Dream Home')->render($content);
?>
