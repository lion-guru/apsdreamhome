<?php
/** @var array|null $commission */
$commission = $commission ?? null;
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-receipt me-2"></i>Commission Detail
            <?php if ($commission): ?>
                <span class="badge bg-secondary ms-2">#<?= (int)($commission['id'] ?? 0) ?></span>
            <?php endif; ?>
        </h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/commissions" class="btn btn-link btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card-body">
        <?php if (!$commission): ?>
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>Commission not found.</div>
        <?php else: ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th width="40%">Type</th><td><?= htmlspecialchars((string)($commission['commission_type'] ?? '')) ?></td></tr>
                        <tr><th>Level</th><td><?= (int)($commission['level'] ?? 0) ?></td></tr>
                        <tr><th>Status</th><td><span class="badge bg-info"><?= htmlspecialchars((string)($commission['status'] ?? '')) ?></span></td></tr>
                        <tr><th>Created</th><td><?= htmlspecialchars((string)($commission['created_at'] ?? '')) ?></td></tr>
                        <tr><th>Updated</th><td><?= htmlspecialchars((string)($commission['updated_at'] ?? '')) ?></td></tr>
                    </table></div>
                </div>
                <div class="col-md-6">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th width="40%">Beneficiary</th><td><?= htmlspecialchars((string)($commission['beneficiary_name'] ?? '#'.($commission['beneficiary_user_id'] ?? ''))) ?> (UID <?= (int)($commission['beneficiary_user_id'] ?? 0) ?>)</td></tr>
                        <tr><th>Source</th><td><?= htmlspecialchars((string)($commission['source_name'] ?? '#'.($commission['source_user_id'] ?? ''))) ?> (UID <?= (int)($commission['source_user_id'] ?? 0) ?>)</td></tr>
                        <tr><th>Property (Booking)</th><td>#<?= (int)($commission['property_id'] ?? 0) ?></td></tr>
                        <tr><th>Sale Amount</th><td>&#8377;<?= number_format((float)($commission['sale_amount'] ?? 0), 2) ?></td></tr>
                        <tr><th>% Applied</th><td><?= number_format((float)($commission['commission_percentage'] ?? 0), 2) ?>%</td></tr>
                    </table></div>
                </div>
                <div class="col-12">
                    <div class="aps-cp-stat bg-success text-white">
                        <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($commission['amount'] ?? 0), 2) ?></div>
                        <div class="aps-cp-stat-label">Commission Amount</div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label small text-muted">Notes</label>
                    <div class="border rounded p-2 bg-light">
                        <?= nl2br(htmlspecialchars((string)($commission['notes'] ?? ''))) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
