<?php
$address = trim(($location['address'] ?? '') . ', ' . ($location['city'] ?? '') . ', ' . ($location['state'] ?? ''));
$latitude = $location['latitude'] ?? null;
$longitude = $location['longitude'] ?? null;
$landmarks = $location['landmarks'] ?? [];
?>
<section class="microsite-section py-5" id="microsite-location">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h2 class="section-title">Location Advantage</h2>
                <?php if (!empty($address)): ?>
                <p class="section-subtitle">
                    <i class="fa-solid fa-location-dot text-danger me-2"></i>
                    <?php echo h(trim($address, ', ')); ?>
                </p>
                <?php endif; ?>

                <?php if (!empty($landmarks)): ?>
                <ul class="list-group list-group-flush microsite-landmarks">
                    <?php foreach ($landmarks as $landmark): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fa-solid fa-map-pin text-primary me-2"></i>
                            <?php echo h(is_array($landmark) ? ($landmark['name'] ?? $landmark['label'] ?? '') : $landmark); ?>
                        </span>
                        <?php if (is_array($landmark) && !empty($landmark['distance'])): ?>
                        <span class="badge bg-light text-dark"><?php echo h($landmark['distance']); ?></span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
            <div class="col-lg-6">
                <div class="microsite-map">
                    <?php if ($latitude && $longitude): ?>
                    <iframe
                        src="https://maps.google.com/maps?q=<?php echo urlencode($latitude . ',' . $longitude); ?>&amp;z=15&amp;output=embed"
                        allowfullscreen
                        loading="lazy"
                        title="Project location map"
                    ></iframe>
                    <?php else: ?>
                    <div class="microsite-map__placeholder">
                        <i class="fa-solid fa-map-location-dot fa-3x text-primary mb-3"></i>
                        <p class="mb-0">Map preview coming soon.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
