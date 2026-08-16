<?php
/** @var int $default_year */
/** @var int $default_month */
$default_year = $default_year ?? (int)date('Y');
$default_month = $default_month ?? (int)date('n');
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-plus-circle me-2"></i>Create Payout Batch</h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payouts/batches" class="btn btn-link btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="aps-cp-card-body">
        <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/payouts/batches/create">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Year <span class="text-danger">*</span></label>
                    <input type="number" name="period_year" min="2020" max="2100" class="form-control" value="<?= (int)$default_year ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Month <span class="text-danger">*</span></label>
                    <select name="period_month" class="form-select" required>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= (int)$default_month === $m ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-bolt me-1"></i>Generate Batch
                    </button>
                </div>
            </div>
            <div class="alert alert-info mt-3 small">
                <i class="fas fa-info-circle me-1"></i>
                The batch aggregates all <code>mlm_commission_ledger</code> rows with
                <code>status IN ('paid', 'approved')</code> for the selected month, groups them
                by beneficiary, applies 5% TDS under Section 194H (brokerage/commission), and
                creates one <code>mlm_payouts</code> row per beneficiary in <code>pending</code> status.
            </div>
        </form>
    </div>
</div>
