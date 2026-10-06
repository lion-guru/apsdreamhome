<?php
/**
 * Reimbursement Claims
*/

$page_title = $page_title ?? 'Reimbursement Claims';
$reimbursements = $reimbursements ?? [];
$claim_types = $claim_types ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Reimbursement Claims</h4>
                </div>
                <div class="card-body">
                    <!-- Submit New Claim -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Submit New Claim</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" class="row g-3" id="reimbursementForm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="col-md-4">
                                    <label class="form-label">Claim Type <span class="text-danger">*</span></label>
                                    <select name="claim_type" class="form-select" required>
                                        <option value="">Select Claim Type</option>
                                        <?php foreach ($claim_types as $key => $label): ?>
                                        <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expense_date" class="form-control" required max="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control" rows="3" required placeholder="Describe the expense (purpose, vendor, etc.)"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Receipt / Bill <span class="text-danger">*</span></label>
                                    <input type="file" name="receipt_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="text-muted">PDF, JPG, PNG (Max 5MB)</small>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fas fa-paper-plane me-1"></i> Submit Claim
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Claims History -->
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>My Claims</h5>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary active" data-filter="all">All</button>
                                <button type="button" class="btn btn-outline-secondary" data-filter="pending">Pending</button>
                                <button type="button" class="btn btn-outline-secondary" data-filter="approved">Approved</button>
                                <button type="button" class="btn btn-outline-secondary" data-filter="rejected">Rejected</button>
                                <button type="button" class="btn btn-outline-secondary" data-filter="paid">Paid</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($reimbursements)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Claims Submitted</h5>
                                <p class="text-muted">Submit your first reimbursement claim using the form above</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="claimsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-center">Status</th>
                                            <th>Submitted</th>
                                            <th>Processed</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reimbursements as $r): ?>
                                        <tr data-status="<?= $r['status'] ?>">
                                            <td><?= date('d M Y', strtotime($r['expense_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($claim_types[$r['claim_type']] ?? $r['claim_type']) ?></span>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars(substr($r['description'] ?? '', 0, 60)) ?></small>
                                            </td>
                                            <td class="text-end fw-bold">₹<?= number_format($r['amount'] ?? 0, 2) ?></td>
                                            <td class="text-center">
                                                <?php $st = $r['status'] ?? 'pending'; ?>
                                                <span class="badge bg-<?= 
                                                    $st === 'paid' ? 'success' : 
                                                    ($st === 'approved' ? 'info' : 
                                                    ($st === 'rejected' ? 'danger' : 'warning')) ?>">
                                                    <?= ucfirst($st) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                            <td>
                                                <?php if ($r['status'] === 'paid' && $r['paid_at']): ?>
                                                <?= date('d M Y', strtotime($r['paid_at'])) ?>
                                                <?php elseif ($r['approved_at']): ?>
                                                <?= date('d M Y', strtotime($r['approved_at'])) ?>
                                                <?php else: ?>
                                                -
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-info" 
                                                        onclick="viewClaimDetails(<?= htmlspecialchars(json_encode($r)) ?>)" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($r['receipt_path']): ?>
                                                    <a href="<?= BASE_URL . '/' . $r['receipt_path'] ?>" 
                                                        class="btn btn-outline-primary" target="_blank" title="View Receipt">
                                                        <i class="fas fa-file-alt"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Claim Detail Modal -->
<div class="modal fade" id="claimDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Claim Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="claimDetailBody">
                <!-- Filled by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
.badge { font-size: 0.75rem; }
#claimsTable tbody tr { transition: background 0.2s; }
#claimsTable tbody tr:hover { background-color: #f8f9fa; }
.btn-group-sm .btn { border-radius: 0.375rem !important; margin: 0 2px; }
</style>

<script>
document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        filterClaims(this.dataset.filter);
    });
});

function filterClaims(status) {
    document.querySelectorAll('#claimsTable tbody tr').forEach(row => {
        const rowStatus = row.dataset.status;
        if (status === 'all' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function viewClaimDetails(r) {
    const modal = new bootstrap.Modal(document.getElementById('claimDetailModal'));
    document.getElementById('claimDetailBody').innerHTML = `
        <div class="row mb-3">
            <div class="col-6"><strong>Claim Type:</strong></div>
            <div class="col-6"><span class="badge bg-secondary">${r.claim_type}</span></div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Amount:</strong></div>
            <div class="col-6 text-end fw-bold text-success">₹${Number(r.amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Expense Date:</strong></div>
            <div class="col-6">${new Date(r.expense_date).toLocaleDateString()}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Description:</strong></div>
            <div class="col-6">${r.description}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Status:</strong></div>
            <div class="col-6"><span class="badge bg-${r.status === 'paid' ? 'success' : (r.status === 'approved' ? 'info' : (r.status === 'rejected' ? 'danger' : 'warning'))}">${r.status}</span></div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Submitted On:</strong></div>
            <div class="col-6">${new Date(r.created_at).toLocaleString()}</div>
        </div>
        ${r.approved_at ? `<div class="row mb-3"><div class="col-6"><strong>Approved On:</strong></div><div class="col-6">${new Date(r.approved_at).toLocaleString()}</div></div>` : ''}
        ${r.paid_at ? `<div class="row mb-3"><div class="col-6"><strong>Paid On:</strong></div><div class="col-6">${new Date(r.paid_at).toLocaleString()}</div></div>` : ''}
        ${r.rejection_reason ? `<div class="row mb-3"><div class="col-6"><strong>Rejection Reason:</strong></div><div class="col-6 text-danger">${r.rejection_reason}</div></div>` : ''}
        ${r.receipt_path ? `<div class="row mb-3"><div class="col-12"><strong>Receipt:</strong><br><a href="${BASE_URL}/${r.receipt_path}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-alt me-1"></i> View Receipt</a></div></div>` : ''}
    `;
    modal.show();
}
</script>