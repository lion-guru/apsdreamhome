<?php
$base = rtrim(BASE_URL, '/');
$csrf = $this->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i><?= htmlspecialchars($page_title) ?></h1>
        <div>
            <a href="<?= $base ?>/admin/property-tax" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="fas fa-plus me-1"></i> Record Payment</button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <div class="row g-3 align-items-center">
                <div class="col-md-3"><strong>Authority:</strong> <?= htmlspecialchars($master['tax_authority']) ?></div>
                <div class="col-md-2"><strong>Year:</strong> <?= htmlspecialchars($master['assessment_year']) ?></div>
                <div class="col-md-2"><strong>Type:</strong> <span class="badge bg-secondary"><?= ucwords(str_replace('_', ' ', $master['tax_type'])) ?></span></div>
                <div class="col-md-2"><strong>Net Tax:</strong> <span class="text-primary fw-bold">₹<?= number_format($master['net_tax_amount'], 2) ?></span></div>
                <div class="col-md-2"><strong>Due:</strong> <?= $master['due_date'] ? date('d M Y', strtotime($master['due_date'])) : '-' ?></div>
                <div class="col-md-1"><span class="badge bg-<?= $master['status']==='demand_generated'?'warning':($master['status']==='closed'?'success':'secondary') ?>"><?= ucfirst(str_replace('_', ' ', $master['status'])) ?></span></div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Ref/Chq #</th>
                            <th>Base Tax</th>
                            <th>Penalty</th>
                            <th>Rebate</th>
                            <th>Total</th>
                            <th>Receipt</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr><td colspan="10" class="text-center py-4 text-muted">No payments recorded</td></tr>
                        <?php else: ?>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
                                    <td><span class="badge bg-info"><?= ucfirst($p['payment_mode']) ?></span></td>
                                    <td><?= htmlspecialchars($p['cheque_dd_number'] ?? $p['transaction_ref'] ?? $p['upi_ref'] ?? '-') ?></td>
                                    <td>₹<?= number_format($p['base_tax_paid'], 2) ?></td>
                                    <td class="text-danger">₹<?= number_format($p['penalty_paid'], 2) ?></td>
                                    <td class="text-success">₹<?= number_format($p['rebate_availed'], 2) ?></td>
                                    <td class="fw-bold">₹<?= number_format($p['total_paid'], 2) ?></td>
                                    <td><code><?= htmlspecialchars($p['receipt_number']) ?></code></td>
                                    <td>
                                        <span class="badge bg-<?= $p['status']==='verified'?'success':($p['status']==='pending'?'warning text-dark':'danger') ?>">
                                            <?= ucfirst($p['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($p['status'] === 'pending'): ?>
                                                <a href="<?= $base ?>/admin/property-tax/verify-payment/<?= $p['id'] ?>" class="btn btn-outline-success" onclick="return confirm('Verify this payment and generate receipt?')"><i class="fas fa-check"></i></a>
                                            <?php endif; ?>
                                            <a href="<?= $base ?>/admin/property-tax/receipt/<?= $p['id'] ?>" class="btn btn-outline-primary" target="_blank" title="Print Receipt"><i class="fas fa-print"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="paymentForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Record Payment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Mode</label>
                                <select name="payment_mode" class="form-select" id="paymentMode" required>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="dd">Demand Draft</option>
                                    <option value="online">Online Transfer</option>
                                    <option value="upi">UPI</option>
                                    <option value="neft">NEFT</option>
                                    <option value="rtgs">RTGS</option>
                                    <option value="card">Card</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="chqFields" style="display:none">
                                <label class="form-label">Cheque/DD Number</label>
                                <input type="text" name="cheque_dd_number" class="form-control">
                            </div>
                            <div class="col-md-4" id="chqDateField" style="display:none">
                                <label class="form-label">Cheque/DD Date</label>
                                <input type="date" name="cheque_dd_date" class="form-control">
                            </div>
                            <div class="col-md-4" id="bankFields" style="display:none">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control">
                            </div>
                            <div class="col-md-4" id="branchFields" style="display:none">
                                <label class="form-label">Branch</label>
                                <input type="text" name="bank_branch" class="form-control">
                            </div>
                            <div class="col-md-4" id="txnFields" style="display:none">
                                <label class="form-label">Transaction Ref</label>
                                <input type="text" name="transaction_ref" class="form-control">
                            </div>
                            <div class="col-md-4" id="upiFields" style="display:none">
                                <label class="form-label">UPI Reference</label>
                                <input type="text" name="upi_ref" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Plot</label>
                                <select name="plot_id" class="form-select">
                                    <option value="">Select Plot</option>
                                    <?php
                                    $plots = $this->db->fetchAll("SELECT id, plot_number FROM plots WHERE tenant_id = ? ORDER BY plot_number", [$this->tenantId()]);
                                    foreach ($plots as $pl): ?>
                                        <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['plot_number']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Customer</label>
                                <select name="customer_id" class="form-select">
                                    <option value="">Select Customer</option>
                                    <?php
                                    $customers = $this->db->fetchAll("SELECT id, name, phone FROM users WHERE tenant_id = ? AND role = 'customer' ORDER BY name", [$this->tenantId()]);
                                    foreach ($customers as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Base Tax Paid</label>
                                <input type="number" step="0.01" name="base_tax_paid" class="form-control" value="<?= number_format($master['net_tax_amount'], 2, '.', '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Interest Paid</label>
                                <input type="number" step="0.01" name="interest_paid" class="form-control" value="0">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Rebate Conditions (JSON)</label>
                                <textarea name="rebate_conditions" class="form-control" rows="2" placeholder='{"early_payment_days": 30, "senior_citizen": false}'>{}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('paymentMode').addEventListener('change', function() {
    const mode = this.value;
    const show = (ids, show) => ids.forEach(id => document.getElementById(id).style.display = show ? 'block' : 'none');
    show(['chqFields','chqDateField','bankFields','branchFields','txnFields','upiFields'], false);
    if (['cheque','dd'].includes(mode)) show(['chqFields','chqDateField','bankFields','branchFields'], true);
    if (['online','neft','rtgs'].includes(mode)) show(['txnFields','bankFields','branchFields'], true);
    if (mode === 'upi') show(['upiFields'], true);
});

document.getElementById('paymentForm').addEventListener('submit', function(e) {
    this.action = '<?= $base ?>/admin/property-tax/record-payment/<?= $master['id'] ?>';
});
</script>
</body>
</html>