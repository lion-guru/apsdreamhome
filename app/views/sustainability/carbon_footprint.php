<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Carbon Footprint</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-leaf me-3 text-success"></i><?= ($page_title ?? 'Carbon Footprint') ?></h1>
        </div>
    </div>

    <?php $carbon_data = $carbon_data ?? []; $current_footprint = $carbon_data['current_footprint'] ?? []; $reduction_strategies = $carbon_data['reduction_strategies'] ?? []; $offset_programs = $carbon_data['offset_programs'] ?? []; $certifications = $carbon_data['sustainability_certifications'] ?? []; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body"><h3 class="text-danger mb-0"><?= ($current_footprint['total_carbon_footprint'] ?? 'N/A') ?></h3><small class="text-muted">Total Carbon Footprint</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body"><h3 class="text-warning mb-0"><?= ($current_footprint['per_user_footprint'] ?? 'N/A') ?></h3><small class="text-muted">Per User</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body"><h3 class="text-info mb-0"><?= ($current_footprint['data_center_emissions'] ?? 'N/A') ?></h3><small class="text-muted">Data Center</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body"><h3 class="text-secondary mb-0"><?= ($current_footprint['user_activity_emissions'] ?? 'N/A') ?></h3><small class="text-muted">User Activity</small></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-danger"></i>Emission Breakdown</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php $breakdown = $current_footprint['breakdown'] ?? []; ?>
                    <?php if (!empty($breakdown)): ?>
                    <div class="table-responsive"><div class="table-responsive"><table class="table table-responsive"><thead class="table-light"><tr><th>Source</th><th>Emissions</th><th>Percentage</th></tr></thead><tbody>
                        <?php foreach ($breakdown as $key => $item): ?>
                        <tr><td><?= ucfirst(str_replace('_', ' ', $key)) ?></td><td><?= ($item['emissions'] ?? '') ?></td><td><?= ($item['percentage'] ?? '') ?></td></tr>
                        <?php endforeach; ?>
                    </tbody></table></div></div>
                    <?php else: ?><p class="text-muted text-center py-3">No breakdown data available.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-certificate me-2 text-success"></i>Certifications</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($certifications as $cert): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div><strong><?= ($cert['certification'] ?? '') ?></strong><br><small class="text-muted">Status: <?= ($cert['status'] ?? '') ?></small></div>
                        <span class="badge bg-<?= (($cert['status'] ?? '') === 'Achieved') ? 'success' : (($cert['status'] ?? '') === 'In Progress' ? 'warning' : 'secondary') ?>"><?= ($cert['status'] ?? '') ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($certifications)): ?><p class="text-muted text-center py-3">No certifications.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-arrow-down me-2 text-success"></i>Reduction Strategies</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($reduction_strategies as $key => $strategy): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6><?= ucfirst(str_replace('_', ' ', $key)) ?></h6>
                        <p class="small text-muted mb-1"><?= ($strategy['strategy'] ?? '') ?></p>
                        <div class="d-flex justify-content-between small"><span>Reduction: <strong><?= ($strategy['potential_reduction'] ?? '') ?></strong></span><span>Cost: <?= ($strategy['implementation_cost'] ?? '') ?></span></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($reduction_strategies)): ?><p class="text-muted text-center py-3">No strategies available.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-tree me-2 text-success"></i>Offset Programs</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($offset_programs as $key => $program): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6><?= ($program['program'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h6>
                        <div class="d-flex justify-content-between small"><span>Offset: <strong><?= ($program['carbon_offset'] ?? '') ?></strong></span><span>Cost: <?= ($program['cost'] ?? '') ?></span></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($offset_programs)): ?><p class="text-muted text-center py-3">No offset programs.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
