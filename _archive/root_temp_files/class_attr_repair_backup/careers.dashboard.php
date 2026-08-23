<?php $pageTitle = 'Careers Dashboard'; ?>
<?php $stats = $stats ?? ['total_jobs' => 0, 'active_jobs' => 0, 'total_applicants' => 0, 'new_applications' => 0]; $recentApplicants = $recentApplicants ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item active">Careers</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-briefcase me-2"></i>Careers Dashboard</h4>
        <div><a href="<?= BASE_URL ?>admin/careers/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Job</a><a href="<?= BASE_URL ?>admin/careers/applications" class="btn btn-outline-primary btn-sm ms-2"><i class="fas fa-users me-1"></i>Applications</a></div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Total Jobs</small><h3 class="mb-0 mt-1"><?= number_format($stats['total_jobs'] ?? 0) ?></h3></div><div class="bg-primary-subtle p-3 rounded"><i class="fas fa-briefcase fa-2x text-primary"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Active</small><h3 class="mb-0 mt-1"><?= number_format($stats['active_jobs'] ?? 0) ?></h3></div><div class="bg-success-subtle p-3 rounded"><i class="fas fa-check-circle fa-2x text-success"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Total Applicants</small><h3 class="mb-0 mt-1"><?= number_format($stats['total_applicants'] ?? 0) ?></h3></div><div class="bg-info-subtle p-3 rounded"><i class="fas fa-users fa-2x text-info"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">New</small><h3 class="mb-0 mt-1 text-warning"><?= number_format($stats['new_applications'] ?? 0) ?></h3></div><div class="bg-warning-subtle p-3 rounded"><i class="fas fa-star fa-2x text-warning"></i></div></div></div></div></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Recent Applicants</h6><a href="<?= BASE_URL ?>admin/careers/applications" class="btn btn-outline-primary btn-sm">View All</a></div>
                <div class="card-body p-0">
                    <?php if (empty($recentApplicants)): ?>
                    <div class="text-center py-4"><p class="text-muted mb-0">No applications yet</p></div>
                    <?php else: ?>
                    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Applicant</th><th>Position</th><th>Applied</th><th>Status</th></tr></thead>
                        <tbody><?php foreach ($recentApplicants as $a): ?><tr><td><?= htmlspecialchars($a['name'] ?? '-') ?></td><td><?= htmlspecialchars($a['position'] ?? $a['job_title'] ?? '-') ?></td><td><?= htmlspecialchars($a['created_at'] ?? '-') ?></td>
                        <td><span class="badge bg-<?= ($a['status'] ?? '') === 'pending' ? 'warning' : (($a['status'] ?? '') === 'reviewed' ? 'info' : 'success') ?>"><?= ucfirst($a['status'] ?? 'pending') ?></span></td></tr><?php endforeach; ?></tbody></table></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Departments</h6></div>
                <div class="card-body aps-cp-card-body"><div class="bg-light rounded d-flex align-items-center justify-content-center" class="style-48982"><p class="text-muted mb-0"><i class="fas fa-chart-pie me-2"></i>Department chart</p></div></div>
            </div>
        </div>
    </div>
</div>
