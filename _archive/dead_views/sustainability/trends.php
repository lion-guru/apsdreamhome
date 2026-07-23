<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Trends</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-chart-line me-3 text-primary"></i><?= ($page_title ?? 'Trends') ?></h1>
        </div>
    </div>

    <?php $td = $trends_data ?? []; $emerging = $td['emerging_trends'] ?? []; $insights = $td['market_insights'] ?? []; ?>

    <div class="row g-4 mb-4">
        <?php foreach ($emerging as $key => $trend): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5><?= ($trend['trend'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                        <span class="badge bg-<?= (($trend['growth_potential'] ?? '') === 'Very High' || ($trend['growth_potential'] ?? '') === 'High') ? 'success' : 'warning' ?>"><?= ($trend['growth_potential'] ?? '') ?></span>
                    </div>
                    <p class="small text-muted"><?= ($trend['description'] ?? '') ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Adoption: <?= ($trend['adoption_rate'] ?? '0%') ?></small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($emerging)): ?><div class="col-12"><div class="alert alert-info">No trend data available.</div></div><?php endif; ?>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-lightbulb me-2 text-warning"></i>Market Insights</h5></div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-3">
                <?php foreach ($insights as $key => $val): ?>
                <div class="col-md-3">
                    <div class="border rounded p-3 text-center h-100">
                        <strong class="d-block text-<?= $key === 'regulatory_pressure' ? 'danger' : 'success' ?>"><?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?></strong>
                        <small class="text-muted text-capitalize"><?= str_replace('_', ' ', $key) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($insights)): ?><div class="col-12"><p class="text-muted text-center">No insights available.</p></div><?php endif; ?>
            </div>
        </div>
    </div>
</div>
