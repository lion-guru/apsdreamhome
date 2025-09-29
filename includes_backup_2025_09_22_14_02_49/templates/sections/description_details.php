<?php
/**
 * Property Details: Description, Features, Amenities, Map Section
 *
 * @var array $property Property data including description, features, amenities, location data.
 * @var callable $e HTML escaping function.
 */

if (empty($property)) {
    echo '<p>Property details are not available.</p>';
    return;
}

// Ensure $e is available
if (!function_exists('e')) {
    function e(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

$features = $property['features'] ?? [];
$amenities = $property['amenities'] ?? [];

?>
<div class="property-description-details col-lg-8">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title">Property Description</h4>
            <p class="text-muted">
                <?php echo nl2br(e($property['description'] ?? 'No description available.')); ?>
            </p>
        </div>
    </div>

    <?php if (!empty($features)) : ?>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title">Features</h4>
            <ul class="list-group list-group-flush">
                <?php foreach ($features as $feature) : ?>
                    <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i><?php echo e($feature['feature_name']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($amenities)) : ?>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title">Amenities</h4>
            <div class="row">
                <?php foreach ($amenities as $amenity) : ?>
                    <div class="col-md-6 col-lg-4 mb-2">
                        <i class="fas fa-star text-warning me-2"></i><?php echo e($amenity['amenity_name']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($property['latitude']) && !empty($property['longitude'])) : ?>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title">Location Map</h4>
            <div id="propertyMap" style="height: 400px; width: 100%; border-radius: 8px;"></div>
            <p class="small text-muted mt-2">Note: Map marker may be approximate.</p>
        </div>
    </div>
    <?php else : ?>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title">Location Map</h4>
            <p>Map data is not available for this property.</p>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php if (!empty($property['latitude']) && !empty($property['longitude'])) : ?>
<script>
function initMap() {
    const propertyLocation = { lat: <?php echo e((float)$property['latitude']); ?>, lng: <?php echo e((float)$property['longitude']); ?> };
    const map = new google.maps.Map(document.getElementById("propertyMap"), {
        zoom: 15,
        center: propertyLocation,
        mapTypeControl: false,
        streetViewControl: false,
    });
    const marker = new google.maps.Marker({
        position: propertyLocation,
        map: map,
        title: "<?php echo e($property['title']); ?>"
    });
}
</script>
<!-- IMPORTANT: Replace YOUR_API_KEY with your actual Google Maps API key -->
<!-- Ensure this script is loaded *after* the Google Maps API script in your main layout/footer -->
<!-- Example: <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script> -->
<!-- For now, we'll call initMap directly. If the API is loaded globally, this will work. -->
<script>
if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
    initMap();
} else {
    // Fallback or load script dynamically if preferred
    console.warn('Google Maps API not loaded. Map will not be displayed. Ensure you load it with your API key.');
    // You might want to load it dynamically here if it's not in the footer
    // var script = document.createElement('script');
    // script.src = 'https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap';
    // script.async = true;
    // script.defer = true;
    // document.head.appendChild(script);
}
</script>
<?php endif; ?>
