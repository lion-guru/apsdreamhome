<?php
/**
 * Enhanced Properties Page - APS Dream Homes Pvt Ltd
 * Professional Property Listings with Filters and Search
 */

require_once 'includes/enhanced_universal_template.php';

// Database connection with error handling
try {
    define('INCLUDED_FROM_MAIN', true);
    require_once 'includes/db_connection.php';

    // Initialize variables
    $company_name = 'APS Dream Homes Pvt Ltd';
    $company_phone = '+91-9554000001';
    $company_email = 'info@apsdreamhomes.com';
    $properties = [];
    $property_types = [];
    $locations = [];

    // Database operations with fallback
    if ($pdo) {
        try {
            // Fetch company settings
            $stmt = $pdo->query("SELECT * FROM company_settings WHERE id = 1");
            if ($company_data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $company_name = $company_data['company_name'];
                $company_phone = $company_data['phone'];
                $company_email = $company_data['email'];
            }

            // Fetch all properties
            $stmt = $pdo->query("SELECT * FROM properties WHERE status = 'available' ORDER BY created_at DESC");
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch property types
            $stmt = $pdo->query("SELECT * FROM property_types ORDER BY name");
            $property_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch unique locations
            $stmt = $pdo->query("SELECT DISTINCT location FROM properties WHERE status = 'available' ORDER BY location");
            $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            // Database error - use fallback data
            error_log("Database query error: " . $e->getMessage());
        }
    }

    // Build properties page content
    $content = '
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">Our Properties</h1>
                    <p class="lead mb-4">Discover your dream home from our curated collection of premium properties across Gorakhpur and surrounding areas.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
                        <a href="contact_template.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-phone me-2"></i>Schedule a Visit
                        </a>
                        <a href="tel:' . htmlspecialchars($company_phone) . '" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-phone-alt me-2"></i>Call Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-custom">
                        <div class="card-body p-4">
                            <h5 class="mb-4">Filter Properties</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <select class="form-select" id="locationFilter">
                                        <option value="">All Locations</option>';
                                        foreach ($locations as $location) {
                                            $content .= '<option value="' . htmlspecialchars($location['location']) . '">' . htmlspecialchars($location['location']) . '</option>';
                                        }
                                        $content .= '
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="typeFilter">
                                        <option value="">All Types</option>';
                                        foreach ($property_types as $type) {
                                            $content .= '<option value="' . htmlspecialchars($type['name']) . '">' . htmlspecialchars($type['name']) . '</option>';
                                        }
                                        $content .= '
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="priceFilter">
                                        <option value="">All Price Ranges</option>
                                        <option value="0-5000000">Under ₹50 Lakh</option>
                                        <option value="5000000-10000000">₹50 Lakh - ₹1 Cr</option>
                                        <option value="10000000-20000000">₹1 Cr - ₹2 Cr</option>
                                        <option value="20000000-50000000">₹2 Cr - ₹5 Cr</option>
                                        <option value="50000000-100000000">Above ₹5 Cr</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary flex-fill" id="applyFilters">
                                            <i class="fas fa-search me-1"></i>Apply Filters
                                        </button>
                                        <button class="btn btn-outline-secondary" id="clearFilters">
                                            <i class="fas fa-times me-1"></i>Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Properties Grid Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title mb-0">Available Properties</h2>
                        <span class="text-muted" id="propertyCount">' . count($properties) . ' properties found</span>
                    </div>

                    <div id="propertiesGrid" class="row g-4">';

                    if (!empty($properties)) {
                        foreach ($properties as $property) {
                            $images = json_decode($property['images'], true);
                            $image = !empty($images) ? $images[0] : 'https://via.placeholder.com/400x300/667eea/ffffff?text=Property';
                            $type_badge = 'success';
                            $status_badge = 'primary';

                            $content .= '
                            <div class="col-lg-4 col-md-6 mb-4 property-item"
                                 data-location="' . htmlspecialchars($property['location']) . '"
                                 data-type="' . htmlspecialchars($property['type'] ?? 'Residential') . '"
                                 data-price="' . $property['price'] . '">
                                <div class="card h-100 shadow-custom property-card">
                                    <div class="position-relative">
                                        <img src="' . htmlspecialchars($image) . '"
                                             class="card-img-top"
                                             alt="' . htmlspecialchars($property['title']) . '"
                                             style="height: 250px; object-fit: cover;">
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-' . $status_badge . '">Available</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">' . htmlspecialchars($property['title']) . '</h5>
                                        <p class="card-text text-muted mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i>' . htmlspecialchars($property['location']) . '
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="fw-bold text-primary">₹' . number_format($property['price']) . '</span>
                                            <span class="text-muted">' . htmlspecialchars($property['area']) . ' sq ft</span>
                                        </div>
                                        <p class="card-text mb-3">' . htmlspecialchars(substr($property['description'], 0, 100)) . '...</p>
                                        <div class="d-flex gap-2">
                                            <a href="property_details.php?id=' . $property['id'] . '" class="btn btn-primary flex-fill">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>
                                            <a href="book_property.php?id=' . $property['id'] . '" class="btn btn-success">
                                                <i class="fas fa-calendar-alt me-1"></i>Book Visit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                    } else {
                        $content .= '
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                            <h4>No Properties Available</h4>
                            <p class="text-muted">Check back soon for new property listings.</p>
                            <a href="contact_template.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-phone me-2"></i>Contact Us for Updates
                            </a>
                        </div>';
                    }

                    $content .= '
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Property Types Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center mb-5">Property Types We Offer</h2>
                    <div class="row g-4">';

                    $default_types = [
                        ['name' => 'Residential Plot', 'icon' => 'fas fa-home', 'color' => 'primary', 'description' => 'Premium residential plots for building your dream home'],
                        ['name' => 'Apartment', 'icon' => 'fas fa-building', 'color' => 'success', 'description' => 'Modern apartments with world-class amenities'],
                        ['name' => 'Villa', 'icon' => 'fas fa-home', 'color' => 'info', 'description' => 'Luxurious villas with private gardens'],
                        ['name' => 'Commercial Shop', 'icon' => 'fas fa-store', 'color' => 'warning', 'description' => 'Commercial spaces for business'],
                        ['name' => 'Office Space', 'icon' => 'fas fa-briefcase', 'color' => 'secondary', 'description' => 'Professional office spaces']
                    ];

                    $types_to_show = !empty($property_types) ? $property_types : $default_types;

                    foreach ($types_to_show as $type) {
                        $content .= '
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <i class="' . $type['icon'] . ' fa-3x text-' . $type['color'] . '"></i>
                                    </div>
                                    <h5>' . htmlspecialchars($type['name']) . '</h5>
                                    <p class="mb-0">' . htmlspecialchars($type['description']) . '</p>
                                </div>
                            </div>
                        </div>';
                    }

                    $content .= '
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="card shadow-custom">
                        <div class="card-body p-5">
                            <h3 class="mb-4">Ready to Find Your Perfect Property?</h3>
                            <p class="mb-4">Contact our expert team today to explore the best properties that match your requirements and budget.</p>
                            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                                <a href="contact_template.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-phone me-2"></i>Contact Us Today
                                </a>
                                <a href="tel:' . htmlspecialchars($company_phone) . '" class="btn btn-success btn-lg">
                                    <i class="fas fa-phone-alt me-2"></i>Call: ' . htmlspecialchars($company_phone) . '
                                </a>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-phone-alt fa-2x text-primary mb-3"></i>
                                        <h5>Call Us</h5>
                                        <p class="mb-0">
                                            <a href="tel:' . htmlspecialchars($company_phone) . '" class="text-decoration-none">' . htmlspecialchars($company_phone) . '</a>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-envelope fa-2x text-success mb-3"></i>
                                        <h5>Email Us</h5>
                                        <p class="mb-0">
                                            <a href="mailto:' . htmlspecialchars($company_email) . '" class="text-decoration-none">' . htmlspecialchars($company_email) . '</a>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-clock fa-2x text-info mb-3"></i>
                                        <h5>Business Hours</h5>
                                        <p class="mb-0">Mon-Sat: 9:30 AM - 7:00 PM<br>Sun: 10:00 AM - 5:00 PM</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';

    // Add JavaScript for filtering
    $scripts = '
    <script>
        // Property filtering functionality
        document.getElementById("applyFilters").addEventListener("click", function() {
            const locationFilter = document.getElementById("locationFilter").value.toLowerCase();
            const typeFilter = document.getElementById("typeFilter").value.toLowerCase();
            const priceFilter = document.getElementById("priceFilter").value;

            const properties = document.querySelectorAll(".property-item");
            let visibleCount = 0;

            properties.forEach(function(property) {
                const location = property.dataset.location.toLowerCase();
                const type = property.dataset.type.toLowerCase();
                const price = parseInt(property.dataset.price);

                let showProperty = true;

                // Location filter
                if (locationFilter && !location.includes(locationFilter)) {
                    showProperty = false;
                }

                // Type filter
                if (typeFilter && !type.includes(typeFilter)) {
                    showProperty = false;
                }

                // Price filter
                if (priceFilter) {
                    const [min, max] = priceFilter.split("-").map(p => parseInt(p));
                    if (max && (price < min || price > max)) {
                        showProperty = false;
                    } else if (!max && price < min) {
                        showProperty = false;
                    }
                }

                if (showProperty) {
                    property.style.display = "block";
                    visibleCount++;
                } else {
                    property.style.display = "none";
                }
            });

            // Update count
            document.getElementById("propertyCount").textContent = visibleCount + " properties found";

            // Show message if no properties found
            if (visibleCount === 0) {
                if (!document.getElementById("noPropertiesMessage")) {
                    const message = document.createElement("div");
                    message.id = "noPropertiesMessage";
                    message.className = "col-12 text-center py-5";
                    message.innerHTML = `
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No Properties Match Your Criteria</h4>
                        <p class="text-muted">Try adjusting your filters or contact us for more options.</p>
                        <button class="btn btn-primary" onclick="clearAllFilters()">Clear All Filters</button>
                    `;
                    document.getElementById("propertiesGrid").appendChild(message);
                }
            } else {
                const message = document.getElementById("noPropertiesMessage");
                if (message) {
                    message.remove();
                }
            }
        });

        // Clear filters
        document.getElementById("clearFilters").addEventListener("click", function() {
            document.getElementById("locationFilter").value = "";
            document.getElementById("typeFilter").value = "";
            document.getElementById("priceFilter").value = "";

            const properties = document.querySelectorAll(".property-item");
            properties.forEach(function(property) {
                property.style.display = "block";
            });

            document.getElementById("propertyCount").textContent = properties.length + " properties found";

            const message = document.getElementById("noPropertiesMessage");
            if (message) {
                message.remove();
            }
        });

        function clearAllFilters() {
            document.getElementById("clearFilters").click();
        }

        // Initialize property count
        document.addEventListener("DOMContentLoaded", function() {
            const properties = document.querySelectorAll(".property-item");
            document.getElementById("propertyCount").textContent = properties.length + " properties found";
        });
    </script>';

    // Render page using enhanced template
    page($content, 'Properties - ' . htmlspecialchars($company_name), $scripts);

} catch (Exception $e) {
    // Error handling with fallback content
    $error_content = '
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="alert alert-warning">
                        <h4>Properties - APS Dream Homes Pvt Ltd</h4>
                        <p>Discover our premium property collection across Gorakhpur.</p>

                        <div class="row g-4 mt-4">
                            <div class="col-md-4">
                                <div class="card h-100 shadow-custom text-center">
                                    <div class="card-body p-4">
                                        <i class="fas fa-home fa-3x text-primary mb-3"></i>
                                        <h5>Residential Properties</h5>
                                        <p class="mb-0">Premium homes and plots</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 shadow-custom text-center">
                                    <div class="card-body p-4">
                                        <i class="fas fa-building fa-3x text-success mb-3"></i>
                                        <h5>Commercial Spaces</h5>
                                        <p class="mb-0">Office and retail spaces</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 shadow-custom text-center">
                                    <div class="card-body p-4">
                                        <i class="fas fa-map-marked-alt fa-3x text-info mb-3"></i>
                                        <h5>Land Development</h5>
                                        <p class="mb-0">Strategic land projects</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="index_template.php" class="btn btn-primary me-2">Homepage</a>
                            <a href="contact_template.php" class="btn btn-success me-2">Contact Us</a>
                            <a href="about_template.php" class="btn btn-info">About Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';

    page($error_content, 'Properties - APS Dream Homes Pvt Ltd');
}
?>
