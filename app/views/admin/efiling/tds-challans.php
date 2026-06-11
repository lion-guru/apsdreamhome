<?php
$page_title = $page_title ?? 'TDS Challans (Form 281)';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-money-check me-2 text-warning"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">FY <?= htmlspecialchars($fy) ?> | Quarter <?= htmlspecialchars($quarter) ?></span>
    </div>
    <div>
        <a href="/admin/efiling/tds" class="btn btn-outline-secondary btn-sm me-1"><i class="fas fa-arrow-left me-1"></i>TDS Filing</a>
        <a href="/admin/efiling/tds/challans/create" class="btn btn-danger btn-sm"><i class="fas fa-plus me-1"></i>New Challan</a>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

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
                    <?php for ($q = 1; $q <= 4; $q++): ?>
                        <option value="Q<?= $q ?>" <?= "Q$q" === $quarter ? 'selected' : '' ?>>Q<?= $q ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button></div>
        </form>
    </div>
</div>

<!-- Summary -->
<?php if (!empty($summary)): ?>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-primary"><?= $summary['total'] ?></div>
            <div class="small text-muted">Total Challans</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-success">₹<?= number_format($summary['total_deposited'], 0) ?></div>
            <div class="small text-muted">Total Deposited</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-info"><?= $summary['bank_count'] ?> via Bank</div>
            <div class="small text-muted">Total Challans</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-warning"><?= $summary['online_count'] ?> via Online</div>
            <div class="small text-muted">Total Challans</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Challans Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Challans</h6></div>
    <div class="card-body p-0">
        <?php if (empty($challans)): ?>
            <div class="p-4 text-center text-muted">No challans found. <a href="/admin/efiling/tds/challans/create">Create one</a>.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Challan No</th><th>BSR Code</th><th>Section</th><th>AY</th><th>Deposited</th><th>Date</th><th>Via</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($challans as $c): ?>
                        <tr>
                            <td>#<?= $c['id'] ?></td>
                            <td class="small fw-bold"><?= htmlspecialchars($c['challan_number'] ?? '-') ?></td>
                            <td class="small"><?= htmlspecialchars($c['bsr_code'] ?? '-') ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($c['section']) ?></span></td>
                            <td class="small"><?= htmlspecialchars($c['assessment_year']) ?></td>
                            <td class="fw-bold">₹<?= number_format($c['total_with_charges'], 0) ?></td>
                            <td class="small"><?= date('d M Y', strtotime($c['deposit_date'])) ?></td>
                            <td><span class="badge bg-<?= $c['deposited_via'] === 'online' ? 'primary' : 'info' ?>"><?= ucfirst($c['deposited_via']) ?></span></td>
                            <td><span class="badge bg-<?= $c['status'] === 'deposited' ? 'success' : ($c['status'] === 'failed' ? 'danger' : 'secondary') ?>"><?= ucfirst($c['status']) ?></span></td>
                            <td><a href="/admin/efiling/tds/challans/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
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
