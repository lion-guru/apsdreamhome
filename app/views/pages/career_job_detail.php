<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Careers', 'url' => BASE_URL . '/careers'], ['title' => 'Job Details', 'url' => '']];
$job = $job ?? [];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold text-primary mb-2"><?= htmlspecialchars($job['title'] ?? 'Job Opening') ?></h1>
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <?php if (isset($crumb['url']) && $crumb['url']): ?>
                                <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active" aria-current="page"><?= $crumb['title'] ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <?php if (!empty($job['department'])): ?>
                                <span class="badge bg-primary"><?= htmlspecialchars($job['department'] ?? '') ?></span>
                            <?php endif; ?>
                            <?php if (!empty($job['location'])): ?>
                                <span class="badge bg-info"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($job['location'] ?? '') ?></span>
                            <?php endif; ?>
                            <?php if (!empty($job['experience'])): ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($job['experience'] ?? '') ?></span>
                            <?php endif; ?>
                            <?php if (!empty($job['salary'])): ?>
                                <span class="badge bg-success"><i class="fas fa-rupee-sign me-1"></i><?= htmlspecialchars($job['salary'] ?? '') ?></span>
                            <?php endif; ?>
                            <?php if (!empty($job['job_type'])): ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($job['job_type'] ?? '') ?></span>
                            <?php endif; ?>
                        </div>

                        <h5 class="fw-bold mb-3">Job Description</h5>
                        <div class="text-muted mb-4 style-79072">
                            <?= nl2br(htmlspecialchars($job['description'] ?? 'No description available.')) ?>
                        </div>

                        <?php if (!empty($job['requirements'])): ?>
                            <h5 class="fw-bold mb-3">Requirements</h5>
                            <div class="text-muted mb-4 style-79072">
                                <?= nl2br(htmlspecialchars($job['requirements'] ?? '')) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($job['benefits'])): ?>
                            <h5 class="fw-bold mb-3">Benefits</h5>
                            <div class="text-muted mb-4 style-79072">
                                <?= nl2br(htmlspecialchars($job['benefits'] ?? '')) ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <a href="<?= BASE_URL ?>/careers/apply?job_id=<?= $job['id'] ?? '' ?>" class="btn btn-primary rounded-pill px-5 py-2">
                                <i class="fas fa-paper-plane me-2"></i> Apply Now
                            </a>
                            <a href="<?= BASE_URL ?>/careers" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                                <i class="fas fa-arrow-left me-1"></i> All Openings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
