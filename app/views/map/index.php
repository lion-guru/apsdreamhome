<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i><?= ($page_title ?? 'Property Map') ?></h4>
            <small class="text-muted"><?= ($page_description ?? 'Browse properties on map') ?></small>
        </div>
        <div class="d-flex gap-2">
            <input type="text" id="mapSearch" class="form-control form-control-sm" placeholder="Search location..." class="style-47085">
            <select id="propertyTypeFilter" class="form-select form-select-sm" class="style-30246">
                <option value="">All Types</option>
                <option value="apartment">Apartment</option>
                <option value="house">House</option>
                <option value="plot">Plot</option>
                <option value="commercial">Commercial</option>
            </select>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div id="propertyMap" class="style-92550">
                    <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt fa-4x mb-3"></i>
                            <p>Map loading... Ensure Google Maps API key is configured.</p>
                            <small>Showing <?= count($properties ?? []) ?> properties</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Properties (<?= count($properties ?? []) ?>)</h6></div>
                <div class="card-body p-0" class="style-62230">
                    <?php if (!empty($properties ?? [])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (($properties ?? []) as $p): ?>
                        <a href="<?= ($base ?? BASE_URL) ?>properties/<?= ($p['id'] ?? '') ?>" class="list-group-item list-group-item-action border-0">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-0 small"><?= htmlspecialchars($p['title'] ?? '') ?></h6>
                                    <small class="text-muted"><i class="fas fa-map-pin me-1"></i><?= htmlspecialchars($p['location'] ?? '') ?></small>
                                </div>
                                <span class="text-primary fw-bold small">â‚¹<?= number_format($p['price'] ?? 0) ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-map-pin fa-2x mb-2"></i>
                        <p class="small">No properties with location data.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
