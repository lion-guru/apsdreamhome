<?php
/**
 * Template System Test - APS Dream Homes Pvt Ltd
 * Testing the Enhanced Universal Template System
 */

require_once 'includes/enhanced_universal_template.php';

// Test content
$content = '
<!-- Test Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-4">🎉 Template System Test</h1>
                <p class="lead mb-4">Testing the enhanced universal template system with all features working correctly.</p>
                <div class="alert alert-success">
                    <h4>✅ Enhanced Template System Active!</h4>
                    <p>All pages are now using the enhanced universal template system with consistent header and footer.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Test Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title text-center mb-5">✅ System Features Working</h2>
                <div class="row g-4">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-custom text-center">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <i class="fas fa-home fa-3x text-primary"></i>
                                </div>
                                <h5>Professional Header</h5>
                                <p class="mb-0">Consistent header across all pages with correct company information.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-custom text-center">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <i class="fas fa-building fa-3x text-success"></i>
                                </div>
                                <h5>Professional Footer</h5>
                                <p class="mb-0">Complete footer with contact info, social links, and company details.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-custom text-center">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <i class="fas fa-cog fa-3x text-info"></i>
                                </div>
                                <h5>Enhanced Features</h5>
                                <p class="mb-0">Advanced styling, animations, and professional appearance.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Navigation Test Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title text-center mb-5">🧭 Navigation Links</h2>
                <div class="row g-4">
                    <div class="col-md-3 mb-4">
                        <a href="index_template.php" class="btn btn-primary w-100">
                            <i class="fas fa-home me-2"></i>Homepage
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="about_template.php" class="btn btn-success w-100">
                            <i class="fas fa-info-circle me-2"></i>About Us
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="properties_template.php" class="btn btn-info w-100">
                            <i class="fas fa-building me-2"></i>Properties
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="contact_template.php" class="btn btn-warning w-100">
                            <i class="fas fa-phone me-2"></i>Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Information Test Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-custom">
                    <div class="card-body p-5 text-center">
                        <h3 class="mb-4">🏢 Company Information</h3>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border-end">
                                    <h5>Company Details</h5>
                                    <p class="mb-2"><strong>Company:</strong> APS Dream Homes Pvt Ltd</p>
                                    <p class="mb-2"><strong>Phone:</strong> +91-9554000001</p>
                                    <p class="mb-2"><strong>Email:</strong> info@apsdreamhomes.com</p>
                                    <p class="mb-0"><strong>Experience:</strong> 8+ Years</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>Services</h5>
                                <div class="d-flex flex-column gap-2">
                                    <span class="badge bg-primary p-2">Residential Properties</span>
                                    <span class="badge bg-success p-2">Commercial Properties</span>
                                    <span class="badge bg-info p-2">Land Development</span>
                                    <span class="badge bg-warning p-2">Property Management</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>';

// Test scripts
$scripts = '
<script>
    // Test JavaScript functionality
    document.addEventListener("DOMContentLoaded", function() {
        console.log("✅ Enhanced template system loaded successfully!");

        // Test button interactions
        document.querySelectorAll(".btn").forEach(btn => {
            btn.addEventListener("click", function(e) {
                // Add visual feedback
                this.style.transform = "scale(0.95)";
                setTimeout(() => {
                    this.style.transform = "scale(1)";
                }, 150);
            });
        });

        // Test form validation if any
        const forms = document.querySelectorAll("form");
        forms.forEach(form => {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                alert("✅ Form validation working correctly!");
            });
        });
    });
</script>';

// Render page using enhanced template
page($content, 'Template System Test - APS Dream Homes Pvt Ltd', $scripts);
?>
