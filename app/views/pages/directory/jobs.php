<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services">Services Directory</a></li>
            <li class="breadcrumb-item active">Jobs & Employment</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="mb-2"><i class="fas fa-briefcase text-success me-2"></i>Real Estate Jobs & Employment</h1>
            <p class="text-muted">Looking for work? Hiring for your project? Post or find real estate jobs here.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a href="<?= BASE_URL ?>/services/jobs/post" class="btn btn-success"><i class="fas fa-plus me-1"></i>Post a Job</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" class="row g-2">
                <div class="col-auto">
                    <select name="seeking" class="form-control" onchange="this.form.submit()">
                        <option value="-1">All Types</option>
                        <option value="1" <?= $seek === 1 ? 'selected' : '' ?>>Seeking Work</option>
                        <option value="0" <?= $seek === 0 ? 'selected' : '' ?>>Hiring</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($jobCategories as $jc): ?>
                            <option value="<?= $jc ?>" <?= $category === $jc ? 'selected' : '' ?>><?= $jc ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="type" class="form-control" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="full_time" <?= $type === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                        <option value="part_time" <?= $type === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                        <option value="contract" <?= $type === 'contract' ? 'selected' : '' ?>>Contract</option>
                        <option value="gig" <?= $type === 'gig' ? 'selected' : '' ?>>Gig</option>
                        <option value="internship" <?= $type === 'internship' ? 'selected' : '' ?>>Internship</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($jobs['items'])): ?>
        <div class="text-center py-5">
            <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
            <h4>No jobs found</h4>
            <p class="text-muted">Try different filters or <a href="<?= BASE_URL ?>/services/jobs/post">post a new job</a></p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($jobs['items'] as $j): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($j['title']) ?></h5>
                                <span class="badge bg-<?= $j['is_seeking'] ? 'info' : 'success' ?>"><?= $j['is_seeking'] ? 'Seeking' : 'Hiring' ?></span>
                            </div>
                            <?php if ($j['category']): ?><p class="text-muted small mb-1"><i class="fas fa-tag me-1"></i><?= htmlspecialchars($j['category']) ?></p><?php endif; ?>
                            <?php if ($j['business_name']): ?><p class="text-muted small mb-1"><i class="fas fa-building me-1"></i><?= htmlspecialchars($j['business_name']) ?></p><?php endif; ?>
                            <?php if ($j['location']): ?><p class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($j['location']) ?></p><?php endif; ?>
                            <?php if ($j['salary_range']): ?><p class="mb-2"><strong>💰 <?= htmlspecialchars($j['salary_range']) ?></strong></p><?php endif; ?>
                            <?php if ($j['description']): ?><p class="small"><?= nl2br(htmlspecialchars(mb_substr($j['description'], 0, 200))) ?></p><?php endif; ?>
                            <p class="small text-muted mb-0"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($j['contact_phone'] ?? $j['listing_phone'] ?? '') ?></p>
                            <small class="text-muted">Posted <?= date('d M Y', strtotime($j['created_at'])) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($jobs['pages'] > 1): ?>
            <nav><ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $jobs['pages']; $p++): ?>
                    <li class="page-item <?= $p === $jobs['page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&seeking=<?= $seek ?>&category=<?= urlencode($category) ?>&type=<?= $type ?>"><?= $p ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
