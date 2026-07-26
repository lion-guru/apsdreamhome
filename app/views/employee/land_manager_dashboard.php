<?php $pageTitle = 'Land Manager Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee/dashboard">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">Land Manager Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-tree me-2"></i>Land Manager Dashboard</h4>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-th-large"></i></div>
                    <h3 class="fw-bold mb-1"><?= $totalPlots ?? 0 ?></h3>
                    <p class="text-muted mb-0">Total Plots</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-check-circle"></i></div>
                    <h3 class="fw-bold mb-1"><?= $availablePlots ?? 0 ?></h3>
                    <p class="text-muted mb-0">Available Plots</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-danger mb-2"><i class="fas fa-times-circle"></i></div>
                    <h3 class="fw-bold mb-1"><?= $soldPlots ?? 0 ?></h3>
                    <p class="text-muted mb-0">Sold Plots</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-calendar-day"></i></div>
                    <h3 class="fw-bold mb-1"><?= $siteVisitsToday ?? 0 ?></h3>
                    <p class="text-muted mb-0">Site Visits Today</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Site Visits Scheduled</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($siteVisits)): ?>
                        <div class="table-responsive"><div class="table-responsive"><table class="table table-sm table-hover mb-0 table-responsive">
                            <thead><tr><th>Plot</th><th>Customer</th><th>Date</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($siteVisits as $visit): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($visit['plot_no'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($visit['customer_name'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($visit['visit_date'] ?? '') ?></td>
                                    <td><span class="badge bg-<?= ($visit['status'] ?? '') === 'completed' ? 'success' : 'primary' ?>"><?= ucfirst($visit['status'] ?? '') ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-calendar fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No site visits scheduled</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Plot Status Distribution</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($plotDistribution)): ?>
                        <?php foreach ($plotDistribution as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= htmlspecialchars($item['label'] ?? '') ?></span>
                            <span class="badge bg-secondary"><?= $item['count'] ?? 0 ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-chart-pie fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No plot data available</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
