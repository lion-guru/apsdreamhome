<?php
/** @var array $commissions */
/** @var array $filters */
$commissions = $commissions ?? [];
$filters = $filters ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

$statusBadge = function ($s) {
    $map = [
        'pending'   => 'bg-warning text-dark',
        'approved'  => 'bg-info',
        'paid'      => 'bg-success',
        'cancelled' => 'bg-secondary',
    ];
    return $map[$s] ?? 'bg-secondary';
};
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5 class="m-0"><i class="fas fa-percentage me-2"></i>MLM Commissions Ledger</h5>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/dashboard" class="btn btn-link btn-sm">Back to Dashboard</a>
    </div>
    <div class="aps-cp-card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label small">Beneficiary User ID</label>
                <input type="number" name="associate_id" class="form-control form-control-sm" value="<?= htmlspecialchars((string)($filters['associate_id'] ?? '')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <?php foreach (['pending','approved','paid','cancelled'] as $st): ?>
                        <option value="<?= $st ?>" <?= (string)($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars((string)($filters['from'] ?? '')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars((string)($filters['to'] ?? '')) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary btn-sm me-2" type="submit"><i class="fas fa-search me-1"></i>Filter</button>
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/commissions">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover m-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Beneficiary</th>
                        <th>Source</th>
                        <th>Type</th>
                        <th>Lvl</th>
                        <th>Sale</th>
                        <th>%</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($commissions)): ?>
                        <tr><td colspan="11" class="text-center py-3 text-muted">No commissions match the filter</td></tr>
                    <?php else: foreach ($commissions as $c): ?>
                        <tr>
                            <td><?= (int)($c['id'] ?? 0) ?></td>
                            <td><?= htmlspecialchars((string)($c['created_at'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string)($c['beneficiary_name'] ?? '#'.($c['beneficiary_user_id'] ?? ''))) ?></td>
                            <td><?= htmlspecialchars((string)($c['source_name'] ?? '#'.($c['source_user_id'] ?? ''))) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars((string)($c['commission_type'] ?? '')) ?></span></td>
                            <td><?= (int)($c['level'] ?? 0) ?></td>
                            <td>&#8377;<?= number_format((float)($c['sale_amount'] ?? 0)) ?></td>
                            <td><?= number_format((float)($c['commission_percentage'] ?? 0), 2) ?>%</td>
                            <td class="text-end"><strong>&#8377;<?= number_format((float)($c['amount'] ?? 0), 2) ?></strong></td>
                            <td><span class="badge <?= $statusBadge($c['status'] ?? '') ?>"><?= htmlspecialchars((string)($c['status'] ?? '')) ?></span></td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($base ?? '') ?>/admin/mlm/commissions/<?= (int)($c['id'] ?? 0) ?>">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
