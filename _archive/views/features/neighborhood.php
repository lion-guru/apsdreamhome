<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Neighborhood Analytics') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/features/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Property Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($property)): ?>
                        <h6><?= htmlspecialchars($property['name'] ?? '') ?></h6>
                        <p class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($property['location'] ?? '') ?></p>
                        <p class="text-muted small mb-0"><i class="fas fa-tag me-1"></i>â‚¹<?= number_format((int)($property['price'] ?? 0)) ?></p>
                    <?php else: ?>
                        <p class="text-muted">Property not selected.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Nearby Places</h5></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($nearby)): ?>
                            <?php foreach ($nearby as $n): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-<?= htmlspecialchars($n['icon'] ?? 'circle') ?> me-2 text-muted"></i><?= htmlspecialchars($n['name'] ?? '') ?></span>
                                    <span class="badge bg-secondary rounded-pill"><?= htmlspecialchars($n['distance'] ?? '') ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted text-center py-3">No nearby places data.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Neighborhood Stats</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block">Avg. Property Price</small><strong>â‚¹<?= number_format((int)($neighborhood['avg_price'] ?? 0)) ?></strong></div></div>
                        <div class="col-md-4"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block">Price Trend (YoY)</small><strong class="<?= ((float)($neighborhood['price_trend'] ?? 0) >= 0) ? 'text-success' : 'text-danger' ?>"><?= round((float)($neighborhood['price_trend'] ?? 0), 1) ?>%</strong></div></div>
                        <div class="col-md-4"><div class="bg-light rounded p-3 text-center"><small class="text-muted d-block">Properties Nearby</small><strong><?= (int)($neighborhood['properties_nearby'] ?? 0) ?></strong></div></div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-school me-2"></i>Infrastructure</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <?php $infra = ['schools' => 'Schools', 'hospitals' => 'Hospitals', 'shopping' => 'Shopping Centers', 'transport' => 'Transport Hubs', 'parks' => 'Parks & Recreation']; ?>
                        <?php foreach ($infra as $key => $label): ?>
                            <div class="col-md-4 col-6">
                                <div class="border rounded p-3 text-center h-100">
                                    <h3 class="mb-0"><?= (int)($infrastructure[$key]['count'] ?? 0) ?></h3>
                                    <small class="text-muted"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Safety & Demographics</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Crime Index</h6>
                            <div class="progress mb-3" class="style-40280">
                                <div class="progress-bar bg-<?= ((int)($neighborhood['crime_index'] ?? 0) < 30) ? 'success' : (((int)($neighborhood['crime_index'] ?? 0) < 60) ? 'warning' : 'danger') ?>" class="style-3498"><?= (int)($neighborhood['crime_index'] ?? 0) ?>/100</div>
                            </div>
                            <h6>Population Density</h6>
                            <p class="text-muted"><?= number_format((int)($neighborhood['population_density'] ?? 0)) ?> / kmÂ²</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Avg. Income Level</h6>
                            <p class="text-muted">â‚¹<?= number_format((int)($neighborhood['avg_income'] ?? 0)) ?>/yr</p>
                            <h6>Growth Potential</h6>
                            <p class="text-muted"><?= round((float)($neighborhood['growth_potential'] ?? 0), 1) ?> / 10</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
