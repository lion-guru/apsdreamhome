<?php $pageTitle = 'Financial Analytics'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-chart-pie me-2"></i>Financial Analytics</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/analytics">Analytics</a></li>
                    <li class="breadcrumb-item active">Financial</li>
                </ul>
            </div>
            <div class="col-auto">
                <select class="form-select form-select-sm" style="width:auto"><option>This Year</option><option>This Quarter</option><option>This Month</option></select>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><i class="fas fa-coins fa-2x text-success mb-2"></i><h6>Total Revenue</h6><h4 class="text-success mb-0">₹<?= number_format($totalRevenue ?? 0, 2) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><i class="fas fa-receipt fa-2x text-danger mb-2"></i><h6>Total Expenses</h6><h4 class="text-danger mb-0">₹<?= number_format($totalExpenses ?? 0, 2) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><i class="fas fa-chart-line fa-2x text-primary mb-2"></i><h6>Net Profit</h6><h4 class="text-primary mb-0">₹<?= number_format(($totalRevenue ?? 0) - ($totalExpenses ?? 0), 2) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><i class="fas fa-percent fa-2x text-info mb-2"></i><h6>Profit Margin</h6><h4 class="text-info mb-0"><?= ($totalRevenue ?? 0) > 0 ? number_format((($totalRevenue - ($totalExpenses ?? 0)) / $totalRevenue) * 100, 1) : 0 ?>%</h4></div></div></div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-arrow-up me-2 text-success"></i>Revenue Breakdown</h5></div><div class="card-body aps-cp-card-body"><div class="text-center py-4 text-muted"><i class="fas fa-chart-simple fa-4x d-block mb-3"></i>Revenue chart area</div></div></div></div>
        <div class="col-md-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-arrow-down me-2 text-danger"></i>Expense Breakdown</h5></div><div class="card-body aps-cp-card-body"><div class="text-center py-4 text-muted"><i class="fas fa-chart-pie fa-4x d-block mb-3"></i>Expense chart area</div></div></div></div>
    </div>
</div>
