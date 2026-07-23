<?php $pageTitle = 'Reports Dashboard'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-chart-pie me-2"></i>Reports Dashboard</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-4 mb-4"><a href="<?= BASE_URL ?>/admin/reports/sales" class="text-decoration-none"><div class="card border-0 shadow-sm h-100" style="cursor:pointer"><div class="card-body text-center py-5"><i class="fas fa-chart-line fa-3x text-primary mb-3"></i><h5>Sales Report</h5><p class="text-muted mb-0">View sales performance, trends, and forecasts</p></div></div></a></div>
        <div class="col-md-4 mb-4"><a href="<?= BASE_URL ?>/admin/reports/leads" class="text-decoration-none"><div class="card border-0 shadow-sm h-100" style="cursor:pointer"><div class="card-body text-center py-5"><i class="fas fa-users fa-3x text-success mb-3"></i><h5>Leads Report</h5><p class="text-muted mb-0">Lead generation, conversion, and source analysis</p></div></div></a></div>
        <div class="col-md-4 mb-4"><a href="<?= BASE_URL ?>/admin/reports/commission" class="text-decoration-none"><div class="card border-0 shadow-sm h-100" style="cursor:pointer"><div class="card-body text-center py-5"><i class="fas fa-coins fa-3x text-warning mb-3"></i><h5>Commission Report</h5><p class="text-muted mb-0">Commission calculations and payout summaries</p></div></div></a></div>
        <div class="col-md-6 mb-4"><a href="<?= BASE_URL ?>/admin/reports" class="text-decoration-none"><div class="card border-0 shadow-sm h-100" style="cursor:pointer"><div class="card-body text-center py-4"><i class="fas fa-file-alt fa-3x text-info mb-3"></i><h5>All Reports</h5><p class="text-muted mb-0">View comprehensive system reports</p></div></div></a></div>
        <div class="col-md-6 mb-4"><a href="<?= BASE_URL ?>/admin/reports/roi_calculator" class="text-decoration-none"><div class="card border-0 shadow-sm h-100" style="cursor:pointer"><div class="card-body text-center py-4"><i class="fas fa-calculator fa-3x text-secondary mb-3"></i><h5>ROI Calculator</h5><p class="text-muted mb-0">Return on investment analysis tool</p></div></div></a></div>
    </div>
</div>
