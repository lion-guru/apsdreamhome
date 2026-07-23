<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Sustainable Properties</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-building me-3 text-success"></i><?= ($page_title ?? 'Sustainable Properties') ?></h1>
        </div>
    </div>

    <?php $sf = $sustainable_features ?? []; $efficient = $sf['energy_efficient_properties'] ?? []; $standards = $sf['green_building_standards'] ?? []; $ratings = $sf['sustainability_ratings'] ?? []; $eco = $sf['eco_friendly_features'] ?? []; ?>

    <div class="row g-4 mb-4">
        <?php foreach ($efficient as $key => $prop): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center h-100">
                <div class="card-body aps-cp-card-body">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5><?= ucfirst(str_replace('_', ' ', $key)) ?></h5>
                    <h3 class="text-primary"><?= ($prop['count'] ?? 0) ?></h3>
                    <small class="text-muted d-block"><?= ($prop['energy_savings'] ?? '') ?> energy savings</small>
                    <small class="text-muted d-block"><?= ($prop['water_savings'] ?? '') ?> water savings</small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($efficient)): ?><div class="col-12"><div class="alert alert-info">No energy efficient property data.</div></div><?php endif; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-certificate me-2 text-primary"></i>Green Building Standards</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($standards as $key => $std): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6><?= ($std['standard'] ?? $key) ?></h6>
                        <div class="mb-2"><?php foreach (($std['certification_levels'] ?? []) as $level): ?><span class="badge bg-secondary me-1"><?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>
                        <small class="text-muted">Focus: <?= implode(', ', $std['focus_areas'] ?? []) ?></small>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($standards)): ?><p class="text-muted text-center py-3">No standards data.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-star me-2 text-warning"></i>Sustainability Ratings</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($ratings as $key => $rating): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span><?= ucfirst(str_replace('_', ' ', $key)) ?></span>
                        <strong class="text-success"><?= htmlspecialchars($rating, ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($ratings)): ?><p class="text-muted text-center py-3">No ratings data.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-leaf me-2 text-success"></i>Eco-Friendly Features</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-4">
                        <?php foreach ($eco as $category => $features): ?>
                        <div class="col-md-4">
                            <h6 class="text-capitalize"><?= str_replace('_', ' ', $category) ?></h6>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($features as $feature => $desc): ?>
                                <li class="list-group-item px-0"><strong><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></small></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($eco)): ?><div class="col-12"><p class="text-muted text-center py-3">No eco-friendly features data.</p></div><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
