<?php $pageTitle = 'Job Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-briefcase me-2"></i>Job Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/admin/careers">Careers</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($job['title'] ?? 'Job', ENT_QUOTES, 'UTF-8') ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/admin/careers/<?= htmlspecialchars($job['id'] ?? 0, ENT_QUOTES, 'UTF-8') ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/admin/careers/applications?job_id=<?= htmlspecialchars($job['id'] ?? 0, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-info btn-sm"><i class="fas fa-users me-1"></i>Applications (<?= htmlspecialchars($applicationCount ?? 0, ENT_QUOTES, 'UTF-8') ?>)</a>
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/admin/careers" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($job)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-briefcase fa-4x d-block mb-3"></i><h5>Job not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><?= htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8') ?></h5></div>
                <div class="card-body aps-cp-card-body"><p><?= nl2br(htmlspecialchars($job['description'] ?? 'No description', ENT_QUOTES, 'UTF-8')) ?></p></div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-users me-2"></i>Applicants</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light"><tr><th scope="col" class="ps-4">Name</th><th scope="col">Email</th><th scope="col">Phone</th><th scope="col">Applied</th><th scope="col">Status</th><th scope="col" class="text-end pe-4">Actions</th></tr></thead>
                            <tbody>
                                <?php if (empty($applicants)): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No applications yet</td></tr>
                                <?php else: ?>
                                    <?php foreach ($applicants as $a): ?>
                                    <tr><td class="ps-4"><strong><?= htmlspecialchars($a['name'], ENT_QUOTES, 'UTF-8') ?></strong></td><td><?= htmlspecialchars($a['email'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($a['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(date('d M Y', strtotime($a['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?></td><td><span class="badge bg-<?= htmlspecialchars(($a['status'] ?? 'new') === 'new' ? 'primary' : (($a['status'] ?? 'new') === 'shortlisted' ? 'success' : 'secondary'), ENT_QUOTES, 'UTF-8') ?>-subtle text-<?= htmlspecialchars(($a['status'] ?? 'new') === 'new' ? 'primary' : (($a['status'] ?? 'new') === 'shortlisted' ? 'success' : 'secondary'), ENT_QUOTES, 'UTF-8') ?> rounded-pill px-3"><?= htmlspecialchars(ucfirst($a['status'] ?? 'New'), ENT_QUOTES, 'UTF-8') ?></span></td><td class="text-end pe-4"><button class="btn btn-sm btn-outline-info" aria-label="Add"><i class="fas fa-eye"></i></button></td></tr>
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
                    <div class="mb-3"><small class="text-muted d-block">Department</small><strong><?= htmlspecialchars($job['department'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Location</small><strong><?= htmlspecialchars($job['location'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Employment Type</small><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $job['employment_type'] ?? 'Full Time')), ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div class="mb-3"><small class="text-muted d-block">Experience</small><strong><?= htmlspecialchars($job['experience'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Salary</small><strong class="text-success"><?= htmlspecialchars($job['salary'] ?? 'Negotiable', ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Vacancies</small><strong><?= htmlspecialchars($job['vacancies'] ?? 1, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Status</small><span class="badge bg-<?= htmlspecialchars(($job['status'] ?? 'active') === 'active' ? 'success' : 'secondary', ENT_QUOTES, 'UTF-8') ?>-subtle text-<?= htmlspecialchars(($job['status'] ?? 'active') === 'active' ? 'success' : 'secondary', ENT_QUOTES, 'UTF-8') ?> rounded-pill px-3"><?= htmlspecialchars(ucfirst($job['status'] ?? 'Active'), ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div class="mb-3"><small class="text-muted d-block">Posted</small><?= htmlspecialchars(date('d M Y', strtotime($job['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?></div>
                    <div><small class="text-muted d-block">Application Count</small><strong class="text-primary"><?= htmlspecialchars($applicationCount ?? 0, ENT_QUOTES, 'UTF-8') ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
