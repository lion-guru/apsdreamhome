<?php $pageTitle = $page_title ?? 'Smart Home Market Insights'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-chart-bar me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php $ins = $insights ?? []; ?>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-globe me-2"></i>Market Growth</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($ins['market_growth'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) ?></th><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Popular Devices</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach (($ins['popular_devices'] ?? []) as $dev => $pct): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= htmlspecialchars($dev) ?></span>
                            <span class="badge bg-info"><?= htmlspecialchars($pct, ENT_QUOTES, 'UTF-8') ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Adoption Trends</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach (($ins['adoption_trends'] ?? []) as $k => $v): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></span>
                            <span class="badge bg-primary"><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-coins me-2"></i>Cost Benefits</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach (($ins['cost_benefits'] ?? []) as $k => $v): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></span>
                            <span class="text-success fw-bold"><?= htmlspecialchars($v) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
