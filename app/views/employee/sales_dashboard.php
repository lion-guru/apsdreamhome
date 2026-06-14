<?php $pageTitle = 'Sales Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sales Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>Sales Dashboard</h4>
        <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?= date('l, F j, Y') ?></span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-rupee-sign"></i></div>
                    <h3 class="fw-bold mb-1">₹<?= number_format($totalSales ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Sales</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-handshake"></i></div>
                    <h3 class="fw-bold mb-1"><?= $activeDeals ?? 0 ?></h3>
                    <p class="text-muted mb-0">Active Deals</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-clock"></i></div>
                    <h3 class="fw-bold mb-1"><?= $pendingFollowups ?? 0 ?></h3>
                    <p class="text-muted mb-0">Pending Follow-ups</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-percentage"></i></div>
                    <h3 class="fw-bold mb-1"><?= $conversionRate ?? 0 ?>%</h3>
                    <p class="text-muted mb-0">Conversion Rate</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-handshake me-2"></i>Active Deals</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($deals)): ?>
                        <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Customer</th><th>Property</th><th class="text-end">Value</th><th>Stage</th></tr></thead>
                            <tbody>
                                <?php foreach ($deals as $deal): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($deal['customer_name'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($deal['property_name'] ?? '') ?></td>
                                    <td class="text-end small">₹<?= number_format($deal['deal_value'] ?? 0) ?></td>
                                    <td><span class="badge bg-<?= ($deal['stage'] ?? '') === 'closed_won' ? 'success' : (($deal['stage'] ?? '') === 'negotiation' ? 'warning' : 'primary') ?>"><?= ucfirst(str_replace('_', ' ', $deal['stage'] ?? '')) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-handshake fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No active deals</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Sales by Colony</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($salesByColony)): ?>
                        <?php foreach ($salesByColony as $col): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= htmlspecialchars($col['colony'] ?? '') ?></span>
                            <span class="badge bg-primary">₹<?= number_format($col['total'] ?? 0) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-building fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No sales data by colony</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
