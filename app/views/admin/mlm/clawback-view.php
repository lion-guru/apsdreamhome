<?php
/** @var array|null $clawback */
$clawback = $clawback ?? null;
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-undo-alt me-2"></i>Clawback Detail</h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/clawbacks" class="btn btn-link btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card-body">
        <?php if (!$clawback): ?>
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>Clawback record not found.</div>
        <?php else: ?>
            <dl class="row">
                <dt class="col-sm-3">Date</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string)($clawback['created_at'] ?? '')) ?></dd>
                <dt class="col-sm-3">Beneficiary</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string)($clawback['beneficiary_name'] ?? '#'.($clawback['beneficiary_user_id'] ?? ''))) ?></dd>
                <dt class="col-sm-3">Source</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string)($clawback['source_name'] ?? '#'.($clawback['source_user_id'] ?? ''))) ?></dd>
                <dt class="col-sm-3">EMI Installment</dt>
                <dd class="col-sm-9">#<?= (int)($clawback['emi_installment_id'] ?? 0) ?></dd>
                <dt class="col-sm-3">Original Amount</dt>
                <dd class="col-sm-9">&#8377;<?= number_format((float)($clawback['original_amount'] ?? 0), 2) ?></dd>
                <dt class="col-sm-3">Clawback Amount</dt>
                <dd class="col-sm-9">&#8377;<?= number_format((float)($clawback['clawback_amount'] ?? 0), 2) ?></dd>
                <dt class="col-sm-3">Recovered Amount</dt>
                <dd class="col-sm-9">&#8377;<?= number_format((float)($clawback['recovered_amount'] ?? 0), 2) ?></dd>
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string)($clawback['status'] ?? '')) ?></dd>
                <dt class="col-sm-3">Reason</dt>
                <dd class="col-sm-9"><?= htmlspecialchars((string)($clawback['reason'] ?? '')) ?></dd>
            </dl>
        <?php endif; ?>
    </div>
</div>
