<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin">Admin</a></li>
                    <li class="breadcrumb-item active">Sustainability Dashboard</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-leaf me-3 text-success"></i><?= ($page_title ?? 'Sustainability Dashboard') ?></h1>
        </div>
    </div>

    <?php $sd = $sustainability_data ?? []; $carbon = $sd['carbon_footprint'] ?? []; $energy = $sd['energy_efficiency'] ?? []; $green = $sd['green_technologies'] ?? []; $goals = $sd['sustainability_goals'] ?? []; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center border-start border-success border-4">
                <div class="card-body aps-cp-card-body"><h3 class="text-success mb-0"><?= ($carbon['total_carbon_footprint'] ?? 'N/A') ?></h3><small class="text-muted">Carbon Footprint</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center border-start border-warning border-4">
                <div class="card-body aps-cp-card-body"><h3 class="text-warning mb-0"><?= ($energy['total_consumption'] ?? 'N/A') ?></h3><small class="text-muted">Energy Consumption</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center border-start border-info border-4">
                <div class="card-body aps-cp-card-body"><h3 class="text-info mb-0"><?= count($green ?? []) ?></h3><small class="text-muted">Green Technologies</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center border-start border-primary border-4">
                <div class="card-body aps-cp-card-body"><h3 class="text-primary mb-0"><?= count($goals ?? []) ?></h3><small class="text-muted">Active Goals</small></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-bullseye me-2 text-primary"></i>Sustainability Goals</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($goals as $key => $goal): if (!is_array($goal)) continue; ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><?= ($goal['target'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h6>
                            <span class="badge bg-<?= (int)($goal['current_progress'] ?? 0) >= 70 ? 'success' : 'warning' ?>"><?= ($goal['current_progress'] ?? '0%') ?></span>
                        </div>
                        <div class="progress mb-2 style-87912"><div class="progress-bar bg-success style-29570"></div></div>
                        <small class="text-muted">Timeline: <?= ($goal['timeline'] ?? '') ?></small>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($goals)): ?><p class="text-muted text-center py-3">No goals defined.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-microchip me-2 text-success"></i>Green Technologies</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php $adopted = $green['adopted_technologies'] ?? $green; if (is_array($adopted)): foreach ($adopted as $key => $tech): if (!is_array($tech)) continue; ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div><strong><?= ($tech['technology'] ?? ucfirst(str_replace('_', ' ', $key))) ?></strong><br><small class="text-muted"><?= ($tech['adoption_date'] ?? '') ?></small></div>
                        <span class="badge bg-success"><?= ($tech['energy_savings'] ?? '') ?></span>
                    </div>
                    <?php endforeach; endif; ?>
                    <?php if (empty($adopted)): ?><p class="text-muted text-center py-3">No green technologies.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
