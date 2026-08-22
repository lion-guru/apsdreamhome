<?php
$financialYears = $financialYears ?? [];
$page_title = 'Financial Years Management';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-calendar-alt me-2"></i>Financial Years</h1>
            <p class="text-muted mb-0">Manage financial years and accounting periods</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFYModal">
                <i class="fas fa-plus me-1"></i>Add Financial Year
            </button>
            <a href="<?php echo BASE_URL; ?>/admin/banking" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Banking
            </a>
        </div>
    </div>

    <?php require __DIR__ . '/../partials/export_buttons.php'; ?>

    <!-- Financial Year Cards -->
    <div class="row g-4 mb-4">
        <?php if (empty($financialYears)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <h5>No Financial Years Defined</h5>
                        <p class="mb-3">Add a financial year to organize transactions by accounting period.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFYModal">
                            <i class="fas fa-plus me-1"></i>Add Financial Year
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($financialYears as $fy): ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($fy['year'] ?? $fy['name'] ?? 'N/A'); ?></h5>
                                    <small class="text-muted">
                                        <?php echo isset($fy['start_date']) ? date('d M Y', strtotime($fy['start_date'])) : '-'; ?>
                                        to
                                        <?php echo isset($fy['end_date']) ? date('d M Y', strtotime($fy['end_date'])) : '-'; ?>
                                    </small>
                                </div>
                                <span class="badge bg-<?php echo ($fy['is_active'] ?? $fy['status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($fy['is_active'] ?? $fy['status'] ?? 'active'); ?>
                                </span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="fas fa-chart-line me-1"></i>Periods: <?php echo $fy['period_count'] ?? 0; ?></span>
                                <span><i class="fas fa-exchange-alt me-1"></i>Transactions: <?php echo $fy['transaction_count'] ?? 0; ?></span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex gap-2">
                                <?php if (($fy['is_active'] ?? $fy['status'] ?? '') !== 'active'): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/banking/financial-years/activate/<?php echo e($fy['id']); ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-check me-1"></i>Activate
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>/admin/banking?financial_year=<?php echo urlencode($fy['year'] ?? $fy['name'] ?? ''); ?>" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye me-1"></i>View Transactions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Financial Year Modal -->
<div class="modal fade" id="addFYModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Financial Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/admin/banking/financial-years/store">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Financial Year <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="year" required placeholder="e.g. 2026-2027">
                        <small class="text-muted">Format: YYYY-YYYY (e.g. 2026-2027)</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="is_active">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Optional notes about this financial year"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
