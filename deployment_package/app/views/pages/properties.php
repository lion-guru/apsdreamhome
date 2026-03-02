<?php
// Breadcrumb
?>
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Properties</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Enhanced Hero Section -->
<section class="hero-properties">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content animate-fade-up">
                    <h1 class="hero-title">
                        Find Your
                        <span class="text-warning">Perfect Property</span>
                    </h1>
                    <p class="hero-subtitle">
                        Discover amazing properties in Gorakhpur, Lucknow and surrounding areas.
                        From luxury apartments to spacious villas, we have something for everyone.
                    </p>

                    <!-- Quick Stats -->
                    <div class="stats-info">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="fw-bold text-warning fs-3"><?php echo number_format($total_properties ?? 0); ?>+</div>
                                    <div class="text-white-75">Properties</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="fw-bold text-warning fs-3"><?php echo isset($locations) ? count($locations['Uttar Pradesh'] ?? []) : 0; ?>+</div>
                                    <div class="text-white-75">Locations</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="fw-bold text-warning fs-3">24/7</div>
                                    <div class="text-white-75">Support</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 animate-slide-right">
                <div class="hero-image position-relative">
                    <img src="<?php echo get_asset_url('assets/images/hero-2.jpg'); ?>"
                         alt="Properties in Gorakhpur"
                         class="img-fluid rounded shadow"
                         onerror="this.src='https://via.placeholder.com/600x400/667eea/ffffff?text=Premium+Properties'">
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-success px-3 py-2">
                            <i class="fas fa-check-circle me-1"></i>Verified Properties
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Advanced Search Filters -->
<section class="py-4">
    <div class="container">
        <div class="search-filters animate-fade-up">
            <form method="GET" action="" class="advanced-search-form">
                <div class="filter-grid">
                    <!-- Property Type -->
                    <div>
                        <label class="filter-title">
                            <i class="fas fa-building me-2 text-primary"></i>Property Type
                        </label>
                        <select class="form-select" name="type">
                            <option value="">All Types</option>
                            <option value="apartment" <?php echo (isset($_GET['type']) && $_GET['type'] === 'apartment') ? 'selected' : ''; ?>>Apartment</option>
                            <option value="villa" <?php echo (isset($_GET['type']) && $_GET['type'] === 'villa') ? 'selected' : ''; ?>>Villa</option>
                            <option value="house" <?php echo (isset($_GET['type']) && $_GET['type'] === 'house') ? 'selected' : ''; ?>>Independent House</option>
                            <option value="plot" <?php echo (isset($_GET['type']) && $_GET['type'] === 'plot') ? 'selected' : ''; ?>>Plot/Land</option>
                            <option value="commercial" <?php echo (isset($_GET['type']) && $_GET['type'] === 'commercial') ? 'selected' : ''; ?>>Commercial</option>
                        </select>
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="filter-title">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>Location
                        </label>
                        <select class="form-select" name="location">
                            <option value="">All Locations</option>
                            <?php if (!empty($locations ?? [])): ?>
                                <?php foreach ($locations as $state => $cities): ?>
                                    <optgroup label="<?php echo htmlspecialchars($state); ?>">
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?php echo htmlspecialchars($city['city']); ?>"
                                                <?php echo (isset($_GET['location']) && $_GET['location'] === $city['city']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($city['city']); ?> (<?php echo $city['count']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Budget Range -->
                    <div>
                        <label class="filter-title">
                            <i class="fas fa-rupee-sign me-2 text-primary"></i>Budget Range
                        </label>
                        <select class="form-select" name="budget">
                            <option value="">Any Budget</option>
                            <option value="0-3000000" <?php echo (isset($_GET['budget']) && $_GET['budget'] === '0-3000000') ? 'selected' : ''; ?>>Under ₹30 Lakh</option>
                            <option value="3000000-5000000" <?php echo (isset($_GET['budget']) && $_GET['budget'] === '3000000-5000000') ? 'selected' : ''; ?>>₹30-50 Lakh</option>
                            <option value="5000000-10000000" <?php echo (isset($_GET['budget']) && $_GET['budget'] === '5000000-10000000') ? 'selected' : ''; ?>>₹50 Lakh - ₹1 Cr</option>
                            <option value="10000000-20000000" <?php echo (isset($_GET['budget']) && $_GET['budget'] === '10000000-20000000') ? 'selected' : ''; ?>>₹1-2 Cr</option>
                            <option value="20000000+" <?php echo (isset($_GET['budget']) && $_GET['budget'] === '20000000+') ? 'selected' : ''; ?>>Above ₹2 Cr</option>
                        </select>
                    </div>

                    <!-- Bedrooms -->
                    <div>
                        <label class="filter-title">
                            <i class="fas fa-bed me-2 text-primary"></i>Bedrooms
                        </label>
                        <select class="form-select" name="bedrooms">
                            <option value="">Any</option>
                            <option value="1" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] === '1') ? 'selected' : ''; ?>>1 BHK</option>
                            <option value="2" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] === '2') ? 'selected' : ''; ?>>2 BHK</option>
                            <option value="3" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] === '3') ? 'selected' : ''; ?>>3 BHK</option>
                            <option value="4+" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] === '4+') ? 'selected' : ''; ?>>4+ BHK</option>
                        </select>
                    </div>

                    <!-- Additional Filters -->
                    <div>
                        <label class="filter-title">
                            <i class="fas fa-filter me-2 text-primary"></i>Additional Filters
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            <label class="filter-tag <?php echo (isset($_GET['featured']) && $_GET['featured'] === '1') ? 'active' : ''; ?>">
                                <input type="checkbox" name="featured" value="1" <?php echo (isset($_GET['featured']) && $_GET['featured'] === '1') ? 'checked' : ''; ?> class="d-none">
                                <i class="fas fa-star me-1"></i>Featured
                            </label>
                            <label class="filter-tag <?php echo (isset($_GET['parking']) && $_GET['parking'] === '1') ? 'active' : ''; ?>">
                                <input type="checkbox" name="parking" value="1" <?php echo (isset($_GET['parking']) && $_GET['parking'] === '1') ? 'checked' : ''; ?> class="d-none">
                                <i class="fas fa-car me-1"></i>Parking
                            </label>
                            <label class="filter-tag <?php echo (isset($_GET['garden']) && $_GET['garden'] === '1') ? 'active' : ''; ?>">
                                <input type="checkbox" name="garden" value="1" <?php echo (isset($_GET['garden']) && $_GET['garden'] === '1') ? 'checked' : ''; ?> class="d-none">
                                <i class="fas fa-tree me-1"></i>Garden
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5 py-3">
                        <i class="fas fa-search me-2"></i>Search Properties
                    </button>
                    <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-secondary btn-lg px-4 py-3 ms-3">
                        <i class="fas fa-times me-2"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Properties Listing -->
<section class="py-4">
    <div class="container">
        <!-- Sort Controls -->
        <div class="sort-controls animate-fade-up">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        Showing <?php echo count($properties ?? []); ?> properties
                        <?php if (!empty($_GET)): ?>
                            <span class="text-primary">(filtered)</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex align-items-center justify-content-end gap-3">
                        <label class="form-label mb-0">Sort by:</label>
                        <select class="form-select form-select-sm" style="width: auto;" onchange="changeSort(this.value)">
                            <option value="newest" <?php echo (!isset($_GET['sort']) || $_GET['sort'] === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="price_low" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="area" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'area') ? 'selected' : ''; ?>>Area: Largest First</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Properties Grid -->
        <?php if (empty($properties ?? [])): ?>
            <div class="text-center py-5">
                <div class="loading-shimmer" style="height: 300px; border-radius: 20px; margin-bottom: 2rem;"></div>
                <h4 class="text-muted">No Properties Found</h4>
                <p class="text-muted">Try adjusting your filters to find what you're looking for.</p>
                <a href="<?php echo BASE_URL; ?>properties" class="btn btn-primary mt-3">View All Properties</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($properties as $property): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="property-card animate-fade-up">
                            <div class="property-image">
                                <?php if (!empty($property->image_path)): ?>
                                    <img src="<?php echo get_asset_url($property->image_path); ?>"
                                         alt="<?php echo htmlspecialchars($property->title); ?>">
                                <?php else: ?>
                                    <img src="<?php echo get_asset_url('assets/images/property-placeholder.jpg'); ?>"
                                         alt="<?php echo htmlspecialchars($property->title); ?>">
                                <?php endif; ?>

                                <div class="property-overlay">
                                    <?php if ($property->featured ?? false): ?>
                                        <span class="property-badge">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    <?php endif; ?>
                                    <?php if (($property->status ?? '') === 'sold'): ?>
                                        <span class="property-badge" style="background: #dc3545;">
                                            <i class="fas fa-check-circle me-1"></i>Sold
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="property-content">
                                <h5 class="property-title">
                                    <?php echo htmlspecialchars(substr($property->title, 0, 50)); ?>
                                    <?php if (strlen($property->title) > 50): ?>...<?php endif; ?>
                                </h5>

                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($property->address ?? $property->city ?? 'Gorakhpur'); ?>
                                </div>

                                <div class="property-features">
                                    <?php if (!empty($property->bedrooms)): ?>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo $property->bedrooms; ?></span>
                                            <span class="property-feature-label">Beds</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($property->bathrooms)): ?>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo $property->bathrooms; ?></span>
                                            <span class="property-feature-label">Baths</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($property->area_sqft) || !empty($property->area)): ?>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo number_format($property->area_sqft ?? $property->area); ?></span>
                                            <span class="property-feature-label">Sqft</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="property-price">
                                    ₹<?php echo number_format($property->price); ?>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="<?php echo BASE_URL; ?>property/<?php echo $property->id; ?>"
                                       class="btn btn-property-action btn-view-details flex-fill">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                    <button class="btn btn-outline-primary btn-property-action"
                                            onclick="showQuickView(<?php echo $property['id']; ?>)">
                                        <i class="fas fa-expand-arrows-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination (if needed, currently not implemented in controller fully for view render) -->
            <!-- Just a placeholder if we want to add it later -->
        <?php endif; ?>
    </div>
</section>

<script>
    function changeSort(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', value);
        window.location.href = url.toString();
    }

    function showQuickView(id) {
        // Implement quick view modal logic here or ensure it's in a global JS file
        // For now, redirect to detail page
        window.location.href = '<?php echo BASE_URL; ?>property/' + id;
    }
</script>