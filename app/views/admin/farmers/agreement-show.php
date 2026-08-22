<?php
$page_title = $page_title ?? 'Agreement Details';
$agreement = $agreement ?? [];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-file-signature text-primary me-2"></i> Agreement: <code><?php echo htmlspecialchars($agreement['agreement_number'] ?? 'N/A'); ?></code></h4>
        <a href="<?php echo BASE_URL; ?>/admin/farmers/agreements" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo e($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo e($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Agreement Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Agreement #</td><td><strong><code><?php echo htmlspecialchars($agreement['agreement_number'] ?? 'N/A'); ?></code></strong></td></tr>
                        <tr><td class="text-muted">Farmer</td><td><strong><?php echo htmlspecialchars($agreement['farmer_name'] ?? 'N/A'); ?></strong></td></tr>
                        <tr><td class="text-muted">Mobile</td><td><?php echo htmlspecialchars($agreement['farmer_mobile'] ?? ''); ?></td></tr>
                        <tr><td class="text-muted">Type</td><td><span class="badge bg-primary"><?php echo ucfirst(str_replace('_', ' ', $agreement['agreement_type'] ?? '')); ?></span></td></tr>
                        <tr><td class="text-muted">Status</td>
                            <td><?php $s = $agreement['status'] ?? ''; ?>
                                <?php if ($s === 'active'): ?><span class="badge bg-success">Active</span>
                                <?php elseif ($s === 'completed'): ?><span class="badge bg-info">Completed</span>
                                <?php elseif ($s === 'terminated'): ?><span class="badge bg-danger">Terminated</span>
                                <?php elseif ($s === 'cancelled'): ?><span class="badge bg-warning text-dark">Cancelled</span>
                                <?php else: ?><span class="badge bg-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><td class="text-muted">Start Date</td><td><?php echo htmlspecialchars($agreement['start_date'] ?? '-'); ?></td></tr>
                        <tr><td class="text-muted">End Date</td><td><?php echo htmlspecialchars($agreement['end_date'] ?? '-'); ?></td></tr>
                        <tr><td class="text-muted">Signed Date</td><td><?php echo htmlspecialchars($agreement['signed_date'] ?? '-'); ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Financial Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Total Amount</td><td><strong>₹<?php echo number_format($agreement['total_amount'] ?? 0); ?></strong></td></tr>
                        <tr><td class="text-muted">Advance Amount</td><td class="text-warning"><strong>₹<?php echo number_format($agreement['advance_amount'] ?? 0); ?></strong></td></tr>
                        <tr><td class="text-muted">Commission Rate</td><td><?php echo htmlspecialchars($agreement['commission_rate'] ?? '0'); ?>%</td></tr>
                    </table></div>

                    <h6 class="mt-3">Signing Status</h6>
                    <div class="d-flex gap-3">
                        <div><i class="fas fa-<?php echo ($agreement['signed_by_farmer'] ?? 0) ? 'check-circle text-success' : 'times-circle text-muted'; ?>"></i> Farmer</div>
                        <div><i class="fas fa-<?php echo ($agreement['signed_by_company'] ?? 0) ? 'check-circle text-success' : 'times-circle text-muted'; ?>"></i> Company</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($agreement['terms_conditions'] ?? ''): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-gavel me-2"></i>Terms & Conditions</h5></div>
        <div class="card-body aps-cp-card-body"><?php echo nl2br(htmlspecialchars($agreement['terms_conditions'] ?? '')); ?></div>
    </div>
    <?php endif; ?>

    <?php if ($agreement['remarks'] ?? ''): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Remarks</h5></div>
        <div class="card-body aps-cp-card-body"><?php echo nl2br(htmlspecialchars($agreement['remarks'] ?? '')); ?></div>
    </div>
    <?php endif; ?>

    <?php if ($agreement['document_path'] ?? ''): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-file-pdf me-2"></i>Document</h5></div>
        <div class="card-body aps-cp-card-body">
            <a href="<?php echo htmlspecialchars($agreement['document_path'] ?? ''); ?>" class="btn btn-outline-danger" target="_blank"><i class="fas fa-file-pdf me-1"></i>View Document</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h5></div>
        <div class="card-body aps-cp-card-body">
            <form method="post" action="<?php echo BASE_URL; ?>/admin/farmers/agreements/update-status/<?php echo $agreement['id'] ?? 0; ?>" class="row g-3">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?php echo ($agreement['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="active" <?php echo ($agreement['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="completed" <?php echo ($agreement['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="terminated" <?php echo ($agreement['status'] ?? '') === 'terminated' ? 'selected' : ''; ?>>Terminated</option>
                        <option value="cancelled" <?php echo ($agreement['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Signed by Farmer</label>
                    <select name="signed_by_farmer" class="form-select">
                        <option value="0">No</option>
                        <option value="1" <?php echo ($agreement['signed_by_farmer'] ?? 0) ? 'selected' : ''; ?>>Yes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Signed by Company</label>
                    <select name="signed_by_company" class="form-select">
                        <option value="0">No</option>
                        <option value="1" <?php echo ($agreement['signed_by_company'] ?? 0) ? 'selected' : ''; ?>>Yes</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update</button>
                </div>
                <div class="col-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2"><?php echo htmlspecialchars($agreement['remarks'] ?? ''); ?></textarea>
                </div>
            </form>
        </div>
    </div>
</div>
