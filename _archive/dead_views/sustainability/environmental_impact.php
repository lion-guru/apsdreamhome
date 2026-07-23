<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Environmental Impact</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-globe-asia me-3 text-success"></i><?= ($page_title ?? 'Environmental Impact') ?></h1>
        </div>
    </div>

    <?php $id = $impact_data ?? []; $property_impact = $id['property_impact'] ?? []; $construction_impact = $id['construction_impact'] ?? []; $operational_impact = $id['operational_impact'] ?? []; $mitigation = $id['mitigation_strategies'] ?? []; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center"><div class="card-body aps-cp-card-body"><h3 class="text-danger mb-0"><?= ($property_impact['carbon_footprint'] ?? 'N/A') ?></h3><small class="text-muted">Carbon Footprint</small></div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center"><div class="card-body aps-cp-card-body"><h3 class="text-warning mb-0"><?= ($property_impact['energy_consumption'] ?? 'N/A') ?></h3><small class="text-muted">Energy Consumption</small></div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center"><div class="card-body aps-cp-card-body"><h3 class="text-info mb-0"><?= ($property_impact['water_usage'] ?? 'N/A') ?></h3><small class="text-muted">Water Usage</small></div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center"><div class="card-body aps-cp-card-body"><h3 class="text-secondary mb-0"><?= ($property_impact['waste_generation'] ?? 'N/A') ?></h3><small class="text-muted">Waste Generation</small></div></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-hard-hat me-2 text-warning"></i>Construction Impact</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6>Material Sourcing</h6>
                    <?php $sourcing = $construction_impact['material_sourcing'] ?? []; ?>
                    <div class="d-flex justify-content-between small mb-1"><span>Sustainable Materials</span><strong><?= ($sourcing['sustainable_materials'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between small mb-1"><span>Local Materials</span><strong><?= ($sourcing['local_materials'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between small mb-3"><span>Recycled Content</span><strong><?= ($sourcing['recycled_content'] ?? 'N/A') ?></strong></div>
                    <h6>Construction Process</h6>
                    <?php $process = $construction_impact['construction_process'] ?? []; ?>
                    <div class="d-flex justify-content-between small mb-1"><span>Waste Diversion</span><strong class="text-success"><?= ($process['waste_diversion'] ?? 'N/A') ?></strong></div>
                    <small class="text-muted"><?= ($process['energy_efficient_equipment'] ?? '') ?></small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-info"></i>Operational Impact</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php $ep = $operational_impact['energy_performance'] ?? []; ?>
                    <h6>Energy Performance</h6>
                    <div class="d-flex justify-content-between small mb-1"><span>Intensity</span><strong><?= ($ep['building_energy_intensity'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between small mb-1"><span>Renewable %</span><strong><?= ($ep['renewable_energy_percentage'] ?? '0%') ?></strong></div>
                    <div class="d-flex justify-content-between small mb-3"><span>Energy Star</span><strong><?= ($ep['energy_star_score'] ?? 'N/A') ?></strong></div>
                    <?php $wp = $operational_impact['water_performance'] ?? []; ?>
                    <h6>Water Performance</h6>
                    <div class="d-flex justify-content-between small mb-1"><span>Intensity</span><strong><?= ($wp['water_use_intensity'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between small mb-3"><span>Efficiency</span><strong><?= ($wp['water_efficiency'] ?? 'N/A') ?></strong></div>
                    <?php $ieq = $operational_impact['indoor_environmental_quality'] ?? []; ?>
                    <h6>Indoor Quality</h6>
                    <div class="d-flex justify-content-between small"><span>Air Quality</span><strong><?= ($ieq['air_quality'] ?? 'N/A') ?></strong></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-shield-alt me-2 text-success"></i>Mitigation Strategies</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($mitigation as $category => $items): ?>
                    <h6 class="text-capitalize mt-3"><?= str_replace('_', ' ', $category) ?></h6>
                    <ul class="list-unstyled"><?php foreach ($items as $item): ?><li class="mb-1 small"><i class="fas fa-check-circle text-success me-1"></i><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                    <?php endforeach; ?>
                    <?php if (empty($mitigation)): ?><p class="text-muted text-center py-3">No strategies.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
