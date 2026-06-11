<?php
$data = $data ?? [];
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active text-white" href="<?= BASE_URL ?>/admin/dashboard/cto"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-building me-2"></i>Main Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/reports"><i class="fas fa-chart-bar me-2"></i>Reports</a></li>
                </ul>
            </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">CTO Dashboard</h1>
            </div>
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100 py-2"><div class="card-body aps-cp-card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">System Uptime</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['uptime'] ?? '99.9%' ?></div></div><div class="col-auto"><i class="fas fa-server fa-2x text-gray-300"></i></div></div></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body aps-cp-card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Users</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['active_users'] ?? 0 ?></div></div><div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div></div></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100 py-2"><div class="card-body aps-cp-card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">API Calls</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($data['api_calls'] ?? 0) ?></div></div><div class="col-auto"><i class="fas fa-code fa-2x text-gray-300"></i></div></div></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100 py-2"><div class="card-body aps-cp-card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Open Tickets</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['open_tickets'] ?? 0 ?></div></div><div class="col-auto"><i class="fas fa-ticket-alt fa-2x text-gray-300"></i></div></div></div></div></div>
            </div>
        </main>
    </div>
</div>
