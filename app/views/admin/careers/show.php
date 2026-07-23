<?php $pageTitle = 'Job Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-briefcase me-2"></i>Job Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/careers">Careers</a></li>
                    <li class="breadcrumb-item active"><?= $job['title'] ?? 'Job' ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/careers/<?= $job['id'] ?? 0 ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="<?= BASE_URL ?>/admin/careers/applications?job_id=<?= $job['id'] ?? 0 ?>" class="btn btn-info btn-sm"><i class="fas fa-users me-1"></i>Applications (<?= $applicationCount ?? 0 ?>)</a>
                <a href="<?= BASE_URL ?>/admin/careers" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($job)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-briefcase fa-4x d-block mb-3"></i><h5>Job not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><?= $job['title'] ?></h5></div>
                <div class="card-body aps-cp-card-body"><p><?= nl2br($job['description'] ?? 'No description') ?></p></div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-users me-2"></i>Applicants</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light"><tr><th class="ps-4">Name</th><th>Email</th><th>Phone</th><th>Applied</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
                            <tbody>
                                <?php if (empty($applicants)): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No applications yet</td></tr>
                                <?php else: ?>
                                    <?php foreach ($applicants as $a): ?>
                                    <tr><td class="ps-4"><strong><?= $a['name'] ?></strong></td><td><?= $a['email'] ?></td><td><?= $a['phone'] ?? '-' ?></td><td><?= date('d M Y', strtotime($a['created_at'] ?? 'now')) ?></td><td><span class="badge bg-<?= ($a['status'] ?? 'new') === 'new' ? 'primary' : (($a['status'] ?? 'new') === 'shortlisted' ? 'success' : 'secondary') ?>-subtle text-<?= ($a['status'] ?? 'new') === 'new' ? 'primary' : (($a['status'] ?? 'new') === 'shortlisted' ? 'success' : 'secondary') ?> rounded-pill px-3"><?= ucfirst($a['status'] ?? 'New') ?></span></td><td class="text-end pe-4"><button class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></button></td></tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3"><small class="text-muted d-block">Department</small><strong><?= $job['department'] ?? '-' ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Location</small><strong><?= $job['location'] ?? '-' ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Employment Type</small><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= ucfirst(str_replace('_', ' ', $job['employment_type'] ?? 'Full Time')) ?></span></div>
                    <div class="mb-3"><small class="text-muted d-block">Experience</small><strong><?= $job['experience'] ?? '-' ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Salary</small><strong class="text-success"><?= $job['salary'] ?? 'Negotiable' ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Vacancies</small><strong><?= $job['vacancies'] ?? 1 ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Status</small><span class="badge bg-<?= ($job['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= ($job['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?> rounded-pill px-3"><?= ucfirst($job['status'] ?? 'Active') ?></span></div>
                    <div class="mb-3"><small class="text-muted d-block">Posted</small><?= date('d M Y', strtotime($job['created_at'] ?? 'now')) ?></div>
                    <div><small class="text-muted d-block">Application Count</small><strong class="text-primary"><?= $applicationCount ?? 0 ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
