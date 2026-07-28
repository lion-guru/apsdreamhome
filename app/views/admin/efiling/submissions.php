<?php
$page_title = $page_title ?? 'Filing Submissions';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-paper-plane me-2"></i><?= htmlspecialchars($page_title) ?></h4>
    </div>
    <a href="<?= BASE_URL ?>/admin/efiling" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="tds_return" <?= ($type ?? '') === 'tds_return' ? 'selected' : '' ?>>TDS Return</option>
                    <option value="tds_challan" <?= ($type ?? '') === 'tds_challan' ? 'selected' : '' ?>>TDS Challan</option>
                    <option value="gstr1" <?= ($type ?? '') === 'gstr1' ? 'selected' : '' ?>>GSTR-1</option>
                    <option value="gstr3b" <?= ($type ?? '') === 'gstr3b' ? 'selected' : '' ?>>GSTR-3B</option>
                    <option value="gstr9" <?= ($type ?? '') === 'gstr9' ? 'selected' : '' ?>>GSTR-9</option>
                    <option value="form16a" <?= ($type ?? '') === 'form16a' ? 'selected' : '' ?>>Form 16A</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="prepared" <?= ($status ?? '') === 'prepared' ? 'selected' : '' ?>>Prepared</option>
                    <option value="submitted" <?= ($status ?? '') === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                    <option value="accepted" <?= ($status ?? '') === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                    <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="fy" class="form-select form-select-sm">
                    <option value="">All FY</option>
                    <?php foreach ($fy_list as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($fy ?? '') === $k ? 'selected' : '' ?>><?= $k ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button></div>
        </form>
    </div>
</div>

<!-- Submissions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($submissions)): ?>
            <div class="p-4 text-center text-muted">No submissions found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Type</th><th>FY</th><th>Period</th><th>Records</th><th>Amount (?)</th><th>Status</th><th>ARN/Token</th><th>Created</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($submissions as $s):
                        $typeLabels = ['tds_return' => 'TDS Return', 'tds_challan' => 'Challan 281', 'gstr1' => 'GSTR-1', 'gstr3b' => 'GSTR-3B', 'gstr9' => 'GSTR-9', 'form16a' => 'Form 16A', 'form16' => 'Form 16'];
                        $periodLabel = $s['quarter'] ? "Q{$s['quarter']}" : ($s['period_month'] ? date('M Y', mktime(0,0,0,$s['period_month'],1,$s['period_year'] ?? 2025)) : '-');
                    ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td><span class="badge bg-secondary"><?= $typeLabels[$s['submission_type']] ?? strtoupper($s['submission_type']) ?></span></td>
                            <td class="small"><?= htmlspecialchars($s['financial_year']) ?></td>
                            <td class="small"><?= $periodLabel ?></td>
                            <td><?= $s['total_records'] ?></td>
                            <td>?<?= number_format($s['total_amount'], 0) ?></td>
                            <td><span class="badge bg-<?= $s['status'] === 'accepted' ? 'success' : ($s['status'] === 'rejected' ? 'danger' : ($s['status'] === 'submitted' ? 'primary' : ($s['status'] === 'prepared' ? 'info' : 'secondary'))) ?>"><?= ucfirst($s['status']) ?></span></td>
                            <td class="small"><?= htmlspecialchars($s['portal_reference'] ?? '-') ?></td>
                            <td class="small"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                            <td><a href="<?= BASE_URL ?>/admin/efiling/submissions/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
?>
