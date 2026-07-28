<?php
$page_title = $page_title ?? 'TDS Certificates (Form 16A)';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-certificate me-2 text-info"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">FY <?= htmlspecialchars($fy) ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/efiling" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="fy" class="form-select form-select-sm">
                    <?php foreach ($fy_list as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $k === $fy ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="quarter" class="form-select form-select-sm">
                    <option value="">All Quarters</option>
                    <?php for ($q = 1; $q <= 4; $q++): ?>
                        <option value="Q<?= $q ?>" <?= ($quarter ?? '') === "Q$q" ? 'selected' : '' ?>>Q<?= $q ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="generated" <?= ($status ?? '') === 'generated' ? 'selected' : '' ?>>Generated</option>
                    <option value="sent" <?= ($status ?? '') === 'sent' ? 'selected' : '' ?>>Sent</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button></div>
        </form>
    </div>
</div>

<!-- Certificates Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Form 16A Certificates</h6></div>
    <div class="card-body p-0">
        <?php if (empty($certificates)): ?>
            <div class="p-4 text-center text-muted">No certificates found for this period.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Certificate No</th><th>Section</th><th>AY</th><th>Quarter</th><th>TDS (?)</th><th>Status</th><th>Issued</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($certificates as $c): ?>
                        <tr>
                            <td>#<?= $c['id'] ?></td>
                            <td class="small fw-bold"><?= htmlspecialchars($c['certificate_number']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($c['section']) ?></span></td>
                            <td class="small"><?= htmlspecialchars($c['assessment_year']) ?></td>
                            <td class="small">Q<?= $c['quarter'] ?></td>
                            <td class="fw-bold">?<?= number_format($c['tds_amount'], 0) ?></td>
                            <td><span class="badge bg-<?= $c['status'] === 'sent' ? 'success' : ($c['status'] === 'generated' ? 'info' : 'secondary') ?>"><?= ucfirst($c['status']) ?></span></td>
                            <td class="small"><?= $c['issued_date'] ? date('d M Y', strtotime($c['issued_date'])) : '-' ?></td>
                            <td class="text-nowrap">
                                <a href="<?= BASE_URL ?>/admin/efiling/tds/certificates/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="<?= BASE_URL ?>/admin/efiling/tds/certificate/<?= $c['id'] ?>/download" class="btn btn-sm btn-success" title="Download Form 16A"><i class="fas fa-download me-1"></i>Form 16A</a>
                            </td>
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
