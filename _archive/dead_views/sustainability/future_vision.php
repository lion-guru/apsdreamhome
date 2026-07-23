<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Future Vision</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-eye me-3 text-primary"></i><?= ($page_title ?? 'Future Vision') ?></h1>
        </div>
    </div>

    <?php $vd = $vision_data ?? []; $goals = $vd['2030_goals'] ?? []; $innovations = $vd['technology_innovations'] ?? []; $transform = $vd['ecosystem_transformation'] ?? []; ?>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-bullseye me-2"></i>2030 Goals</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <?php foreach ($goals as $key => $goal): ?>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center h-100">
                                <h6 class="text-primary"><?= ucfirst(str_replace('_', ' ', $key)) ?></h6>
                                <small><?= htmlspecialchars($goal, ENT_QUOTES, 'UTF-8') ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($goals)): ?><div class="col-12"><p class="text-muted text-center">No goals data.</p></div><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-microchip me-2"></i>Technology Innovations</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($innovations as $key => $innovation): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6><?= ucfirst(str_replace('_', ' ', $key)) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($innovation, ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($innovations)): ?><p class="text-muted text-center py-3">No data.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-globe me-2"></i>Ecosystem Transformation</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($transform as $key => $item): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6><?= ucfirst(str_replace('_', ' ', $key)) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($transform)): ?><p class="text-muted text-center py-3">No data.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
