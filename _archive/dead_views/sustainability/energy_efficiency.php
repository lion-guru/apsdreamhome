<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Energy Efficiency</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-bolt me-3 text-warning"></i><?= ($page_title ?? 'Energy Efficiency') ?></h1>
        </div>
    </div>

    <?php $energy_data = $energy_data ?? []; $consumption = $energy_data['current_consumption'] ?? []; $improvements = $energy_data['efficiency_improvements'] ?? []; $renewable = $energy_data['renewable_energy'] ?? []; $recommendations = $energy_data['optimization_recommendations'] ?? []; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center"><div class="card-body aps-cp-card-body"><h3 class="text-warning mb-0"><?= ($consumption['total_consumption'] ?? 'N/A') ?></h3><small class="text-muted">Total Consumption</small></div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center"><div class="card-body aps-cp-card-body"><h3 class="text-info mb-0"><?= ($consumption['per_user_consumption'] ?? 'N/A') ?></h3><small class="text-muted">Per User</small></div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center"><div class="card-body aps-cp-card-body"><h3 class="text-primary mb-0"><?= ($consumption['data_center_consumption'] ?? 'N/A') ?></h3><small class="text-muted">Data Center</small></div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center"><div class="card-body aps-cp-card-body"><h3 class="text-secondary mb-0"><?= ($consumption['office_consumption'] ?? 'N/A') ?></h3><small class="text-muted">Office</small></div></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-chart-line me-2 text-success"></i>Efficiency Improvements</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($improvements as $key => $imp): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6><?= ($imp['improvement'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h6>
                        <div class="row text-center mt-2">
                            <div class="col-4"><small class="text-muted d-block">Energy Saved</small><strong class="text-success"><?= ($imp['energy_saved'] ?? '') ?></strong></div>
                            <div class="col-4"><small class="text-muted d-block">Cost Savings</small><strong><?= ($imp['cost_savings'] ?? '') ?></strong></div>
                            <div class="col-4"><small class="text-muted d-block">Timeline</small><strong><?= ($imp['implementation_time'] ?? '') ?></strong></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($improvements)): ?><p class="text-muted text-center py-3">No improvement data.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-solar-panel me-2 text-warning"></i>Renewable Energy Sources</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($renewable as $key => $re): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6><?= ucfirst(str_replace('_', ' ', $key)) ?></h6>
                        <div class="d-flex justify-content-between small"><span>Capacity: <strong><?= ($re['capacity'] ?? '') ?></strong></span><span>Generation: <?= ($re['generation'] ?? '') ?></span></div>
                        <small class="text-muted">Coverage: <?= ($re['coverage'] ?? '') ?></small>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($renewable)): ?><p class="text-muted text-center py-3">No renewable sources.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-list-check me-2 text-info"></i>Recommendations</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-4">
                        <?php foreach (['immediate_actions', 'short_term_goals', 'long_term_strategies'] as $category): $items = $recommendations[$category] ?? []; ?>
                        <div class="col-md-4">
                            <h6 class="text-<?= $category === 'immediate_actions' ? 'success' : ($category === 'short_term_goals' ? 'warning' : 'info') ?>"><?= ucfirst(str_replace('_', ' ', $category)) ?></h6>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($items as $item): ?><li class="list-group-item px-0 small"><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
                                <?php if (empty($items)): ?><li class="list-group-item px-0 text-muted small">No items.</li><?php endif; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
