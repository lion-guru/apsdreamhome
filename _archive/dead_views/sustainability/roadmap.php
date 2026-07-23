<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Roadmap</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-road me-3 text-success"></i><?= ($page_title ?? 'Sustainability Roadmap') ?></h1>
        </div>
    </div>

    <?php $rd = $roadmap_data ?? []; ?>

    <div class="row g-4">
        <?php foreach ($rd as $year => $milestones): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-<?= $year === '2024' ? 'secondary' : ($year === '2025' ? 'success' : 'primary') ?> text-white">
                    <h4 class="mb-0"><?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?></h4>
                </div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($milestones as $quarter => $milestone): ?>
                        <li class="list-group-item px-0">
                            <span class="badge bg-light text-dark me-2"><?= strtoupper($quarter) ?></span>
                            <small><?= htmlspecialchars($milestone, ENT_QUOTES, 'UTF-8') ?></small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($rd)): ?><div class="col-12"><div class="alert alert-info">No roadmap data available.</div></div><?php endif; ?>
    </div>
</div>
