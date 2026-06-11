<?php $pageTitle = $page_title ?? 'Edge Computing Cost Analysis'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-coins me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php $cd = $cost_data ?? []; ?>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Infrastructure Costs</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($cd['infrastructure_costs'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Operational Benefits</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($cd['operational_benefits'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td class="text-success"><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>ROI Timeline</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($cd['roi_timeline'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td class="fw-bold"><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
