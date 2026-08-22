<?php
$transaction = $transaction ?? [];
$page_title = 'Transaction Details - #' . ($transaction['id'] ?? '');
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-exchange-alt me-2"></i>Transaction Details</h1>
            <p class="text-muted mb-0">Transaction #<?php echo $transaction['id'] ?? '-'; ?></p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/banking" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Banking
            </a>
            <?php if (($transaction['reconciliation_status'] ?? '') !== 'reconciled'): ?>
                <button class="btn btn-success" onclick="reconcileTransaction(<?php echo $transaction['id'] ?? 0; ?>)">
                    <i class="fas fa-check-double me-2"></i>Reconcile
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($transaction)): ?>
        <div class="alert alert-warning">Transaction not found.</div>
    <?php else: ?>

    <div class="row">
        <!-- Transaction Info -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Transaction Information</h5>
                    <span class="badge bg-<?php echo ($transaction['type'] ?? '') === 'credit' ? 'success' : 'danger'; ?> fs-6">
                        <?php echo strtoupper($transaction['type'] ?? '-'); ?>
                    </span>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Transaction ID</small>
                            <strong>#<?php echo $transaction['id'] ?? '-'; ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Amount</small>
                            <h4 class="text-<?php echo ($transaction['type'] ?? '') === 'credit' ? 'success' : 'danger'; ?> mb-0">
                                <?php echo ($transaction['type'] ?? '') === 'credit' ? '+' : '-'; ?>
                                ₹<?php echo number_format($transaction['amount'] ?? 0, 2); ?>
                            </h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Date</small>
                            <strong><?php echo isset($transaction['date']) ? date('d M Y H:i', strtotime($transaction['date'])) : '-'; ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Reference ID</small>
                            <strong><?php echo htmlspecialchars($transaction['ref_id'] ?? 'N/A'); ?></strong>
                        </div>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Description</small>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($transaction['description'] ?? 'No description')); ?></p>
                    </div>
                </div>
            </div>

            <!-- Banking Details -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-university me-2"></i>Banking Details</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Bank Name</small>
                            <strong><?php echo htmlspecialchars($transaction['bank_name'] ?? 'N/A'); ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Bank Branch</small>
                            <strong><?php echo htmlspecialchars($transaction['bank_branch'] ?? 'N/A'); ?></strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Account Number</small>
                            <strong><?php echo htmlspecialchars($transaction['account_number'] ?? 'N/A'); ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">IFSC Code</small>
                            <strong><?php echo htmlspecialchars($transaction['ifsc_code'] ?? 'N/A'); ?></strong>
                        </div>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Cheque Number</small>
                        <strong><?php echo htmlspecialchars($transaction['cheque_number'] ?? 'N/A'); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reconciliation Section -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-balance-scale me-2"></i>Reconciliation</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3 text-center">
                        <span class="badge bg-<?php echo match($transaction['reconciliation_status'] ?? 'pending') {
                            'reconciled' => 'success',
                            'cleared' => 'info',
                            'bounced' => 'danger',
                            default => 'warning'
                        }; ?> fs-6 p-2">
                            <i class="fas fa-<?php echo match($transaction['reconciliation_status'] ?? 'pending') {
                                'reconciled' => 'check-circle',
                                'cleared' => 'check',
                                'bounced' => 'times-circle',
                                default => 'clock'
                            }; ?> me-1"></i>
                            <?php echo ucfirst($transaction['reconciliation_status'] ?? 'Pending'); ?>
                        </span>
                    </div>

                    <?php if (($transaction['reconciliation_status'] ?? '') === 'reconciled'): ?>
                        <hr>
                        <div class="mb-2">
                            <small class="text-muted d-block">Reconciled At</small>
                            <strong><?php echo isset($transaction['reconciled_at']) ? date('d M Y H:i', strtotime($transaction['reconciled_at'])) : '-'; ?></strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Reconciled By</small>
                            <strong><?php echo htmlspecialchars($transaction['reconciled_by'] ?? 'Unknown'); ?></strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Financial Year</small>
                            <strong><?php echo htmlspecialchars($transaction['financial_year'] ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <small class="text-muted d-block">Period</small>
                            <strong><?php echo htmlspecialchars($transaction['financial_period'] ?? 'N/A'); ?></strong>
                        </div>
                    <?php else: ?>
                        <hr>
                        <p class="text-muted text-center mb-3">This transaction has not been reconciled yet.</p>
                        <form method="POST" action="<?php echo BASE_URL; ?>/admin/banking/reconcile/<?php echo e($transaction['id']); ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select" name="reconciliation_status" required>
                                    <option value="cleared">Cleared</option>
                                    <option value="bounced">Bounced</option>
                                    <option value="reconciled">Reconciled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Financial Year</label>
                                <select class="form-select" name="financial_year">
                                    <option value="">-- Select --</option>
                                    <option value="2024-2025">2024-2025</option>
                                    <option value="2025-2026" selected>2025-2026</option>
                                    <option value="2026-2027">2026-2027</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Period</label>
                                <select class="form-select" name="financial_period">
                                    <option value="">-- Select --</option>
                                    <option value="Q1">Q1 (Apr-Jun)</option>
                                    <option value="Q2">Q2 (Jul-Sep)</option>
                                    <option value="Q3">Q3 (Oct-Dec)</option>
                                    <option value="Q4">Q4 (Jan-Mar)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check-double me-2"></i>Confirm Reconciliation
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function reconcileTransaction(id) {
    apsConfirm('Mark this transaction as reconciled?').then(function(result) {
        if (result) {
            window.location.href = '<?php echo BASE_URL; ?>/admin/banking/reconcile/' + id;
        }
    });
}
</script>
