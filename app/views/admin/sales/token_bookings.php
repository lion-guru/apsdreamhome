<?php
/** Token bookings list. */
/** @var array $rows */
$rows = $rows ?? [];
$status_filter = $status_filter ?? '';
$released = (int)($released ?? 0);
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$badge = ['active' => 'success', 'converted' => 'primary', 'expired' => 'secondary', 'refunded' => 'warning text-dark'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-handshake me-2"></i>Token Bookings (Bayana)</h4>
        <p class="text-muted mb-0"><?= count($rows) ?> records<?= $released > 0 ? " · $released expired lock(s) auto-released" : '' ?></p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php foreach (['active', 'converted', 'expired', 'refunded'] as $s): ?>
                <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
<?php if (empty($rows)): ?>
    <div class="text-center py-5"><i class="fas fa-handshake fa-3x text-muted mb-3"></i>
        <h6 class="text-muted">No token bookings<?= $status_filter !== '' ? ' with status ' . $e($status_filter) : '' ?></h6>
        <p class="text-muted small">Take token from the <a href="<?= $base ?>/admin/plots/map">master plot map</a>.</p>
    </div>
<?php else: ?>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Receipt</th><th>Buyer</th><th>Plot</th><th class="text-end">Token ₹</th><th>Mode</th><th>Lock Till</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td class="fw-medium"><?= $e($r['receipt_no'] ?? '') ?><br><small class="text-muted"><?= $e($r['created_at'] ?? '') ?></small></td>
                <td><?= $e($r['customer_name'] ?? '') ?><br><small class="text-muted"><?= $e($r['customer_phone'] ?? '') ?></small></td>
                <td><?= $e($r['plot_number'] ?? '') ?><br><small class="text-muted"><?= $e($r['colony_name'] ?? '') ?></small></td>
                <td class="text-end">₹<?= number_format((float)($r['token_amount'] ?? 0)) ?></td>
                <td><span class="badge bg-light text-dark border"><?= $e($r['payment_mode'] ?? '') ?></span></td>
                <td class="small"><?= $e($r['lock_expires_at'] ?? '') ?></td>
                <td><span class="badge bg-<?= $badge[$r['status']] ?? 'secondary' ?>"><?= $e($r['status'] ?? '') ?></span></td>
                <td><a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= $base ?>/admin/sales/token-booking/receipt/<?= $e($r['receipt_no'] ?? '') ?>"><i class="fas fa-print"></i></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
<?php endif; ?>
</div></div>
