<?php
// includes/templates/sections/featured-properties.php

// Expects $featured_properties to be passed from the main page (index.new.php)
// $featured_properties should be an array of property objects/arrays

$section_title = $page_data['featured_properties_title'] ?? 'Featured Properties';
$section_subtitle = $page_data['featured_properties_subtitle'] ?? 'Handpicked properties by our team.';

?>
<section id="featured-properties" class="featured-properties-section py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8 mx-auto text-center">
                <h2 class="section-title fw-bold"><?php echo e($section_title); ?></h2>
                <p class="section-subtitle lead text-muted"><?php echo e($section_subtitle); ?></p>
            </div>
        </div>

        <?php if (!empty($featured_properties) && is_array($featured_properties)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($featured_properties as $property): ?>
                    <?php 
                        // Ensure property data is in a consistent format (array)
                        $property = (array) $property;
                        $property_url = e($property['url'] ?? get_property_url($property['id'] ?? 0, $property['slug'] ?? ''));
                        $main_image = e($property['main_image'] ?? (SITE_URL . '/assets/images/property-placeholder.jpg'));
                        $title = e($property['title'] ?? 'N/A');
                        $address = e($property['address'] ?? ($property['city'] ?? 'N/A'));
                        $formatted_price = e($property['formatted_price'] ?? 'Price on request');
                        $bedrooms = (int)($property['bedrooms'] ?? 0);
                        $bathrooms = (int)($property['bathrooms'] ?? 0);
                        $area = e($property['formatted_area'] ?? ($property['area'] ? (number_format($property['area']) . ' ' . ($property['area_unit'] ?? 'sq.ft.')) : 'N/A'));
                        $property_type = e($property['property_type'] ?? 'Property');
                        $status_class = e($property['status_class'] ?? 'available');
                        $status_text = e(ucfirst($property['status'] ?? 'Available'));
                    ?>
                    <div class="col animate__animated animate__fadeInUp">
                        <div class="card property-card h-100 shadow-sm overflow-hidden">
                            <div class="property-card-image-container">
                                <a href="<?php echo $property_url; ?>">
                                    <img src="<?php echo $main_image; ?>" class="card-img-top property-card-img" alt="<?php echo $title; ?>">
                                </a>
                                <div class="property-status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></div>
                                <?php if (!empty($property['is_featured'])): ?>
                                <div class="property-featured-badge">
                                    <i class="fas fa-star"></i> Featured
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title property-title mb-1">
                                    <a href="<?php echo $property_url; ?>" class="text-decoration-none text-dark stretched-link"><?php echo $title; ?></a>
                                </h5>
                                <p class="property-location text-muted small mb-2"><i class="fas fa-map-marker-alt me-1 text-primary"></i><?php echo $address; ?></p>
                                
                                <h4 class="property-price fw-bold text-primary my-2"><?php echo $formatted_price; ?></h4>
                                
                                <div class="property-features d-flex justify-content-around text-muted border-top border-bottom py-2 my-2 small">
                                    <?php if ($bedrooms > 0): ?>
                                    <span title="Bedrooms"><i class="fas fa-bed me-1"></i> <?php echo $bedrooms; ?> Bed<?php echo ($bedrooms > 1 ? 's' : ''); ?></span>
                                    <?php endif; ?>
                                    <?php if ($bathrooms > 0): ?>
                                    <span title="Bathrooms"><i class="fas fa-bath me-1"></i> <?php echo $bathrooms; ?> Bath<?php echo ($bathrooms > 1 ? 's' : ''); ?></span>
                                    <?php endif; ?>
                                    <?php if ($area !== 'N/A'): ?>
                                    <span title="Area"><i class="fas fa-ruler-combined me-1"></i> <?php echo $area; ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="property-type small text-muted mt-auto mb-0">Type: <?php echo $property_type; ?></p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 text-center pb-3">
                                <a href="<?php echo $property_url; ?>" class="btn btn-primary btn-sm">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row mt-5">
                <div class="col text-center">
                    <a href="<?php echo SITE_URL; ?>/properties.php" class="btn btn-outline-primary btn-lg">View All Properties</a>
                </div>
            </div>

        <?php else: ?>
            <div class="row">
                <div class="col">
                    <div class="alert alert-info text-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i>No featured properties are available at the moment. Please check back later.
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.property-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}
.property-card-image-container {
    position: relative;
    overflow: hidden;
    height: 220px; /* Fixed height for images */
}
.property-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.property-card:hover .property-card-img {
    transform: scale(1.05);
}
.property-status-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 5px 10px;
    font-size: 0.8rem;
    font-weight: bold;
    color: #fff;
    border-radius: 3px;
    z-index: 10;
}
.property-status-badge.available { background-color: #28a745; /* Green */ }
.property-status-badge.sold { background-color: #dc3545; /* Red */ }
.property-status-badge.pending { background-color: #ffc107; /* Yellow */ }
.property-status-badge.rented { background-color: #17a2b8; /* Info Blue */ }

.property-featured-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: rgba(255, 193, 7, 0.9); /* Yellow, slightly transparent */
    color: #333;
    padding: 5px 8px;
    font-size: 0.75rem;
    font-weight: bold;
    border-radius: 3px;
    z-index: 10;
}
.property-title a {
    color: #333;
    font-size: 1.1rem;
    font-weight: 600;
}
.property-title a:hover {
    color: var(--bs-primary, #0d6efd);
}
.property-price {
    font-size: 1.5rem;
}
.property-features span {
    margin-right: 10px;
}
.property-features span:last-child {
    margin-right: 0;
}
.property-features i {
    color: var(--bs-primary, #0d6efd);
}
.section-title {
    position: relative;
    padding-bottom: 15px;
    margin-bottom: 20px;
}
.section-title::after {
    content: '';
    position: absolute;
    display: block;
    width: 60px;
    height: 3px;
    background: var(--bs-primary, #0d6efd);
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
}
/* Ensure animations are defined or use a library like Animate.css */
.animate__animated.animate__fadeInUp {
    animation-name: fadeInUp; /* Ensure fadeInUp is defined if not using full Animate.css */
}
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translate3d(0, 20px, 0);
  }
  to {
    opacity: 1;
    transform: translate3d(0, 0, 0);
  }
}
</style>
