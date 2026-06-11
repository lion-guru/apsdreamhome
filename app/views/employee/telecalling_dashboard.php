<?php $pageTitle = 'Telecalling Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">Telecalling Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-phone-alt me-2"></i>Telecalling Dashboard</h4>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-phone-volume"></i></div>
                    <h3 class="fw-bold mb-1"><?= $totalCalls ?? 0 ?></h3>
                    <p class="text-muted mb-0">Total Calls</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-phone-alt"></i></div>
                    <h3 class="fw-bold mb-1"><?= $connectedCalls ?? 0 ?></h3>
                    <p class="text-muted mb-0">Connected</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-users"></i></div>
                    <h3 class="fw-bold mb-1"><?= $leadsGenerated ?? 0 ?></h3>
                    <p class="text-muted mb-0">Leads Generated</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-percent"></i></div>
                    <h3 class="fw-bold mb-1"><?= $conversionRate ?? 0 ?>%</h3>
                    <p class="text-muted mb-0">Conversion Rate</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Call Log</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($callLog)): ?>
                        <div class="table-responsive"><div class="table-responsive"><table class="table table-sm table-hover mb-0 table-responsive">
                            <thead><tr><th>Customer</th><th>Phone</th><th>Duration</th><th>Status</th><th>Notes</th></tr></thead>
                            <tbody>
                                <?php foreach ($callLog as $call): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($call['customer_name'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($call['phone'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($call['duration'] ?? '') ?></td>
                                    <td><span class="badge bg-<?= ($call['status'] ?? '') === 'connected' ? 'success' : 'secondary' ?>"><?= ucfirst($call['status'] ?? '') ?></span></td>
                                    <td class="small text-muted"><?= htmlspecialchars($call['notes'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-phone-slash fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No calls logged yet</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-chart-simple me-2"></i>Today's Stats</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($todayStats)): ?>
                        <?php foreach ($todayStats as $key => $val): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small"><?= ucfirst(str_replace('_', ' ', $key)) ?></span>
                            <strong><?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-chart-bar fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No stats for today</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
