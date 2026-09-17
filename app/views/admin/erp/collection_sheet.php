<?php
/** @var string $month */
/** @var array $rows */
/** @var array $summary */
$month = $month ?? date('Y-m');
$rows = $rows ?? [];
$summary = $summary ?? ['due' => 0, 'collected' => 0, 'outstanding' => 0, 'count' => 0, 'rate' => 0];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Monthly Collection Sheet</h4>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" action="<?= BASE_URL ?>/admin/erp/collection-sheet" class="d-flex gap-2">
                <input type="month" name="month" class="form-control form-control-sm" value="<?= htmlspecialchars($month) ?>">
                <button type="submit" class="btn btn-sm btn-primary">View</button>
            </form>
            <a href="<?= BASE_URL ?>/admin/erp/collection-sheet.csv?month=<?= htmlspecialchars($month) ?>" class="btn btn-sm btn-success"><i class="fas fa-download me-1"></i>Export CSV</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col">
            <div class="card h-100"><div class="card-body text-center">
                <h3>Rs.<?= number_format((float)$summary['due']) ?></h3>
                <small class="text-muted">Due in <?= htmlspecialchars(date('M Y', strtotime($month . '-01'))) ?> (<?= (int)$summary['count'] ?> EMIs)</small>
            </div></div>
        </div>
        <div class="col">
            <div class="card h-100"><div class="card-body text-center">
                <h3 class="text-success">Rs.<?= number_format((float)$summary['collected']) ?></h3>
                <small class="text-muted">Collected (<?= (float)$summary['rate'] ?>%)</small>
            </div></div>
        </div>
        <div class="col">
            <div class="card h-100"><div class="card-body text-center">
                <h3 class="text-danger">Rs.<?= number_format((float)$summary['outstanding']) ?></h3>
                <small class="text-muted">Still in market</small>
            </div></div>
        </div>
    </div>
    <div class="progress mb-4" style="height: 10px;">
        <div class="progress-bar bg-success" role="progressbar" style="width: <?= (float)$summary['rate'] ?>%"><?= (float)$summary['rate'] ?>%</div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($rows)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3"></i><p>No EMIs due in this month.</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Booking</th><th>Plot</th><th>Customer</th><th>Due Date</th><th class="text-end">Due</th><th class="text-end">Paid</th><th class="text-end">Outstanding</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r):
                                $out = max(0, (float)($r['amount'] ?? 0) - (float)($r['paid_amount'] ?? 0)); ?>
                            <tr>
                                <td><?= (int)($r['installment_no'] ?? 0) ?></td>
                                <td><?= htmlspecialchars($r['booking_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['plot_number'] ?? '-') ?> <small class="text-muted"><?= htmlspecialchars($r['colony_name'] ?? '') ?></small></td>
                                <td><?= htmlspecialchars($r['customer_name'] ?? '-') ?><br><small class="text-muted"><?= htmlspecialchars($r['customer_phone'] ?? '') ?></small></td>
                                <td><?= htmlspecialchars(substr((string)($r['due_date'] ?? ''), 0, 10)) ?></td>
                                <td class="text-end">Rs.<?= number_format((float)($r['amount'] ?? 0)) ?></td>
                                <td class="text-end text-success">Rs.<?= number_format((float)($r['paid_amount'] ?? 0)) ?></td>
                                <td class="text-end <?= $out > 0 ? 'text-danger fw-bold' : '' ?>">Rs.<?= number_format($out) ?></td>
                                <td><span class="badge bg-<?= ($r['status'] ?? '') === 'paid' ? 'success' : 'warning' ?>"><?= htmlspecialchars($r['status'] ?? '') ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
