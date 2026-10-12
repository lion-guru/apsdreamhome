<?php
/** Maintenance dues + batch billing. */
/** @var array $rows */
/** @var array $colonies */
$rows = $rows ?? [];
$colonies = $colonies ?? [];
$colony_id = (int)($colony_id ?? 0);
$status_filter = $status_filter ?? '';
$totals = $totals ?? ['outstanding' => 0, 'collected' => 0];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$stBadge = ['pending' => 'warning text-dark', 'paid' => 'success', 'waived' => 'secondary'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-building-shield me-2"></i>Maintenance Dues</h4>
        <p class="text-muted mb-0">Outstanding <strong class="text-danger">₹<?= number_format((float)($totals['outstanding'] ?? 0)) ?></strong> · pending dues block registry NOC</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="colony_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="0">All colonies</option>
            <?php foreach ($colonies as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $colony_id === (int)$c['id'] ? 'selected' : '' ?>><?= $e($c['name']) ?></option><?php endforeach; ?>
        </select>
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php foreach (['pending', 'paid', 'waived'] as $s): ?><option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
        </select>
    </form>
</div>
<div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
    <h6 class="fw-bold mb-3">Generate Monthly Batch</h6>
    <form method="POST" action="<?= $base ?>/admin/maintenance-dues/generate-batch">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="row g-2">
            <div class="col-md-3"><label class="form-label small">Colony *</label>
                <select name="colony_id" class="form-select" required>
                    <?php foreach ($colonies as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $colony_id === (int)$c['id'] ? 'selected' : '' ?>><?= $e($c['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label small">Month *</label><input type="number" min="1" max="12" name="bill_month" class="form-control" value="<?= date('n') ?>" required></div>
            <div class="col-md-2"><label class="form-label small">Year *</label><input type="number" min="2000" max="2100" name="bill_year" class="form-control" value="<?= date('Y') ?>" required></div>
            <div class="col-md-2"><label class="form-label small">₹/sqft *</label><input type="number" step="0.01" min="0" name="rate_per_sqft" class="form-control" value="1" required></div>
            <div class="col-md-2 pt-4"><button type="submit" class="btn btn-primary w-100">Generate Bills</button></div>
        </div>
    </form>
</div></div>
<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Bill</th><th>Buyer</th><th class="text-end">Due ₹</th><th class="text-end">Paid ₹</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No dues — generate a batch above.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?= $e($r['booking_number'] ?? '') ?> · <?= $e($r['colony_name'] ?? '') ?><br><small class="text-muted"><?= str_pad((string)($r['bill_month'] ?? ''), 2, '0', STR_PAD_LEFT) ?>/<?= $e($r['bill_year'] ?? '') ?></small></td>
                <td><?= $e($r['customer_name'] ?? '') ?></td>
                <td class="text-end">₹<?= number_format((float)($r['total_due'] ?? 0)) ?></td>
                <td class="text-end text-success">₹<?= number_format((float)($r['paid_amount'] ?? 0)) ?></td>
                <td><span class="badge bg-<?= $stBadge[$r['status']] ?? 'secondary' ?>"><?= $e($r['status'] ?? '') ?></span><?= !empty($r['receipt_id']) ? '<br><small class="text-muted">' . $e($r['receipt_id']) . '</small>' : '' ?></td>
                <td class="text-end">
                    <?php if (($r['status'] ?? '') === 'pending'): ?>
                    <form method="POST" action="<?= $base ?>/admin/maintenance-dues/collect/<?= (int)$r['id'] ?>" class="d-inline-flex gap-1">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <select name="payment_mode" class="form-select form-select-sm"><option value="upi">UPI</option><option value="cash">Cash</option><option value="bank_transfer">Bank</option><option value="cheque">Cheque</option></select>
                        <button type="submit" class="btn btn-sm btn-success" data-aps-confirm="Mark this bill paid?">Collect</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div>
</div></div>
