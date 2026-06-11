<?php
$page_title = $page_title ?? 'Acquisition Details';
$acquisition = $acquisition ?? [];
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Acquisition: <?php echo $acquisition['acquisition_number'] ?? '#' . ($acquisition['id'] ?? ''); ?></h1>
                    <p class="text-muted mb-0">Date: <?php echo $acquisition['acquisition_date'] ?? '-'; ?></p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>/admin/land/acquisitions" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Acquisition Details</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Acquisition Number</div>
                        <div class="col-sm-8"><strong><?php echo $acquisition['acquisition_number'] ?? '-'; ?></strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Land Area</div>
                        <div class="col-sm-8"><?php echo number_format($acquisition['land_area'] ?? 0, 2); ?> <?php echo $acquisition['land_area_unit'] ?? 'sqft'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Acquisition Cost</div>
                        <div class="col-sm-8"><strong class="text-success">₹<?php echo number_format($acquisition['acquisition_cost'] ?? 0, 2); ?></strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Payment Status</div>
                        <div class="col-sm-8">
                            <span class="badge bg-<?php echo ($acquisition['payment_status'] ?? '') === 'completed' ? 'success' : (($acquisition['payment_status'] ?? '') === 'partial' ? 'warning' : 'danger'); ?>-subtle text-<?php echo ($acquisition['payment_status'] ?? '') === 'completed' ? 'success' : (($acquisition['payment_status'] ?? '') === 'partial' ? 'warning' : 'danger'); ?> rounded-pill px-3">
                                <?php echo ucfirst($acquisition['payment_status'] ?? 'pending'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Land Type</div>
                        <div class="col-sm-8"><span class="badge bg-info-subtle text-info rounded-pill px-3"><?php echo $acquisition['land_type'] ?? '-'; ?></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Status</div>
                        <div class="col-sm-8">
                            <span class="badge bg-<?php echo ($acquisition['status'] ?? '') === 'active' ? 'success' : (($acquisition['status'] ?? '') === 'sold' ? 'secondary' : (($acquisition['status'] ?? '') === 'under_development' ? 'primary' : 'danger')); ?>-subtle text-<?php echo ($acquisition['status'] ?? '') === 'active' ? 'success' : (($acquisition['status'] ?? '') === 'sold' ? 'secondary' : (($acquisition['status'] ?? '') === 'under_development' ? 'primary' : 'danger')); ?> rounded-pill px-3">
                                <?php echo ucfirst(str_replace('_', ' ', $acquisition['status'] ?? 'active')); ?>
                            </span>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-3">Location Information</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Location</div>
                        <div class="col-sm-8"><?php echo $acquisition['location'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Village</div>
                        <div class="col-sm-8"><?php echo $acquisition['village'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Tehsil</div>
                        <div class="col-sm-8"><?php echo $acquisition['tehsil'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">District</div>
                        <div class="col-sm-8"><?php echo $acquisition['district'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-4 text-muted">State</div>
                        <div class="col-sm-8"><?php echo $acquisition['state'] ?? '-'; ?></div>
                    </div>
                    <hr>
                    <h6 class="mb-3">Land Characteristics</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Soil Type</div>
                        <div class="col-sm-8"><?php echo $acquisition['soil_type'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Water Source</div>
                        <div class="col-sm-8"><?php echo $acquisition['water_source'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Electricity</div>
                        <div class="col-sm-8"><?php echo !empty($acquisition['electricity_available']) ? '<i class="fas fa-check-circle text-success"></i> Available' : '<i class="fas fa-times-circle text-danger"></i> Not Available'; ?></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Road Access</div>
                        <div class="col-sm-8"><?php echo !empty($acquisition['road_access']) ? '<i class="fas fa-check-circle text-success"></i> Accessible' : '<i class="fas fa-times-circle text-danger"></i> Not Accessible'; ?></div>
                    </div>
                </div>
            </div>

            <?php if (!empty($acquisition['documents']) || !empty($acquisition['remarks'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Documents & Remarks</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($acquisition['documents'])): ?>
                    <div class="mb-3">
                        <h6>Documents</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($acquisition['documents'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($acquisition['remarks'])): ?>
                    <div>
                        <h6>Remarks</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($acquisition['remarks'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Farmer Info</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($acquisition['farmer_id'])): ?>
                    <p class="mb-1"><strong>Farmer ID:</strong> <?php echo $acquisition['farmer_id']; ?></p>
                    <?php else: ?>
                    <p class="text-muted mb-0">No farmer linked</p>
                    <?php endif; ?>
                    <hr>
                    <p class="mb-1 text-muted"><i class="fas fa-user-check me-1"></i> Created by: <?php echo $acquisition['created_by_name'] ?? 'System'; ?></p>
                    <p class="mb-0 text-muted"><i class="fas fa-calendar me-1"></i> Date: <?php echo $acquisition['created_at'] ?? '-'; ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/land/acquisitions/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="acquisition_id" value="<?php echo $acquisition['id'] ?? 0; ?>">
                        <div class="mb-3">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="pending" <?php echo ($acquisition['payment_status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="partial" <?php echo ($acquisition['payment_status'] ?? '') === 'partial' ? 'selected' : ''; ?>>Partial</option>
                                <option value="completed" <?php echo ($acquisition['payment_status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($acquisition['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="under_development" <?php echo ($acquisition['status'] ?? '') === 'under_development' ? 'selected' : ''; ?>>Under Development</option>
                                <option value="sold" <?php echo ($acquisition['status'] ?? '') === 'sold' ? 'selected' : ''; ?>>Sold</option>
                                <option value="inactive" <?php echo ($acquisition['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"><?php echo $acquisition['remarks'] ?? ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
