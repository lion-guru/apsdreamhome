<?php
$loan = $loan ?? [];
$installments = $installments ?? [];
$guarantors = $guarantors ?? [];
$documents = $documents ?? [];
$activity_log = $activity_log ?? [];
$early_settlement = $early_settlement ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>Loan: <?= htmlspecialchars($loan['loan_number'] ?? '') ?></h2>
            <small class="text-muted">Customer: <?= htmlspecialchars($loan['customer_name'] ?? '') ?> | <?= htmlspecialchars($loan['customer_phone'] ?? '') ?></small>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/admin/company-loans" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <!-- Status & Loan Info -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header d-flex justify-content-between">
                    <span><i class="fas fa-info-circle me-2"></i>Loan Details</span>
                    <span class="aps-cp-badge badge bg-<?= match($loan['status'] ?? '') {
                        'active' => 'success', 'pending' => 'warning', 'completed' => 'info',
                        'defaulted' => 'danger', 'foreclosed' => 'secondary', 'cancelled' => 'dark'
                    } ?>"><?= ucfirst($loan['status'] ?? '') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-3"><small class="text-muted d-block">Loan Amount</small><strong>₹<?= number_format(($loan['loan_amount'] ?? 0) / 100000, 2) ?>L</strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">Interest Rate</small><strong><?= $loan['interest_rate'] ?? 0 ?>%</strong> <small>(<?= $loan['interest_type'] ?? '' ?>)</small></div>
                        <div class="col-md-3"><small class="text-muted d-block">Tenure</small><strong><?= $loan['tenure_months'] ?? 0 ?> months</strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">EMI Amount</small><strong>₹<?= number_format($loan['emi_amount'] ?? 0) ?></strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">Total Payable</small><strong>₹<?= number_format($loan['total_payable'] ?? 0) ?></strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">Total Interest</small><strong>₹<?= number_format($loan['total_interest'] ?? 0) ?></strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">Amount Paid</small><strong class="text-success">₹<?= number_format($loan['amount_paid'] ?? 0) ?></strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">Balance</small><strong class="text-<?= ($loan['balance_amount'] ?? 0) > 0 ? 'danger' : 'success' ?>">₹<?= number_format($loan['balance_amount'] ?? 0) ?></strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">Start Date</small><strong><?= $loan['start_date'] ?? '-' ?></strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">End Date</small><strong><?= $loan['end_date'] ?? '-' ?></strong></div>
                        <div class="col-md-3"><small class="text-muted d-block">Disbursed</small><strong><?= $loan['disbursed_at'] ? date('d/m/Y', strtotime($loan['disbursed_at'])) : '-' ?></strong></div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Interest-Free</small>
                            <?php if (!empty($loan['interest_free_months'])): ?>
                                <strong class="text-success"><?= $loan['interest_free_months'] ?> months</strong>
                                <?php if (!empty($loan['interest_free_active'])): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Revoked</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($loan['offer_name'])): ?>
                        <div class="mt-3 p-2 bg-light rounded"><small><i class="fas fa-tag text-info me-1"></i>Offer: <?= htmlspecialchars($loan['offer_name']) ?></small></div>
                    <?php endif; ?>
                    <?php if (!empty($loan['purpose'])): ?>
                        <div class="mt-2"><small class="text-muted">Purpose:</small> <?= htmlspecialchars($loan['purpose']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions Panel -->
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-tasks me-2"></i>Actions</div>
                <div class="aps-cp-card-body">
                    <?php if ($loan['status'] === 'pending'): ?>
                        <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/<?= $loan['id'] ?>/disburse" class="mb-2">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-check-circle me-1"></i>Disburse Loan</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($loan['status'] === 'active'): ?>
                        <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/<?= $loan['id'] ?>/default" class="mb-2" onsubmit="return confirm('Mark this loan as defaulted?')">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="btn btn-outline-danger w-100"><i class="fas fa-exclamation-triangle me-1"></i>Mark Default</button>
                        </form>

                        <button type="button" class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#forecloseModal"><i class="fas fa-handshake me-1"></i>Foreclose Loan</button>
                    <?php endif; ?>

                    <hr>
                    <div class="d-grid gap-1">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="fas fa-money-bill me-1"></i>Record Payment</button>
                        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#guarantorModal"><i class="fas fa-user-shield me-1"></i>Add Guarantor</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#docModal"><i class="fas fa-file me-1"></i>Generate Document</button>
                    </div>
                </div>
            </div>

            <!-- Early Settlement -->
            <?php if ($early_settlement['success'] ?? false): ?>
                <div class="aps-cp-card mt-3">
                    <div class="aps-cp-card-header bg-success text-white"><i class="fas fa-gift me-2"></i>Early Settlement</div>
                    <div class="aps-cp-card-body">
                        <div class="mb-2"><small>Principal Remaining:</small> <strong>₹<?= number_format($early_settlement['remaining_principal'] ?? 0) ?></strong></div>
                        <div class="mb-2"><small>Remaining Interest:</small> ₹<?= number_format($early_settlement['remaining_interest'] ?? 0) ?></div>
                        <?php if (($early_settlement['discount_amount'] ?? 0) > 0): ?>
                            <div class="mb-2 text-success"><small>Discount:</small> <strong>-₹<?= number_format($early_settlement['discount_amount']) ?></strong></div>
                        <?php endif; ?>
                        <div class="mb-2"><small>Penalties:</small> ₹<?= number_format($early_settlement['total_penalty'] ?? 0) ?></div>
                        <hr>
                        <div><small>Settlement Amount:</small> <strong class="text-success h5">₹<?= number_format($early_settlement['settlement_amount'] ?? 0) ?></strong></div>
                        <?php if ($early_settlement['incentive_applied'] ?? null): ?>
                            <small class="text-muted">Incentive: <?= htmlspecialchars($early_settlement['incentive_applied']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Schedule -->
    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header d-flex justify-content-between">
            <span><i class="fas fa-calendar-alt me-2"></i>Payment Schedule (<?= count($installments) ?> installments)</span>
            <span class="text-muted small">
                Paid: <span class="text-success"><?= count(array_filter($installments, fn($i) => $i['status'] === 'paid')) ?></span> |
                Pending: <span class="text-warning"><?= count(array_filter($installments, fn($i) => $i['status'] === 'pending')) ?></span> |
                Overdue: <span class="text-danger"><?= count(array_filter($installments, fn($i) => $i['status'] === 'overdue')) ?></span>
            </span>
        </div>
        <div class="aps-cp-card-body p-0">
            <?php if (empty($installments)): ?>
                <div class="text-center text-muted py-4"><p>No installments generated yet.</p></div>
            <?php else: ?>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="sticky-top bg-white"><tr>
                            <th>#</th><th>Due Date</th><th>Principal</th><th>Interest</th><th>Total</th><th>Paid</th><th>Penalty</th><th>Status</th><th>Paid At</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($installments as $inst): ?>
                            <tr class="<?= $inst['status'] === 'overdue' ? 'table-danger' : ($inst['status'] === 'paid' ? 'table-success' : '') ?>">
                                <td><?= $inst['installment_no'] ?></td>
                                <td><?= $inst['due_date'] ?></td>
                                <td>₹<?= number_format($inst['principal_amount']) ?></td>
                                <td>₹<?= number_format($inst['interest_amount']) ?>
                                    <?php if (!empty($inst['waived_interest'])): ?>
                                        <br><small class="text-success">(₹<?= number_format($inst['waived_interest']) ?> waived)</small>
                                    <?php endif; ?>
                                </td>
                                <td>₹<?= number_format($inst['total_amount']) ?></td>
                                <td>₹<?= number_format($inst['paid_amount']) ?></td>
                                <td>₹<?= number_format($inst['accrued_penalty']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $inst['status'] === 'paid' ? 'success' : ($inst['status'] === 'overdue' ? 'danger' : 'secondary') ?>">
                                        <?= ucfirst($inst['status']) ?>
                                    </span>
                                </td>
                                <td class="small"><?= $inst['paid_at'] ? date('d/m/Y', strtotime($inst['paid_at'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Guarantors -->
        <div class="col-md-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header d-flex justify-content-between">
                    <span><i class="fas fa-user-shield me-2"></i>Guarantors (<?= count($guarantors) ?>)</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#guarantorModal"><i class="fas fa-plus"></i></button>
                </div>
                <div class="aps-cp-card-body">
                    <?php if (empty($guarantors)): ?>
                        <p class="text-muted mb-0">No guarantors added yet.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                        <?php foreach ($guarantors as $g): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($g['name'] ?? '') ?></strong>
                                    <span class="badge bg-info"><?= htmlspecialchars($g['relationship'] ?? '') ?></span>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars($g['phone'] ?? '') ?> | <?= htmlspecialchars($g['pan_number'] ?? '') ?></small>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="col-md-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header d-flex justify-content-between">
                    <span><i class="fas fa-file-alt me-2"></i>Documents (<?= count($documents) ?>)</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#docModal"><i class="fas fa-plus"></i></button>
                </div>
                <div class="aps-cp-card-body">
                    <?php if (empty($documents)): ?>
                        <p class="text-muted mb-0">No documents generated yet.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                        <?php foreach ($documents as $d): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($d['title'] ?? '') ?></strong><br>
                                        <small class="text-muted"><?= str_replace('_', ' ', ucfirst($d['document_type'] ?? '')) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $d['status'] === 'final' ? 'success' : ($d['status'] === 'signed' ? 'info' : 'warning') ?> me-1"><?= ucfirst($d['status']) ?></span>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/admin/company-loans/document/<?= $d['id'] ?>" target="_blank" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                            <?php if ($d['status'] === 'draft'): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/document/<?= $d['id'] ?>/finalize" style="display:inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <button type="submit" class="btn btn-outline-success" title="Finalize"><i class="fas fa-check"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($d['status'] === 'final' && !$d['signed_by_customer']): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/document/<?= $d['id'] ?>/sign" style="display:inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <button type="submit" class="btn btn-outline-info" title="Mark Signed"><i class="fas fa-signature"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="aps-cp-card mt-4">
        <div class="aps-cp-card-header"><i class="fas fa-history me-2"></i>Activity Log</div>
        <div class="aps-cp-card-body p-0">
            <?php if (empty($activity_log)): ?>
                <div class="text-center text-muted py-3"><p>No activity recorded yet.</p></div>
            <?php else: ?>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Date</th><th>Action</th><th>Description</th></tr></thead>
                        <tbody>
                        <?php foreach ($activity_log as $log): ?>
                            <tr>
                                <td class="small"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars(str_replace('_', ' ', $log['action'] ?? '')) ?></span></td>
                                <td class="small"><?= htmlspecialchars($log['description'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-money-bill me-2"></i>Record Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/<?= $loan['id'] ?>/payment">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Installment</label>
                    <select name="installment_id" class="form-select" required>
                        <option value="">Select installment</option>
                        <?php foreach ($installments as $inst): ?>
                            <?php if ($inst['status'] !== 'paid'): ?>
                                <option value="<?= $inst['id'] ?>">#<?= $inst['installment_no'] ?> - Due: <?= $inst['due_date'] ?> - ₹<?= number_format($inst['total_amount']) ?> (Outstanding: ₹<?= number_format($inst['total_amount'] - $inst['paid_amount']) ?>)</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" name="amount" class="form-control" min="1" step="1" required>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                            <option value="razorpay">Razorpay</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Transaction ID</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="Optional">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Record Payment</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Guarantor Modal -->
<div class="modal fade" id="guarantorModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-shield me-2"></i>Add Guarantor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/<?= $loan['id'] ?>/guarantor">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">PAN Number</label>
                        <input type="text" name="pan_number" class="form-control" placeholder="ABCDE1234F">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Aadhar Number</label>
                        <input type="text" name="aadhar_number" class="form-control" placeholder="1234 1234 1234">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="occupation" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Annual Income (₹)</label>
                        <input type="number" name="annual_income" class="form-control" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="relationship" class="form-control" placeholder="e.g., Spouse, Parent">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Add Guarantor</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Document Modal -->
<div class="modal fade" id="docModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Generate Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/<?= $loan['id'] ?>/document/agreement">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Document Type</label>
                    <select name="doc_type" id="docTypeSelect" class="form-select">
                        <option value="agreement">Loan Agreement</option>
                        <option value="promissory">Promissory Note</option>
                        <option value="demand">Demand Letter</option>
                        <option value="default_notice">Default Notice</option>
                    </select>
                </div>
                <div class="mb-3" id="installmentSelectWrapper" style="display:none;">
                    <label class="form-label">Installment Number</label>
                    <select name="installment_no" class="form-select">
                        <?php for ($i = 1; $i <= count($installments); $i++): ?>
                            <option value="<?= $i ?>">Installment #<?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-file me-1"></i>Generate</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Foreclose Modal -->
<div class="modal fade" id="forecloseModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-handshake me-2"></i>Foreclose Loan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/<?= $loan['id'] ?>/foreclose">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="modal-body">
                <p>Foreclose this loan with the settlement amount below:</p>
                <?php if ($early_settlement['success'] ?? false): ?>
                    <div class="alert alert-info">
                        <strong>Suggested Settlement:</strong> ₹<?= number_format($early_settlement['settlement_amount'] ?? 0) ?>
                        <?php if (($early_settlement['discount_amount'] ?? 0) > 0): ?>
                            <br><small class="text-success">Includes discount of ₹<?= number_format($early_settlement['discount_amount']) ?></small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Settlement Amount (₹) <span class="text-danger">*</span></label>
                    <input type="number" name="settlement_amount" class="form-control" min="1" step="1" required value="<?= round($early_settlement['settlement_amount'] ?? 0) ?>">
                </div>
                <p class="text-danger small"><i class="fas fa-exclamation-triangle me-1"></i>This action is irreversible. All remaining installments will be marked as paid.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning"><i class="fas fa-handshake me-1"></i>Foreclose</button>
            </div>
        </form>
    </div></div>
</div>

<script>
document.getElementById('docTypeSelect').addEventListener('change', function() {
    document.getElementById('installmentSelectWrapper').style.display = this.value === 'demand' ? 'block' : 'none';
});
</script>
