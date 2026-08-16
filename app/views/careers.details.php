<?php $pageTitle = 'Job Details'; ?>
<?php $job = $job ?? null; ?>
<div class="container py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Home</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>careers">Careers</a></li><li class="breadcrumb-item active">Job Details</li></ol></nav>
    <?php if (!$job): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i><h6 class="text-muted">Job posting not found</h6><a href="<?= BASE_URL ?>careers" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Browse Jobs</a></div></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1"><?= htmlspecialchars($job['title'] ?? $job['position'] ?? 'Job Title') ?></h4>
                            <p class="text-muted mb-0"><i class="fas fa-building me-1"></i>APS Dream Home &middot; <i class="fas fa-map-marker-alt ms-2 me-1"></i><?= htmlspecialchars($job['location'] ?? 'N/A') ?></p>
                        </div>
                        <span class="badge bg-primary fs-6"><?= htmlspecialchars(ucfirst($job['type'] ?? 'Full Time')) ?></span>
                    </div>
                    <hr>
                    <h6>Job Description</h6>
                    <p><?= nl2br(htmlspecialchars($job['description'] ?? 'No description provided.')) ?></p>
                    <h6>Requirements</h6>
                    <p><?= nl2br(htmlspecialchars($job['requirements'] ?? 'N/A')) ?></p>
                    <?php if (!empty($job['responsibilities'])): ?>
                    <h6>Responsibilities</h6>
                    <p><?= nl2br(htmlspecialchars($job['responsibilities'] ?? '')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Job Info</h6></div>
                <div class="card-body aps-cp-card-body">
                    <table class="table table-sm">
                        <tr><th>Department</th><td><?= htmlspecialchars(ucfirst($job['department'] ?? $job['category'] ?? 'General')) ?></td></tr>
                        <tr><th>Type</th><td><?= htmlspecialchars(ucfirst($job['type'] ?? 'Full Time')) ?></td></tr>
                        <tr><th>Location</th><td><?= htmlspecialchars($job['location'] ?? 'N/A') ?></td></tr>
                        <tr><th>Salary</th><td><?= htmlspecialchars($job['salary'] ?? $job['salary_range'] ?? 'Negotiable') ?></td></tr>
                        <tr><th>Posted</th><td><?= htmlspecialchars($job['created_at'] ?? 'N/A') ?></td></tr>
                        <tr><th>Closing</th><td><?= htmlspecialchars($job['closing_date'] ?? $job['deadline'] ?? 'Open') ?></td></tr>
                    </table>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Apply Now</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($job['application_url'])): ?>
                    <a href="<?= htmlspecialchars($job['application_url'] ?? '') ?>" target="_blank" class="btn btn-primary w-100"><i class="fas fa-external-link-alt me-1"></i>Apply Externally</a>
                    <?php else: ?>
                    <form method="post" action="<?= BASE_URL ?>careers/apply/<?= $job['id'] ?? 0 ?>" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2"><input type="text" class="form-control form-control-sm" name="name" placeholder="Your Name" required></div>
                        <div class="mb-2"><input type="email" class="form-control form-control-sm" name="email" placeholder="Your Email" required></div>
                        <div class="mb-2"><input type="tel" class="form-control form-control-sm" name="phone" placeholder="Phone Number" required></div>
                        <div class="mb-2"><input type="file" class="form-control form-control-sm" name="resume" accept=".pdf,.doc,.docx" required></div>
                        <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="fas fa-paper-plane me-1"></i>Submit Application</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
