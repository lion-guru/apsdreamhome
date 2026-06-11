<?php $pageTitle = 'Application Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-file-user me-2"></i>Application Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/jobs">Jobs</a></li>
                    <li class="breadcrumb-item"><a href="/admin/jobs/applications">Applications</a></li>
                    <li class="breadcrumb-item active">#<?= $application['id'] ?? 0 ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="/admin/jobs/applications" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($application)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-file-user fa-4x d-block mb-3"></i><h5>Application not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body py-4">
                    <div class="avatar-lg mx-auto mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:32px"><?= strtoupper(substr($application['name'], 0, 1)) ?></div>
                    <h5 class="mb-1"><?= $application['name'] ?></h5>
                    <p class="text-muted mb-2"><?= $application['email'] ?></p>
                    <span class="badge bg-<?= ($application['status'] ?? 'new') === 'new' ? 'primary' : (($application['status'] ?? 'new') === 'shortlisted' ? 'success' : (($application['status'] ?? 'new') === 'rejected' ? 'danger' : 'secondary')) ?>-subtle text-<?= ($application['status'] ?? 'new') === 'new' ? 'primary' : (($application['status'] ?? 'new') === 'shortlisted' ? 'success' : (($application['status'] ?? 'new') === 'rejected' ? 'danger' : 'secondary')) ?> rounded-pill px-3"><?= ucfirst($application['status'] ?? 'New') ?></span>
                </div>
            </div>
            <?php if ($application['resume_url']): ?>
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body text-center">
                    <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                    <h6>Resume</h6>
                    <a href="<?= $application['resume_url'] ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-download me-1"></i>Download</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Application Information</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Applied For</div><div class="col-sm-8"><strong><?= $application['job_title'] ?? '-' ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Phone</div><div class="col-sm-8"><?= $application['phone'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Email</div><div class="col-sm-8"><?= $application['email'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Experience</div><div class="col-sm-8"><?= $application['experience'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Qualification</div><div class="col-sm-8"><?= $application['qualification'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Current CTC</div><div class="col-sm-8"><?= $application['current_ctc'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Expected CTC</div><div class="col-sm-8"><?= $application['expected_ctc'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Notice Period</div><div class="col-sm-8"><?= $application['notice_period'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Applied On</div><div class="col-sm-8"><?= date('d M Y H:i', strtotime($application['created_at'] ?? 'now')) ?></div></div>
                    <div class="row"><div class="col-sm-4 text-muted">Cover Letter</div><div class="col-sm-8"><?= nl2br($application['cover_letter'] ?? '-') ?></div></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
