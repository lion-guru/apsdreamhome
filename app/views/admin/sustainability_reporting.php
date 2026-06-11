<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin">Admin</a></li>
                    <li class="breadcrumb-item active">Sustainability Reporting</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-file-alt me-3 text-success"></i><?= ($page_title ?? 'Sustainability Reporting') ?></h1>
        </div>
    </div>

    <?php $rd = $reporting_data ?? []; $esg = $rd['esg_reports'] ?? []; $compliance = $rd['compliance_status'] ?? []; $metrics = $rd['sustainability_metrics'] ?? []; $stakeholder = $rd['stakeholder_reports'] ?? []; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>ESG Reports (<?= ($esg['environmental_report']['report_period'] ?? 'Current') ?>)</h5></div>
                <div class="card-body aps-cp-card-body">
                    <ul class="nav nav-tabs mb-3" id="esgTabs" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#envReport">Environmental</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#socialReport">Social</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#govReport">Governance</button></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="envReport">
                            <?php $er = $esg['environmental_report'] ?? []; ?>
                            <div class="row g-2"><?php foreach (['carbon_emissions', 'energy_consumption', 'water_usage', 'waste_generation'] as $k): ?><div class="col-6"><div class="border rounded p-2"><small class="text-muted d-block"><?= ucfirst(str_replace('_', ' ', $k)) ?></small><strong><?= ($er[$k] ?? 'N/A') ?></strong></div></div><?php endforeach; ?></div>
                        </div>
                        <div class="tab-pane fade" id="socialReport">
                            <?php $sr = $esg['social_report'] ?? []; ?>
                            <div class="row g-2"><?php foreach (['employee_satisfaction', 'diversity_inclusion', 'community_impact', 'customer_satisfaction'] as $k): ?><div class="col-6"><div class="border rounded p-2"><small class="text-muted d-block"><?= ucfirst(str_replace('_', ' ', $k)) ?></small><strong><?= ($sr[$k] ?? 'N/A') ?></strong></div></div><?php endforeach; ?></div>
                        </div>
                        <div class="tab-pane fade" id="govReport">
                            <?php $gr = $esg['governance_report'] ?? []; ?>
                            <div class="row g-2"><?php foreach (['board_diversity', 'ethical_practices', 'transparency_score', 'stakeholder_engagement'] as $k): ?><div class="col-6"><div class="border rounded p-2"><small class="text-muted d-block"><?= ucfirst(str_replace('_', ' ', $k)) ?></small><strong><?= ($gr[$k] ?? 'N/A') ?></strong></div></div><?php endforeach; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-check-circle me-2 text-success"></i>Compliance Status</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-2"><?php foreach ($compliance as $key => $status): ?><div class="col-md-4"><div class="border rounded p-2 d-flex justify-content-between"><span class="small text-capitalize"><?= str_replace('_', ' ', $key) ?></span><strong class="small text-<?= strpos($status, '100%') !== false || strpos($status, 'Certified') !== false ? 'success' : 'warning' ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></strong></div></div><?php endforeach; ?></div>
                    <?php if (empty($compliance)): ?><p class="text-muted text-center py-3">No compliance data.</p><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-info"></i>Key Metrics</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($metrics as $key => $val): ?><div class="d-flex justify-content-between mb-2 pb-2 border-bottom"><span class="small text-capitalize"><?= str_replace('_', ' ', $key) ?></span><strong><?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?></strong></div><?php endforeach; ?>
                    <?php if (empty($metrics)): ?><p class="text-muted text-center py-3">No metrics.</p><?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-users me-2 text-primary"></i>Stakeholder Reports</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($stakeholder as $group => $data): ?>
                    <h6 class="text-capitalize"><?= htmlspecialchars($group, ENT_QUOTES, 'UTF-8') ?></h6>
                    <?php foreach ($data as $key => $val): ?><div class="d-flex justify-content-between small mb-1"><span class="text-capitalize"><?= str_replace('_', ' ', $key) ?></span><strong><?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?></strong></div><?php endforeach; ?>
                    <hr>
                    <?php endforeach; ?>
                    <?php if (empty($stakeholder)): ?><p class="text-muted text-center py-3">No reports.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
