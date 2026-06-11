<?php
$page_title = $page_title ?? 'Farmer Agreements';
$agreements = $agreements ?? [];
$totalAgreements = $totalAgreements ?? 0;
$activeCount = $active_count ?? 0;
$completedCount = $completed_count ?? 0;
$terminatedCount = $terminated_count ?? 0;
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-file-signature text-primary me-2"></i> Farmer Agreements</h4>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAgreementModal"><i class="fas fa-plus me-1"></i>New Agreement</button>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><div class="bg-primary bg-opacity-10 text-primary rounded p-3"><i class="fas fa-file-contract fa-2x"></i></div></div>
                <div><h6 class="text-muted mb-1">Total</h6><h3 class="mb-0"><?php echo $totalAgreements; ?></h3></div>
            </div>
        </div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><div class="bg-success bg-opacity-10 text-success rounded p-3"><i class="fas fa-check-circle fa-2x"></i></div></div>
                <div><h6 class="text-muted mb-1">Active</h6><h3 class="mb-0"><?php echo $activeCount; ?></h3></div>
            </div>
        </div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><div class="bg-info bg-opacity-10 text-info rounded p-3"><i class="fas fa-flag-checkered fa-2x"></i></div></div>
                <div><h6 class="text-muted mb-1">Completed</h6><h3 class="mb-0"><?php echo $completedCount; ?></h3></div>
            </div>
        </div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><div class="bg-danger bg-opacity-10 text-danger rounded p-3"><i class="fas fa-times-circle fa-2x"></i></div></div>
                <div><h6 class="text-muted mb-1">Terminated</h6><h3 class="mb-0"><?php echo $terminatedCount; ?></h3></div>
            </div>
        </div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Agreements</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Agreement#</th><th>Farmer</th><th>Type</th><th>Amount</th><th>Status</th><th>Start Date</th><th>End Date</th><th>Signed</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agreements as $a): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($a['agreement_number'] ?? 'N/A'); ?></code></td>
                            <td><strong><?php echo htmlspecialchars($a['farmer_name'] ?? 'N/A'); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($a['farmer_mobile'] ?? ''); ?></small></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $a['agreement_type'] ?? '')); ?></td>
                            <td>₹<?php echo number_format($a['total_amount'] ?? 0); ?></td>
                            <td>
                                <?php $s = $a['status'] ?? ''; ?>
                                <?php if ($s === 'active'): ?><span class="badge bg-success">Active</span>
                                <?php elseif ($s === 'completed'): ?><span class="badge bg-info">Completed</span>
                                <?php elseif ($s === 'terminated'): ?><span class="badge bg-danger">Terminated</span>
                                <?php elseif ($s === 'cancelled'): ?><span class="badge bg-warning text-dark">Cancelled</span>
                                <?php else: ?><span class="badge bg-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($a['start_date'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($a['end_date'] ?? '-'); ?></td>
                            <td>
                                <?php if ($a['signed_by_farmer'] ?? 0): ?><i class="fas fa-check-circle text-success" title="Farmer Signed"></i><?php endif; ?>
                                <?php if ($a['signed_by_company'] ?? 0): ?><i class="fas fa-check-circle text-primary ms-1" title="Company Signed"></i><?php endif; ?>
                                <?php if (!($a['signed_by_farmer'] ?? 0) && !($a['signed_by_company'] ?? 0)): ?><span class="text-muted">Pending</span><?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <a href="<?php echo BASE_URL; ?>/admin/farmers/agreements/<?php echo $a['id']; ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($agreements)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No agreements found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Agreement Modal -->
<div class="modal fade" id="addAgreementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?php echo BASE_URL; ?>/admin/farmers/agreements/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header"><h5 class="modal-title">New Agreement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Farmer ID</label>
                            <input type="number" name="farmer_id" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agreement Number</label>
                            <input type="text" name="agreement_number" class="form-control" placeholder="AGR-">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select name="agreement_type" class="form-select">
                                <option value="land_purchase">Land Purchase</option>
                                <option value="lease">Lease</option>
                                <option value="crop_share">Crop Share</option>
                                <option value="partnership">Partnership</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Amount</label>
                            <input type="number" step="0.01" name="total_amount" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Advance Amount</label>
                            <input type="number" step="0.01" name="advance_amount" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Commission Rate (%)</label>
                            <input type="number" step="0.01" name="commission_rate" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms_conditions" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Agreement</button>
                </div>
            </form>
        </div>
    </div>
</div>
