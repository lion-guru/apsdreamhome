<?php $pageTitle = 'Careers'; ?>
<?php $jobs = $jobs ?? []; $categories = $categories ?? []; ?>
<div class="container py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Home</a></li><li class="breadcrumb-item active">Careers</li></ol></nav>
    <div class="text-center mb-5">
        <h2 class="fw-bold"><i class="fas fa-briefcase me-2 text-primary"></i>Join Our Team</h2>
        <p class="text-muted">Explore career opportunities at APS Dream Home</p>
    </div>
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="get" action="<?= BASE_URL ?>careers">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label small">Department</label>
                            <select class="form-select form-select-sm" name="category">
                                <option value="">All Departments</option>
                                <?php foreach ($categories as $k => $v): ?>
                                <option value="<?= $k ?>" <?= (($_GET['category'] ?? '') === $k) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Location</label>
                            <input type="text" class="form-control form-control-sm" name="location" placeholder="City" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Type</label>
                            <select class="form-select form-select-sm" name="type">
                                <option value="">All Types</option>
                                <option value="full-time" <?= (($_GET['type'] ?? '') === 'full-time') ? 'selected' : '' ?>>Full Time</option>
                                <option value="part-time" <?= (($_GET['type'] ?? '') === 'part-time') ? 'selected' : '' ?>>Part Time</option>
                                <option value="contract" <?= (($_GET['type'] ?? '') === 'contract') ? 'selected' : '' ?>>Contract</option>
                                <option value="internship" <?= (($_GET['type'] ?? '') === 'internship') ? 'selected' : '' ?>>Internship</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Search</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <?php if (empty($jobs)): ?>
            <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-briefcase fa-3x text-muted mb-3"></i><h6 class="text-muted">No positions currently open</h6><p class="text-muted small">Check back later for new opportunities.</p></div></div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($jobs as $j): ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary"><?= htmlspecialchars(ucfirst($j['type'] ?? 'Full Time')) ?></span>
                                <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($j['location'] ?? 'N/A') ?></small>
                            </div>
                            <h5 class="card-title"><?= htmlspecialchars($j['title'] ?? $j['position'] ?? '-') ?></h5>
                            <p class="card-text small text-muted"><?= htmlspecialchars(mb_substr($j['description'] ?? '', 0, 120)) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="far fa-clock me-1"></i>Posted: <?= htmlspecialchars($j['created_at'] ?? 'N/A') ?></small>
                                <a href="<?= BASE_URL ?>careers/details/<?= $j['id'] ?? 0 ?>" class="btn btn-outline-primary btn-sm">Apply Now <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
