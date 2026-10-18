<?php
/**
 * Salary Advances & Loans Dashboard
*/

$page_title = $page_title ?? 'Salary Advances & Loans';
$advances = $advances ?? [];
$pending_repayments = $pending_repayments ?? [];
$summary = $summary ?? ['total_advances' => 0, 'total_pending' => 0, 'total_repaid' => 0];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.advance-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.advance-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); transform: translateY(-1px); }
.advance-pending { border-left: 4px solid #ffc107; }
.advance-approved { border-left: 4px solid #22c55e; }
.advance-disbursed { border-left: 4px solid #3b82f6; }
.advance-repaid { border-left: 4px solid #22c55e; }
.advance-rejected { border-left: 4px solid #ef4444; }
.advance-overdue { border-left: 4px solid #dc3545; }
.repayment-row { transition: background 0.15s; }
.repayment-row:hover { background: #fef3e2; }
.repayment-overdue { background: #fef2f2; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-hand-holding-usd me-2 text-primary"></i>Salary Advances & Loans</h4>
            <p class="text-muted mb-0 small">Request advances, track loans, manage repayments</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyAdvanceModal">
            <i class="fas fa-plus me-1"></i> Apply for Advance / Loan
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-primary"><?= $summary['total_advances'] ?></div>
                    <div class="small text-muted">Total Requests</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-warning">₹<?= number_format($summary['total_pending'], 0) ?></div>
                    <div class="small text-muted">Pending Approval</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-success">₹<?= number_format($summary['total_repaid'], 0) ?></div>
                    <div class="small text-muted">Total Repaid</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-info"><?= count($pending_repayments) ?></div>
                    <div class="small text-muted">Due Repayments</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Due Repayments Alert -->
    <?php if (!empty($pending_repayments)): ?>
    <div class="alert alert-warning mb-4">
        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Pending Repayments</h6>
        <p class="mb-2">You have <strong><?= count($pending_repayments) ?></strong> repayment(s) due or overdue:</p>
        <ul class="mb-0">
            <?php foreach (array_slice($pending_repayments, 0, 3) as $r): ?>
                <li><?= date('d M Y', strtotime($r['due_date'])) ?> - ₹<?= number_format($r['total_amount'] - $r['paid_amount'], 2) ?> 
                    (Installment #<?= $r['installment_no'] ?>) 
                    <span class="badge bg-<?= $r['status'] === 'overdue' ? 'danger' : 'warning' ?> ms-1">
                        <?= ucfirst($r['status']) ?>
                    </span>
                </li>
            <?php endforeach; ?>
            <?php if (count($pending_repayments) > 3): ?>
                <li>... and <?= count($pending_repayments) - 3 ?> more</li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Advances Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="advancesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>EMI/Installments</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Approved</th>
                            <th>Repayment Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($advances as $a): 
                            $repaid = $a['amount'] - $a['remaining_balance'];
                            $progress = $a['amount'] > 0 ? round(($repaid / $a['amount']) * 100) : 0;
                            $statusClass = [
                                'pending' => 'warning',
                                'approved' => 'info',
                                'disbursed' => 'primary',
                                'repaid' => 'success',
                                'rejected' => 'danger',
                                'cancelled' => 'secondary'
                            ][$a['status']] ?? 'secondary';
                        ?>
                        <tr>
                            <td>
                                <span class="badge bg-<?= $a['type'] === 'loan' ? 'primary' : 'success' ?>">
                                    <i class="fas fa-<?= $a['type'] === 'loan' ? 'university' : 'hand-holding-usd' ?> me-1"></i>
                                    <?= ucfirst($a['type']) ?>
                                </span>
                            </td>
                            <td class="fw-bold">₹<?= number_format($a['amount'], 2) ?></td>
                            <td>
                                <?php if ($a['type'] === 'loan'): ?>
                                    <span class="badge bg-info"><?= $a['installments'] ?> EMI</span>
                                    <?php if ($a['emi_amount']): ?>
                                        <div class="small text-muted">₹<?= number_format($a['emi_amount'], 2) ?>/month</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">One-time</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($a['status']) ?></span>
                                <?php if ($a['status'] === 'disbursed'): ?>
                                    <div class="small text-muted">Ref: <?= htmlspecialchars($a['disbursement_ref'] ?? 'N/A') ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($a['requested_at'])) ?></td>
                            <td><?= $a['approved_at'] ? date('d M Y', strtotime($a['approved_at'])) : '-' ?></td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-<?= $a['status'] === 'repaid' ? 'success' : ($a['status'] === 'disbursed' ? 'primary' : 'warning') ?>" 
                                         style="width: <?= $progress ?>%"></div>
                                </div>
                                <small class="text-muted"><?= $progress ?>%</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/employee/self-service/advances/<?= $a['id'] ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($a['status'] === 'pending'): ?>
                                        <button class="btn btn-outline-danger" onclick="cancelAdvance(<?= $a['id'] ?>)" title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($advances)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Advances or Loans</h5>
                                <p class="text-muted small mb-0">Click "Apply for Advance / Loan" to get started</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Due Repayments Section -->
    <?php if (!empty($pending_repayments)): ?>
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Due / Overdue Repayments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Advance Type</th>
                            <th>Installment #</th>
                            <th>Due Date</th>
                            <th>Total Amount</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_repayments as $r): 
                            $balance = $r['total_amount'] - $r['paid_amount'];
                            $isOverdue = $r['status'] === 'overdue';
                        ?>
                        <tr class="<?= $isOverdue ? 'repayment-overdue' : '' ?>">
                            <td>
                                <span class="badge bg-<?= $r['advance_type'] === 'loan' ? 'primary' : 'success' ?>">
                                    <?= ucfirst($r['advance_type']) ?>
                                </span>
                            </td>
                            <td>#<?= $r['installment_no'] ?></td>
                            <td class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                <?= date('d M Y', strtotime($r['due_date'])) ?>
                                <?php if ($isOverdue): ?>
                                    <i class="fas fa-exclamation-circle ms-1"></i>
                                <?php endif; ?>
                            </td>
                            <td>₹<?= number_format($r['total_amount'], 2) ?></td>
                            <td>₹<?= number_format($r['paid_amount'], 2) ?></td>
                            <td class="fw-bold text-<?= $isOverdue ? 'danger' : 'warning' ?>">₹<?= number_format($r['total_amount'] - $r['paid_amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $isOverdue ? 'danger' : 'warning' ?>">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/employee/self-service/advances/<?= $r['advance_id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Apply Advance Modal -->
<div class="modal fade" id="applyAdvanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="advanceForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-hand-holding-usd me-2"></i>Apply for Salary Advance / Loan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" id="advanceType" required onchange="toggleInstallments()">
                                <option value="">Select type</option>
                                <option value="advance">Salary Advance (One-time, no interest)</option>
                                <option value="loan">Loan (EMI with interest)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="1000" name="amount" class="form-control" required placeholder="Enter amount">
                        </div>
                        <div class="col-md-6" id="installmentsGroup" style="display: none;">
                            <label class="form-label fw-semibold">Number of Installments <span class="text-danger">*</span></label>
                            <select name="installments" class="form-select" id="installmentsSelect" required>
                                <option value="">Select installments</option>
                                <option value="2">2 Months</option>
                                <option value="3">3 Months</option>
                                <option value="4">4 Months</option>
                                <option value="6">6 Months</option>
                                <option value="8">8 Months</option>
                                <option value="10">10 Months</option>
                                <option value="12">12 Months</option>
                                <option value="18">18 Months</option>
                                <option value="24">24 Months</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="interestGroup" style="display: none;">
                            <label class="form-label fw-semibold">Interest Rate (% per annum) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" min="0" max="24" name="interest_rate" class="form-control" value="12" required placeholder="e.g., 12">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Briefly explain the purpose..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function toggleInstallments() {
    const type = document.getElementById('advanceType').value;
    const installmentsGroup = document.getElementById('installmentsGroup');
    const interestGroup = document.getElementById('interestGroup');
    
    if (type === 'loan') {
        installmentsGroup.style.display = 'block';
        interestGroup.style.display = 'block';
    } else {
        installmentsGroup.style.display = 'none';
        interestGroup.style.display = 'none';
    }
}

function cancelAdvance(id) {
    if (!confirm('Are you sure you want to cancel this advance/loan request?')) return;
    
    fetch('<?= BASE_URL ?>/employee/self-service/advances/cancel/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'csrf_token=' + encodeURIComponent('<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>')
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed to cancel');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    toggleInstallments();
});
</script>