<?php
$page_title = 'Agreement ' . ($agreement['agreement_number'] ?? '') . ' - APS Dream Home';
$active_page = 'agreements';
$a = $agreement ?? [];
$related_docs = $related_docs ?? [];
$documents = $documents ?? [];
$typeLabels = ['sale_deed' => 'Sale Deed', 'allotment' => 'Allotment', 'mortgage' => 'Mortgage', 'lease' => 'Lease', 'nda' => 'NDA', 'joint_venture' => 'Joint Venture', 'other' => 'Other'];
$statusLabels = ['draft' => 'Draft', 'pending_signature' => 'Pending Signature', 'signed' => 'Signed', 'registered' => 'Registered', 'cancelled' => 'Cancelled', 'expired' => 'Expired'];
$nextStatuses = [
    'draft' => ['pending_signature'],
    'pending_signature' => ['signed', 'cancelled'],
    'signed' => ['registered', 'cancelled'],
    'registered' => [],
    'cancelled' => [],
    'expired' => []
];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-file-contract me-2"></i><?= $a['agreement_number'] ?? 'Agreement' ?></h1>
    <div class="d-flex gap-2">
        <?php if ($a['booking_id']): ?>
            <a href="<?= BASE_URL ?>/admin/agreements/preview/<?= $a['booking_id'] ?>/allotment" class="btn btn-success" target="_blank"><i class="fas fa-file-pdf me-1"></i>Generate PDF</a>
            <a href="<?= BASE_URL ?>/admin/agreements/download/<?= $a['booking_id'] ?>" class="btn btn-outline-success"><i class="fas fa-download me-1"></i>Download</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/admin/agreements" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_success'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_error'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="row">
    <!-- Left: Agreement Details -->
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Agreement Details</h5>
                <?php
                $statusBadge = match($a['status'] ?? 'draft') {
                    'draft' => 'secondary',
                    'pending_signature' => 'warning',
                    'signed' => 'info',
                    'registered' => 'success',
                    'cancelled' => 'danger',
                    'expired' => 'dark',
                    default => 'secondary'
                };
                ?>
                <span class="badge bg-<?= $statusBadge ?> fs-6"><?= $statusLabels[$a['status']] ?? ucfirst($a['status']) ?></span>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small">Agreement Number</label>
                        <p class="fw-bold mb-0"><code><?= htmlspecialchars($a['agreement_number'] ?? '') ?></code></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Type</label>
                        <p class="mb-0"><span class="badge bg-primary"><?= $typeLabels[$a['agreement_type']] ?? $a['agreement_type'] ?></span></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Plot</label>
                        <p class="fw-bold mb-0">
                            <?= htmlspecialchars($a['plot_number'] ?? 'â€”') ?>
                            <?php if (!empty($a['block'])): ?>
                                <span class="text-muted"> / <?= htmlspecialchars($a['block']) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small">Colony</label>
                        <p class="mb-0"><?= htmlspecialchars($a['colony_name'] ?? 'â€”') ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Booking Reference</label>
                        <p class="mb-0">
                            <?php if ($a['booking_id']): ?>
                                <a href="<?= BASE_URL ?>/admin/bookings/<?= $a['booking_id'] ?>">BK-<?= $a['booking_id'] ?></a>
                            <?php else: ?>
                                â€”
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Agreement Date</label>
                        <p class="mb-0"><?= date('d M Y', strtotime($a['agreement_date'] ?? 'now')) ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small">Validity Date</label>
                        <p class="mb-0"><?= $a['validity_date'] ? date('d M Y', strtotime($a['validity_date'])) : 'â€”' ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Created</label>
                        <p class="mb-0"><?= $a['created_at'] ? date('d M Y H:i', strtotime($a['created_at'])) : 'â€”' ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Last Updated</label>
                        <p class="mb-0"><?= $a['updated_at'] ? date('d M Y H:i', strtotime($a['updated_at'])) : 'â€”' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parties -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Parties</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light">
                            <h6 class="text-primary">Party A (Seller / Company)</h6>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($a['party_a_name'] ?? 'â€”') ?></p>
                            <small class="text-muted">ID: <?= $a['party_a_id'] ?? 'â€”' ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light">
                            <h6 class="text-success">Party B (Buyer / Tenant)</h6>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($a['party_b_name'] ?? 'â€”') ?></p>
                            <small class="text-muted">ID: <?= $a['party_b_id'] ?? 'â€”' ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financials -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-rupee-sign me-2"></i>Financial Details</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted">Total Value</h6>
                            <h4 class="text-primary mb-0">Rs. <?= number_format(floatval($a['total_value'] ?? 0), 0) ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted">Stamp Duty</h6>
                            <h4 class="text-warning mb-0">Rs. <?= number_format(floatval($a['stamp_duty_amount'] ?? 0), 0) ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted">Registration Fee</h6>
                            <h4 class="text-info mb-0">Rs. <?= number_format(floatval($a['registration_fee'] ?? 0), 0) ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted">Registration Date</h6>
                            <p class="mb-0 fw-bold"><?= $a['registration_date'] ? date('d M Y', strtotime($a['registration_date'])) : 'â€”' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <?php if (!empty($a['notes'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p class="mb-0"><?= nl2br(htmlspecialchars($a['notes'])) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Generated Documents -->
        <?php if (!empty($documents)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Generated Documents (<?= count($documents) ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr><th>Doc #</th><th>Type</th><th>Generated</th><th class="text-center">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $d): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($d['document_number'] ?? '') ?></code></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst($d['document_type'] ?? '') ?></span></td>
                                    <td><small><?= $d['created_at'] ? date('d M Y H:i', strtotime($d['created_at'])) : 'â€”' ?></small></td>
                                    <td class="text-center">
                                        <?php if (!empty($d['file_path']) && file_exists(STORAGE_PATH . '/uploads/' . $d['file_path'])): ?>
                                            <a href="<?= BASE_URL ?>/storage/uploads/<?= $d['file_path'] ?>" class="btn btn-sm btn-outline-success" target="_blank"><i class="fas fa-download"></i> PDF</a>
                                        <?php else: ?>
                                            <span class="text-muted small">Not generated</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right: Actions Sidebar -->
    <div class="col-md-4">
        <!-- Status Actions -->
        <?php if (!empty($nextStatuses[$a['status']] ?? [])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/agreements/update/<?= $a['id'] ?>" id="statusForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="action" id="statusAction" value="">

                        <label class="form-label fw-bold">Change Status</label>
                        <select name="status" class="form-select mb-2" id="statusSelect">
                            <?php foreach ($nextStatuses[$a['status']] as $ns): ?>
                                <option value="<?= $ns ?>"><?= $statusLabels[$ns] ?? ucfirst($ns) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <div class="mb-3" id="reasonGroup" class="style-2248">
                            <label class="form-label">Rejection/Cancellation Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" id="reasonField"></textarea>
                        </div>

                        <button type="button" class="btn btn-primary w-100 mb-2" onclick="submitStatus('update')">
                            <i class="fas fa-save me-1"></i>Update Status
                        </button>
                    </form>

                    <?php if ($a['status'] === 'draft' || $a['status'] === 'pending_signature'): ?>
                        <form method="POST" action="<?= BASE_URL ?>/admin/agreements/send/<?= $a['id'] ?>" class="mt-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="btn btn-outline-info w-100"><i class="fas fa-paper-plane me-1"></i>Send to Customer</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Info -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Info</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ID</span>
                        <strong>#<?= $a['id'] ?? 'â€”' ?></strong>
                    </li>
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Type</span>
                        <span class="badge bg-primary"><?= $typeLabels[$a['agreement_type']] ?? 'â€”' ?></span>
                    </li>
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-<?= $statusBadge ?>"><?= $statusLabels[$a['status']] ?? 'â€”' ?></span>
                    </li>
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Plot</span>
                        <strong><?= htmlspecialchars($a['plot_number'] ?? 'â€”') ?></strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Value</span>
                        <strong>Rs. <?= number_format(floatval($a['total_value'] ?? 0), 0) ?></strong>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Related Agreement -->
        <?php if (!empty($related_docs)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Related Documents (<?= count($related_docs) ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($related_docs as $rd): ?>
                            <li class="list-group-item">
                                <small class="text-muted"><?= htmlspecialchars($rd['document_type'] ?? '') ?></small><br>
                                <code class="small"><?= htmlspecialchars($rd['document_number'] ?? '') ?></code>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('statusSelect')?.addEventListener('change', function() {
    const v = this.value;
    document.getElementById('reasonGroup').style.display = (v === 'cancelled' || v === 'expired') ? 'block' : 'none';
    document.getElementById('reasonField').required = (v === 'cancelled' || v === 'expired');
});

function submitStatus(action) {
    const form = document.getElementById('statusForm');
    const status = document.getElementById('statusSelect').value;
    const reason = document.getElementById('reasonField')?.value || '';

    if ((status === 'cancelled' || status === 'expired') && !reason.trim()) {
        alert('Reason is required for cancellation/expiration.');
        return;
    }

    document.getElementById('statusAction').value = action;
    form.submit();
}
</script>
