<?php
/**
 * Enhanced About Page - APS Dream Homes Pvt Ltd
 * Professional Company Information Page with Story and Services
 */

require_once 'includes/enhanced_universal_template.php';

// Define security constant for database connection
if (!defined('INCLUDED_FROM_MAIN')) {
    define('INCLUDED_FROM_MAIN', true);
}

    // Initialize variables
    $company_name = 'APS Dream Homes Pvt Ltd';
    $company_phone = '+91-9554000001';
    $company_email = 'info@apsdreamhomes.com';
    $company_address = '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008';
    $total_properties = 1000;
    $experience_years = 8;

    // Database operations with fallback
    if ($pdo) {
        try {
            // Fetch company settings
            $stmt = $pdo->query("SELECT * FROM company_settings WHERE id = 1");
            if ($company_data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $company_name = $company_data['company_name'];
                $company_phone = $company_data['phone'];
                $company_email = $company_data['email'];
                $company_address = $company_data['address'];
            }

            // Fetch property statistics
            $stmt = $pdo->query("SELECT COUNT(*) as total_properties FROM properties");
            if ($stats_data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $total_properties = $stats_data['total_properties'];
            }

        } catch (Exception $e) {
            // Database error - use fallback data
            error_log("Database query error: " . $e->getMessage());
        }
    }

    // Build about page content
    $content = '
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">About ' . htmlspecialchars($company_name) . '</h1>
                    <p class="lead mb-4">Leading real estate developer in Gorakhpur with ' . $experience_years . '+ years of excellence in property development and customer satisfaction.</p>
                    <div class="row g-4 mt-5">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h2 class="text-primary fw-bold">' . $experience_years . '+</h2>
                                <p class="mb-0">Years of Excellence</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h2 class="text-primary fw-bold">500+</h2>
                                <p class="mb-0">Happy Families</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h2 class="text-primary fw-bold">' . number_format($total_properties) . '+</h2>
                                <p class="mb-0">Properties Delivered</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h2 class="text-primary fw-bold">15+</h2>
                                <p class="mb-0">Projects Completed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Journey Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center mb-5">Our Journey</h2>
                    <div class="card shadow-custom">
                        <div class="card-body p-5">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h3 class="mb-4">From Vision to Reality</h3>
                                    <p class="mb-4">Founded with a vision to transform the real estate landscape of Gorakhpur, ' . htmlspecialchars($company_name) . ' has been a pioneer in delivering quality construction and exceptional customer service.</p>
                                    <p class="mb-4">Our commitment to excellence, transparency, and customer satisfaction has made us one of the most trusted names in the real estate industry. We believe in building not just homes, but lifelong relationships with our customers.</p>

                                    <h4 class="mb-3">Our Core Values</h4>
                                    <div class="d-flex flex-wrap gap-3">
                                        <span class="badge bg-primary fs-6 p-2">
                                            <i class="fas fa-shield-alt me-1"></i>Quality First
                                        </span>
                                        <span class="badge bg-success fs-6 p-2">
                                            <i class="fas fa-clock me-1"></i>Timely Delivery
                                        </span>
                                        <span class="badge bg-info fs-6 p-2">
                                            <i class="fas fa-users me-1"></i>Customer Centric
                                        </span>
                                        <span class="badge bg-warning fs-6 p-2">
                                            <i class="fas fa-chart-line me-1"></i>Innovation
                                        </span>
                                        <span class="badge bg-danger fs-6 p-2">
                                            <i class="fas fa-handshake me-1"></i>Transparency
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <img src="assets/images/about-hero.jpg"
                                         alt="Our Journey - ' . htmlspecialchars($company_name) . '"
                                         class="img-fluid rounded shadow"
                                         onerror="this.src=\'https://via.placeholder.com/600x400/667eea/ffffff?text=Our+Journey\'"
                                         style="width: 100%; height: 400px; object-fit: cover;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision & Mission Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 shadow-custom">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <i class="fas fa-eye fa-3x text-primary"></i>
                            </div>
                            <h3 class="mb-4">Our Vision</h3>
                            <p class="mb-0">To be the most trusted and respected real estate developer in Uttar Pradesh, known for our commitment to quality, innovation, and customer satisfaction. We envision creating living spaces that enhance the quality of life for generations to come.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 shadow-custom">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <i class="fas fa-bullseye fa-3x text-success"></i>
                            </div>
                            <h3 class="mb-4">Our Mission</h3>
                            <p class="mb-0">To create exceptional living spaces that exceed customer expectations while maintaining the highest standards of quality, transparency, and ethical business practices. We are committed to delivering value to our customers, employees, and stakeholders.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center mb-5">Our Comprehensive Services</h2>
                    <div class="row g-4">
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-custom">
                                <div class="card-body p-4 text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-home fa-2x text-primary"></i>
                                    </div>
                                    <h5>Residential Properties</h5>
                                    <p class="mb-0">Premium apartments, villas, and residential plots designed for modern living with world-class amenities and contemporary architecture.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-custom">
                                <div class="card-body p-4 text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-building fa-2x text-success"></i>
                                    </div>
                                    <h5>Commercial Properties</h5>
                                    <p class="mb-0">Office spaces, retail shops, and commercial complexes designed for business growth and success with strategic locations.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-custom">
                                <div class="card-body p-4 text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-map-marked-alt fa-2x text-info"></i>
                                    </div>
                                    <h5>Land Development</h5>
                                    <p class="mb-0">Strategic land acquisition and development services for future residential and commercial projects with growth potential.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center mb-5">Why Choose ' . htmlspecialchars($company_name) . '?</h2>
                    <div class="row g-4">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-custom">
                                <div class="card-body p-4">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-award fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h5>Proven Track Record</h5>
                                            <p class="mb-0">' . $experience_years . '+ years of successful project delivery with 100% customer satisfaction and zero disputes.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-custom">
                                <div class="card-body p-4">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-tools fa-2x text-success"></i>
                                        </div>
                                        <div>
                                            <h5>Quality Construction</h5>
                                            <p class="mb-0">Using premium materials and modern construction techniques to ensure durability and longevity of all our projects.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-custom">
                                <div class="card-body p-4">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-handshake fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <h5>Transparent Pricing</h5>
                                            <p class="mb-0">No hidden costs, clear pricing structure, and flexible payment options to suit every budget and requirement.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-custom">
                                <div class="card-body p-4">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-headset fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <h5>After-Sales Service</h5>
                                            <p class="mb-0">Comprehensive after-sales support and maintenance services for complete peace of mind and customer satisfaction.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Company Stats Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center mb-5">Our Achievements</h2>
                    <div class="row g-4">
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <h3 class="text-primary mb-2">' . $experience_years . '+ Years</h3>
                                    <p class="mb-0">Industry Experience</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <h3 class="text-success mb-2">' . number_format($total_properties) . '+</h3>
                                    <p class="mb-0">Properties Delivered</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <h3 class="text-info mb-2">500+</h3>
                                    <p class="mb-0">Happy Customers</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <h3 class="text-warning mb-2">15+</h3>
                                    <p class="mb-0">Projects Completed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="card shadow-custom">
                        <div class="card-body p-5">
                            <h3 class="mb-4">Ready to Learn More About Our Services?</h3>
                            <p class="mb-4">Contact our team to discover how we can help you find your perfect property or develop your real estate project.</p>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="contact_template.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-phone me-2"></i>Contact Us Today
                                </a>
                                <a href="tel:' . htmlspecialchars($company_phone) . '" class="btn btn-success btn-lg">
                                    <i class="fas fa-phone-alt me-2"></i>Call: ' . htmlspecialchars($company_phone) . '
                                </a>
                            </div>
                            <p class="mt-3 mb-0 text-muted">
                                <i class="fas fa-envelope me-2"></i>' . htmlspecialchars($company_email) . ' |
                                <i class="fas fa-clock me-2"></i>Mon-Sat: 9:30 AM - 7:00 PM
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';

    // Render page using enhanced template
    page($content, 'About Us - ' . htmlspecialchars($company_name));

} catch (Exception $e) {
    // Comprehensive error handling with fallback content
    $error_content = '
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="alert alert-warning">
                        <h4>About APS Dream Homes Pvt Ltd</h4>
                        <p>We are a leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.</p>

                        <div class="row g-4 mt-4">
                            <div class="col-md-3 text-center">
                                <h3 class="text-primary">8+</h3>
                                <small>Years Experience</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <h3 class="text-primary">500+</h3>
                                <small>Happy Families</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <h3 class="text-primary">1000+</h3>
                                <small>Properties Delivered</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <h3 class="text-primary">15+</h3>
                                <small>Projects Completed</small>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="index_template.php" class="btn btn-primary me-2">Homepage</a>
                            <a href="contact_template.php" class="btn btn-success me-2">Contact Us</a>
                            <a href="properties_template.php" class="btn btn-info">Properties</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';

    page($error_content, 'About Us - APS Dream Homes Pvt Ltd');
}
?>
