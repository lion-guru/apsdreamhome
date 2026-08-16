<?php
$page_title = $page_title ?? 'Filing Calendar';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-calendar-check me-2 text-success"></i><?= htmlspecialchars($page_title ?? '') ?></h4>
        <span class="text-muted">FY <?= htmlspecialchars($fy ?? '') ?><?= $type ? ' | ' . strtoupper($type) : '' ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/efiling" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
            <div class="col-auto">
                <select name="fy" class="form-select form-select-sm">
                    <?php foreach ($fy_list as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $k === $fy ? 'selected' : '' ?>><?= htmlspecialchars($v ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="tds_return" <?= ($type ?? '') === 'tds_return' ? 'selected' : '' ?>>TDS Return</option>
                    <option value="tds_challan" <?= ($type ?? '') === 'tds_challan' ? 'selected' : '' ?>>TDS Challan</option>
                    <option value="gstr1" <?= ($type ?? '') === 'gstr1' ? 'selected' : '' ?>>GSTR-1</option>
                    <option value="gstr3b" <?= ($type ?? '') === 'gstr3b' ? 'selected' : '' ?>>GSTR-3B</option>
                    <option value="form16a" <?= ($type ?? '') === 'form16a' ? 'selected' : '' ?>>Form 16A</option>
                    <option value="gstr9" <?= ($type ?? '') === 'gstr9' ? 'selected' : '' ?>>GSTR-9</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button></div>
        </form>
    </div>
</div>

<!-- Overdue Alert -->
<?php if (!empty($overdue)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle me-2"></i><strong><?= count($overdue) ?> overdue filing(s)!</strong> These require immediate attention.
</div>
<?php endif; ?>

<!-- Deadlines Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($deadlines)): ?>
            <div class="p-4 text-center text-muted">No deadlines found for this period.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Type</th><th>Period</th><th>Description</th><th>Due Date</th><th>Days</th><th>Penalty/Day</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($deadlines as $d):
                        $today = (int)date('Y-m-d');
                        $daysLeft = $d['due_date'] >= $today
                            ? (int)((strtotime($d['due_date']) - time()) / 86400)
                            : -(int)((time() - strtotime($d['due_date'])) / 86400);
                        $isOverdue = $daysLeft < 0;
                        $typeColors = [
                            'tds_return' => 'danger', 'tds_challan' => 'warning',
                            'gstr1' => 'primary', 'gstr3b' => 'info',
                            'form16a' => 'secondary', 'gstr9' => 'dark',
                        ];
                    ?>
                        <tr class="<?= $isOverdue ? 'table-danger' : ($daysLeft <= 7 && $d['status'] === 'upcoming' ? 'table-warning' : '') ?>">
                            <td><span class="badge bg-<?= $typeColors[$d['filing_type']] ?? 'secondary' ?>"><?= strtoupper($d['filing_type']) ?></span></td>
                            <td class="small"><?= $d['quarter'] ? "Q{$d['quarter']}" : ($d['period_month'] ? date('M', mktime(0,0,0,$d['period_month'])) : '-') ?></td>
                            <td class="small"><?= htmlspecialchars($d['description'] ?? '') ?></td>
                            <td class="small fw-bold"><?= date('d M Y', strtotime($d['due_date'])) ?></td>
                            <td>
                                <?php if ($isOverdue): ?>
                                    <span class="badge bg-danger"><?= abs($daysLeft) ?>d overdue</span>
                                <?php elseif ($daysLeft <= 3): ?>
                                    <span class="badge bg-danger"><?= $daysLeft ?>d left</span>
                                <?php elseif ($daysLeft <= 7): ?>
                                    <span class="badge bg-warning text-dark"><?= $daysLeft ?>d left</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= $daysLeft ?>d left</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= $d['penalty_per_day'] > 0 ? '?' . number_format($d['penalty_per_day'], 0) . '/day' : '-' ?></td>
                            <td><span class="badge bg-<?= $d['status'] === 'completed' ? 'success' : ($d['status'] === 'overdue' ? 'danger' : ($d['status'] === 'extended' ? 'warning' : 'secondary')) ?>"><?= ucfirst($d['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

