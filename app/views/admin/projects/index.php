<?php
$page_title = $page_title ?? 'Projects & Townships';
$projects   = $projects ?? [];
$stats = $stats ?? ['total' => 0, 'under_construction' => 0, 'completed' => 0, 'planning' => 0, 'on_hold' => 0, 'total_plots' => 0, 'available_plots' => 0, 'sold_plots' => 0];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-building text-primary me-2"></i>Projects & Townships</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item active">Projects</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/projects/progress" class="btn btn-outline-info btn-sm">
            <i class="fas fa-tasks me-1"></i>Progress Tracker
        </a>
        <a href="<?= BASE_URL ?>/admin/noc-registry" class="btn btn-outline-warning btn-sm">
            <i class="fas fa-file-contract me-1"></i>NOC &amp; Registry
        </a>
        <a href="<?= BASE_URL ?>/admin/colonies" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-city me-1"></i>Colonies
        </a>
        <a href="<?= BASE_URL ?>/admin/projects/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>New Project
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['label' => 'Total Projects',       'key' => 'total',              'color' => 'primary',   'icon' => 'fa-building'],
        ['label' => 'Under Construction',    'key' => 'under_construction', 'color' => 'warning',   'icon' => 'fa-hard-hat'],
        ['label' => 'Completed',             'key' => 'completed',          'color' => 'success',   'icon' => 'fa-check-circle'],
        ['label' => 'Planning Stage',        'key' => 'planning',           'color' => 'info',      'icon' => 'fa-clipboard-list'],
        ['label' => 'Total Plots',           'key' => 'total_plots',        'color' => 'dark',      'icon' => 'fa-map'],
        ['label' => 'Available Plots',       'key' => 'available_plots',    'color' => 'success',   'icon' => 'fa-check-square'],
    ];
    foreach ($statCards as $sc): ?>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:3px solid var(--bs-<?= $sc['color'] ?>);">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-<?= $sc['color'] ?> bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:40px;height:40px;">
                    <i class="fas <?= $sc['icon'] ?> text-<?= $sc['color'] ?>"></i>
                </div>
                <div class="fw-bold fs-4 text-dark"><?= number_format($stats[$sc['key']] ?? 0) ?></div>
                <div class="text-muted small"><?= $sc['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Projects Table -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 fw-bold">All Projects</h6>
        <input type="text" id="projectSearch" class="form-control form-control-sm"
               placeholder="Search projects..." style="width:220px;"
               id="projectSearch">
    </div>
    <div class="card-body p-0">
        <?php if (!empty($projects)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="projectsTable">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="px-4">Project Name</th>
                            <th>Colony / Location</th>
                            <th class="text-center">Status</th>
                            <th>Progress</th>
                            <th>Plots</th>
                            <th>Price Range</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $p):
                            $st = $p['status'] ?? 'planning';
                            $sc = ['completed' => 'success', 'under_construction' => 'warning', 'planning' => 'info', 'on_hold' => 'secondary', 'cancelled' => 'danger', 'delayed' => 'danger'];
                            $stColor = $sc[$st] ?? 'secondary';
                            $progress = (int)($p['progress_pct'] ?? 0);
                            $prMin = (float)($p['price_range_min'] ?? 0);
                            $prMax = (float)($p['price_range_max'] ?? 0);
                        ?>
                        <tr class="project-row">
                            <td class="px-4">
                                <div class="d-flex align-items-start gap-2">
                                    <div>
                                        <a href="<?= BASE_URL ?>/admin/projects/view/<?= $p['id'] ?>" class="fw-semibold text-dark text-decoration-none">
                                            <?= htmlspecialchars($p['name'] ?? '') ?>
                                        </a>
                                        <?php if (!empty($p['rera_number'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1 small">
                                                <i class="fas fa-certificate me-1" style="font-size:9px;"></i>RERA
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($p['is_featured'] ?? 0): ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1 small"><i class="fas fa-star"></i></span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted"><?= ucfirst($p['project_type'] ?? 'residential') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($p['colony_name'])): ?>
                                    <a href="<?= BASE_URL ?>/admin/colonies?search=<?= urlencode($p['colony_name'] ?? '') ?>"
                                       class="text-decoration-none text-primary small fw-semibold">
                                        <i class="fas fa-city me-1"></i><?= htmlspecialchars($p['colony_name']) ?>
                                    </a><br>
                                <?php endif; ?>
                                <small class="text-muted">
                                    <?= htmlspecialchars($p['district_name'] ?? '') ?><?= !empty($p['state_name']) ? ', ' . htmlspecialchars($p['state_name']) : '' ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $stColor ?>"><?= ucwords(str_replace('_', ' ', $st)) ?></span>
                            </td>
                            <td style="min-width:100px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px; border-radius:4px;">
                                        <div class="progress-bar bg-<?= $progress >= 100 ? 'success' : ($progress >= 50 ? 'info' : 'warning') ?>"
                                             style="width:<?= $progress ?>%;" role="progressbar" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="small text-muted" style="white-space:nowrap;"><?= $progress ?>%</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary"><?= (int)($p['total_plots'] ?? 0) ?> total</span>
                                <span class="badge bg-success bg-opacity-10 text-success"><?= (int)($p['available_plots'] ?? 0) ?> avail</span>
                            </td>
                            <td class="small">
                                <?php if ($prMin > 0 || $prMax > 0): ?>
                                    ₹<?= number_format($prMin / 100000, 1) ?>L<?php if ($prMax > $prMin): ?> – ₹<?= number_format($prMax / 100000, 1) ?>L<?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/projects/view/<?= $p['id'] ?>" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/projects/edit/<?= $p['id'] ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/projects/progress/<?= $p['id'] ?>" class="btn btn-outline-info" title="Progress">
                                        <i class="fas fa-tasks"></i>
                                    </a>
                                    <?php if (!empty($p['colony_id'])): ?>
                                    <a href="<?= BASE_URL ?>/admin/plots?colony_id=<?= (int)$p['colony_id'] ?>" class="btn btn-outline-success" title="Plots">
                                        <i class="fas fa-map"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3 d-block opacity-50"></i>
                <h5 class="text-muted">No Projects Found</h5>
                <p class="text-muted small mb-3">Create your first real estate project to track progress and inventory.</p>
                <a href="<?= BASE_URL ?>/admin/projects/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Project
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function () {
    var si = document.getElementById('projectSearch');
    if (!si) return;
    si.addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#projectsTable .project-row').forEach(function (r) {
            r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
})();
</script>