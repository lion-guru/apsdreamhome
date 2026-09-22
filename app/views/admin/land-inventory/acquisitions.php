<?php
$acquisitions = $acquisitions ?? [];
$stats = $stats ?? ['total'=>0,'open'=>0,'registered'=>0,'lost'=>0,'pipeline_value'=>0];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-file-contract text-primary me-2"></i>Land Acquisitions</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/land-inventory/leads">Land Leads</a></li>
                <li class="breadcrumb-item active">Acquisitions</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/land-inventory/leads"   class="btn btn-outline-primary btn-sm"><i class="fas fa-mountain me-1"></i>Land Leads</a>
        <a href="<?= BASE_URL ?>/admin/land-inventory/brokers" class="btn btn-outline-secondary btn-sm"><i class="fas fa-handshake me-1"></i>Brokers</a>
        <a href="<?= BASE_URL ?>/admin/land/records"           class="btn btn-outline-info btn-sm"><i class="fas fa-scroll me-1"></i>Land Records</a>
        <a href="<?= BASE_URL ?>/admin/colony-pipeline"        class="btn btn-outline-success btn-sm"><i class="fas fa-sitemap me-1"></i>Colony Pipeline</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0d6efd;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-file-contract text-primary"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= (int)$stats['total'] ?></div>
                <div class="text-muted small">Total Deals</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0dcaf0;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-spinner text-info"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= (int)$stats['open'] ?></div>
                <div class="text-muted small">In Pipeline</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #198754;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= (int)$stats['registered'] ?></div>
                <div class="text-muted small">Registered</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #dc3545;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-times-circle text-danger"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= (int)$stats['lost'] ?></div>
                <div class="text-muted small">Lost / Dropped</div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($stats['pipeline_value'])): ?>
<div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius:10px; border-left:4px solid #0dcaf0 !important;">
    <i class="fas fa-rupee-sign me-2"></i>
    <strong>Total Pipeline Value:</strong>
    ₹<?= number_format((float)$stats['pipeline_value'], 0) ?>
    (<?= (int)$stats['open'] ?> active acquisitions)
</div>
<?php endif; ?>

<!-- Table Card -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Acquisition Pipeline</h6>
        <input type="text" id="acqSearch" class="form-control form-control-sm" placeholder="Search acquisitions..." style="width:200px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="acqTable">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="px-4">Deal #</th>
                        <th>Land Lead</th>
                        <th>Owner</th>
                        <th>Survey #</th>
                        <th>Final Price</th>
                        <th>Stamp Duty</th>
                        <th>Reg Fee</th>
                        <th class="text-center">Status</th>
                        <th>Reg Date</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($acquisitions as $a): ?>
                        <tr class="acq-row">
                            <td class="px-4 fw-bold text-primary">#<?= (int)($a['id'] ?? 0) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= (int)($a['land_lead_id'] ?? 0) ?>" class="small text-decoration-none">
                                    Lead #<?= (int)($a['land_lead_id'] ?? 0) ?>
                                </a>
                            </td>
                            <td class="fw-semibold small"><?= htmlspecialchars($a['land_owner_name'] ?? '—') ?></td>
                            <td class="small text-muted font-monospace"><?= htmlspecialchars($a['survey_number'] ?? '—') ?></td>
                            <td class="fw-bold">₹<?= number_format((float)($a['final_price'] ?? 0)) ?></td>
                            <td class="small">₹<?= number_format((float)($a['stamp_duty_amount'] ?? 0)) ?></td>
                            <td class="small">₹<?= number_format((float)($a['registration_fee'] ?? 0)) ?></td>
                            <td class="text-center">
                                <?php
                                $ast = $a['status'] ?? '';
                                $asc = ['registered'=>'success','dropped'=>'danger','in_progress'=>'info','negotiation'=>'warning'];
                                $acolor = $asc[$ast] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $acolor ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $ast))) ?></span>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($a['registration_date'] ?? '—') ?></td>
                            <td class="text-end pe-4">
                                <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= (int)($a['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($acquisitions)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-file-contract fa-3x text-muted mb-3 d-block opacity-50"></i>
                                <h6 class="text-muted">No closed deals yet</h6>
                                <p class="text-muted small mb-3">Convert land leads into acquisitions when deals are finalized.</p>
                                <a href="<?= BASE_URL ?>/admin/land-inventory/leads" class="btn btn-sm btn-primary">
                                    <i class="fas fa-mountain me-1"></i>View Land Leads
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function () {
    var si = document.getElementById('acqSearch');
    if (!si) return;
    si.addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#acqTable .acq-row').forEach(function (r) {
            r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
})();
</script>
