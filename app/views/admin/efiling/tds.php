<?php
$page_title = $page_title ?? 'TDS E-Filing';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-invoice-dollar me-2 text-danger"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">FY <?= htmlspecialchars($fy) ?> | Quarter <?= htmlspecialchars($quarter) ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/efiling" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'] ?? ''); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small">Financial Year</label>
                <select name="fy" class="form-select form-select-sm">
                    <?php foreach ($fy_list as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $k === $fy ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small">Quarter</label>
                <select name="quarter" class="form-select form-select-sm">
                    <?php for ($q = 1; $q <= 4; $q++): ?>
                        <option value="Q<?= $q ?>" <?= "Q$q" === $quarter ? 'selected' : '' ?>>Q<?= $q ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button></div>
        </form>
    </div>
</div>

<!-- TDS Summary by Section -->
<?php if (!empty($summary['by_section'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-table me-2"></i>TDS Summary by Section</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Section</th><th>Description</th><th class="text-end">Count</th><th class="text-end">Gross (?)</th><th class="text-end">TDS (?)</th><th class="text-end">Pending</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($summary['by_section'] as $s): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['tds_section']) ?></strong></td>
                        <td class="small"><?= htmlspecialchars(($rates[$s['tds_section']]['desc'] ?? $s['tds_section'])) ?></td>
                        <td class="text-end"><?= $s['count'] ?></td>
                        <td class="text-end">₹<?= number_format($s['total_gross'], 0) ?></td>
                        <td class="text-end fw-bold">₹<?= number_format($s['total_tds'], 0) ?></td>
                        <td class="text-end"><span class="badge bg-<?= $s['pending_count'] > 0 ? 'warning' : 'success' ?>"><?= $s['pending_count'] ?></span></td>
                        <td><span class="badge bg-<?= $s['filed_count'] > 0 ? 'success' : 'secondary' ?>"><?= $s['filed_count'] > 0 ? 'Filed' : 'Pending' ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr><th colspan="2">Total</th><th class="text-end"><?= $summary['totals']['total_records'] ?></th><th class="text-end">₹<?= number_format($summary['totals']['total_gross'], 0) ?></th><th class="text-end">₹<?= number_format($summary['totals']['total_tds'], 0) ?></th><th class="text-end"><span class="badge bg-warning"><?= $summary['totals']['pending_count'] ?></span></th><th></th></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Generate Form 26Q -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Generate Form 26Q</h6></div>
    <div class="card-body aps-cp-card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/efiling/tds/generate" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="col-auto">
                <select name="fy" class="form-select form-select-sm">
                    <?php foreach ($fy_list as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $k === $fy ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="quarter" class="form-select form-select-sm">
                    <?php for ($q = 1; $q <= 4; $q++): ?>
                        <option value="Q<?= $q ?>" <?= "Q$q" === $quarter ? 'selected' : '' ?>>Q<?= $q ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Generate Form 26Q for <?= htmlspecialchars($quarter) ?> <?= htmlspecialchars($fy) ?>?')">
                    <i class="fas fa-file-export me-1"></i>Generate 26Q JSON
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Submissions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between">
        <h6 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Filing Submissions</h6>
        <a href="<?= BASE_URL ?>/admin/efiling/submissions?type=tds_return&fy=<?= urlencode($fy) ?>" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($submissions)): ?>
            <div class="p-3 text-center text-muted small">No submissions for this quarter. Click "Generate 26Q JSON" above.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Date</th><th>Records</th><th>TDS (?)</th><th>Status</th><th>ARN/Token</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($submissions as $s): ?>
                        <tr>
                            <td>#<?= $s['id'] ?></td>
                            <td class="small"><?= date('d M Y', strtotime($s['filing_date'])) ?></td>
                            <td><?= $s['total_records'] ?></td>
                            <td>₹<?= number_format($s['total_amount'], 0) ?></td>
                            <td><span class="badge bg-<?= $s['status'] === 'accepted' ? 'success' : ($s['status'] === 'rejected' ? 'danger' : ($s['status'] === 'submitted' ? 'primary' : 'secondary')) ?>"><?= ucfirst($s['status']) ?></span></td>
                            <td class="small"><?= htmlspecialchars($s['portal_reference'] ?? '-') ?></td>
                            <td><a href="<?= BASE_URL ?>/admin/efiling/submissions/<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

