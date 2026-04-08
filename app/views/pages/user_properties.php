<?php
$extraHead = '<style>
    .property-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .property-card:hover { transform: translateY(-3px); }
</style>';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h3><i class="fas fa-building me-2 text-primary"></i>My Properties</h3>
        <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Post New Property
        </a>
    </div>

    <?php if (empty($properties)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-home fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No properties posted yet</h5>
                <p class="text-muted">Start by posting your first property for free!</p>
                <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Post Property
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($properties as $p): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card property-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($p['name']); ?></h5>
                                    <p class="text-muted mb-0 small">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($p['address'] ?? 'Location not specified'); ?>
                                    </p>
                                </div>
                                <?php
                                $statusClass = match($p['status'] ?? 'pending') {
                                    'pending' => 'warning',
                                    'verified' => 'info',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'sold' => 'dark',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($p['status'] ?? 'pending'); ?></span>
                            </div>

                            <?php if (!empty($p['image'])): ?>
                                <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($p['image']); ?>" class="img-fluid rounded mb-3" style="max-height: 180px; width: 100%; object-fit: cover;" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <?php endif; ?>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <small class="text-muted">Type</small>
                                    <p class="mb-0 fw-bold"><?php echo ucfirst($p['property_type']); ?></p>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">For</small>
                                    <p class="mb-0 fw-bold"><?php echo ucfirst($p['listing_type']); ?></p>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Price</small>
                                    <p class="mb-0 fw-bold text-success">Rs.<?php echo number_format($p['price']); ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <small class="text-muted">Area</small>
                                    <p class="mb-0"><?php echo number_format($p['area_sqft'] ?? 0); ?> sq ft</p>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Views</small>
                                    <p class="mb-0"><i class="fas fa-eye me-1"></i><?php echo $p['views'] ?? 0; ?></p>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Inquiries</small>
                                    <p class="mb-0"><i class="fas fa-envelope me-1"></i><?php echo $p['inquiries'] ?? 0; ?></p>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Posted: <?php echo date('d M Y', strtotime($p['created_at'])); ?>
                                </small>
                                <?php if ($p['status'] === 'pending'): ?>
                                    <span class="badge bg-warning">Under Review</span>
                                <?php elseif ($p['status'] === 'approved'): ?>
                                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-sm btn-outline-primary">View Listing</a>
                                <?php elseif ($p['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger">Rejected - Contact Us</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
