<?php
$page_title = $page_title ?? 'Payroll Periods';
$periods = $periods ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-calendar-lock me-2"></i><?= htmlspecialchars($page_title) ?></h4>
    <a href="<?= BASE_URL ?>/admin/salary" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Salary</a>
</div>
<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Locked periods block payslip generation and payments. The auto-lock cron locks open periods older than 2 months. Reopen a locked period (never closed ones) to allow corrections.</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Period</th><th>Status</th><th>Locked At</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($periods)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No payroll periods yet — they are created automatically on first payslip/payment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($periods as $p): ?>
                            <?php $st = $p['status'] ?? 'open'; ?>
                            <tr>
                                <td class="fw-medium"><?= date('F Y', mktime(0, 0, 0, $p['period_month'] ?? 1, 1, $p['period_year'] ?? date('Y'))) ?></td>
                                <td><span class="badge bg-<?= $st === 'open' ? 'success' : ($st === 'locked' ? 'warning' : 'secondary') ?>"><?= ucfirst($st) ?></span></td>
                                <td><?= htmlspecialchars($p['locked_at'] ?? '-') ?></td>
                                <td class="text-end">
                                    <?php if ($st === 'locked'): ?>
                                    <form method="post" action="<?= BASE_URL ?>/admin/salary/periods/reopen" class="d-inline" onsubmit="return confirm('Reopen <?= (int)($p['period_month'] ?? 0) ?>/<?= (int)($p['period_year'] ?? 0) ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="month" value="<?= (int)($p['period_month'] ?? 0) ?>">
                                        <input type="hidden" name="year" value="<?= (int)($p['period_year'] ?? 0) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Reopen period"><i class="fas fa-lock-open"></i> Reopen</button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
