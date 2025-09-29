<?php
/**
 * Property Details: Similar Properties Section
 *
 * @var array $similar_properties Array of similar property data.
 * @var callable $e HTML escaping function.
 * @var callable|null $formatPrice Price formatting function.
 */

if (empty($similar_properties)) {
    // No similar properties to display, so just return.
    return;
}

// Ensure $e is available
if (!function_exists('e')) {
    function e(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

?>
<section class="similar-properties py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-4">Similar Properties You Might Like</h3>
        <div class="row">
            <?php foreach ($similar_properties as $sim_prop) : ?>
                <?php
                // Basic price formatting if a global helper isn't used
                $sim_prop_price = isset($sim_prop['price']) ? (function_exists('formatPrice') ? formatPrice($sim_prop['price']) : '$' . number_format($sim_prop['price'])) : 'N/A';
                $property_url = SITE_URL . '/property-details.php?id=' . e($sim_prop['id']); // Link to old page for now
                // TODO: Update URL to property-details.new.php once it's the final version
                // $property_url = SITE_URL . '/property-details.new.php?id=' . e($sim_prop['id']); 
                ?>
                <div class="col-md-6 col-lg-4 mb-4 d-flex align-items-stretch">
                    <div class="card property-card h-100 shadow-sm lift">
                        <a href="<?php echo e($property_url); ?>">
                            <img src="<?php echo e(SITE_URL . '/' . ($sim_prop['thumbnail_image_path'] ?? 'assets/img/default-property.jpg')); ?>" class="card-img-top" alt="<?php echo e($sim_prop['title']); ?>" style="height: 200px; object-fit: cover;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><a href="<?php echo e($property_url); ?>" class="text-decoration-none text-dark stretched-link-hover"><?php echo e(mb_strimwidth($sim_prop['title'], 0, 50, '...')); ?></a></h5>
                            <p class="card-text text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo e(mb_strimwidth($sim_prop['address'], 0, 60, '...')); ?></p>
                            <h6 class="card-subtitle mb-2 text-primary fw-bold"><?php echo e($sim_prop_price); ?></h6>
                            <div class="mt-auto property-meta-icons">
                                <small class="text-muted me-2"><i class="fas fa-bed me-1"></i> <?php echo e($sim_prop['bedrooms'] ?? 'N/A'); ?></small>
                                <small class="text-muted me-2"><i class="fas fa-bath me-1"></i> <?php echo e($sim_prop['bathrooms'] ?? 'N/A'); ?></small>
                                <small class="text-muted"><i class="fas fa-ruler-combined me-1"></i> <?php echo e($sim_prop['area_sqft'] ?? 'N/A'); ?> sqft</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($similar_properties) === 0): ?>
            <p class="text-center">No similar properties found at the moment.</p>
        <?php endif; ?>
    </div>
</section>

<style>
.property-card .card-title a.stretched-link-hover:hover {
    color: var(--bs-primary) !important;
    text-decoration: underline !important;
}
.lift {
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}
.lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 20px rgba(0,0,0,0.12) !important;
}
</style>
