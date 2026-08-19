<?php
/**
 * Neighborhood Analytics Page
 * @var array $property
 * @var array $analytics
 * @var array $nearbyAmenities
 */
$base = BASE_URL;
?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-map-marked-alt me-2"></i> Neighborhood Analytics</h4>
        <a href="<?= $base ?>/admin/custom-features" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (empty($property)): ?>
        <!-- Property Selection -->
        <div class="card aps-cp-card">
            <div class="card-body">
                <h5 class="mb-3">Select a Property</h5>
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Property</label>
                        <select name="property_id" class="form-select" required>
                            <option value="">Choose a property...</option>
                            <?php foreach ($properties ?? [] as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($selectedId ?? '') == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['title']) ?> - <?= htmlspecialchars($p['location']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Analyze</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Property Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card aps-cp-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1"><?= htmlspecialchars($property['title']) ?></h5>
                                <p class="text-muted mb-0"><?= htmlspecialchars($property['location']) ?> • 
                                    <span class="badge bg-primary"><?= htmlspecialchars($property['property_type']) ?></span>
                                    <span class="badge bg-info"><?= number_format($property['area_sqft'] ?? 0) ?> sq ft</span>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="<?= $base ?>/admin/custom-features/neighborhood" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Change Property
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card aps-cp-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-school fa-3x text-primary mb-2"></i>
                        <h5 class="fw-bold"><?= $analytics['education_score'] ?? 0 ?>/100</h5>
                        <p class="text-muted mb-0">Education</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card aps-cp-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-hospital fa-3x text-danger mb-2"></i>
                        <h5 class="fw-bold"><?= $analytics['healthcare_score'] ?? 0 ?>/100</h5>
                        <p class="text-muted mb-0">Healthcare</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card aps-cp-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-bag fa-3x text-success mb-2"></i>
                        <h5 class="fw-bold"><?= $analytics['shopping_score'] ?? 0 ?>/100</h5>
                        <p class="text-muted mb-0">Shopping</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card aps-cp-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-bus fa-3x text-warning mb-2"></i>
                        <h5 class="fw-bold"><?= $analytics['transport_score'] ?? 0 ?>/100</h5>
                        <p class="text-muted mb-0">Transport</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nearby Amenities -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card aps-cp-card">
                    <div class="card-header">
                        <h5 class="mb-0">Nearby Amenities</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach (['education' => 'Schools', 'healthcare' => 'Healthcare', 'shopping' => 'Shopping', 'transport' => 'Transport', 'banking' => 'Banking', 'recreation' => 'Recreation'] as $key => $label): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card aps-cp-card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><?= $label ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <?php if (empty($nearbyAmenities[$key])): ?>
                                                <p class="text-muted text-center py-3">No data available</p>
                                            <?php else: ?>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach (array_slice($nearbyAmenities[$key], 0, 5) as $amenity): ?>
                                                        <li class="d-flex justify-content-between py-1 border-bottom">
                                                            <span><?= htmlspecialchars($amenity['name']) ?></span>
                                                            <small class="text-muted"><?= $amenity['distance'] ?> km</small>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Walk/Transit/Lifestyle Scores -->
        <div class="row">
            <div class="col-md-4">
                <div class="card aps-cp-card h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Walk Score</h6>
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="position-relative" style="width: 120px; height: 120px;">
                                <svg width="120" height="120">
                                    <circle cx="60" cy="60" r="54" stroke="#e9ecef" stroke-width="8" fill="none"/>
                                    <circle cx="60" cy="60" r="54" stroke="#0d9488" stroke-width="8" fill="none"
                                            stroke-dasharray="339" stroke-dashoffset="<?= 339 - (339 * ($analytics['walk_score'] ?? 0) / 100) ?>"
                                            stroke-linecap="round" style="transition: stroke-dashoffset 0.5s;"/>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <h3 class="mb-0"><?= $analytics['walk_score'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card aps-cp-card h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Transit Score</h6>
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="position-relative" style="width: 120px; height: 120px;">
                                <svg width="120" height="120">
                                    <circle cx="60" cy="60" r="54" stroke="#e9ecef" stroke-width="8" fill="none"/>
                                    <circle cx="60" cy="60" r="54" stroke="#3b82f6" stroke-width="8" fill="none"
                                            stroke-dasharray="339" stroke-dashoffset="<?= 339 - (339 * ($analytics['transit_score'] ?? 0) / 100) ?>"
                                            stroke-linecap="round" style="transition: stroke-dashoffset 0.5s;"/>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <h3 class="mb-0"><?= $analytics['transit_score'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card aps-cp-card h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Lifestyle Score</h6>
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="position-relative" style="width: 120px; height: 120px;">
                                <svg width="120" height="120">
                                    <circle cx="60" cy="60" r="54" stroke="#e9ecef" stroke-width="8" fill="none"/>
                                    <circle cx="60" cy="60" r="54" stroke="#8b5cf6" stroke-width="8" fill="none"
                                            stroke-dasharray="339" stroke-dashoffset="<?= 339 - (339 * ($analytics['lifestyle_score'] ?? 0) / 100) ?>"
                                            stroke-linecap="round" style="transition: stroke-dashoffset 0.5s;"/>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <h3 class="mb-0"><?= $analytics['lifestyle_score'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>