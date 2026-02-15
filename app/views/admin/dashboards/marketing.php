<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-bullhorn me-2"></i>Marketing Dashboard</h2>
        </div>
    </div>

    <!-- Marketing Performance -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Total Leads</h6>
                    <h3><?php echo $leads_pipeline['total'] ?? '0'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up me-1"></i>15% increase</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Marketing ROI</h6>
                    <h3><?php echo $marketing_roi['overall'] ?? '3.5x'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-check-circle me-1"></i>Above target</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-info border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Active Campaigns</h6>
                    <h3><?php echo $marketing_roi['active_campaigns'] ?? '4'; ?></h3>
                    <p class="text-muted mb-0">Running smoothly</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lead Sources -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Lead Generation by Source</h5>
                </div>
                <div class="card-body" style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                    <div class="text-center text-muted">
                        <i class="fas fa-pie-chart fa-3x mb-3"></i>
                        <p>Source Breakdown (Chart.js Integration Pending)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campaign Status -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Active Campaigns</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small">Facebook Ads (Luxury Plots)</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <small class="text-muted">Conversion: 2.4%</small>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small">Google Search (Homes)</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <small class="text-muted">Conversion: 1.8%</small>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small">Email Newsletter</span>
                                <span class="badge bg-warning">Paused</span>
                            </div>
                            <small class="text-muted">Conversion: 0.5%</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
