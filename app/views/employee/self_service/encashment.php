<?php
/**
 * Leave Encashment Dashboard
 */

$page_title = $page_title ?? 'Leave Encashment';
$policy = $policy ?? [];
$requests = $requests ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fas fa-money-bill-wave me-2 text-success"></i>Leave Encashment</h4>
                    <p class="text-muted mb-0 small">Convert your unused leave balance to cash</p>
                </div>
                <?php if (($policy['eligible_days'] ?? 0) > 0): ?>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#applyEncashmentModal">
                    <i class="fas fa-plus me-1"></i> Apply for Encashment
                </button>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- Policy Summary -->
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Your Encashment Policy</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fw-bold fs-3 text-success"><?= $policy['eligible_days'] ?? 0 ?></div>
                                <div class="small text-muted">Eligible Days</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fw-bold fs-3 text-primary"><?= $policy['max_encashable_days'] ?? 0 ?></div>
                                <div class="small text-muted">Max Encashable Days/Year</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fw-bold fs-3 text-info"><?= $policy['encashment_rate'] ?? '100%' ?></div>
                                <div class="small text-muted">Encashment Rate</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fw-bold fs-3 text-warning">₹<?= number_format($policy['estimated_amount'] ?? 0, 2) ?></div>
                                <div class="small text-muted">Estimated Amount</div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Policy:</strong> <?= htmlspecialchars($policy['policy_name'] ?? 'Standard') ?><br>
                                <strong>Leave Types Allowed:</strong> <?= htmlspecialchars($policy['allowed_leave_types'] ?? 'Annual, Casual') ?>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Frequency:</strong> <?= htmlspecialchars($policy['frequency'] ?? 'Once per financial year') ?><br>
                                <strong>Approval:</strong> Manager + HR
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Balance Breakdown -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Leave Balance Breakdown</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($policy['leave_balances'])): ?>
                        <div class="text-center py-4 text-muted">No leave balance data available</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Total Entitled</th>
                                        <th>Availed</th>
                                        <th>Balance</th>
                                        <th>Encashable</th>
                                        <th>Rate</th>
                                        <th>Est. Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($policy['leave_balances'] as $lb): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($lb['leave_type'] ?? '') ?></strong></td>
                                        <td><?= $lb['entitled'] ?? 0 ?></td>
                                        <td><?= $lb['availed'] ?? 0 ?></td>
                                        <td class="fw-bold"><?= $lb['balance'] ?? 0 ?></td>
                                        <td><?= $lb['encashable'] ?? 0 ?></td>
                                        <td><?= $lb['rate'] ?? '100%' ?></td>
                                        <td class="text-success fw-bold">₹<?= number_format($lb['estimated_amount'] ?? 0, 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Previous Requests -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Previous Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($requests)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No encashment requests yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reference</th>
                                        <th>Leave Type</th>
                                        <th>Days</th>
                                        <th>Amount</th>
                                        <th>Applied</th>
                                        <th>Status</th>
                                        <th>Processed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $r): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($r['reference_number'] ?? 'N/A') ?></code></td>
                                        <td><?= htmlspecialchars($r['leave_type'] ?? '') ?></td>
                                        <td><?= $r['days_requested'] ?? 0 ?></td>
                                        <td class="fw-bold text-success">₹<?= number_format($r['amount'] ?? 0, 2) ?></td>
                                        <td><?= date('d M Y', strtotime($r['created_at'] ?? 'now')) ?></td>
                                        <td>
                                            <span class="badge <?= $r['status'] === 'approved' ? 'bg-success' : ($r['status'] === 'rejected' ? 'bg-danger' : ($r['status'] === 'paid' ? 'bg-info' : 'bg-warning text-dark')) ?>">
                                                <?= ucfirst($r['status'] ?? 'pending') ?>
                                            </span>
                                        </td>
                                        <td><?= $r['processed_at'] ? date('d M Y', strtotime($r['processed_at'])) : '-' ?></td>
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

<!-- Apply Encashment Modal -->
<div class="modal fade" id="applyEncashmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/employee/self-service/encashment/apply" id="encashmentApplyForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Apply Leave Encashment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label for="leave_type_id" class="col-sm-3 col-form-label fw-bold">Leave Type <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-select" id="leave_type_id" name="leave_type_id" required>
                                <option value="">Select leave type</option>
                                <?php foreach ($policy['leave_balances'] ?? [] as $lb): ?>
                                    <?php if (($lb['encashable'] ?? 0) > 0): ?>
                                    <option value="<?= $lb['leave_type_id'] ?? 0 ?>" data-balance="<?= $lb['encashable'] ?? 0 ?>" data-rate="<?= $lb['rate'] ?? '100%' ?>">
                                        <?= htmlspecialchars($lb['leave_type'] ?? '') ?> (<?= $lb['encashable'] ?? 0 ?> days available)
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Only leave types with encashable balance are shown.</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="days_to_encash" class="col-sm-3 col-form-label fw-bold">Days to Encash <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="days_to_encash" name="days_to_encash" step="0.5" min="0.5" max="<?= $policy['max_encashable_days'] ?? 30 ?>" required>
                            <div class="form-text">Maximum: <?= $policy['max_encashable_days'] ?? 30 ?> days per financial year</div>
                        </div>
                    </div>
                    <div class="card bg-light mb-3" id="encashmentPreview" style="display: none;">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="fw-bold text-primary" id="previewDays">0</div>
                                    <small class="text-muted">Days</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold text-info" id="previewRate">100%</div>
                                    <small class="text-muted">Rate</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold text-success" id="previewAmount">₹0</div>
                                    <small class="text-muted">Est. Amount</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function() {
    const leaveTypeSelect = document.getElementById('leave_type_id');
    const daysInput = document.getElementById('days_to_encash');
    const previewCard = document.getElementById('encashmentPreview');
    const previewDays = document.getElementById('previewDays');
    const previewRate = document.getElementById('previewRate');
    const previewAmount = document.getElementById('previewAmount');

    function updatePreview() {
        const option = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        const days = parseFloat(daysInput.value) || 0;
        
        if (option.value && days > 0) {
            const balance = parseFloat(option.dataset.balance) || 0;
            const rate = option.dataset.rate || '100%';
            
            if (days <= balance) {
                previewCard.style.display = 'block';
                previewDays.textContent = days;
                previewRate.textContent = rate;
                
                // Calculate amount based on daily rate
                // This is a simplified calculation - actual would use daily salary
                const dailyRate = 1000; // placeholder - would come from policy
                const rateMultiplier = parseFloat(rate) / 100 || 1;
                const amount = days * dailyRate * rateMultiplier;
                
                previewAmount.textContent = '₹' + amount.toLocaleString('en-IN', {maximumFractionDigits: 2});
            } else {
                previewCard.style.display = 'none';
            }
        } else {
            previewCard.style.display = 'none';
        }
    }

    leaveTypeSelect.addEventListener('change', updatePreview);
    daysInput.addEventListener('input', updatePreview);

    // Validation
    daysInput.addEventListener('input', function() {
        const option = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        const balance = parseFloat(option.dataset.balance) || 0;
        const maxDays = parseFloat(daysInput.max) || 30;
        
        if (this.value > balance) {
            this.setCustomValidity('Cannot exceed available encashable balance (' + balance + ' days)');
        } else if (this.value > maxDays) {
            this.setCustomValidity('Cannot exceed maximum allowed (' + maxDays + ' days)');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>