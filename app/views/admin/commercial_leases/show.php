<?php
/**
 * Commercial Lease Detail & Rent Ledger View
 */
$lease = $lease ?? [];
$invoices = $invoices ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb & Nav -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="/admin/commercial-leases" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i>Back to Commercial Leases
            </a>
            <h4 class="mb-0 fw-bold mt-1">
                Unit <?= htmlspecialchars($lease['unit_number'] ?? 'N/A') ?> — <?= htmlspecialchars($lease['tenant_name'] ?? 'N/A') ?>
                <span class="badge bg-primary ms-2 font-monospace fs-6"><?= htmlspecialchars($lease['lease_token'] ?? '') ?></span>
            </h4>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateInvoiceModal">
                <i class="fas fa-file-invoice-dollar me-1"></i>Generate Monthly Rent Invoice
            </button>
        </div>
    </div>

    <!-- Lease Information Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Township Complex</div>
                    <div class="fw-bold fs-6"><?= htmlspecialchars($lease['colony_name'] ?? 'APS Township') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($lease['colony_address'] ?? 'Gorakhpur') ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Unit Classification</div>
                    <div class="fw-bold fs-6 text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($lease['unit_type'] ?? 'retail_shop')) ?></div>
                    <div class="small text-muted"><?= number_format((float)($lease['carpet_area_sqft'] ?? 0), 0) ?> sq.ft carpet area</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Monthly Base Rent</div>
                    <div class="fw-bold fs-5 text-success">₹<?= number_format((float)($lease['monthly_rent'] ?? 0), 2) ?></div>
                    <div class="small text-muted">+<?= (float)($lease['annual_escalation_pct'] ?? 5) ?>% yearly escalation</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Security Deposit Held</div>
                    <div class="fw-bold fs-5 text-info">₹<?= number_format((float)($lease['security_deposit'] ?? 0), 2) ?></div>
                    <div class="small text-muted"><?= (int)($lease['lock_in_months'] ?? 12) ?> months lock-in period</div>
                </div>
            </div>

            <hr class="text-muted my-3">

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Tenant Contact</div>
                    <div class="fw-semibold"><?= htmlspecialchars($lease['tenant_phone'] ?? '') ?> &bull; <?= htmlspecialchars($lease['tenant_email'] ?? 'No email') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Business / Trade Name &amp; GSTIN</div>
                    <div class="fw-semibold"><?= htmlspecialchars($lease['tenant_business_name'] ?? 'Independent Tenant') ?> (<?= htmlspecialchars($lease['tenant_gstin'] ?? 'Unregistered') ?>)</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Lease Validity Period</div>
                    <div class="fw-semibold"><?= date('d M Y', strtotime($lease['lease_start_date'] ?? 'now')) ?> to <?= date('d M Y', strtotime($lease['lease_end_date'] ?? 'now')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Rent Invoices Ledger -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold mb-0"><i class="fas fa-receipt text-primary me-2"></i>Rent Invoices &amp; Utility Demand Ledger</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Invoice No</th>
                            <th>Month</th>
                            <th>Base Rent</th>
                            <th>Electricity Bill</th>
                            <th>Maintenance</th>
                            <th>Total Due</th>
                            <th>Paid Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-invoice fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No rent demand invoices raised yet for this lease.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold font-monospace text-dark"><?= htmlspecialchars($inv['invoice_no']) ?></div>
                                        <div class="small text-muted"><?= date('d M Y', strtotime($inv['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-bold"><?= htmlspecialchars($inv['billing_month']) ?></span>
                                    </td>
                                    <td>
                                        ₹<?= number_format((float)$inv['base_rent'], 2) ?>
                                    </td>
                                    <td>
                                        <div>₹<?= number_format((float)$inv['electricity_amount'], 2) ?></div>
                                        <div class="small text-muted"><?= (float)$inv['electricity_units'] ?> units @ ₹<?= (float)$inv['electricity_rate'] ?></div>
                                    </td>
                                    <td>
                                        ₹<?= number_format((float)$inv['maintenance_fee'], 2) ?>
                                    </td>
                                    <td class="fw-bold text-dark fs-6">
                                        ₹<?= number_format((float)$inv['total_due'], 2) ?>
                                    </td>
                                    <td class="fw-semibold text-success">
                                        ₹<?= number_format((float)$inv['paid_amount'], 2) ?>
                                    </td>
                                    <td>
                                        <?php if ($inv['status'] === 'paid'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Paid</span>
                                        <?php elseif ($inv['status'] === 'partially_paid'): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-adjust me-1"></i>Partially Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Unpaid</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($inv['status'] !== 'paid'): ?>
                                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#payModal<?= $inv['id'] ?>">
                                                    <i class="fas fa-money-bill-wave me-1"></i>Collect
                                                </button>
                                            <?php endif; ?>
                                            <a href="/admin/commercial-leases/invoices/<?= $inv['id'] ?>/print" target="_blank" class="btn btn-outline-primary">
                                                <i class="fas fa-print me-1"></i>Print
                                            </a>
                                        </div>

                                        <!-- Collect Payment Modal -->
                                        <div class="modal fade" id="payModal<?= $inv['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="/admin/commercial-leases/invoices/<?= $inv['id'] ?>/pay">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                                    <input type="hidden" name="lease_id" value="<?= (int)$lease['id'] ?>">
                                                    <div class="modal-content text-start">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Collect Rent — <?= htmlspecialchars($inv['invoice_no']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="pay_amount_<?= $inv['id'] ?>" class="form-label">Payment Amount (₹)</label>
                                                                <input type="number" step="0.01" class="form-control" id="pay_amount_<?= $inv['id'] ?>" name="paid_amount" value="<?= (float)($inv['total_due'] - $inv['paid_amount']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="pay_mode_<?= $inv['id'] ?>" class="form-label">Payment Mode</label>
                                                                <select name="payment_mode" id="pay_mode_<?= $inv['id'] ?>" class="form-select">
                                                                    <option value="upi">UPI / QR (apsdreamhome@icici)</option>
                                                                    <option value="neft">NEFT / RTGS</option>
                                                                    <option value="cheque">Cheque</option>
                                                                    <option value="cash">Cash Counter</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="pay_ref_<?= $inv['id'] ?>" class="form-label">Transaction UTR / Cheque Ref</label>
                                                                <input type="text" class="form-control" id="pay_ref_<?= $inv['id'] ?>" name="payment_ref" placeholder="e.g. UTR-20261012-987654">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Record Rent Receipt</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
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
</div>

<!-- Generate Monthly Rent Invoice Modal -->
<div class="modal fade" id="generateInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="/admin/commercial-leases/<?= (int)$lease['id'] ?>/invoice">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice text-primary me-2"></i>Generate Monthly Rent Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inv_month" class="form-label fw-semibold">Billing Month <span class="text-danger">*</span></label>
                        <input type="month" class="form-control" id="inv_month" name="billing_month" value="<?= date('Y-m') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Contracted Monthly Base Rent</label>
                        <div class="form-control-plaintext fw-bold fs-6">₹<?= number_format((float)($lease['monthly_rent'] ?? 0), 2) ?></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="inv_elec_units" class="form-label">Electricity Submeter (Units)</label>
                            <input type="number" step="0.1" class="form-control" id="inv_elec_units" name="electricity_units" value="0.0">
                        </div>
                        <div class="col-md-6">
                            <label for="inv_elec_rate" class="form-label">Rate / Unit (₹)</label>
                            <input type="number" step="0.1" class="form-control" id="inv_elec_rate" name="electricity_rate" value="9.50">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inv_maint_fee" class="form-label">Common Maintenance Fee (₹)</label>
                        <input type="number" step="0.01" class="form-control" id="inv_maint_fee" name="maintenance_fee" value="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Rent Demand Note</button>
                </div>
            </div>
        </form>
    </div>
</div>
