<?php
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $protocol . '://' . $host . rtrim($base, '/') . '/');
}
?>
<?php
/**
 * Enhanced Properties Page - APS Dream Home
 * Modern UI/UX for property listings with advanced filtering
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Properties - APS Dream Home'; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Browse premium properties in Gorakhpur, Lucknow & UP. Apartments, villas, plots & commercial spaces with advanced search filters.">
    <meta name="keywords" content="properties Gorakhpur, buy house, apartments Lucknow, real estate UP, property for sale">

    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://unpkg.com/swiper@10/swiper-bundle.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/nouislider@15.7.0/dist/nouislider.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: #f8f9fa;
        }

        /* Modern Hero Section */
        .hero-properties {
            background: var(--primary-gradient);
            color: white;
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }

        .hero-properties::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><circle cx="500" cy="500" r="300" fill="rgba(255,255,255,0.05)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        /* Enhanced Search Filters */
        .search-filters {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .price-range-slider {
            margin-top: 1rem;
        }

        /* Property Cards */
        .property-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
        }

        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .property-image {
            height: 250px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            position: relative;
            overflow: hidden;
        }

        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .property-card:hover .property-image img {
            transform: scale(1.05);
        }

        .property-overlay {
            position: absolute;
            top: 1rem;
            left: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .property-badge {
            background: rgba(255, 255, 255, 0.9);
            color: #667eea;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .property-content {
            padding: 1.5rem;
        }

        .property-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .property-location {
            color: #667eea;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .property-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .property-feature {
            text-align: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .property-feature-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            display: block;
        }

        .property-feature-label {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .property-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: #28a745;
            text-align: center;
            margin-bottom: 1rem;
        }

        /* Enhanced Buttons */
        .btn-property-action {
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-view-details {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-view-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        /* Filter Tags */
        .filter-tag {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 0.25rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-tag.active {
            background: var(--primary-gradient);
            color: white;
        }

        .filter-tag:hover {
            background: #667eea;
            color: white;
        }

        /* Sort Controls */
        .sort-controls {
            background: white;
            border-radius: 15px;
            padding: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        /* Loading Animation */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.8s ease-out;
        }

        .animate-slide-right {
            animation: slideInRight 0.8s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-properties {
                padding: 3rem 0;
                text-align: center;
            }

            .filter-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .property-features {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../layouts/modern_header.php'; ?>

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
                                    <div class="fw-bold text-warning fs-3"><?php echo isset($locations) ? count($locations) : 0; ?>+</div>
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
                    <img src="<?php echo ASSET_URL ?? '/assets/'; ?>images/properties-hero.jpg"
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
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary btn-lg px-4 py-3 ms-3">
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
                <h4 class="text-muted">Loading Properties...</h4>
                <p class="text-muted">Please wait while we fetch the best properties for you.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($properties as $property): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="property-card animate-fade-up">
                            <div class="property-image">
                                <?php if (!empty($property['main_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($property['main_image']); ?>"
                                         alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <?php endif; ?>

                                <div class="property-overlay">
                                    <?php if ($property['featured']): ?>
                                        <span class="property-badge">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($property['status'] === 'sold'): ?>
                                        <span class="property-badge" style="background: #dc3545;">
                                            <i class="fas fa-check-circle me-1"></i>Sold
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="property-content">
                                <h5 class="property-title">
                                    <?php echo htmlspecialchars(substr($property['title'], 0, 50)); ?>
                                    <?php if (strlen($property['title']) > 50): ?>...<?php endif; ?>
                                </h5>

                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($property['address'] ?? $property['city'] ?? 'Gorakhpur'); ?>
                                </div>

                                <div class="property-features">
                                    <?php if (!empty($property['bedrooms'])): ?>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo $property['bedrooms']; ?></span>
                                            <span class="property-feature-label">Beds</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($property['bathrooms'])): ?>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo $property['bathrooms']; ?></span>
                                            <span class="property-feature-label">Baths</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($property['area_sqft'])): ?>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo number_format($property['area_sqft']); ?></span>
                                            <span class="property-feature-label">Sqft</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="property-price">
                                    ₹<?php echo number_format($property['price']); ?>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="<?php echo BASE_URL; ?>property/<?php echo $property['id']; ?>"
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

            <!-- Load More Button -->
            <?php if (isset($has_more) && $has_more): ?>
                <div class="text-center mt-5">
                    <button class="btn btn-outline-primary btn-lg px-5 py-3" onclick="loadMoreProperties()">
                        <i class="fas fa-plus me-2"></i>Load More Properties
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Property Quick View Modal -->
<div class="modal fade" id="propertyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Property Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="propertyModalContent">
                <!-- Property details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/modern_footer.php'; ?>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.0/dist/nouislider.min.js"></script>

<script>
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });

    // Sort functionality
    function changeSort(sortValue) {
        const url = new URL(window.location);
        url.searchParams.set('sort', sortValue);
        window.location.href = url.toString();
    }

    // Quick view functionality
    function showQuickView(propertyId) {
        // Show loading in modal
        document.getElementById('propertyModalContent').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 mb-0">Loading property details...</p>
            </div>
        `;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('propertyModal'));
        modal.show();

        // Fetch property details
        fetch(`<?php echo BASE_URL; ?>api/property/${propertyId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayPropertyModal(data.property);
                } else {
                    document.getElementById('propertyModalContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading property details. Please try again.
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('propertyModalContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Network error. Please check your connection and try again.
                    </div>
                `;
            });
    }

    function displayPropertyModal(property) {
        const modalContent = `
            <div class="row">
                <div class="col-md-6">
                    <img src="${property.main_image || 'https://via.placeholder.com/400x300/667eea/ffffff?text=No+Image'}"
                         alt="${property.title}" class="img-fluid rounded mb-3">
                </div>
                <div class="col-md-6">
                    <h4>${property.title}</h4>
                    <p class="text-muted mb-3">${property.address || property.city || 'Location not specified'}</p>

                    <div class="property-features mb-3">
                        ${property.bedrooms ? `<div class="property-feature"><span class="property-feature-value">${property.bedrooms}</span><span class="property-feature-label">Beds</span></div>` : ''}
                        ${property.bathrooms ? `<div class="property-feature"><span class="property-feature-value">${property.bathrooms}</span><span class="property-feature-label">Baths</span></div>` : ''}
                        ${property.area_sqft ? `<div class="property-feature"><span class="property-feature-value">${property.area_sqft}</span><span class="property-feature-label">Sqft</span></div>` : ''}
                    </div>

                    <div class="property-price fs-4 fw-bold text-success mb-3">
                        ₹${property.price.toLocaleString()}
                    </div>

                    <a href="<?php echo BASE_URL; ?>property/${property.id}" class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i>View Full Details
                    </a>
                </div>
            </div>
        `;

        document.getElementById('propertyModalContent').innerHTML = modalContent;
    }

    // Load more properties functionality
    let currentPage = 1;
    function loadMoreProperties() {
        currentPage++;
        const loadMoreBtn = event.target;
        const originalText = loadMoreBtn.innerHTML;

        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
        loadMoreBtn.disabled = true;

        // Simulate loading more properties
        setTimeout(() => {
            loadMoreBtn.innerHTML = originalText;
            loadMoreBtn.disabled = false;
            // In a real implementation, this would make an AJAX call to load more properties
        }, 1500);
    }

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe property cards for animation
    document.querySelectorAll('.property-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Filter tag interaction
    document.querySelectorAll('.filter-tag').forEach(tag => {
        tag.addEventListener('click', function() {
            const checkbox = this.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                this.classList.toggle('active', checkbox.checked);

                // Submit form when filter changes
                const form = document.querySelector('.advanced-search-form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.click();
                }
            }
        });
    });

    // Form submission enhancement
    document.querySelector('.advanced-search-form')?.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';
        submitBtn.disabled = true;

        // Re-enable after 3 seconds
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 3000);
    });
</script>

</body>
</html>
