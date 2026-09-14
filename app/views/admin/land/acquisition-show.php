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
                    <p class="text-muted mb-0">Date: <?php echo $acquisition['created_at'] ?? '-'; ?></p>
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
                        <div class="col-sm-4 text-muted">Total Area</div>
                        <div class="col-sm-8"><?php echo number_format($acquisition['total_area_sqft'] ?? 0, 2); ?> sqft</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Acquired Area</div>
                        <div class="col-sm-8"><?php echo number_format($acquisition['acquired_area_sqft'] ?? 0, 2); ?> sqft</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Acquisition Cost</div>
                        <div class="col-sm-8"><strong class="text-success">₹<?php echo number_format($acquisition['acquisition_cost'] ?? 0, 2); ?></strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Total Consideration</div>
                        <div class="col-sm-8">₹<?php echo number_format($acquisition['total_consideration'] ?? 0, 2); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Advance Paid</div>
                        <div class="col-sm-8">₹<?php echo number_format($acquisition['advance_paid'] ?? 0, 2); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Balance Amount</div>
                        <div class="col-sm-8">₹<?php echo number_format($acquisition['balance_amount'] ?? 0, 2); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Mutation Status</div>
                        <div class="col-sm-8">
                            <span class="badge bg-<?php echo ($acquisition['mutation_status'] ?? '') === 'completed' ? 'success' : (($acquisition['mutation_status'] ?? '') === 'in_progress' ? 'primary' : (($acquisition['mutation_status'] ?? '') === 'applied' ? 'info' : (($acquisition['mutation_status'] ?? '') === 'rejected' ? 'danger' : 'secondary'))); ?>-subtle text-<?php echo ($acquisition['mutation_status'] ?? '') === 'completed' ? 'success' : (($acquisition['mutation_status'] ?? '') === 'in_progress' ? 'primary' : (($acquisition['mutation_status'] ?? '') === 'applied' ? 'info' : (($acquisition['mutation_status'] ?? '') === 'rejected' ? 'danger' : 'secondary'))); ?> rounded-pill px-3">
                                <?php echo ucfirst(str_replace('_', ' ', $acquisition['mutation_status'] ?? 'not_started')); ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Status</div>
                        <div class="col-sm-8">
                            <span class="badge bg-<?php echo ($acquisition['status'] ?? '') === 'in_progress' ? 'primary' : (($acquisition['status'] ?? '') === 'registered' ? 'info' : (($acquisition['status'] ?? '') === 'mutated' ? 'success' : (($acquisition['status'] ?? '') === 'closed' ? 'secondary' : 'danger'))); ?>-subtle text-<?php echo ($acquisition['status'] ?? '') === 'in_progress' ? 'primary' : (($acquisition['status'] ?? '') === 'registered' ? 'info' : (($acquisition['status'] ?? '') === 'mutated' ? 'success' : (($acquisition['status'] ?? '') === 'closed' ? 'secondary' : 'danger'))); ?> rounded-pill px-3">
                                <?php echo ucfirst(str_replace('_', ' ', $acquisition['status'] ?? 'in_progress')); ?>
                            </span>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-3">Agreement & Registration</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Sale Agreement Date</div>
                        <div class="col-sm-8"><?php echo $acquisition['sale_agreement_date'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Sale Agreement Number</div>
                        <div class="col-sm-8"><?php echo $acquisition['sale_agreement_number'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Registration Date</div>
                        <div class="col-sm-8"><?php echo $acquisition['registration_date'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Registration Number</div>
                        <div class="col-sm-8"><?php echo $acquisition['registration_number'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Sub-Registrar Office</div>
                        <div class="col-sm-8"><?php echo $acquisition['sub_registrar_office'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Stamp Duty</div>
                        <div class="col-sm-8">₹<?php echo number_format($acquisition['stamp_duty_amount'] ?? 0, 2); ?></div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-4 text-muted">Registration Fee</div>
                        <div class="col-sm-8">₹<?php echo number_format($acquisition['registration_fee'] ?? 0, 2); ?></div>
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
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($acquisition['documents'] ?? '')); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($acquisition['remarks'])): ?>
                    <div>
                        <h6>Remarks</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($acquisition['remarks'] ?? '')); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Lead & Colony Info</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($acquisition['land_lead_id'])): ?>
                    <p class="mb-1"><strong>Land Lead ID:</strong> <?php echo e($acquisition['land_lead_id']); ?></p>
                    <?php else: ?>
                    <p class="text-muted mb-0">No land lead linked</p>
                    <?php endif; ?>
                    <?php if (!empty($acquisition['colony_id'])): ?>
                    <p class="mb-1"><strong>Colony ID:</strong> <?php echo e($acquisition['colony_id']); ?></p>
                    <?php else: ?>
                    <p class="text-muted mb-0">No colony linked</p>
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
                            <label class="form-label">Mutation Status</label>
                            <select name="mutation_status" class="form-select">
                                <option value="not_started" <?php echo ($acquisition['mutation_status'] ?? '') === 'not_started' ? 'selected' : ''; ?>>Not Started</option>
                                <option value="applied" <?php echo ($acquisition['mutation_status'] ?? '') === 'applied' ? 'selected' : ''; ?>>Applied</option>
                                <option value="in_progress" <?php echo ($acquisition['mutation_status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo ($acquisition['mutation_status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="rejected" <?php echo ($acquisition['mutation_status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="in_progress" <?php echo ($acquisition['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="registered" <?php echo ($acquisition['status'] ?? '') === 'registered' ? 'selected' : ''; ?>>Registered</option>
                                <option value="mutated" <?php echo ($acquisition['status'] ?? '') === 'mutated' ? 'selected' : ''; ?>>Mutated</option>
                                <option value="closed" <?php echo ($acquisition['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                <option value="cancelled" <?php echo ($acquisition['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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
