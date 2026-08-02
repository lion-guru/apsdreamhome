<?php $pageTitle = $page_title ?? 'Edge Computing Industry Impact'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-globe me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php $id = $impact_data ?? []; ?>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Real Estate Sector</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-danger">Challenges</h6>
                    <ul class="small"><?php foreach (($id['real_estate_sector']['current_challenges'] ?? []) as $c): ?><li><?= htmlspecialchars($c) ?></li><?php endforeach; ?></ul>
                    <h6 class="text-success">Solutions</h6>
                    <ul class="small"><?php foreach (($id['real_estate_sector']['edge_solutions'] ?? []) as $s): ?><li><?= htmlspecialchars($s) ?></li><?php endforeach; ?></ul>
                    <h6 class="text-primary">Impact</h6>
                    <ul class="small mb-0"><?php foreach (($id['real_estate_sector']['business_impact'] ?? []) as $i): ?><li><?= htmlspecialchars($i) ?></li><?php endforeach; ?></ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-microchip me-2"></i>Technology Adoption</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($id['technology_adoption'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Economic Impact</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($id['economic_impact'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
