<?php
$page_title = $page_title ?? 'Projects';
$projects   = $projects ?? [];
$stats = $stats ?? ['total'=>0,'under_construction'=>0,'completed'=>0,'planning'=>0,'on_hold'=>0,'total_plots'=>0,'available_plots'=>0,'sold_plots'=>0];
$total = count($projects);
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-building text-primary me-2"></i>Projects</h1>
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
            <i class="fas fa-file-contract me-1"></i>NOC & Registry
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
    <div class="col-6 col-md col-lg">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0d6efd;">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:46px;height:46px;">
                    <i class="fas fa-building text-primary"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $total ?></div>
                <div class="text-muted small">Total Projects</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #ffc107;">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:46px;height:46px;">
                    <i class="fas fa-cogs text-warning"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $stats['under_construction'] ?? 0 ?></div>
                <div class="text-muted small">Under Construction</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #198754;">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:46px;height:46px;">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $stats['completed'] ?? 0 ?></div>
                <div class="text-muted small">Completed</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0dcaf0;">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:46px;height:46px;">
                    <i class="fas fa-clipboard-list text-info"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $stats['planning'] ?? 0 ?></div>
                <div class="text-muted small">Planning</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #6c757d;">
            <div class="card-body text-center py-3">
                <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:46px;height:46px;">
                    <i class="fas fa-pause-circle text-secondary"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $stats['on_hold'] ?? 0 ?></div>
                <div class="text-muted small">On Hold</div>
            </div>
        </div>
    </div>
</div>

<!-- Projects Table -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">All Projects</h6>
        <input type="text" id="projectSearch" class="form-control form-control-sm" placeholder="Search projects..." style="width:200px;">
    </div>
    <div class="card-body p-0">
        <?php if (!empty($projects)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="projectsTable">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="px-4">Project Name</th>
                            <th>Type</th>
                            <th class="text-center">Status</th>
                            <th>Plots</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $p): ?>
                            <tr class="project-row">
                                <td class="px-4">
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($p['name'] ?? '') ?></div>
                                    <?php if (!empty($p['description'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 60)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-15 text-primary fw-semibold">
                                        <?= ucfirst($p['project_type'] ?? 'residential') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $st = $p['status'] ?? 'planning';
                                    $sc = [
                                        'completed'         => 'success',
                                        'under_construction'=> 'warning',
                                        'planning'          => 'info',
                                        'on_hold'           => 'secondary',
                                        'cancelled'         => 'danger',
                                    ];
                                    $stColor = $sc[$st] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $stColor ?>"><?= ucwords(str_replace('_', ' ', $st)) ?></span>
                                </td>
                                <td>
                                    <?php if (isset($p['total_plots'])): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary"><?= (int)$p['total_plots'] ?> total</span>
                                        <span class="badge bg-success bg-opacity-10 text-success"><?= (int)($p['available_plots'] ?? 0) ?> avail</span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?= date('d M Y', strtotime($p['created_at'] ?? 'now')) ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/projects/view/<?= $p['id'] ?>" class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/projects/edit/<?= $p['id'] ?>" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/projects/progress?project_id=<?= $p['id'] ?>" class="btn btn-outline-info" title="Progress">
                                            <i class="fas fa-tasks"></i>
                                        </a>
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
                <h5 class="text-muted">No projects found</h5>
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