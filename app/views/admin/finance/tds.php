<?php $page_title = $page_title ?? 'TDS Register'; $page_heading = $page_heading ?? 'TDS Register & Compliance'; $fy = $fy ?? '2025-26'; $quarter = $quarter ?? null; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>TDS Register</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/tds-certificates" class="btn btn-outline-primary"><i class="fas fa-certificate me-1"></i>Certificates</a>
            <a href="<?= BASE_URL ?>/admin/finance/tds-record" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Record TDS</a>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="url" value="/admin/finance/tds">
                <div class="col-md-3"><label class="form-label small">Financial Year</label>
                    <select name="fy" class="form-select form-select-sm">
                        <?php foreach (['2024-25','2025-26','2026-27','2027-28'] as $y): ?>
                            <option value="<?= $y ?>" <?= $fy === $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label small">Quarter</label>
                    <select name="quarter" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (['Q1','Q2','Q3','Q4'] as $q): ?>
                            <option value="<?= $q ?>" <?= $quarter === $q ? 'selected' : '' ?>><?= $q ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button></div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Total Gross</div><div class="aps-cp-stat-value">₹<?= number_format((float)($summary['total_gross'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-4"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Total TDS Deducted</div><div class="aps-cp-stat-value text-danger">₹<?= number_format((float)($summary['total_tds'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-4"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">FY</div><div class="aps-cp-stat-value text-primary"><?= htmlspecialchars($fy) ?></div></div></div></div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Section</th><th>Deductee</th><th>PAN</th><th>Quarter</th><th class="text-end">Gross</th><th class="text-end">TDS</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php if (empty($entries)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No TDS entries for <?= htmlspecialchars($fy) ?></td></tr>
                <?php else: foreach ($entries as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['tds_date'] ?? '-') ?></td>
                        <td><code><?= htmlspecialchars($t['section_code'] ?? '-') ?></code></td>
                        <td><?= htmlspecialchars($t['deductee_name'] ?? '-') ?></td>
                        <td><code><?= htmlspecialchars($t['deductee_pan'] ?? '-') ?></code></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($t['quarter'] ?? '-') ?></span></td>
                        <td class="text-end">₹<?= number_format((float)($t['gross_amount'] ?? 0), 2) ?></td>
                        <td class="text-end fw-bold text-danger">₹<?= number_format((float)($t['tds_amount'] ?? 0), 2) ?></td>
                        <td><span class="badge bg-<?= ($t['deposit_status'] ?? '') === 'deposited' ? 'success' : 'warning' ?>"><?= htmlspecialchars($t['deposit_status'] ?? 'pending') ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
