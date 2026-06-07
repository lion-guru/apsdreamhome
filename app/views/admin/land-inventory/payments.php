<?php
$acq = $acquisition ?? [];
$payments = $payments ?? [];
$id = (int)($acq['id'] ?? 0);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-receipt text-primary me-2"></i>Payments — Acquisition #<?= $id ?></h4>
        <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= $id ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Type</th><th>Amount</th><th>Mode</th><th>Ref</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['payment_date'] ?? '—') ?></td>
                            <td><?= htmlspecialchars(ucwords(str_replace('_',' ', $p['payment_type'] ?? ''))) ?></td>
                            <td>₹<?= number_format((float)($p['amount'] ?? 0)) ?></td>
                            <td><?= htmlspecialchars(ucwords($p['payment_mode'] ?? '—')) ?></td>
                            <td><?= htmlspecialchars($p['reference_number'] ?? '—') ?></td>
                            <td>
                                <span class="badge bg-<?= ($p['status'] ?? '') === 'cleared' ? 'success' : (($p['status'] ?? '') === 'bounced' ? 'danger' : 'warning') ?>">
                                    <?= htmlspecialchars(ucfirst($p['status'] ?? 'pending')) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= $id ?>/payments/edit/<?= (int)($p['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No payments recorded.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
