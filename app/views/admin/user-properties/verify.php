

<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/user-properties">User Properties</a></li>
            <li class="breadcrumb-item active">Property #<?php echo $property['id']; ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-building me-2"></i>Property Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-primary"><?php echo htmlspecialchars($property['name'] ?? ''); ?></h4>
                            <p class="text-muted mb-3">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($property['address'] ?? ''); ?>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <?php
                            $statusClass = match($property['status']) {
                                'pending' => 'warning',
                                'verified' => 'info',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?php echo $statusClass; ?> fs-6"><?php echo ucfirst($property['status']); ?></span>
                            <span class="badge bg-secondary ms-2"><?php echo ucfirst($property['listing_type']); ?></span>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Property Type</label>
                            <p class="mb-0 fw-bold"><?php echo ucfirst($property['property_type']); ?></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Area</label>
                            <p class="mb-0 fw-bold"><?php echo number_format(floatval($property['area_sqft'] ?? 0)); ?> sq ft</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Price</label>
                            <p class="mb-0 fw-bold text-success fs-5">₹<?php echo number_format(floatval($property['price'] ?? 0)); ?> <?php echo ucfirst($property['price_type']); ?></p>
                        </div>
                    </div>

                    <hr>

                    <h6>Description</h6>
                    <p><?php echo nl2br(htmlspecialchars($property['description'] ?? 'No description provided.')); ?></p>

                    <?php if ($property['verified_at']): ?>
                        <hr>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-user-check me-1"></i>
                            Verified on <?php echo date('d M Y h:i A', strtotime($property['verified_at'])); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Owner Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Owner Details</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong><?php echo htmlspecialchars($property['name'] ?? ''); ?></strong>
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <a href="tel:<?php echo htmlspecialchars($property['phone'] ?? ''); ?>"><?php echo htmlspecialchars($property['phone'] ?? ''); ?></a>
                    </p>
                    <?php if ($$property['email']): ?>
                        <p class="mb-0">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <a href="mailto:<?php echo htmlspecialchars($property['email'] ?? ''); ?>"><?php echo htmlspecialchars($property['email'] ?? ''); ?></a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <?php if ($property['status'] === 'pending'): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo BASE_URL; ?>/admin/user-properties/action">
                            <input type="hidden" name="id" value="<?php echo $property['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <div class="mb-3">
                                <label class="form-label">Admin Notes</label>
                                <textarea name="admin_notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-check me-1"></i> Approve Property
                            </button>
                        </form>
                        <form method="POST" action="<?php echo BASE_URL; ?>/admin/user-properties/action">
                            <input type="hidden" name="id" value="<?php echo $property['id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-times me-1"></i> Reject Property
                            </button>
                        </form>
                    </div>
                </div>
            <?php elseif ($property['status'] === 'approved'): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-check-circle me-2 text-success"></i>Approved</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">This property has been approved and is now visible to users.</p>
                    </div>
                </div>
            <?php elseif ($property['status'] === 'rejected'): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-times-circle me-2 text-danger"></i>Rejected</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">This property has been rejected.</p>
                        <form method="POST" action="<?php echo BASE_URL; ?>/admin/user-properties/action" class="mt-3">
                            <input type="hidden" name="id" value="<?php echo $property['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="fas fa-check me-1"></i> Re-approve
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


