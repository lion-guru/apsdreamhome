<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Innovation Lab</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-flask me-3 text-primary"></i><?= ($page_title ?? 'Innovation Lab') ?></h1>
        </div>
    </div>

    <?php $projects = $innovation_projects ?? []; ?>

    <div class="row g-4">
        <?php foreach ($projects as $key => $proj): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <i class="fas fa-<?= $key === 'ai_energy_optimization' ? 'brain' : ($key === 'carbon_capture_technology' ? 'leaf' : 'recycle') ?> fa-3x text-primary"></i>
                        <span class="badge bg-<?= ($proj['status'] ?? '') === 'In Development' ? 'warning' : (($proj['status'] ?? '') === 'Research Phase' ? 'info' : 'success') ?>"><?= ($proj['status'] ?? '') ?></span>
                    </div>
                    <h5><?= ($proj['project'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                    <p class="small text-muted">Timeline: <?= ($proj['timeline'] ?? 'N/A') ?></p>
                    <p class="small text-muted mb-2">Impact: <strong><?= ($proj['potential_impact'] ?? 'N/A') ?></strong></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-users me-1"></i><?= ($proj['researchers'] ?? 0) ?> researchers</small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($projects)): ?><div class="col-12"><div class="alert alert-info">No innovation projects.</div></div><?php endif; ?>
    </div>
</div>
