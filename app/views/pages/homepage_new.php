<!-- Hero Section with Background Slider -->
<section class="hero-section">
    <!-- Background Slider -->
    <div class="hero-bg">
        <div class="hero-slider swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide" style="background-image: url('<?php echo ASSET_URL; ?>images/hero-1.jpg');"></div>
                <div class="swiper-slide" style="background-image: url('<?php echo ASSET_URL; ?>images/hero-2.jpg');"></div>
                <div class="swiper-slide" style="background-image: url('<?php echo ASSET_URL; ?>images/hero-3.jpg');"></div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
        <div class="hero-overlay"></div>
    </div>

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="hero-content" data-aos="fade-right">
                    <span class="badge bg-primary bg-opacity-20 text-white mb-3 px-4 py-2 rounded-pill d-inline-flex align-items-center" data-aos="fade-up" data-aos-delay="100">
                        <i class="fas fa-star me-2"></i>Trusted by 10,000+ Clients
                    </span>

                    <h1 class="hero-title text-white mb-4" data-aos="fade-up" data-aos-delay="150">
                        Your Dream Home <span class="text-primary">Awaits</span> in Gorakhpur
                    </h1>

                    <p class="hero-subtitle text-white-75 mb-5" data-aos="fade-up" data-aos-delay="200">
                        Discover exclusive properties with the most trusted real estate platform in Gorakhpur.
                        From luxury apartments to prime commercial spaces, we have it all.
                    </p>

                    <!-- Quick Actions -->
                    <div class="d-flex flex-wrap gap-3 mb-5" data-aos="fade-up" data-aos-delay="250">
                        <a href="#featured-properties" class="btn btn-primary btn-lg px-4 py-3 d-flex align-items-center">
                            <i class="fas fa-home me-2"></i>Explore Properties
                        </a>
                        <a href="#contact" class="btn btn-outline-light btn-lg px-4 py-3 d-flex align-items-center">
                            <i class="fas fa-phone-alt me-2"></i>Contact Agent
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="trust-indicators" data-aos="fade-up" data-aos-delay="300">
                        <div class="d-flex align-items-center flex-wrap gap-4">
                            <div class="d-flex align-items-center">
                                <div class="trust-avatar-group me-2">
                                    <img src="https://randomuser.me/api/portraits/women/32.jpg" class="trust-avatar" alt="Client">
                                    <img src="https://randomuser.me/api/portraits/men/44.jpg" class="trust-avatar" alt="Client">
                                    <img src="https://randomuser.me/api/portraits/women/68.jpg" class="trust-avatar" alt="Client">
                                </div>
                                <div class="trust-text">
                                    <div class="text-white fw-bold">5,000+</div>
                                    <small class="text-white-50">Happy Clients</small>
                                </div>
                            </div>
                            <div class="vr text-white-50 d-none d-md-block"></div>
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    <div class="text-warning">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <div class="text-white-50 small">4.8/5 (2,500+ Reviews)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Card -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="search-card">
                    <div class="search-header text-center mb-4">
                        <h3 class="search-title mb-2">
                            <i class="fas fa-search-location text-primary me-2"></i>
                            Find Your Dream Home
                        </h3>
                        <p class="search-subtitle text-muted">Search from our premium collection of properties</p>
                    </div>

                    <form action="<?php echo BASE_URL; ?>properties" method="GET" class="modern-search-form">
                        <div class="search-grid">
                            <!-- Property Type -->
                            <div class="search-group">
                                <label class="form-label fw-medium mb-2">
                                    <i class="fas fa-building me-2 text-primary"></i>Property Type
                                </label>
                                <select class="form-select" name="type">
                                    <option value="">All Types</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="villa">Villa</option>
                                    <option value="house">Independent House</option>
                                    <option value="plot">Plot/Land</option>
                                    <option value="commercial">Commercial</option>
                                </select>
                            </div>

                            <!-- Location -->
                            <div class="search-group">
                                <label class="form-label fw-medium mb-2">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Location
                                </label>
                                <select class="form-select" name="location" id="location-select">
                                    <option value="">All Locations</option>
                                    <?php if (isset($locations) && !empty($locations)): ?>
                                        <?php
                                        $current_state = '';
                                        foreach ($locations as $state => $cities):
                                            if (!empty($cities)):
                                                echo '<optgroup label="' . htmlspecialchars($state) . '">';
                                                foreach ($cities as $city):
                                        ?>
                                            <option value="<?php echo htmlspecialchars($city['city']); ?>">
                                                <?php echo htmlspecialchars($city['city']); ?>
                                            </option>
                                        <?php
                                                endforeach;
                                                echo '</optgroup>';
                                            endif;
                                        endforeach;
                                        ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="search-group">
                                <label class="form-label fw-medium mb-2">
                                    <i class="fas fa-indian-rupee-sign me-2 text-primary"></i>Price Range
                                </label>
                                <div class="price-range-slider mb-3">
                                    <div id="price-slider" class="mb-3"></div>
                                    <div class="d-flex justify-content-between">
                                        <input type="text" id="min-price" name="min_price" class="form-control form-control-sm w-45" placeholder="Min Price" readonly>
                                        <span class="mx-2 my-auto">-</span>
                                        <input type="text" id="max-price" name="max_price" class="form-control form-control-sm w-45" placeholder="Max Price" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="search-group">
                                <label class="search-label">
                                    <i class="fas fa-bed me-2"></i>Bedrooms
                                </label>
                                <select class="form-select modern-select" name="bedrooms">
                                    <option value="">🛏️ Any</option>
                                    <option value="1">1 BHK</option>
                                    <option value="2">2 BHK</option>
                                    <option value="3">3 BHK</option>
                                    <option value="4">4+ BHK</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-search w-100">
                            <i class="fas fa-search me-2"></i>🔍 Search Properties
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties Section -->
<section id="featured-properties" class="featured-properties-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title" data-aos="fade-up">
                <i class="fas fa-home me-3"></i>Featured Properties
            </h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Handpicked premium properties for discerning buyers
            </p>
        </div>

        <?php if (empty($featured_properties) || !isset($featured_properties)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon mb-4">
                            <i class="fas fa-home fa-4x text-muted"></i>
                        </div>
                        <h4 class="text-muted">No featured properties available</h4>
                        <p class="text-muted mb-4">Please check back later for new property listings or browse all available properties.</p>
                        <div class="empty-actions">
                            <a href="<?php echo BASE_URL; ?>properties" class="btn btn-primary me-3">
                                <i class="fas fa-search me-2"></i>View All Properties
                            </a>
                            <a href="#contact" class="btn btn-outline-primary">
                                <i class="fas fa-phone me-2"></i>Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="properties-grid">
                <?php foreach ($featured_properties as $index => $property): ?>
                    <div class="property-card" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100); ?>">
                        <div class="property-card-inner">
                            <div class="property-image-container">
                                <div class="property-badges">
                                    <span class="badge bg-success status-badge">
                                        <i class="fas fa-check-circle me-1"></i>Available
                                    </span>
                                </div>
                                <img src="<?php echo !empty($property['main_image']) ? htmlspecialchars($property['main_image']) : 'https://via.placeholder.com/800x600/667eea/ffffff?text=' . urlencode($property['title'] ?? 'Property'); ?>"
                                     alt="<?php echo htmlspecialchars($property['title'] ?? 'Property'); ?>"
                                     class="property-image"
                                     loading="lazy">
                                <div class="property-overlay">
                                    <a href="<?php echo BASE_URL; ?>property?id=<?php echo $property['id'] ?? 0; ?>" class="btn btn-light btn-sm">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>

                            <div class="property-content">
                                <div class="property-header">
                                    <h3 class="property-title">
                                        <a href="<?php echo BASE_URL; ?>property?id=<?php echo $property['id'] ?? 0; ?>">
                                            <?php echo htmlspecialchars($property['title'] ?? 'Untitled Property'); ?>
                                        </a>
                                    </h3>
                                    <div class="property-price">
                                        <?php
                                        $price = $property['price'] ?? 0;
                                        echo $price > 0 ? '₹' . number_format($price) : 'Price on Request';
                                        ?>
                                    </div>
                                </div>

                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <?php echo htmlspecialchars($property['address'] ?? 'Location not specified'); ?>
                                </div>

                                <div class="property-features">
                                    <div class="row g-2">
                                        <?php if (!empty($property['bedrooms'])): ?>
                                            <div class="col-4">
                                                <div class="feature-item">
                                                    <i class="fas fa-bed text-muted me-1"></i>
                                                    <span><?php echo $property['bedrooms']; ?> BR</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($property['bathrooms'])): ?>
                                            <div class="col-4">
                                                <div class="feature-item">
                                                    <i class="fas fa-bath text-muted me-1"></i>
                                                    <span><?php echo $property['bathrooms']; ?> BA</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($property['area_sqft'])): ?>
                                            <div class="col-4">
                                                <div class="feature-item">
                                                    <i class="fas fa-ruler-combined text-muted me-1"></i>
                                                    <span><?php echo number_format($property['area_sqft']); ?> sq.ft</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="property-agent d-flex align-items-center">
                                    <img src="<?php echo !empty($property['agent_image']) ? htmlspecialchars($property['agent_image']) : 'https://via.placeholder.com/40x40/667eea/ffffff?text=AG'; ?>"
                                         alt="<?php echo htmlspecialchars($property['agent_name'] ?? 'Agent'); ?>"
                                         class="agent-avatar me-2"
                                         onerror="this.src='https://via.placeholder.com/40x40/667eea/ffffff?text=AG'">
                                    <div class="agent-info">
                                        <div class="agent-name"><?php echo htmlspecialchars($property['agent_name'] ?? 'Agent'); ?></div>
                                        <div class="agent-role text-muted small">Property Agent</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Company Achievements Section -->
<section class="achievements-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="company-highlights-card">
                    <h3 class="mb-4">
                        <i class="fas fa-trophy text-primary me-2"></i>
                        Our Achievements
                    </h3>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="highlight-item">
                                <div class="highlight-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="highlight-number"><?php echo number_format($company_stats['properties_listed'] ?? 0); ?></div>
                                <div class="highlight-label">Properties Listed</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="highlight-item">
                                <div class="highlight-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="highlight-number"><?php echo number_format($company_stats['happy_customers'] ?? 0); ?></div>
                                <div class="highlight-label">Happy Customers</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="highlight-item">
                                <div class="highlight-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="highlight-number"><?php echo number_format($company_stats['expert_agents'] ?? 0); ?></div>
                                <div class="highlight-label">Expert Agents</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="highlight-item">
                                <div class="highlight-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="highlight-number"><?php echo number_format($company_stats['cities_covered'] ?? 0); ?></div>
                                <div class="highlight-label">Cities Covered</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="stats-container">
                    <h4 class="stats-heading">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Company Statistics
                    </h4>

                    <div class="stats-grid-primary">
                        <div class="stat-card-primary">
                            <div class="stat-icon-container">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-number"><?php echo number_format($company_stats['properties_sold'] ?? 0); ?></div>
                                <div class="stat-label">Properties Sold</div>
                                <div class="stat-description">Successfully completed transactions</div>
                            </div>
                        </div>

                        <div class="stat-card-primary">
                            <div class="stat-icon-container">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-number"><?php echo $company_stats['years_experience'] ?? 15; ?>+</div>
                                <div class="stat-label">Years Experience</div>
                                <div class="stat-description">Trusted industry expertise</div>
                            </div>
                        </div>

                        <div class="stat-card-primary">
                            <div class="stat-icon-container">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-number"><?php echo $company_stats['client_satisfaction'] ?? '4.8'; ?></div>
                                <div class="stat-label">Client Satisfaction</div>
                                <div class="stat-description">Average rating from customers</div>
                            </div>
                        </div>

                        <div class="stat-card-primary">
                            <div class="stat-icon-container">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-number"><?php echo number_format($company_stats['awards_won'] ?? 0); ?></div>
                                <div class="stat-label">Awards Won</div>
                                <div class="stat-description">Industry recognition received</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Timeline Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="company-timeline">
                    <h4 class="timeline-heading">
                        <i class="fas fa-history text-primary me-2"></i>
                        Our Journey
                    </h4>

                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="fas fa-flag"></i>
                            </div>
                            <div class="timeline-year">2009</div>
                            <div class="timeline-description">
                                Founded APS Dream Home with a vision to revolutionize real estate in Gorakhpur
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="timeline-year">2012</div>
                            <div class="timeline-description">
                                Expanded operations to multiple cities across Uttar Pradesh
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="timeline-year">2015</div>
                            <div class="timeline-description">
                                Reached milestone of 1,000+ satisfied customers
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="timeline-year">2018</div>
                            <div class="timeline-description">
                                Received "Best Real Estate Company" award from local business association
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div class="timeline-year">2022</div>
                            <div class="timeline-description">
                                Launched digital transformation with modern web platform
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="company-values">
                    <h4 class="values-heading">
                        <i class="fas fa-heart text-primary me-2"></i>
                        Our Values
                    </h4>

                    <div class="values-list">
                        <div class="value-item">
                            <i class="fas fa-handshake"></i>
                            <div>
                                <strong>Trust & Integrity</strong>
                                <small class="d-block text-muted">Building lasting relationships through honesty</small>
                            </div>
                        </div>

                        <div class="value-item">
                            <i class="fas fa-bullseye"></i>
                            <div>
                                <strong>Excellence</strong>
                                <small class="d-block text-muted">Delivering exceptional service quality</small>
                            </div>
                        </div>

                        <div class="value-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <strong>Customer First</strong>
                                <small class="d-block text-muted">Putting client needs above everything</small>
                            </div>
                        </div>

                        <div class="value-item">
                            <i class="fas fa-lightbulb"></i>
                            <div>
                                <strong>Innovation</strong>
                                <small class="d-block text-muted">Embracing technology for better solutions</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h3 class="text-white mb-3">
                    <i class="fas fa-rocket me-2"></i>
                    Ready to Find Your Dream Home?
                </h3>
                <p class="text-white-50 mb-0">
                    Join thousands of satisfied customers who found their perfect property with APS Dream Home.
                    Start your journey today!
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?php echo BASE_URL; ?>contact" class="btn btn-light btn-lg px-4 py-3">
                    <i class="fas fa-phone-alt me-2"></i>Get Started Today
                </a>
            </div>
        </div>
    </div>
</section>
