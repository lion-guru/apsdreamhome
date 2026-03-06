<?php
/**
 * Properties Listing Page - APS Dream Home
 * Browse all available properties with filters
 */

// Set page title and description for layout
$page_title = $page_title ?? 'Properties - APS Dream Home';
$page_description = $page_description ?? 'Browse our extensive collection of premium properties in Gorakhpur, Lucknow, and across Uttar Pradesh.';
?>

<!-- Include Header -->
<?php include __DIR__ . '/../layouts/header_new.php'; ?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">
                        Find Your <span class="text-warning">Dream Property</span>
                    </h1>
                    <p class="lead mb-4">
                        Discover our handpicked selection of premium properties across Uttar Pradesh. Your perfect home awaits!
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo BASE_URL; ?>contact" class="btn btn-warning btn-lg">
                            <i class="fas fa-phone me-2"></i>Get Expert Help
                        </a>
                        <a href="#properties" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-search me-2"></i>Browse Properties
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/properties-hero.jpg"
                         alt="Browse Properties"
                         class="img-fluid rounded shadow-lg"
                         onerror="this.src='https://via.placeholder.com/600x400/667eea/ffffff?text=Browse+Properties'">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filters Section -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <h2 class="text-center mb-4">Search Properties</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Property Type
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="type-all" checked>
                            <label class="form-check-label" for="type-all">All Types</label>
                        </div>
                        <?php foreach ($filters['types'] as $type): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="type[]" id="type-<?php echo strtolower(str_replace(' ', '', $type)); ?>" value="<?php echo $type; ?>">
                                <label class="form-check-label" for="type-<?php echo strtolower(str_replace(' ', '', $type)); ?>"><?php echo htmlspecialchars($type); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>Location
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="location-all" checked>
                            <label class="form-check-label" for="location-all">All Locations</label>
                        </div>
                        <?php foreach ($filters['locations'] as $location): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="location[]" id="location-<?php echo strtolower(str_replace(' ', '', $location)); ?>" value="<?php echo $location; ?>">
                                <label class="form-check-label" for="location-<?php echo strtolower(str_replace(' ', '', $location)); ?>"><?php echo htmlspecialchars($location); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-rupee-sign me-2"></i>Price Range
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="price-all" checked>
                            <label class="form-check-label" for="price-all">All Prices</label>
                        </div>
                        <?php foreach ($filters['price_ranges'] as $price): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="price_range[]" id="price-<?php echo strtolower(str_replace([' ', '₹', 'Lac', 'Crore'], '', $price)); ?>" value="<?php echo $price; ?>">
                                <label class="form-check-label" for="price-<?php echo strtolower(str_replace([' ', '₹', 'Lac', 'Crore'], '', $price)); ?>"><?php echo htmlspecialchars($price); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bed me-2"></i>Bedrooms
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="bedrooms-all" checked>
                            <label class="form-check-label" for="bedrooms-all">Any</label>
                        </div>
                        <?php foreach ($filters['bedrooms'] as $bedrooms): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="bedrooms[]" id="bedrooms-<?php echo strtolower(str_replace([' ', '+', 'BHK'], '', $bedrooms)); ?>" value="<?php echo $bedrooms; ?>">
                                <label class="form-check-label" for="bedrooms-<?php echo strtolower(str_replace([' ', '+', 'BHK'], '', $bedrooms)); ?>"><?php echo htmlspecialchars($bedrooms); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                <i class="fas fa-search me-2"></i>Apply Filters
            </button>
            <button type="button" class="btn btn-outline-secondary ms-2" onclick="clearFilters()">
                <i class="fas fa-times me-2"></i>Clear Filters
            </button>
        </div>
    </div>
</section>

<!-- Properties Grid -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <h2 class="mb-4">Available Properties</h2>
                <p class="text-muted">Found <?php echo count($properties); ?> properties matching your criteria</p>
            </div>
        </div>
        
        <div class="row g-4" id="propertiesGrid">
            <?php foreach ($properties as $property): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card property-card h-100 border-0 shadow-sm">
                        <div class="property-image">
                            <?php if ($property->featured): ?>
                                <span class="featured-badge">
                                    <i class="fas fa-star"></i> Featured
                                </span>
                            <?php endif; ?>
                            <img src="<?php echo BASE_URL; ?>/public/assets/<?php echo $property->image; ?>"
                                 alt="<?php echo htmlspecialchars($property->title); ?>"
                                 class="img-fluid"
                                 onerror="this.src='https://via.placeholder.com/400x300/667eea/ffffff?text=Property'">
                        </div>
                        <div class="card-body">
                            <div class="property-header d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <a href="<?php echo BASE_URL; ?>properties/<?php echo $property->id; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($property->title); ?>
                                        </a>
                                    </h5>
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($property->location); ?>
                                    </div>
                                </div>
                                <div class="property-price">
                                    <span class="h4 text-primary mb-0"><?php echo $property->price_display; ?></span>
                                    <span class="text-muted small">per <?php echo $property->area_unit; ?></span>
                                </div>
                            </div>
                            
                            <div class="property-details mb-3">
                                <div class="row text-muted small">
                                    <div class="col-6">
                                        <i class="fas fa-home me-1"></i>
                                        <?php echo htmlspecialchars($property->type); ?>
                                    </div>
                                    <div class="col-6 text-end">
                                        <i class="fas fa-bed me-1"></i>
                                        <?php echo $property->bedrooms; ?> BHK
                                    </div>
                                </div>
                                <div class="row text-muted small">
                                    <div class="col-6">
                                        <i class="fas fa-expand-arrows-alt me-1"></i>
                                        <?php echo $property->area_display; ?>
                                    </div>
                                    <div class="col-6 text-end">
                                        <i class="fas fa-bath me-1"></i>
                                        <?php echo $property->bathrooms; ?> Bath
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($property->amenities)): ?>
                                <div class="property-amenities">
                                    <h6 class="mb-2">Amenities</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($property->amenities as $amenity): ?>
                                            <span class="amenity-badge">
                                                <?php 
                                                $amenityIcons = [
                                                    'Parking' => 'fa-parking',
                                                    'Garden' => 'fa-tree',
                                                    'Security' => 'fa-shield-alt',
                                                    'Power Backup' => 'fa-plug',
                                                    'Lift' => 'fa-elevator',
                                                    'Swimming Pool' => 'fa-swimming-pool'
                                                ];
                                                $icon = $amenityIcons[$amenity] ?? 'fa-check';
                                                echo '<i class="fas ' . $icon . ' me-1"></i>' . htmlspecialchars($amenity);
                                            ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="<?php echo BASE_URL; ?>properties/<?php echo $property->id; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Include Footer -->
<?php include __DIR__ . '/../layouts/footer_new.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    window.applyFilters = function() {
        const checkedTypes = Array.from(document.querySelectorAll('input[name="type[]"]:checked')).map(cb => cb.value);
        const checkedLocations = Array.from(document.querySelectorAll('input[name="location[]"]:checked')).map(cb => cb.value);
        const checkedPrices = Array.from(document.querySelectorAll('input[name="price_range[]"]:checked')).map(cb => cb.value);
        const checkedBedrooms = Array.from(document.querySelectorAll('input[name="bedrooms[]"]:checked')).map(cb => cb.value);
        
        const allCards = document.querySelectorAll('.property-card');
        
        allCards.forEach(card => {
            const propertyType = card.querySelector('.property-details .col-6').textContent.trim();
            const propertyLocation = card.querySelector('.property-details .text-muted').textContent.trim();
            
            let show = true;
            
            if (checkedTypes.length > 0 && !checkedTypes.includes(propertyType)) {
                show = false;
            }
            
            if (checkedLocations.length > 0 && !checkedLocations.includes(propertyLocation)) {
                show = false;
            }
            
            card.style.display = show ? 'block' : 'none';
        });
        
        // Update count
        const visibleCards = Array.from(document.querySelectorAll('.property-card')).filter(card => card.style.display !== 'none');
        document.querySelector('.text-muted').textContent = `Found ${visibleCards.length} properties matching your criteria`;
    };
    
    window.clearFilters = function() {
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = cb.id.includes('-all'));
        document.getElementById('type-all').checked = true;
        document.getElementById('location-all').checked = true;
        document.getElementById('price-all').checked = true;
        document.getElementById('bedrooms-all').checked = true;
        
        // Show all cards
        document.querySelectorAll('.property-card').forEach(card => {
            card.style.display = 'block';
        });
        
        document.querySelector('.text-muted').textContent = `Found ${document.querySelectorAll('.property-card').length} properties`;
    };
});
</script>

<style>
.property-card {
    transition: transform 0.3s ease;
}

.property-card:hover {
    transform: translateY(-5px);
}

.property-image {
    position: relative;
    overflow: hidden;
    border-radius: 8px 8px 0 0;
}

.featured-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #ffc107;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: bold;
}

.property-price .h4 {
    margin: 0;
    font-weight: bold;
}

.property-price .small {
    font-weight: normal;
}

.amenity-badge {
    background: #f8f9fa;
    color: #6c757d;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    display: inline-block;
    margin-bottom: 4px;
}
</style>
