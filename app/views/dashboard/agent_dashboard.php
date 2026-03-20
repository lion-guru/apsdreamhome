<?php
// Agent Dashboard Template
$data = $data ?? [];
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= BASE_URL ?>/admin/dashboard/agent">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/properties">
                            <i class="fas fa-home me-2"></i>Properties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/bookings">
                            <i class="fas fa-calendar me-2"></i>Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/team/genealogy">
                            <i class="fas fa-network-wired me-2"></i>Network
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/reports">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Agent Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Properties Sold</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['properties_sold'] ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-home fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Commission Earned</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">₹<?= number_format($data['commission_earned'] ?? 0) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Clients</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['active_clients'] ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Deals</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['pending_deals'] ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                        </div>
                        <div class="card-body">
                            <?php if (isset($data['recent_activities']) && !empty($data['recent_activities'])): ?>
                                <?php foreach ($data['recent_activities'] as $activity): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-<?= $activity['icon'] ?? 'circle' ?> text-<?= $activity['color'] ?? 'primary' ?>"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="small text-gray-500"><?= $activity['date'] ?? '' ?></div>
                                            <div class="fw-bold"><?= $activity['title'] ?? '' ?></div>
                                            <div class="text-muted small"><?= $activity['description'] ?? '' ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No recent activities found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Performance Chart</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="performanceChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/admin/properties/create" class="btn btn-primary btn-block">
                                        <i class="fas fa-plus me-2"></i>Add Property
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/admin/bookings/create" class="btn btn-success btn-block">
                                        <i class="fas fa-calendar-plus me-2"></i>New Booking
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/admin/reports" class="btn btn-info btn-block">
                                        <i class="fas fa-chart-line me-2"></i>View Reports
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="<?= BASE_URL ?>/team/genealogy" class="btn btn-warning btn-block">
                                        <i class="fas fa-sitemap me-2"></i>Network Tree
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Performance Chart
const ctx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($data['chart_labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) ?>,
        datasets: [{
            label: 'Sales',
            data: <?= json_encode($data['chart_data'] ?? [0, 0, 0, 0, 0, 0]) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
