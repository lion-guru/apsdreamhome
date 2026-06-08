<?php
$page_title = $page_title ?? 'NOC Details';
$page_heading = $page_heading ?? 'NOC Request Details';
$noc = $noc ?? [];
$eligibility = $eligibility ?? null;
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-file-contract me-2"></i>NOC #<?= $noc['id'] ?? 0 ?></h2>
            <p class="text-muted mb-0"><?= htmlspecialchars($noc['booking_number'] ?? '') ?> — <?= htmlspecialchars($noc['customer_name'] ?? '') ?></p>
        </div>
        <a href="<?= BASE_URL ?>/admin/noc-registry/nocs" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>NOC Information</h5></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Status</div>
                        <div class="col-sm-8">
                            <?php $colors = ['pending'=>'warning','processing'=>'info','approved'=>'success','blocked'=>'danger','rejected'=>'dark','cancelled'=>'secondary']; ?>
                            <span class="badge bg-<?= $colors[$noc['status']] ?? 'secondary' ?> px-3 py-2 fs-6"><?= ucfirst($noc['status'] ?? '') ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Customer</div>
                        <div class="col-sm-8"><strong><?= htmlspecialchars($noc['customer_name'] ?? '') ?></strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Plot</div>
                        <div class="col-sm-8"><?= htmlspecialchars(($noc['block'] ?? '') . '-' . ($noc['plot_number'] ?? '')) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Booking Value</div>
                        <div class="col-sm-8">₹<?= number_format($noc['total_plot_value'] ?? 0) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Purpose</div>
                        <div class="col-sm-8"><?= htmlspecialchars($noc['purpose'] ?? '-') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Notes</div>
                        <div class="col-sm-8"><?= nl2br(htmlspecialchars($noc['notes'] ?? '-')) ?></div>
                    </div>
                    <?php if (!empty($noc['rejection_reason'])): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Rejection Reason</div>
                        <div class="col-sm-8 text-danger"><?= htmlspecialchars($noc['rejection_reason']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Created</div>
                        <div class="col-sm-8"><?= $noc['created_at'] ? date('d M Y H:i', strtotime($noc['created_at'])) : 'N/A' ?></div>
                    </div>
                </div>
            </div>

            <?php if ($eligibility): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>Eligibility Checks
                        <?php if ($eligibility['eligible']): ?>
                            <span class="badge bg-success float-end">ELIGIBLE</span>
                        <?php else: ?>
                            <span class="badge bg-danger float-end">BLOCKED</span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>Check</th><th>Result</th><th>Detail</th></tr></thead>
                        <tbody>
                            <?php foreach ($eligibility['checks'] as $chk): ?>
                                <tr class="<?= $chk['passed'] ? '' : 'table-danger' ?>">
                                    <td><strong><?= htmlspecialchars($chk['name']) ?></strong></td>
                                    <td>
                                        <?php if ($chk['passed']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>PASS</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fas fa-times me-1"></i>FAIL</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($chk['detail'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5></div>
                <div class="card-body">
                    <?php if (($noc['status'] ?? '') === 'pending' || ($noc['status'] ?? '') === 'blocked'): ?>
                        <div class="d-grid gap-2">
                            <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/nocs/reprocess">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="id" value="<?= $noc['id'] ?? 0 ?>">
                                <button type="submit" class="btn btn-info w-100"><i class="fas fa-sync me-2"></i>Re-check Eligibility</button>
                            </form>
                            <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/nocs/approve">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="id" value="<?= $noc['id'] ?? 0 ?>">
                                <button type="submit" class="btn btn-success w-100"><i class="fas fa-check me-2"></i>Approve NOC</button>
                            </form>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#rejectForm">
                                <i class="fas fa-times me-2"></i>Reject NOC
                            </button>
                        </div>
                        <div class="collapse mt-3" id="rejectForm">
                            <form method="POST" action="<?= BASE_URL ?>/admin/noc-registry/nocs/reject">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="id" value="<?= $noc['id'] ?? 0 ?>">
                                <textarea class="form-control mb-2" name="reason" rows="3" placeholder="Rejection reason..." required></textarea>
                                <button type="submit" class="btn btn-danger w-100">Confirm Reject</button>
                            </form>
                        </div>
                    <?php elseif (($noc['status'] ?? '') === 'approved'): ?>
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i>NOC is approved. You can now <a href="<?= BASE_URL ?>/admin/noc-registry/registries/create">request registry</a>.
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No actions available for this status.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h5></div>
                <div class="card-body">
                    <a href="<?= BASE_URL ?>/admin/noc-registry?booking_id=<?= $noc['booking_id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary w-100 mb-2"><i class="fas fa-search me-1"></i>Check Eligibility</a>
                    <a href="<?= BASE_URL ?>/admin/bookings" class="btn btn-sm btn-outline-secondary w-100"><i class="fas fa-arrow-right me-1"></i>View Booking</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
