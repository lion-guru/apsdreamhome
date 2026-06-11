<?php
$page_title = $page_title ?? 'Our Projects - APS Dream Home';
$page_description = $page_description ?? 'Explore our ongoing and completed real estate projects';
$projects = $projects ?? [];
$base = $base ?? BASE_URL;
?>

<section class="py-5 bg-gradient-warning text-white position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #232526 0%, #414345 50%, #667eea 100%);"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><i class="fas fa-hard-hat me-3"></i>Our Projects</h1>
                <p class="lead mb-0">Discover our premium residential and commercial developments across Uttar Pradesh</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/properties" class="btn btn-light">
                    <i class="fas fa-building me-1"></i> View Properties
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if (!empty($projects)): ?>
        <div class="row g-4">
            <?php foreach ($projects as $project): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 project-card">
                    <div class="position-relative overflow-hidden" style="height: 220px;">
                        <?php if (!empty($project->image_path ?? $project['image_path'] ?? '')): ?>
                        <img src="<?= htmlspecialchars($project->image_path ?? $project['image_path'] ?? '') ?>" class="card-img-top h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($project->name ?? $project['name'] ?? 'Project') ?>" loading="lazy">
                        <?php else: ?>
                        <div class="bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center h-100">
                            <i class="fas fa-building fa-4x text-secondary" style="opacity:0.3;"></i>
                        </div>
                        <?php endif; ?>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-<?= (($project->status ?? $project['status'] ?? '') === 'Completed') ? 'success' : 'warning' ?> fs-6">
                                <?= htmlspecialchars($project->status ?? $project['status'] ?? 'N/A') ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($project->name ?? $project['name'] ?? 'Untitled') ?></h5>
                        <p class="card-text small text-muted">
                            <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($project->location ?? $project['location'] ?? 'N/A') ?>
                        </p>
                        <p class="card-text small"><?= htmlspecialchars($project->description ?? $project['description'] ?? '') ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-info"><?= htmlspecialchars(ucfirst($project->type ?? $project['type'] ?? 'N/A')) ?></span>
                            <?php if (!empty($project->completion ?? $project['completion'] ?? '')): ?>
                            <small class="text-muted">Completion: <?= htmlspecialchars($project->completion ?? $project['completion']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/project/<?= htmlspecialchars($project->id ?? $project['id'] ?? 0) ?>" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-info-circle me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-tools fa-4x text-muted" style="opacity:0.5;"></i>
            </div>
            <h3 class="text-muted">Projects Coming Soon</h3>
            <p class="text-muted mb-4">We're developing exciting new projects. Stay tuned!</p>
            <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/properties" class="btn btn-primary btn-lg">
                <i class="fas fa-building me-2"></i> Browse Available Properties
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.project-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}
</style>
