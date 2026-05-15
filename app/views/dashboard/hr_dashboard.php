<?php
$data = $data ?? [];
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active text-white" href="<?= BASE_URL ?>/admin/dashboard/hr"><i class="fas fa-tachometer-alt me-2"></i>HR Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-building me-2"></i>Main Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/reports"><i class="fas fa-chart-bar me-2"></i>Reports</a></li>
                </ul>
            </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 mt-3">HR Dashboard</h1>
            <div class="row mb-4 mt-3">
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><h6>Total Employees</h6><h3><?= $data['total_employees'] ?? 0 ?></h3></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body"><h6>On Leave</h6><h3><?= $data['on_leave'] ?? 0 ?></h3></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100 py-2"><div class="card-body"><h6>Open Positions</h6><h3><?= $data['open_positions'] ?? 0 ?></h3></div></div></div>
                <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100 py-2"><div class="card-body"><h6>New Hires (Month)</h6><h3><?= $data['new_hires'] ?? 0 ?></h3></div></div></div>
            </div>
        </main>
    </div>
</div>
