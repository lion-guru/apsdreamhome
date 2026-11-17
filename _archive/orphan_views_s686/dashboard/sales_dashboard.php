<?php
$data = $data ?? [];
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active text-white" href="<?= BASE_URL ?>/admin/dashboard/sales"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-building me-2"></i>Main Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/leads"><i class="fas fa-users me-2"></i>Leads</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/reports"><i class="fas fa-chart-bar me-2"></i>Reports</a></li>
                </ul>
            </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Sales Dashboard</h1>
                <div>
                    <a href="<?= BASE_URL ?>/admin/ai/executive-assistant" class="btn btn-sm btn-info text-white me-2" title="AI Assistant">
                        <i class="fas fa-robot me-1"></i>Ask AI
                    </a>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100 py-2"><div class="card-body aps-cp-card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Monthly Sales</div><div class="h5 mb-0 font-weight-bold text-gray-800">₹<?= number_format($data['monthly_sales'] ?? 0) ?></div></div><div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div></div></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body aps-cp-card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Leads</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['total_leads'] ?? 0 ?></div></div><div class="col-auto"><i class="fas fa-user-plus fa-2x text-gray-300"></i></div></div></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100 py-2"><div class="card-body aps-cp-card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Conversion Rate</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['conversion_rate'] ?? 0 ?>%</div></div><div class="col-auto"><i class="fas fa-percentage fa-2x text-gray-300"></i></div></div></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100 py-2"><div class="card-body aps-cp-card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Properties Sold</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['properties_sold'] ?? 0 ?></div></div><div class="col-auto"><i class="fas fa-home fa-2x text-gray-300"></i></div></div></div></div></div>
            </div>
        </main>
    </div>
</div>
