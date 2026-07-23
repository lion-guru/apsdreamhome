<?php
$page_title = $page_title ?? 'GST E-Filing';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-alt me-2 text-primary"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">FY <?= htmlspecialchars($fy) ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/efiling" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'] ?? ''); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<!-- Monthly Summary -->
<?php if (!empty($summary['success']) && !empty($summary['summary'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Monthly GST Summary</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Month</th><th class="text-end">Output Tax</th><th class="text-end">Input Tax (ITC)</th><th class="text-end">Net Payable</th><th>Invoices</th></tr>
                </thead>
                <tbody>
                <?php
                $totalOut = $totalIn = $totalNet = 0;
                foreach ($summary['summary'] as $m):
                    $totalOut += $m['out_tax'];
                    $totalIn += $m['in_tax'];
                    $totalNet += $m['net_payable'];
                ?>
                    <tr>
                        <td class="small"><?= htmlspecialchars($m['period']) ?></td>
                        <td class="text-end">₹<?= number_format($m['out_tax'], 0) ?></td>
                        <td class="text-end text-success">₹<?= number_format($m['in_tax'], 0) ?></td>
                        <td class="text-end fw-bold <?= $m['net_payable'] > 0 ? 'text-danger' : 'text-success' ?>">₹<?= number_format($m['net_payable'], 0) ?></td>
                        <td class="small"><?= ($m['output']['count'] ?? 0) + ($m['input']['count'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr><th>Total</th><th class="text-end">₹<?= number_format($totalOut, 0) ?></th><th class="text-end">₹<?= number_format($totalIn, 0) ?></th><th class="text-end">₹<?= number_format($totalNet, 0) ?></th><th></th></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Generate Returns -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-file-export me-2 text-primary"></i>Generate GSTR-1</h6></div>
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/efiling/gst/gstr1">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                <?php foreach ($months as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= $k == $month ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Year</label>
                            <input type="number" name="year" value="<?= $year ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">FY</label>
                            <select name="fy" class="form-select form-select-sm">
                                <?php foreach ($fy_list as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= $k === $fy ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-file-export me-1"></i>Generate GSTR-1 JSON</button>
                    <a href="<?= BASE_URL ?>/admin/efiling/gst/export/gstr1?fy=<?= urlencode($fy) ?>&month=<?= (int)$month ?>&year=<?= (int)$year ?>" class="btn btn-sm btn-outline-success ms-2"><i class="fas fa-download me-1"></i>Export GSTR-1 JSON</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-file-invoice me-2 text-warning"></i>Generate GSTR-3B</h6></div>
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/efiling/gst/gstr3b">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                <?php foreach ($months as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= $k == $month ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Year</label>
                            <input type="number" name="year" value="<?= $year ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">FY</label>
                            <select name="fy" class="form-select form-select-sm">
                                <?php foreach ($fy_list as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= $k === $fy ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-file-export me-1"></i>Generate GSTR-3B JSON</button>
                    <a href="<?= BASE_URL ?>/admin/efiling/gst/export/gstr3b?fy=<?= urlencode($fy) ?>&month=<?= (int)$month ?>&year=<?= (int)$year ?>" class="btn btn-sm btn-outline-success ms-2"><i class="fas fa-download me-1"></i>Export GSTR-3B JSON</a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Submissions -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between">
        <h6 class="mb-0"><i class="fas fa-paper-plane me-2"></i>GST Filing Submissions</h6>
        <a href="<?= BASE_URL ?>/admin/efiling/submissions?type=gstr1&fy=<?= urlencode($fy) ?>" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($submissions)): ?>
            <div class="p-3 text-center text-muted small">No submissions yet. Generate GSTR-1 or GSTR-3B above.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Type</th><th>Period</th><th>Records</th><th>Amount (₹)</th><th>Status</th><th>ARN</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($submissions as $s): ?>
                        <tr>
                            <td>#<?= $s['id'] ?></td>
                            <td><span class="badge bg-<?= $s['submission_type'] === 'gstr1' ? 'primary' : 'warning' ?>"><?= strtoupper($s['submission_type']) ?></span></td>
                            <td class="small"><?= $s['period_month'] ? date('M Y', mktime(0,0,0,$s['period_month'],1,$s['period_year'] ?? 2025)) : $s['financial_year'] ?></td>
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

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
?>
