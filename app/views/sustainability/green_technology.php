<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Green Technology</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-microchip me-3 text-success"></i><?= ($page_title ?? 'Green Technology') ?></h1>
        </div>
    </div>

    <?php $green_tech_data = $green_tech_data ?? []; $adopted = $green_tech_data['adopted_technologies'] ?? []; $timeline = $green_tech_data['implementation_timeline'] ?? []; $cba = $green_tech_data['cost_benefit_analysis'] ?? []; $env_impact = $green_tech_data['environmental_impact'] ?? []; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-check-circle me-2 text-success"></i>Adopted Technologies</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><div class="table-responsive"><table class="table table-responsive"><thead class="table-light"><tr><th>Technology</th><th>Adoption Date</th><th>Energy Savings</th><th>Cost Savings</th></tr></thead><tbody>
                        <?php foreach ($adopted as $ad): ?><tr><td><?= ($ad['technology'] ?? '') ?></td><td><?= ($ad['adoption_date'] ?? '') ?></td><td class="text-success"><?= ($ad['energy_savings'] ?? '') ?></td><td><?= ($ad['cost_savings'] ?? '') ?></td></tr><?php endforeach; ?>
                        <?php if (empty($adopted)): ?><tr><td colspan="4" class="text-center text-muted py-3">No adopted technologies.</td></tr><?php endif; ?>
                    </tbody></table></div></div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-clock me-2 text-info"></i>Implementation Timeline</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <?php foreach ($timeline as $quarter => $items): ?>
                        <div class="col-md-3">
                            <h6 class="text-uppercase small"><?= str_replace('_', ' ', $quarter) ?></h6>
                            <ul class="list-unstyled">
                                <?php foreach ($items as $status => $item): ?>
                                <li class="mb-2"><span class="badge bg-<?= $status === 'completed' ? 'success' : ($status === 'in_progress' ? 'warning' : 'secondary') ?> me-1"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span><small><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></small></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($timeline)): ?><div class="col-12"><p class="text-muted text-center py-3">No timeline data.</p></div><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-calculator me-2 text-primary"></i>Cost-Benefit Analysis</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Investment</span><strong><?= ($cba['investment_required'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Annual Savings</span><strong class="text-success"><?= ($cba['annual_savings'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Payback Period</span><strong><?= ($cba['payback_period'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>5-Year ROI</span><strong class="text-success"><?= ($cba['roi_over_5_years'] ?? 'N/A') ?></strong></div>
                    <hr>
                    <h6>Breakdown</h6>
                    <?php foreach (($cba['breakdown'] ?? []) as $key => $val): ?><div class="d-flex justify-content-between small mb-1"><span><?= ucfirst(str_replace('_', ' ', $key)) ?></span><span><?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?></span></div><?php endforeach; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-globe me-2 text-success"></i>Environmental Impact</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6>Carbon Reduction</h6>
                    <div class="d-flex justify-content-between small mb-2"><span>Current</span><strong class="text-success"><?= ($env_impact['carbon_reduction']['current_reduction'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between small mb-2"><span>Target</span><strong><?= ($env_impact['carbon_reduction']['target_reduction'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between small mb-2"><span>Equivalent</span><small><?= ($env_impact['carbon_reduction']['equivalent_trees'] ?? '') ?></small></div>
                    <hr>
                    <h6>Energy Conservation</h6>
                    <small class="text-muted"><?= ($env_impact['energy_conservation']['electricity_saved'] ?? '') ?> saved</small>
                </div>
            </div>
        </div>
    </div>
</div>
