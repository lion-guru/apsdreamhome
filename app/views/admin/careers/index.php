<?php $pageTitle = 'Job Listings'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-briefcase me-2"></i>Job Listings</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Jobs</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/careers/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Post Job</a>
                <a href="<?= BASE_URL ?>/admin/careers/applications" class="btn btn-info btn-sm"><i class="fas fa-users me-1"></i>Applications</a>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Active Jobs</h6><h3 class="mb-0"><?= number_format($activeJobs ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Applications</h6><h3 class="mb-0"><?= number_format($totalApplications ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Shortlisted</h6><h3 class="mb-0"><?= number_format($shortlisted ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Positions Filled</h6><h3 class="mb-0"><?= number_format($filled ?? 0) ?></h3></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Jobs</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">Title</th><th>Department</th><th>Location</th><th>Applications</th><th>Posted</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($jobs)): ?>
                            <tr><td colspan="7" class="text-center py-5">
                                <i class="fas fa-briefcase fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No job listings yet</h5>
                                <p class="text-muted mb-3">Post your first job to start attracting talent for your team.</p>
                                <a href="<?= BASE_URL ?>/admin/jobs/manage/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Post Job</a>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $j): ?>
                            <tr><td class="ps-4"><strong><?= $j['title'] ?></strong></td><td><?= $j['department'] ?? '-' ?></td><td><?= $j['location'] ?? '-' ?></td><td><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= $j['application_count'] ?? 0 ?></span></td><td><?= date('d M Y', strtotime($j['created_at'] ?? 'now')) ?></td><td><span class="badge bg-<?= ($j['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= ($j['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?> rounded-pill px-3"><?= ucfirst($j['status'] ?? 'Active') ?></span></td><td class="text-end pe-4"><a href="<?= BASE_URL ?>/admin/careers/show/<?= $j['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a> <a href="<?= BASE_URL ?>/admin/careers/edit/<?= $j['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
