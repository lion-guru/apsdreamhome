<?php
$acq = $acquisition ?? [];
$payments = $payments ?? [];
$summary = $summary ?? ['total_amount'=>0, 'cleared_amount'=>0, 'pending_amount'=>0];
$id = (int)($acq['id'] ?? 0);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-file-contract text-primary me-2"></i>Acquisition #<?= $id ?></h4>
            <small class="text-muted"><?= htmlspecialchars($acq['land_owner_name'] ?? '') ?></small>
        </div>
        <div>
            <?php if (($acq['status'] ?? '') !== 'registered' && ($acq['status'] ?? '') !== 'dropped'): ?>
                <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= $id ?>/register" class="btn btn-success btn-sm">
                    <i class="fas fa-registered me-1"></i>Register Property
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-info-circle me-2"></i>Deal Terms</div>
                <div class="aps-cp-card-body">
                    <table class="table table-sm mb-0">
                        <tr><th class="text-muted">Status</th><td>
                            <span class="badge bg-<?= ($acq['status'] ?? '') === 'registered' ? 'success' : 'info' ?>">
                                <?= htmlspecialchars(ucwords(str_replace('_',' ', $acq['status'] ?? ''))) ?>
                            </span>
                        </td></tr>
                        <tr><th class="text-muted">Lead #</th><td><?= (int)($acq['land_lead_id'] ?? 0) ?></td></tr>
                        <tr><th class="text-muted">Vendor</th><td><?= htmlspecialchars($acq['land_owner_name'] ?? '—') ?></td></tr>
                        <tr><th class="text-muted">Survey #</th><td><?= htmlspecialchars($acq['survey_number'] ?? '—') ?></td></tr>
                        <tr><th class="text-muted">Plot Area</th><td><?= number_format((float)($acq['plot_area_sqft'] ?? 0), 2) ?> sqft</td></tr>
                        <tr><th class="text-muted">Sale Deed #</th><td><?= htmlspecialchars($acq['sale_deed_number'] ?? '—') ?></td></tr>
                        <tr><th class="text-muted">Reg Date</th><td><?= htmlspecialchars($acq['registration_date'] ?? '—') ?></td></tr>
                        <tr><th class="text-muted">Reg Office</th><td><?= htmlspecialchars($acq['registration_office'] ?? '—') ?></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-money-bill-wave text-success me-2"></i>Financials</div>
                <div class="aps-cp-card-body">
                    <table class="table table-sm mb-2">
                        <tr><th class="text-muted">Negotiated</th><td>₹<?= number_format((float)($acq['negotiated_price'] ?? 0)) ?></td></tr>
                        <tr><th class="text-muted">Final</th><td class="fw-bold">₹<?= number_format((float)($acq['final_price'] ?? 0)) ?></td></tr>
                        <tr><th class="text-muted">Stamp Duty</th><td>₹<?= number_format((float)($acq['stamp_duty_amount'] ?? 0)) ?></td></tr>
                        <tr><th class="text-muted">Reg Fee</th><td>₹<?= number_format((float)($acq['registration_fee'] ?? 0)) ?></td></tr>
                        <tr><th class="text-muted">Brokerage</th><td>₹<?= number_format((float)($acq['broker_commission'] ?? 0)) ?></td></tr>
                    </table>
                    <hr>
                    <h6 class="text-muted">Ledger Summary</h6>
                    <table class="table table-sm mb-0">
                        <tr><th>Total</th><td class="text-end">₹<?= number_format((float)$summary['total_amount']) ?></td></tr>
                        <tr class="text-success"><th>Cleared</th><td class="text-end">₹<?= number_format((float)$summary['cleared_amount']) ?></td></tr>
                        <tr class="text-danger"><th>Pending</th><td class="text-end">₹<?= number_format((float)$summary['pending_amount']) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-calendar-check text-info me-2"></i>Compliance</div>
                <div class="aps-cp-card-body">
                    <table class="table table-sm mb-0">
                        <tr><th class="text-muted">Mutation Filed</th><td><?= !empty($acq['mutation_filed_date']) ? '✓ '.htmlspecialchars($acq['mutation_filed_date'] ?? '') : '<span class="text-muted">Pending</span>' ?></td></tr>
                        <tr><th class="text-muted">Mutation #</th><td><?= htmlspecialchars($acq['mutation_number'] ?? '—') ?></td></tr>
                        <tr><th class="text-muted">Tax Up-to-date</th><td><?= !empty($acq['property_tax_upto_date']) ? '✓' : '✗' ?></td></tr>
                        <tr><th class="text-muted">RERA Reg</th><td><?= !empty($acq['rera_registration']) ? '✓ '.htmlspecialchars($acq['rera_registration'] ?? '') : 'N/A' ?></td></tr>
                        <tr><th class="text-muted">DD Date</th><td><?= htmlspecialchars($acq['dd_filed_date'] ?? '—') ?></td></tr>
                        <tr><th class="text-muted">DD Ref</th><td><?= htmlspecialchars($acq['dd_reference'] ?? '—') ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card mt-3">
        <div class="aps-cp-card-header d-flex justify-content-between">
            <span><i class="fas fa-receipt me-2"></i>Payment Ledger (<?= count($payments) ?>)</span>
            <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= $id ?>/payments/new" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Add Payment
            </a>
        </div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Type</th><th>Amount</th><th>Mode</th><th>Reference</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><small><?= htmlspecialchars($p['payment_date'] ?? '—') ?></small></td>
                            <td><?= htmlspecialchars(ucwords(str_replace('_',' ', $p['payment_type'] ?? ''))) ?></td>
                            <td>₹<?= number_format((float)($p['amount'] ?? 0)) ?></td>
                            <td><small><?= htmlspecialchars(ucwords($p['payment_mode'] ?? '—')) ?></small></td>
                            <td><small><?= htmlspecialchars($p['reference_number'] ?? '—') ?></small></td>
                            <td>
                                <span class="badge bg-<?= ($p['status'] ?? '') === 'cleared' ? 'success' : (($p['status'] ?? '') === 'bounced' ? 'danger' : 'warning') ?>">
                                    <?= htmlspecialchars(ucfirst($p['status'] ?? 'pending')) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
